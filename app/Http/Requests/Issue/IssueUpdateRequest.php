<?php

namespace App\Http\Requests\Issue;

use App\Models\Issue;
use App\Models\ProjectMember;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IssueUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        $issue = $this->route('issue');
        $project = $issue->project;

        return $this->user()->can('issue.edit')
            && ProjectMember::hasRole($this->user(), $project, [ProjectMember::ROLE_LEAD, ProjectMember::ROLE_MEMBER]);
    }

    /** @return array<string,mixed> */
    public function rules(): array
    {
        $issue = $this->route('issue');

        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:'.implode(',', [Issue::TYPE_BUG, Issue::TYPE_FEATURE, Issue::TYPE_TASK, Issue::TYPE_EPIC]),
            'status' => 'nullable|in:'.implode(',', [Issue::STATUS_OPEN, Issue::STATUS_IN_PROGRESS, Issue::STATUS_BLOCKED, Issue::STATUS_DONE]),
            'priority' => 'required|in:'.implode(',', [Issue::PRIORITY_LOW, Issue::PRIORITY_MEDIUM, Issue::PRIORITY_HIGH, Issue::PRIORITY_URGENT]),
            'assignee_id' => 'nullable|exists:users,id',
            'parent_id' => 'nullable|exists:issues,id|not_in:'.$issue->id,
            'due_date' => 'nullable|date',
            'labels' => ['nullable', 'array'],
            'labels.*' => Rule::exists('labels', 'id')->where(fn ($q) => $q->where('project_id', $issue->project->id)),
        ];
    }
}
