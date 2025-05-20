<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\CheckboxList;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Traits\HasResourcePermissions;

class UserResource extends Resource
{
    use HasResourcePermissions;

    protected static string $resourceName = 'users';
    protected static ?string $model = User::class;

    // Navigacija
    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static ?string $navigationLabel = 'Korisnici';
    protected static ?string $navigationGroup = 'Administracija';

    // Eager load relacija roles da bi tabela radila sa njima
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('roles', 'permissions');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Korisnik')
                    ->tabs([
                        Tabs\Tab::make('Detalji')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Ime')
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('email')
                                    ->label('Email')
                                    ->email()
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true)
                                    ->validationMessages([
                                        'unique' => 'Korisnik sa ovom mejl adresom već postoji.',
                                    ]),

                                Forms\Components\TextInput::make('password')
                                    ->label('Lozinka')
                                    ->password()
                                    ->maxLength(255)
                                    ->dehydrateStateUsing(fn ($state) => $state ? Hash::make($state) : null)
                                    ->required(fn (string $context) => $context === 'create')
                                    ->dehydrated(fn ($state) => filled($state)),

                                Forms\Components\TextInput::make('password_confirmation')
                                    ->label('Potvrdi lozinku')
                                    ->password()
                                    ->maxLength(255)
                                    ->required(fn (string $context) => $context === 'create')
                                    ->dehydrated(false) // Ne čuvamo u bazi
                                    ->same('password')  // Validacija da bude isto kao password
                                    ->visible(fn (string $context) => $context === 'create' || $context === 'edit')
                                    ->validationMessages([
                                        'visible' => 'potrebno je da se poklapa sa poljem "lozinka"',
                                    ]),

                                Forms\Components\MultiSelect::make('roles')
                                    ->label('Uloge')
                                    ->relationship('roles', 'name')
                                    ->preload()
                                    ->required(),
                            ]),

                        Tabs\Tab::make('Dozvole')
                            ->visible(fn () => auth()->user()?->hasRole('admin')) // vidi samo admin
                            ->schema(static::getGroupedPermissions()),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Ime')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('roles')
                    ->label('Uloge')
                    ->formatStateUsing(fn ($state, $record) => $record->roles->pluck('name')->join(', ') ?: '-'),
                    // ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Kreiran')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('Uloga')
                    ->relationship('roles', 'name'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
    protected static function getGroupedPermissions(): array
    {
        $permissions = Permission::all();

        $grouped = $permissions->groupBy(function ($permission) {
            return explode(' ', $permission->name)[0]; // grupiši po akciji (view, edit, create, delete)
        });

        $components = [];

        foreach ($grouped as $action => $perms) {
        $components[] = CheckboxList::make('permissions_group_' . $action)
            ->label(ucfirst($action))
            ->options($perms->pluck('name', 'id'))
            ->columns(2)
            ->dehydrated(false) // sprečava da Laravel očekuje automatsku vezu sa modelom
            // ->afterStateHydrated(function (CheckboxList $component, $state) use ($action) {
            //     $record = $component->getRecord();

            //     if ($record instanceof \App\Models\User) {
            //         $groupPermissionIds = Permission::where('name', 'like', $action . ' %')->pluck('id')->toArray();
            //         $userPermissionIds = $record->permissions->pluck('id')->toArray();
            //         $selected = array_intersect($groupPermissionIds, $userPermissionIds);

            //         // Postavi početno stanje
            //         $component->state($selected);
            //     }
            // })
            // ->afterStateUpdated(function ($state, callable $set, CheckboxList $component) use ($action) {
            //     $record = $component->getRecord();

            //     if ($record instanceof \App\Models\User) {
            //         $permissionNamesInGroup = Permission::where('name', 'like', $action . ' %')->pluck('name')->toArray();
            //         $selectedPermissionNames = Permission::whereIn('id', $state)->pluck('name')->toArray();

            //         foreach ($permissionNamesInGroup as $permName) {
            //             $record->revokePermissionTo($permName);
            //         }

            //         foreach ($selectedPermissionNames as $permName) {
            //             $record->givePermissionTo($permName);
            //         }
            //     }
            // })
            ;
        }

        return $components;
    }

}
