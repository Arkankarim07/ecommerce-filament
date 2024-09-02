<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Filament\Resources\OrderResource\RelationManagers\AddressRelationManager;
use App\Models\Order;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Number;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 5; //berguna untuk mengurutkan menu navigasi

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()
                    ->schema([
                        Section::make('Order Information')
                            ->schema([
                                Select::make('user_id')
                                    ->required()
                                    ->preload()
                                    ->searchable()
                                    ->relationship('user', 'name'),

                                Select::make('payment_method')
                                    ->options([
                                        'stripe' => 'Stripe',
                                        'cod' => 'Cash on delivery',
                                    ])
                                    ->required(),

                                Select::make('payment_status')
                                    ->options([
                                        'pending' => 'Pending',
                                        'paid' => 'Paid',
                                        'failed' => 'Failed',
                                    ])
                                    ->required()
                                    ->default('pending'),

                                ToggleButtons::make('status')
                                    ->options([
                                        'new' => 'New',
                                        'processing' => 'Processing',
                                        'shipped' => 'Shipped',
                                        'delivered' => 'Deliverd',
                                        'canceled' => 'Cancelled',
                                    ])
                                    ->colors([
                                        'new' => 'info',
                                        'processing' => 'warning',
                                        'shipped' => 'success',
                                        'deliverd' => 'success',
                                        'cancelled' => 'danger',
                                    ])
                                    ->icons([
                                        'new' => 'heroicon-m-sparkles',
                                        'processing' => 'heroicon-m-arrow-path',
                                        'shipped' => 'heroicon-o-truck',
                                        'deliverd' => 'heroicon-m-check-badge',
                                        'cancelled' => 'heroicon-o-x-circle',
                                    ])
                                    ->required()
                                    ->default('new')
                                    ->inline(),

                                Select::make('currency')
                                    ->options([
                                        'idr' => 'Indonesian Rupiah (Rp)',
                                        'usd' => 'United States Dollar (USD)',
                                        'eur' => 'Euro (EUR)',

                                    ])
                                    ->required()
                                    ->default('idr'),

                                Select::make('shipping_method')
                                    ->options([
                                        'fedex' => 'FedEx',
                                        'jne' => 'JNE',
                                        'sicepat' => 'Sicepat',
                                    ]),

                                Textarea::make('notes')
                                    ->columnSpanFull()
                            ])->columns(2),

                        Section::make('Order Items')
                            ->schema([
                                Repeater::make('items') // dari model order (fn items)
                                    ->relationship()
                                    ->schema([
                                        Select::make('product_id')
                                            ->required()
                                            ->relationship('product', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->distinct()
                                            ->disableOptionsWhenSelectedInSiblingRepeaterItems() //mencegah select yang sama
                                            ->reactive()
                                            ->afterStateUpdated(fn($state, Set $set) => $set('unit_amount', Product::find($state)?->price ?? 0))
                                            ->afterStateUpdated(fn($state, Set $set) => $set('total_amount', Product::find($state)?->price ?? 0))
                                            ->columnSpan(4),


                                        TextInput::make('quantity')
                                            ->required()
                                            ->minValue(1)
                                            ->numeric()
                                            ->default(1)
                                            ->reactive()
                                            ->afterStateUpdated(fn($state, Set $set, Get $get) => $set('total_amount', $state * $get('unit_amount')))
                                            ->columnSpan(2),

                                        TextInput::make('unit_amount')
                                            ->required()
                                            ->numeric()
                                            ->disabled()
                                            ->dehydrated()
                                            ->columnSpan(3),

                                        TextInput::make('total_amount')
                                            ->required()
                                            ->numeric()
                                            ->dehydrated()
                                            ->columnSpan(3),
                                    ])->columns(12),

                                Placeholder::make('grand_total_placeholder')
                                    ->label('Grand Total')
                                    ->content(function (Get $get, Set $set) {
                                        $total = 0;
                                        // jika tidak ada repeater, return 0
                                        if (!$repeaters = $get('items')) {
                                            return $total;
                                        }

                                        // jika ada repeater
                                        foreach ($repeaters as $item) {
                                            $total += $item['total_amount'];
                                        }

                                        $set('grand_total', $total);
                                        return Number::currency($total, 'IDR');
                                    }),

                                Hidden::make('grand_total')
                                    ->required()
                                    ->default(0)
                            ])
                    ])->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('grand_total')
                    ->label('Grand Total')
                    ->sortable()
                    ->money('IDR'),

                TextColumn::make('payment_method')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('payment_status')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('currency')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('shipping_method')
                    ->sortable()
                    ->searchable(),

                SelectColumn::make('status')
                    ->options([
                        'new' => 'New',
                        'processing' => 'Processing',
                        'shipped' => 'Shipped',
                        'delivered' => 'Delivered',
                        'canceled' => 'cancelled',
                    ])
                    ->searchable()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true), //mengaktifkan kolom toggle, dan menonaktifkan kolom default

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true), //mengaktifkan kolom toggle, dan menonaktifkan kolom default


            ])
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            AddressRelationManager::class
        ];
    }

    public static function getNavigationBadge(): ?string {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): string|array|null {
        return static::getModel()::count() > 10 ? 'success' : 'danger';
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
