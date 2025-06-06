<?php

namespace App\Filament\Resources;

use App\Helpers\FilamentColumns;
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
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
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

    /**
     * Defines the form schema of the resource.
     *
     * @param  \Filament\Forms\Form  $form
     * @return \Filament\Forms\Form
     */
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

            TextInput::make('time_norm')
                ->label('Vremenska norma (min)')
                ->numeric()
                ->minValue(1)
                ->suffix('min')
                ->required(),

            Forms\Components\Textarea::make('description')
                ->label('Opis')
                ->rows(4),

            TextInput::make('number_of_workers')
                ->label('Broj radnika')
                ->numeric()
                ->minValue(1)
                ->required(),
        ]);
    }

    /**
     * Configures the table for the WorkPhaseResource.
     *
     * Defines the columns, filters, actions, and bulk actions
     * for the table display. It includes columns for the work
     * phase name, location, time norm, and description, along
     * with user tracking columns. Filters allow selecting by
     * location, and actions include editing and bulk deleting.
     *
     * @param  \Filament\Tables\Table  $table
     * @return \Filament\Tables\Table
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Naziv')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('location')
                    ->label('Lokacija')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('time_norm')
                    ->label('Vremenska norma')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->suffix(' min'),
                Tables\Columns\TextColumn::make('description')
                    ->label('Opis')
                    ->limit(50),
                Tables\Columns\TextColumn::make('number_of_workers')
                    ->label('Broj radnika')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                ...FilamentColumns::userTrackingColumns(),
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
