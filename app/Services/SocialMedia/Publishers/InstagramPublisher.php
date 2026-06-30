<?php

namespace App\Services\SocialMedia\Publishers;

use App\Models\Post;
use App\Services\SocialMedia\AbstractPublisher;
use Illuminate\Support\Facades\Log;

class InstagramPublisher extends AbstractPublisher
{
    public function key(): string
    {
        return 'instagram';
    }

    public function name(): string
    {
        return 'Instagram';
    }

    public function publish(Post $post, string $caption): void
    {
        $mediaUrl = $post->mediaUrl();

        // Instagram only supports posts that contain an image or video.
        if ($mediaUrl === null) {
            throw new \RuntimeException('Instagram requires an image or video.');
        }

        /** @var \Illuminate\Http\Client\PendingRequest $instagram */
        $instagram = app('instagram');

        // Step 1: create a media container (videos are published as Reels).
        $payload = $post->isVideo()
            ? ['media_type' => 'REELS', 'video_url' => $mediaUrl, 'caption' => $caption]
            : ['image_url' => $mediaUrl, 'caption' => $caption];

        $createResponse = $instagram->asForm()->post('/me/media', $payload);
        $creationId = $createResponse->json('id');

        if ($createResponse->failed() || empty($creationId)) {
            throw new \RuntimeException('Failed to create media container: ' . $createResponse->body());
        }

        // Containers are processed asynchronously (Instagram downloads the
        // media first). Wait until the container is FINISHED before publishing
        // — for images this is usually one quick check, videos take longer.
        $interval = $post->isVideo() ? 5 : 2;
        $attempts = 0;
        $status = null;

        do {
            sleep($interval);
            $statusResponse = $instagram->get("/{$creationId}", ['fields' => 'status_code']);
            $status = $statusResponse->json('status_code');
            $attempts++;
        } while ($status === 'IN_PROGRESS' && $attempts < 30);

        if ($status !== 'FINISHED') {
            throw new \RuntimeException("Media container not ready (status: {$status}): " . $statusResponse->body());
        }

        // Step 2: publish the container.
        $publishResponse = $instagram->asForm()->post('/me/media_publish', [
            'creation_id' => $creationId,
        ]);

        if ($publishResponse->failed() || empty($publishResponse->json('id'))) {
            throw new \RuntimeException('Failed to publish media: ' . $publishResponse->body());
        }

        Log::info('Instagram response: ' . $publishResponse->body());
    }
}
