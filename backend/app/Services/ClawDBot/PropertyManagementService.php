<?php

namespace App\Services\ClawDBot;

use App\Models\Property;
use App\Models\User;
use App\Models\Enquiry;
use App\Models\ClawDBot\BotTask;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PropertyManagementService
{
    /**
     * Process expired properties
     */
    public function processExpiredProperties(): array
    {
        $results = [
            'processed' => 0,
            'failed' => 0,
            'notifications_sent' => 0
        ];

        try {
            DB::transaction(function () use (&$results) {
                $expiredProperties = Property::where('status', 'active')
                    ->where('expires_at', '<=', now())
                    ->with(['owner', 'images'])
                    ->get();

                foreach ($expiredProperties as $property) {
                    try {
                        // Update property status
                        $property->update([
                            'status' => 'expired',
                            'status_updated_at' => now()
                        ]);

                        // Log the change
                        Log::info('Property marked as expired', [
                            'property_id' => $property->id,
                            'title' => $property->title
                        ]);

                        $results['processed']++;

                    } catch (\Exception $e) {
                        Log::error('Failed to process expired property', [
                            'property_id' => $property->id,
                            'error' => $e->getMessage()
                        ]);
                        $results['failed']++;
                    }
                }
            });

        } catch (\Exception $e) {
            Log::error('PropertyManagementService: Failed to process expired properties', [
                'error' => $e->getMessage()
            ]);
        }

        return $results;
    }

    /**
     * Process inactive properties
     */
    public function processInactiveProperties(int $inactiveDays): array
    {
        $results = [
            'processed' => 0,
            'failed' => 0,
            'notifications_sent' => 0
        ];

        try {
            $inactiveDate = Carbon::now()->subDays($inactiveDays);

            DB::transaction(function () use ($inactiveDate, &$results) {
                $inactiveProperties = Property::where('status', 'active')
                    ->where('updated_at', '<', $inactiveDate)
                    ->whereDoesntHave('enquiries', function ($query) use ($inactiveDate) {
                        $query->where('created_at', '>=', $inactiveDate);
                    })
                    ->with(['owner'])
                    ->get();

                foreach ($inactiveProperties as $property) {
                    try {
                        // Update property status
                        $property->update([
                            'status' => 'inactive',
                            'status_updated_at' => now()
                        ]);

                        // Log the change
                        Log::info('Property marked as inactive', [
                            'property_id' => $property->id,
                            'title' => $property->title,
                            'days_inactive' => $inactiveDays
                        ]);

                        $results['processed']++;

                    } catch (\Exception $e) {
                        Log::error('Failed to process inactive property', [
                            'property_id' => $property->id,
                            'error' => $e->getMessage()
                        ]);
                        $results['failed']++;
                    }
                }
            });

        } catch (\Exception $e) {
            Log::error('PropertyManagementService: Failed to process inactive properties', [
                'error' => $e->getMessage(),
                'inactive_days' => $inactiveDays
            ]);
        }

        return $results;
    }

    /**
     * Get properties expiring within specified days
     */
    public function getPropertiesExpiringWithin(int $days): \Illuminate\Database\Eloquent\Collection
    {
        return Property::where('status', 'active')
            ->where('expires_at', '>=', now())
            ->where('expires_at', '<=', now()->copy()->addDays($days))
            ->with(['owner', 'location', 'images'])
            ->get();
    }

    /**
     * Get properties that expired today
     */
    public function getPropertiesExpiredToday(): \Illuminate\Database\Eloquent\Collection
    {
        return Property::where('status', 'active')
            ->where('expires_at', '<', now())
            ->whereDate('expires_at', now()->toDateString())
            ->with(['owner', 'location', 'images'])
            ->get();
    }

    /**
     * Validate property data
     */
    public function validateProperty(Property $property): array
    {
        $issues = [];

        // Check if property has required fields
        if (empty($property->title)) {
            $issues[] = 'Property title is missing';
        }

        if (empty($property->description)) {
            $issues[] = 'Property description is missing';
        }

        if ($property->price <= 0) {
            $issues[] = 'Property price is invalid';
        }

        if (!$property->location_id) {
            $issues[] = 'Property location is not set';
        }

        if (!$property->owner_id) {
            $issues[] = 'Property owner is not set';
        }

        // Check if property has images
        if ($property->images->isEmpty()) {
            $issues[] = 'Property has no images';
        }

        // Check if property is expired but still active
        if ($property->expires_at && $property->expires_at->isPast() && $property->status === 'active') {
            $issues[] = 'Property is expired but still marked as active';
        }

        return $issues;
    }

    /**
     * Bulk validate properties
     */
    public function bulkValidateProperties(): array
    {
        $results = [
            'total_checked' => 0,
            'valid' => 0,
            'invalid' => 0,
            'issues' => []
        ];

        try {
            $properties = Property::with(['owner', 'location', 'images'])->get();

            foreach ($properties as $property) {
                $results['total_checked']++;
                $issues = $this->validateProperty($property);

                if (empty($issues)) {
                    $results['valid']++;
                } else {
                    $results['invalid']++;
                    $results['issues'][] = [
                        'property_id' => $property->id,
                        'property_title' => $property->title,
                        'issues' => $issues
                    ];
                }
            }

        } catch (\Exception $e) {
            Log::error('PropertyManagementService: Failed to bulk validate properties', [
                'error' => $e->getMessage()
            ]);
        }

        return $results;
    }

    /**
     * Get property statistics
     */
    public function getPropertyStatistics(): array
    {
        try {
            return [
                'total' => Property::count(),
                'active' => Property::where('status', 'active')->count(),
                'expired' => Property::where('status', 'expired')->count(),
                'inactive' => Property::where('status', 'inactive')->count(),
                'expiring_soon' => Property::where('status', 'active')
                    ->where('expires_at', '>=', now())
                    ->where('expires_at', '<=', now()->copy()->addDays(7))
                    ->count(),
                'without_images' => Property::whereDoesntHave('images')->count(),
                'without_location' => Property::whereNull('location_id')->count(),
                'average_price' => Property::avg('price'),
                'total_value' => Property::sum('price')
            ];

        } catch (\Exception $e) {
            Log::error('PropertyManagementService: Failed to get property statistics', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Reactivate expired property
     */
    public function reactivateProperty(Property $property, int $extensionDays = 30): bool
    {
        try {
            DB::transaction(function () use ($property, $extensionDays) {
                $property->update([
                    'status' => 'active',
                    'expires_at' => now()->addDays($extensionDays),
                    'status_updated_at' => now()
                ]);

                Log::info('Property reactivated', [
                    'property_id' => $property->id,
                    'title' => $property->title,
                    'extension_days' => $extensionDays
                ]);
            });

            return true;

        } catch (\Exception $e) {
            Log::error('PropertyManagementService: Failed to reactivate property', [
                'property_id' => $property->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
