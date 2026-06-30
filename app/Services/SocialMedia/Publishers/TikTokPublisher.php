<?php

namespace App\Services\SocialMedia\Publishers;

use App\Models\Post;
use App\Services\SocialMedia\AbstractPublisher;
use Illuminate\Support\Facades\Log;

class TikTokPublisher extends AbstractPublisher
{
    public function key(): string
    {
        return 'tiktok';
    }

    public function name(): string
    {
        return 'TikTok';
    }

    public function publish(Post $post, string $caption): void
    {
        // Uses the registered HTTP client (base: https://open.tiktokapis.com/v2).
        /** @var \Illuminate\Http\Client\PendingRequest $tiktok */
        $tiktok = app('tiktok');

        $data = ['caption' => $caption];

        if ($mediaUrl = $post->mediaUrl()) {
            $data['video_url'] = $mediaUrl;
        }

        $response = $tiktok->asJson()->post('/video/upload', $data);

        if ($response->failed()) {
            throw new \RuntimeException('TikTok error: ' . $response->body());
        }

        Log::info('TikTok response: ' . $response->body());
    }
}
