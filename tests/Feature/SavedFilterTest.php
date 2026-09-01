<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\SavedFilter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SavedFilterTest extends TestCase
{
    use RefreshDatabase;

    private function seedProject(): array
    {
        $this->seed();
        $manager = User::factory()->create();
        $manager->givePermissionTo(['project.manage', 'issue.view']);
        $member = User::factory()->create();
        $member->givePermissionTo(['issue.view']);
        $other = User::factory()->create();
        $other->givePermissionTo(['issue.view']);
        $project = Project::create(['key' => 'HEL', 'name' => 'Helpdesk', 'owner_id' => $manager->id]);
        ProjectMember::create(['project_id' => $project->id, 'user_id' => $manager->id, 'role' => ProjectMember::ROLE_LEAD]);
        ProjectMember::create(['project_id' => $project->id, 'user_id' => $member->id, 'role' => ProjectMember::ROLE_MEMBER]);

        return [$manager, $member, $other, $project];
    }

    public function test_owner_sees_own_private_filter(): void
    {
        [$manager, , , $project] = $this->seedProject();
        SavedFilter::create(['user_id' => $manager->id, 'project_id' => $project->id, 'name' => 'My', 'filter_params' => []]);

        $this->actingAs($manager)
            ->get(route('projects.saved-filters.index', $project))
            ->assertOk()
            ->assertJsonCount(1);
    }

    public function test_member_sees_public_filter(): void
    {
        [$manager, $member, , $project] = $this->seedProject();
        SavedFilter::create(['user_id' => $manager->id, 'project_id' => $project->id, 'name' => 'Public', 'filter_params' => [], 'is_public' => true]);

        $this->actingAs($member)
            ->get(route('projects.saved-filters.index', $project))
            ->assertOk()
            ->assertJsonCount(1);
    }

    public function test_member_does_not_see_private_filter_of_other(): void
    {
        [$manager, $member, , $project] = $this->seedProject();
        SavedFilter::create(['user_id' => $manager->id, 'project_id' => $project->id, 'name' => 'Private', 'filter_params' => [], 'is_public' => false]);

        $this->actingAs($member)
            ->get(route('projects.saved-filters.index', $project))
            ->assertOk()
            ->assertJsonCount(0);
    }

    public function test_non_owner_cannot_delete_filter(): void
    {
        [$manager, $member, , $project] = $this->seedProject();
        $filter = SavedFilter::create(['user_id' => $manager->id, 'project_id' => $project->id, 'name' => 'Private', 'filter_params' => []]);

        $this->actingAs($member)
            ->delete(route('projects.saved-filters.destroy', [$project, $filter]))
            ->assertStatus(403);
    }
}
