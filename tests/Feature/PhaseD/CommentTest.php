<?php

namespace Tests\Feature\PhaseD;

use App\Models\Comment;
use App\Models\Issue;

class CommentTest extends PhaseDTestCase
{
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
}
