<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderRequestResource\Pages;
use App\Filament\Resources\OrderRequestResource\RelationManagers;
use App\Models\OrderRequest;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Traits\HasResourcePermissions;

class OrderRequestResource extends Resource
{
    use HasResourcePermissions;

    protected static string $resourceName = 'orderrequests';
    protected static ?string $model = OrderRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';  // promenjeno
    protected static ?string $navigationLabel = 'Porudžbina';               // dodato
    // protected static ?int $navigationSort = 4;      
    public static function getNavigationGroup(): ?string
    {
        return 'Magacin';
    }

    public static function getNavigationSort(): ?int
    {
        return 2;
    }
    public static function form(Form $form): Form
    {
        return $form->schema([
            Wizard::make([
                Wizard\Step::make('Unos narudžbe')
                    ->schema([
                        TextInput::make('customer_name')
                            ->label('Kupac')
                            ->required()
                            ->maxLength(255),

                        Repeater::make('items')
                            ->label('Proizvodi')
                            ->relationship('items')
                            ->schema([
                                Select::make('product_id')
                                    ->label('Proizvod')
                                    ->options(Product::all()->pluck('name', 'id')->toArray())
                                    ->searchable()
                                    ->required(),

                                TextInput::make('quantity')
                                    ->label('Količina')
                                    ->numeric()
                                    ->minValue(1)  // Ispravljeno sa ->min(1)
                                    ->required(),
                            ])
                            ->minItems(1)
                            ->required(),
                    ]),

                Wizard\Step::make('Provera lagera')
                    ->schema([
                        // Ovde ide View komponenta ili Livewire komponenta
                        // Forms\Components\View::make('filament.orders.check-stock')
                    ]),
            ])
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Kupac')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('items_count')
                    ->label('Broj proizvoda')
                    ->counts('items')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Datum')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(), // omogućava pregled
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListOrderRequests::route('/'),
            'create' => Pages\CreateOrderRequest::route('/create'),
            'edit' => Pages\EditOrderRequest::route('/{record}/edit'),
        ];
    }
}
