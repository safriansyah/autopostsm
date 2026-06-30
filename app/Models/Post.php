<?php

namespace App\Models;

use Spatie\Tags\HasTags;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasTags;

    protected $fillable = [
        'description',
        'image',
        'site_url',
        'is_posted',
        'is_posted_to_twitter',
        'is_posted_to_facebook',
        'is_posted_to_linkedin',
        'is_posted_to_instagram',
        'is_posted_to_tiktok',
        'is_posted_to_whatsapp',
        'published_at',
    ];

    protected $casts = [
        'published_at'           => 'datetime',
        'is_posted'              => 'boolean',
        'is_posted_to_twitter'   => 'boolean',
        'is_posted_to_facebook'  => 'boolean',
        'is_posted_to_linkedin'  => 'boolean',
        'is_posted_to_instagram' => 'boolean',
        'is_posted_to_tiktok'    => 'boolean',
        'is_posted_to_whatsapp'  => 'boolean',
    ];

    /**
     * Posts that are due and still pending on at least one platform.
     */
    public function scopePublishable(Builder $query, ?string $timezone = null): Builder
    {
        return $query
            ->where('published_at', '<=', now($timezone))
            ->where(function (Builder $query) {
                $query->where('is_posted_to_twitter', false)
                    ->orWhere('is_posted_to_facebook', false)
                    ->orWhere('is_posted_to_linkedin', false)
                    ->orWhere('is_posted_to_instagram', false)
                    ->orWhere('is_posted_to_tiktok', false)
                    ->orWhere('is_posted_to_whatsapp', false);
            });
    }

    /**
     * Build the caption: description + site URL + hashtags, skipping any
     * empty part so no blank lines are left behind.
     */
    public function caption(): string
    {
        $tags = $this->tags->pluck('name')->map(fn ($tag) => "#$tag")->implode(' ');

        return collect([
            trim((string) $this->description),
            trim((string) $this->site_url),
            trim($tags),
        ])->filter(fn ($part) => $part !== '')->implode("\n\n");
    }

    /**
     * Public URL of the attached media, or null when there is none.
     * Already-absolute URLs are returned as-is.
     */
    public function mediaUrl(): ?string
    {
        if (empty($this->image)) {
            return null;
        }

        return preg_match('/^https?:\/\//', $this->image)
            ? $this->image
            : url("storage/{$this->image}");
    }

    /**
     * Whether the attached media is a video (published as a Reel on Instagram).
     */
    public function isVideo(): bool
    {
        $url = $this->mediaUrl();

        if ($url === null) {
            return false;
        }

        $extension = strtolower(pathinfo(parse_url($url, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));

        return in_array($extension, ['mp4', 'mov', 'm4v', 'avi'], true);
    }
}
