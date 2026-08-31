<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSavedFilterRequest;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\SavedFilter;
use Illuminate\Http\Request;

class SavedFilterController extends Controller
{
    private function ensureMember(Project $project): void
    {
        abort_unless(
            ProjectMember::hasRole(auth()->user(), $project, [
                ProjectMember::ROLE_LEAD,
                ProjectMember::ROLE_MEMBER,
                ProjectMember::ROLE_VIEWER,
            ]),
            403
        );
    }

    public function index(Request $request, Project $project)
    {
        $this->ensureMember($project);

        $filters = SavedFilter::where('user_id', $request->user()->id)
            ->where('project_id', $project->id)
            ->orderBy('name')
            ->get(['id', 'name', 'filter_params']);

        return response()->json($filters);
    }

    public function store(StoreSavedFilterRequest $request, Project $project)
    {
        $this->ensureMember($project);

        $filter = SavedFilter::create([
            'user_id' => $request->user()->id,
            'project_id' => $project->id,
            'name' => $request->input('name'),
            'filter_params' => $request->query(),
        ]);

        return response()->json($filter, 201);
    }

    public function destroy(Request $request, Project $project, SavedFilter $filter)
    {
        $this->ensureMember($project);
        abort_unless($filter->user_id === $request->user()->id, 403);
        abort_unless($filter->project_id === $project->id, 404);

        $filter->delete();

        return response()->json(null, 204);
    }
}
