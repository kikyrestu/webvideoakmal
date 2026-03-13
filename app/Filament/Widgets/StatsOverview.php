<?php

namespace App\Filament\Widgets;

use App\Models\Comment;
use App\Models\Video;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $published = Video::where('status', 'published')->count();
        $draft     = Video::where('status', 'draft')->count();
        $totalViews = Video::sum('views_count');
        $pending   = Comment::where('status', 'pending')->count();

        return [
            Stat::make('Videos Published', $published)
                ->description("$draft draft")
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Total Views', number_format($totalViews))
                ->descriptionIcon('heroicon-m-eye')
                ->color('info'),

            Stat::make('Pending Comments', $pending)
                ->descriptionIcon('heroicon-m-chat-bubble-left')
                ->color($pending > 0 ? 'danger' : 'gray'),
        ];
    }
}
