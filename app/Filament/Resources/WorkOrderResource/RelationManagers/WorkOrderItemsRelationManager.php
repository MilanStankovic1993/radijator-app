<?php

namespace App\Filament\Resources\WorkOrderResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use App\Helpers\FilamentColumns;

class WorkOrderItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';
    protected static ?string $title = null;
    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return 'Proizvodnja za: ' . ($ownerRecord->full_name ?? ('#' . $ownerRecord->id));
    }
    public function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            // Prikazujemo ostala polja kao tekst (samo za prikaz)
            TextInput::make('id')->label('ID')->disabled(),
            // TextInput::make('work_order_id')->label('Work Order ID')->disabled(),
            TextInput::make('workPhase.name')->label('Faza')->disabled(),
            // TextInput::make('product_id')->label('Product ID')->disabled(),
            TextInput::make('is_confirmed')->label('Potvrđeno')->disabled(),
            TextInput::make('required_to_complete')->label('Potrebno završiti')->disabled(),
            // TextInput::make('status')->label('Status')->disabled(),
            TextInput::make('created_by')->label('Created By')->disabled(),
            TextInput::make('updated_by')->label('Updated By')->disabled(),
            TextInput::make('created_at')->label('Created At')->disabled(),
            TextInput::make('updated_at')->label('Updated At')->disabled(),
            TextInput::make('total_completed')
                ->label('Završeno')
                ->numeric()
                ->required()
                ->minValue(0)
                ->maxValue(fn (callable $get) => $get('required_to_complete') ?? null)
                ->reactive()
                ->afterStateUpdated(function (callable $set, $state, callable $get) {
                    $required = $get('required_to_complete') ?? 0;
                    if ($state == $required && $required > 0) {
                        $set('is_confirmed', true);
                    } else {
                        $set('is_confirmed', false);
                    }
                }),

        ]);
    }
    public function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            // TextColumn::make('id')->label('ID'),
            // TextColumn::make('work_order_id')->label('Work Order ID'),
            TextColumn::make('workPhase.name')->label('Faza'),
            // TextColumn::make('product_id')->label('Product ID'),
            Tables\Columns\BooleanColumn::make('is_confirmed')->label('Potvrđeno'),
            TextColumn::make('required_to_complete')->label('Potrebno završiti'),
            TextColumn::make('remaining_to_complete')
                ->label('Preostalo da se završi')
                ->getStateUsing(fn ($record) => max(0, ($record->required_to_complete - $record->total_completed))),
            TextInputColumn::make('total_completed')
                ->label('Završeno')
            ->afterStateUpdated(function ($state, $record) {
                $record->total_completed = $state;
                $record->is_confirmed = ($state == $record->required_to_complete);
                $record->save();
            }),
            // TextColumn::make('status')->label('Status'),
            ...FilamentColumns::userTrackingColumns(),
        ]);
    }
    protected function canEdit(Model $record): bool
    {
        return true; // Omogućava editovanje
    }
}
