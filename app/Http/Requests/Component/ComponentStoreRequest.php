<?php

namespace App\Http\Requests\Component;

use Illuminate\Foundation\Http\FormRequest;

class ComponentStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // route middleware gates this
    }

    /** @return array<string,mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'lead_id' => ['nullable', 'exists:users,id'],
        ];
    }
}
