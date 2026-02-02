<?php

namespace App\Observers;

use App\Models\Property;
use App\Jobs\ClawDBot\UpdatePropertyStatus;
use App\Jobs\ClawDBot\NotifyPropertyOwners;
use App\Jobs\ClawDBot\GenerateSuggestions;
use App\Services\ClawDBot\ValidationService;
use App\Services\ClawDBot\AILogsService;
use Illuminate\Support\Facades\Log;

class PropertyObserver
{
    /**
     * Handle the Property "created" event.
     */
    public function created(Property $property): void
    {
        Log::info('Property created', [
            'property_id' => $property->id,
            'title' => $property->title,
            'owner_id' => $property->owner_id
        ]);

        // Validate the new property
        $this->validateProperty($property);

        // Generate AI suggestions for the property
        $this->generatePropertySuggestions($property);

        // Log AI interaction
        $this->logAIInteraction('property_created', $property);
    }

    /**
     * Handle the Property "updated" event.
     */
    public function updated(Property $property): void
    {
        Log::info('Property updated', [
            'property_id' => $property->id,
            'title' => $property->title,
            'changes' => $property->getDirty()
        ]);

        // Check if status changed
        if ($property->wasChanged('status')) {
            $this->handleStatusChange($property);
        }

        // Check if price changed
        if ($property->wasChanged('price')) {
            $this->handlePriceChange($property);
        }

        // Validate the updated property
        $this->validateProperty($property);

        // Re-generate suggestions if significant changes
        if ($this->hasSignificantChanges($property)) {
            $this->generatePropertySuggestions($property);
        }
    }

    /**
     * Handle the Property "deleted" event.
     */
    public function deleted(Property $property): void
    {
        Log::info('Property deleted', [
            'property_id' => $property->id,
            'title' => $property->title,
            'owner_id' => $property->owner_id
        ]);

        // Log AI interaction for property removal
        $this->logAIInteraction('property_deleted', $property);
    }

    /**
     * Handle the Property "restored" event.
     */
    public function restored(Property $property): void
    {
        Log::info('Property restored', [
            'property_id' => $property->id,
            'title' => $property->title
        ]);
    }

    /**
     * Handle the Property "force deleted" event.
     */
    public function forceDeleted(Property $property): void
    {
        Log::warning('Property force deleted', [
            'property_id' => $property->id,
            'title' => $property->title,
            'owner_id' => $property->owner_id
        ]);
    }

    /**
     * Validate property using ValidationService
     */
    private function validateProperty(Property $property): void
    {
        try {
            $validationService = app(ValidationService::class);
            $issues = $validationService->validateProperty($property);

            if (!empty($issues)) {
                Log::warning('Property validation issues found', [
                    'property_id' => $property->id,
                    'issues' => $issues
                ]);

                // Could dispatch a job to handle validation issues
                // or notify the property owner
            }
        } catch (\Exception $e) {
            Log::error('Failed to validate property', [
                'property_id' => $property->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Generate AI suggestions for the property
     */
    private function generatePropertySuggestions(Property $property): void
    {
        try {
            // Dispatch job to generate suggestions in background
            GenerateSuggestions::dispatch(
                $property->id,
                null,
                'property'
            );
        } catch (\Exception $e) {
            Log::error('Failed to generate property suggestions', [
                'property_id' => $property->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle property status changes
     */
    private function handleStatusChange(Property $property): void
    {
        try {
            $oldStatus = $property->getOriginal('status');
            $newStatus = $property->status;

            Log::info('Property status changed', [
                'property_id' => $property->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus
            ]);

            // Dispatch job to handle status-specific actions
            UpdatePropertyStatus::dispatch($property, $oldStatus, $newStatus);

            // If property became active, notify interested users
            if ($newStatus === 'active' && $oldStatus !== 'active') {
                $this->notifyInterestedUsers($property);
            }

            // If property expired, notify owner
            if ($newStatus === 'expired') {
                $this->notifyOwnerOfExpiry($property);
            }

        } catch (\Exception $e) {
            Log::error('Failed to handle property status change', [
                'property_id' => $property->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle property price changes
     */
    private function handlePriceChange(Property $property): void
    {
        try {
            $oldPrice = $property->getOriginal('price');
            $newPrice = $property->price;
            $priceChange = $newPrice - $oldPrice;

            Log::info('Property price changed', [
                'property_id' => $property->id,
                'old_price' => $oldPrice,
                'new_price' => $newPrice,
                'change' => $priceChange
            ]);

            // If significant price change, notify interested users
            if (abs($priceChange) > ($oldPrice * 0.05)) { // 5% threshold
                $this->notifyPriceChange($property, $priceChange);
            }

        } catch (\Exception $e) {
            Log::error('Failed to handle property price change', [
                'property_id' => $property->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Notify interested users about property status change
     */
    private function notifyInterestedUsers(Property $property): void
    {
        try {
            // This would find users who have saved this property
            // or shown interest in similar properties
            NotifyPropertyOwners::dispatch(
                $property->id,
                'status_change',
                [
                    'old_status' => $property->getOriginal('status'),
                    'new_status' => $property->status
                ]
            );
        } catch (\Exception $e) {
            Log::error('Failed to notify interested users', [
                'property_id' => $property->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Notify owner of property expiry
     */
    private function notifyOwnerOfExpiry(Property $property): void
    {
        try {
            if ($property->owner) {
                NotifyPropertyOwners::dispatch(
                    $property->id,
                    'expired',
                    [
                        'expiry_date' => $property->expires_at?->toDateString()
                    ]
                );
            }
        } catch (\Exception $e) {
            Log::error('Failed to notify owner of expiry', [
                'property_id' => $property->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Notify about price changes
     */
    private function notifyPriceChange(Property $property, float $priceChange): void
    {
        try {
            $alertType = $priceChange > 0 ? 'increase' : 'decrease';
            
            NotifyPropertyOwners::dispatch(
                $property->id,
                'price_change',
                [
                    'price_change' => $priceChange,
                    'alert_type' => $alertType
                ]
            );
        } catch (\Exception $e) {
            Log::error('Failed to notify price change', [
                'property_id' => $property->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Check if property has significant changes
     */
    private function hasSignificantChanges(Property $property): bool
    {
        $significantFields = ['title', 'description', 'price', 'category', 'location_id'];
        
        foreach ($significantFields as $field) {
            if ($property->wasChanged($field)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Log AI interaction
     */
    private function logAIInteraction(string $action, Property $property): void
    {
        try {
            $aiLogsService = app(AILogsService::class);
            
            $aiLogsService->logAIInteraction([
                'type' => $action,
                'property_id' => $property->id,
                'user_id' => $property->owner_id,
                'request' => [
                    'property_data' => $property->toArray()
                ],
                'response' => [
                    'action_taken' => $action,
                    'timestamp' => now()->toISOString()
                ],
                'success' => true
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log AI interaction', [
                'action' => $action,
                'property_id' => $property->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
