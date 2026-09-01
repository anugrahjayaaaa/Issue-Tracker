<?php

namespace App\Http\Controllers;

use App\Http\Requests\Sprint\StoreSprintRequest;
use App\Http\Requests\Sprint\UpdateSprintRequest;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\Sprint;
use App\Http\Controllers\Concerns\AuthorizesProject;
use Illuminate\Http\Request;

class SprintController extends Controller
{
    use AuthorizesProject;

    public function index(Request $request, Project $project)
    {
        $this->ensureProjectReader($project);

        $sprints = Sprint::where('project_id', $project->id)
            ->orderByDesc('ends_at')
            ->get(['id', 'name', 'goal', 'starts_at', 'ends_at']);

        return response()->json($sprints);
    }

    public function store(StoreSprintRequest $request, Project $project)
    {
        $this->ensureProjectLead($project);

        $sprint = Sprint::create(array_merge(
            $request->validated(),
            ['project_id' => $project->id]
        ));

        return response()->json($sprint, 201);
    }

    public function show(Request $request, Project $project, Sprint $sprint)
    {
        $this->ensureProjectReader($project);

        return response()->json($sprint);
    }

    public function update(UpdateSprintRequest $request, Project $project, Sprint $sprint)
    {
        $this->ensureProjectLead($project);
        abort_if($sprint->project_id !== $project->id, 404);

        $sprint->update($request->validated());

        return response()->json($sprint);
    }

    public function destroy(Request $request, Project $project, Sprint $sprint)
    {
        $this->ensureProjectLead($project);
        abort_if($sprint->project_id !== $project->id, 404);

        $sprint->delete();

        return response()->json(null, 204);
    }
}
