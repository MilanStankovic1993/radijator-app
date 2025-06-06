<?php

namespace App\Filament\Resources;

use App\Helpers\FilamentColumns;
use Illuminate\Support\Facades\Auth;
use App\Filament\Resources\WorkOrderItemResource\Pages;
use App\Filament\Resources\WorkOrderItemResource\RelationManagers;
use App\Models\WorkOrderItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters\SelectFilter;


class WorkOrderItemResource extends Resource
{
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $model = WorkOrderItem::class;

    public static function getModelLabel(): string
    {
        return 'Proizvodnja';
    }
    protected static ?string $navigationLabel = 'Proizvodnja';
    public static function getNavigationGroup(): ?string
    {
        return 'Proizvodnja';
    }
    

    public static function getNavigationSort(): ?int
    {
        return 1;
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-cog-6-tooth';
    }

    public static function form(Form $form): Form
    {
    return $form
        ->schema([
            Forms\Components\TextInput::make('code')
                ->disabled(),

            Forms\Components\Select::make('work_order_id')
                ->label('Radni nalog')
                ->relationship('workOrder', 'work_order_number')
                ->required(),

            Forms\Components\Select::make('product_id')
                ->label('Proizvod')
                ->relationship('product', 'name')
                ->required(),

            Forms\Components\Select::make('work_phase_id')
                ->label('Faza')
                ->relationship('workPhase', 'name')
                ->required(),

            Forms\Components\TextInput::make('required_to_complete')
                ->label('Potrebno da se odradi')
                ->numeric()
                ->minValue(0)
                ->suffix('min'),

            Forms\Components\TextInput::make('total_completed')
                ->label('Ukupno odrađeno')
                ->numeric()
                ->minValue(0)
                ->suffix('min'),

            Forms\Components\Select::make('status')
                ->label('Status')
                ->options([
                    'pending' => 'Na čekanju',
                    'in_progress' => 'U toku',
                    'completed' => 'Završeno',
                ])
                ->required(),
        ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // TextColumn::make('code')
                //     ->label('Šifra')
                //     ->searchable()
                //     ->sortable()
                //     ->toggleable(),

                TextColumn::make('workPhase.name')
                    ->label('Faza')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_confirmed')
                    ->label('Potvrđeno')
                    ->boolean()
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                ...FilamentColumns::userTrackingColumns(),
            ])
            ->filters([
                Tables\Filters\Filter::make('is_confirmed')
                    ->label('Potvrđeno')
                    ->query(fn (Builder $query) => $query->where('is_confirmed', true)),

                Tables\Filters\Filter::make('not_confirmed')
                    ->label('Nije potvrđeno')
                    ->query(fn (Builder $query) => $query->where('is_confirmed', false)),

                Tables\Filters\SelectFilter::make('work_order_id')
                    ->label('Proizvod')
                    ->options(
                        \App\Models\WorkOrder::query()
                            ->pluck('work_order_number', 'id')
                    ),
                    
                Tables\Filters\SelectFilter::make('work_phase_id')
                    ->label('Radna faza')
                    ->options(
                        \App\Models\WorkPhase::query()
                            ->pluck('name', 'id')
                    ),
            ])
            ->defaultGroup(
                \Filament\Tables\Grouping\Group::make('workOrder.work_order_number')
                    ->label('Radni nalog')
            )
            ->defaultSort('work_order_id');
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
            'edit' => Pages\EditWorkOrderItem::route('/{record}/edit'),
            'index' => Pages\ListWorkOrderItems::route('/'),
            'create' => Pages\CreateWorkOrderItem::route('/create'),
        ];
    }
}
