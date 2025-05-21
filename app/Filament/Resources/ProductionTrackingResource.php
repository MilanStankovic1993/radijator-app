<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductionTrackingResource\Pages;
use App\Filament\Resources\ProductionTrackingResource\RelationManagers;
use App\Models\ProductionTracking;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductionTrackingResource extends Resource
{
    protected static ?string $model = ProductionTracking::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->disabled(),

                CheckboxList::make('work_phases')
                    ->label('Radne faze')
                    ->relationship('workPhases', 'name')
                    ->getOptionLabelUsing(fn($phase) => $phase->name)
                    ->columns(1)
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, $get) {
                        // logika može da se doda ako je potrebno odmah reagovati
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Artikal'),

                IconColumn::make('has_work_order')
                    ->label('Radni nalog')
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record->workOrders()->exists())
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                ProgressColumn::make('completion_percent')
                    ->label('Završenost faza')
                    ->getStateUsing(function ($record) {
                        $total = $record->workPhases()->count();
                        if ($total === 0) {
                            return 0;
                        }
                        $completed = $record->workPhases()->where('is_completed', true)->count();
                        return intval(($completed / $total) * 100);
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('Praćenje')
                    ->url(fn ($record) => ProductTrackingResource::getUrl('edit', ['record' => $record->id])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListProductionTrackings::route('/'),
            'create' => Pages\CreateProductionTracking::route('/create'),
            'edit' => Pages\EditProductionTracking::route('/{record}/edit'),
        ];
    }
}
