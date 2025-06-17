<?php

// app/Filament/Resources/WorkOrderResource.php

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
use Filament\Tables\Actions\Action;

class WorkOrderResource extends Resource
{
    protected static ?string $model = WorkOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Radni nalozi';

    /**
     * Get the navigation group for the resource.
     *
     * @return string|null
     */
    public static function getNavigationGroup(): ?string
    {
        return 'Proizvodnja';
    }


    /**
     * Control the order in which the resource is displayed in the navigation.
     *
     * @return int|null
     */
    public static function getNavigationSort(): ?int
    {
        return 1;
    }

    /**
     * The form schema definition for the resource.
     *
     * @param  \Filament\Forms\Form  $form
     * @param  \App\Models\WorkOrder|null  $record
     * @return \Filament\Forms\Form
     */
    public static function form(Form $form, $record = null): Form
    {
        $updateFullName = function ($get, $set) {
            $set('full_name', 
                $get('work_order_number') . '.' .
                $get('product_name') . '.' .
                $get('series') . '-' .
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
                                // ->reactive()
                                ->live()
                                ->afterStateUpdated(fn ($state, $set, $get) => $updateFullName($get, $set)),

                            TextInput::make('series')
                                ->label('Serija')
                                // ->integer()
                                ->required()
                                // ->reactive()
                                ->afterStateUpdated(fn ($state, $set, $get) => $updateFullName($get, $set)),

                            TextInput::make('quantity')
                                ->label('Količina')
                                ->numeric()
                                ->required()
                                // ->reactive()
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
                                    'zavrsen' => 'Završen',
                                    'neaktivan' => 'Neaktivan',
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
                            'zavrsen' => 'Završen',
                            'neaktivan' => 'Neaktivan',
                        ])
                        ->default(fn ($record) => $record->status)
                        ->visible(fn ($record) => $record?->type === 'custom') // 👈 ovde je razlika
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

    /**
     * The table schema definition for the resource.
     *
     * Defines the columns, actions, and bulk actions for the table
     * display. The columns are: full name, user, product, completion percentage,
     * launch date, status, and progresija. The actions are: edit, view, delete,
     * and transfer to warehouse (only visible for completed orders with
     * confirmed items). The bulk actions are: delete.
     *
     * @param  \Filament\Tables\Table  $table
     * @return \Filament\Tables\Table
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('full_name')->label('Radni nalog')->searchable()->sortable()->toggleable(),
                TextColumn::make('user.name')->label('Izdao')->searchable()->sortable()->toggleable(),
                TextColumn::make('product.name')->label('Artikal')->searchable()->sortable()->toggleable(),
                BadgeColumn::make('completion_percentage')
                    ->label('Procenat izvršenja')
                    ->colors([
                        'danger' => fn ($state) => $state < 50,
                        'warning' => fn ($state) => $state >= 50 && $state < 80,
                        'info' => fn ($state) => $state >= 80 && $state < 100,
                        'success' => fn ($state) => $state === 100,
                    ])
                    ->formatStateUsing(fn ($state) => $state . ' %')
                    ->sortable()
                    ->alignCenter()
                    ->toggleable()
                    ->searchable(), // ako koristiš kao tekst
                TextColumn::make('launch_date')->label('Datum lansiranja')->date()->sortable(),
                BadgeColumn::make('status')->label('Status')->colors([
                    'aktivan' => 'success',
                    'neaktivan' => 'danger',
                    'zavrsen' => 'warning',
                ]),
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
                    ->toggleable(),
                ...FilamentColumns::userTrackingColumns(),
            ])
            ->actions([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\Action::make('transfer_to_warehouse')
                        ->label('Transfer u magacin')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->requiresConfirmation()
                        ->color('success')
                        ->visible(fn (WorkOrder $record) =>
                            $record->status === 'zavrsen' &&
                            $record->items()->count() > 0 &&
                            $record->items()->where('is_confirmed', false)->count() === 0 &&
                            !\App\Models\Warehouse::where('product_id', $record->product_id)
                                ->where('location', 'Glavno skladište') // Ako imaš default location
                                ->exists()
                        )
                        ->action(function (WorkOrder $record) {
                            $alreadyTransferred = \App\Models\Warehouse::where('product_id', $record->product_id)
                                ->where('location', 'Glavno skladište') // Ako koristiš `location` kao unique deo
                                ->exists();

                            if ($alreadyTransferred) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Već prebačeno')
                                    ->body("Ovaj proizvod je već prebačen u magacin.")
                                    ->warning()
                                    ->send();
                                return;
                            }

                            \App\Models\Warehouse::create([
                                'product_id' => $record->product_id,
                                'quantity' => $record->quantity,
                                'location' => 'Glavno skladište', // obavezno ako je deo unique ključa
                                'created_by' => auth()->id(),
                                'updated_by' => auth()->id(),
                            ]);

                            $record->updateStatusBasedOnItems();

                            \Filament\Notifications\Notification::make()
                                ->title('Uspešno')
                                ->body("Radni nalog je uspešno prebačen u magacin.")
                                ->success()
                                ->send();
                        }),
                ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('status', 'desc')
            ->searchDebounce(500)
            ->recordUrl(fn (WorkOrder $record) => static::getUrl('edit', ['record' => $record]))
            ->recordAction(null); // spriječava otvaranje modala i koristi URL
    }

    /**
     * Get the relation managers that should be available for the resource.
     *
     * @return array
     */
    public static function getRelations(): array
    {
        return [
            WorkOrderItemsRelationManager::class,
        ];
    }

    /**
     * The pages that should be available for the resource.
     *
     * The pages are: edit, index, create, custom-create, and view.
     *
     * @return array
     */
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
