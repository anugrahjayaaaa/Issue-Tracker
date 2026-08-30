<?php

namespace App\Http\Requests\Issue;

use App\Models\Issue;
use App\Models\Project;
use App\Models\ProjectMember;
use Illuminate\Foundation\Http\FormRequest;

class IssueStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        $project = $this->route('project') ?? Project::find($this->input('project_id'));
        if (! $project) {
            return false;
        }

        return $this->user()->can('issue.create')
            && ProjectMember::hasRole($this->user(), $project, [ProjectMember::ROLE_LEAD, ProjectMember::ROLE_MEMBER]);
    }

    /** @return array<string,mixed> */
    public function rules(): array
    {
        return [
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:'.implode(',', [Issue::TYPE_BUG, Issue::TYPE_FEATURE, Issue::TYPE_TASK, Issue::TYPE_EPIC]),
            'status' => 'required|in:'.implode(',', [Issue::STATUS_OPEN, Issue::STATUS_IN_PROGRESS, Issue::STATUS_BLOCKED, Issue::STATUS_DONE]),
            'priority' => 'required|in:'.implode(',', [Issue::PRIORITY_LOW, Issue::PRIORITY_MEDIUM, Issue::PRIORITY_HIGH, Issue::PRIORITY_URGENT]),
            'assignee_id' => 'nullable|exists:users,id',
            'parent_id' => 'nullable|exists:issues,id',
            'due_date' => 'nullable|date',
        ];
    }
}
