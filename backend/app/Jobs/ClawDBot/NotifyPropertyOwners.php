<?php

namespace App\Jobs\ClawDBot;

use App\Models\Property;
use App\Notifications\ClawDBot\PropertyExpiringSoon;
use App\Notifications\ClawDBot\PropertyExpired;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NotifyPropertyOwners implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     */
    public int $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Property $property,
        public string $notificationType
    ) {
        $this->onQueue('clawdbot-notifications');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $owner = $this->property->owner;

            if (!$owner) {
                Log::warning('ClawDBot: Property has no owner', [
                    'property_id' => $this->property->id,
                    'property_title' => $this->property->title
                ]);
                return;
            }

            Log::info('ClawDBot: Sending property notification', [
                'property_id' => $this->property->id,
                'owner_email' => $owner->email,
                'notification_type' => $this->notificationType
            ]);

            // Determine which notification to send
            $notification = $this->getNotificationInstance();

            if ($notification) {
                $owner->notify($notification);

                Log::info('ClawDBot: Property notification sent successfully', [
                    'property_id' => $this->property->id,
                    'owner_email' => $owner->email,
                    'notification_type' => $this->notificationType
                ]);
            }

        } catch (\Exception $e) {
            Log::error('ClawDBot: Failed to send property notification', [
                'property_id' => $this->property->id,
                'notification_type' => $this->notificationType,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Get the appropriate notification instance based on type.
     */
    private function getNotificationInstance()
    {
        return match($this->notificationType) {
            'warning' => new PropertyExpiringSoon($this->property, 'warning'),
            'critical' => new PropertyExpiringSoon($this->property, 'critical'),
            'expired' => new PropertyExpired($this->property),
            'inactive' => new PropertyExpiringSoon($this->property, 'inactive'),
            default => null
        };
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('ClawDBot: Property notification job failed', [
            'property_id' => $this->property->id,
            'notification_type' => $this->notificationType,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);

        // Optionally notify admin about the failure
        if ($admin = \App\Models\User::whereHas('roles', function ($q) {
            $q->where('name', 'admin');
        })->first()) {
            $admin->notify(new \App\Notifications\ClawDBot\BotStatusAlert(
                'Property Notification Failed',
                "Failed to send {$this->notificationType} notification for property '{$this->property->title}'. Error: {$exception->getMessage()}"
            ));
        }
    }

    /**
     * Get the unique identifier for the job.
     */
    public function uniqueId(): string
    {
        return 'property-notification-' . $this->property->id . '-' . $this->notificationType;
    }
}
