<?php

namespace App\Filament\Resources\OrderResource\Widgets;

use App\Models\Order;
use Illuminate\Support\Number;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class OrderStats extends BaseWidget
{
    // php artisan make:filament-widget OrderStats --resource=OrderResource
    protected function getStats(): array
    {
        return [
            Stat::make('New Orders', Order::query()->where('status', 'new')->count()),
            Stat::make('Processing Orders', Order::query()->where('status', 'processing')->count()),
            Stat::make('Shipped Orders', Order::query()->where('status', 'shipped')->count()),
            Stat::make('Delivered Orders', Order::query()->where('status', 'delivered')->count()),
            Stat::make('Cancelled Orders', Order::query()->where('status', 'cancelled')->count()),
            Stat::make('Average Total Price Orders', !(empty(Order::query()->avg('grand_total'))) ? Number::currency(Order::query()->avg('grand_total'), 'IDR') : '-'),
        ];
    }
}
