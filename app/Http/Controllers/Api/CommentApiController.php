<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Issue;
use App\Models\ProjectMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentApiController extends Controller
{
    public function __construct(private Comment $model) {}

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

    public function index(Request $request, Issue $issue): JsonResponse
    {
        $this->authorizeMember($issue, $request);

        $comments = $this->model->where('issue_id', $issue->id)
            ->with('user')
            ->orderByDesc('id')
            ->paginate(20);

        return response()->json(CommentResource::collection($comments)->response()->getData(true));
    }

    public function show(Request $request, Issue $issue, Comment $comment): JsonResponse
    {
        $this->authorizeMember($issue, $request);
        abort_unless($comment->issue_id === $issue->id, 404);

        return response()->json(new CommentResource($comment->load('user')));
    }
}
