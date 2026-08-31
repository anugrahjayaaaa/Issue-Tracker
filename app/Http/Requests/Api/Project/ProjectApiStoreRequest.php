<?php

namespace App\Http\Requests\Api\Project;

use App\Models\ProjectMember;
use Illuminate\Foundation\Http\FormRequest;

class ProjectApiStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('project.manage');
    }

    public function rules(): array
    {
        return [
            'key' => 'required|string|max:20|unique:projects,key',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'owner_id' => 'nullable|exists:users,id',
        ];
    }
}
