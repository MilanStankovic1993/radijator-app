<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkPhaseResource\Pages;
use App\Models\WorkPhase;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;

class WorkPhaseResource extends Resource
{
    protected static ?string $model = WorkPhase::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-8-tooth';
    protected static ?string $navigationLabel = 'Radne faze';

    public static function getNavigationGroup(): ?string
    {
        return 'Proizvodnja';
    }

    public static function getNavigationSort(): ?int
    {
        return 1;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')
                ->required()
                ->label('Naziv radne faze'),

            Select::make('location')
                ->required()
                ->options([
                    'Grdica' => 'Grdica',
                    'Seovac' => 'Seovac',
                ])
                ->label('Lokacija'),

            Forms\Components\Textarea::make('description')
                ->label('Opis')
                ->rows(4),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Naziv')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('location')
                    ->label('Lokacija')
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Opis')
                    ->limit(50),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('location')
                    ->label('Lokacija')
                    ->options([
                        'Grdica' => 'Grdica',
                        'Seovac' => 'Seovac',
                    ]),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWorkPhases::route('/'),
            'create' => Pages\CreateWorkPhase::route('/create'),
            'edit' => Pages\EditWorkPhase::route('/{record}/edit'),
        ];
    }
}
