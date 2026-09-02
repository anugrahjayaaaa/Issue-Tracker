<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesProject;
use App\Http\Requests\Project\ProjectStoreRequest;
use App\Http\Requests\Project\ProjectUpdateRequest;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    use AuthorizesProject;
    public function index(Request $request): View
    {
        $projects = Project::query()
            ->whereHas('members', fn ($q) => $q->where('user_id', auth()->id()))
            ->when($request->filled('q'), function ($q) use ($request) {
                $q->where(function ($q2) use ($request) {
                    $q2->where('name', 'like', '%'.$request->q.'%')
                        ->orWhere('key', 'like', '%'.$request->q.'%');
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('projects.index', compact('projects'));
    }

    public function create(): View
    {
        return view('projects.create');
    }

    public function store(ProjectStoreRequest $request): RedirectResponse
    {
        $project = Project::create($request->validated() + ['owner_id' => $request->user()->id]);
        // creator becomes lead automatically
        $project->members()->create(['user_id' => $request->user()->id, 'role' => ProjectMember::ROLE_LEAD]);

        return redirect()->route('projects.index')->with('success', __('messages.project_created'));
    }

    public function show(Project $project): View
    {
        $this->ensureProjectReader($project);
        $project->load(['members.user', 'labels', 'components.lead', 'automationRules', 'issues' => fn ($q) => $q->latest()->limit(5)]);
        $users = User::orderBy('name')->get();

        return view('projects.show', compact('project', 'users'));
    }

    public function edit(Project $project): View
    {
        return view('projects.edit', compact('project'));
    }

    public function update(ProjectUpdateRequest $request, Project $project): RedirectResponse
    {
        $project->update($request->validated());

        return redirect()->route('projects.index')->with('success', __('messages.project_updated'));
    }

    public function destroy(Project $project): RedirectResponse
    {
        $project->delete();

        return redirect()->route('projects.index')->with('success', __('messages.project_deleted'));
    }
}
