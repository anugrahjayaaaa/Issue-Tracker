<?php namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\Sprint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SprintTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_create_sprint(): void
    {
        $this->seed();
        $manager = User::factory()->create();
        $manager->givePermissionTo(['project.manage']);
        $project = Project::create(['key' => 'HEL', 'name' => 'Helpdesk', 'owner_id' => $manager->id]);
        ProjectMember::create(['project_id' => $project->id, 'user_id' => $manager->id, 'role' => ProjectMember::ROLE_LEAD]);

        $this->actingAs($manager)
            ->postJson(route('projects.sprints.store', $project), ['name' => 'S1', 'ends_at' => now()])
            ->assertCreated()
            ->assertJson(['name' => 'S1']);

        $this->assertDatabaseHas('sprints', ['project_id' => $project->id, 'name' => 'S1']);
    }

    public function test_member_can_list_sprints(): void
    {
        $this->seed();
        $manager = User::factory()->create();
        $manager->givePermissionTo(['project.manage']);
        $member = User::factory()->create();
        $member->givePermissionTo(['issue.view']);
        $project = Project::create(['key' => 'HEL', 'name' => 'Helpdesk', 'owner_id' => $manager->id]);
        ProjectMember::create(['project_id' => $project->id, 'user_id' => $manager->id, 'role' => ProjectMember::ROLE_LEAD]);
        ProjectMember::create(['project_id' => $project->id, 'user_id' => $member->id, 'role' => ProjectMember::ROLE_MEMBER]);
        $sprint = Sprint::create(['project_id' => $project->id, 'name' => 'S1', 'ends_at' => now()]);

        $this->actingAs($member)
            ->getJson(route('projects.sprints.index', $project))
            ->assertOk()
            ->assertJsonPath('0.id', $sprint->id);
    }

    public function test_non_member_cannot_access_sprints(): void
    {
        $this->seed();
        $manager = User::factory()->create();
        $manager->givePermissionTo(['project.manage']);
        $outsider = User::factory()->create();
        $outsider->givePermissionTo(['issue.view']);
        $project = Project::create(['key' => 'HEL', 'name' => 'Helpdesk', 'owner_id' => $manager->id]);
        ProjectMember::create(['project_id' => $project->id, 'user_id' => $manager->id, 'role' => ProjectMember::ROLE_LEAD]);

        $this->actingAs($outsider)
            ->getJson(route('projects.sprints.index', $project))
            ->assertForbidden();
    }
}
