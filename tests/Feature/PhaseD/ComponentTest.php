<?php

namespace Tests\Feature\PhaseD;

use App\Models\Comment;
use App\Models\Component;
use App\Models\Issue;

class ComponentTest extends PhaseDTestCase
{
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

        // Filter by component -> only issue1
        $res = $this->actingAs($user)->get(route('issues.index', ['project_id' => $project->id, 'component_id' => $comp->id]));
        $res->assertOk()->assertSee('HEL-1')->assertSee('Issue 1');
        $this->assertStringNotContainsString('HEL-2', $res->content());

        // All components -> both
        $resAll = $this->actingAs($user)->get(route('issues.index', ['project_id' => $project->id, 'component_id' => 'all']));
        $resAll->assertOk()->assertSee('HEL-1')->assertSee('HEL-2');
    }

    /** @test */
    public function test_board_component_filter(): void
    {
        [$manager, $project, $user] = $this->setupProject();

        // statuses are created automatically by Project::boot
        $comp = Component::create(['project_id' => $project->id, 'name' => 'UI']);

        $issue1 = Issue::create([
            'project_id' => $project->id, 'code' => 'B-1', 'title' => 'Board filter A',
            'type' => 'task', 'status' => 'open', 'priority' => 'low', 'reporter_id' => $user->id,
        ])->components()->attach($comp->id);

        Issue::create([
            'project_id' => $project->id, 'code' => 'B-2', 'title' => 'Board filter B',
            'type' => 'task', 'status' => 'open', 'priority' => 'low', 'reporter_id' => $user->id,
        ]);

        $filtered = $this->actingAs($user)->get(route('issues.board', ['project_id' => $project->id, 'component_id' => $comp->id]));
        $filtered->assertOk()->assertSee('B-1')->assertSee('Board filter A');
        $this->assertStringNotContainsString('B-2', $filtered->content());
    }
}
