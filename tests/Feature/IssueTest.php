<?php

namespace Tests\Feature;

use App\Models\Issue;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IssueTest extends TestCase
{
    use RefreshDatabase;

    private function member(User $user, Project $project, string $role = ProjectMember::ROLE_MEMBER): void
    {
        ProjectMember::create(['project_id' => $project->id, 'user_id' => $user->id, 'role' => $role]);
    }

    private function seedAndProject(string $role = ProjectMember::ROLE_MEMBER): array
    {
        $this->seed();
        $manager = User::factory()->create();
        $manager->givePermissionTo(['project.manage', 'issue.create', 'issue.edit', 'issue.delete', 'issue.view']);
        $project = Project::create(['key' => 'HEL', 'name' => 'Helpdesk', 'owner_id' => $manager->id]);
        $this->member($manager, $project, ProjectMember::ROLE_LEAD);

        $user = User::factory()->create();
        $user->givePermissionTo(['issue.create', 'issue.edit', 'issue.delete', 'issue.view']);
        $this->member($user, $project, $role);

        return [$manager, $project, $user];
    }

    public function test_member_can_create_issue_with_generated_code(): void
    {
        [$manager, $project, $user] = $this->seedAndProject();

        $response = $this->actingAs($user)->post(route('issues.store'), [
            'project_id' => $project->id,
            'title' => 'Login broken',
            'type' => 'bug',
            'status' => 'open',
            'priority' => 'high',
        ]);

        $response->assertRedirect(route('issues.index', ['project_id' => $project->id]));
        $this->assertDatabaseHas('issues', ['project_id' => $project->id, 'code' => 'HEL-1', 'title' => 'Login broken']);
    }

    public function test_issue_code_increments_per_project(): void
    {
        [$manager, $project, $user] = $this->seedAndProject();
        $this->actingAs($user)->post(route('issues.store'), ['project_id' => $project->id, 'title' => 'A', 'type' => 'task', 'status' => 'open', 'priority' => 'low']);
        $this->actingAs($user)->post(route('issues.store'), ['project_id' => $project->id, 'title' => 'B', 'type' => 'task', 'status' => 'open', 'priority' => 'low']);

        $this->assertDatabaseHas('issues', ['code' => 'HEL-1']);
        $this->assertDatabaseHas('issues', ['code' => 'HEL-2']);
    }

    public function test_drag_change_status_updates_status_and_order(): void
    {
        [$manager, $project, $user] = $this->seedAndProject();
        $issue = Issue::create([
            'project_id' => $project->id, 'code' => 'HEL-1', 'title' => 'X',
            'type' => 'task', 'status' => 'open', 'priority' => 'low',
            'reporter_id' => $user->id,
        ]);

        $this->actingAs($user)->post(route('issues.status', $issue), [
            'status' => 'done', 'order' => 3,
        ])->assertRedirect();

        $issue->refresh();
        $this->assertEquals('done', $issue->status);
        $this->assertEquals(3, $issue->order);
    }

    public function test_show_renders_with_timeline_and_comments(): void
    {
        [$manager, $project, $user] = $this->seedAndProject();
        $issue = Issue::create([
            'project_id' => $project->id, 'code' => 'HEL-1', 'title' => 'X',
            'type' => 'task', 'status' => 'open', 'priority' => 'low', 'reporter_id' => $user->id,
        ]);
        Comment::create(['issue_id' => $issue->id, 'user_id' => $user->id, 'body' => 'hi']);

        $this->actingAs($user)
            ->get(route('issues.show', $issue))
            ->assertOk()
            ->assertSee('Activity')
            ->assertSee('hi');
    }

    public function test_non_member_cannot_view_board(): void
    {
        $this->seed();
        $outsider = User::factory()->create();
        $outsider->givePermissionTo('issue.view');
        $manager = User::factory()->create();
        $manager->givePermissionTo('project.manage');
        $project = Project::create(['key' => 'HEL', 'name' => 'X', 'owner_id' => $manager->id]);

        $this->actingAs($outsider)->get(route('issues.board', ['project_id' => $project->id]))
            ->assertForbidden();
    }

    public function test_rich_text_description_is_sanitized(): void
    {
        [$manager, $project, $user] = $this->seedAndProject();
        $this->actingAs($user)->post(route('issues.store'), [
            'project_id' => $project->id, 'title' => 'X', 'type' => 'task',
            'status' => 'open', 'priority' => 'low',
            'description' => '<script>alert(1)</script><p>ok <strong>bold</strong></p>',
        ]);

        $issue = Issue::where('code', 'HEL-1')->first();
        $this->assertStringNotContainsString('<script>', $issue->description);
        $this->assertStringContainsString('<strong>bold</strong>', $issue->description);
    }
}
