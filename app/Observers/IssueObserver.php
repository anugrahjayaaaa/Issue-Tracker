<?php

namespace App\Observers;

use App\Models\Issue;
use Illuminate\Support\Facades\Request;

class IssueObserver
{
    public function created(Issue $issue): void
    {
        // Decision #3: auto-subscribe reporter + assignee (commenter added on comment).
        $issue->syncWatchers([$issue->reporter_id, $issue->assignee_id]);
        activity()->causedBy(auth()->user())->withProperties([
            'ip' => Request::ip(), 'user_agent' => Request::userAgent(),
        ])->performedOn($issue)->log('issue_created');
    }

    public function saved(Issue $issue): void
    {
        // Opt-in sub-task rollup (decision #2): when a child reaches a closed status,
        // flip the parent to a closed status once ALL its children are closed.
        if ($issue->project->subtask_rollup && $issue->parent_id) {
            $parent = $issue->parent;
            if ($parent && $parent->children()->count() > 0) {
                $allClosed = $parent->children()
                    ->whereHas('statusLink', fn ($q) => $q->where('is_closed', true))
                    ->count() === $parent->children()->count();
                if ($allClosed) {
                    $closed = $parent->project->statuses()->where('is_closed', true)->orderBy('order')->first();
                    if ($closed && $parent->status !== $closed->key) {
                        $parent->update(['status' => $closed->key]);
                    }
                }
            }
        }
    }

    public function updated(Issue $issue): void
    {
        $dirty = $issue->getChanges();
        $dirty = $dirty ?: $issue->getDirty();
        $old = [];
        foreach ($dirty as $k => $v) {
            $old[$k] = $issue->getOriginal($k);
        }
        activity()->causedBy(auth()->user())->withProperties([
            'ip' => Request::ip(), 'user_agent' => Request::userAgent(),
            'old' => $old, 'new' => $dirty,
        ])->performedOn($issue)->log('issue_updated');

        // Phase D: fire automation rules on status change.
        // ponytail: skip if this save was triggered by an automation rule itself (prevents loop)
        if (isset($dirty['status']) && ! $issue->getAttribute('automationProcessing')) {
            $issue->fireAutomationRules('issue:status_changed');
        }
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
