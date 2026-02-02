<?php

namespace App\Jobs\ClawDBot;

use App\Models\Property;
use App\Models\User;
use App\Notifications\ClawDBot\PriceChangeAlert;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendPriceChangeAlerts implements ShouldQueue
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
        public ?int $propertyId = null,
        public float $priceChange = 0,
        public string $alertType = 'decrease'
    ) {
        $this->onQueue('clawdbot-notifications');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('ClawDBot: Sending price change alerts', [
                'property_id' => $this->propertyId,
                'price_change' => $this->priceChange,
                'alert_type' => $this->alertType
            ]);

            $results = $this->sendAlerts();

            Log::info('ClawDBot: Price change alerts sent', $results);

        } catch (\Exception $e) {
            Log::error('ClawDBot: Failed to send price change alerts', [
                'property_id' => $this->propertyId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Send price change alerts
     */
    private function sendAlerts(): array
    {
        $results = [
            'alerts_sent' => 0,
            'users_notified' => 0,
            'properties_processed' => 0
        ];

        if ($this->propertyId) {
            // Send alert for specific property
            $property = Property::find($this->propertyId);
            if ($property) {
                $alertResults = $this->sendPropertyAlert($property);
                $results['alerts_sent'] += $alertResults['alerts_sent'];
                $results['users_notified'] += $alertResults['users_notified'];
                $results['properties_processed']++;
            }
        } else {
            // Send alerts for all properties with recent price changes
            $properties = $this->getPropertiesWithPriceChanges();
            
            foreach ($properties as $property) {
                $alertResults = $this->sendPropertyAlert($property);
                $results['alerts_sent'] += $alertResults['alerts_sent'];
                $results['users_notified'] += $alertResults['users_notified'];
                $results['properties_processed']++;
            }
        }

        return $results;
    }

    /**
     * Send alert for a specific property
     */
    private function sendPropertyAlert(Property $property): array
    {
        $results = [
            'alerts_sent' => 0,
            'users_notified' => 0
        ];

        // Get users who should be notified
        $usersToNotify = $this->getUsersToNotify($property);

        foreach ($usersToNotify as $user) {
            try {
                $user->notify(new PriceChangeAlert(
                    $property,
                    $this->priceChange,
                    $this->alertType
                ));
                
                $results['alerts_sent']++;
                $results['users_notified']++;

            } catch (\Exception $e) {
                Log::error("Failed to send price alert to user {$user->id}", [
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $results;
    }

    /**
     * Get users who should be notified about price changes
     */
    private function getUsersToNotify(Property $property): \Illuminate\Database\Eloquent\Collection
    {
        $users = collect();

        // Notify property owner
        if ($property->owner) {
            $users->push($property->owner);
        }

        // Notify users who have enquired about this property
        $enquiryUsers = User::whereHas('enquiries', function ($query) use ($property) {
            $query->where('property_id', $property->id);
        })->get();

        $users = $users->merge($enquiryUsers);

        // Notify users who have saved this property to wishlist
        $wishlistUsers = User::whereHas('wishlist', function ($query) use ($property) {
            $query->where('property_id', $property->id);
        })->get();

        $users = $users->merge($wishlistUsers);

        // Remove duplicates
        return $users->unique('id');
    }

    /**
     * Get properties with recent price changes
     */
    private function getPropertiesWithPriceChanges(): \Illuminate\Database\Eloquent\Collection
    {
        // This would typically use a price history table
        // For now, return recently updated properties
        return Property::where('updated_at', '>=', now()->subHours(24))
            ->where('status', 'active')
            ->with(['owner'])
            ->get();
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('ClawDBot: Send price change alerts job failed', [
            'property_id' => $this->propertyId,
            'price_change' => $this->priceChange,
            'alert_type' => $this->alertType,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }

    /**
     * Get the unique identifier for the job.
     */
    public function uniqueId(): string
    {
        return 'price-change-alerts-' . ($this->propertyId ?? 'all') . '-' . $this->alertType;
    }
}
