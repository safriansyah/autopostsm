<?php

namespace App\Filament\Resources\PostResource\Pages;

use App\Filament\Resources\PostResource;
use App\Models\Post;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPost extends EditRecord
{
    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * Keep the is_posted_to_* flags in sync with the selected platforms,
     * preserving any platform that has already been published.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $existing = $this->record->only(
            array_map(fn (string $key): string => "is_posted_to_{$key}", Post::PLATFORMS)
        );

        return array_merge($data, Post::flagsForPlatforms($data['platforms'] ?? [], $existing));
    }
}
