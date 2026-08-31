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

        // Only the comment's author may edit it.
        return $this->user()->can('comment.edit')
            && $comment->user_id === $this->user()->id;
    }

    /** @return array<string,mixed> */
    public function rules(): array
    {
        return [
            'body' => 'required|string|max:10000',
        ];
    }
}
