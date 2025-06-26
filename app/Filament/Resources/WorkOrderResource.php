<?php

namespace App\Filament\Resources;

use App\Helpers\FilamentColumns;
use Illuminate\Support\Facades\Auth;
use App\Filament\Resources\WorkOrderResource\Pages;
use App\Filament\Resources\WorkOrderResource\RelationManagers\WorkOrderItemsRelationManager;
use App\Models\WorkOrder;
use App\Models\Product;
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
use Filament\Tables\Filters\TernaryFilter;

class WorkOrderResource extends Resource
{
    protected static ?string $model = WorkOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Radni nalozi';

    public static function getNavigationGroup(): ?string
    {
        return 'Proizvodnja';
    }

    public static function getNavigationSort(): ?int
    {
        return 1;
    }

    public static function form(Form $form, $record = null): Form
    {
        $updateFullName = function ($get, $set) {
            $set('full_name',
                $get('work_order_number') . '.' .
                $get('product_name') . '.' .
                $get('series') . '.' .
                $get('quantity')
            );
        };

        $operation = $form->getOperation();
        if ($operation === 'create') {
            return $form->schema([
                Tabs::make('Radni nalog')
                    ->tabs([
                        Tabs\Tab::make('Osnovno')
                            ->schema([
                                Select::make('work_order_number')
                                    ->label('Broj radnog naloga')
                                    ->options([
                                        '021' => 'naručeno',
                                        '020' => 'zalihe',
                                        '022' => 'naručeno i dopunjeno za zalihe',
                                        '001' => 'prototip kotla/proizvoda',
                                        '002' => 'rekonstrukcija kotla/proizvoda',
                                        '003' => 'remont kotla/proizvoda',
                                        '090' => 'tehnološka proba',
                                        '100' => 'magacinske rezerve',
                                        '110' => 'usluga od našeg materijala',
                                        '111' => 'usluga od materijala naručioca',
                                        '112' => 'usluga od našeg i materijala kupca',
                                        '201' => 'pomoćni pribor, alat, naprava za proizvodnju',
                                        '202' => 'održavanje, remont opreme, dodatna oprema',
                                        '030' => 'rezervni delovi',
                                        '050' => 'Dopunski nalog (nedostajuće pozicije-zahtev Šeovac; Škart po RN',
                                    ])
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(fn ($state, $set, $get) => $updateFullName($get, $set)),

                                TextInput::make('series')
                                    ->label('Serija')
                                    ->required()
                                    ->validationAttribute('Serija')
                                    ->afterStateUpdated(fn ($state, $set, $get) => $updateFullName($get, $set)),

                                TextInput::make('quantity')
                                    ->label('Količina')
                                    ->numeric()
                                    ->required()
                                    ->afterStateUpdated(fn ($state, $set, $get) => $updateFullName($get, $set)),

                                Hidden::make('user_id')
                                    ->default(fn () => auth()->id()),

                                DatePicker::make('launch_date')
                                    ->label('Datum lansiranja')
                                    ->required(),

                                Select::make('product_id')
                                    ->label('Artikal')
                                    ->relationship('product', 'name')
                                    ->preload()
                                    ->required()
                                    ->reactive()
                                    ->live()
                                    ->afterStateUpdated(function ($state, $set, $get) use ($updateFullName) {
                                        $product = Product::find($state);
                                        $set('product_name', optional($product)->name);
                                        $updateFullName($get, $set);
                                    }),

                                Hidden::make('product_name'),

                                Hidden::make('full_name'),

                                Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'aktivan' => 'Aktivan',
                                        'u_toku' => 'U toku',
                                        'zavrsen' => 'Završen',
                                        'otkazan' => 'Otkazan',
                                    ])
                                    ->default('aktivan')
                                    ->disabled(),
                            ]),
                    ]),
            ]);
        } else {
            return $form
                ->schema([
                    Select::make('status')
                        ->label('Status')
                        ->options([
                            'aktivan' => 'Aktivan',
                            'u_toku' => 'U toku',
                            'zavrsen' => 'Završen',
                            'otkazan' => 'Otkazan',
                        ])
                        ->default(fn ($record) => $record->status)
                        ->visible(fn ($record) => $record?->type === 'custom')
                        ->required(),

                    Select::make('status_progresije')
                        ->label('Status progresije')
                        ->options([
                            'hitno' => '🔴 Hitno',
                            'ceka se' => '🟡 Čeka se',
                            'aktivan' => '🟢 Aktivan',
                        ])
                        ->default('aktivan')
                        ->required(),
                ]);
        }
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('full_name')
                    ->label('Radni nalog')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->extraAttributes(fn (WorkOrder $record) => $record->isTransferredToWarehouse() ? ['class' => 'italic font-semibold'] : []),

                BadgeColumn::make('completion_percentage')
                    ->label('%')
                    ->getStateUsing(fn (WorkOrder $record) => $record->status === 'zavrsen' ? 100 : $record->completion_percentage)
                    ->colors([
                        'danger' => fn ($state) => $state < 50,
                        'warning' => fn ($state) => $state >= 50 && $state < 80,
                        'info' => fn ($state) => $state >= 80 && $state < 100,
                        'success' => fn ($state) => $state === 100,
                    ])
                    ->formatStateUsing(fn ($state) => $state . ' %')
                    ->alignCenter()
                    ->toggleable()
                    ->extraAttributes(fn (WorkOrder $record) => $record->isTransferredToWarehouse() ? ['class' => 'italic font-semibold'] : []),

                TextColumn::make('launch_date')
                    ->label('Datum lansiranja')
                    ->date()
                    ->sortable()
                    ->extraAttributes(fn (WorkOrder $record) => $record->isTransferredToWarehouse() ? ['class' => 'italic font-semibold'] : []),

                TextColumn::make('ready_to_transfer_count')
                    ->label('Spremno za transfer')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('transferred_count')
                    ->label('Prebačeno')
                    ->sortable()
                    ->toggleable(),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'aktivan' => 'Aktivan',
                        'u_toku' => 'U toku',
                        'zavrsen' => 'Završen',
                        'otkazan' => 'Otkazan',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'aktivan' => 'success',
                        'u_toku' => 'info',
                        'zavrsen' => 'warning',
                        'otkazan' => 'danger',
                        default => 'gray',
                    })
                ->extraAttributes(fn (WorkOrder $record) => $record->isTransferredToWarehouse() ? ['class' => 'italic font-semibold'] : []),

                BadgeColumn::make('status_progresije')
                    ->label('Progresija')
                    ->color(fn (string $state): string => match ($state) {
                        'hitno' => 'danger',
                        'ceka se' => 'warning',
                        'aktivan' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'hitno' => 'Hitno',
                        'ceka se' => 'Čeka se',
                        'aktivan' => 'Aktivan',
                        default => $state,
                    })
                    ->sortable()
                    ->toggleable()
                    ->extraAttributes(fn (WorkOrder $record) => $record->isTransferredToWarehouse() ? ['class' => 'italic font-semibold'] : []),

                ...collect(FilamentColumns::userTrackingColumns())
                    ->map(function ($column) {
                        return $column
                            ->extraAttributes(fn (WorkOrder $record) =>
                                $record->isTransferredToWarehouse()
                                    ? ['class' => 'italic font-semibold']
                                    : []
                            )
                            ->toggleable(true);
                    })
                    ->all(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->visible(fn (WorkOrder $record) => !$record->isTransferredToWarehouse()),

                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\DeleteAction::make(),

                    Tables\Actions\Action::make('transfer_to_warehouse')
                        ->label('Transfer u magacin')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->requiresConfirmation()
                        ->color('success')
                        ->visible(fn (WorkOrder $record) =>
                            $record->ready_to_transfer_count > 0 &&
                            !$record->isTransferredToWarehouse()
                        )
                        ->form(fn (WorkOrder $record) => [
                            Forms\Components\Repeater::make('items')
                                ->label('Unesi kodove za ' . $record->ready_to_transfer_count . ' jedinica')
                                ->schema([
                                    Forms\Components\TextInput::make('code')
                                        ->label('Kod')
                                        ->required()
                                        ->unique(table: \App\Models\WarehouseItem::class, column: 'code'),
                                ])
                                ->minItems($record->ready_to_transfer_count)
                                ->maxItems($record->ready_to_transfer_count)
                                ->default(fn () => array_fill(0, $record->ready_to_transfer_count, ['code' => '']))
                                ->disableItemDeletion()
                                ->disableItemCreation()
                                ->columns(1),
                        ])
                        ->action(function (array $data, WorkOrder $record) {
                            $productId = $record->product_id;
                            $location = 'Seovac';

                            $warehouse = \App\Models\Warehouse::firstOrCreate([
                                'product_id' => $productId,
                                'location' => $location,
                                'status' => 'na_cekanju',
                            ], [
                                'quantity' => 0,
                                'created_by' => auth()->id(),
                                'updated_by' => auth()->id(),
                            ]);

                            $codeIndex = 0;
                            $totalTransferred = 0;

                            foreach ($record->items as $item) {
                                $readyCount = floor($item->total_completed) - $item->transferred_count;

                                if ($readyCount <= 0) {
                                    continue;
                                }

                                for ($i = 0; $i < $readyCount; $i++) {
                                    if (!isset($data['items'][$codeIndex]['code'])) {
                                        continue;
                                    }

                                    \App\Models\WarehouseItem::create([
                                        'warehouse_id' => $warehouse->id,
                                        'product_id' => $productId,
                                        'work_order_id' => $record->id,
                                        'location' => $location,
                                        'status' => 'na_cekanju',
                                        'code' => $data['items'][$codeIndex]['code'],
                                        'quantity' => 1,
                                        'created_by' => auth()->id(),
                                        'updated_by' => auth()->id(),
                                    ]);

                                    $codeIndex++;
                                    $totalTransferred++;
                                }

                                $item->increment('transferred_count', $readyCount);
                                $item->save();
                            }

                            $warehouse->increment('quantity', $totalTransferred);
                            $record->checkIfFullyTransferredAndUpdate();
                            $record->updateStatusBasedOnItems();

                            \Filament\Notifications\Notification::make()
                                ->title('Uspešno')
                                ->body("Prebačeno $totalTransferred komada u magacin.")
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\Action::make('already_transferred')
                        ->label('Prebačeno u magacin')
                        ->disabled()
                        ->color('gray')
                        ->visible(fn (WorkOrder $record) => $record->isTransferredToWarehouse()),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->filters([
                // Filter po procentu izvršenja
                Tables\Filters\Filter::make('completion_percentage_range')
                    ->label('Procenat izvršenja')
                    ->form([
                        Forms\Components\Select::make('range')
                            ->label('Opseg')
                            ->options([
                                '0-49' => 'Manje od 50%',
                                '50-79' => '50% do 79%',
                                '80-99' => '80% do 99%',
                                '100'   => '100%',
                            ])
                            ->placeholder('Odaberi opseg'),
                    ])
                    ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data) {
                        if (empty($data['range'])) {
                            return $query;
                        }

                        return $query->where(function ($query) use ($data) {
                            return match ($data['range']) {
                                '0-49' => $query->whereRaw('(SELECT COUNT(*) FROM work_order_items WHERE work_order_id = work_orders.id AND is_confirmed = true) * 100 / GREATEST((SELECT COUNT(*) FROM work_order_items WHERE work_order_id = work_orders.id), 1) < 50'),
                                '50-79' => $query->whereRaw('(SELECT COUNT(*) FROM work_order_items WHERE work_order_id = work_orders.id AND is_confirmed = true) * 100 / GREATEST((SELECT COUNT(*) FROM work_order_items WHERE work_order_id = work_orders.id), 1) BETWEEN 50 AND 79'),
                                '80-99' => $query->whereRaw('(SELECT COUNT(*) FROM work_order_items WHERE work_order_id = work_orders.id AND is_confirmed = true) * 100 / GREATEST((SELECT COUNT(*) FROM work_order_items WHERE work_order_id = work_orders.id), 1) BETWEEN 80 AND 99'),
                                '100'   => $query->whereRaw('(SELECT COUNT(*) FROM work_order_items WHERE work_order_id = work_orders.id AND is_confirmed = true) * 100 / GREATEST((SELECT COUNT(*) FROM work_order_items WHERE work_order_id = work_orders.id), 1) = 100'),
                                default => $query, // bez filtera ako vrednost nije podržana
                            };
                        });
                    }),

                // Filter po statusu
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'aktivan' => 'Aktivan',
                        'u_toku' => 'U toku',
                        'zavrsen' => 'Završen',
                        'otkazan' => 'Otkazan',
                    ])
                    ->placeholder('Svi statusi'),

                // Filter po progresiji
                Tables\Filters\SelectFilter::make('status_progresije')
                    ->label('Status progresije')
                    ->options([
                        'hitno' => '🔴 Hitno',
                        'ceka se' => '🟡 Čeka se',
                        'aktivan' => '🟢 Aktivan',
                    ])
                    ->placeholder('Sve progresije'),
            ])
            ->defaultSort('updated_at', 'desc')
            ->searchDebounce(500)
            ->recordUrl(fn (WorkOrder $record) => static::getUrl('edit', ['record' => $record]))
            ->recordAction(null);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->with('items')
            ->where(function ($query) {
                $query->where('status', '!=', 'zavrsen')
                    ->orWhere(function ($query) {
                        $query->where('is_transferred_to_warehouse', false)
                            ->orWhereNull('is_transferred_to_warehouse');
                    })
                    ->where('type', '!=', 'custom');
            });
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
            'custom-create' => Pages\CustomCreateWorkOrder::route('/custom-create'),
            'view' => Pages\ViewWorkOrder::route('/{record}'),
        ];
    }
}