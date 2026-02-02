<?php

namespace App\Jobs\ClawDBot;

use App\Models\User;
use App\Notifications\ClawDBot\DailyPropertyDigest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendDailyDigest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     */
    public int $timeout = 180;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public User $recipient,
        public array $summaryData,
        public \Carbon\Carbon $targetDate
    ) {
        $this->onQueue('clawdbot-notifications');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('ClawDBot: Sending daily digest', [
                'recipient' => $this->recipient->email,
                'date' => $this->targetDate->toDateString()
            ]);

            // Send the daily digest notification
            $this->recipient->notify(new DailyPropertyDigest(
                $this->summaryData,
                $this->targetDate
            ));

            Log::info('ClawDBot: Daily digest sent successfully', [
                'recipient' => $this->recipient->email,
                'date' => $this->targetDate->toDateString()
            ]);

        } catch (\Exception $e) {
            Log::error('ClawDBot: Failed to send daily digest', [
                'recipient' => $this->recipient->email,
                'date' => $this->targetDate->toDateString(),
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
        Log::error('ClawDBot: Daily digest job failed', [
            'recipient' => $this->recipient->email,
            'date' => $this->targetDate->toDateString(),
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);

        // Optionally notify admin about the failure
        if ($admin = User::whereHas('roles', function ($q) {
            $q->where('name', 'admin');
        })->first()) {
            $admin->notify(new \App\Notifications\ClawDBot\BotStatusAlert(
                'Daily Digest Failed',
                "Failed to send daily digest to {$this->recipient->email} for {$this->targetDate->toDateString()}. Error: {$exception->getMessage()}"
            ));
        }
    }

    /**
     * Get the unique identifier for the job.
     */
    public function uniqueId(): string
    {
        return 'daily-digest-' . $this->recipient->id . '-' . $this->targetDate->toDateString();
    }
}
