<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates the login form (email or username + password).
 * Rate-limiting / lockout is handled in LoginController, not here.
 */
class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // public endpoint
    }

    /** @return array<string,string> */
    public function rules(): array
    {
        return [
            'identifier' => 'required|string|max:255',
            'password' => 'required|string',
            'remember' => 'boolean',
        ];
    }

    /** Coerce checkbox to bool. */
    protected function prepareForValidation(): void
    {
        $this->merge(['remember' => $this->boolean('remember')]);
    }
}
