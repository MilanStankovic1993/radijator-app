<?php

namespace App\Filament\Resources;

use App\Helpers\FilamentColumns;
use Illuminate\Support\Facades\Auth;
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
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\BadgeColumn;

class ProductResource extends Resource
{
    use HasResourcePermissions;

    protected static string $resourceName = 'products';
    protected static ?string $model = Product::class;
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
            ->columns(1)
            ->extraAttributes([
                'style' => 'max-width: 1400px; margin: 0;'
            ])
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
                                Forms\Components\Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'active' => 'Aktivan',
                                        'inactive' => 'Neaktivan',
                                        'discontinued' => 'Obustavljen',
                                    ])
                                    ->default('active')
                                    ->required(),
                            ]),
                        Tab::make('Import Sastavnice')
                            ->schema([
                                Forms\Components\FileUpload::make('import_file')
                                    ->label('Uvezi sastavnicu')
                                    ->directory('uploads')
                                    ->rules([
                                        'file',
                                        'mimes:csv,xlsx,xls,json,xml,txt,pdf'
                                    ])
                                    ->visibility('public')
                                    ->preserveFilenames()
                                    ->disk('public')
                                    ->maxSize(5120)
                                    ->required(false),
                            ]),
                    ]),
            ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Naziv')->searchable()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('code')->label('Šifra')->sortable()->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('price')->label('Cena')->sortable()->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('import_file')
                    ->label('Sastavnica')
                    ->url(fn ($record) => $record->import_file ? asset('storage/' . $record->import_file) : null, true)
                    ->openUrlInNewTab()
                    ->formatStateUsing(fn ($state) => $state ? basename($state) : '-')
                    ->toggleable(),
                BadgeColumn::make('status')->label('Status')->colors([
                        'aktivan' => 'success',
                        'neaktivan' => 'danger',
                        'zavrsen' => 'warning',
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'active' => 'Aktivan',
                        'inactive' => 'Neaktivan',
                        'discontinued' => 'Obustavljen',
                        default => $state,
                    })
                    ->sortable()
                    ->toggleable(),
                ...FilamentColumns::userTrackingColumns(),
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
            \App\Filament\Resources\ProductResource\RelationManagers\WorkPhasesRelationManager::class,
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
