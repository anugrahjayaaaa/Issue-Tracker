<?php

namespace App\Http\Requests\Automation;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAutomationRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // route middleware gates this
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'event' => 'sometimes|string',
            'conditions' => 'nullable|array',
            'actions' => 'sometimes|array',
            'enabled' => 'boolean',
        ];
    }
}
