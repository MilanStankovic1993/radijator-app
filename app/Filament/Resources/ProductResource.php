<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
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
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Repeater;

class ProductResource extends Resource
{
    use HasResourcePermissions;

    protected static string $resourceName = 'products';
    protected static ?string $model = Product::class;
    // protected static ?int $navigationSort = 1;
    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationLabel = 'Gotovi proizvodi';
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
                Tabs::make('Product Tabs')
                    ->tabs([
                        Tab::make('Izmene Kotla')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Naziv')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('code')
                                    ->label('Šifra')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->validationMessages([
                                        'unique' => 'Artikal sa ovim kodom vec postoji.',
                                    ])
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

                        Tab::make('Radne faze u proizvodji')
                            ->schema([
                                Repeater::make('workPhases')
                                    ->label('Radne faze')
                                    ->relationship('workPhases')
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->label('Naziv faze')
                                            ->required(),
                                        Forms\Components\Textarea::make('description')
                                            ->label('Opis')
                                            ->rows(2),
                                    ])
                                    ->columns(2)
                                    ->orderable()
                                    ->defaultItems(0)
                                    ->addActionLabel('Dodaj fazu'),
                            ]),

                        Tab::make('Import Sastavnice')
                            ->schema([
                                Forms\Components\FileUpload::make('import_file')
                                    ->label('Uvezi sastavnicu')
                                    ->directory('uploads') // folder u storage/app/public/uploads
                                    ->rules([
                                        'file',
                                        'mimes:csv,xlsx,xls,json,xml,txt', // ovde mora biti xlsx da bi prihvatio Excel fajlove
                                    ])
                                    ->visibility('public')
                                    ->preserveFilenames()  // da ne menja ime fajla
                                    ->disk('public')  // Obavezno ako koristiš Storage disk 'public'
                                    ->maxSize(5120)
                                    ->required(false)
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
                // Tables\Columns\TextColumn::make('import_file')
                // ->label('Sastavnica')
                // ->formatStateUsing(function ($state) {
                //     if ($state) {
                //         $url = asset('storage/' . $state);
                //         return "<a href=\"{$url}\" target=\"_blank\" class=\"text-primary-600 hover:underline\">Prikaži fajl</a>";
                //     } else {
                //         return '-';
                //     }
                // })
                // ->html(), // dozvoljava HTML u koloni
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktivan')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Aktivan'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // I dalje može ostati za prikaz u posebnom tabu ako želiš u budućnosti
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
