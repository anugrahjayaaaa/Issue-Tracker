<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates the change-password form (old password required).
 */
class PasswordChangeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // own account (auth required by route)
    }

    /** @return array<string,string> */
    public function rules(): array
    {
        return [
            'current_password' => 'required|current_password',
            'password' => 'required|string|min:12|confirmed',
            'password_confirmation' => 'required|string',
        ];
    }
}
