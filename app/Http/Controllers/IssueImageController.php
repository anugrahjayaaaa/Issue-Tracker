<?php

namespace App\Http\Controllers;

use App\Http\Requests\Issue\IssueImageRequest;
use App\Models\Issue;
use Illuminate\Support\Facades\Storage;

class IssueImageController extends Controller
{
    /**
     * Upload an image pasted/dropped into an issue's rich-text description.
     * Scoped to storage/app/public/projects/{project.folder}/issues/{code}/description/{file}.
     */
    public function store(IssueImageRequest $request, Issue $issue)
    {
        $path = $request->file('file')->store(
            'projects/'.$issue->project->folder().'/issues/'.$issue->code.'/description',
            'public'
        );

        return response()->json([
            'location' => Storage::disk('public')->url($path),
        ]);
    }
}
