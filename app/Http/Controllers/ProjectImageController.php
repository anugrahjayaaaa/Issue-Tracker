<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProjectImageController extends Controller
{
    /**
     * Upload an image pasted/dropped into a project's rich-text description.
     * Scoped to storage/app/public/projects/{project.folder}/description/{file}.
     */
    public function store(Request $request, Project $project)
    {
        $request->validate([
            'file' => ['required', 'image', 'max:5120'], // ponytail: 5MB cap; tune via plan limit later
        ]);

        $path = $request->file('file')->store(
            'projects/'.$project->folder().'/description',
            'public'
        );

        return response()->json([
            'location' => Storage::disk('public')->url($path),
        ]);
    }
}
