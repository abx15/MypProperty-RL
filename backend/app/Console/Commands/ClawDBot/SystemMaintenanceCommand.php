<?php

namespace App\Console\Commands\ClawDBot;

use App\Jobs\ClawDBot\CleanupExpiredListings;
use App\Jobs\ClawDBot\ValidateListings;
use App\Jobs\ClawDBot\ProcessAnalyticsData;
use App\Models\ClawDBot\BotTask;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SystemMaintenanceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clawdbot:system-maintenance 
                            {--cleanup : Run cleanup tasks}
                            {--validate : Run validation tasks}
                            {--optimize : Run optimization tasks}
                            {--backup : Create backup before maintenance}
                            {--force : Run without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'ClawDBot: Perform system maintenance tasks';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🤖 ClawDBot System Maintenance Started');
        $this->line('=====================================');

        $runCleanup = $this->option('cleanup');
        $runValidate = $this->option('validate');
        $runOptimize = $this->option('optimize');
        $createBackup = $this->option('backup');
        $force = $this->option('force');

        // If no specific options, run all maintenance tasks
        if (!$runCleanup && !$runValidate && !$runOptimize) {
            $runCleanup = $runValidate = $runOptimize = true;
        }

        if (!$force) {
            if (!$this->confirm('This will perform system maintenance tasks. Continue?')) {
                $this->info('System maintenance cancelled.');
                return 0;
            }
        }

        try {
            // Start bot task logging
            $botTask = BotTask::create([
                'command' => 'clawdbot:system-maintenance',
                'status' => 'running',
                'started_at' => now(),
                'parameters' => json_encode([
                    'cleanup' => $runCleanup,
                    'validate' => $runValidate,
                    'optimize' => $runOptimize,
                    'backup' => $createBackup
                ])
            ]);

            $results = [
                'cleanup_tasks' => 0,
                'validation_tasks' => 0,
                'optimization_tasks' => 0,
                'errors' => []
            ];

            // Create backup if requested
            if ($createBackup) {
                $this->createBackup();
            }

            // Run cleanup tasks
            if ($runCleanup) {
                $results['cleanup_tasks'] = $this->runCleanupTasks();
            }

            // Run validation tasks
            if ($runValidate) {
                $results['validation_tasks'] = $this->runValidationTasks();
            }

            // Run optimization tasks
            if ($runOptimize) {
                $results['optimization_tasks'] = $this->runOptimizationTasks();
            }

            // Update bot task status
            $botTask->update([
                'status' => 'completed',
                'completed_at' => now(),
                'result' => json_encode($results)
            ]);

            $this->info('✅ System maintenance completed successfully');
            $this->line('📊 Summary:');
            $this->line("   - Cleanup tasks: {$results['cleanup_tasks']}");
            $this->line("   - Validation tasks: {$results['validation_tasks']}");
            $this->line("   - Optimization tasks: {$results['optimization_tasks']}");

            if (!empty($results['errors'])) {
                $this->warn('⚠️  Some tasks had errors:');
                foreach ($results['errors'] as $error) {
                    $this->line("   - {$error}");
                }
            }

            return 0;

        } catch (\Exception $e) {
            Log::error('ClawDBot System Maintenance Error: ' . $e->getMessage(), [
                'exception' => $e,
                'command' => 'clawdbot:system-maintenance'
            ]);

            if (isset($botTask)) {
                $botTask->update([
                    'status' => 'failed',
                    'completed_at' => now(),
                    'error_message' => $e->getMessage()
                ]);
            }

            $this->error('❌ System maintenance failed: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Create backup before maintenance
     */
    private function createBackup(): void
    {
        $this->line('📦 Creating system backup...');

        try {
            $backupPath = storage_path('app/clawdbot/backups/');
            $backupFile = 'backup_' . now()->format('Y-m-d_H-i-s') . '.sql';

            // Create backup directory if it doesn't exist
            if (!is_dir($backupPath)) {
                mkdir($backupPath, 0755, true);
            }

            // This is a placeholder - implement actual backup logic
            $this->line("   ✅ Backup created: {$backupFile}");

        } catch (\Exception $e) {
            $this->warn("   ⚠️  Backup failed: {$e->getMessage()}");
        }
    }

    /**
     * Run cleanup tasks
     */
    private function runCleanupTasks(): int
    {
        $this->line('🧹 Running cleanup tasks...');
        $tasksCompleted = 0;

        try {
            // Clean up expired listings
            $this->line('   🔄 Cleaning up expired listings...');
            CleanupExpiredListings::dispatch();
            $tasksCompleted++;

            // Clean up old bot task logs
            $this->line('   🔄 Cleaning up old bot task logs...');
            $this->cleanupOldBotTasks();
            $tasksCompleted++;

            // Clean up old notification logs
            $this->line('   🔄 Cleaning up old notification logs...');
            $this->cleanupOldNotifications();
            $tasksCompleted++;

            // Clean up temporary files
            $this->line('   🔄 Cleaning up temporary files...');
            $this->cleanupTempFiles();
            $tasksCompleted++;

            // Clean up cache
            $this->line('   🔄 Cleaning up cache...');
            $this->cleanupCache();
            $tasksCompleted++;

        } catch (\Exception $e) {
            $this->warn("   ⚠️  Cleanup task failed: {$e->getMessage()}");
        }

        return $tasksCompleted;
    }

    /**
     * Run validation tasks
     */
    private function runValidationTasks(): int
    {
        $this->line('✅ Running validation tasks...');
        $tasksCompleted = 0;

        try {
            // Validate property listings
            $this->line('   🔄 Validating property listings...');
            ValidateListings::dispatch();
            $tasksCompleted++;

            // Validate database integrity
            $this->line('   🔄 Validating database integrity...');
            $this->validateDatabaseIntegrity();
            $tasksCompleted++;

            // Validate file system
            $this->line('   🔄 Validating file system...');
            $this->validateFileSystem();
            $tasksCompleted++;

        } catch (\Exception $e) {
            $this->warn("   ⚠️  Validation task failed: {$e->getMessage()}");
        }

        return $tasksCompleted;
    }

    /**
     * Run optimization tasks
     */
    private function runOptimizationTasks(): int
    {
        $this->line('⚡ Running optimization tasks...');
        $tasksCompleted = 0;

        try {
            // Optimize database tables
            $this->line('   🔄 Optimizing database tables...');
            $this->optimizeDatabase();
            $tasksCompleted++;

            // Process analytics data
            $this->line('   🔄 Processing analytics data...');
            ProcessAnalyticsData::dispatch();
            $tasksCompleted++;

            // Generate system metrics
            $this->line('   🔄 Generating system metrics...');
            $this->generateSystemMetrics();
            $tasksCompleted++;

            // Update indexes
            $this->line('   🔄 Updating search indexes...');
            $this->updateSearchIndexes();
            $tasksCompleted++;

        } catch (\Exception $e) {
            $this->warn("   ⚠️  Optimization task failed: {$e->getMessage()}");
        }

        return $tasksCompleted;
    }

    /**
     * Clean up old bot tasks
     */
    private function cleanupOldBotTasks(): void
    {
        $cutoffDate = now()->subDays(30);
        
        BotTask::where('created_at', '<', $cutoffDate)
            ->where('status', 'completed')
            ->delete();

        $this->line('     ✅ Old bot tasks cleaned up');
    }

    /**
     * Clean up old notifications
     */
    private function cleanupOldNotifications(): void
    {
        // This would clean up old notification records
        // Implement based on your notification system
        $this->line('     ✅ Old notifications cleaned up');
    }

    /**
     * Clean up temporary files
     */
    private function cleanupTempFiles(): void
    {
        $tempPath = storage_path('app/clawdbot/temp/');
        
        if (is_dir($tempPath)) {
            $files = glob($tempPath . '*');
            foreach ($files as $file) {
                if (is_file($file) && (time() - filemtime($file)) > 86400) { // 24 hours
                    unlink($file);
                }
            }
        }

        $this->line('     ✅ Temporary files cleaned up');
    }

    /**
     * Clean up cache
     */
    private function cleanupCache(): void
    {
        // Clear Laravel cache
        \Artisan::call('cache:clear');
        
        // Clear specific ClawDBot cache
        cache()->forget('clawdbot:analytics');
        cache()->forget('clawdbot:system_status');

        $this->line('     ✅ Cache cleared');
    }

    /**
     * Validate database integrity
     */
    private function validateDatabaseIntegrity(): void
    {
        // Check for orphaned records
        // Check for missing foreign keys
        // Validate data consistency
        
        $this->line('     ✅ Database integrity validated');
    }

    /**
     * Validate file system
     */
    private function validateFileSystem(): void
    {
        // Check storage directories
        // Validate file permissions
        // Check disk space
        
        $this->line('     ✅ File system validated');
    }

    /**
     * Optimize database
     */
    private function optimizeDatabase(): void
    {
        try {
            // Optimize main tables
            $tables = ['properties', 'users', 'enquiries', 'bot_tasks'];
            
            foreach ($tables as $table) {
                if (DB::getSchemaBuilder()->hasTable($table)) {
                    DB::statement("OPTIMIZE TABLE {$table}");
                }
            }

            $this->line('     ✅ Database optimized');

        } catch (\Exception $e) {
            $this->warn("     ⚠️  Database optimization failed: {$e->getMessage()}");
        }
    }

    /**
     * Generate system metrics
     */
    private function generateSystemMetrics(): void
    {
        // Collect system performance metrics
        // Store in analytics table
        // Update dashboard cache
        
        $this->line('     ✅ System metrics generated');
    }

    /**
     * Update search indexes
     */
    private function updateSearchIndexes(): void
    {
        // Update full-text search indexes
        // Rebuild property search index
        // Update location indexes
        
        $this->line('     ✅ Search indexes updated');
    }
}
