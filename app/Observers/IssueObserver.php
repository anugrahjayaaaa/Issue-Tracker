<?php

namespace App\Observers;

use App\Models\Issue;
use Illuminate\Support\Facades\Request;

class IssueObserver
{
    public function created(Issue $issue): void
    {
        activity()->causedBy(auth()->user())->withProperties([
            'ip' => Request::ip(), 'user_agent' => Request::userAgent(),
        ])->performedOn($issue)->log('issue_created');
    }

    public function updated(Issue $issue): void
    {
        $dirty = $issue->getDirty();
        $old = [];
        foreach ($dirty as $k => $v) {
            $old[$k] = $issue->getOriginal($k);
        }
        activity()->causedBy(auth()->user())->withProperties([
            'ip' => Request::ip(), 'user_agent' => Request::userAgent(),
            'old' => $old, 'new' => $dirty,
        ])->performedOn($issue)->log('issue_updated');
    }

    public function deleted(Issue $issue): void
    {
        // ponytail: root-cause cleanup — any delete path (controller/bulk/console)
        // removes the issue's scoped storage folder.
        deleteStorageFolder('projects/'.$issue->project->folder().'/issues/'.$issue->code);
        activity()->causedBy(auth()->user())->withProperties([
            'ip' => Request::ip(), 'user_agent' => Request::userAgent(),
        ])->performedOn($issue)->log('issue_deleted');
    }

    public function restored(Issue $issue): void
    {
        activity()->causedBy(auth()->user())->withProperties([
            'ip' => Request::ip(), 'user_agent' => Request::userAgent(),
        ])->performedOn($issue)->log('issue_restored');
    }
}
