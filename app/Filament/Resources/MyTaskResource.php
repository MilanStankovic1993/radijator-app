<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MyTaskResource\Pages;
use App\Models\Task;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MyTaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationLabel = 'Moji zadaci';
    protected static ?string $navigationGroup = 'Zadaci';
    protected static ?string $navigationIcon = 'heroicon-o-inbox';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return TaskResource::form($form);
    }

    public static function table(Table $table): Table
    {
        return TaskResource::table($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('users', fn ($q) =>
                $q->where('user_id', auth()->id())
            )
            ->with('users');
    }

    /**
     * Get the relations associated with the resource.
     *
     * @return array The list of relation managers for the resource.
     */

    public static function getRelations(): array
    {
        return [];
    }

    public static function getNavigationBadge(): ?string
    {
        $userId = auth()->id();

        if (!$userId) return null;

        $query = Task::whereHas('users', fn ($q) => $q->where('user_id', $userId));

        $unread = (clone $query)->whereHas('users', fn ($q) =>
            $q->where('user_id', $userId)->where('is_read', false)
        )->count();

        $notDone = (clone $query)->whereHas('users', fn ($q) =>
            $q->where('user_id', $userId)->where('is_done', false)
        )->count();

        return "$unread / $notDone";
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMyTasks::route('/'),
            'create' => Pages\CreateMyTask::route('/create'),
            'edit' => Pages\EditMyTask::route('/{record}/edit'),
        ];
    }
}
