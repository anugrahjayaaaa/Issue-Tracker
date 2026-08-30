<?php

namespace App\Observers;

use App\Models\Comment;
use Illuminate\Support\Facades\Request;

class CommentObserver
{
    public function created(Comment $comment): void
    {
        activity()->causedBy(auth()->user())->withProperties([
            'ip' => Request::ip(), 'user_agent' => Request::userAgent(),
        ])->performedOn($comment)->log('comment_created');
    }

    public function updated(Comment $comment): void
    {
        $dirty = $comment->getDirty();
        $old = [];
        foreach ($dirty as $k => $v) {
            $old[$k] = $comment->getOriginal($k);
        }
        activity()->causedBy(auth()->user())->withProperties([
            'ip' => Request::ip(), 'user_agent' => Request::userAgent(),
            'old' => $old, 'new' => $dirty,
        ])->performedOn($comment)->log('comment_updated');
    }

    public function deleted(Comment $comment): void
    {
        activity()->causedBy(auth()->user())->withProperties([
            'ip' => Request::ip(), 'user_agent' => Request::userAgent(),
        ])->performedOn($comment)->log('comment_deleted');
    }
}
