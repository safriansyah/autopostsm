<?php

namespace App\Services\SocialMedia\Publishers;

use App\Models\Post;
use App\Services\SocialMedia\AbstractPublisher;
use Facebook\Facebook;
use Illuminate\Support\Facades\Log;

class FacebookPublisher extends AbstractPublisher
{
    public function key(): string
    {
        return 'facebook';
    }

    public function name(): string
    {
        return 'Facebook';
    }

    public function publish(Post $post, string $caption): void
    {
        $accessToken = app('facebook.access_token');
        /** @var Facebook $facebook */
        $facebook = app(Facebook::class);
        $pageId = $this->setting('facebook_page_id', 'FACEBOOK_PAGE_ID');

        $data = ['message' => $caption];

        if ($mediaUrl = $post->mediaUrl()) {
            $data['source'] = $facebook->fileToUpload($mediaUrl);
            $response = $facebook->post("/{$pageId}/photos", $data, $accessToken);
        } else {
            $response = $facebook->post("/{$pageId}/feed", $data, $accessToken);
        }

        Log::info('Facebook response: ' . json_encode($response));
    }
}
