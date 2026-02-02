<?php

namespace App\Http\Requests\ClawDBot;

use Illuminate\Foundation\Http\FormRequest;

class ScheduleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only admin users can manage schedules
        return auth()->user()->hasRole('admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'command' => 'required|string|max:255',
            'schedule_expression' => 'required|string|max:255',
            'description' => 'sometimes|string|max:500',
            'is_active' => 'sometimes|boolean',
            'parameters' => 'sometimes|array',
            'parameters.*' => 'string'
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
            'command.required' => 'The command field is required.',
            'command.max' => 'The command may not exceed 255 characters.',
            'schedule_expression.required' => 'The schedule expression is required.',
            'schedule_expression.max' => 'The schedule expression may not exceed 255 characters.',
            'description.max' => 'The description may not exceed 500 characters.',
            'parameters.array' => 'The parameters must be an array.',
            'parameters.*.string' => 'Each parameter must be a string.'
        ];
    }
}
