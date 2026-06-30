<?php

namespace App\Services\SocialMedia;

use App\Models\Post;
use App\Services\SocialMedia\Contracts\SocialPublisher;
use App\Services\SocialMedia\Publishers\FacebookPublisher;
use App\Services\SocialMedia\Publishers\InstagramPublisher;
use App\Services\SocialMedia\Publishers\LinkedInPublisher;
use App\Services\SocialMedia\Publishers\TikTokPublisher;
use App\Services\SocialMedia\Publishers\TwitterPublisher;
use App\Services\SocialMedia\Publishers\WhatsAppPublisher;
use Illuminate\Support\Facades\Log;

class SocialMediaManager
{
    /** @var SocialPublisher[] */
    protected array $publishers;

    public function __construct()
    {
        $this->publishers = [
            new TwitterPublisher(),
            new FacebookPublisher(),
            new LinkedInPublisher(),
            new InstagramPublisher(),
            new TikTokPublisher(),
            new WhatsAppPublisher(),
        ];
    }

    /** @return SocialPublisher[] */
    public function publishers(): array
    {
        return $this->publishers;
    }

    /**
     * Platforms with real credentials configured (connected accounts),
     * as [key => name] — used to populate the post target selector.
     *
     * @return array<string, string>
     */
    public function availablePlatforms(): array
    {
        $platforms = [];

        foreach ($this->publishers as $publisher) {
            if ($publisher->isConfigured()) {
                $platforms[$publisher->key()] = $publisher->name();
            }
        }

        return $platforms;
    }

    /**
     * Publish a post to every enabled platform it hasn't been posted to yet.
     *
     * Each platform is isolated: a failure is logged and the others continue.
     * Returns true if at least one platform was posted to successfully.
     */
    public function publishPost(Post $post, string $caption): bool
    {
        $anySuccess = false;

        foreach ($this->publishers as $publisher) {
            $column = $publisher->flagColumn();

            if ($post->{$column} || ! $publisher->isEnabled()) {
                continue;
            }

            try {
                $publisher->publish($post, $caption);
                $post->{$column} = true;
                $anySuccess = true;
                Log::info("Post ID {$post->id} posted to {$publisher->name()}.");
            } catch (\Throwable $e) {
                Log::error("Error posting Post ID {$post->id} to {$publisher->name()}: " . $e->getMessage());
            }
        }

        $post->save();

        // Mark the post fully done once every platform flag is set.
        $allDone = collect($this->publishers)->every(fn (SocialPublisher $p) => (bool) $post->{$p->flagColumn()});

        if ($allDone && ! $post->is_posted) {
            $post->is_posted = true;
            $post->save();
            Log::info("Post ID {$post->id} posted to all platforms.");
        }

        return $anySuccess;
    }
}
