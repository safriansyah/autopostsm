<?php

namespace App\Services\SocialMedia\Contracts;

use App\Models\Post;

interface SocialPublisher
{
    /** Machine key, e.g. "instagram". */
    public function key(): string;

    /** Human-friendly name, e.g. "Instagram". */
    public function name(): string;

    /** The Post column that tracks whether this platform has been posted to. */
    public function flagColumn(): string;

    /** Whether auto-posting to this platform is enabled (setting or env). */
    public function isEnabled(): bool;

    /**
     * Publish the given post to the platform.
     *
     * Must throw on failure so the caller can log it and keep the flag false.
     */
    public function publish(Post $post, string $caption): void;
}
