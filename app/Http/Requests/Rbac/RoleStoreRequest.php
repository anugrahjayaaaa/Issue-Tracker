<?php

namespace App\Http\Requests\Rbac;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates creating a role. Authz via route middleware (can:role.create).
 */
class RoleStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('role.create');
    }

    /** @return array<string,string> */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ];
    }
}
