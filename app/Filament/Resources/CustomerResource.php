<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\{TextInput, Select, DatePicker, Textarea, Grid, Hidden};
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    // Navigacija
    protected static ?string $navigationLabel = 'Kupci';
    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function getNavigationGroup(): ?string
    {
        return 'Administracija';
    }
    
    public static function getNavigationSort(): ?int
    {
        return 0;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('type')
                    ->label('Tip kupca')
                    ->options([
                        'individual' => 'Fizičko lice',
                        'company' => 'Firma',
                    ])
                    ->reactive()
                    ->afterStateUpdated(fn ($state, callable $set) => $set('is_company', $state === 'company')),

                Hidden::make('is_company'),

                Grid::make(2)->schema([
                    TextInput::make('name')->label('Ime / Naziv kupca')->required(),
                    TextInput::make('phone')->label('Telefon')->tel(),
                    TextInput::make('email')->email()->label('Email'),
                    TextInput::make('address')->label('Adresa'),
                    TextInput::make('city')->label('Grad'),
                    Textarea::make('note')->label('Napomena'),

                    // Polja za fizička lica
                    TextInput::make('jmbg')
                        ->label('JMBG')
                        ->visible(fn ($get) => $get('type') === 'individual'),
                    TextInput::make('id_card_number')
                        ->label('Broj lične karte')
                        ->visible(fn ($get) => $get('type') === 'individual'),
                    DatePicker::make('date_of_birth')
                        ->label('Datum rođenja')
                        ->visible(fn ($get) => $get('type') === 'individual'),

                    // Polja za firme
                    TextInput::make('company_name')
                        ->label('Naziv firme')
                        ->visible(fn ($get) => $get('type') === 'company'),
                    TextInput::make('pib')
                        ->label('PIB')
                        ->visible(fn ($get) => $get('type') === 'company'),
                    TextInput::make('contact_person')
                        ->label('Kontakt osoba')
                        ->visible(fn ($get) => $get('type') === 'company'),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Ime / Naziv')->searchable()->sortable(),
                TextColumn::make('type')->label('Tip')->sortable()->badge(),
                TextColumn::make('phone')->label('Telefon'),
                TextColumn::make('email')->label('Email'),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Filter po tipu')
                    ->options([
                        'individual' => 'Fizičko lice',
                        'company' => 'Firma',
                    ]),
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
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
