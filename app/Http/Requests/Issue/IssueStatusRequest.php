<?php

namespace App\Http\Requests\Issue;

use App\Models\Issue;
use App\Models\ProjectMember;
use Illuminate\Foundation\Http\FormRequest;

class IssueStatusRequest extends FormRequest
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
        return [
            'status' => 'required|in:'.implode(',', [Issue::STATUS_OPEN, Issue::STATUS_IN_PROGRESS, Issue::STATUS_BLOCKED, Issue::STATUS_DONE]),
            'order' => 'nullable|integer|min:0',
        ];
    }
}
