<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PhaseTrackingResource\Pages;
use App\Filament\Resources\PhaseTrackingResource\RelationManagers;
use App\Models\PhaseTracking;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Traits\HasResourcePermissions;

class PhaseTrackingResource extends Resource
{
    use HasResourcePermissions;

    protected static string $resourceName = 'phase_trackings';
    protected static ?string $model = PhaseTracking::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListPhaseTrackings::route('/'),
            'create' => Pages\CreatePhaseTracking::route('/create'),
            'edit' => Pages\EditPhaseTracking::route('/{record}/edit'),
        ];
    }
}
