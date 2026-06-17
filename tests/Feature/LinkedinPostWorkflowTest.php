<?php

namespace Tests\Feature;

use App\Jobs\PublishLinkedInPostJob;
use App\Models\LinkedinAccount;
use App\Models\LinkedinPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class LinkedinPostWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_requires_authentication(): void
    {
        $this->get('/dashboard')
            ->assertRedirect(route('login'));
    }

    public function test_user_can_schedule_a_pending_post(): void
    {
        $user = User::factory()->create(['timezone' => 'Asia/Karachi']);
        $account = LinkedinAccount::create([
            'user_id' => $user->id,
            'linkedin_user_id' => 'abc123',
            'name' => 'Test Account',
            'access_token' => 'token',
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->post(route('posts.store'), [
                'linkedin_account_id' => $account->id,
                'title' => 'Laravel tip',
                'content' => 'Use queues for slow API work.',
                'scheduled_at' => now('Asia/Karachi')->addDay()->format('Y-m-d\TH:i'),
                'status' => 'pending',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('linkedin_posts', [
            'user_id' => $user->id,
            'linkedin_account_id' => $account->id,
            'status' => 'pending',
            'title' => 'Laravel tip',
        ]);
    }

    public function test_publish_command_dispatches_due_posts(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $account = LinkedinAccount::create([
            'user_id' => $user->id,
            'linkedin_user_id' => 'abc123',
            'access_token' => 'token',
            'status' => 'active',
        ]);

        $post = LinkedinPost::create([
            'user_id' => $user->id,
            'linkedin_account_id' => $account->id,
            'content' => 'Due post',
            'scheduled_at' => now()->subMinute(),
            'status' => 'pending',
        ]);

        $this->artisan('linkedin:publish-posts')->assertExitCode(0);

        Queue::assertPushed(PublishLinkedInPostJob::class, fn (PublishLinkedInPostJob $job) => $job->postId === $post->id);
    }
}
