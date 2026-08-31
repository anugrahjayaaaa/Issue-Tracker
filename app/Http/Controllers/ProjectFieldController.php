<?php

namespace App\Http\Controllers;

use App\Http\Requests\Project\IssueTypeRequest;
use App\Http\Requests\Project\StatusRequest;
use App\Http\Requests\Project\TransitionRequest;
use App\Models\IssueType;
use App\Models\Status;
use App\Models\StatusTransition;
use App\Models\Project;
use App\Models\ProjectMember;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProjectFieldController extends Controller
{
    private function abortIfNotLead(Project $project): void
    {
        abort_unless(
            auth()->user()->can('project.manage')
            || ProjectMember::hasRole(auth()->user(), $project, [ProjectMember::ROLE_LEAD]),
            403
        );
    }

    /** Field management page (types, statuses, transitions) for a project. */
    public function index(Project $project): View
    {
        $this->abortIfNotLead($project);
        $project->load(['issueTypes', 'statuses', 'statusTransitions.from', 'statusTransitions.to']);

        return view('projects.fields', compact('project'));
    }

    // ── Issue types ──
    public function storeType(IssueTypeRequest $request, Project $project): RedirectResponse
    {
        $this->abortIfNotLead($project);
        $order = $project->issueTypes()->max('order') + 1;
        $project->issueTypes()->create([...$request->validated(), 'order' => $order]);

        return redirect()->route('projects.fields', $project)->with('success', __('messages.saved'));
    }

    public function updateType(IssueTypeRequest $request, Project $project, IssueType $type): RedirectResponse
    {
        $this->abortIfNotLead($project);
        $type->update($request->validated());

        return redirect()->route('projects.fields', $project)->with('success', __('messages.saved'));
    }

    public function destroyType(Project $project, IssueType $type): RedirectResponse
    {
        $this->abortIfNotLead($project);
        abort_if($type->issues()->exists(), 409, __('messages.issue_type_in_use'));

        $type->delete();

        return redirect()->route('projects.fields', $project)->with('success', __('messages.deleted'));
    }

    // ── Statuses ──
    public function storeStatus(StatusRequest $request, Project $project): RedirectResponse
    {
        $this->abortIfNotLead($project);
        $order = $project->statuses()->max('order') + 1;
        $data = $request->validated();
        $data['is_closed'] = $request->boolean('is_closed');
        $project->statuses()->create([...$data, 'order' => $order]);

        return redirect()->route('projects.fields', $project)->with('success', __('messages.saved'));
    }

    public function updateStatus(StatusRequest $request, Project $project, Status $status): RedirectResponse
    {
        $this->abortIfNotLead($project);
        $data = $request->validated();
        $data['is_closed'] = $request->boolean('is_closed');
        $status->update($data);

        return redirect()->route('projects.fields', $project)->with('success', __('messages.saved'));
    }

    public function destroyStatus(Project $project, Status $status): RedirectResponse
    {
        $this->abortIfNotLead($project);
        abort_if($status->issues()->exists(), 409, __('messages.status_in_use'));

        $status->delete();

        return redirect()->route('projects.fields', $project)->with('success', __('messages.deleted'));
    }

    // ── Workflow transitions ──
    public function storeTransition(TransitionRequest $request, Project $project): RedirectResponse
    {
        $this->abortIfNotLead($project);
        $data = $request->validated();
        if ($data['from_status_id'] === $data['to_status_id']) {
            return redirect()->route('projects.fields', $project)->with('error', __('messages.invalid_transition'));
        }
        $project->statusTransitions()->firstOrCreate($data);

        return redirect()->route('projects.fields', $project)->with('success', __('messages.saved'));
    }

    public function destroyTransition(Project $project, StatusTransition $transition): RedirectResponse
    {
        $this->abortIfNotLead($project);
        $transition->delete();

        return redirect()->route('projects.fields', $project)->with('success', __('messages.deleted'));
    }
}
