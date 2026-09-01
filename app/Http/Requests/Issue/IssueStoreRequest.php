<?php

namespace App\Http\Requests\Issue;

use App\Models\Issue;
use App\Models\Project;
use App\Models\ProjectMember;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
        $project = $this->route('project') ?? Project::find($this->input('project_id'));
        if (! $project) {
            return ['project_id' => 'required|exists:projects,id'];
        }

        return [
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|exists:issue_types,key,project_id,'.$project->id,
            'status' => 'nullable|exists:statuses,key,project_id,'.$project->id,
            'priority' => 'required|in:'.implode(',', [Issue::PRIORITY_LOW, Issue::PRIORITY_MEDIUM, Issue::PRIORITY_HIGH, Issue::PRIORITY_URGENT]),
            'assignee_id' => 'nullable|exists:users,id',
            'parent_id' => 'nullable|exists:issues,id',
            'sprint_id' => 'nullable|exists:sprints,id',
            'due_date' => 'nullable|date',
            'labels' => ['nullable', 'array'],
            'labels.*' => Rule::exists('labels', 'id')->where(fn ($q) => $q->where('project_id', $project->id)),
            'sprint_id' => ['nullable', Rule::exists('sprints', 'id')->where(fn ($q) => $q->where('project_id', $project->id))],
        ];
    }
}
