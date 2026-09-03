<?php

namespace App\Http\Controllers;

use App\Http\Requests\Component\ComponentStoreRequest;
use App\Http\Requests\Component\ComponentUpdateRequest;
use App\Http\Controllers\Concerns\AuthorizesProject;
use App\Models\Component;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;

class ComponentController extends Controller
{
    use AuthorizesProject;

    public function store(ComponentStoreRequest $request, Project $project): RedirectResponse
    {
        $this->ensureProjectLead($project);

        $project->components()->create($request->validated());

        return back()->with('success', __('messages.component_created'));
    }

    public function update(ComponentUpdateRequest $request, Project $project, Component $component): RedirectResponse
    {
        $this->ensureProjectLead($project);
        abort_unless($component->project_id === $project->id, 404);

        $component->update($request->validated());

        return back()->with('success', __('messages.component_updated'));
    }

    public function destroy(Project $project, Component $component): RedirectResponse
    {
        $this->ensureProjectLead($project);
        abort_unless($component->project_id === $project->id, 404);

        $component->delete();

        return back()->with('success', __('messages.component_deleted'));
    }
}
