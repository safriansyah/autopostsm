<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Log;

class Connections extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-link';

    protected static ?string $title = 'Connections';

    protected static ?string $navigationLabel = 'Connections';

    protected static ?int $navigationSort = 4;

    protected static string $view = 'filament.pages.connections';

    /**
     * The resolved connection data for every platform.
     *
     * @var array<int, array<string, mixed>>
     */
    public array $connections = [];

    public function mount(): void
    {
        $settings = Setting::pluck('option_value', 'option_name')->toArray();

        $this->connections = [
            $this->instagram($settings),
            $this->simple('Facebook', 'facebook', $settings, ['facebook_access_token']),
            $this->simple('Twitter (X)', 'twitter', $settings, ['twitter_access_token']),
            $this->simple('LinkedIn', 'linkedin', $settings, ['linkedin_access_token']),
            $this->simple('TikTok', 'tiktok', $settings, ['tiktok_access_token']),
            $this->simple('WhatsApp', 'whatsapp', $settings, ['whatsapp_access_token']),
        ];
    }

    /**
     * Fetch the live Instagram profile the access token is connected to.
     */
    protected function instagram(array $settings): array
    {
        $base = [
            'platform' => 'Instagram',
            'key'      => 'instagram',
            'connected' => false,
            'profile'  => null,
            'error'    => null,
        ];

        $token = $settings['instagram_access_token'] ?? env('INSTAGRAM_ACCESS_TOKEN');
        if ($this->isPlaceholder($token)) {
            $base['error'] = 'Belum dikonfigurasi. Isi kredensial di halaman Settings.';
            return $base;
        }

        try {
            $response = app('instagram')->get('/me', [
                'fields' => 'id,username,name,account_type,profile_picture_url,followers_count,follows_count,media_count,biography',
            ]);

            if ($response->failed() || empty($response->json('id'))) {
                $base['error'] = 'Token tidak valid atau kedaluwarsa: ' . $response->body();
                return $base;
            }

            $data = $response->json();

            $base['connected'] = true;
            $base['profile'] = [
                'username'     => $data['username'] ?? null,
                'name'         => $data['name'] ?? null,
                'avatar'       => $data['profile_picture_url'] ?? null,
                'account_type' => $data['account_type'] ?? null,
                'biography'    => $data['biography'] ?? null,
                'stats'        => [
                    'Posts'     => $data['media_count'] ?? 0,
                    'Followers' => $data['followers_count'] ?? 0,
                    'Following' => $data['follows_count'] ?? 0,
                ],
            ];
        } catch (\Throwable $e) {
            Log::error('Connections: failed to fetch Instagram profile: ' . $e->getMessage());
            $base['error'] = 'Gagal terhubung ke Instagram: ' . $e->getMessage();
        }

        return $base;
    }

    /**
     * Build a simple "configured / not configured" status for platforms
     * that don't (yet) have a live profile lookup.
     */
    protected function simple(string $platform, string $key, array $settings, array $requiredKeys): array
    {
        $configured = true;
        foreach ($requiredKeys as $req) {
            $value = $settings[$req] ?? env(strtoupper($req));
            if ($this->isPlaceholder($value)) {
                $configured = false;
                break;
            }
        }

        return [
            'platform'  => $platform,
            'key'       => $key,
            'connected' => $configured,
            'profile'   => null,
            'error'     => $configured ? null : 'Belum dikonfigurasi. Isi kredensial di halaman Settings.',
        ];
    }

    /**
     * Decide whether a credential is empty or still a placeholder value.
     */
    protected function isPlaceholder(?string $value): bool
    {
        if (empty($value)) {
            return true;
        }

        return str_starts_with($value, 'your_');
    }
}
