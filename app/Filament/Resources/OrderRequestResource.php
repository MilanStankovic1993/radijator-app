<?php

namespace App\Filament\Resources;

use App\Helpers\FilamentColumns;
use Illuminate\Support\Facades\Auth;
use App\Filament\Resources\OrderRequestResource\Pages;
use App\Models\OrderRequest;
use App\Models\Product;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Traits\HasResourcePermissions;

class OrderRequestResource extends Resource
{
    use HasResourcePermissions;

    protected static string $resourceName = 'orderrequests';
    protected static ?string $model = OrderRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'Porudžbina';

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
                        Select::make('customer_id')
                            ->label('Kupac')
                            ->options(Customer::all()->pluck('name', 'id')->toArray())
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                TextInput::make('name')->required()->label('Ime kupca'),
                                // po potrebi dodaj još polja kupca koja želiš da uneseš prilikom kreiranja
                            ])
                            ->createOptionUsing(function (array $data) {
                                return Customer::create($data)->id;
                            }),

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
                                    ->minValue(1)
                                    ->required(),
                            ])
                            ->minItems(1)
                            ->required(),
                    ]),

                Wizard\Step::make('Provera lagera')
                    ->schema([
                        // Ovde možeš dodati View komponentu ili Livewire komponentu za proveru lagera
                    ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Kupac')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('items_count')
                    ->label('Broj proizvoda')
                    ->counts('items')
                    ->toggleable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Datum')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                ...FilamentColumns::userTrackingColumns(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
