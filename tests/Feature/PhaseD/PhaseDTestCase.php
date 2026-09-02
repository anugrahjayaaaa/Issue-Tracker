<?php

namespace Tests\Feature\PhaseD;

use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Base test case for Phase D features. Provides a seeded project with a
 * manager (lead) and one other member at a configurable role.
 */
abstract class PhaseDTestCase extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array{0:User,1:Project,2:User} [manager, project, user]
     */
    protected function setupProject(string $role = ProjectMember::ROLE_MEMBER): array
    {
        $this->seed();
        $manager = User::factory()->create();
        $manager->givePermissionTo(['project.manage', 'issue.view', 'issue.create', 'issue.edit', 'comment.create']);
        $project = Project::create(['key' => 'HEL', 'name' => 'Tracker', 'owner_id' => $manager->id]);
        ProjectMember::create(['project_id' => $project->id, 'user_id' => $manager->id, 'role' => ProjectMember::ROLE_LEAD]);

        $user = User::factory()->create();
        $user->givePermissionTo(['issue.view', 'issue.create', 'issue.edit', 'comment.create']);
        ProjectMember::create(['project_id' => $project->id, 'user_id' => $user->id, 'role' => $role]);

        return [$manager, $project, $user];
    }
}
