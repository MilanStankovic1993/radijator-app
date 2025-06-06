<?php

namespace App\Filament\Resources;

use App\Helpers\FilamentColumns;
use Illuminate\Support\Facades\Auth;
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
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

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

    public static function form(Form $form, $record = null): Form
    {
    $operation = $form->getOperation();
    if ($operation === 'create') {
            // forma za create radnog naloga
            return $form->schema([
                Tabs::make('Radni nalog')
                    ->tabs([
                        Tabs\Tab::make('Osnovno')
                            ->schema([
                                TextInput::make('product_code')
                                    ->label('Šifra artikla')
                                    ->regex('/^[0-9a-zA-Z]+$/') // omogućava unos kombinacije slova i brojeva
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(fn ($state, $set, $get) => $set('full_name', $get('work_order_number') . '-' . $get('product_code') . '-' . $get('series') . '-' . $get('quantity'))),

                                Select::make('work_order_number')
                                    ->label('Broj radnog naloga')
                                    ->options([
                                        '021' => 'naručeno',
                                        '020' => 'zalihe',
                                        '022' => 'naručeno i dopunjeno  za zalihe',
                                        '001' => 'prototip kotla/prizvoda',
                                        '002' => 'rekonstrukcija kotla/prizvoda',
                                        '003' => 'remont kotla/prizvoda',
                                        '090' => 'tehnološka proba',
                                        '100' => 'magacinske rezerve',
                                        '110' => 'usluga od našeg materijala',
                                        '111' => 'usluga od  materijalanaručioca',
                                        '112' => 'usluga od našeg materijala i materijala kupca',
                                        '201' => 'pomoćni pribor , alat, naprava za proizvodnju',
                                        '202' => 'održavanje,remont opreme, dodatna oprema...',
                                        '030' => 'rezervni delovi',
                                        '050' => 'Dopunski nalog (nedostajuće pozicije-zahtev Šeovac; Škart po RN',
                                    ])
                                    ->required()
                                    ->live()
                                    ->reactive()
                                    ->afterStateUpdated(fn ($state, $set, $get) => $set('full_name', $get('work_order_number') . '.' . $get('product_code') . '.' . $get('series') . '.' . $get('quantity'))),

                                TextInput::make('series')
                                    ->label('Serija')
                                    ->integer() 
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(fn ($state, $set, $get) => $set('full_name', $get('work_order_number') . '.' . $get('product_code') . '.' . $get('series') . '-' . $get('quantity'))),

                                TextInput::make('quantity')
                                    ->label('Količina')
                                    ->integer() 
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(fn ($state, $set, $get) => $set('full_name', $get('work_order_number') . '.' . $get('product_code') . '.' . $get('series') . '-' . $get('quantity'))),
                                Hidden::make('user_id')
                                    ->default(fn () => auth()->id()),

                                DatePicker::make('launch_date')
                                    ->label('Datum lansiranja')
                                    ->required(),

                                Select::make('product_id')
                                    ->label('Artikal')
                                    ->relationship('product', 'name')
                                    ->preload()
                                    ->required(),
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
            ]);
        }
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('full_name')->label('Radni nalog')->searchable()->sortable()->toggleable(),
                TextColumn::make('user.name')->label('Izdao')->searchable()->sortable()->toggleable(),
                TextColumn::make('product.name')->label('Artikal')->searchable()->sortable()->toggleable(),
                TextColumn::make('launch_date')->label('Datum lansiranja')->date()->sortable(),
                TextColumn::make('confirmed_items_percentage')
                    ->label('Procenat odrađenog')
                    ->getStateUsing(function (WorkOrder $record) {
                        return $record->confirmedItemsPercentage() . '%';
                    })
                    ->sortable()->toggleable(),
                BadgeColumn::make('status')->label('Status')->colors([
                    'aktivan' => 'success',
                    'neaktivan' => 'danger',
                    'zavrsen' => 'warning',
                ]),
                ...FilamentColumns::userTrackingColumns(),
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
            ->searchDebounce(500)
            ->recordUrl(fn (WorkOrder $record) => static::getUrl('edit', ['record' => $record]))
            ->recordAction(null); // spriječava otvaranje modala i koristi URL
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
}
