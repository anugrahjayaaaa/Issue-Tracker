<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Issue;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;
use Illuminate\Http\Request;

trait AuthorizesProject
{
    private function ensureProjectReader(Project|Issue $projectOrIssue, ?User $user = null): void
    {
        $user ??= auth()->user();
        $project = $projectOrIssue instanceof Issue ? $projectOrIssue->project : $projectOrIssue;

        abort_unless(
            ProjectMember::hasRole($user, $project, [
                ProjectMember::ROLE_LEAD,
                ProjectMember::ROLE_MEMBER,
                ProjectMember::ROLE_VIEWER,
            ]),
            403
        );
    }

    private function ensureProjectLead(Project|Issue $projectOrIssue, ?User $user = null): void
    {
        $user ??= auth()->user();
        $project = $projectOrIssue instanceof Issue ? $projectOrIssue->project : $projectOrIssue;

        abort_unless(
            $user->can('project.manage')
            || ProjectMember::hasRole($user, $project, [ProjectMember::ROLE_LEAD]),
            403
        );
    }

    protected function projectUser(Request $request): User
    {
        return $request->user() ?? auth()->user();
    }
}
