<?php

namespace App\Http\Requests\Component;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ComponentUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // route middleware gates this
    }

    /** @return array<string,mixed> */
    public function rules(): array
    {
        $project = $this->route('project');

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'lead_id' => ['nullable', 'exists:users,id'],
        ];
    }
}
