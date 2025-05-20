<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkPhaseResource\Pages;
use App\Filament\Resources\WorkPhaseResource\RelationManagers;
use App\Models\WorkPhase;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Traits\HasResourcePermissions;

class WorkPhaseResource extends Resource
{
    use HasResourcePermissions;

    protected static string $resourceName = 'work_pheses';
    protected static ?string $model = WorkPhase::class;

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
            'index' => Pages\ListWorkPhases::route('/'),
            'create' => Pages\CreateWorkPhase::route('/create'),
            'edit' => Pages\EditWorkPhase::route('/{record}/edit'),
        ];
    }
}
