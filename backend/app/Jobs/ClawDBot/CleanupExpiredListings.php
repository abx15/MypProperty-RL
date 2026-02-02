<?php

namespace App\Jobs\ClawDBot;

use App\Models\Property;
use App\Notifications\ClawDBot\ListingRemoved;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CleanupExpiredListings implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     */
    public int $timeout = 600;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $daysThreshold = 90
    ) {
        $this->onQueue('clawdbot-maintenance');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('ClawDBot: Starting expired listings cleanup', [
                'days_threshold' => $this->daysThreshold
            ]);

            $results = $this->performCleanup();

            Log::info('ClawDBot: Expired listings cleanup completed', $results);

        } catch (\Exception $e) {
            Log::error('ClawDBot: Failed to cleanup expired listings', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Perform the actual cleanup
     */
    private function performCleanup(): array
    {
        $results = [
            'properties_removed' => 0,
            'notifications_sent' => 0,
            'errors' => []
        ];

        $cutoffDate = now()->subDays($this->daysThreshold);

        // Find properties to cleanup
        $propertiesToCleanup = Property::where('status', 'expired')
            ->where('updated_at', '<', $cutoffDate)
            ->with(['owner', 'images', 'enquiries'])
            ->get();

        foreach ($propertiesToCleanup as $property) {
            try {
                DB::transaction(function () use ($property, &$results) {
                    // Notify owner before removal
                    if ($property->owner) {
                        $property->owner->notify(new ListingRemoved($property));
                        $results['notifications_sent']++;
                    }

                    // Soft delete the property
                    $property->delete();

                    $results['properties_removed']++;

                    Log::info("Property {$property->id} removed during cleanup", [
                        'property_title' => $property->title,
                        'owner_id' => $property->owner_id
                    ]);
                });

            } catch (\Exception $e) {
                $results['errors'][] = "Failed to remove property {$property->id}: {$e->getMessage()}";
                Log::error("Failed to cleanup property {$property->id}", [
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $results;
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('ClawDBot: Cleanup expired listings job failed', [
            'days_threshold' => $this->daysThreshold,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }

    /**
     * Get the unique identifier for the job.
     */
    public function uniqueId(): string
    {
        return 'cleanup-expired-listings-' . $this->daysThreshold . '-' . now()->format('Y-m-d');
    }
}
