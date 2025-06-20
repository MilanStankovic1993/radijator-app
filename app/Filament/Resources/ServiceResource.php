<?php

namespace App\Filament\Resources;

use App\Helpers\FilamentColumns;
use Illuminate\Support\Facades\Auth;
use App\Models\Service;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\ServiceResource\Pages;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-circle';
    protected static ?string $navigationLabel = 'Reklamacije';
    protected static ?string $modelLabel = 'Reklamacija';
    protected static ?string $pluralModelLabel = 'Reklamacije';

    public static function getNavigationGroup(): ?string
    {
        return 'Proizvodnja';
    }

    public static function getNavigationSort(): ?int
    {
        return 1;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('customer_id')
                    ->label('Kupac')
                    ->options(Customer::all()->pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->required()
                    ->createOptionForm([
                        TextInput::make('name')->required()->label('Ime kupca'),
                    ])
                    ->createOptionUsing(fn (array $data) => Customer::create($data)->id),

                Select::make('product_id')
                    ->label('Proizvod')
                    ->options(\App\Models\Product::all()->pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->required(),

                Textarea::make('description')
                    ->label('Opis reklamacije')
                    ->required()
                    ->rows(4),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('customer.name')->label('Kupac')->sortable()->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('product.name')->label('Proizvod')->sortable()->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('description')->label('Opis')->limit(50)->searchable()->sortable()->toggleable(),
                ...FilamentColumns::userTrackingColumns(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
