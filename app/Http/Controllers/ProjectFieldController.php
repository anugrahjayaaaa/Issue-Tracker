<?php

namespace App\Http\Controllers;

use App\Models\IssueType;
use App\Models\Status;
use App\Models\StatusTransition;
use App\Models\Project;
use App\Models\ProjectMember;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
    public function storeType(Request $request, Project $project): RedirectResponse
    {
        $this->abortIfNotLead($project);
        $data = $request->validate([
            'name' => 'required|string|max:50|unique:issue_types,name,NULL,id,project_id,'.$project->id,
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:50',
            'description' => 'nullable|string',
        ]);
        $order = $project->issueTypes()->max('order') + 1;
        $project->issueTypes()->create([...$data, 'order' => $order]);

        return redirect()->route('projects.fields', $project)->with('success', __('messages.saved'));
    }

    public function updateType(Request $request, Project $project, IssueType $type): RedirectResponse
    {
        $this->abortIfNotLead($project);
        $data = $request->validate([
            'name' => 'required|string|max:50|unique:issue_types,name,'.$type->id.',id,project_id,'.$project->id,
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:50',
            'description' => 'nullable|string',
        ]);
        $type->update($data);

        return redirect()->route('projects.fields', $project)->with('success', __('messages.saved'));
    }

    public function destroyType(Project $project, IssueType $type): RedirectResponse
    {
        $this->abortIfNotLead($project);
        $type->delete();

        return redirect()->route('projects.fields', $project)->with('success', __('messages.deleted'));
    }

    // ── Statuses ──
    public function storeStatus(Request $request, Project $project): RedirectResponse
    {
        $this->abortIfNotLead($project);
        $data = $request->validate([
            'name' => 'required|string|max:50|unique:statuses,name,NULL,id,project_id,'.$project->id,
            'color' => 'nullable|string|max:7',
            'is_closed' => 'nullable|boolean',
        ]);
        $order = $project->statuses()->max('order') + 1;
        $project->statuses()->create([...$data, 'order' => $order, 'is_closed' => $request->boolean('is_closed')]);

        return redirect()->route('projects.fields', $project)->with('success', __('messages.saved'));
    }

    public function updateStatus(Request $request, Project $project, Status $status): RedirectResponse
    {
        $this->abortIfNotLead($project);
        $data = $request->validate([
            'name' => 'required|string|max:50|unique:statuses,name,'.$status->id.',id,project_id,'.$project->id,
            'color' => 'nullable|string|max:7',
            'is_closed' => 'nullable|boolean',
        ]);
        $status->update([...$data, 'is_closed' => $request->boolean('is_closed')]);

        return redirect()->route('projects.fields', $project)->with('success', __('messages.saved'));
    }

    public function destroyStatus(Project $project, Status $status): RedirectResponse
    {
        $this->abortIfNotLead($project);
        $status->delete();

        return redirect()->route('projects.fields', $project)->with('success', __('messages.deleted'));
    }

    // ── Workflow transitions ──
    public function storeTransition(Request $request, Project $project): RedirectResponse
    {
        $this->abortIfNotLead($project);
        $data = $request->validate([
            'from_status_id' => 'required|exists:statuses,id,project_id,'.$project->id,
            'to_status_id' => 'required|exists:statuses,id,project_id,'.$project->id,
        ]);
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
