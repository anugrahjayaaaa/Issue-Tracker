<?php

namespace App\Http\Requests\Project;

use App\Models\Project;
use App\Models\ProjectMember;
use Illuminate\Foundation\Http\FormRequest;

class StatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $project = $this->route('project');

        return $this->user()->can('project.manage')
            || ProjectMember::hasRole($this->user(), $project, [ProjectMember::ROLE_LEAD]);
    }

    /** @return array<string,mixed> */
    public function rules(): array
    {
        $project = $this->route('project');
        $status = $this->route('status'); // for update
        $id = $status ? $status->id : 'NULL';

        return [
            'name' => 'required|string|max:50|unique:statuses,name,'.$id.',id,project_id,'.$project->id,
            'color' => 'nullable|string|max:7',
            'is_closed' => 'nullable|boolean',
        ];
    }
}
