<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

    public function test_rich_text_description_is_sanitized(): void
    {
        $user = $this->projectManager();

        $this->actingAs($user)->post(route('projects.store'), [
            'key' => 'HEL', 'name' => 'Helpdesk',
            'description' => '<script>alert(1)</script><p>ok <strong>bold</strong></p>',
        ]);

        $project = Project::where('key', 'HEL')->first();
        $this->assertStringNotContainsString('<script>', $project->description);
        $this->assertStringContainsString('<strong>bold</strong>', $project->description);
    }

    public function test_slug_is_auto_generated_from_name(): void
    {
        $user = $this->projectManager();

        $this->actingAs($user)->post(route('projects.store'), [
            'key' => 'HEL', 'name' => 'My Cool Project',
        ]);

        $project = Project::where('key', 'HEL')->first();
        $this->assertSame('my-cool-project', $project->slug);
        $this->assertSame('my-cool-project', $project->folder());
    }

    public function test_image_upload_is_scoped_to_project_folder_and_returns_url(): void
    {
        Storage::fake('public');
        $user = $this->projectManager();
        $project = Project::create(['key' => 'HEL', 'name' => 'Helpdesk', 'owner_id' => $user->id]);

        $response = $this->actingAs($user)
            ->post(route('projects.image.upload', $project), [
                'file' => UploadedFile::fake()->image('pic.png', 10, 10),
            ]);

        $response->assertOk()->assertJsonStructure(['location']);
        Storage::disk('public')->assertExists(
            'projects/'.$project->folder().'/description/'.$this->filenameFrom($response)
        );
    }

    private function filenameFrom($response): string
    {
        return basename(json_decode($response->getContent())->location);
    }

    public function test_non_local_image_src_is_stripped_on_save(): void
    {
        $user = $this->projectManager();

        $this->actingAs($user)->post(route('projects.store'), [
            'key' => 'HEL', 'name' => 'Helpdesk',
            'description' => '<p><img src="javascript:alert(1)">keep</p>'
                .'<img src="/storage/projects/hel/description/ok.png">',
        ]);

        $project = Project::where('key', 'HEL')->first();
        $this->assertStringNotContainsString('javascript:', $project->description);
        $this->assertStringContainsString('/storage/projects/hel/description/ok.png', $project->description);
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

    public function test_image_quota_unlimited_when_plan_storage_is_zero(): void
    {
        Storage::fake('public');
        $user = $this->projectManager();
        Plan::where('slug', 'free')->update(['limits' => array_merge(
            Plan::where('slug', 'free')->first()->limits ?? [], ['max_storage_mb' => 0],
        )]);
        $project = Project::create(['key' => 'HEL', 'name' => 'Helpdesk', 'owner_id' => $user->id]);

        $this->actingAs($user)
            ->post(route('projects.image.upload', $project), ['file' => UploadedFile::fake()->image('pic.png', 10, 10)])
            ->assertOk();
    }

    public function test_image_upload_blocked_when_quota_exceeded(): void
    {
        Storage::fake('public');
        $user = $this->projectManager();
        $free = Plan::where('slug', 'free')->first();
        $limits = array_merge($free->limits ?? [], ['max_storage_mb' => 1]); // 1 MB cap (array_merge overwrites)
        Plan::where('slug', 'free')->update(['limits' => $limits]);
        $project = Project::create(['key' => 'HEL', 'name' => 'Helpdesk', 'owner_id' => $user->id]);

        // Pre-fill the project folder with ~1.5 MB so a new upload exceeds the 1 MB cap.
        Storage::disk('public')->put('projects/'.$project->folder().'/description/seed.bin', str_repeat('x', 1536 * 1024));

        $this->actingAs($user)
            ->post(route('projects.image.upload', $project), ['file' => UploadedFile::fake()->image('pic.png', 10, 10)])
            ->assertInvalid('file');
    }
    public function test_non_manager_cannot_view_create_form(): void
    {
        $this->seed();
        $viewer = User::factory()->create(); // no project.manage

        $this->actingAs($viewer)->get(route('projects.create'))
            ->assertForbidden();
    }
}
