<?php

namespace App\Http\Requests\Project;

use App\Models\IssueType;
use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;

class IssueTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $project = $this->route('project');

        return $this->user()->can('project.manage')
            || \App\Models\ProjectMember::hasRole($this->user(), $project, [\App\Models\ProjectMember::ROLE_LEAD]);
    }

    /** @return array<string,mixed> */
    public function rules(): array
    {
        $project = $this->route('project');
        $type = $this->route('issueType'); // for update
        $id = $type ? $type->id : 'NULL';

        return [
            'name' => 'required|string|max:50|unique:issue_types,name,'.$id.',id,project_id,'.$project->id,
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:50',
            'description' => 'nullable|string',
        ];
    }
}
