<?php

namespace App\Filament\Resources;

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
            Forms\Components\TextInput::make('code')->disabled(),
            Forms\Components\Select::make('work_phase_id')
                ->label('Faza')
                ->relationship('workPhase', 'name')
                ->required(),
            Forms\Components\Toggle::make('is_confirmed')
                ->label('Odrađeno')
                ->onColor('success')
                ->offColor('danger'),
            ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // TextColumn::make('workOrder.work_order_number')
                //     ->label('Radni nalog')
                //     ->sortable()
                //     ->searchable()
                //     ->badge(),

                TextColumn::make('code')
                    ->label('Šifra')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('workPhase.name')
                    ->label('Faza')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\IconColumn::make('is_confirmed')
                    ->label('Potvrđeno')
                    ->boolean()
                    ->sortable(),
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
            'index' => Pages\ListWorkOrderItems::route('/'),
            'create' => Pages\CreateWorkOrderItem::route('/create'),
            'edit' => Pages\EditWorkOrderItem::route('/{record}/edit'),
        ];
    }
}
