<?php

namespace App\Observers;

use App\Models\Property;
use App\Services\ClawDBot\ValidationService;
use App\Services\ClawDBot\AILogsService;
use Illuminate\Support\Facades\Log;

class ListingObserver
{
    /**
     * Handle the Property "created" event (alias for PropertyObserver)
     */
    public function created(Property $property): void
    {
        Log::info('Listing created (ListingObserver)', [
            'property_id' => $property->id,
            'title' => $property->title
        ]);

        // Additional listing-specific logic
        $this->handleListingCreated($property);
    }

    /**
     * Handle the Property "updated" event (alias for PropertyObserver)
     */
    public function updated(Property $property): void
    {
        Log::info('Listing updated (ListingObserver)', [
            'property_id' => $property->id,
            'changes' => $property->getDirty()
        ]);

        // Additional listing-specific logic
        if ($this->hasListingChanges($property)) {
            $this->handleListingUpdated($property);
        }
    }

    /**
     * Handle the Property "deleted" event (alias for PropertyObserver)
     */
    public function deleted(Property $property): void
    {
        Log::info('Listing deleted (ListingObserver)', [
            'property_id' => $property->id,
            'title' => $property->title
        ]);

        // Additional listing-specific logic
        $this->handleListingDeleted($property);
    }

    /**
     * Handle listing creation
     */
    private function handleListingCreated(Property $property): void
    {
        try {
            // Check if listing needs review
            $this->checkListingForReview($property);

            // Update market data
            $this->updateMarketData($property);

            // Generate listing insights
            $this->generateListingInsights($property);

        } catch (\Exception $e) {
            Log::error('Failed to handle listing creation', [
                'property_id' => $property->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle listing updates
     */
    private function handleListingUpdated(Property $property): void
    {
        try {
            // Re-check if listing needs review
            $this->checkListingForReview($property);

            // Update market data if significant changes
            if ($this->hasSignificantListingChanges($property)) {
                $this->updateMarketData($property);
            }

            // Re-generate insights if needed
            $this->generateListingInsights($property);

        } catch (\Exception $e) {
            Log::error('Failed to handle listing update', [
                'property_id' => $property->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle listing deletion
     */
    private function handleListingDeleted(Property $property): void
    {
        try {
            // Update market data
            $this->updateMarketData($property, true);

            // Log listing removal for analytics
            $this->logListingRemoval($property);

        } catch (\Exception $e) {
            Log::error('Failed to handle listing deletion', [
                'property_id' => $property->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Check if listing needs review
     */
    private function checkListingForReview(Property $property): void
    {
        try {
            $validationService = app(ValidationService::class);
            $issues = $validationService->validateProperty($property);

            $needsReview = false;
            $reviewReasons = [];

            // Check for suspicious patterns
            if ($this->hasSuspiciousPatterns($property)) {
                $needsReview = true;
                $reviewReasons[] = 'Suspicious patterns detected';
            }

            // Check for incomplete information
            if (!empty($issues)) {
                $needsReview = true;
                $reviewReasons = array_merge($reviewReasons, $issues);
            }

            // Check for unusual pricing
            if ($this->hasUnusualPricing($property)) {
                $needsReview = true;
                $reviewReasons[] = 'Unusual pricing detected';
            }

            if ($needsReview) {
                Log::warning('Listing flagged for review', [
                    'property_id' => $property->id,
                    'reasons' => $reviewReasons
                ]);

                // This could dispatch a job to notify administrators
                // or create a review task
            }

        } catch (\Exception $e) {
            Log::error('Failed to check listing for review', [
                'property_id' => $property->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Update market data
     */
    private function updateMarketData(Property $property, bool $isDeletion = false): void
    {
        try {
            $action = $isDeletion ? 'removed' : 'added';
            
            Log::info('Updating market data', [
                'property_id' => $property->id,
                'action' => $action,
                'category' => $property->category,
                'location_id' => $property->location_id,
                'price' => $property->price
            ]);

            // This would update market statistics
            // price trends, inventory levels, etc.

        } catch (\Exception $e) {
            Log::error('Failed to update market data', [
                'property_id' => $property->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Generate listing insights
     */
    private function generateListingInsights(Property $property): void
    {
        try {
            $aiLogsService = app(AILogsService::class);
            
            $aiLogsService->logAIInteraction([
                'type' => 'listing_insights',
                'property_id' => $property->id,
                'user_id' => $property->owner_id,
                'request' => [
                    'property_data' => $property->toArray(),
                    'market_context' => $this->getMarketContext($property)
                ],
                'response' => [
                    'insights_generated' => true,
                    'timestamp' => now()->toISOString()
                ],
                'success' => true
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to generate listing insights', [
                'property_id' => $property->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Log listing removal
     */
    private function logListingRemoval(Property $property): void
    {
        try {
            Log::info('Listing removal logged for analytics', [
                'property_id' => $property->id,
                'title' => $property->title,
                'category' => $property->category,
                'location_id' => $property->location_id,
                'price' => $property->price,
                'listing_duration' => $property->created_at->diffInDays(now()),
                'status_at_removal' => $property->status
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to log listing removal', [
                'property_id' => $property->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Check for suspicious patterns
     */
    private function hasSuspiciousPatterns(Property $property): bool
    {
        $suspiciousPatterns = [];

        // Check for unusually low prices
        if ($property->price < 10000) {
            $suspiciousPatterns[] = 'Very low price';
        }

        // Check for missing required fields
        if (empty($property->description) || strlen($property->description) < 50) {
            $suspiciousPatterns[] = 'Insufficient description';
        }

        // Check for duplicate titles (simplified)
        $duplicateCount = Property::where('title', $property->title)
            ->where('id', '!=', $property->id)
            ->count();

        if ($duplicateCount > 2) {
            $suspiciousPatterns[] = 'Duplicate title pattern';
        }

        return !empty($suspiciousPatterns);
    }

    /**
     * Check for unusual pricing
     */
    private function hasUnusualPricing(Property $property): bool
    {
        try {
            // Get average price for similar properties
            $avgPrice = Property::where('category', $property->category)
                ->where('location_id', $property->location_id)
                ->where('status', 'active')
                ->avg('price');

            if ($avgPrice && $property->price) {
                $priceRatio = $property->price / $avgPrice;
                
                // Flag if price is more than 50% below or above average
                return $priceRatio < 0.5 || $priceRatio > 1.5;
            }

            return false;

        } catch (\Exception $e) {
            Log::error('Failed to check unusual pricing', [
                'property_id' => $property->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Check if listing has changes
     */
    private function hasListingChanges(Property $property): bool
    {
        $listingFields = ['title', 'description', 'price', 'category', 'location_id', 'status'];
        
        foreach ($listingFields as $field) {
            if ($property->wasChanged($field)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Check if listing has significant changes
     */
    private function hasSignificantListingChanges(Property $property): bool
    {
        $significantFields = ['price', 'category', 'location_id', 'status'];
        
        foreach ($significantFields as $field) {
            if ($property->wasChanged($field)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Get market context for AI insights
     */
    private function getMarketContext(Property $property): array
    {
        try {
            return [
                'category' => $property->category,
                'location' => $property->location?->name,
                'average_price' => Property::where('category', $property->category)
                    ->where('location_id', $property->location_id)
                    ->avg('price'),
                'total_listings' => Property::where('category', $property->category)
                    ->where('location_id', $property->location_id)
                    ->count(),
                'market_trend' => $this->getMarketTrend($property)
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get market context', [
                'property_id' => $property->id,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Get market trend
     */
    private function getMarketTrend(Property $property): string
    {
        // This would analyze actual market data
        // For now, return placeholder
        return 'stable';
    }
}
