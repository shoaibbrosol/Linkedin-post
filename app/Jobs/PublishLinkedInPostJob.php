<?php

namespace App\Jobs;

use App\Models\LinkedinPost;
use App\Services\LinkedInService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Throwable;

class PublishLinkedInPostJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    /**
     * Create a new job instance.
     */
    public function __construct(public int $postId)
    {
    }

    public function uniqueId(): string
    {
        return 'linkedin-post-'.$this->postId;
    }

    /**
     * Execute the job.
     */
    public function handle(LinkedInService $linkedIn): void
    {
        DB::transaction(function () use ($linkedIn): void {
            $post = LinkedinPost::query()
                ->whereKey($this->postId)
                ->lockForUpdate()
                ->first();

            if (! $post || $post->status !== 'pending') {
                return;
            }

            try {
                $result = $linkedIn->publish($post);

                $post->update([
                    'status' => 'posted',
                    'posted_at' => now(),
                    'linkedin_post_id' => $result['id'] ?? null,
                    'api_response' => $result['response'] ?? $result,
                    'error_message' => null,
                ]);

                $post->logs()->create([
                    'status' => 'posted',
                    'message' => 'Post published successfully.',
                    'request_payload' => $result['payload'] ?? null,
                    'response_payload' => $result['response'] ?? $result,
                ]);
            } catch (Throwable $exception) {
                $post->increment('retry_count');
                $post->update([
                    'status' => 'failed',
                    'error_message' => $exception->getMessage(),
                ]);

                $post->logs()->create([
                    'status' => 'failed',
                    'message' => $exception->getMessage(),
                ]);
            }
        });
    }
}
