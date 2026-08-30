<?php

namespace App\Notifications;

use App\Models\Issue;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class IssueStatusChanged extends Notification
{
    use Queueable;

    public function __construct(public Issue $issue, public string $oldStatus, public string $newStatus) {}

    /** @return array<int,string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string,mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'issue_status_changed',
            'issue_id' => $this->issue->id,
            'issue_code' => $this->issue->code,
            'title' => $this->issue->title,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'message' => "{$this->issue->code} status changed: {$this->oldStatus} → {$this->newStatus}",
        ];
    }
}
