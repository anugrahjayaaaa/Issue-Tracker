<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Issue\IssueApiStoreRequest;
use App\Http\Requests\Api\Issue\IssueApiUpdateRequest;
use App\Http\Resources\IssueResource;
use App\Models\Issue;
use App\Models\Project;
use App\Models\ProjectMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IssueApiController extends Controller
{
    public function __construct(private Issue $model) {}

    private function authorizeMember(Issue $issue, Request $request): void
    {
        abort_unless(
            ProjectMember::hasRole($request->user(), $issue->project, [
                ProjectMember::ROLE_LEAD,
                ProjectMember::ROLE_MEMBER,
                ProjectMember::ROLE_VIEWER,
            ]),
            403
        );
    }

    public function index(Request $request, Project $project): JsonResponse
    {
        if (! ProjectMember::hasRole($request->user(), $project, [
            ProjectMember::ROLE_LEAD, ProjectMember::ROLE_MEMBER, ProjectMember::ROLE_VIEWER,
        ])) {
            abort(403);
        }

        $query = $this->model->where('project_id', $project->id)
            ->with(['assignee', 'reporter', 'project', 'labels'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('priority'), fn ($q) => $q->where('priority', $request->string('priority')))
            ->when($request->filled('assignee_id'), fn ($q) => $q->where('assignee_id', $request->integer('assignee_id')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->string('q');
                $q->where('title', 'like', '%'.$term.'%')
                  ->orWhere('code', 'like', '%'.$term.'%');
            })
            ->when($request->filled('sort'), fn ($q) => $q->orderBy($request->string('sort'), $request->filled('dir') ? $request->string('dir') : 'asc'));

        return response()->json(IssueResource::collection($query->paginate(20))->response()->getData(true));
    }

    public function show(Request $request, Project $project, Issue $issue): JsonResponse
    {
        $this->authorizeMember($issue, $request);
        $issue->load('assignee', 'reporter', 'parent', 'children', 'labels', 'watchers', 'comments.user', 'comments.attachments', 'attachments');

        return response()->json(new IssueResource($issue));
    }

    public function store(IssueApiStoreRequest $request, Project $project): JsonResponse
    {
        abort_unless(
            ProjectMember::hasRole($request->user(), $project, [
                ProjectMember::ROLE_LEAD, ProjectMember::ROLE_MEMBER,
            ]), 403
        );

        if ($request->filled('parent_id') && (new Issue())->wouldCreateCycle($request->input('parent_id'))) {
            return response()->json(['message' => __('messages.invalid_parent')], 422);
        }

        $issue = new Issue($request->validated());
        $issue->code = $project->nextIssueCode();
        $issue->reporter_id = $request->user()->id;
        $issue->status = $request->input('status') ?? $project->statuses()->orderBy('order')->value('key');
        $issue->save();
        $issue->labels()->sync($request->input('labels', []));

        return response()->json(new IssueResource($issue->load('assignee', 'reporter', 'labels', 'watchers')), 201);
    }

    public function update(IssueApiUpdateRequest $request, Project $project, Issue $issue): JsonResponse
    {
        abort_unless(ProjectMember::hasRole($request->user(), $issue->project, [
            ProjectMember::ROLE_LEAD, ProjectMember::ROLE_MEMBER,
        ]), 403);

        if ($request->filled('status') && ! $issue->canTransitionTo($request->input('status'))) {
            return response()->json(['message' => __('messages.status_transition_not_allowed')], 422);
        }
        if ($request->filled('parent_id') && $issue->wouldCreateCycle($request->input('parent_id'))) {
            return response()->json(['message' => __('messages.invalid_parent')], 422);
        }

        $oldAssignee = $issue->assignee_id;
        $issue->update($request->validated());
        $issue->labels()->sync($request->input('labels', []));

        if ($issue->assignee_id && $issue->assignee_id !== $oldAssignee) {
            $issue->syncWatchers([$issue->assignee_id]);
            if ($issue->assignee_id !== $request->user()->id && $issue->assignee) {
                $issue->assignee->notify(new \App\Notifications\IssueAssigned($issue));
            }
        }

        return response()->json(new IssueResource($issue->load('assignee', 'reporter', 'labels', 'watchers')));
    }

    public function destroy(Request $request, Project $project, Issue $issue): JsonResponse
    {
        abort_unless(
            ProjectMember::hasRole($request->user(), $issue->project, [ProjectMember::ROLE_LEAD, ProjectMember::ROLE_MEMBER]),
            403
        );
        $issue->delete();

        return response()->json(['message' => __('messages.issue_deleted')]);
    }
}
