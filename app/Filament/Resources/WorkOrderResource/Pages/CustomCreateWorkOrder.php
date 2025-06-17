<?php

namespace App\Filament\Resources\WorkOrderResource\Pages;

use App\Filament\Resources\WorkOrderResource;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Resources\Pages\CreateRecord;
use Filament\Forms\Form;

class CustomCreateWorkOrder extends CreateRecord
{
    protected static string $resource = WorkOrderResource::class;

    protected static bool $shouldUseResourceForm = false;

    /**
     * Defines the form schema for the CreateRecord page.
     *
     * @param \Filament\Forms\Form $form
     *
     * @return \Filament\Forms\Form
     */
    public function form(Form $form): Form
    {
        return $form->schema($this->getFormSchema());
    }

    /**
     * Returns the form schema for the CreateRecord page.
     *
     * @return array
     */
    protected function getFormSchema(): array
    {
        return [
            Tabs::make('Custom Radni nalog')
                ->tabs([
                    Tabs\Tab::make('Osnovno')->schema([
                        TextInput::make('full_name')
                            ->label('Naziv radnog naloga')
                            ->required(),

                        TextInput::make('quantity')
                            ->label('Količina')
                            ->numeric()
                            ->required(),

                        DatePicker::make('launch_date')
                            ->label('Datum lansiranja')
                            ->required(),

                        Hidden::make('user_id')
                            ->default(fn () => auth()->id()),

                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'aktivan' => 'Aktivan',
                                'zavrsen' => 'Završen',
                                'neaktivan' => 'Neaktivan',
                            ])
                            ->default('aktivan')
                            ->disabled(),
                    ]),
                ]),
        ];
    }

    /**
     * Mutate the data before it is saved to the database.
     *
     * This method is called before the data is saved to the database.
     *
     * @param array $data The data to be saved.
     *
     * @return array The mutated data.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['type'] = 'custom';
        $data['user_id'] = auth()->id();
        return $data;
    }

    /**
     * Get the URL to redirect to after creation.
     *
     * @return string
     */
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
