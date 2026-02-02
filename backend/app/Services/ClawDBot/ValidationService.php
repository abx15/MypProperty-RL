<?php

namespace App\Services\ClawDBot;

use App\Models\Property;
use App\Models\User;
use App\Models\Enquiry;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ValidationService
{
    /**
     * Validate a property
     */
    public function validateProperty(Property $property): array
    {
        $issues = [];

        // Basic required fields
        if (empty($property->title)) {
            $issues[] = 'Property title is required';
        }

        if (empty($property->description)) {
            $issues[] = 'Property description is required';
        }

        if (!$property->price || $property->price <= 0) {
            $issues[] = 'Property price must be greater than 0';
        }

        if (!$property->category) {
            $issues[] = 'Property category is required';
        }

        if (!$property->location_id) {
            $issues[] = 'Property location is required';
        }

        if (!$property->owner_id) {
            $issues[] = 'Property owner is required';
        }

        // Description validation
        if ($property->description && strlen($property->description) < 50) {
            $issues[] = 'Property description should be at least 50 characters';
        }

        // Price validation
        if ($property->price) {
            if ($property->price < 1000) {
                $issues[] = 'Property price seems too low';
            }
            if ($property->price > 10000000) {
                $issues[] = 'Property price seems unusually high';
            }
        }

        // Status validation
        if (!in_array($property->status, ['active', 'inactive', 'expired', 'pending'])) {
            $issues[] = 'Invalid property status';
        }

        // Expiry validation
        if ($property->expires_at && $property->expires_at->isPast() && $property->status === 'active') {
            $issues[] = 'Property has expired but still marked as active';
        }

        // Images validation
        if ($property->images->isEmpty()) {
            $issues[] = 'Property should have at least one image';
        }

        // Contact information validation
        if ($property->owner && !$property->owner->email) {
            $issues[] = 'Property owner must have a valid email';
        }

        return $issues;
    }

    /**
     * Validate a user
     */
    public function validateUser(User $user): array
    {
        $issues = [];

        // Basic required fields
        if (empty($user->name)) {
            $issues[] = 'User name is required';
        }

        if (empty($user->email)) {
            $issues[] = 'User email is required';
        }

        // Email validation
        if ($user->email && !filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
            $issues[] = 'Invalid email format';
        }

        // Phone validation
        if ($user->phone && !preg_match('/^[+]?[\d\s\-\(\)]+$/', $user->phone)) {
            $issues[] = 'Invalid phone number format';
        }

        // Status validation
        if (!in_array($user->status, ['active', 'inactive', 'suspended', 'pending'])) {
            $issues[] = 'Invalid user status';
        }

        // Profile completeness
        if (!$user->phone) {
            $issues[] = 'User should provide a phone number';
        }

        // Role validation
        if ($user->roles->isEmpty()) {
            $issues[] = 'User should have at least one role assigned';
        }

        return $issues;
    }

    /**
     * Validate an enquiry
     */
    public function validateEnquiry(Enquiry $enquiry): array
    {
        $issues = [];

        // Basic required fields
        if (empty($enquiry->message)) {
            $issues[] = 'Enquiry message is required';
        }

        if (!$enquiry->property_id) {
            $issues[] = 'Enquiry must be associated with a property';
        }

        if (!$enquiry->user_id) {
            $issues[] = 'Enquiry must be associated with a user';
        }

        // Message validation
        if ($enquiry->message && strlen($enquiry->message) < 10) {
            $issues[] = 'Enquiry message should be at least 10 characters';
        }

        if ($enquiry->message && strlen($enquiry->message) > 2000) {
            $issues[] = 'Enquiry message is too long (max 2000 characters)';
        }

        // Status validation
        if (!in_array($enquiry->status, ['pending', 'responded', 'closed', 'spam'])) {
            $issues[] = 'Invalid enquiry status';
        }

        // Property validation
        if ($enquiry->property_id && !$enquiry->property) {
            $issues[] = 'Associated property not found';
        }

        // User validation
        if ($enquiry->user_id && !$enquiry->user) {
            $issues[] = 'Associated user not found';
        }

        // Duplicate check
        $duplicateEnquiry = Enquiry::where('user_id', $enquiry->user_id)
            ->where('property_id', $enquiry->property_id)
            ->where('created_at', '>=', now()->subHours(24))
            ->where('id', '!=', $enquiry->id)
            ->first();

        if ($duplicateEnquiry) {
            $issues[] = 'Duplicate enquiry detected (same user and property within 24 hours)';
        }

        return $issues;
    }

    /**
     * Validate property data before creation
     */
    public function validatePropertyData(array $data): array
    {
        $validator = Validator::make($data, [
            'title' => 'required|string|max:255',
            'description' => 'required|string|min:50',
            'price' => 'required|numeric|min:1000|max:10000000',
            'category' => 'required|string',
            'location_id' => 'required|exists:locations,id',
            'owner_id' => 'required|exists:users,id',
            'status' => 'in:active,inactive,expired,pending',
            'expires_at' => 'nullable|date|after:today'
        ]);

        $issues = [];

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $issues[] = $error;
            }
        }

        // Custom validations
        if (isset($data['price']) && isset($data['category'])) {
            $issues = array_merge($issues, $this->validatePriceByCategory($data['price'], $data['category']));
        }

        return $issues;
    }

    /**
     * Validate user data before creation
     */
    public function validateUserData(array $data): array
    {
        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'status' => 'in:active,inactive,suspended,pending'
        ]);

        $issues = [];

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $issues[] = $error;
            }
        }

        return $issues;
    }

    /**
     * Validate enquiry data before creation
     */
    public function validateEnquiryData(array $data): array
    {
        $validator = Validator::make($data, [
            'message' => 'required|string|min:10|max:2000',
            'property_id' => 'required|exists:properties,id',
            'user_id' => 'required|exists:users,id',
            'status' => 'in:pending,responded,closed,spam'
        ]);

        $issues = [];

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $issues[] = $error;
            }
        }

        return $issues;
    }

    /**
     * Validate price based on category
     */
    private function validatePriceByCategory(float $price, string $category): array
    {
        $issues = [];

        $priceRanges = [
            'apartment' => ['min' => 50000, 'max' => 500000],
            'house' => ['min' => 100000, 'max' => 2000000],
            'commercial' => ['min' => 100000, 'max' => 5000000],
            'land' => ['min' => 25000, 'max' => 1000000],
        ];

        if (isset($priceRanges[$category])) {
            $range = $priceRanges[$category];
            
            if ($price < $range['min']) {
                $issues[] = "Price for {$category} seems too low (minimum: \${$range['min']})";
            }
            
            if ($price > $range['max']) {
                $issues[] = "Price for {$category} seems too high (maximum: \${$range['max']})";
            }
        }

        return $issues;
    }

    /**
     * Bulk validate all properties
     */
    public function bulkValidateProperties(): array
    {
        $results = [
            'total' => 0,
            'valid' => 0,
            'invalid' => 0,
            'issues' => []
        ];

        $properties = Property::with(['owner', 'location', 'images'])->get();

        foreach ($properties as $property) {
            $results['total']++;
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

        return $results;
    }

    /**
     * Bulk validate all users
     */
    public function bulkValidateUsers(): array
    {
        $results = [
            'total' => 0,
            'valid' => 0,
            'invalid' => 0,
            'issues' => []
        ];

        $users = User::with('roles')->get();

        foreach ($users as $user) {
            $results['total']++;
            $issues = $this->validateUser($user);

            if (empty($issues)) {
                $results['valid']++;
            } else {
                $results['invalid']++;
                $results['issues'][] = [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'issues' => $issues
                ];
            }
        }

        return $results;
    }

    /**
     * Bulk validate all enquiries
     */
    public function bulkValidateEnquiries(): array
    {
        $results = [
            'total' => 0,
            'valid' => 0,
            'invalid' => 0,
            'issues' => []
        ];

        $enquiries = Enquiry::with(['user', 'property'])->get();

        foreach ($enquiries as $enquiry) {
            $results['total']++;
            $issues = $this->validateEnquiry($enquiry);

            if (empty($issues)) {
                $results['valid']++;
            } else {
                $results['invalid']++;
                $results['issues'][] = [
                    'enquiry_id' => $enquiry->id,
                    'enquiry_message' => substr($enquiry->message, 0, 50) . '...',
                    'issues' => $issues
                ];
            }
        }

        return $results;
    }

    /**
     * Get validation summary statistics
     */
    public function getValidationSummary(): array
    {
        return [
            'properties' => $this->bulkValidateProperties(),
            'users' => $this->bulkValidateUsers(),
            'enquiries' => $this->bulkValidateEnquiries()
        ];
    }
}
