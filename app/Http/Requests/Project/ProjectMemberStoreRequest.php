<?php

namespace App\Http\Requests\Project;

use App\Models\ProjectMember;
use Illuminate\Foundation\Http\FormRequest;

class ProjectMemberStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        // PM/lead capability: global project.manage OR project-level lead.
        $project = $this->route('project');

        return $this->user()->can('project.manage')
            || ProjectMember::isLead($this->user(), $project);
    }

    /** @return array<string,mixed> */
    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:'.implode(',', [ProjectMember::ROLE_LEAD, ProjectMember::ROLE_MEMBER, ProjectMember::ROLE_VIEWER]),
        ];
    }
}
