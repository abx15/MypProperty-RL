<?php

namespace App\Console\Commands\ClawDBot;

use App\Models\ClawDBot\BotTask;
use App\Models\Property;
use App\Models\User;
use App\Models\Enquiry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class BotStatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clawdbot:status 
                            {--detailed : Show detailed system information}
                            {--health : Run health checks only}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'ClawDBot: Display system status and health information';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🤖 ClawDBot System Status');
        $this->line('========================');

        $detailed = $this->option('detailed');
        $healthOnly = $this->option('health');

        try {
            if ($healthOnly) {
                $this->runHealthChecks();
                return 0;
            }

            $this->displayBasicStatus();
            
            if ($detailed) {
                $this->displayDetailedStatus();
            }

            $this->runHealthChecks();

            return 0;

        } catch (\Exception $e) {
            Log::error('ClawDBot Status Command Error: ' . $e->getMessage(), [
                'exception' => $e,
                'command' => 'clawdbot:status'
            ]);

            $this->error('❌ Status check failed: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Display basic system status
     */
    private function displayBasicStatus(): void
    {
        $this->line("\n📊 SYSTEM OVERVIEW:");
        $this->line("   Laravel Version: " . app()->version());
        $this->line("   PHP Version: " . PHP_VERSION);
        $this->line("   Environment: " . config('app.env'));
        $this->line("   Debug Mode: " . (config('app.debug') ? 'Enabled' : 'Disabled'));
        $this->line("   Timezone: " . config('app.timezone'));
        $this->line("   Current Time: " . now()->format('Y-m-d H:i:s'));

        $this->line("\n🏠 PROPERTY STATISTICS:");
        $this->line("   Total Properties: " . Property::count());
        $this->line("   Active Properties: " . Property::where('status', 'active')->count());
        $this->line("   Expired Properties: " . Property::where('status', 'expired')->count());
        $this->line("   Inactive Properties: " . Property::where('status', 'inactive')->count());

        $this->line("\n👥 USER STATISTICS:");
        $this->line("   Total Users: " . User::count());
        $this->line("   Active Users: " . User::where('status', 'active')->count());
        $this->line("   Admin Users: " . User::whereHas('roles', function ($q) {
            $q->where('name', 'admin');
        })->count());
        $this->line("   Agent Users: " . User::whereHas('roles', function ($q) {
            $q->where('name', 'agent');
        })->count());

        $this->line("\n💬 ENQUIRY STATISTICS:");
        $this->line("   Total Enquiries: " . Enquiry::count());
        $this->line("   Pending Enquiries: " . Enquiry::where('status', 'pending')->count());
        $this->line("   Responded Enquiries: " . Enquiry::where('status', 'responded')->count());
    }

    /**
     * Display detailed system status
     */
    private function displayDetailedStatus(): void
    {
        $this->line("\n🔧 DETAILED SYSTEM STATUS:");

        // Recent bot tasks
        $recentTasks = BotTask::orderBy('created_at', 'desc')->limit(10)->get();
        if ($recentTasks->isNotEmpty()) {
            $this->line("\n📋 RECENT BOT TASKS:");
            foreach ($recentTasks as $task) {
                $status = $this->formatTaskStatus($task->status);
                $duration = $task->completed_at 
                    ? $task->started_at->diffInSeconds($task->completed_at) . 's'
                    : 'Running';
                $this->line("   {$task->command} - {$status} ({$duration})");
            }
        }

        // Database information
        $this->line("\n💾 DATABASE INFORMATION:");
        $this->line("   Connection: " . config('database.default'));
        $this->line("   Database: " . config('database.connections.mysql.database'));
        
        try {
            $dbSize = DB::select("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'size' 
                                 FROM information_schema.tables 
                                 WHERE table_schema = ?", [config('database.connections.mysql.database')]);
            if (!empty($dbSize)) {
                $this->line("   Size: " . $dbSize[0]->size . " MB");
            }
        } catch (\Exception $e) {
            $this->line("   Size: Unable to determine");
        }

        // Queue information
        $this->line("\n⏳ QUEUE INFORMATION:");
        $this->line("   Queue Driver: " . config('queue.default'));
        
        if (config('queue.default') === 'database') {
            $failedJobs = DB::table('failed_jobs')->count();
            $pendingJobs = DB::table('jobs')->count();
            $this->line("   Failed Jobs: {$failedJobs}");
            $this->line("   Pending Jobs: {$pendingJobs}");
        }

        // Cache information
        $this->line("\n💾 CACHE INFORMATION:");
        $this->line("   Cache Driver: " . config('cache.default'));
        
        try {
            $cacheStore = cache()->store();
            $this->line("   Cache Store: " . get_class($cacheStore));
        } catch (\Exception $e) {
            $this->line("   Cache Store: Unable to determine");
        }

        // Memory usage
        $this->line("\n💻 MEMORY USAGE:");
        $memoryUsage = memory_get_usage(true);
        $peakMemory = memory_get_peak_usage(true);
        $this->line("   Current: " . $this->formatBytes($memoryUsage));
        $this->line("   Peak: " . $this->formatBytes($peakMemory));
    }

    /**
     * Run system health checks
     */
    private function runHealthChecks(): void
    {
        $this->line("\n🏥 SYSTEM HEALTH CHECKS:");

        $checks = [
            'Database Connection' => $this->checkDatabase(),
            'Cache System' => $this->checkCache(),
            'Queue System' => $this->checkQueue(),
            'File System' => $this->checkFileSystem(),
            'Email Configuration' => $this->checkEmail(),
            'Bot Tasks Table' => $this->checkBotTasksTable(),
        ];

        $allPassed = true;

        foreach ($checks as $check => $result) {
            $status = $result ? '✅ PASS' : '❌ FAIL';
            $this->line("   {$check}: {$status}");
            if (!$result) {
                $allPassed = false;
            }
        }

        if ($allPassed) {
            $this->line("\n🎉 All health checks passed!");
        } else {
            $this->line("\n⚠️  Some health checks failed. Please review the system.");
        }
    }

    /**
     * Check database connection
     */
    private function checkDatabase(): bool
    {
        try {
            DB::select('SELECT 1');
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check cache system
     */
    private function checkCache(): bool
    {
        try {
            $testKey = 'clawdbot_health_check_' . time();
            cache()->put($testKey, 'test', 60);
            $result = cache()->get($testKey) === 'test';
            cache()->forget($testKey);
            return $result;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check queue system
     */
    private function checkQueue(): bool
    {
        try {
            // This is a basic check - you might want to add more specific checks
            return in_array(config('queue.default'), ['sync', 'database', 'redis']);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check file system
     */
    private function checkFileSystem(): bool
    {
        try {
            $testFile = storage_path('app/clawdbot_health_check.txt');
            file_put_contents($testFile, 'test');
            $result = file_exists($testFile);
            if ($result) {
                unlink($testFile);
            }
            return $result;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check email configuration
     */
    private function checkEmail(): bool
    {
        try {
            // Basic check - ensure mail config is set
            return !empty(config('mail.default')) && 
                   !empty(config('mail.mailers.smtp.host')) &&
                   !empty(config('mail.from.address'));
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check bot tasks table
     */
    private function checkBotTasksTable(): bool
    {
        try {
            return DB::table('bot_tasks')->exists();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Format task status for display
     */
    private function formatTaskStatus(string $status): string
    {
        return match($status) {
            'running' => '🔄 Running',
            'completed' => '✅ Completed',
            'failed' => '❌ Failed',
            default => '❓ Unknown'
        };
    }

    /**
     * Format bytes for display
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
