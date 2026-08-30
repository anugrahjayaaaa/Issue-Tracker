<?php

namespace App\Http\Requests\Rbac;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates updating a permission. Authz via route middleware (can:permission.update).
 */
class PermissionUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('permission.edit');
    }

    /** @return array<string,string> */
    public function rules(): array
    {
        $permId = $this->route('permission')->id;

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('permissions', 'name')->ignore($permId)],
        ];
    }
}
