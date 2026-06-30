<?php

namespace App\Services\SocialMedia\Publishers;

use App\Models\Post;
use App\Services\SocialMedia\AbstractPublisher;
use Illuminate\Support\Facades\Log;

class LinkedInPublisher extends AbstractPublisher
{
    public function key(): string
    {
        return 'linkedin';
    }

    public function name(): string
    {
        return 'LinkedIn';
    }

    public function publish(Post $post, string $caption): void
    {
        // Uses the registered HTTP client (base: https://api.linkedin.com/v2).
        /** @var \Illuminate\Http\Client\PendingRequest $linkedin */
        $linkedin = app('linkedin');

        $data = ['comment' => $caption];

        if ($mediaUrl = $post->mediaUrl()) {
            $data['content']['media'] = [
                'title'       => $post->description,
                'description' => $caption,
                'source'      => $mediaUrl,
            ];
        }

        $response = $linkedin->asJson()->post('/ugcPosts', $data);

        if ($response->failed()) {
            throw new \RuntimeException('LinkedIn error: ' . $response->body());
        }

        Log::info('LinkedIn response: ' . $response->body());
    }
}
