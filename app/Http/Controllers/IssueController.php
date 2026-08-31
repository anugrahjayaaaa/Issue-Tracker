<?php

namespace App\Http\Controllers;

use App\Http\Requests\Issue\IssueBulkRequest;
use App\Http\Requests\Issue\IssueStatusRequest;
use App\Http\Requests\Issue\IssueStoreRequest;
use App\Http\Requests\Issue\IssueUpdateRequest;
use App\Models\Issue;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;
use App\Notifications\IssueAssigned;
use App\Notifications\IssueStatusChanged;
use App\Http\Controllers\Concerns\Sortable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IssueController extends Controller
{
    use Sortable;
    /** Read-gate: must be a project member (any role). */
    private function abortIfNotReader(Project $project): void
    {
        abort_unless(
            ProjectMember::hasRole(auth()->user(), $project, [
                ProjectMember::ROLE_LEAD, ProjectMember::ROLE_MEMBER, ProjectMember::ROLE_VIEWER,
            ]),
            403
        );
    }

    public function index(Request $request): View
    {
        $projects = Project::query()
            ->whereHas('members', fn ($q) => $q->where('user_id', auth()->id()))
            ->orderBy('name')->get();
        $project = $request->filled('project_id') ? Project::find($request->project_id) : null;

        $issues = collect();
        if ($project) {
            $this->abortIfNotReader($project);
            $issues = Issue::with(['assignee', 'labels'])
                ->where('project_id', $project->id)
                ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status)) // status = key slug
                ->when($request->filled('assignee_id'), fn ($q) => $q->where('assignee_id', $request->assignee_id))
                ->when($request->filled('priority'), fn ($q) => $q->where('priority', $request->priority))
                ->when($request->filled('label_id'), fn ($q) => $q->whereHas('labels', fn ($l) => $l->where('labels.id', $request->label_id)))
                ->when(true, fn ($q) => $this->sortIndex($q, $request, 'order', ['code', 'title', 'type', 'status', 'priority', 'assignee_id', 'due_date']))
                ->paginate(20)
                ->withQueryString();
        }

        return view('issues.index', compact('projects', 'project', 'issues'));
    }

    public function board(Request $request): View
    {
        $projects = Project::query()
            ->whereHas('members', fn ($q) => $q->where('user_id', auth()->id()))
            ->orderBy('name')->get();
        $project = $request->filled('project_id') ? Project::find($request->project_id) : null;

        $columns = [];
        if ($project) {
            $this->abortIfNotReader($project);
            $statuses = $project->statuses->pluck('key')->all();
            foreach ($statuses as $statusKey) {
                $columns[$statusKey] = Issue::with('assignee')
                    ->where('project_id', $project->id)
                    ->where('status', $statusKey)
                    ->orderBy('order')
                    ->get();
            }
        }

        return view('issues.board', compact('projects', 'project', 'columns'));
    }

    public function create(Request $request): View
    {
        $projects = Project::query()
            ->whereHas('members', fn ($q) => $q->where('user_id', auth()->id()))
            ->orderBy('name')->get();
        $project = $request->filled('project_id') ? Project::find($request->project_id) : null;
        $users = $project ? $project->users : collect();
        $types = $project ? $project->issueTypes : collect();
        $statuses = $project ? $project->statuses : collect();
        $priorities = [Issue::PRIORITY_LOW, Issue::PRIORITY_MEDIUM, Issue::PRIORITY_HIGH, Issue::PRIORITY_URGENT];

        return view('issues.create', compact('projects', 'project', 'users', 'types', 'statuses', 'priorities'));
    }

    public function store(IssueStoreRequest $request): RedirectResponse
    {
        $project = Project::findOrFail($request->input('project_id'));
        $issue = new Issue($request->validated());
        $issue->code = $project->nextIssueCode();
        $issue->reporter_id = $request->user()->id;
        $issue->status = $request->input('status')
            ?? $project->statuses()->orderBy('order')->value('key');
        $issue->save();
        $issue->labels()->sync($request->input('labels', []));

        if ($issue->assignee_id && $issue->assignee_id !== $request->user()->id) {
            $issue->assignee->notify(new IssueAssigned($issue));
        }

        return redirect()->route('issues.index', ['project_id' => $project->id])
            ->with('success', __('messages.issue_created'));
    }

    public function show(Issue $issue): View
    {
        $this->abortIfNotReader($issue->project);
        $issue->load('assignee', 'reporter', 'parent', 'comments.user', 'comments.attachments', 'labels', 'attachments');

        return view('issues.show', compact('issue'));
    }

    public function edit(Issue $issue): View
    {
        $project = $issue->project;
        $users = $project->users;
        $types = $project->issueTypes;
        $statuses = $project->statuses;
        $priorities = [Issue::PRIORITY_LOW, Issue::PRIORITY_MEDIUM, Issue::PRIORITY_HIGH, Issue::PRIORITY_URGENT];

        return view('issues.edit', compact('issue', 'project', 'users', 'types', 'statuses', 'priorities'));
    }

    public function update(IssueUpdateRequest $request, Issue $issue): RedirectResponse
    {
        if ($request->filled('status') && ! $issue->canTransitionTo($request->input('status'))) {
            return redirect()->route('issues.show', $issue)
                ->with('error', __('messages.status_transition_not_allowed'));
        }
        $oldAssignee = $issue->assignee_id;
        $issue->update($request->validated());
        $issue->labels()->sync($request->input('labels', []));

        if ($issue->assignee_id && $issue->assignee_id !== $oldAssignee && $issue->assignee_id !== $request->user()->id) {
            $issue->assignee->notify(new IssueAssigned($issue));
        }

        return redirect()->route('issues.show', $issue)
            ->with('success', __('messages.issue_updated'));
    }

    public function changeStatus(IssueStatusRequest $request, Issue $issue): RedirectResponse
    {
        $newStatus = $request->input('status');
        if (! $issue->canTransitionTo($newStatus)) {
            return redirect()->route('issues.board', ['project_id' => $issue->project_id])
                ->with('error', __('messages.status_transition_not_allowed'));
        }
        $old = $issue->status;
        $issue->status = $request->input('status');
        if ($request->filled('order')) {
            $issue->order = $request->input('order');
        }
        $issue->save();

        if ($old !== $issue->status) {
            $recipients = collect([$issue->reporter, $issue->assignee])->filter();
            foreach ($recipients as $recipient) {
                if ($recipient->id !== $request->user()->id) {
                    $recipient->notify(new IssueStatusChanged($issue, $old, $issue->status));
                }
            }
        }

        return redirect()->route('issues.board', ['project_id' => $issue->project_id])
            ->with('success', __('messages.issue_status_changed'));
    }

    public function destroy(Issue $issue): RedirectResponse
    {
        $projectId = $issue->project_id;
        $issue->delete();

        return redirect()->route('issues.index', ['project_id' => $projectId])
            ->with('success', __('messages.issue_deleted'));
    }

    public function bulk(IssueBulkRequest $request): RedirectResponse
    {
        $projectId = $request->input('project_id');
        $project = Project::findOrFail($projectId);
        // Re-check membership scope: only delete IDs that belong to THIS project
        // and the user can act on (prevents cross-project mass delete).
        $count = Issue::where('project_id', $projectId)
            ->whereIn('id', $request->input('ids'))
            ->delete();

        return redirect()->route('issues.index', ['project_id' => $projectId])
            ->with('success', __('messages.issues_deleted_count', ['count' => $count]));
    }
}
