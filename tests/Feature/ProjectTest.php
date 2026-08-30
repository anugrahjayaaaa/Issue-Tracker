<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    private function projectManager(): User
    {
        // seed base perms, then grant project.manage
        $this->seed();
        $user = User::factory()->create();
        $user->givePermissionTo('project.manage');

        return $user;
    }

    public function test_manager_can_create_project_and_becomes_lead(): void
    {
        $user = $this->projectManager();

        $response = $this->actingAs($user)->post(route('projects.store'), [
            'key' => 'HEL',
            'name' => 'Helpdesk',
            'description' => 'Internal helpdesk',
        ]);

        $response->assertRedirect(route('projects.index'));
        $this->assertDatabaseHas('projects', ['key' => 'HEL', 'name' => 'Helpdesk']);
        // creator auto-lead
        $project = Project::where('key', 'HEL')->first();
        $this->assertTrue(ProjectMember::isLead($user, $project));
    }

    public function test_key_must_be_unique_and_uppercase(): void
    {
        $user = $this->projectManager();
        Project::create(['key' => 'HEL', 'name' => 'A', 'owner_id' => $user->id]);

        $this->actingAs($user)->post(route('projects.store'), [
            'key' => 'HEL', 'name' => 'B',
        ])->assertInvalid('key');
    }

    public function test_member_can_be_added_with_role_and_scope_gate_works(): void
    {
        $manager = $this->projectManager();
        $project = Project::create(['key' => 'HEL', 'name' => 'A', 'owner_id' => $manager->id]);
        $member = User::factory()->create();

        $this->actingAs($manager)->post(route('projects.members.store', $project), [
            'user_id' => $member->id,
            'role' => 'member',
        ])->assertRedirect();

        $this->assertTrue(ProjectMember::hasRole($member, $project, ['member', 'lead']));
        $this->assertFalse(ProjectMember::hasRole($member, $project, ['lead']));
    }

    public function test_non_manager_cannot_view_create_form(): void
    {
        $this->seed();
        $viewer = User::factory()->create(); // no project.manage

        $this->actingAs($viewer)->get(route('projects.create'))
            ->assertForbidden();
    }
}
