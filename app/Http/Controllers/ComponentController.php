<?php

namespace App\Http\Controllers;

use App\Http\Requests\Component\StoreComponentRequest;
use App\Http\Requests\Component\UpdateComponentRequest;
use App\Http\Controllers\Concerns\AuthorizesProject;
use App\Models\Component;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;

class ComponentController extends Controller
{
    use AuthorizesProject;

    public function store(StoreComponentRequest $request, Project $project): RedirectResponse
    {
        $this->ensureProjectLead($project);

        $data = $request->validated();
        // ponytail: form field 'component_name' maps to model 'name'
        $data['name'] = $data['component_name'];
        unset($data['component_name']);
        $project->components()->create($data);

        return back()->with('success', __('messages.component_created'));
    }

    public function update(UpdateComponentRequest $request, Project $project, Component $component): RedirectResponse
    {
        $this->ensureProjectLead($project);
        abort_unless($component->project_id === $project->id, 404);

        $data = $request->validated();
        if (array_key_exists('component_name', $data)) {
            $data['name'] = $data['component_name'];
            unset($data['component_name']);
        }
        $component->update($data);

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
