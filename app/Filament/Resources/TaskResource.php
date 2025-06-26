<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaskResource\Pages;
use App\Models\Task;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Illuminate\Database\Eloquent\Builder;
use Filament\Facades\Filament;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Forms\Components\Checkbox;
use App\Filament\Resources\TaskResource\RelationManagers;

class TaskResource extends Resource
{
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $model = Task::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        $isMyTaskContext = request()->routeIs('filament.' . Filament::getCurrentPanel()?->getId() . '.resources.my-tasks.*');

        return $form->schema([
            TextInput::make('title')
                ->label('Naslov zadatka')
                ->required()
                ->maxLength(255),

            Textarea::make('description')
                ->label('Opis zadatka')
                ->rows(4),

            Select::make('users')
                ->label('Dodaj korisnike')
                ->multiple()
                ->relationship('users', 'name')
                ->preload()
                ->required()
                ->options(function () {
                    return \App\Models\User::query()
                        ->where('id', '!=', auth()->id())
                        ->pluck('name', 'id');
                })
                ->default(fn () => $isMyTaskContext ? [auth()->id()] : null)
                ->disabled($isMyTaskContext),

            Checkbox::make('pivot_is_done')
                ->label('Označi kao odrađen')
                ->visible(function (?Task $record) {
                    return $record?->users->contains(auth()->id());
                })
                ->default(function (?Task $record) {
                    return $record?->users->firstWhere('id', auth()->id())?->pivot->is_done;
                })
                ->afterStateUpdated(function ($state, $set, $get, $record) {
                    if ($record) {
                        $record->users()->updateExistingPivot(auth()->id(), [
                            'is_done' => $state,
                        ]);
                    }
                }),

            DatePicker::make('due_date')
                ->label('Rok za završetak')
                ->minDate(now())
                ->nullable(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Naslov')
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(function ($state, $record) {
                        $pivot = $record->users()->where('user_id', auth()->id())->first()?->pivot;
                        if ($pivot && !$pivot->is_read) {
                            return "**_$state**"; // bold + italic
                        }
                        return $state;
                    })
                    ->markdown(),

                // TextColumn::make('description')->label('Opis')->limit(50),

                ToggleColumn::make('pivot.is_done')
                    ->label('Odradio?')
                    ->visible(fn ($record) => $record && $record->users && $record->users->contains(auth()->id()))
                    ->afterStateUpdated(function ($state, $record) {
                        $record->users()->updateExistingPivot(auth()->id(), ['is_done' => $state]);
                        $record->updateStatus();
                    }),

                TextColumn::make('status')
                    ->label('Status')
                    ->getStateUsing(function ($record) {
                        $user = $record->users->firstWhere('id', auth()->id());
                        if (!$user) return '-';
                        $done = $user->pivot->is_done ? '✅' : '❌';
                        $read = $user->pivot->is_read ? '📖' : '📕';
                        return "$read $done";
                    }),

                TextColumn::make('users')
                    ->label('Korisnici')
                    ->visible(fn ($record) => $record && $record->creator_id === auth()->id())
                    ->formatStateUsing(function ($state, $record) {
                        return $record && $record->users
                            ? $record->users->map(function ($user) {
                                $done = $user->pivot->is_done ? '✅' : '❌';
                                $read = $user->pivot->is_read ? '📖' : '📕';
                                return $user->name . " $read $done";
                            })->implode(', ')
                            : '';
                    })
                    ->wrap(),

                BadgeColumn::make('due_date')
                    ->label('Rok')
                    ->colors([
                        'danger' => fn ($state) => $state && \Carbon\Carbon::parse($state)->isPast(),
                        'warning' => fn ($state) => $state && \Carbon\Carbon::parse($state)->isToday(),
                        'success' => fn ($state) => $state && \Carbon\Carbon::parse($state)->isFuture(),
                    ])
                    ->date('d.m.Y'),

                TextColumn::make('creator.name')->label('Kreator'),
                TextColumn::make('updated_at')->label('Poslednja izmena')->since(),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->filters([
                TernaryFilter::make('pivot.is_done')
                    ->label('Završeno')
                    ->queries(
                        true: fn ($query) => $query->whereHas('users', fn ($q) =>
                            $q->where('user_id', auth()->id())->where('is_done', true)
                        ),
                        false: fn ($query) => $query->whereHas('users', fn ($q) =>
                            $q->where('user_id', auth()->id())->where('is_done', false)
                        ),
                    ),

                TernaryFilter::make('pivot.is_read')
                    ->label('Pročitano')
                    ->queries(
                        true: fn ($query) => $query->whereHas('users', fn ($q) =>
                            $q->where('user_id', auth()->id())->where('is_read', true)
                        ),
                        false: fn ($query) => $query->whereHas('users', fn ($q) =>
                            $q->where('user_id', auth()->id())->where('is_read', false)
                        ),
                    ),
            ])
            ->modifyQueryUsing(fn ($query) =>
                $query->with(['users' => fn ($q) => $q->where('user_id', auth()->id())])
            );
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\UsersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTasks::route('/'),
            'create' => Pages\CreateTask::route('/create'),
            'edit' => Pages\EditTask::route('/{record}/edit'),
        ];
    }
}
