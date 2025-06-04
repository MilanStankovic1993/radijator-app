<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkOrderResource\Pages;
use App\Filament\Resources\WorkOrderResource\RelationManagers\WorkOrderItemsRelationManager;
use App\Models\WorkOrder;
use App\Filament\Resources\WorkOrderResource\Widgets\WorkOrdersAreaChart;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\HasManyRepeater;

class WorkOrderResource extends Resource
{
    protected static ?string $model = WorkOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Radni nalozi';
    // protected static ?int $navigationSort = 2;
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
        return $form->schema([
            Tabs::make('Radni nalog')
                ->tabs([
                    Tabs\Tab::make('Osnovno')
                        ->schema([
                            TextInput::make('work_order_number')
                                ->label('Broj radnog naloga')
                                ->required(),

                            Hidden::make('user_id')
                                ->default(fn () => auth()->id()), // koristi callback da se uzme ID trenutno prijavljenog korisnika

                            DatePicker::make('launch_date')
                                ->label('Datum lansiranja')
                                ->required(),

                            Select::make('product_id')
                                ->label('Artikal')
                                ->relationship('product', 'name')
                                ->preload()
                                ->required(),

                            TextInput::make('quantity')
                                ->label('Količina')
                                ->numeric()
                                ->required()
                                ->reactive() // omogućava instant promenu vrednosti i aktivira callback
                                ->afterStateUpdated(function ($state, callable $set) {
                                    // Kada se količina menja, resetuj artikle
                                    $set('items', []);
                                }),
                            Select::make('status')
                                ->label('Status')
                                ->options([
                                    'aktivan' => 'Aktivan',
                                    'zavrsen' => 'Završen',
                                    'neaktivan' => 'Neaktivan',
                                ])
                                ->default('aktivan')
                                ->required(),
                        ]),
                        // Tabs\Tab::make('Proizvodnja')
                    //     ->schema([
                    //         HasManyRepeater::make('items')
                    //             ->relationship('items')
                    //             ->schema([
                    //                 TextInput::make('code')->label('Šifra'),
                    //                 Select::make('work_phase_id')
                    //                     ->relationship('workPhase', 'name')
                    //                     ->label('Faza'),
                    //                 Forms\Components\Toggle::make('is_confirmed')->label('Potvrđeno'),
                    //             ])
                    //             ->columns(3)
                    //             ->createItemButtonLabel('Dodaj proizvodnju'),
                    //     ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('work_order_number')->label('Broj')->searchable()->sortable(),
                TextColumn::make('user.name')->label('Izdao')->searchable()->sortable(),
                TextColumn::make('product.name')->label('Artikal')->searchable()->sortable(),
                TextColumn::make('launch_date')->label('Datum lansiranja')->date(),
                TextColumn::make('confirmed_items_percentage')
                    ->label('Procenat odrađenog')
                    ->getStateUsing(function (WorkOrder $record) {
                        return $record->confirmedItemsPercentage() . '%';
                    })
                    ->sortable(),
                BadgeColumn::make('status')->label('Status')->colors([
                    'aktivan' => 'success',
                    'neaktivan' => 'danger',
                    'zavrsen' => 'warning',
                ]),
                TextColumn::make('created_at')->label('Kreirano')->since(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'aktivan' => 'Aktivan',
                        'zavrsen' => 'Završen',
                        'neaktivan' => 'Neaktivan',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('status', 'desc')
            ->searchDebounce(500); // debounce za bolje UX
    }

    public static function getRelations(): array
    {
        return [
            WorkOrderItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'edit' => Pages\EditWorkOrder::route('/{record}/edit'),
            'index' => Pages\ListWorkOrders::route('/'),
            'create' => Pages\CreateWorkOrder::route('/create'),
            'view' => Pages\ViewWorkOrder::route('/{record}'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            WorkOrdersAreaChart::class,
        ];
    }
}
