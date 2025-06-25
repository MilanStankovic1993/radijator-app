<?php

namespace App\Filament\Resources;

use App\Models\Task;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\CreatedTaskResource\Pages;
use Filament\Tables\Actions\Action;
use App\Filament\Resources\TaskResource;
use Illuminate\Support\HtmlString;

class CreatedTaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationLabel = 'Kreirani zadaci';
    protected static ?string $navigationGroup = 'Zadaci';
    protected static ?string $navigationIcon = 'heroicon-o-pencil';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return TaskResource::form($form);
    }

    public static function table(Table $table): Table
    {
        return TaskResource::table($table)
            ->columns([
                ...TaskResource::table($table)->getColumns(),
                \Filament\Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'gray' => 'aktivan',
                        'warning' => 'u radu',
                        'success' => 'zavrsen',
                    ])
                    ->sortable(),
            ])
            ->actions([
                Action::make('Pregledaj korisnike')
                    ->icon('heroicon-o-eye')
                    ->label('Pregled')
                    ->modalHeading('Status korisnika za ovaj zadatak')
                    ->modalSubheading(fn (Task $record) => $record->title)
                    ->visible(fn (Task $record) => $record->creator_id === auth()->id())
                    ->modalContent(function (Task $record) {
                        $record->load('users');

                        return new HtmlString(
                            $record->users->map(function ($user) {
                                $read = $user->pivot->is_read ? '📖' : '📕';
                                $done = $user->pivot->is_done ? '✅' : '❌';
                                return "<div class='py-1'><strong>{$user->name}</strong> — $read $done</div>";
                            })->implode('')
                        );
                    }),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('creator_id', auth()->id())
            ->whereHas('users', fn ($q) => $q->where('user_id', '!=', auth()->id()));
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getNavigationBadge(): ?string
    {
        $userId = auth()->id();

        if (!$userId) return null;

        $query = Task::where('creator_id', $userId)
            ->whereHas('users', fn ($q) => $q->where('user_id', '!=', $userId));

        $total = $query->count();
        $done = $query->whereHas('users', fn ($q) => $q->where('is_done', true))->count();

        return "$done / $total";
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCreatedTasks::route('/'),
            'create' => Pages\CreateCreatedTask::route('/create'),
            'edit' => Pages\EditCreatedTask::route('/{record}/edit'),
        ];
    }
}
