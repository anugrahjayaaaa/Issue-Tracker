<?php

namespace App\Http\Requests\Component;

use Illuminate\Foundation\Http\FormRequest;

class UpdateComponentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function attributes(): array
    {
        return ['component_name' => 'component name'];
    }

    public function rules(): array
    {
        return [
            'component_name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'lead_id' => ['nullable', 'exists:users,id'],
        ];
    }
}
