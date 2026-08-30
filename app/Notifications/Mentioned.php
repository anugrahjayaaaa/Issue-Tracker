<?php

namespace App\Notifications;

use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class Mentioned extends Notification
{
    use Queueable;

    public function __construct(public Comment $comment) {}

    /** @return array<int,string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string,mixed> */
    public function toArray(object $notifiable): array
    {
        $issue = $this->comment->issue;

        return [
            'type' => 'mentioned',
            'comment_id' => $this->comment->id,
            'issue_id' => $issue->id,
            'issue_code' => $issue->code,
            'message' => "{$this->comment->user->name} mentioned you in {$issue->code}",
        ];
    }
}
