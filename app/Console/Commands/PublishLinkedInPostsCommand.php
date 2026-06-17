<?php

namespace App\Console\Commands;

use App\Jobs\PublishLinkedInPostJob;
use App\Models\LinkedinPost;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('linkedin:publish-posts')]
#[Description('Publish due LinkedIn posts')]
class PublishLinkedInPostsCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $posts = LinkedinPost::query()
            ->where('status', 'pending')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->whereNull('linkedin_post_id')
            ->limit(100)
            ->get();

        $posts->each(fn (LinkedinPost $post) => PublishLinkedInPostJob::dispatch($post->id));

        $this->info("Dispatched {$posts->count()} due LinkedIn post(s).");

        return self::SUCCESS;
    }
}
