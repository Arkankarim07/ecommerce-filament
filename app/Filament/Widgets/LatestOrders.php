<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use App\Models\Order;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use App\Filament\Resources\OrderResource;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestOrders extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = 2;
    public function table(Table $table): Table
    {
        return $table
            ->query(OrderResource::getEloquentQuery()) // getEloquentQuery() adalah query yang digunakan dalam resource filament
            ->defaultPaginationPageOption(5) //berguna untuk mengatur nomor halaman default
            ->defaultSort('created_at', 'desc') //berguna untuk mengatur urutan default
            ->columns([
                TextColumn::make('id')
                    ->searchable()
                    ->label('Order ID'),

                    TextColumn::make('user.name') //mengambil relationship dari model user yg sudah di Eloquent: Relationships pada Model Order
                    ->searchable(),

                TextColumn::make('grand_total')
                    ->money('IDR'),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'new' => 'info',
                        'processing' => 'primary',
                        'shipped' => 'success',
                        'delivered' => 'success',
                        'cancelled' => 'danger',
                    })

                    ->icon(fn(string $state): string => match ($state) {

                        'new' => 'heroicon-m-sparkles',
                        'processing' => 'heroicon-m-arrow-path',
                        'shipped' => 'heroicon-o-truck',
                        'deliverd' => 'heroicon-m-check-badge',
                        'cancelled' => 'heroicon-o-x-circle',
                    }),

                TextColumn::make('payment_method')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('payment_status')
                    ->sortable()
                    ->badge()
                    ->searchable(),

                TextColumn::make('created_at')
                    ->label('Order Date')
                    ->dateTime('d M, Y H:i A')
            ])
            ->actions([
                Action::make('View Order')
                ->url(fn(Order $record): string => OrderResource::getUrl('view', ['record' => $record]))
                ->icon('heroicon-o-eye')
            ]);
    }
}
