<?php

namespace App\Http\Requests\Api\Issue;

use App\Models\Issue;
use App\Models\ProjectMember;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IssueApiUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        $issue = $this->route('issue');

        return ProjectMember::hasRole($this->user(), $issue->project, [
            ProjectMember::ROLE_LEAD,
            ProjectMember::ROLE_MEMBER,
        ]);
    }

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
            'due_date' => 'sometimes|nullable|date',
            'labels' => ['sometimes', 'nullable', 'array'],
            'labels.*' => Rule::exists('labels', 'id')->where(fn ($q) => $q->where('project_id', $issue->project->id)),
        ];
    }
}
