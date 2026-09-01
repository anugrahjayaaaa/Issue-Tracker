<?php

namespace App\Http\Requests\Comment;

use App\Models\Issue;
use App\Models\ProjectMember;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CommentStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        $issue = $this->route('issue');
        $project = $issue->project;

        return $this->user()->can('comment.create')
            && ProjectMember::hasRole($this->user(), $project, [ProjectMember::ROLE_LEAD, ProjectMember::ROLE_MEMBER]);
    }

    /** @return array<string,mixed> */
    public function rules(): array
    {
        $issueId = $this->route('issue')?->id;

        return [
            'body' => 'required|string|max:10000',
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('comments', 'id')->where(fn ($q) => $q->where('issue_id', $issueId)),
            ],
        ];
    }
}
