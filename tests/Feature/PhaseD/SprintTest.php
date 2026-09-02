<?php

namespace Tests\Feature\PhaseD;

use App\Models\Issue;
use App\Models\Sprint;

class SprintTest extends PhaseDTestCase
{
    /** @test */
    public function test_backlog_view_renders_for_member(): void
    {
        [$manager, $project, $user] = $this->setupProject();
        Issue::create([
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
        $outsider = \App\Models\User::factory()->create();
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
    public function test_sprint_completion_moves_unfinished_issues(): void
    {
        [$manager, $project, $user] = $this->setupProject();
        $sprint = Sprint::create([
            'project_id' => $project->id, 'name' => 'S1', 'state' => 'active',
            'starts_at' => now(), 'ends_at' => now()->addDays(7),
        ]);
        $openIssue = Issue::create([
            'project_id' => $project->id, 'code' => 'HEL-1', 'title' => 'Open issue',
            'type' => 'task', 'status' => 'open', 'priority' => 'low', 'reporter_id' => $user->id,
            'sprint_id' => $sprint->id,
        ]);
        $closedIssue = Issue::create([
            'project_id' => $project->id, 'code' => 'HEL-2', 'title' => 'Done issue',
            'type' => 'task', 'status' => 'done', 'priority' => 'low', 'reporter_id' => $user->id,
            'sprint_id' => $sprint->id,
        ]);

        $sprint->complete();

        $this->assertNull($openIssue->fresh()->sprint_id);
        $this->assertEquals($sprint->id, $closedIssue->fresh()->sprint_id);
        $this->assertEquals('completed', $sprint->fresh()->state);
    }
}
