<?php

namespace App\Http\Requests\Automation;

use Illuminate\Foundation\Http\FormRequest;

class AutomationRuleUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // route middleware gates this
    }

    /** @return array<string,mixed> */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'event' => ['sometimes', 'string'],
            'conditions' => ['nullable', 'array'],
            'actions' => ['nullable', 'array'],
            'enabled' => ['sometimes', 'boolean'],
        ];
    }
}
