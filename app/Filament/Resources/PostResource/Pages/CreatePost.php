<?php

namespace App\Filament\Resources\PostResource\Pages;

use App\Filament\Resources\PostResource;
use App\Models\Post;
use Filament\Resources\Pages\CreateRecord;

class CreatePost extends CreateRecord
{
    protected static string $resource = PostResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * Translate the selected target platforms into the is_posted_to_* flags
     * so the publisher only posts to the chosen platforms.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return array_merge($data, Post::flagsForPlatforms($data['platforms'] ?? []));
    }
}
