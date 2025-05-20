<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Traits\HasResourcePermissions;
use App\Filament\Resources\ProductResource\RelationManagers\WorkPhasesRelationManager;


class ProductResource extends Resource
{
    use HasResourcePermissions;

    protected static string $resourceName = 'products';
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Product Tabs')
                    ->tabs([
                        Tab::make('Edit Product')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Naziv')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('code')
                                    ->label('Šifra')
                                    ->required()
                                    ->maxLength(100),
                                Forms\Components\Textarea::make('description')
                                    ->label('Opis')
                                    ->rows(3)
                                    ->maxLength(1000),
                                Forms\Components\Textarea::make('specifications')
                                    ->label('Specifikacije')
                                    ->rows(3)
                                    ->maxLength(1000),
                                Forms\Components\TextInput::make('price')
                                    ->label('Cena')
                                    ->numeric()
                                    ->required(),
                                Forms\Components\Toggle::make('is_active')
                                    ->label('Aktivan')
                                    ->default(true),
                            ]),

                        Tab::make('Radne faze')
                            ->schema([
                                // možeš dodati info ili komponentu za prikaz radnih faza ako želiš
                                Forms\Components\Placeholder::make('info')->content('Radne faze su dostupne kroz relacije.'),
                            ]),

                        Tab::make('Import Sastavnice')
                            ->schema([
                                Forms\Components\FileUpload::make('import_file')
                                    ->label('Uvezi sastavnicu')
                                    ->acceptedFileTypes([
                                        '.xlsx',
                                        '.xls',
                                        '.csv',
                                        '.txt',
                                        '.json',
                                        '.xml',
                                    ]),
                            ]),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Naziv')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('code')->label('Šifra')->sortable(),
                Tables\Columns\TextColumn::make('price')->label('Cena')->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktivan')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Aktivan'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            WorkPhasesRelationManager::class,
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
