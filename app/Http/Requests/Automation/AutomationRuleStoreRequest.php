<?php

namespace App\Http\Requests\Automation;

use Illuminate\Foundation\Http\FormRequest;

class AutomationRuleStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // route middleware gates this
    }

    /** @return array<string,mixed> */
    public function rules(): array
    {
        return [
            'project_id' => ['required', 'exists:projects,id'],
            'name' => ['required', 'string', 'max:255'],
            'event' => ['required', 'string'],
            'conditions' => ['nullable', 'array'],
            'actions' => ['required', 'array'],
            'enabled' => ['boolean'],
        ];
    }
}
