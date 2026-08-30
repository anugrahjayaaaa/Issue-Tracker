<?php

namespace App\Http\Requests\Rbac;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates updating a role. Authz via route middleware (can:role.update).
 */
class RoleUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('role.edit');
    }

    /** @return array<string,string> */
    public function rules(): array
    {
        $roleId = $this->route('role')->id;

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->ignore($roleId)],
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ];
    }
}
