<?php

namespace App\Console\Commands\ClawDBot;

use App\Jobs\ClawDBot\CleanupExpiredListings;
use App\Jobs\ClawDBot\NotifyPropertyOwners;
use App\Jobs\ClawDBot\UpdatePropertyStatus;
use App\Models\Property;
use App\Models\ClawDBot\BotTask;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PropertyCleanupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clawdbot:property-cleanup 
                            {--dry-run : Show what would be done without executing}
                            {--force : Force cleanup without confirmation}
                            {--days=30 : Consider properties older than X days as inactive}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'ClawDBot: Clean up expired and inactive properties';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🤖 ClawDBot Property Cleanup Started');
        $this->line('================================');

        $dryRun = $this->option('dry-run');
        $force = $this->option('force');
        $inactiveDays = $this->option('days');

        if (!$dryRun && !$force) {
            if (!$this->confirm('This will process expired properties. Continue?')) {
                $this->info('Property cleanup cancelled.');
                return 0;
            }
        }

        try {
            // Start bot task logging
            $botTask = BotTask::create([
                'command' => 'clawdbot:property-cleanup',
                'status' => 'running',
                'started_at' => now(),
                'parameters' => json_encode([
                    'dry_run' => $dryRun,
                    'inactive_days' => $inactiveDays
                ])
            ]);

            // Process expired properties
            $this->processExpiredProperties($dryRun);

            // Process inactive properties
            $this->processInactiveProperties($inactiveDays, $dryRun);

            // Update bot task status
            $botTask->update([
                'status' => 'completed',
                'completed_at' => now(),
                'result' => json_encode([
                    'expired_processed' => $this->expiredCount,
                    'inactive_processed' => $this->inactiveCount,
                    'notifications_sent' => $this->notificationCount
                ])
            ]);

            $this->info('✅ Property cleanup completed successfully');
            $this->line("📊 Summary:");
            $this->line("   - Expired properties processed: {$this->expiredCount}");
            $this->line("   - Inactive properties processed: {$this->inactiveCount}");
            $this->line("   - Notifications sent: {$this->notificationCount}");

            return 0;

        } catch (\Exception $e) {
            Log::error('ClawDBot Property Cleanup Error: ' . $e->getMessage(), [
                'exception' => $e,
                'command' => 'clawdbot:property-cleanup'
            ]);

            if (isset($botTask)) {
                $botTask->update([
                    'status' => 'failed',
                    'completed_at' => now(),
                    'error_message' => $e->getMessage()
                ]);
            }

            $this->error('❌ Property cleanup failed: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Process expired properties
     */
    private function processExpiredProperties(bool $dryRun): void
    {
        $this->line('🔍 Processing expired properties...');

        $expiredProperties = Property::where('status', 'active')
            ->where('expires_at', '<=', now())
            ->with(['owner', 'images'])
            ->get();

        $this->expiredCount = $expiredProperties->count();

        if ($this->expiredCount === 0) {
            $this->info('   ✨ No expired properties found');
            return;
        }

        $this->line("   📋 Found {$this->expiredCount} expired properties");

        foreach ($expiredProperties as $property) {
            if ($dryRun) {
                $this->line("   🔄 [DRY RUN] Would expire: {$property->title} (ID: {$property->id})");
                continue;
            }

            // Dispatch job to handle property expiration
            UpdatePropertyStatus::dispatch($property, 'expired');
            
            // Notify owner
            NotifyPropertyOwners::dispatch($property, 'expired');
            
            $this->line("   ✅ Processed: {$property->title}");
            $this->notificationCount++;
        }
    }

    /**
     * Process inactive properties
     */
    private function processInactiveProperties(int $inactiveDays, bool $dryRun): void
    {
        $this->line('🔍 Processing inactive properties...');

        $inactiveDate = Carbon::now()->subDays($inactiveDays);

        $inactiveProperties = Property::where('status', 'active')
            ->where('updated_at', '<', $inactiveDate)
            ->whereDoesntHave('enquiries', function ($query) use ($inactiveDate) {
                $query->where('created_at', '>=', $inactiveDate);
            })
            ->with(['owner'])
            ->get();

        $this->inactiveCount = $inactiveProperties->count();

        if ($this->inactiveCount === 0) {
            $this->info('   ✨ No inactive properties found');
            return;
        }

        $this->line("   📋 Found {$this->inactiveCount} inactive properties (older than {$inactiveDays} days)");

        foreach ($inactiveProperties as $property) {
            if ($dryRun) {
                $this->line("   🔄 [DRY RUN] Would mark inactive: {$property->title} (ID: {$property->id})");
                continue;
            }

            // Dispatch job to handle inactive property
            UpdatePropertyStatus::dispatch($property, 'inactive');
            
            // Notify owner about inactivity
            NotifyPropertyOwners::dispatch($property, 'inactive');
            
            $this->line("   ✅ Processed: {$property->title}");
            $this->notificationCount++;
        }
    }
}
