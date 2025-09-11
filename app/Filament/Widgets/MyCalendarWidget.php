<?php

namespace App\Filament\Widgets;

use App\Models\OrderRequest;
use App\Models\Task;
use App\Models\WorkOrder;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Form;
use Guava\Calendar\ValueObjects\CalendarEvent;
use Guava\Calendar\Widgets\CalendarWidget;
use Illuminate\Support\Carbon;

class MyCalendarWidget extends CalendarWidget
{
    protected string $calendarView = 'timeGridWeek';
    protected ?string $locale = 'sr';
    protected bool $useFilamentTimezone = true;

    protected bool $eventClickEnabled  = true;
    protected bool $dateClickEnabled   = false;
    protected bool $dateSelectEnabled  = false;
    protected ?string $defaultEventClickAction = 'view';

    private const COLOR_TASK       = '#3b82f6';
    private const COLOR_WORK_ORDER = '#8b5cf6';
    private const COLOR_ORDER      = '#10b981';

    public bool $showTasks = true;
    public bool $showWorkOrders = true;
    public bool $showOrders = true;

    // ⬇️ dodato: klasa na root <div> widgeta – koristimo je u CSS-u
    protected function getExtraAttributes(): array
    {
        return ['class' => 'fim-cal'];
    }

    public function authorize($ability, $arguments = []): bool
    {
        return true;
    }

    public function getHeaderActions(): array
    {
        return [
            Action::make('filter')
                ->label('Filter')
                ->icon('heroicon-m-funnel')
                ->slideOver()
                ->form([
                    CheckboxList::make('types')
                        ->label('Prikaži na kalendaru')
                        ->options([
                            'work_order' => 'Radni nalozi',
                            'task'       => 'Zadaci',
                            'order'      => 'Porudžbine',
                        ])
                        ->default(fn () => $this->getCheckedTypes())
                        ->columns(1),
                ])
                ->mountUsing(function (Form $form) {
                    $form->fill(['types' => $this->getCheckedTypes()]);
                })
                ->action(function (array $data) {
                    $types = $data['types'] ?? [];
                    $this->showWorkOrders = in_array('work_order', $types, true);
                    $this->showTasks      = in_array('task', $types, true);
                    $this->showOrders     = in_array('order', $types, true);
                    $this->refreshRecords();
                }),
        ];
    }

    private function getCheckedTypes(): array
    {
        $out = [];
        if ($this->showWorkOrders) $out[] = 'work_order';
        if ($this->showTasks)      $out[] = 'task';
        if ($this->showOrders)     $out[] = 'order';
        return $out;
    }

    public function getOptions(): array
    {
        return array_replace_recursive(parent::getOptions(), [
            'timeZone' => 'local',
            'displayEventTime' => false,
            'headerToolbar' => [
                'start'  => 'prev,next today',
                'center' => 'title',
                'end'    => 'dayGridMonth,timeGridWeek,timeGridDay,listWeek',
            ],
            'views' => [
                'dayGridMonth' => [
                    'dayMaxEvents'  => 2,
                    'moreLinkClick' => 'popover',
                    'moreLinkText'  => '+ još',
                ],
                'timeGridWeek' => [
                    'slotDuration' => '00:30',
                    'nowIndicator' => true,
                    'allDaySlot'   => true,
                ],
                'listWeek' => [
                    'noEventsContent' => 'Nema događaja',
                ],
            ],
        ]);
    }

    public function getEvents(array $fetchInfo = []): \Illuminate\Support\Collection|array
    {
        $rangeStart = data_get($fetchInfo, 'startStr');
        $rangeEnd   = data_get($fetchInfo, 'endStr');

        $events = collect();

        if ($this->showWorkOrders) {
            $workOrders = WorkOrder::query()
                ->when($rangeStart && $rangeEnd, fn ($q) => $q->whereBetween('launch_date', [$rangeStart, $rangeEnd]))
                ->whereNotNull('launch_date')
                ->with(['product:id,name,code', 'items.workPhase'])
                ->limit(500)
                ->get();

            foreach ($workOrders as $wo) {
                $percent   = $wo->status === 'zavrsen' ? 100 : (int) round((float) ($wo->completion_percentage ?? 0));
                $startDate = Carbon::parse($wo->created_at)->addDay()->toDateString();
                $endDate   = Carbon::parse($wo->launch_date)->addDay()->toDateString();

                $events->push(
                    CalendarEvent::make($wo)
                        ->title('🛠 RN ' . ($wo->full_name ?? $wo->id) . ' • ' . $wo->launch_date?->format('d.m') . ' • ' . $percent . '%')
                        ->start($startDate)
                        ->end($endDate)
                        ->allDay(true)
                        ->backgroundColor(self::COLOR_WORK_ORDER)
                        ->textColor('#ffffff')
                        ->extendedProps(['type' => 'work_order', 'percent' => $percent])
                );
            }
        }

        if ($this->showOrders) {
            $orders = OrderRequest::query()
                ->when($rangeStart && $rangeEnd, fn ($q) => $q->whereBetween('created_at', [$rangeStart, $rangeEnd]))
                ->with('customer:id,name')
                ->limit(500)
                ->get();

            foreach ($orders as $or) {
                $startDate = Carbon::parse($or->created_at)->addDay()->toDateString();
                $endDate   = Carbon::parse($or->created_at)->addDay()->toDateString();

                $events->push(
                    CalendarEvent::make($or)
                        ->title('🧾 PO ' . ($or->order_code ?? $or->id))
                        ->start($startDate)
                        ->end($endDate)
                        ->allDay(true)
                        ->backgroundColor(self::COLOR_ORDER)
                        ->textColor('#ffffff')
                        ->extendedProps(['type' => 'order_request'])
                );
            }
        }

        if ($this->showTasks) {
            $tasks = Task::query()
                ->when($rangeStart && $rangeEnd, fn ($q) => $q->whereBetween('due_date', [$rangeStart, $rangeEnd]))
                ->whereNotNull('due_date')
                ->limit(500)
                ->get();

            foreach ($tasks as $task) {
                $date = Carbon::parse($task->due_date)->addDay()->toDateString();

                $events->push(
                    CalendarEvent::make($task)
                        ->title('📌 ' . $task->title)
                        ->start($date)
                        ->end($date)
                        ->allDay(true)
                        ->backgroundColor(self::COLOR_TASK)
                        ->textColor('#ffffff')
                        ->extendedProps(['type' => 'task'])
                );
            }
        }

        return $events->values();
    }

    public function getEventClickContextMenuActions(): array
    {
        return [
            // $this->viewAction()->label('Pregled')->slideOver(),
        ];
    }

    public function getSchema(?string $model = null): ?array
    {
        return match ($model) {
            WorkOrder::class => [
                Placeholder::make('rn_broj')->label('RN broj')
                    ->content(fn (?WorkOrder $r) => $r?->work_order_number ?? '—'),
                Placeholder::make('naziv')->label('Naziv / Full name')
                    ->content(fn (?WorkOrder $r) => $r?->full_name ?? '—'),
                Placeholder::make('proizvod')->label('Proizvod')
                    ->content(fn (?WorkOrder $r) => $r?->product?->name ?? '—'),
                Placeholder::make('kolicina')->label('Količina')
                    ->content(fn (?WorkOrder $r) => (string)($r?->quantity ?? '—')),
                Placeholder::make('status')->label('Status')
                    ->content(fn (?WorkOrder $r) => $r?->status ?? '—'),
                Placeholder::make('launch')->label('Datum lansiranja')
                    ->content(fn (?WorkOrder $r) => $r?->launch_date ? Carbon::parse($r->launch_date)->format('d.m.Y') : '—'),
                Placeholder::make('procenat')->label('Procenat proizvedeno')
                    ->content(fn (?WorkOrder $r) => ($r?->status === 'zavrsen' ? 100 : (int) round((float) ($r?->completion_percentage ?? 0))) . ' %'),
            ],

            OrderRequest::class => [
                Placeholder::make('broj')->label('Broj porudžbine')
                    ->content(fn (?OrderRequest $r) => $r?->order_code ?? (string)($r?->id ?? '—')),
                Placeholder::make('kupac')->label('Kupac')
                    ->content(fn (?OrderRequest $r) => $r?->customer?->name ?? '—'),
                Placeholder::make('status')->label('Status')
                    ->content(fn (?OrderRequest $r) => $r?->status ?? '—'),
                Placeholder::make('kreirano')->label('Kreirano')
                    ->content(fn (?OrderRequest $r) => $r?->created_at ? Carbon::parse($r->created_at)->format('d.m.Y') : '—'),
            ],

            Task::class => [
                Placeholder::make('naslov')->label('Naslov')
                    ->content(fn (?Task $r) => $r?->title ?? '—'),
                Placeholder::make('opis')->label('Opis')
                    ->content(fn (?Task $r) => $r?->description ?? '—'),
                Placeholder::make('rok')->label('Rok')
                    ->content(fn (?Task $r) => $r?->due_date ? Carbon::parse($r->due_date)->format('d.m.Y') : '—'),
            ],

            default => [ Placeholder::make('info')->content('Nema šeme za ovaj tip zapisa.') ],
        };
    }
}
