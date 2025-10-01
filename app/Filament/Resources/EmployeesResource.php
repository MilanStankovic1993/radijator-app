<?php

namespace App\Filament\Resources;

use App\Helpers\FilamentColumns;
use Illuminate\Support\Facades\Auth;
use App\Filament\Resources\EmployeesResource\Pages;
use App\Filament\Resources\EmployeesResource\RelationManagers;
use App\Models\Employees;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\{TextInput, Select, DatePicker, Textarea, Grid, Hidden};
use Filament\Tables\Columns\TextColumn;

class EmployeesResource extends Resource
{
    protected static ?string $model = Employees::class;

    // Navigacija
    protected static ?string $navigationLabel = 'Radnici';
    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public static function getNavigationGroup(): ?string
    {
        return 'Administracija';
    }
    
    public static function getNavigationSort(): ?int
    {
        return 2;
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)->schema([
                    TextInput::make('name')->label('Ime')->required(),
                    TextInput::make('surname')->label('Prezime')->required(),
                    TextInput::make('phone')->label('Telefon')->tel(),
                    TextInput::make('email')->email()->label('Email'),
                    TextInput::make('address')->label('Adresa'),
                    TextInput::make('city')->label('Grad'),
                    TextInput::make('jmbg')
                        ->label('JMBG'),
                    TextInput::make('id_card_number')
                        ->label('Broj lične karte'),
                    DatePicker::make('date_of_birth')
                        ->label('Datum rođenja'),
                    Forms\Components\FileUpload::make('import_file')
                    ->label('Uvezi fotografiju')
                    ->directory('uploads')
                    ->rules([
                        'file',
                        'mimes:jpg,jpeg,png'
                    ])
                    ->visibility('public')
                    ->preserveFilenames()
                    ->disk('public')
                    ->maxSize(5120)
                    ->required(false),                        
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Ime')->searchable()
                ->sortable()
                ->toggleable(),
                TextColumn::make('surname')->label('Prezime')->searchable()
                ->sortable()
                ->toggleable(),
                TextColumn::make('phone')->label('Telefon')->searchable()
                ->sortable()
                ->toggleable(),
                TextColumn::make('email')->label('Email')->searchable()
                ->sortable()
                ->toggleable(),
                ...FilamentColumns::userTrackingColumns(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployees::route('/create'),
            'edit' => Pages\EditEmployees::route('/{record}/edit'),
        ];
    }
}
