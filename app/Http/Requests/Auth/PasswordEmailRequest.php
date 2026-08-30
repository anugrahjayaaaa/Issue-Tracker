<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates the "forgot password" email submission.
 */
class PasswordEmailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // public endpoint
    }

    /** @return array<string,string> */
    public function rules(): array
    {
        return [
            'email' => 'required|email|max:255',
        ];
    }
}
