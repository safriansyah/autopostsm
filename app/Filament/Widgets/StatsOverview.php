<?php

namespace App\Filament\Widgets;

use App\Models\Post;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = -5;

    protected function getStats(): array
    {
        $timezone = env('APP_TIMEZONE') ?? config('app.timezone');

        $totalPosts = Post::count();
        $newThisMonth = Post::whereMonth('created_at', now($timezone)->month)
            ->whereYear('created_at', now($timezone)->year)
            ->count();

        $published = Post::where('is_posted', true)->count();
        $pending = Post::where('is_posted', false)->count();
        $instagram = Post::where('is_posted_to_instagram', true)->count();

        return [
            Stat::make('Total Posts', $totalPosts)
                ->description("{$newThisMonth} baru bulan ini")
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary'),

            Stat::make('Terbit (semua platform)', $published)
                ->description('Selesai di seluruh platform aktif')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),

            Stat::make('Terjadwal / Pending', $pending)
                ->description('Menunggu atau sebagian belum terkirim')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Terbit ke Instagram', $instagram)
                ->description('Post yang sudah naik ke Instagram')
                ->descriptionIcon('heroicon-m-camera')
                ->color('info'),
        ];
    }
}
