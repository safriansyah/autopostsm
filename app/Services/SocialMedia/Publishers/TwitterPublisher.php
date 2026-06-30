<?php

namespace App\Services\SocialMedia\Publishers;

use App\Models\Post;
use App\Services\SocialMedia\AbstractPublisher;
use Illuminate\Support\Facades\Log;
use Noweh\TwitterApi\Client;

class TwitterPublisher extends AbstractPublisher
{
    public function key(): string
    {
        return 'twitter';
    }

    public function name(): string
    {
        return 'Twitter';
    }

    public function publish(Post $post, string $caption): void
    {
        /** @var Client $client */
        $client = app(Client::class);

        $tweetData = ['text' => $caption];

        if ($mediaUrl = $post->mediaUrl()) {
            $fileData = base64_encode(file_get_contents($mediaUrl));
            $mediaInfo = $client->uploadMedia()->upload($fileData);
            $tweetData['media'] = [
                'media_ids' => [(string) $mediaInfo['media_id']],
            ];
        }

        $response = $client->tweet()->create()->performRequest($tweetData);

        Log::info('Twitter response: ' . json_encode($response));
    }
}
