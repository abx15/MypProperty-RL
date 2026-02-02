<?php

namespace App\Console\Commands\ClawDBot;

use App\Jobs\ClawDBot\SendDailyDigest;
use App\Models\ClawDBot\BotTask;
use App\Models\User;
use App\Models\Property;
use App\Models\Enquiry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DailySummaryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clawdbot:daily-summary 
                            {--date= : Specific date to summarize (YYYY-MM-DD)}
                            {--email= : Send summary to specific email}
                            {--admin-only : Send only to admin users}
                            {--preview : Show summary without sending}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'ClawDBot: Generate and send daily property summary';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🤖 ClawDBot Daily Summary Generation Started');
        $this->line('==========================================');

        $targetDate = $this->option('date') ? Carbon::parse($this->option('date')) : Carbon::yesterday();
        $specificEmail = $this->option('email');
        $adminOnly = $this->option('admin-only');
        $preview = $this->option('preview');

        $this->line("📅 Generating summary for: {$targetDate->format('Y-m-d')}");

        try {
            // Start bot task logging
            $botTask = BotTask::create([
                'command' => 'clawdbot:daily-summary',
                'status' => 'running',
                'started_at' => now(),
                'parameters' => json_encode([
                    'target_date' => $targetDate->toDateString(),
                    'specific_email' => $specificEmail,
                    'admin_only' => $adminOnly,
                    'preview' => $preview
                ])
            ]);

            // Gather daily statistics
            $summaryData = $this->gatherDailyStats($targetDate);

            // Display preview if requested
            if ($preview) {
                $this->displaySummaryPreview($summaryData, $targetDate);
                $botTask->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'result' => json_encode(['preview_generated' => true])
                ]);
                return 0;
            }

            // Determine recipients
            $recipients = $this->getRecipients($specificEmail, $adminOnly);

            if ($recipients->isEmpty()) {
                $this->warn('⚠️  No recipients found for daily summary');
                $botTask->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'result' => json_encode(['no_recipients' => true])
                ]);
                return 0;
            }

            // Send daily digest to all recipients
            foreach ($recipients as $recipient) {
                SendDailyDigest::dispatch($recipient, $summaryData, $targetDate);
            }

            // Update bot task status
            $botTask->update([
                'status' => 'completed',
                'completed_at' => now(),
                'result' => json_encode([
                    'recipients_count' => $recipients->count(),
                    'summary_data' => $summaryData
                ])
            ]);

            $this->info('✅ Daily summary sent successfully');
            $this->line("📊 Summary sent to {$recipients->count()} recipients");
            $this->line("📈 New properties: {$summaryData['new_properties']}");
            $this->line("💬 New enquiries: {$summaryData['new_enquiries']}");
            $this->line("👥 New users: {$summaryData['new_users']}");

            return 0;

        } catch (\Exception $e) {
            Log::error('ClawDBot Daily Summary Error: ' . $e->getMessage(), [
                'exception' => $e,
                'command' => 'clawdbot:daily-summary',
                'target_date' => $targetDate->toDateString()
            ]);

            if (isset($botTask)) {
                $botTask->update([
                    'status' => 'failed',
                    'completed_at' => now(),
                    'error_message' => $e->getMessage()
                ]);
            }

            $this->error('❌ Daily summary failed: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Gather daily statistics
     */
    private function gatherDailyStats(Carbon $date): array
    {
        $startOfDay = $date->copy()->startOfDay();
        $endOfDay = $date->copy()->endOfDay();

        return [
            'date' => $date->format('Y-m-d'),
            'new_properties' => Property::whereBetween('created_at', [$startOfDay, $endOfDay])->count(),
            'new_enquiries' => Enquiry::whereBetween('created_at', [$startOfDay, $endOfDay])->count(),
            'new_users' => User::whereBetween('created_at', [$startOfDay, $endOfDay])->count(),
            'active_properties' => Property::where('status', 'active')->count(),
            'total_properties' => Property::count(),
            'total_users' => User::count(),
            'popular_categories' => $this->getPopularCategories($startOfDay, $endOfDay),
            'top_locations' => $this->getTopLocations($startOfDay, $endOfDay),
            'price_changes' => $this->getPriceChanges($startOfDay, $endOfDay),
        ];
    }

    /**
     * Get popular categories for the day
     */
    private function getPopularCategories(Carbon $start, Carbon $end): array
    {
        return Property::whereBetween('created_at', [$start, $end])
            ->selectRaw('category, COUNT(*) as count')
            ->groupBy('category')
            ->orderByDesc('count')
            ->limit(5)
            ->pluck('count', 'category')
            ->toArray();
    }

    /**
     * Get top locations for the day
     */
    private function getTopLocations(Carbon $start, Carbon $end): array
    {
        return Property::whereBetween('created_at', [$start, $end])
            ->with('location')
            ->selectRaw('location_id, COUNT(*) as count')
            ->groupBy('location_id')
            ->orderByDesc('count')
            ->limit(5)
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->location?->name ?? 'Unknown' => $item->count];
            })
            ->toArray();
    }

    /**
     * Get price changes for the day
     */
    private function getPriceChanges(Carbon $start, Carbon $end): array
    {
        // This would track price changes - assuming you have a price history table
        // For now, return placeholder data
        return [
            'increased' => 0,
            'decreased' => 0,
            'total_changes' => 0
        ];
    }

    /**
     * Get recipients for the daily summary
     */
    private function getRecipients(?string $specificEmail, bool $adminOnly): \Illuminate\Database\Eloquent\Collection
    {
        if ($specificEmail) {
            return User::where('email', $specificEmail)->get();
        }

        $query = User::where('status', 'active');

        if ($adminOnly) {
            $query->whereHas('roles', function ($q) {
                $q->where('name', 'admin');
            });
        } else {
            // Send to admins and agents
            $query->whereHas('roles', function ($q) {
                $q->whereIn('name', ['admin', 'agent']);
            });
        }

        return $query->get();
    }

    /**
     * Display summary preview
     */
    private function displaySummaryPreview(array $summaryData, Carbon $date): void
    {
        $this->line("\n📊 DAILY SUMMARY PREVIEW - {$date->format('l, F j, Y')}");
        $this->line(str_repeat('=', 50));

        $this->line("\n🏠 PROPERTIES:");
        $this->line("   New Properties: {$summaryData['new_properties']}");
        $this->line("   Active Properties: {$summaryData['active_properties']}");
        $this->line("   Total Properties: {$summaryData['total_properties']}");

        $this->line("\n💬 ENQUIRIES:");
        $this->line("   New Enquiries: {$summaryData['new_enquiries']}");

        $this->line("\n👥 USERS:");
        $this->line("   New Users: {$summaryData['new_users']}");
        $this->line("   Total Users: {$summaryData['total_users']}");

        if (!empty($summaryData['popular_categories'])) {
            $this->line("\n📈 POPULAR CATEGORIES:");
            foreach ($summaryData['popular_categories'] as $category => $count) {
                $this->line("   {$category}: {$count}");
            }
        }

        if (!empty($summaryData['top_locations'])) {
            $this->line("\n📍 TOP LOCATIONS:");
            foreach ($summaryData['top_locations'] as $location => $count) {
                $this->line("   {$location}: {$count}");
            }
        }

        $this->line("\n" . str_repeat('=', 50));
    }
}
