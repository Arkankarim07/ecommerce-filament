<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Product;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\MarkdownEditor;
use App\Filament\Resources\ProductResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ProductResource\RelationManagers;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?int $navigationSort = 4; //berguna untuk mengurutkan menu navigasi

    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            Group::make()->schema([
                Section::make('Product Information')
                    ->schema([
                        TextInput::make('name')
                        ->required()
                        ->maxLength(225)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (string $operation, $state, Set $set) {
                            if($operation !== 'create') {
                                return;
                            } else {
                                $set('slug', Str::slug($state));
                            }

                        }),
    
                        TextInput::make('slug')
                        ->required()
                        ->unique(Product::class, 'slug', ignoreRecord: true)
                        ->disabled()
                        ->dehydrated(), //walau sudah disabled, dehydrated ttp akan mengambil nilai input dari form
    
                        MarkdownEditor::make('description')
                        ->columnSpanFull()
                        ->fileAttachmentsDirectory('products')
                    ])->columns(2),

                    Section::make('Images')
                    ->schema([
                        FileUpload::make('image')
                        ->multiple()
                        ->directory('products')
                        ->maxFiles(5)
                        ->reorderable() //memungkinkan pengguna menyeret dan menjatuhkan (drag-and-drop) baris dalam sebuah tabel untuk mengubah urutan item secara langsung
                    ])
            ])->columnSpan(2),

            Group::make()->schema([
                Section::make('Price')
                ->schema([
                    TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('IDR')
                ]),

                Section::make(('Associations'))
                ->schema([
                    Select::make('category_id')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->relationship('category', 'name'), //reference from Model Brand::class : category()

                    Select::make('brand_id')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->relationship('brand', 'name') //reference from Model Brand::class : brand()
                ]),

                Section::make('Status')
                ->schema([
                    Toggle::make('in_stock')
                    ->required()
                    ->default(true),

                    Toggle::make('is_active')
                    ->required()
                    ->default(true),

                    Toggle::make('is_featured')
                    ->required(),
                    Toggle::make('on_sale')
                    ->required()
                ]
                )
            ])->columnSpan(1)
        ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                ->searchable(),

                TextColumn::make('category.name')
                ->searchable(),

                TextColumn::make('brand.name')
                ->searchable(),

                TextColumn::make('price')
                ->money('IDR')
                ->sortable(),

                IconColumn::make('is_featured')
                ->boolean(),

                IconColumn::make('on_sale')
                ->boolean(),

                IconColumn::make('in_stock')
                ->boolean(),

                IconColumn::make('is_active')
                ->boolean(),

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
                SelectFilter::make('category')
                ->relationship('category', 'name'),
                SelectFilter::make('brand')
                ->relationship('brand', 'name')
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
