<?php

namespace App\Filament\Resources;

use App\Helpers\FilamentColumns;
use App\Filament\Resources\OrderRequestResource\Pages;
use App\Filament\Traits\HasResourcePermissions;
use App\Models\Customer;
use App\Models\OrderRequest;
use App\Models\Product;
use Filament\Forms\Form;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Grid;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Actions\StaticAction;
use Filament\Tables\Columns\TextColumn;

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
            TextInput::make('order_code')
                ->label('Šifra narudžbine')
                ->maxLength(50)
                ->required()
                ->unique(ignoreRecord: true),

            Select::make('customer_id')
                ->label('Kupac')
                ->options(Customer::all()->pluck('name', 'id')->toArray())
                ->searchable()
                ->preload()
                ->required()
                ->createOptionForm([
                    TextInput::make('name')->required()->label('Ime kupca'),
                ])
                ->createOptionUsing(fn (array $data) => Customer::create($data)->id),

            Repeater::make('items')
                ->label('Proizvodi')
                ->relationship('items')
                ->schema([
                    Grid::make(12)->schema([
                        Select::make('product_id')
                            ->label('Proizvod')
                            ->options(Product::all()->pluck('name', 'id')->toArray())
                            ->searchable()
                            ->required()
                            ->columnSpan(8), // 8 od 12 => 66.7%

                        TextInput::make('quantity')
                            ->label('Količina')
                            ->numeric()
                            ->minValue(1)
                            ->required()
                            ->columnSpan(4), // 4 od 12 => 33.3%
                    ]),
                ])
                ->grid(3)
                ->columns(3)
                ->defaultItems(1)
                ->minItems(1)
                ->required()
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('items.product'))
            ->recordUrl(fn ($record) => static::getUrl('edit', ['record' => $record]))
            ->columns([
                TextColumn::make('order_code')
                    ->label('Šifra')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer.name')
                    ->label('Kupac')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('items_count')
                    ->label('Broj proizvoda')
                    ->counts('items')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Datum')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),

                ...FilamentColumns::userTrackingColumns(),
            ])
            ->actions([
                Action::make('generisiFakturu')
                    ->icon('heroicon-o-document-text')
                    ->label('Generiši fakturu')
                    ->color('primary')
                    ->action(function (OrderRequest $record) {
                        return response()->streamDownload(function () use ($record) {
                            echo \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.invoice', ['order' => $record])->stream();
                        }, 'faktura-' . $record->order_code . '.pdf');
                    }),
                Action::make('prikaziStavke')
                    ->icon('heroicon-o-list-bullet')
                    ->label('Stavke')
                    ->modalHeading('Stavke porudžbine')
                    ->modalSubmitAction(false)
                    ->modalCancelAction(fn (StaticAction $action) => $action->label('Zatvori'))
                    ->modalContent(fn ($record) => view(
                        'filament.resources.order-request.items-modal',
                        ['order' => $record]
                    )),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
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
