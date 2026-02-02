<?php

namespace App\Observers;

use App\Models\Enquiry;
use App\Services\ClawDBot\ValidationService;
use App\Services\ClawDBot\AILogsService;
use Illuminate\Support\Facades\Log;

class EnquiryObserver
{
    /**
     * Handle the Enquiry "created" event.
     */
    public function created(Enquiry $enquiry): void
    {
        Log::info('Enquiry created', [
            'enquiry_id' => $enquiry->id,
            'property_id' => $enquiry->property_id,
            'user_id' => $enquiry->user_id
        ]);

        // Validate the new enquiry
        $this->validateEnquiry($enquiry);

        // Check for duplicate enquiries
        $this->checkForDuplicates($enquiry);

        // Notify property owner
        $this->notifyPropertyOwner($enquiry);

        // Update property engagement metrics
        $this->updatePropertyMetrics($enquiry);

        // Log AI interaction
        $this->logAIInteraction('enquiry_created', $enquiry);
    }

    /**
     * Handle the Enquiry "updated" event.
     */
    public function updated(Enquiry $enquiry): void
    {
        Log::info('Enquiry updated', [
            'enquiry_id' => $enquiry->id,
            'property_id' => $enquiry->property_id,
            'user_id' => $enquiry->user_id,
            'changes' => $enquiry->getDirty()
        ]);

        // Check if status changed
        if ($enquiry->wasChanged('status')) {
            $this->handleStatusChange($enquiry);
        }

        // Validate the updated enquiry
        $this->validateEnquiry($enquiry);
    }

    /**
     * Handle the Enquiry "deleted" event.
     */
    public function deleted(Enquiry $enquiry): void
    {
        Log::info('Enquiry deleted', [
            'enquiry_id' => $enquiry->id,
            'property_id' => $enquiry->property_id,
            'user_id' => $enquiry->user_id
        ]);

        // Update property metrics
        $this->updatePropertyMetrics($enquiry, true);

        // Log AI interaction
        $this->logAIInteraction('enquiry_deleted', $enquiry);
    }

    /**
     * Handle the Enquiry "restored" event.
     */
    public function restored(Enquiry $enquiry): void
    {
        Log::info('Enquiry restored', [
            'enquiry_id' => $enquiry->id,
            'property_id' => $enquiry->property_id
        ]);
    }

    /**
     * Handle the Enquiry "force deleted" event.
     */
    public function forceDeleted(Enquiry $enquiry): void
    {
        Log::warning('Enquiry force deleted', [
            'enquiry_id' => $enquiry->id,
            'property_id' => $enquiry->property_id,
            'user_id' => $enquiry->user_id
        ]);
    }

    /**
     * Validate enquiry using ValidationService
     */
    private function validateEnquiry(Enquiry $enquiry): void
    {
        try {
            $validationService = app(ValidationService::class);
            $issues = $validationService->validateEnquiry($enquiry);

            if (!empty($issues)) {
                Log::warning('Enquiry validation issues found', [
                    'enquiry_id' => $enquiry->id,
                    'issues' => $issues
                ]);

                // Could flag the enquiry for review
                // or notify administrators
            }
        } catch (\Exception $e) {
            Log::error('Failed to validate enquiry', [
                'enquiry_id' => $enquiry->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Check for duplicate enquiries
     */
    private function checkForDuplicates(Enquiry $enquiry): void
    {
        try {
            $duplicateEnquiry = Enquiry::where('user_id', $enquiry->user_id)
                ->where('property_id', $enquiry->property_id)
                ->where('created_at', '>=', now()->subHours(24))
                ->where('id', '!=', $enquiry->id)
                ->first();

            if ($duplicateEnquiry) {
                Log::info('Duplicate enquiry detected', [
                    'enquiry_id' => $enquiry->id,
                    'duplicate_id' => $duplicateEnquiry->id,
                    'user_id' => $enquiry->user_id,
                    'property_id' => $enquiry->property_id
                ]);

                // Could merge enquiries or flag for review
            }
        } catch (\Exception $e) {
            Log::error('Failed to check for duplicate enquiries', [
                'enquiry_id' => $enquiry->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Notify property owner about new enquiry
     */
    private function notifyPropertyOwner(Enquiry $enquiry): void
    {
        try {
            if ($enquiry->property && $enquiry->property->owner) {
                // This would send a notification to the property owner
                Log::info('Notifying property owner of new enquiry', [
                    'enquiry_id' => $enquiry->id,
                    'property_id' => $enquiry->property_id,
                    'owner_id' => $enquiry->property->owner_id
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to notify property owner', [
                'enquiry_id' => $enquiry->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Update property engagement metrics
     */
    private function updatePropertyMetrics(Enquiry $enquiry, bool $isDeletion = false): void
    {
        try {
            if ($enquiry->property) {
                $property = $enquiry->property;
                
                // Update enquiry count
                $currentCount = $property->enquiry_count ?? 0;
                $newCount = $isDeletion ? max(0, $currentCount - 1) : $currentCount + 1;
                
                $property->update(['enquiry_count' => $newCount]);
                
                Log::info('Updated property enquiry metrics', [
                    'property_id' => $property->id,
                    'new_count' => $newCount,
                    'is_deletion' => $isDeletion
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to update property metrics', [
                'enquiry_id' => $enquiry->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle enquiry status changes
     */
    private function handleStatusChange(Enquiry $enquiry): void
    {
        try {
            $oldStatus = $enquiry->getOriginal('status');
            $newStatus = $enquiry->status;

            Log::info('Enquiry status changed', [
                'enquiry_id' => $enquiry->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus
            ]);

            // If enquiry was responded to, notify the user
            if ($newStatus === 'responded' && $oldStatus !== 'responded') {
                $this->notifyUserOfResponse($enquiry);
            }

            // If enquiry was marked as spam, log security event
            if ($newStatus === 'spam') {
                $this->logSpamEnquiry($enquiry);
            }

            // Update response time metrics
            if ($newStatus === 'responded') {
                $this->updateResponseMetrics($enquiry);
            }

        } catch (\Exception $e) {
            Log::error('Failed to handle enquiry status change', [
                'enquiry_id' => $enquiry->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Notify user of response
     */
    private function notifyUserOfResponse(Enquiry $enquiry): void
    {
        try {
            if ($enquiry->user) {
                // This would send a notification to the user
                Log::info('Notifying user of enquiry response', [
                    'enquiry_id' => $enquiry->id,
                    'user_id' => $enquiry->user_id
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to notify user of response', [
                'enquiry_id' => $enquiry->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Log spam enquiry
     */
    private function logSpamEnquiry(Enquiry $enquiry): void
    {
        try {
            Log::warning('Enquiry marked as spam', [
                'enquiry_id' => $enquiry->id,
                'user_id' => $enquiry->user_id,
                'property_id' => $enquiry->property_id,
                'message' => substr($enquiry->message, 0, 100) . '...'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log spam enquiry', [
                'enquiry_id' => $enquiry->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Update response time metrics
     */
    private function updateResponseMetrics(Enquiry $enquiry): void
    {
        try {
            if ($enquiry->property && $enquiry->property->owner) {
                // Calculate response time
                $responseTime = now()->diffInHours($enquiry->created_at);
                
                Log::info('Updated response metrics', [
                    'enquiry_id' => $enquiry->id,
                    'response_time_hours' => $responseTime,
                    'property_id' => $enquiry->property_id
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to update response metrics', [
                'enquiry_id' => $enquiry->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Log AI interaction
     */
    private function logAIInteraction(string $action, Enquiry $enquiry): void
    {
        try {
            $aiLogsService = app(AILogsService::class);
            
            $aiLogsService->logAIInteraction([
                'type' => $action,
                'user_id' => $enquiry->user_id,
                'property_id' => $enquiry->property_id,
                'request' => [
                    'enquiry_data' => $enquiry->toArray()
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
                'enquiry_id' => $enquiry->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
