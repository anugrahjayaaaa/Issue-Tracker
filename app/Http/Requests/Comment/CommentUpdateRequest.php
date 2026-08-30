<?php

namespace App\Http\Requests\Comment;

use App\Models\Comment;
use App\Models\Issue;
use App\Models\ProjectMember;
use Illuminate\Foundation\Http\FormRequest;

class CommentUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        $comment = $this->route('comment');
        $project = $comment->issue->project;

        // owner or project lead may edit
        return $this->user()->can('comment.edit')
            && ($comment->user_id === $this->user()->id
                || ProjectMember::isLead($this->user(), $project));
    }

    /** @return array<string,mixed> */
    public function rules(): array
    {
        return [
            'body' => 'required|string|max:10000',
        ];
    }
}
