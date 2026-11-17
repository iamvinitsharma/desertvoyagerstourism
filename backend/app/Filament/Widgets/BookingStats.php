<?php

namespace App\Filament\Widgets;

use App\Models\Enquiry;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BookingStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $new = Enquiry::where('status', 'new')->count();
        $upcoming = Enquiry::whereIn('status', ['confirmed', 'contacted'])->whereBetween('date', [today(), today()->addDays(7)])->count();
        $month = (float) Order::where('status', 'paid')->where('created_at', '>=', now()->startOfMonth())->sum('total');
        $lastMonth = (float) Order::where('status', 'paid')->whereBetween('created_at', [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()])->sum('total');
        $today = Order::where('status', 'paid')->whereDate('created_at', today())->count();

        // 7-day sparkline of tour requests
        $spark = collect(range(6, 0))->map(fn ($d) => Enquiry::whereDate('created_at', today()->subDays($d))->count())->all();

        return [
            Stat::make('New tour requests', $new)
                ->description($new ? 'Waiting for your reply' : 'All caught up')
                ->descriptionIcon($new ? 'heroicon-m-bell-alert' : 'heroicon-m-check-circle')
                ->color($new ? 'warning' : 'success')->chart($spark),
            Stat::make('Tours in the next 7 days', $upcoming)
                ->description('Confirmed & contacted')->descriptionIcon('heroicon-m-calendar-days')->color('info'),
            Stat::make('Ticket sales this month', 'AED ' . number_format($month))
                ->description($lastMonth ? (($month >= $lastMonth ? '+' : '') . round(($month - $lastMonth) / $lastMonth * 100) . '% vs last month') : 'Paid orders')
                ->descriptionIcon($month >= $lastMonth ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color('success'),
            Stat::make('Ticket orders today', $today)
                ->description('Paid online')->descriptionIcon('heroicon-m-ticket')->color('primary'),
        ];
    }
}
