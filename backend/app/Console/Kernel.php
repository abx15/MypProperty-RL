<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // ClawDBot Scheduled Tasks
        
        // Daily Property Summary - Every day at 8:00 AM
        $schedule->command('clawdbot:daily-summary')
                 ->dailyAt('08:00')
                 ->description('Generate and send daily property summary')
                 ->onSuccess(function () {
                     \Log::info('ClawDBot: Daily summary completed successfully');
                 })
                 ->onFailure(function () {
                     \Log::error('ClawDBot: Daily summary failed');
                 });

        // Property Expiry Warnings - Every day at 9:00 AM
        $schedule->command('clawdbot:expiry-notifier --type=warning --days=7')
                 ->dailyAt('09:00')
                 ->description('Send 7-day expiry warnings')
                 ->onSuccess(function () {
                     \Log::info('ClawDBot: 7-day expiry warnings sent successfully');
                 })
                 ->onFailure(function () {
                     \Log::error('ClawDBot: 7-day expiry warnings failed');
                 });

        // Critical Expiry Alerts - Every day at 10:00 AM
        $schedule->command('clawdbot:expiry-notifier --type=critical --days=3')
                 ->dailyAt('10:00')
                 ->description('Send critical expiry alerts (3 days)')
                 ->onSuccess(function () {
                     \Log::info('ClawDBot: Critical expiry alerts sent successfully');
                 })
                 ->onFailure(function () {
                     \Log::error('ClawDBot: Critical expiry alerts failed');
                 });

        // Property Cleanup - Every day at 11:00 PM
        $schedule->command('clawdbot:property-cleanup')
                 ->dailyAt('23:00')
                 ->description('Clean up expired and inactive properties')
                 ->onSuccess(function () {
                     \Log::info('ClawDBot: Property cleanup completed successfully');
                 })
                 ->onFailure(function () {
                     \Log::error('ClawDBot: Property cleanup failed');
                 });

        // Weekly Analytics Report - Every Monday at 8:00 AM
        $schedule->command('clawdbot:weekly-report')
                 ->weekly()
                 ->mondays()
                 ->at('08:00')
                 ->description('Generate comprehensive weekly analytics report')
                 ->onSuccess(function () {
                     \Log::info('ClawDBot: Weekly report completed successfully');
                 })
                 ->onFailure(function () {
                     \Log::error('ClawDBot: Weekly report failed');
                 });

        // System Maintenance - Every Sunday at 2:00 AM
        $schedule->command('clawdbot:system-maintenance')
                 ->weekly()
                 ->sundays()
                 ->at('02:00')
                 ->description('Perform system maintenance tasks')
                 ->onSuccess(function () {
                     \Log::info('ClawDBot: System maintenance completed successfully');
                 })
                 ->onFailure(function () {
                     \Log::error('ClawDBot: System maintenance failed');
                 });

        // Bot Health Check - Every 6 hours
        $schedule->command('clawdbot:status --health')
                 ->everySixHours()
                 ->description('Run bot health checks')
                 ->onSuccess(function () {
                     \Log::info('ClawDBot: Health check completed successfully');
                 })
                 ->onFailure(function () {
                     \Log::error('ClawDBot: Health check failed');
                 });

        // Expired Properties Notification - Every day at midnight
        $schedule->command('clawdbot:expiry-notifier --type=expired')
                 ->dailyAt('00:00')
                 ->description('Notify about properties that expired today')
                 ->onSuccess(function () {
                     \Log::info('ClawDBot: Expired property notifications sent successfully');
                 })
                 ->onFailure(function () {
                     \Log::error('ClawDBot: Expired property notifications failed');
                 });

        // Analytics Processing - Every 2 hours
        $schedule->command('clawdbot:analytics --process')
                 ->everyTwoHours()
                 ->description('Process analytics data')
                 ->onSuccess(function () {
                     \Log::info('ClawDBot: Analytics processing completed successfully');
                 })
                 ->onFailure(function () {
                     \Log::error('ClawDBot: Analytics processing failed');
                 });

        // Queue Monitoring - Every 30 minutes
        $schedule->command('queue:monitor')
                 ->everyThirtyMinutes()
                 ->description('Monitor queue health')
                 ->withoutOverlapping()
                 ->onSuccess(function () {
                     \Log::info('ClawDBot: Queue monitoring completed successfully');
                 })
                 ->onFailure(function () {
                     \Log::error('ClawDBot: Queue monitoring failed');
                 });

        // Prevent overlapping for critical tasks
        $schedule->command('clawdbot:property-cleanup')
                 ->dailyAt('23:00')
                 ->withoutOverlapping()
                 ->runInBackground();

        $schedule->command('clawdbot:weekly-report')
                 ->weekly()
                 ->mondays()
                 ->at('08:00')
                 ->withoutOverlapping()
                 ->runInBackground();

        // Send notifications if scheduler fails
        $schedule->call(function () {
            $this->checkSchedulerHealth();
        })->everyFiveMinutes();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        // Include ClawDBot commands
        $this->load(__DIR__.'/Commands/ClawDBot');

        require base_path('routes/console.php');
    }

    /**
     * Check scheduler health and send alerts if needed.
     */
    private function checkSchedulerHealth(): void
    {
        try {
            // Check if scheduler is running properly
            $lastRun = \App\Models\ClawDBot\BotTask::where('command', 'like', 'clawdbot:%')
                ->orderBy('created_at', 'desc')
                ->first();

            if ($lastRun && $lastRun->created_at->lt(now()->subHours(2))) {
                // Scheduler might be stuck, send alert
                $admin = \App\Models\User::whereHas('roles', function ($q) {
                    $q->where('name', 'admin');
                })->first();

                if ($admin) {
                    $admin->notify(new \App\Notifications\ClawDBot\BotStatusAlert(
                        'Scheduler Health Warning',
                        'ClawDBot scheduler may not be running properly. Last task was over 2 hours ago.'
                    ));
                }
            }
        } catch (\Exception $e) {
            \Log::error('Scheduler health check failed: ' . $e->getMessage());
        }
    }
}
