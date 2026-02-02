<?php

namespace App\Console\Commands\ClawDBot;

use App\Jobs\ClawDBot\NotifyPropertyOwners;
use App\Models\ClawDBot\BotTask;
use App\Models\Property;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ExpiryNotifierCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clawdbot:expiry-notifier 
                            {--days=7 : Notify owners properties expiring within X days}
                            {--type=warning : Notification type (warning|critical|expired)}
                            {--force : Send notifications without confirmation}
                            {--preview : Show notifications without sending}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'ClawDBot: Notify property owners about expiring listings';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🤖 ClawDBot Property Expiry Notifier Started');
        $this->line('===========================================');

        $warningDays = $this->option('days');
        $notificationType = $this->option('type');
        $force = $this->option('force');
        $preview = $this->option('preview');

        $this->line("⚠️  Checking properties expiring within {$warningDays} days");
        $this->line("📧 Notification type: {$notificationType}");

        if (!$force && !$preview) {
            if (!$this->confirm('This will send expiry notifications to property owners. Continue?')) {
                $this->info('Expiry notifications cancelled.');
                return 0;
            }
        }

        try {
            // Start bot task logging
            $botTask = BotTask::create([
                'command' => 'clawdbot:expiry-notifier',
                'status' => 'running',
                'started_at' => now(),
                'parameters' => json_encode([
                    'warning_days' => $warningDays,
                    'notification_type' => $notificationType,
                    'preview' => $preview
                ])
            ]);

            // Find properties based on notification type
            $properties = $this->getPropertiesByType($warningDays, $notificationType);

            if ($properties->isEmpty()) {
                $this->info('✨ No properties found for expiry notifications');
                $botTask->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'result' => json_encode(['no_properties' => true])
                ]);
                return 0;
            }

            $this->line("📋 Found {$properties->count()} properties for notifications");

            // Process notifications
            $notificationCount = 0;
            foreach ($properties as $property) {
                if ($preview) {
                    $this->displayNotificationPreview($property, $notificationType);
                    $notificationCount++;
                    continue;
                }

                // Dispatch notification job
                NotifyPropertyOwners::dispatch($property, $notificationType);
                $notificationCount++;

                $this->line("✅ Queued notification for: {$property->title}");
            }

            // Update bot task status
            $botTask->update([
                'status' => 'completed',
                'completed_at' => now(),
                'result' => json_encode([
                    'notifications_sent' => $notificationCount,
                    'notification_type' => $notificationType,
                    'warning_days' => $warningDays
                ])
            ]);

            $action = $preview ? 'previewed' : 'sent';
            $this->info("✅ Expiry notifications {$action} successfully");
            $this->line("📊 Total notifications: {$notificationCount}");

            return 0;

        } catch (\Exception $e) {
            Log::error('ClawDBot Expiry Notifier Error: ' . $e->getMessage(), [
                'exception' => $e,
                'command' => 'clawdbot:expiry-notifier',
                'notification_type' => $notificationType
            ]);

            if (isset($botTask)) {
                $botTask->update([
                    'status' => 'failed',
                    'completed_at' => now(),
                    'error_message' => $e->getMessage()
                ]);
            }

            $this->error('❌ Expiry notifier failed: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Get properties based on notification type
     */
    private function getPropertiesByType(int $warningDays, string $notificationType)
    {
        $now = now();
        
        switch ($notificationType) {
            case 'warning':
                // Properties expiring within X days
                return Property::where('status', 'active')
                    ->where('expires_at', '>=', $now)
                    ->where('expires_at', '<=', $now->copy()->addDays($warningDays))
                    ->with(['owner', 'location', 'images'])
                    ->get();

            case 'critical':
                // Properties expiring within 3 days (critical)
                return Property::where('status', 'active')
                    ->where('expires_at', '>=', $now)
                    ->where('expires_at', '<=', $now->copy()->addDays(3))
                    ->with(['owner', 'location', 'images'])
                    ->get();

            case 'expired':
                // Properties that expired today
                return Property::where('status', 'active')
                    ->where('expires_at', '<', $now)
                    ->whereDate('expires_at', $now->toDateString())
                    ->with(['owner', 'location', 'images'])
                    ->get();

            default:
                return collect();
        }
    }

    /**
     * Display notification preview
     */
    private function displayNotificationPreview(Property $property, string $notificationType): void
    {
        $owner = $property->owner;
        $expiryDate = $property->expires_at ? $property->expires_at->format('M j, Y') : 'Not set';
        $daysUntilExpiry = $property->expires_at ? now()->diffInDays($property->expires_at) : 0;

        $this->line("\n📧 NOTIFICATION PREVIEW");
        $this->line("   To: {$owner->name} ({$owner->email})");
        $this->line("   Property: {$property->title}");
        $this->line("   Location: {$property->location?->name ?? 'Not specified'}");
        $this->line("   Price: $" . number_format($property->price, 2));
        $this->line("   Expires: {$expiryDate}");

        switch ($notificationType) {
            case 'warning':
                $this->line("   Type: ⚠️  Expiry Warning ({$daysUntilExpiry} days remaining)");
                break;
            case 'critical':
                $this->line("   Type: 🚨 Critical Expiry Alert ({$daysUntilExpiry} days remaining)");
                break;
            case 'expired':
                $this->line("   Type: ❌ Property Expired");
                break;
        }

        $this->line("   Subject: Property Listing Status Alert");
        $this->line("   Template: clawdbot.expiry-{$notificationType}");
        $this->line("");
    }
}
