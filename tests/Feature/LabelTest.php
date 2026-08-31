<?php

namespace Tests\Feature;

use App\Models\Issue;
use App\Models\Label;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LabelTest extends TestCase
{
    use RefreshDatabase;

    private function seedAndProject(): array
    {
        $this->seed();
        $manager = User::factory()->create();
        $manager->givePermissionTo(['project.manage', 'issue.create', 'issue.edit', 'issue.delete', 'issue.view']);
        $project = Project::create(['key' => 'HEL', 'name' => 'Helpdesk', 'owner_id' => $manager->id]);
        ProjectMember::create(['project_id' => $project->id, 'user_id' => $manager->id, 'role' => ProjectMember::ROLE_LEAD]);

        return [$manager, $project];
    }

    public function test_lead_can_create_label(): void
    {
        [$manager, $project] = $this->seedAndProject();

        $this->actingAs($manager)
            ->post(route('projects.labels.store', $project), ['name' => 'Bug', 'color' => '#ff0000'])
            ->assertRedirect();

        $this->assertDatabaseHas('labels', ['project_id' => $project->id, 'name' => 'Bug', 'color' => '#ff0000']);
    }

    public function test_label_name_unique_per_project(): void
    {
        [$manager, $project] = $this->seedAndProject();
        Label::create(['project_id' => $project->id, 'name' => 'Bug']);

        $this->actingAs($manager)
            ->post(route('projects.labels.store', $project), ['name' => 'Bug'])
            ->assertSessionHasErrors('name');
    }

    public function test_label_can_be_assigned_to_issue_and_filtered(): void
    {
        [$manager, $project] = $this->seedAndProject();
        $label = Label::create(['project_id' => $project->id, 'name' => 'Bug']);
        $other = Label::create(['project_id' => $project->id, 'name' => 'Feature']);

        $this->actingAs($manager)
            ->post(route('issues.store'), [
                'project_id' => $project->id, 'title' => 'Login broken', 'type' => 'bug',
                'status' => 'open', 'priority' => 'high', 'labels' => [$label->id],
            ])
            ->assertRedirect();

        $issue = Issue::where('code', 'HEL-1')->first();
        $this->assertTrue($issue->labels->contains($label));
        $this->assertFalse($issue->labels->contains($other));

        $this->actingAs($manager)
            ->get(route('issues.index', ['project_id' => $project->id, 'label_id' => $label->id]))
            ->assertOk()
            ->assertSee('Login broken');

        $this->actingAs($manager)
            ->get(route('issues.index', ['project_id' => $project->id, 'label_id' => $other->id]))
            ->assertOk()
            ->assertDontSee('Login broken');
    }

    public function test_cannot_assign_label_from_other_project(): void
    {
        [$manager, $project] = $this->seedAndProject();
        $otherProject = Project::create(['key' => 'OTH', 'name' => 'Other', 'owner_id' => $manager->id]);
        $foreign = Label::create(['project_id' => $otherProject->id, 'name' => 'Foreign']);

        $this->actingAs($manager)
            ->post(route('issues.store'), [
                'project_id' => $project->id, 'title' => 'X', 'type' => 'task',
                'status' => 'open', 'priority' => 'low', 'labels' => [$foreign->id],
            ])
            ->assertSessionHasErrors('labels.0');
    }

    public function test_lead_can_delete_label(): void
    {
        [$manager, $project] = $this->seedAndProject();
        $label = Label::create(['project_id' => $project->id, 'name' => 'Bug']);

        $this->actingAs($manager)
            ->delete(route('projects.labels.destroy', [$project, $label]))
            ->assertRedirect();

        $this->assertDatabaseMissing('labels', ['id' => $label->id]);
    }
}
