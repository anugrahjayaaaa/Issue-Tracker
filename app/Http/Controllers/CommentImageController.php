<?php

namespace App\Http\Controllers;

use App\Http\Requests\Comment\CommentImageRequest;
use App\Models\Comment;
use Illuminate\Support\Facades\Storage;

class CommentImageController extends Controller
{
    /**
     * Upload an image pasted/dropped into a comment's rich text.
     * Scoped to projects/{project.folder}/issues/{issue.code}/comments/{id}/description/{file}.
     */
    public function store(CommentImageRequest $request, Comment $comment)
    {
        $issue = $comment->issue;
        $path = $request->file('file')->store(
            'projects/'.$issue->project->folder().'/issues/'.$issue->code.'/comments/'.$comment->id.'/description',
            'public'
        );

        return response()->json([
            'location' => Storage::disk('public')->url($path),
        ]);
    }
}
