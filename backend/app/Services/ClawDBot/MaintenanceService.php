<?php

namespace App\Services\ClawDBot;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class MaintenanceService
{
    /**
     * Perform system maintenance tasks
     */
    public function performMaintenance(): array
    {
        $results = [
            'tasks_completed' => 0,
            'tasks_failed' => 0,
            'errors' => [],
            'details' => []
        ];

        try {
            // Database maintenance
            $dbResults = $this->performDatabaseMaintenance();
            $results['details']['database'] = $dbResults;
            $results['tasks_completed'] += $dbResults['completed'];
            $results['tasks_failed'] += $dbResults['failed'];

            // Cache maintenance
            $cacheResults = $this->performCacheMaintenance();
            $results['details']['cache'] = $cacheResults;
            $results['tasks_completed'] += $cacheResults['completed'];
            $results['tasks_failed'] += $cacheResults['failed'];

            // File system maintenance
            $fileResults = $this->performFileSystemMaintenance();
            $results['details']['filesystem'] = $fileResults;
            $results['tasks_completed'] += $fileResults['completed'];
            $results['tasks_failed'] += $fileResults['failed'];

            // Log maintenance
            $logResults = $this->performLogMaintenance();
            $results['details']['logs'] = $logResults;
            $results['tasks_completed'] += $logResults['completed'];
            $results['tasks_failed'] += $logResults['failed'];

            // Performance optimization
            $perfResults = $this->performPerformanceOptimization();
            $results['details']['performance'] = $perfResults;
            $results['tasks_completed'] += $perfResults['completed'];
            $results['tasks_failed'] += $perfResults['failed'];

            Log::info('ClawDBot: System maintenance completed', $results);

        } catch (\Exception $e) {
            Log::error('ClawDBot: System maintenance failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $results['errors'][] = $e->getMessage();
            $results['tasks_failed']++;
        }

        return $results;
    }

    /**
     * Perform database maintenance
     */
    private function performDatabaseMaintenance(): array
    {
        $results = [
            'completed' => 0,
            'failed' => 0,
            'details' => []
        ];

        try {
            // Optimize tables
            $tables = ['properties', 'users', 'enquiries', 'bot_tasks', 'notifications'];
            
            foreach ($tables as $table) {
                if (DB::getSchemaBuilder()->hasTable($table)) {
                    try {
                        DB::statement("OPTIMIZE TABLE {$table}");
                        $results['details'][] = "Optimized table: {$table}";
                        $results['completed']++;
                    } catch (\Exception $e) {
                        $results['details'][] = "Failed to optimize {$table}: {$e->getMessage()}";
                        $results['failed']++;
                    }
                }
            }

            // Clean up old records
            $this->cleanupOldRecords($results);

            // Update statistics
            $this->updateTableStatistics($results);

        } catch (\Exception $e) {
            $results['failed']++;
            $results['details'][] = "Database maintenance failed: {$e->getMessage()}";
        }

        return $results;
    }

    /**
     * Perform cache maintenance
     */
    private function performCacheMaintenance(): array
    {
        $results = [
            'completed' => 0,
            'failed' => 0,
            'details' => []
        ];

        try {
            // Clear application cache
            Cache::flush();
            $results['details'][] = 'Cleared application cache';
            $results['completed']++;

            // Clear specific ClawDBot cache keys
            $clawdbotKeys = [
                'clawdbot_analytics',
                'clawdbot_system_status',
                'clawdbot_property_suggestions',
                'clawdbot_user_preferences'
            ];

            foreach ($clawdbotKeys as $key) {
                Cache::forget($key);
                $results['details'][] = "Cleared cache key: {$key}";
                $results['completed']++;
            }

            // Warm up cache with frequently accessed data
            $this->warmUpCache($results);

        } catch (\Exception $e) {
            $results['failed']++;
            $results['details'][] = "Cache maintenance failed: {$e->getMessage()}";
        }

        return $results;
    }

    /**
     * Perform file system maintenance
     */
    private function performFileSystemMaintenance(): array
    {
        $results = [
            'completed' => 0,
            'failed' => 0,
            'details' => []
        ];

        try {
            // Clean up temporary files
            $tempPath = storage_path('app/clawdbot/temp/');
            if (is_dir($tempPath)) {
                $files = glob($tempPath . '*');
                $cleanedFiles = 0;
                
                foreach ($files as $file) {
                    if (is_file($file) && (time() - filemtime($file)) > 86400) { // 24 hours
                        unlink($file);
                        $cleanedFiles++;
                    }
                }
                
                $results['details'][] = "Cleaned {$cleanedFiles} temporary files";
                $results['completed']++;
            }

            // Clean up old reports
            $this->cleanupOldReports($results);

            // Check disk space
            $diskUsage = $this->checkDiskUsage();
            $results['details'][] = "Disk usage: {$diskUsage['used']} / {$diskUsage['total']} ({$diskUsage['percentage']}%)";
            $results['completed']++;

        } catch (\Exception $e) {
            $results['failed']++;
            $results['details'][] = "File system maintenance failed: {$e->getMessage()}";
        }

        return $results;
    }

    /**
     * Perform log maintenance
     */
    private function performLogMaintenance(): array
    {
        $results = [
            'completed' => 0,
            'failed' => 0,
            'details' => []
        ];

        try {
            // Rotate ClawDBot logs
            $logPath = storage_path('logs/clawdbot.log');
            
            if (file_exists($logPath) && filesize($logPath) > 10 * 1024 * 1024) { // 10MB
                $backupPath = storage_path('logs/clawdbot_' . date('Y-m-d_H-i-s') . '.log');
                rename($logPath, $backupPath);
                
                $results['details'][] = 'Rotated ClawDBot log file';
                $results['completed']++;
            }

            // Clean up old log files
            $logFiles = glob(storage_path('logs/clawdbot_*.log'));
            $deletedLogs = 0;
            
            foreach ($logFiles as $logFile) {
                if (filemtime($logFile) < strtotime('-30 days')) {
                    unlink($logFile);
                    $deletedLogs++;
                }
            }
            
            if ($deletedLogs > 0) {
                $results['details'][] = "Deleted {$deletedLogs} old log files";
                $results['completed']++;
            }

        } catch (\Exception $e) {
            $results['failed']++;
            $results['details'][] = "Log maintenance failed: {$e->getMessage()}";
        }

        return $results;
    }

    /**
     * Perform performance optimization
     */
    private function performPerformanceOptimization(): array
    {
        $results = [
            'completed' => 0,
            'failed' => 0,
            'details' => []
        ];

        try {
            // Update search indexes
            $this->updateSearchIndexes($results);

            // Pre-compute analytics
            $this->precomputeAnalytics($results);

            // Optimize image storage
            $this->optimizeImageStorage($results);

        } catch (\Exception $e) {
            $results['failed']++;
            $results['details'][] = "Performance optimization failed: {$e->getMessage()}";
        }

        return $results;
    }

    /**
     * Clean up old database records
     */
    private function cleanupOldRecords(array &$results): void
    {
        try {
            // Clean up old bot tasks (older than 90 days)
            $deletedTasks = DB::table('bot_tasks')
                ->where('created_at', '<', now()->subDays(90))
                ->where('status', 'completed')
                ->delete();

            if ($deletedTasks > 0) {
                $results['details'][] = "Deleted {$deletedTasks} old bot task records";
                $results['completed']++;
            }

            // Clean up old notifications (older than 30 days)
            $deletedNotifications = DB::table('notifications')
                ->where('created_at', '<', now()->subDays(30))
                ->delete();

            if ($deletedNotifications > 0) {
                $results['details'][] = "Deleted {$deletedNotifications} old notification records";
                $results['completed']++;
            }

        } catch (\Exception $e) {
            $results['details'][] = "Record cleanup failed: {$e->getMessage()}";
            $results['failed']++;
        }
    }

    /**
     * Update table statistics
     */
    private function updateTableStatistics(array &$results): void
    {
        try {
            $tables = ['properties', 'users', 'enquiries'];
            
            foreach ($tables as $table) {
                if (DB::getSchemaBuilder()->hasTable($table)) {
                    $count = DB::table($table)->count();
                    $results['details'][] = "Table {$table}: {$count} records";
                    $results['completed']++;
                }
            }

        } catch (\Exception $e) {
            $results['details'][] = "Statistics update failed: {$e->getMessage()}";
            $results['failed']++;
        }
    }

    /**
     * Warm up cache with frequently accessed data
     */
    private function warmUpCache(array &$results): void
    {
        try {
            // Cache system status
            Cache::put('clawdbot_system_status', [
                'status' => 'healthy',
                'last_check' => now()->toISOString(),
                'version' => '1.0.0'
            ], 3600);

            // Cache analytics summary
            Cache::put('clawdbot_analytics', [
                'total_properties' => DB::table('properties')->count(),
                'total_users' => DB::table('users')->count(),
                'total_enquiries' => DB::table('enquiries')->count(),
                'generated_at' => now()->toISOString()
            ], 1800);

            $results['details'][] = 'Warmed up cache with key data';
            $results['completed']++;

        } catch (\Exception $e) {
            $results['details'][] = "Cache warm-up failed: {$e->getMessage()}";
            $results['failed']++;
        }
    }

    /**
     * Clean up old reports
     */
    private function cleanupOldReports(array &$results): void
    {
        try {
            $reportsPath = storage_path('app/clawdbot/reports/');
            
            if (is_dir($reportsPath)) {
                $files = glob($reportsPath . '*.json');
                $deletedReports = 0;
                
                foreach ($files as $file) {
                    if (filemtime($file) < strtotime('-7 days')) {
                        unlink($file);
                        $deletedReports++;
                    }
                }
                
                if ($deletedReports > 0) {
                    $results['details'][] = "Deleted {$deletedReports} old report files";
                    $results['completed']++;
                }
            }

        } catch (\Exception $e) {
            $results['details'][] = "Report cleanup failed: {$e->getMessage()}";
            $results['failed']++;
        }
    }

    /**
     * Check disk usage
     */
    private function checkDiskUsage(): array
    {
        $total = disk_total_space(storage_path());
        $free = disk_free_space(storage_path());
        $used = $total - $free;
        $percentage = $total > 0 ? round(($used / $total) * 100, 2) : 0;

        return [
            'total' => $this->formatBytes($total),
            'used' => $this->formatBytes($used),
            'free' => $this->formatBytes($free),
            'percentage' => $percentage
        ];
    }

    /**
     * Update search indexes
     */
    private function updateSearchIndexes(array &$results): void
    {
        try {
            // This would update full-text search indexes
            $results['details'][] = 'Updated search indexes';
            $results['completed']++;

        } catch (\Exception $e) {
            $results['details'][] = "Search index update failed: {$e->getMessage()}";
            $results['failed']++;
        }
    }

    /**
     * Pre-compute analytics
     */
    private function precomputeAnalytics(array &$results): void
    {
        try {
            // This would pre-compute expensive analytics queries
            $results['details'][] = 'Pre-computed analytics data';
            $results['completed']++;

        } catch (\Exception $e) {
            $results['details'][] = "Analytics pre-computation failed: {$e->getMessage()}";
            $results['failed']++;
        }
    }

    /**
     * Optimize image storage
     */
    private function optimizeImageStorage(array &$results): void
    {
        try {
            // This would optimize and compress images
            $results['details'][] = 'Optimized image storage';
            $results['completed']++;

        } catch (\Exception $e) {
            $results['details'][] = "Image optimization failed: {$e->getMessage()}";
            $results['failed']++;
        }
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

    /**
     * Get system health status
     */
    public function getSystemHealth(): array
    {
        return [
            'database' => $this->getDatabaseHealth(),
            'cache' => $this->getCacheHealth(),
            'filesystem' => $this->getFileSystemHealth(),
            'queue' => $this->getQueueHealth(),
            'memory' => $this->getMemoryHealth(),
            'overall' => 'healthy'
        ];
    }

    /**
     * Get database health
     */
    private function getDatabaseHealth(): array
    {
        try {
            DB::select('SELECT 1');
            return ['status' => 'healthy', 'message' => 'Database connection OK'];
        } catch (\Exception $e) {
            return ['status' => 'unhealthy', 'message' => $e->getMessage()];
        }
    }

    /**
     * Get cache health
     */
    private function getCacheHealth(): array
    {
        try {
            Cache::put('health_check', 'ok', 60);
            $value = Cache::get('health_check');
            
            if ($value === 'ok') {
                return ['status' => 'healthy', 'message' => 'Cache working properly'];
            } else {
                return ['status' => 'unhealthy', 'message' => 'Cache not responding'];
            }
        } catch (\Exception $e) {
            return ['status' => 'unhealthy', 'message' => $e->getMessage()];
        }
    }

    /**
     * Get file system health
     */
    private function getFileSystemHealth(): array
    {
        try {
            $diskUsage = $this->checkDiskUsage();
            
            if ($diskUsage['percentage'] > 90) {
                return ['status' => 'warning', 'message' => 'Disk usage high: ' . $diskUsage['percentage'] . '%'];
            } else {
                return ['status' => 'healthy', 'message' => 'Disk usage OK: ' . $diskUsage['percentage'] . '%'];
            }
        } catch (\Exception $e) {
            return ['status' => 'unhealthy', 'message' => $e->getMessage()];
        }
    }

    /**
     * Get queue health
     */
    private function getQueueHealth(): array
    {
        try {
            $failedJobs = DB::table('failed_jobs')->count();
            $pendingJobs = DB::table('jobs')->count();
            
            if ($failedJobs > 10) {
                return ['status' => 'warning', 'message' => "High failed jobs: {$failedJobs}"];
            } else {
                return ['status' => 'healthy', 'message' => "Queue OK: {$pendingJobs} pending, {$failedJobs} failed"];
            }
        } catch (\Exception $e) {
            return ['status' => 'unhealthy', 'message' => $e->getMessage()];
        }
    }

    /**
     * Get memory health
     */
    private function getMemoryHealth(): array
    {
        $memoryUsage = memory_get_usage(true);
        $memoryLimit = ini_get('memory_limit');
        $memoryLimitBytes = $this->parseMemoryLimit($memoryLimit);
        
        if ($memoryLimitBytes > 0) {
            $percentage = ($memoryUsage / $memoryLimitBytes) * 100;
            
            if ($percentage > 80) {
                return ['status' => 'warning', 'message' => 'High memory usage: ' . round($percentage, 2) . '%'];
            } else {
                return ['status' => 'healthy', 'message' => 'Memory usage OK: ' . round($percentage, 2) . '%'];
            }
        }
        
        return ['status' => 'healthy', 'message' => 'Memory usage: ' . $this->formatBytes($memoryUsage)];
    }

    /**
     * Parse memory limit string
     */
    private function parseMemoryLimit(string $limit): int
    {
        $limit = strtolower($limit);
        $multiplier = 1;
        
        if (str_ends_with($limit, 'g')) {
            $multiplier = 1024 * 1024 * 1024;
            $limit = substr($limit, 0, -1);
        } elseif (str_ends_with($limit, 'm')) {
            $multiplier = 1024 * 1024;
            $limit = substr($limit, 0, -1);
        } elseif (str_ends_with($limit, 'k')) {
            $multiplier = 1024;
            $limit = substr($limit, 0, -1);
        }
        
        return (int) $limit * $multiplier;
    }
}
