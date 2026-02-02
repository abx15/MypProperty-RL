<?php

namespace App\Jobs\ClawDBot;

use App\Models\Property;
use App\Services\ClawDBot\ValidationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ValidateListings implements ShouldQueue
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
        public string $validationType = 'properties',
        public ?int $itemId = null
    ) {
        $this->onQueue('clawdbot-maintenance');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('ClawDBot: Starting listings validation', [
                'validation_type' => $this->validationType,
                'item_id' => $this->itemId
            ]);

            $validationService = app(ValidationService::class);
            $results = $this->performValidation($validationService);

            Log::info('ClawDBot: Listings validation completed', $results);

        } catch (\Exception $e) {
            Log::error('ClawDBot: Failed to validate listings', [
                'validation_type' => $this->validationType,
                'item_id' => $this->itemId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Perform the validation
     */
    private function performValidation(ValidationService $validationService): array
    {
        $results = [
            'items_validated' => 0,
            'issues_found' => 0,
            'validation_details' => []
        ];

        switch ($this->validationType) {
            case 'properties':
                $results = $this->validateProperties($validationService);
                break;
            case 'users':
                $results = $this->validateUsers($validationService);
                break;
            case 'enquiries':
                $results = $this->validateEnquiries($validationService);
                break;
            default:
                throw new \InvalidArgumentException("Unknown validation type: {$this->validationType}");
        }

        return $results;
    }

    /**
     * Validate properties
     */
    private function validateProperties(ValidationService $validationService): array
    {
        $results = [
            'items_validated' => 0,
            'issues_found' => 0,
            'validation_details' => []
        ];

        if ($this->itemId) {
            // Validate specific property
            $property = Property::find($this->itemId);
            if ($property) {
                $issues = $validationService->validateProperty($property);
                $results['validation_details'][$this->itemId] = $issues;
                $results['items_validated'] = 1;
                $results['issues_found'] = count($issues);
            }
        } else {
            // Validate all properties
            $properties = Property::all();
            foreach ($properties as $property) {
                $issues = $validationService->validateProperty($property);
                $results['validation_details'][$property->id] = $issues;
                $results['items_validated']++;
                $results['issues_found'] += count($issues);
            }
        }

        return $results;
    }

    /**
     * Validate users
     */
    private function validateUsers(ValidationService $validationService): array
    {
        $results = [
            'items_validated' => 0,
            'issues_found' => 0,
            'validation_details' => []
        ];

        if ($this->itemId) {
            // Validate specific user
            $user = \App\Models\User::find($this->itemId);
            if ($user) {
                $issues = $validationService->validateUser($user);
                $results['validation_details'][$this->itemId] = $issues;
                $results['items_validated'] = 1;
                $results['issues_found'] = count($issues);
            }
        } else {
            // Validate all users
            $users = \App\Models\User::all();
            foreach ($users as $user) {
                $issues = $validationService->validateUser($user);
                $results['validation_details'][$user->id] = $issues;
                $results['items_validated']++;
                $results['issues_found'] += count($issues);
            }
        }

        return $results;
    }

    /**
     * Validate enquiries
     */
    private function validateEnquiries(ValidationService $validationService): array
    {
        $results = [
            'items_validated' => 0,
            'issues_found' => 0,
            'validation_details' => []
        ];

        if ($this->itemId) {
            // Validate specific enquiry
            $enquiry = \App\Models\Enquiry::find($this->itemId);
            if ($enquiry) {
                $issues = $validationService->validateEnquiry($enquiry);
                $results['validation_details'][$this->itemId] = $issues;
                $results['items_validated'] = 1;
                $results['issues_found'] = count($issues);
            }
        } else {
            // Validate all enquiries
            $enquiries = \App\Models\Enquiry::all();
            foreach ($enquiries as $enquiry) {
                $issues = $validationService->validateEnquiry($enquiry);
                $results['validation_details'][$enquiry->id] = $issues;
                $results['items_validated']++;
                $results['issues_found'] += count($issues);
            }
        }

        return $results;
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('ClawDBot: Validate listings job failed', [
            'validation_type' => $this->validationType,
            'item_id' => $this->itemId,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }

    /**
     * Get the unique identifier for the job.
     */
    public function uniqueId(): string
    {
        return 'validate-listings-' . $this->validationType . '-' . ($this->itemId ?? 'all');
    }
}
