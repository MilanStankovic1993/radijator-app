<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkOrderResource\Pages;
use App\Filament\Resources\WorkOrderResource\RelationManagers;
use App\Models\WorkOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Traits\HasResourcePermissions;


class WorkOrderResource extends Resource
{
    // use HasResourcePermissions;

    protected static string $resourceName = 'work_orders';
    protected static ?string $model = WorkOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Radni nalozi';
    protected static ?int $navigationSort = 2; // Redosled u meniju

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('work_order_number')
                    ->label('Broj radnog naloga')
                    ->required(),

                // Hidden user_id, popunjava se automatski
                Hidden::make('user_id')
                    ->default(auth()->id()),

                DatePicker::make('launch_date')
                    ->label('Datum lansiranja')
                    ->required(),

                Select::make('product_id')
                    ->label('Artikal')
                    ->relationship('product', 'name') // ako model ima 'name' kolonu
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
                    ->required(),
            ]);
    }
 
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('work_order_number')
                    ->label('Broj')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->searchable()
                    ->sortable()
                    ->label('Izdao'),
                TextColumn::make('product.name')
                    ->searchable()
                    ->sortable()
                    ->label('Artikal'),
                TextColumn::make('launch_date')
                    ->date()
                    ->label('Datum lansiranja'),
                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'aktivan',
                        'danger' => 'neaktivan',
                        'warning' => 'zavrsen',
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
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('status', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWorkOrders::route('/'),
            'create' => Pages\CreateWorkOrder::route('/create'),
            'edit' => Pages\EditWorkOrder::route('/{record}/edit'),
        ];
    }
}
