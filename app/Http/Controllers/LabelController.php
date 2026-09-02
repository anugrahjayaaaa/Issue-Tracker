<?php

namespace App\Http\Controllers;

use App\Http\Requests\Label\LabelStoreRequest;
use App\Http\Requests\Label\LabelUpdateRequest;
use App\Models\Label;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;

class LabelController extends Controller
{
    public function store(LabelStoreRequest $request, Project $project): RedirectResponse
    {
        $data = $request->validated();
        // ponytail: form field 'label_name' maps to model 'name'
        $data['name'] = $data['label_name'];
        unset($data['label_name']);
        $project->labels()->create($data);

        return back()->with('success', __('messages.label_created'));
    }

    public function update(LabelUpdateRequest $request, Project $project, Label $label): RedirectResponse
    {
        abort_unless($label->project_id === $project->id, 404);

        $data = $request->validated();
        if (array_key_exists('label_name', $data)) {
            $data['name'] = $data['label_name'];
            unset($data['label_name']);
        }
        $label->update($data);

        return back()->with('success', __('messages.label_updated'));
    }

    public function destroy(Project $project, Label $label): RedirectResponse
    {
        abort_unless($label->project_id === $project->id, 404);
        $label->delete();

        return back()->with('success', __('messages.label_deleted'));
    }
}
