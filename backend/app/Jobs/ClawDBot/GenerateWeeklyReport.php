<?php

namespace App\Jobs\ClawDBot;

use App\Models\User;
use App\Notifications\ClawDBot\WeeklyAnalyticsReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateWeeklyReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     */
    public int $timeout = 300;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public User $recipient,
        public array $reportData,
        public int $week,
        public int $year
    ) {
        $this->onQueue('clawdbot-reports');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('ClawDBot: Generating weekly report', [
                'recipient' => $this->recipient->email,
                'week' => $this->week,
                'year' => $this->year
            ]);

            // Send the weekly report notification
            $this->recipient->notify(new WeeklyAnalyticsReport(
                $this->reportData,
                $this->week,
                $this->year
            ));

            Log::info('ClawDBot: Weekly report sent successfully', [
                'recipient' => $this->recipient->email,
                'week' => $this->week,
                'year' => $this->year
            ]);

        } catch (\Exception $e) {
            Log::error('ClawDBot: Failed to generate weekly report', [
                'recipient' => $this->recipient->email,
                'week' => $this->week,
                'year' => $this->year,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('ClawDBot: Weekly report job failed', [
            'recipient' => $this->recipient->email,
            'week' => $this->week,
            'year' => $this->year,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);

        // Optionally notify admin about the failure
        if ($admin = User::whereHas('roles', function ($q) {
            $q->where('name', 'admin');
        })->first()) {
            $admin->notify(new \App\Notifications\ClawDBot\BotStatusAlert(
                'Weekly Report Generation Failed',
                "Failed to send weekly report to {$this->recipient->email} for week {$this->week}, {$this->year}. Error: {$exception->getMessage()}"
            ));
        }
    }

    /**
     * Get the unique identifier for the job.
     */
    public function uniqueId(): string
    {
        return 'weekly-report-' . $this->recipient->id . '-' . $this->week . '-' . $this->year;
    }
}
