<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesProject;
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
    use AuthorizesProject;

    public function store(CommentStoreRequest $request, Issue $issue): RedirectResponse
    {
        $this->ensureProjectReader($issue);

        $comment = Comment::create([
            'issue_id' => $issue->id,
            'user_id' => $request->user()->id,
            'body' => $request->input('body'),
            'parent_id' => $request->input('parent_id'),
        ]);

        // Decision #3: commenter auto-subscribes to the issue.
        $issue->syncWatchers([$request->user()->id]);

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
        $this->ensureProjectReader($comment->issue);

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
        $this->ensureProjectReader($issue);
        // owner only (leads may not delete others' comments)
        abort_unless($comment->user_id === auth()->id(), 403);

        deleteStorageFolder(
            'projects/'.$issue->project->folder().'/issues/'.$issue->code.'/comments/'.$comment->id
        );
        $comment->delete();

        return redirect()->route('issues.show', $issue)
            ->with('success', __('messages.comment_deleted'));
    }

    public function attach(CommentAttachRequest $request, Issue $issue): RedirectResponse
    {
        $this->ensureProjectReader($issue);

        // ponytail: store in the issue's scoped folder; comment_id set later (Phase B)
        // when the route gains a {comment} segment. File lives under project/issue/attachments.
        $folder = 'projects/'.$issue->project->folder().'/issues/'.$issue->code.'/attachments';
        $path = $request->file('file')->store($folder, 'public');

        Attachment::create([
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
