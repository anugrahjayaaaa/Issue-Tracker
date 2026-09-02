<?php

namespace Tests\Feature;

use App\Models\AutomationRule;
use App\Models\Issue;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\Sprint;
use App\Models\Status;
use App\Models\StatusTransition;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhaseDTest extends TestCase
{
    use RefreshDatabase;

    private function setupProject(string $role = ProjectMember::ROLE_MEMBER): array
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

    /** @test */
    public function test_backlog_view_renders_for_member(): void
    {
        [$manager, $project, $user] = $this->setupProject();
        $issue = Issue::create([
            'project_id' => $project->id, 'code' => 'HEL-1', 'title' => 'Backlog item',
            'type' => 'task', 'status' => 'open', 'priority' => 'low', 'reporter_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->get(route('projects.backlog', $project))
            ->assertOk()
            ->assertSee('Backlog')
            ->assertSee('HEL-1');
    }

    /** @test */
    public function test_backlog_view_forbidden_for_non_member(): void
    {
        [$manager, $project, $user] = $this->setupProject();
        $outsider = User::factory()->create();
        $outsider->givePermissionTo('issue.view');

        $this->actingAs($outsider)
            ->get(route('projects.backlog', $project))
            ->assertForbidden();
    }

    /** @test */
    public function test_sprint_assignment_via_update_sprint(): void
    {
        [$manager, $project, $user] = $this->setupProject();
        $sprint = Sprint::create([
            'project_id' => $project->id, 'name' => 'S1',
            'starts_at' => now(), 'ends_at' => now()->addDays(7),
        ]);
        $issue = Issue::create([
            'project_id' => $project->id, 'code' => 'HEL-1', 'title' => 'Task',
            'type' => 'task', 'status' => 'open', 'priority' => 'low', 'reporter_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->putJson(route('issues.sprint', $issue), ['sprint_id' => $sprint->id])
            ->assertOk()
            ->assertJsonPath('sprint_id', $sprint->id);

        $this->assertEquals($sprint->id, $issue->fresh()->sprint_id);
    }

    /** @test */
    public function test_component_management_view_renders(): void
    {
        [$manager, $project, $user] = $this->setupProject();
        $project->components()->create(['name' => 'Backend', 'description' => 'API layer']);

        $this->actingAs($manager)
            ->get(route('projects.show', $project))
            ->assertOk()
            ->assertSee('Backend');
    }

    /** @test */
    public function test_member_can_create_component(): void
    {
        [$manager, $project, $user] = $this->setupProject();

        // lead creates component (controller gates on ensureProjectLead)
        $this->actingAs($manager)
            ->post(route('projects.components.store', $project), ['name' => 'Frontend'])
            ->assertRedirect();

        $this->assertDatabaseHas('components', ['project_id' => $project->id, 'name' => 'Frontend']);
    }

    /** @test */
    public function test_threaded_comment_reply(): void
    {
        [$manager, $project, $user] = $this->setupProject();
        $issue = Issue::create([
            'project_id' => $project->id, 'code' => 'HEL-1', 'title' => 'Thread test',
            'type' => 'task', 'status' => 'open', 'priority' => 'low', 'reporter_id' => $user->id,
        ]);

        // top-level comment
        $parent = Comment::create([
            'issue_id' => $issue->id, 'user_id' => $user->id, 'body' => '<p>question</p>',
        ]);

        // reply
        $this->actingAs($user)
            ->post(route('issues.comments.store', $issue), [
                'body' => '<p>answer</p>',
                'parent_id' => $parent->id,
            ])->assertRedirect();

        $reply = Comment::where('parent_id', $parent->id)->first();
        $this->assertNotNull($reply);
        $this->assertCount(1, $parent->fresh()->replies);
    }

    /** @test */
    public function test_automation_rule_fires_on_status_change(): void
    {
        [$manager, $project, $user] = $this->setupProject();
        $issue = Issue::create([
            'project_id' => $project->id, 'code' => 'HEL-1', 'title' => 'Auto test',
            'type' => 'task', 'status' => 'open', 'priority' => 'low', 'reporter_id' => $user->id,
        ]);

        $rule = AutomationRule::create([
            'project_id' => $project->id,
            'name' => 'Assign on done',
            'event' => 'issue:status_changed',
            'conditions' => [['field' => 'status', 'value' => 'done']],
            'actions' => [['type' => 'assign', 'value' => $manager->id]],
            'enabled' => true,
        ]);

        // Change status to 'done' — should trigger the rule
        $issue->update(['status' => 'done']);

        $this->assertEquals($manager->id, $issue->fresh()->assignee_id);
        $this->assertDatabaseHas('automation_rule_logs', ['automation_rule_id' => $rule->id, 'issue_id' => $issue->id]);
    }

    /** @test */
    public function test_strict_transition_requires_role(): void
    {
        [$manager, $project, $user] = $this->setupProject(ProjectMember::ROLE_VIEWER);

        $st = $project->statuses()->orderBy('order')->get();
        $open = $st->firstWhere('key', 'open');
        $done = $st->firstWhere('is_closed', true);

        // Define a transition from Open -> Done that requires 'lead' role.
        StatusTransition::create([
            'project_id' => $project->id,
            'from_status_id' => $open->id,
            'to_status_id' => $done->id,
            'required_role' => ProjectMember::ROLE_LEAD,
        ]);

        $issue = Issue::create([
            'project_id' => $project->id, 'code' => 'HEL-1', 'title' => 'Strict',
            'type' => 'task', 'status' => 'open', 'priority' => 'low', 'reporter_id' => $user->id,
        ]);

        // viewer cannot transition to done
        $this->assertFalse($issue->canTransitionTo('done', $user));

        // lead can
        $this->assertTrue($issue->canTransitionTo('done', $manager));
    }

    /** @test */
    public function test_component_filter_in_issue_list(): void
    {
        [$manager, $project, $user] = $this->setupProject();
        $comp = $project->components()->create(['name' => 'Backend']);
        $issue1 = Issue::create([
            'project_id' => $project->id, 'code' => 'HEL-1', 'title' => 'Issue 1',
            'type' => 'task', 'status' => 'open', 'priority' => 'low', 'reporter_id' => $user->id,
        ]);
        $issue1->components()->attach($comp);
        Issue::create([
            'project_id' => $project->id, 'code' => 'HEL-2', 'title' => 'Issue 2',
            'type' => 'task', 'status' => 'open', 'priority' => 'low', 'reporter_id' => $user->id,
        ]);

        // Filter by component → only issue1
        $res = $this->actingAs($user)->get(route('issues.index', ['project_id' => $project->id, 'component_id' => $comp->id]));
        $res->assertOk()->assertSee('HEL-1')->assertSee('Issue 1');
        $this->assertStringNotContainsString('HEL-2', $res->content());

        // All components → both
        $resAll = $this->actingAs($user)->get(route('issues.index', ['project_id' => $project->id, 'component_id' => 'all']));
        $resAll->assertOk()->assertSee('HEL-1')->assertSee('HEL-2');
    }
}
