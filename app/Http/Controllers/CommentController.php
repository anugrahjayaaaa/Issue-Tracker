<?php

namespace App\Http\Controllers;

use App\Http\Requests\Comment\CommentAttachRequest;
use App\Http\Requests\Comment\CommentStoreRequest;
use App\Http\Requests\Comment\CommentUpdateRequest;
use App\Models\Attachment;
use App\Models\Comment;
use App\Models\Issue;
use App\Models\ProjectMember;
use App\Models\User;
use App\Notifications\Mentioned;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class CommentController extends Controller
{
    private function abortIfNotReader(Issue $issue): void
    {
        abort_unless(
            ProjectMember::hasRole(auth()->user(), $issue->project, [
                ProjectMember::ROLE_LEAD, ProjectMember::ROLE_MEMBER, ProjectMember::ROLE_VIEWER,
            ]),
            403
        );
    }

    public function store(CommentStoreRequest $request, Issue $issue): RedirectResponse
    {
        $comment = Comment::create([
            'issue_id' => $issue->id,
            'user_id' => $request->user()->id,
            'body' => $request->input('body'),
        ]);

        // notify mentioned users (by @username)
        $mentioned = User::whereIn('username', parseMentions($request->input('body')))->get();
        foreach ($mentioned as $mentionedUser) {
            if ($mentionedUser->id !== $request->user()->id) {
                $mentionedUser->notify(new Mentioned($comment));
            }
        }

        return redirect()->route('issues.show', $issue)
            ->with('success', __('messages.comment_added'));
    }

    public function update(CommentUpdateRequest $request, Comment $comment): RedirectResponse
    {
        $oldBody = $comment->body;
        $newBody = $request->input('body');

        pruneUnusedImages($oldBody, $newBody); // delete images dropped from the comment
        $comment->update(['body' => $newBody]);

        return redirect()->route('issues.show', $comment->issue)
            ->with('success', __('messages.comment_updated'));
    }

    public function destroy(Comment $comment): RedirectResponse
    {
        $issue = $comment->issue;
        // owner or lead
        abort_unless(
            $comment->user_id === auth()->id()
            || ProjectMember::isLead(auth()->user(), $issue->project),
            403
        );

        deleteStorageFolder(
            'projects/'.$issue->project->folder().'/issues/'.$issue->code.'/comments/'.$comment->id
        );
        $comment->delete();

        return redirect()->route('issues.show', $issue)
            ->with('success', __('messages.comment_deleted'));
    }

    public function attach(CommentAttachRequest $request, Issue $issue): RedirectResponse
    {
        $path = $request->file('file')->store('comments', 'public');

        $attachment = Attachment::create([
            'issue_id' => $issue->id,
            'user_id' => $request->user()->id,
            'path' => $path,
            'mime' => $request->file('file')->getClientMimeType(),
            'size' => $request->file('file')->getSize(),
        ]);

        return redirect()->route('issues.show', $issue)
            ->with('success', __('messages.attachment_added'));
    }
}
