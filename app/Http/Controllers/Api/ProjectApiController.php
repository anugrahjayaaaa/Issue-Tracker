<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Models\ProjectMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectApiController extends Controller
{
    public function __construct(private Project $model) {}

    private function isLeadMemberOrViewer(Project $project, \Illuminate\Contracts\Auth\Authenticatable $user): bool
    {
        return ProjectMember::hasRole($user, $project, [
            ProjectMember::ROLE_LEAD,
            ProjectMember::ROLE_MEMBER,
            ProjectMember::ROLE_VIEWER,
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = $this->model->whereHas('members', fn ($q) => $q->where('user_id', $user->id))
            ->withCount('labels');

        if ($request->filled('q')) {
            $term = $request->string('q');
            $query->where(fn ($q) => $q->where('name', 'like', '%'.$term.'%')
                ->orWhere('key', 'like', '%'.$term.'%'));
        }

        return response()->json(ProjectResource::collection($query->orderBy('name')->paginate(20))->response()->getData(true));
    }

    public function show(Request $request, Project $project): JsonResponse
    {
        $this->authorizeView($request, $project);
        $project->load('labels');

        return response()->json(new ProjectResource($project));
    }

    public function store(\App\Http\Requests\Api\Project\ProjectApiStoreRequest $request): JsonResponse
    {
        $project = $this->model->create(array_merge($request->validated(), ['owner_id' => $request->user()->id]));
        $project->labels()->createMany([
            ['name' => 'Bug', 'color' => '#dc3545'],
            ['name' => 'Feature', 'color' => '#0d6efd'],
            ['name' => 'Task', 'color' => '#198754'],
        ]);

        return response()->json(new ProjectResource($project->load('labels')), 201);
    }

    public function update(\App\Http\Requests\Api\Project\ProjectApiUpdateRequest $request, Project $project): JsonResponse
    {
        $project->update($request->validated());

        return response()->json(new ProjectResource($project->load('labels')));
    }

    public function destroy(Request $request, Project $project): JsonResponse
    {
        $this->authorizeManage($request, $project);
        $project->delete();

        return response()->json(['message' => __('messages.project_deleted')]);
    }

    private function authorizeView(Request $request, Project $project): void
    {
        abort_unless($this->isLeadMemberOrViewer($project, $request->user()), 403);
    }

    private function authorizeManage(Request $request, Project $project): void
    {
        abort_unless(ProjectMember::hasRole($request->user(), $project, [ProjectMember::ROLE_LEAD]), 403);
    }
}
