<?php

namespace App\Http\Requests\Project;

use App\Models\Project;
use App\Models\ProjectMember;
use Illuminate\Foundation\Http\FormRequest;

class TransitionRequest extends FormRequest
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

        return [
            'from_status_id' => 'required|exists:statuses,id,project_id,'.$project->id,
            'to_status_id' => 'required|exists:statuses,id,project_id,'.$project->id,
            'key' => 'nullable|string|max:50', // stable slug; auto-derived from name if absent
        ];
    }
}
