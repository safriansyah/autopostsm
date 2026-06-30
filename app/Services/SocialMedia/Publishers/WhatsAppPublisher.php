<?php

namespace App\Services\SocialMedia\Publishers;

use App\Models\Post;
use App\Services\SocialMedia\AbstractPublisher;
use Illuminate\Support\Facades\Log;

class WhatsAppPublisher extends AbstractPublisher
{
    public function key(): string
    {
        return 'whatsapp';
    }

    public function name(): string
    {
        return 'WhatsApp';
    }

    public function publish(Post $post, string $caption): void
    {
        // Uses the registered HTTP client
        // (base: https://graph.facebook.com/v21.0/{phone_number_id}).
        /** @var \Illuminate\Http\Client\PendingRequest $whatsapp */
        $whatsapp = app('whatsapp');

        // WhatsApp Cloud API needs an explicit recipient phone number.
        $recipient = $this->setting('whatsapp_recipient', 'WHATSAPP_RECIPIENT');

        $data = [
            'messaging_product' => 'whatsapp',
            'to'                => $recipient,
            'type'              => 'text',
            'text'              => ['body' => $caption],
        ];

        $response = $whatsapp->asJson()->post('/messages', $data);

        if ($response->failed()) {
            throw new \RuntimeException('WhatsApp error: ' . $response->body());
        }

        Log::info('WhatsApp response: ' . $response->body());
    }
}
