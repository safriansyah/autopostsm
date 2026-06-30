<?php

namespace App\Services\SocialMedia;

use App\Models\Setting;
use App\Services\SocialMedia\Contracts\SocialPublisher;

abstract class AbstractPublisher implements SocialPublisher
{
    public function flagColumn(): string
    {
        return 'is_posted_to_' . $this->key();
    }

    public function isEnabled(): bool
    {
        $raw = $this->setting($this->key() . '_autopost', strtoupper($this->key()) . '_AUTO_POST');

        return filter_var($raw, FILTER_VALIDATE_BOOLEAN);
    }

    public function isConfigured(): bool
    {
        $token = $this->setting($this->key() . '_access_token', strtoupper($this->key()) . '_ACCESS_TOKEN');

        return ! empty($token) && ! str_starts_with($token, 'your_');
    }

    /**
     * Read a value from the settings table, falling back to an env var.
     */
    protected function setting(string $name, ?string $envKey = null): ?string
    {
        $value = Setting::where('option_name', $name)->value('option_value');

        if ($value !== null && $value !== '') {
            return $value;
        }

        return $envKey ? env($envKey) : null;
    }
}
