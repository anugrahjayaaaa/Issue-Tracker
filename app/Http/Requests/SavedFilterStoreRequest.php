<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SavedFilterStoreRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:100'],
            'filter_params' => ['required', 'array'],
            'is_public' => ['nullable', 'boolean'],
        ];
    }
}
