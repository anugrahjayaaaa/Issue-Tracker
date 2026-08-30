<?php

namespace App\Http\Requests\Issue;

use App\Models\Issue;
use App\Models\ProjectMember;
use Illuminate\Foundation\Http\FormRequest;

class IssueBulkRequest extends FormRequest
{
    public function authorize(): bool
    {
        $project = $this->route('project');

        return $this->user()->can('issue.delete')
            && ProjectMember::hasRole($this->user(), $project, [ProjectMember::ROLE_LEAD, ProjectMember::ROLE_MEMBER]);
    }

    /** @return array<string,mixed> */
    public function rules(): array
    {
        return [
            'ids' => 'required|array',
            'ids.*' => 'exists:issues,id',
            'action' => 'required|in:delete',
        ];
    }
}
