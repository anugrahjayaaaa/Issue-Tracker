<?php

namespace App\Http\Requests\ApiToken;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates creating a new API token (Sanctum).
 */
class ApiTokenStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('api-token.create');
    }

    /** @return array<string,string> */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
        ];
    }
}
