<?php

namespace App\Jobs\ClawDBot;

use App\Models\Property;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdatePropertyStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     */
    public int $timeout = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Property $property,
        public string $newStatus
    ) {
        $this->onQueue('clawdbot-maintenance');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $oldStatus = $this->property->status;

            Log::info('ClawDBot: Updating property status', [
                'property_id' => $this->property->id,
                'old_status' => $oldStatus,
                'new_status' => $this->newStatus
            ]);

            // Update the property status
            $this->property->update([
                'status' => $this->newStatus,
                'status_updated_at' => now()
            ]);

            // Log the status change
            Log::info('ClawDBot: Property status updated successfully', [
                'property_id' => $this->property->id,
                'old_status' => $oldStatus,
                'new_status' => $this->property->status
            ]);

            // Trigger any additional actions based on status
            $this->handleStatusSpecificActions($oldStatus);

        } catch (\Exception $e) {
            Log::error('ClawDBot: Failed to update property status', [
                'property_id' => $this->property->id,
                'new_status' => $this->newStatus,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Handle status-specific actions.
     */
    private function handleStatusSpecificActions(string $oldStatus): void
    {
        switch ($this->newStatus) {
            case 'expired':
                // Handle expired property actions
                $this->handleExpiredProperty();
                break;

            case 'inactive':
                // Handle inactive property actions
                $this->handleInactiveProperty();
                break;

            case 'active':
                // Handle reactivated property actions
                $this->handleReactivatedProperty($oldStatus);
                break;
        }
    }

    /**
     * Handle expired property actions.
     */
    private function handleExpiredProperty(): void
    {
        // Archive property from search results
        // Notify interested users
        // Update analytics
        Log::info('ClawDBot: Handling expired property actions', [
            'property_id' => $this->property->id
        ]);
    }

    /**
     * Handle inactive property actions.
     */
    private function handleInactiveProperty(): void
    {
        // Reduce visibility in search
        // Send reactivation reminder
        Log::info('ClawDBot: Handling inactive property actions', [
            'property_id' => $this->property->id
        ]);
    }

    /**
     * Handle reactivated property actions.
     */
    private function handleReactivatedProperty(string $oldStatus): void
    {
        // Restore full visibility
        // Send reactivation confirmation
        Log::info('ClawDBot: Handling reactivated property actions', [
            'property_id' => $this->property->id,
            'previous_status' => $oldStatus
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('ClawDBot: Property status update job failed', [
            'property_id' => $this->property->id,
            'new_status' => $this->newStatus,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);

        // Optionally notify admin about the failure
        if ($admin = \App\Models\User::whereHas('roles', function ($q) {
            $q->where('name', 'admin');
        })->first()) {
            $admin->notify(new \App\Notifications\ClawDBot\BotStatusAlert(
                'Property Status Update Failed',
                "Failed to update property '{$this->property->title}' status to '{$this->newStatus}'. Error: {$exception->getMessage()}"
            ));
        }
    }

    /**
     * Get the unique identifier for the job.
     */
    public function uniqueId(): string
    {
        return 'property-status-' . $this->property->id . '-' . $this->newStatus;
    }
}
