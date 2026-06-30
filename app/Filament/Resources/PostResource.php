<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\Post;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\PostResource\Pages;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Split::make([
                    Forms\Components\Section::make('Post content')->schema([
                        Forms\Components\Textarea::make('description')
                            ->maxLength(255) // Individual max length, but dynamically validated
                            ->placeholder('Add a description')
                            ->hint('Max 180 characters, total combined length with other fields must not exceed 275')
                            ->reactive(),
                        Forms\Components\FileUpload::make('image')
                            ->label('Image / Video')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'video/mp4', 'video/quicktime'])
                            ->maxSize(102400) // 100 MB
                            ->helperText('Gambar (JPG/PNG/WebP) atau video (MP4/MOV). Video akan diposting sebagai Reels di Instagram.'),
                        Forms\Components\TextInput::make('site_url')
                            ->maxLength(255) // Individual max length, but dynamically validated
                            ->placeholder('Add a site URL e.g https://example.com')
                            ->default(env('APP_URL'))
                            ->reactive(),
                        Forms\Components\SpatieTagsInput::make('tags')
                            ->placeholder('Add a tag')
                            ->nestedRecursiveRules([
                                'min:3',
                                'max:50',
                            ])
                            ->reactive()
                            ->rule(function ($get) {
                                return function ($attribute, $value, $fail) use ($get) {
                                    $description = $get('description') ?? '';
                                    $siteUrl = $get('site_url') ?? '';
                                    $image = $get('image') ?? '';
                                    $tags = is_array($value) ? implode(',', $value) : '';

                                    $descriptionLength = strlen($description);
                                    $siteUrlLength = strlen($siteUrl);
                                    $tagsLength = strlen($tags);

                                    $totalLength = $descriptionLength + $siteUrlLength + $tagsLength;

                                    if ($totalLength > 275) {
                                        $fail(
                                            "The total length of description, site URL, and tags must not exceed 275 characters. Current total: {$totalLength}."
                                        );
                                    }

                                    if (empty($description) && empty($image)) {
                                        $fail(
                                            "Either post description or image is required."
                                        );
                                    }
                                };
                            }),
                    ]),
                    Forms\Components\Section::make('Publikasi')->schema([
                        Forms\Components\CheckboxList::make('platforms')
                            ->label('Posting ke')
                            ->options(fn (): array => app(\App\Services\SocialMedia\SocialMediaManager::class)->availablePlatforms())
                            ->required()
                            ->helperText('Pilih akun sosmed aktif yang dituju. Hubungkan akun baru di halaman Settings.'),
                        Forms\Components\DateTimePicker::make('published_at')
                            ->label('Jadwal terbit')
                            ->required()
                            ->seconds(false)
                            ->default(now()),
                    ])->grow(false),
                ])->from('md')->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ViewColumn::make('media')
                    ->label('Media')
                    ->view('filament.tables.columns.media-preview'),
                Tables\Columns\TextColumn::make('description')
                    ->label('Deskripsi')
                    ->searchable()
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();

                        if (strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }

                        // Only render the tooltip if the column content exceeds the length limit.
                        return $state;
                    }),
                Tables\Columns\TextColumn::make('platforms')
                    ->label('Sosmed')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(fn (Post $record): string => $record->is_posted
                        ? 'Terbit'
                        : (($record->published_at && $record->published_at->isPast()) ? 'Diproses' : 'Terjadwal'))
                    ->color(fn (string $state): string => match ($state) {
                        'Terbit' => 'success',
                        'Diproses' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('published_at')
                    ->label('Jadwal Terbit')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label(''),
                Tables\Actions\DeleteAction::make()->label(''),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])->modifyQueryUsing(function (Builder $query) {
                return $query->latest();
            });
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
