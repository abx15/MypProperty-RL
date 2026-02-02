<?php

namespace App\Services\ClawDBot;

use App\Models\Property;
use App\Models\User;
use App\Models\Enquiry;
use App\Models\ClawDBot\BotTask;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsService
{
    /**
     * Get comprehensive dashboard analytics
     */
    public function getDashboardAnalytics(): array
    {
        try {
            return [
                'properties' => $this->getPropertyAnalytics(),
                'users' => $this->getUserAnalytics(),
                'enquiries' => $this->getEnquiryAnalytics(),
                'system' => $this->getSystemAnalytics(),
                'trends' => $this->getTrendAnalytics()
            ];

        } catch (\Exception $e) {
            Log::error('AnalyticsService: Failed to get dashboard analytics', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Get property analytics
     */
    public function getPropertyAnalytics(): array
    {
        try {
            $now = now();
            $lastMonth = $now->copy()->subMonth();
            $lastWeek = $now->copy()->subWeek();

            return [
                'total' => Property::count(),
                'active' => Property::where('status', 'active')->count(),
                'expired' => Property::where('status', 'expired')->count(),
                'inactive' => Property::where('status', 'inactive')->count(),
                'this_month' => Property::whereBetween('created_at', [$lastMonth, $now])->count(),
                'this_week' => Property::whereBetween('created_at', [$lastWeek, $now])->count(),
                'today' => Property::whereDate('created_at', $now->toDateString())->count(),
                'expiring_soon' => Property::where('status', 'active')
                    ->where('expires_at', '>=', $now)
                    ->where('expires_at', '<=', $now->copy()->addDays(7))
                    ->count(),
                'average_price' => Property::avg('price'),
                'total_value' => Property::sum('price'),
                'by_category' => $this->getPropertiesByCategory(),
                'by_location' => $this->getPropertiesByLocation(),
                'price_distribution' => $this->getPriceDistribution()
            ];

        } catch (\Exception $e) {
            Log::error('AnalyticsService: Failed to get property analytics', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Get user analytics
     */
    public function getUserAnalytics(): array
    {
        try {
            $now = now();
            $lastMonth = $now->copy()->subMonth();
            $lastWeek = $now->copy()->subWeek();

            return [
                'total' => User::count(),
                'active' => User::where('status', 'active')->count(),
                'this_month' => User::whereBetween('created_at', [$lastMonth, $now])->count(),
                'this_week' => User::whereBetween('created_at', [$lastWeek, $now])->count(),
                'today' => User::whereDate('created_at', $now->toDateString())->count(),
                'by_role' => $this->getUsersByRole(),
                'by_status' => $this->getUsersByStatus(),
                'registration_trends' => $this->getRegistrationTrends()
            ];

        } catch (\Exception $e) {
            Log::error('AnalyticsService: Failed to get user analytics', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Get enquiry analytics
     */
    public function getEnquiryAnalytics(): array
    {
        try {
            $now = now();
            $lastMonth = $now->copy()->subMonth();
            $lastWeek = $now->copy()->subWeek();

            $enquiries = Enquiry::all();

            return [
                'total' => $enquiries->count(),
                'pending' => $enquiries->where('status', 'pending')->count(),
                'responded' => $enquiries->where('status', 'responded')->count(),
                'closed' => $enquiries->where('status', 'closed')->count(),
                'this_month' => Enquiry::whereBetween('created_at', [$lastMonth, $now])->count(),
                'this_week' => Enquiry::whereBetween('created_at', [$lastWeek, $now])->count(),
                'today' => Enquiry::whereDate('created_at', $now->toDateString())->count(),
                'response_rate' => $this->calculateResponseRate(),
                'average_response_time' => $this->calculateAverageResponseTime(),
                'by_property' => $this->getEnquiriesByProperty(),
                'by_status' => $this->getEnquiriesByStatus()
            ];

        } catch (\Exception $e) {
            Log::error('AnalyticsService: Failed to get enquiry analytics', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Get system analytics
     */
    public function getSystemAnalytics(): array
    {
        try {
            return [
                'bot_tasks' => $this->getBotTaskAnalytics(),
                'queue_health' => $this->getQueueHealth(),
                'database_size' => $this->getDatabaseSize(),
                'storage_usage' => $this->getStorageUsage(),
                'system_uptime' => $this->getSystemUptime(),
                'error_rate' => $this->getErrorRate()
            ];

        } catch (\Exception $e) {
            Log::error('AnalyticsService: Failed to get system analytics', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Get trend analytics
     */
    public function getTrendAnalytics(): array
    {
        try {
            return [
                'property_trends' => $this->getPropertyTrends(),
                'user_trends' => $this->getUserTrends(),
                'enquiry_trends' => $this->getEnquiryTrends(),
                'price_trends' => $this->getPriceTrends(),
                'location_trends' => $this->getLocationTrends()
            ];

        } catch (\Exception $e) {
            Log::error('AnalyticsService: Failed to get trend analytics', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Get properties by category
     */
    private function getPropertiesByCategory(): array
    {
        return Property::selectRaw('category, COUNT(*) as count')
            ->groupBy('category')
            ->orderByDesc('count')
            ->pluck('count', 'category')
            ->toArray();
    }

    /**
     * Get properties by location
     */
    private function getPropertiesByLocation(): array
    {
        return Property::with('location')
            ->selectRaw('location_id, COUNT(*) as count')
            ->groupBy('location_id')
            ->orderByDesc('count')
            ->limit(10)
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->location ? $item->location->name : 'Unknown' => $item->count];
            })
            ->toArray();
    }

    /**
     * Get price distribution
     */
    private function getPriceDistribution(): array
    {
        $ranges = [
            '0-100k' => [0, 100000],
            '100k-250k' => [100000, 250000],
            '250k-500k' => [250000, 500000],
            '500k-1m' => [500000, 1000000],
            '1m+' => [1000000, PHP_INT_MAX]
        ];

        $distribution = [];

        foreach ($ranges as $label => [$min, $max]) {
            $distribution[$label] = Property::where('price', '>=', $min)
                ->where('price', '<', $max)
                ->count();
        }

        return $distribution;
    }

    /**
     * Get users by role
     */
    private function getUsersByRole(): array
    {
        return User::join('model_has_roles', 'users.id', 'model_has_roles.model_id')
            ->join('roles', 'model_has_roles.role_id', 'roles.id')
            ->selectRaw('roles.name, COUNT(*) as count')
            ->groupBy('roles.name')
            ->orderByDesc('count')
            ->pluck('count', 'roles.name')
            ->toArray();
    }

    /**
     * Get users by status
     */
    private function getUsersByStatus(): array
    {
        return User::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->orderByDesc('count')
            ->pluck('count', 'status')
            ->toArray();
    }

    /**
     * Get registration trends
     */
    private function getRegistrationTrends(): array
    {
        $trends = [];
        $days = 30;

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $trends[$date->format('Y-m-d')] = User::whereDate('created_at', $date->toDateString())->count();
        }

        return $trends;
    }

    /**
     * Calculate response rate
     */
    private function calculateResponseRate(): float
    {
        $total = Enquiry::count();
        $responded = Enquiry::where('status', 'responded')->count();

        return $total > 0 ? round(($responded / $total) * 100, 2) : 0;
    }

    /**
     * Calculate average response time
     */
    private function calculateAverageResponseTime(): float
    {
        // This would require tracking response times in your enquiries table
        // For now, return placeholder
        return 2.5; // hours
    }

    /**
     * Get enquiries by property
     */
    private function getEnquiriesByProperty(): array
    {
        return Enquiry::with('property')
            ->selectRaw('property_id, COUNT(*) as count')
            ->groupBy('property_id')
            ->orderByDesc('count')
            ->limit(10)
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->property ? $item->property->title : 'Unknown' => $item->count];
            })
            ->toArray();
    }

    /**
     * Get enquiries by status
     */
    private function getEnquiriesByStatus(): array
    {
        return Enquiry::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->orderByDesc('count')
            ->pluck('count', 'status')
            ->toArray();
    }

    /**
     * Get bot task analytics
     */
    private function getBotTaskAnalytics(): array
    {
        $tasks = BotTask::all();

        return [
            'total' => $tasks->count(),
            'completed' => $tasks->where('status', 'completed')->count(),
            'failed' => $tasks->where('status', 'failed')->count(),
            'running' => $tasks->where('status', 'running')->count(),
            'success_rate' => $tasks->count() > 0 
                ? round(($tasks->where('status', 'completed')->count() / $tasks->count()) * 100, 2) 
                : 0,
            'average_execution_time' => $tasks->where('status', 'completed')->avg('execution_time')
        ];
    }

    /**
     * Get queue health
     */
    private function getQueueHealth(): array
    {
        try {
            $failedJobs = DB::table('failed_jobs')->count();
            $pendingJobs = DB::table('jobs')->count();

            return [
                'failed_jobs' => $failedJobs,
                'pending_jobs' => $pendingJobs,
                'health_status' => $failedJobs === 0 ? 'healthy' : 'warning'
            ];

        } catch (\Exception $e) {
            return [
                'failed_jobs' => 0,
                'pending_jobs' => 0,
                'health_status' => 'unknown'
            ];
        }
    }

    /**
     * Get database size
     */
    private function getDatabaseSize(): string
    {
        try {
            $size = DB::select("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'size' 
                              FROM information_schema.tables 
                              WHERE table_schema = ?", [config('database.connections.mysql.database')]);
            
            return !empty($size) ? $size[0]->size . ' MB' : 'Unknown';
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }

    /**
     * Get storage usage
     */
    private function getStorageUsage(): array
    {
        try {
            $total = disk_total_space(storage_path());
            $free = disk_free_space(storage_path());
            $used = $total - $free;

            return [
                'total' => $this->formatBytes($total),
                'used' => $this->formatBytes($used),
                'free' => $this->formatBytes($free),
                'usage_percentage' => $total > 0 ? round(($used / $total) * 100, 2) : 0
            ];

        } catch (\Exception $e) {
            return [
                'total' => 'Unknown',
                'used' => 'Unknown',
                'free' => 'Unknown',
                'usage_percentage' => 0
            ];
        }
    }

    /**
     * Get system uptime
     */
    private function getSystemUptime(): string
    {
        // This would require system monitoring - placeholder for now
        return '99.9%';
    }

    /**
     * Get error rate
     */
    private function getErrorRate(): float
    {
        // This would require error tracking - placeholder for now
        return 0.1;
    }

    /**
     * Get property trends
     */
    private function getPropertyTrends(): array
    {
        return $this->getDailyTrends('properties', 'created_at');
    }

    /**
     * Get user trends
     */
    private function getUserTrends(): array
    {
        return $this->getDailyTrends('users', 'created_at');
    }

    /**
     * Get enquiry trends
     */
    private function getEnquiryTrends(): array
    {
        return $this->getDailyTrends('enquiries', 'created_at');
    }

    /**
     * Get price trends
     */
    private function getPriceTrends(): array
    {
        // This would require price history tracking - placeholder
        return [];
    }

    /**
     * Get location trends
     */
    private function getLocationTrends(): array
    {
        return $this->getMonthlyTrends('properties', 'location_id');
    }

    /**
     * Get daily trends for a table
     */
    private function getDailyTrends(string $table, string $dateColumn): array
    {
        $trends = [];
        $days = 30;

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $count = DB::table($table)->whereDate($dateColumn, $date->toDateString())->count();
            $trends[$date->format('Y-m-d')] = $count;
        }

        return $trends;
    }

    /**
     * Get monthly trends for a table
     */
    private function getMonthlyTrends(string $table, string $column): array
    {
        $trends = [];
        $months = 12;

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $count = DB::table($table)
                ->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->count();
            $trends[$date->format('Y-m')] = $count;
        }

        return $trends;
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
