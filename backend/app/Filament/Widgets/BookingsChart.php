<?php

namespace App\Filament\Widgets;

use App\Models\Enquiry;
use App\Models\Order;
use Filament\Widgets\ChartWidget;

class BookingsChart extends ChartWidget
{
    protected static ?int $sort = 2;
    protected ?string $heading = 'Bookings – last 30 days';
    protected int|string|array $columnSpan = 'full';
    protected ?string $maxHeight = '280px';

    protected function getData(): array
    {
        $days = collect(range(29, 0))->map(fn ($d) => today()->subDays($d));
        $count = fn ($model, $extra = null) => $days->map(fn ($day) => $model::query()
            ->when($extra, $extra)->whereDate('created_at', $day)->count())->all();

        return [
            'datasets' => [
                ['label' => 'Tour requests', 'data' => $count(Enquiry::class), 'borderColor' => '#e8873a', 'backgroundColor' => 'rgba(232,135,58,.15)', 'fill' => true, 'tension' => .35],
                ['label' => 'Paid ticket orders', 'data' => $count(Order::class, fn ($q) => $q->where('status', 'paid')), 'borderColor' => '#3c9a4a', 'backgroundColor' => 'rgba(60,154,74,.12)', 'fill' => true, 'tension' => .35],
            ],
            'labels' => $days->map(fn ($d) => $d->format('d M'))->all(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
