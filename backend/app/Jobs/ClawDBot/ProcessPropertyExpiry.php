<?php

namespace App\Jobs\ClawDBot;

use App\Models\Property;
use App\Notifications\ClawDBot\PropertyExpired;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessPropertyExpiry implements ShouldQueue
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
        public ?Property $property = null
    ) {
        $this->onQueue('clawdbot-maintenance');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $this->line('🔄 Processing property expiry...');

            if ($this->property) {
                // Process specific property
                $this->processSingleProperty($this->property);
            } else {
                // Process all expired properties
                $this->processAllExpiredProperties();
            }

            Log::info('ClawDBot: Property expiry processing completed');

        } catch (\Exception $e) {
            Log::error('ClawDBot: Failed to process property expiry', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Process a single property expiry
     */
    private function processSingleProperty(Property $property): void
    {
        if ($property->status !== 'active') {
            Log::info("Property {$property->id} is not active, skipping expiry processing");
            return;
        }

        // Update property status
        $property->update([
            'status' => 'expired',
            'status_updated_at' => now()
        ]);

        // Notify owner
        if ($property->owner) {
            $property->owner->notify(new PropertyExpired($property));
        }

        // Log the action
        Log::info("Property {$property->id} marked as expired", [
            'property_title' => $property->title,
            'owner_id' => $property->owner_id
        ]);
    }

    /**
     * Process all expired properties
     */
    private function processAllExpiredProperties(): void
    {
        $expiredProperties = Property::where('status', 'active')
            ->where('expires_at', '<=', now())
            ->with(['owner'])
            ->get();

        $processedCount = 0;
        $notificationCount = 0;

        foreach ($expiredProperties as $property) {
            try {
                // Update property status
                $property->update([
                    'status' => 'expired',
                    'status_updated_at' => now()
                ]);

                // Notify owner
                if ($property->owner) {
                    $property->owner->notify(new PropertyExpired($property));
                    $notificationCount++;
                }

                $processedCount++;

            } catch (\Exception $e) {
                Log::error("Failed to process expired property {$property->id}", [
                    'error' => $e->getMessage()
                ]);
            }
        }

        Log::info("Processed {$processedCount} expired properties, sent {$notificationCount} notifications");
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('ClawDBot: Property expiry job failed', [
            'property_id' => $this->property?->id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }

    /**
     * Get the unique identifier for the job.
     */
    public function uniqueId(): string
    {
        return 'property-expiry-' . ($this->property?->id ?? 'all');
    }
}
