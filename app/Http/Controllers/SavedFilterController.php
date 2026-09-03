<?php

namespace App\Http\Controllers;

use App\Http\Requests\SavedFilterStoreRequest;
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
        $user = $request->user();

        $filters = SavedFilter::query()
            ->where('project_id', $project->id)
            ->when(! $user->can('project.manage'), function ($q) use ($user) {
                $q->where(function ($q2) use ($user) {
                    $q2->where('user_id', $user->id)->orWhere('is_public', true);
                });
            })
            ->orderBy('name')
            ->get(['id', 'name', 'filter_params', 'is_public']);

        return response()->json($filters);
    }

    public function store(SavedFilterStoreRequest $request, Project $project)
    {
        $this->ensureMember($project);

        $filter = SavedFilter::create([
            'user_id' => $request->user()->id,
            'project_id' => $project->id,
            'name' => $request->input('name'),
            'filter_params' => $request->query(),
            'is_public' => (bool) $request->input('is_public', false),
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
