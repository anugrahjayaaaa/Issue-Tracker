<?php

namespace App\Notifications;

use App\Models\Issue;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class IssueAssigned extends Notification
{
    use Queueable;

    public function __construct(public Issue $issue) {}

    /** @return array<int,string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string,mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'issue_assigned',
            'issue_id' => $this->issue->id,
            'issue_code' => $this->issue->code,
            'title' => $this->issue->title,
            'message' => "You were assigned to {$this->issue->code}: {$this->issue->title}",
        ];
    }
}
