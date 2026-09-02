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

        // Any project member (lead/member) may patch meta fields (assignee, due_date, …).
        // Full issue edit additionally needs issue.edit; but members can always update assignee/due_date.
        return ProjectMember::hasRole($this->user(), $issue->project, [
            ProjectMember::ROLE_LEAD, ProjectMember::ROLE_MEMBER,
        ]);
    }

    /** @return array<string,mixed> */
    public function rules(): array
    {
        $issue = $this->route('issue');

        return [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'type' => 'sometimes|required|exists:issue_types,key,project_id,'.$issue->project->id,
            'status' => 'sometimes|nullable|exists:statuses,key,project_id,'.$issue->project->id,
            'priority' => 'sometimes|required|in:'.implode(',', [Issue::PRIORITY_LOW, Issue::PRIORITY_MEDIUM, Issue::PRIORITY_HIGH, Issue::PRIORITY_URGENT]),
            'assignee_id' => 'sometimes|nullable|exists:users,id',
            'parent_id' => 'sometimes|nullable|exists:issues,id|not_in:'.$issue->id,
            'sprint_id' => 'sometimes|nullable|exists:sprints,id',
            'components' => ['sometimes', 'nullable', 'array'],
            'components.*' => Rule::exists('components', 'id')->where(fn ($q) => $q->where('project_id', $issue->project->id)),
            'due_date' => 'sometimes|nullable|date',
            'labels' => ['sometimes', 'nullable', 'array'],
            'labels.*' => Rule::exists('labels', 'id')->where(fn ($q) => $q->where('project_id', $issue->project->id)),
            'sprint_id' => ['sometimes', 'nullable', Rule::exists('sprints', 'id')->where(fn ($q) => $q->where('project_id', $issue->project->id))],
        ];
    }
}
