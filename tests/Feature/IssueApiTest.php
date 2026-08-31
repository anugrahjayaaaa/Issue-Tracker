<?php

namespace Tests\Feature;

use App\Models\Issue;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IssueApiTest extends TestCase
{
    use RefreshDatabase;

    private function seedProject(): array
    {
        $this->seed();
        $user = User::factory()->create();
        $user->givePermissionTo([
            'project.manage',
            'issue.view',
            'issue.create',
            'issue.edit',
            'issue.delete',
        ]);

        $project = Project::create(['key' => 'HEL', 'name' => 'Helpdesk', 'owner_id' => $user->id]);
        ProjectMember::create([
            'project_id' => $project->id,
            'user_id' => $user->id,
            'role' => ProjectMember::ROLE_LEAD,
        ]);

        return [$user, $project];
    }

    private function makeIssue(Project $project, array $overrides = []): Issue
    {
        return Issue::create(array_merge([
            'project_id' => $project->id,
            'code' => 'HEL-1',
            'title' => 'API issue',
            'type' => 'task',
            'status' => 'open',
            'priority' => 'medium',
            'reporter_id' => $project->owner_id,
        ], $overrides));
    }

    public function test_lists_issues_scoped_to_project(): void
    {
        [$user, $project] = $this->seedProject();
        $this->makeIssue($project, ['title' => 'API issue']);

        $this->actingAs($user)->getJson("/api/v1/projects/{$project->id}/issues")
            ->assertOk()
            ->assertJsonPath('data.0.title', 'API issue');
    }

    public function test_creates_issue_via_api(): void
    {
        [$manager, $project] = $this->seedProject();

        $this->actingAs($manager)->postJson("/api/v1/projects/{$project->id}/issues", [
            'project_id' => $project->id,
            'title' => 'API issue',
            'description' => 'desc',
            'type' => 'task',
            'priority' => 'medium',
        ])->assertCreated()
          ->assertJsonPath('title', 'API issue')
          ->assertJsonPath('code', 'HEL-1');
    }

    public function test_updates_issue_via_api(): void
    {
        [$manager, $project] = $this->seedProject();
        $issue = $this->makeIssue($project);

        $this->actingAs($manager)->putJson("/api/v1/projects/{$project->id}/issues/{$issue->id}", [
            'project_id' => $project->id,
            'title' => 'Updated API issue',
        ])->assertOk()
          ->assertJsonPath('title', 'Updated API issue');
    }

    public function test_deletes_issue_via_api(): void
    {
        [$manager, $project] = $this->seedProject();
        $issue = $this->makeIssue($project);

        $this->actingAs($manager)->deleteJson("/api/v1/projects/{$project->id}/issues/{$issue->id}")
            ->assertOk()
            ->assertJsonPath('message', trans('messages.issue_deleted'));

        $this->assertDatabaseMissing('issues', ['id' => $issue->id]);
    }
}
