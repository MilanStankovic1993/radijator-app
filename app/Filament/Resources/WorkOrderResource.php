<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkOrderResource\Pages;
use App\Filament\Resources\WorkOrderResource\RelationManagers\WorkOrderItemsRelationManager;
use App\Models\WorkOrder;
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
    protected static ?int $navigationSort = 2;

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

                    Tabs\Tab::make('Artikli')
                        ->schema([
                            HasManyRepeater::make('items')
                                ->relationship() // automatski povezuje prema 'items' relaciji u modelu
                                ->schema([
                                    TextInput::make('code')
                                        ->label('Kod artikla')
                                        ->required(),

                                    TextInput::make('name')
                                        ->label('Naziv artikla')
                                        ->required(),

                                    Select::make('status')
                                        ->label('Status')
                                        ->options([
                                            'pending' => 'Na čekanju',
                                            'in_progress' => 'U toku',
                                            'done' => 'Završeno',
                                        ])
                                        ->default('pending')
                                        ->required(),
                                ])
                                ->columns(3)
                                ->createItemButtonLabel('Dodaj artikal')
                                ->afterStateHydrated(function ($state, callable $set, $get) {
                                    $quantity = $get('quantity') ?? 0;
                                    $items = $state ?? [];

                                    if (count($items) < $quantity) {
                                        for ($i = count($items); $i < $quantity; $i++) {
                                            $items[] = ['code' => '', 'name' => '', 'status' => 'pending'];
                                        }
                                        $set('items', $items);
                                    }
                                })
                                ->afterStateUpdated(function ($state, callable $set, $get) {
                                    $quantity = $get('quantity') ?? 0;
                                    $items = $state ?? [];

                                    if (count($items) < $quantity) {
                                        for ($i = count($items); $i < $quantity; $i++) {
                                            $items[] = ['code' => '', 'name' => '', 'status' => 'pending'];
                                        }
                                        $set('items', $items);
                                    } elseif (count($items) > $quantity) {
                                        $items = array_slice($items, 0, $quantity);
                                        $set('items', $items);
                                    }
                                }),
                        ]),
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
        ])
        ->bulkActions([
            Tables\Actions\DeleteBulkAction::make(),
        ])
        // ->expandable() // omogući proširivanje reda
        // ->expanded(function (WorkOrder $record) {
        //     return view('filament.work-orders.partials.details', [
        //         'workOrder' => $record,
        //     ]);
        // })
        ->defaultSort('status', 'desc');
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
            'index' => Pages\ListWorkOrders::route('/'),
            'create' => Pages\CreateWorkOrder::route('/create'),
            'edit' => Pages\EditWorkOrder::route('/{record}/edit'),
            'view' => Pages\ViewWorkOrder::route('/{record}'),
        ];
    }
}
