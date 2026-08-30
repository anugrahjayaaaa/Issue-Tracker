<?php

namespace App\Http\Requests\Rbac;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates creating a permission. Authz via route middleware (can:permission.create).
 */
class PermissionStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('permission.create');
    }

    /** @return array<string,string> */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:permissions,name',
        ];
    }
}
