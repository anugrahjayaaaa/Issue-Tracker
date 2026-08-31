<?php

namespace App\Http\Controllers;

use App\Http\Requests\Project\ProjectImageRequest;
use App\Models\Project;
use Illuminate\Support\Facades\Storage;

class ProjectImageController extends Controller
{
    /**
     * Upload an image pasted/dropped into a project's rich-text description.
     * Scoped to storage/app/public/projects/{project.folder}/description/{file}.
     */
    public function store(ProjectImageRequest $request, Project $project)
    {
        $path = $request->file('file')->store(
            'projects/'.$project->folder().'/description',
            'public'
        );

        return response()->json([
            'location' => Storage::disk('public')->url($path),
        ]);
    }
}
