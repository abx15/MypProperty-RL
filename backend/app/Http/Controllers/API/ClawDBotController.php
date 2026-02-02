<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ClawDBot\BotTask;
use App\Models\ClawDBot\BotAnalytics;
use App\Models\ClawDBot\BotNotification;
use App\Services\ClawDBot\AnalyticsService;
use App\Services\ClawDBot\MaintenanceService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class ClawDBotController extends Controller
{
    /**
     * Get bot status and health information
     */
    public function status(): JsonResponse
    {
        try {
            $maintenanceService = app(MaintenanceService::class);
            $analyticsService = app(AnalyticsService::class);

            $status = [
                'bot_status' => 'active',
                'health' => $maintenanceService->getSystemHealth(),
                'recent_tasks' => BotTask::latest()->limit(10)->get(),
                'analytics_summary' => $analyticsService->getDashboardAnalytics(),
                'timestamp' => now()->toISOString()
            ];

            return response()->json([
                'success' => true,
                'data' => $status
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get bot status', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get bot status'
            ], 500);
        }
    }

    /**
     * Get analytics data
     */
    public function analytics(Request $request): JsonResponse
    {
        try {
            $period = $request->get('period', 'daily');
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');

            $analyticsService = app(AnalyticsService::class);

            $data = match($period) {
                'daily' => $analyticsService->generateDailyReport($startDate ? \Carbon\Carbon::parse($startDate) : null),
                'weekly' => $analyticsService->generateWeeklyReport($startDate ? \Carbon\Carbon::parse($startDate) : null),
                'monthly' => $analyticsService->generateMonthlyReport($startDate ? \Carbon\Carbon::parse($startDate) : null),
                'custom' => $analyticsService->generateCustomReport(
                    \Carbon\Carbon::parse($startDate),
                    \Carbon\Carbon::parse($endDate)
                ),
                default => throw new \InvalidArgumentException("Invalid period: {$period}")
            };

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get analytics', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get analytics'
            ], 500);
        }
    }

    /**
     * Trigger a bot command manually
     */
    public function trigger(Request $request): JsonResponse
    {
        try {
            $command = $request->get('command');
            $parameters = $request->get('parameters', []);

            if (!$command) {
                return response()->json([
                    'success' => false,
                    'message' => 'Command is required'
                ], 400);
            }

            $validCommands = [
                'clawdbot:status',
                'clawdbot:daily-summary',
                'clawdbot:weekly-report',
                'clawdbot:property-cleanup',
                'clawdbot:expiry-notifier',
                'clawdbot:system-maintenance',
                'clawdbot:analytics'
            ];

            if (!in_array($command, $validCommands)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid command',
                    'valid_commands' => $validCommands
                ], 400);
            }

            // Build command with parameters
            $commandString = $command;
            foreach ($parameters as $key => $value) {
                $commandString .= " --{$key}={$value}";
            }

            // Execute command
            $exitCode = Artisan::call($commandString);

            if ($exitCode === 0) {
                return response()->json([
                    'success' => true,
                    'message' => "Command '{$command}' executed successfully",
                    'exit_code' => $exitCode
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => "Command '{$command}' failed",
                    'exit_code' => $exitCode
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Failed to trigger command', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to trigger command'
            ], 500);
        }
    }

    /**
     * Get bot task history
     */
    public function tasks(Request $request): JsonResponse
    {
        try {
            $limit = $request->get('limit', 50);
            $status = $request->get('status');
            $command = $request->get('command');

            $query = BotTask::latest();

            if ($status) {
                $query->where('status', $status);
            }

            if ($command) {
                $query->where('command', 'like', "%{$command}%");
            }

            $tasks = $query->limit($limit)->get();

            return response()->json([
                'success' => true,
                'data' => $tasks
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get tasks', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get tasks'
            ], 500);
        }
    }

    /**
     * Get system health information
     */
    public function health(): JsonResponse
    {
        try {
            $maintenanceService = app(MaintenanceService::class);
            
            $health = $maintenanceService->getSystemHealth();

            return response()->json([
                'success' => true,
                'data' => $health
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get health status', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get health status'
            ], 500);
        }
    }

    /**
     * Get bot statistics
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = [
                'total_tasks' => BotTask::count(),
                'completed_tasks' => BotTask::where('status', 'completed')->count(),
                'failed_tasks' => BotTask::where('status', 'failed')->count(),
                'running_tasks' => BotTask::where('status', 'running')->count(),
                'success_rate' => $this->calculateSuccessRate(),
                'analytics_records' => BotAnalytics::count(),
                'notifications_sent' => BotNotification::count(),
                'uptime_percentage' => $this->calculateUptime(),
                'last_activity' => BotTask::latest()->first()?->created_at
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get statistics', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get statistics'
            ], 500);
        }
    }

    /**
     * Calculate success rate
     */
    private function calculateSuccessRate(): float
    {
        $total = BotTask::count();
        $completed = BotTask::where('status', 'completed')->count();
        
        return $total > 0 ? round(($completed / $total) * 100, 2) : 0;
    }

    /**
     * Calculate uptime percentage
     */
    private function calculateUptime(): float
    {
        // This would calculate actual uptime based on task execution history
        // For now, return placeholder
        return 99.5;
    }
}
