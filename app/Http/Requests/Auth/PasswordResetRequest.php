<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates the password reset form (token + new password).
 */
class PasswordResetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // public endpoint (token proves ownership)
    }

    /** @return array<string,string> */
    public function rules(): array
    {
        return [
            'token' => 'required|string',
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string',
        ];
    }
}
