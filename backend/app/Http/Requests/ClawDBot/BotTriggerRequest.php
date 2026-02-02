<?php

namespace App\Http\Requests\ClawDBot;

use Illuminate\Foundation\Http\FormRequest;

class BotTriggerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only admin users can trigger bot commands
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
            'command' => 'required|string|in:clawdbot:status,clawdbot:daily-summary,clawdbot:weekly-report,clawdbot:property-cleanup,clawdbot:expiry-notifier,clawdbot:system-maintenance,clawdbot:analytics',
            'parameters' => 'sometimes|array',
            'parameters.*' => 'string',
            'force' => 'sometimes|boolean',
            'preview' => 'sometimes|boolean'
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
            'command.in' => 'The selected command is invalid.',
            'parameters.array' => 'The parameters must be an array.',
            'parameters.*.string' => 'Each parameter must be a string.'
        ];
    }
}
