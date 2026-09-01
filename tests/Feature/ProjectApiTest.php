<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectApiTest extends TestCase
{
    use RefreshDatabase;

    private function manager(): User
    {
        $this->seed();
        $user = User::factory()->create();
        $user->givePermissionTo(['project.manage']);

        return $user;
    }

    public function test_creates_project_via_api(): void
    {
        $user = $this->manager();

        $this->actingAs($user)->postJson('/api/v1/projects', [
            'key' => 'HEL',
            'name' => 'Helpdesk API',
        ])->assertCreated()
          ->assertJsonPath('name', 'Helpdesk API')
          ->assertJsonPath('key', 'HEL');
    }

    public function test_project_response_contains_expected_fields(): void
    {
        $user = $this->manager();
        $project = Project::create(['key' => 'X', 'name' => 'X', 'owner_id' => $user->id]);
        ProjectMember::create([
            'project_id' => $project->id,
            'user_id' => $user->id,
            'role' => ProjectMember::ROLE_LEAD,
        ]);

        $this->actingAs($user)->getJson("/api/v1/projects/{$project->id}")
            ->assertOk()
            ->assertJsonPath('name', 'X')
            ->assertJsonPath('key', 'X');
    }
}
