<?php

namespace App\Http\Controllers;

use App\Http\Requests\Project\ProjectMemberStoreRequest;
use App\Http\Requests\Project\ProjectMemberUpdateRequest;
use App\Models\Project;
use App\Models\ProjectMember;
use Illuminate\Http\RedirectResponse;

class ProjectMemberController extends Controller
{
    public function store(ProjectMemberStoreRequest $request, Project $project): RedirectResponse
    {
        $validated = $request->validated();

        // upsert: same user re-added → update role (unique constraint safe)
        ProjectMember::updateOrCreate(
            ['project_id' => $project->id, 'user_id' => $validated['user_id']],
            ['role' => $validated['role']]
        );

        return redirect()->route('projects.show', $project)->with('success', __('messages.member_added'));
    }

    public function update(ProjectMemberUpdateRequest $request, Project $project, ProjectMember $member): RedirectResponse
    {
        $member->update($request->validated());

        return redirect()->route('projects.show', $project)->with('success', __('messages.member_role_updated'));
    }

    public function destroy(Project $project, ProjectMember $member): RedirectResponse
    {
        $member->delete();

        return redirect()->route('projects.show', $project)->with('success', __('messages.member_removed'));
    }
}
