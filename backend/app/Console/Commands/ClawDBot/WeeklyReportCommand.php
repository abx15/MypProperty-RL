<?php

namespace App\Console\Commands\ClawDBot;

use App\Jobs\ClawDBot\GenerateWeeklyReport;
use App\Models\ClawDBot\BotTask;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class WeeklyReportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clawdbot:weekly-report 
                            {--week= : Specific week number (1-52)}
                            {--year= : Specific year}
                            {--email= : Send report to specific email}
                            {--preview : Show report without sending}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'ClawDBot: Generate comprehensive weekly analytics report';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🤖 ClawDBot Weekly Report Generation Started');
        $this->line('===========================================');

        $week = $this->option('week') ?? now()->weekOfYear;
        $year = $this->option('year') ?? now()->year;
        $specificEmail = $this->option('email');
        $preview = $this->option('preview');

        // Calculate week date range
        $startOfWeek = Carbon::createFromDate($year, 1, 1)
            ->addWeeks($week - 1)
            ->startOfWeek();
        $endOfWeek = $startOfWeek->copy()->endOfWeek();

        $this->line("📅 Generating report for Week {$week}, {$year}");
        $this->line("📊 Period: {$startOfWeek->format('M j')} - {$endOfWeek->format('M j, Y')}");

        try {
            // Start bot task logging
            $botTask = BotTask::create([
                'command' => 'clawdbot:weekly-report',
                'status' => 'running',
                'started_at' => now(),
                'parameters' => json_encode([
                    'week' => $week,
                    'year' => $year,
                    'specific_email' => $specificEmail,
                    'preview' => $preview
                ])
            ]);

            // Generate weekly report data
            $reportData = $this->generateWeeklyReport($startOfWeek, $endOfWeek);

            // Display preview if requested
            if ($preview) {
                $this->displayReportPreview($reportData, $week, $year);
                $botTask->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'result' => json_encode(['preview_generated' => true])
                ]);
                return 0;
            }

            // Get recipients (admins only for weekly reports)
            $recipients = $this->getAdminRecipients($specificEmail);

            if ($recipients->isEmpty()) {
                $this->warn('⚠️  No admin recipients found for weekly report');
                $botTask->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'result' => json_encode(['no_recipients' => true])
                ]);
                return 0;
            }

            // Send weekly report to all admin recipients
            foreach ($recipients as $recipient) {
                GenerateWeeklyReport::dispatch($recipient, $reportData, $week, $year);
            }

            // Update bot task status
            $botTask->update([
                'status' => 'completed',
                'completed_at' => now(),
                'result' => json_encode([
                    'recipients_count' => $recipients->count(),
                    'report_data' => $reportData
                ])
            ]);

            $this->info('✅ Weekly report sent successfully');
            $this->line("📊 Report sent to {$recipients->count()} admin recipients");
            $this->line("📈 Total properties added: {$reportData['properties']['new']}");
            $this->line("💬 Total enquiries: {$reportData['enquiries']['total']}");
            $this->line("👥 New users: {$reportData['users']['new']}");

            return 0;

        } catch (\Exception $e) {
            Log::error('ClawDBot Weekly Report Error: ' . $e->getMessage(), [
                'exception' => $e,
                'command' => 'clawdbot:weekly-report',
                'week' => $week,
                'year' => $year
            ]);

            if (isset($botTask)) {
                $botTask->update([
                    'status' => 'failed',
                    'completed_at' => now(),
                    'error_message' => $e->getMessage()
                ]);
            }

            $this->error('❌ Weekly report failed: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Generate comprehensive weekly report data
     */
    private function generateWeeklyReport(Carbon $start, Carbon $end): array
    {
        return [
            'period' => [
                'week' => $start->weekOfYear,
                'year' => $start->year,
                'start_date' => $start->toDateString(),
                'end_date' => $end->toDateString(),
                'days_in_week' => $start->diffInDays($end) + 1
            ],
            'properties' => $this->getPropertyStats($start, $end),
            'enquiries' => $this->getEnquiryStats($start, $end),
            'users' => $this->getUserStats($start, $end),
            'analytics' => $this->getAnalyticsStats($start, $end),
            'performance' => $this->getPerformanceMetrics($start, $end),
            'recommendations' => $this->generateRecommendations($start, $end)
        ];
    }

    /**
     * Get property statistics for the week
     */
    private function getPropertyStats(Carbon $start, Carbon $end): array
    {
        $properties = \App\Models\Property::whereBetween('created_at', [$start, $end]);

        return [
            'new' => $properties->count(),
            'active' => \App\Models\Property::where('status', 'active')->count(),
            'expired' => \App\Models\Property::where('status', 'expired')->count(),
            'inactive' => \App\Models\Property::where('status', 'inactive')->count(),
            'total_value' => $properties->sum('price'),
            'avg_price' => $properties->avg('price'),
            'categories' => $properties->selectRaw('category, COUNT(*) as count')
                ->groupBy('category')
                ->orderByDesc('count')
                ->pluck('count', 'category')
                ->toArray(),
            'top_locations' => $properties->with('location')
                ->selectRaw('location_id, COUNT(*) as count')
                ->groupBy('location_id')
                ->orderByDesc('count')
                ->limit(10)
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->location?->name ?? 'Unknown' => $item->count];
                })
                ->toArray()
        ];
    }

    /**
     * Get enquiry statistics for the week
     */
    private function getEnquiryStats(Carbon $start, Carbon $end): array
    {
        $enquiries = \App\Models\Enquiry::whereBetween('created_at', [$start, $end]);

        return [
            'total' => $enquiries->count(),
            'pending' => $enquiries->where('status', 'pending')->count(),
            'responded' => $enquiries->where('status', 'responded')->count(),
            'closed' => $enquiries->where('status', 'closed')->count(),
            'response_rate' => $enquiries->where('status', '!=', 'pending')->count() > 0 
                ? round(($enquiries->where('status', 'responded')->count() / $enquiries->count()) * 100, 2) 
                : 0,
            'daily_average' => round($enquiries->count() / 7, 2)
        ];
    }

    /**
     * Get user statistics for the week
     */
    private function getUserStats(Carbon $start, Carbon $end): array
    {
        $newUsers = User::whereBetween('created_at', [$start, $end]);

        return [
            'new' => $newUsers->count(),
            'total' => User::count(),
            'active' => User::where('status', 'active')->count(),
            'roles' => $newUsers->join('model_has_roles', 'users.id', 'model_has_roles.model_id')
                ->join('roles', 'model_has_roles.role_id', 'roles.id')
                ->selectRaw('roles.name, COUNT(*) as count')
                ->groupBy('roles.name')
                ->orderByDesc('count')
                ->pluck('count', 'roles.name')
                ->toArray()
        ];
    }

    /**
     * Get analytics statistics for the week
     */
    private function getAnalyticsStats(Carbon $start, Carbon $end): array
    {
        // This would integrate with your analytics system
        // For now, return placeholder data that you can customize
        return [
            'page_views' => 0,
            'unique_visitors' => 0,
            'property_views' => 0,
            'search_queries' => 0,
            'conversion_rate' => 0,
            'bounce_rate' => 0
        ];
    }

    /**
     * Get performance metrics for the week
     */
    private function getPerformanceMetrics(Carbon $start, Carbon $end): array
    {
        return [
            'avg_response_time' => $this->calculateAverageResponseTime($start, $end),
            'property_conversion_rate' => $this->calculatePropertyConversionRate($start, $end),
            'user_engagement_score' => $this->calculateUserEngagement($start, $end),
            'system_uptime' => 99.9, // Placeholder
            'error_rate' => 0.1 // Placeholder
        ];
    }

    /**
     * Generate AI-ready recommendations
     */
    private function generateRecommendations(Carbon $start, Carbon $end): array
    {
        return [
            'content' => [
                'focus_categories' => $this->getTopCategories($start, $end),
                'recommended_locations' => $this->getRecommendedLocations($start, $end),
                'price_adjustments' => $this->getPriceRecommendations($start, $end)
            ],
            'marketing' => [
                'target_audience' => 'Focus on first-time home buyers',
                'featured_properties' => $this->getFeaturedProperties($start, $end),
                'campaign_suggestions' => ['Email campaign for expired listings', 'Social media for new properties']
            ],
            'operations' => [
                'staffing_needs' => 'Consider hiring more agents for enquiry handling',
                'system_improvements' => 'Implement automated price suggestions',
                'process_optimization' => 'Streamline property approval workflow'
            ]
        ];
    }

    /**
     * Helper methods for calculations
     */
    private function calculateAverageResponseTime(Carbon $start, Carbon $end): float
    {
        // Calculate average time between enquiry and response
        return 2.5; // Placeholder in hours
    }

    private function calculatePropertyConversionRate(Carbon $start, Carbon $end): float
    {
        // Calculate property-to-enquiry conversion rate
        return 15.5; // Placeholder percentage
    }

    private function calculateUserEngagement(Carbon $start, Carbon $end): float
    {
        // Calculate user engagement score
        return 78.2; // Placeholder score
    }

    private function getTopCategories(Carbon $start, Carbon $end): array
    {
        return ['Apartment', 'House', 'Commercial']; // Placeholder
    }

    private function getRecommendedLocations(Carbon $start, Carbon $end): array
    {
        return ['Downtown', 'Suburbs', 'Industrial Area']; // Placeholder
    }

    private function getPriceRecommendations(Carbon $start, Carbon $end): array
    {
        return ['Increase prices in Downtown', 'Competitive pricing for apartments']; // Placeholder
    }

    private function getFeaturedProperties(Carbon $start, Carbon $end): array
    {
        return ['High-view properties', 'Newly listed', 'Price reduced']; // Placeholder
    }

    /**
     * Get admin recipients for the report
     */
    private function getAdminRecipients(?string $specificEmail): \Illuminate\Database\Eloquent\Collection
    {
        if ($specificEmail) {
            return User::where('email', $specificEmail)->get();
        }

        return User::where('status', 'active')
            ->whereHas('roles', function ($query) {
                $query->where('name', 'admin');
            })
            ->get();
    }

    /**
     * Display report preview
     */
    private function displayReportPreview(array $reportData, int $week, int $year): void
    {
        $this->line("\n📊 WEEKLY REPORT PREVIEW - Week {$week}, {$year}");
        $this->line(str_repeat('=', 60));

        $this->line("\n🏠 PROPERTY STATISTICS:");
        $this->line("   New Properties: {$reportData['properties']['new']}");
        $this->line("   Active Properties: {$reportData['properties']['active']}");
        $this->line("   Total Value: $" . number_format($reportData['properties']['total_value'] ?? 0, 2));
        $this->line("   Average Price: $" . number_format($reportData['properties']['avg_price'] ?? 0, 2));

        $this->line("\n💬 ENQUIRY STATISTICS:");
        $this->line("   Total Enquiries: {$reportData['enquiries']['total']}");
        $this->line("   Response Rate: {$reportData['enquiries']['response_rate']}%");
        $this->line("   Daily Average: {$reportData['enquiries']['daily_average']}");

        $this->line("\n👥 USER STATISTICS:");
        $this->line("   New Users: {$reportData['users']['new']}");
        $this->line("   Total Active Users: {$reportData['users']['active']}");

        $this->line("\n📈 PERFORMANCE METRICS:");
        $this->line("   Avg Response Time: {$reportData['performance']['avg_response_time']} hours");
        $this->line("   Conversion Rate: {$reportData['performance']['property_conversion_rate']}%");
        $this->line("   Engagement Score: {$reportData['performance']['user_engagement_score']}");

        if (!empty($reportData['recommendations']['content']['focus_categories'])) {
            $this->line("\n💡 RECOMMENDATIONS:");
            $this->line("   Focus Categories: " . implode(', ', $reportData['recommendations']['content']['focus_categories']));
            $this->line("   Target Audience: {$reportData['recommendations']['marketing']['target_audience']}");
        }

        $this->line("\n" . str_repeat('=', 60));
    }
}
