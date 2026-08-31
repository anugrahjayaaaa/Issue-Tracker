<?php

namespace App\Http\Requests\Api\Project;

use App\Models\ProjectMember;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProjectApiUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('project.manage');
    }

    public function rules(): array
    {
        /** @var \App\Models\Project|null $project */
        $project = $this->route('project');

        return [
            'key' => ['sometimes','required','string','max:20', Rule::unique('projects','key')->ignore($project?->id)],
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'owner_id' => 'sometimes|nullable|exists:users,id',
        ];
    }
}
