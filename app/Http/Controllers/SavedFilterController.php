<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSavedFilterRequest;
use App\Models\SavedFilter;
use Illuminate\Http\Request;

class SavedFilterController extends Controller
{
    public function index(Request $request, $projectId)
    {
        $filters = SavedFilter::where('user_id', $request->user()->id)
            ->where('project_id', $projectId)
            ->orderBy('name')
            ->get(['id', 'name', 'filter_params']);

        return response()->json($filters);
    }

    public function store(StoreSavedFilterRequest $request)
    {
        $filter = SavedFilter::create([
            'user_id' => $request->user()->id,
            'project_id' => $request->input('project_id'),
            'name' => $request->input('name'),
            'filter_params' => $request->query(),
        ]);

        return response()->json($filter, 201);
    }

    public function destroy(Request $request, SavedFilter $filter)
    {
        abort_unless($filter->user_id === $request->user()->id, 403);
        $filter->delete();

        return response()->json(null, 204);
    }
}
