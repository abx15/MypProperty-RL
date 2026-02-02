<?php

namespace App\Http\Requests\ClawDBot;

use Illuminate\Foundation\Http\FormRequest;

class AnalyticsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Admin and agent users can view analytics
        return auth()->user()->hasAnyRole(['admin', 'agent']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'period' => 'sometimes|string|in:daily,weekly,monthly,custom',
            'start_date' => 'required_if:period,custom|date',
            'end_date' => 'required_if:period,custom|date|after_or_equal:start_date',
            'limit' => 'sometimes|integer|min:1|max:1000'
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'period.in' => 'The period must be one of: daily, weekly, monthly, custom.',
            'start_date.required_if' => 'Start date is required when period is custom.',
            'end_date.required_if' => 'End date is required when period is custom.',
            'end_date.after_or_equal' => 'End date must be after or equal to start date.',
            'limit.min' => 'Limit must be at least 1.',
            'limit.max' => 'Limit cannot exceed 1000.'
        ];
    }
}
