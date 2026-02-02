<?php

namespace App\Observers;

use App\Models\User;
use App\Services\ClawDBot\ValidationService;
use App\Services\ClawDBot\AILogsService;
use Illuminate\Support\Facades\Log;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        Log::info('User created', [
            'user_id' => $user->id,
            'email' => $user->email,
            'name' => $user->name
        ]);

        // Validate the new user
        $this->validateUser($user);

        // Generate AI recommendations for the user
        $this->generateUserRecommendations($user);

        // Log AI interaction
        $this->logAIInteraction('user_created', $user);
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        Log::info('User updated', [
            'user_id' => $user->id,
            'email' => $user->email,
            'changes' => $user->getDirty()
        ]);

        // Check if status changed
        if ($user->wasChanged('status')) {
            $this->handleStatusChange($user);
        }

        // Check if profile was significantly updated
        if ($this->hasProfileUpdate($user)) {
            $this->handleProfileUpdate($user);
        }

        // Validate the updated user
        $this->validateUser($user);

        // Re-generate recommendations if significant changes
        if ($this->hasSignificantChanges($user)) {
            $this->generateUserRecommendations($user);
        }
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        Log::info('User deleted', [
            'user_id' => $user->id,
            'email' => $user->email
        ]);

        // Log AI interaction for user removal
        $this->logAIInteraction('user_deleted', $user);
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        Log::info('User restored', [
            'user_id' => $user->id,
            'email' => $user->email
        ]);
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        Log::warning('User force deleted', [
            'user_id' => $user->id,
            'email' => $user->email
        ]);
    }

    /**
     * Validate user using ValidationService
     */
    private function validateUser(User $user): void
    {
        try {
            $validationService = app(ValidationService::class);
            $issues = $validationService->validateUser($user);

            if (!empty($issues)) {
                Log::warning('User validation issues found', [
                    'user_id' => $user->id,
                    'issues' => $issues
                ]);

                // Could dispatch a job to handle validation issues
                // or notify the user
            }
        } catch (\Exception $e) {
            Log::error('Failed to validate user', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Generate AI recommendations for the user
     */
    private function generateUserRecommendations(User $user): void
    {
        try {
            // This would dispatch a job to generate personalized recommendations
            // based on user preferences, search history, etc.
            Log::info('Generating user recommendations', [
                'user_id' => $user->id
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to generate user recommendations', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle user status changes
     */
    private function handleStatusChange(User $user): void
    {
        try {
            $oldStatus = $user->getOriginal('status');
            $newStatus = $user->status;

            Log::info('User status changed', [
                'user_id' => $user->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus
            ]);

            // If user became active, welcome them back
            if ($newStatus === 'active' && $oldStatus !== 'active') {
                $this->welcomeBackUser($user);
            }

            // If user was suspended, log security event
            if ($newStatus === 'suspended') {
                $this->logSuspension($user);
            }

        } catch (\Exception $e) {
            Log::error('Failed to handle user status change', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle profile updates
     */
    private function handleProfileUpdate(User $user): void
    {
        try {
            Log::info('User profile updated', [
                'user_id' => $user->id,
                'updated_fields' => $this->getProfileFields($user)
            ]);

            // Could trigger profile completion rewards
            // or update user segmentation
        } catch (\Exception $e) {
            Log::error('Failed to handle profile update', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Welcome back user
     */
    private function welcomeBackUser(User $user): void
    {
        try {
            // This could send a welcome back notification
            Log::info('Welcoming back user', [
                'user_id' => $user->id
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to welcome back user', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Log user suspension
     */
    private function logSuspension(User $user): void
    {
        try {
            Log::warning('User suspended', [
                'user_id' => $user->id,
                'email' => $user->email,
                'reason' => 'Status changed to suspended'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log suspension', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Check if user has profile update
     */
    private function hasProfileUpdate(User $user): bool
    {
        $profileFields = ['name', 'phone', 'bio', 'avatar'];
        
        foreach ($profileFields as $field) {
            if ($user->wasChanged($field)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Get updated profile fields
     */
    private function getProfileFields(User $user): array
    {
        $profileFields = ['name', 'phone', 'bio', 'avatar'];
        $updatedFields = [];
        
        foreach ($profileFields as $field) {
            if ($user->wasChanged($field)) {
                $updatedFields[] = $field;
            }
        }
        
        return $updatedFields;
    }

    /**
     * Check if user has significant changes
     */
    private function hasSignificantChanges(User $user): bool
    {
        $significantFields = ['email', 'phone', 'status'];
        
        foreach ($significantFields as $field) {
            if ($user->wasChanged($field)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Log AI interaction
     */
    private function logAIInteraction(string $action, User $user): void
    {
        try {
            $aiLogsService = app(AILogsService::class);
            
            $aiLogsService->logAIInteraction([
                'type' => $action,
                'user_id' => $user->id,
                'request' => [
                    'user_data' => $user->toArray()
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
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
