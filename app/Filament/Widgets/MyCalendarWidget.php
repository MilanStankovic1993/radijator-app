<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\OrderRequestResource;
use App\Filament\Resources\TaskResource;
use App\Filament\Resources\WorkOrderResource;
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
    protected string $calendarView = 'dayGridMonth';
    protected ?string $locale = 'sr';
    protected bool $useFilamentTimezone = true;

    // ponašanje klika
    protected bool $eventClickEnabled  = true;
    protected bool $dateClickEnabled   = false;
    protected bool $dateSelectEnabled  = false;
    protected ?string $defaultEventClickAction = null; // bez slideOver-a

    // boje po tipu
    private const COLOR_TASK       = '#3b82f6'; // plava
    private const COLOR_WORK_ORDER = '#5cf676ff'; // ljubičasta
    private const COLOR_ORDER      = '#10b981'; // zelena

    // filter state
    public bool $showTasks = true;
    public bool $showWorkOrders = true;
    public bool $showOrders = true;

    public function authorize($ability, $arguments = []): bool
    {
        return true;
    }

    # ====== UI: FILTER dugme u headeru ======
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
            'timeZone'          => 'local',
            'displayEventTime'  => false,

            // >>> ključne stavke
            'dayMaxEvents'      => 1,    // maksimalno 1 događaj vidljiv u danu (ostalo ide u "+ još")
            'views' => [
                'dayGridMonth' => [
                    'dayMaxEventRows' => 1,      // i za višednevne trake (RN) takođe max 1 red
                    'moreLinkClick'   => 'popover',
                    'moreLinkText'    => '+ još',
                ],
            ],
            // prioritet prikaza: prvo RN (A_), pa Task (B_), pa Porudžbina (C_)
            'eventOrder'        => ['type', 'start', 'title'],
            'eventOrderStrict'  => true,

            // malo veća mreža
            'height'            => 'auto',
            'expandRows'        => true,
            'aspectRatio'       => 1.05, // manja vrednost = viši kalendar
            // Toolbar + dodatni pogledi
            'headerToolbar' => [
                'start'  => 'prev,next today',
                'center' => 'title',
                'end'    => 'dayGridMonth,dayGridWeek,timeGridWeek,timeGridDay,listWeek',
            ],
            'buttonText' => [
                'today'  => 'danas',
                'month'  => 'mesec',
                'week'   => 'nedelja',
                'day'    => 'dan',
                'list'   => 'lista',
            ],
        ]);
    }

    public function getEvents(array $fetchInfo = []): \Illuminate\Support\Collection|array
    {
        $rangeStart = data_get($fetchInfo, 'startStr');
        $rangeEnd   = data_get($fetchInfo, 'endStr');

        $events = collect();

        // ===== RADNI NALOZI =====
        if ($this->showWorkOrders) {
            $workOrders = WorkOrder::query()
                ->when($rangeStart && $rangeEnd, fn ($q) => $q->whereBetween('launch_date', [$rangeStart, $rangeEnd]))
                ->whereNotNull('launch_date')
                ->with(['product:id,name,code', 'items.workPhase'])
                ->limit(500)
                ->get();

            foreach ($workOrders as $wo) {
                $percent   = $wo->status === 'zavrsen' ? 100 : (int) round((float) ($wo->completion_percentage ?? 0));

                // all-day interval: end je ekskluzivan
                $startDate = Carbon::parse($wo->created_at)->addDay()->toDateString();
                $endDate   = Carbon::parse($wo->launch_date)->addDay()->toDateString();

                $editUrl = $this->resourceUrlSafe(WorkOrderResource::class, 'edit', $wo);

                $events->push(
                    CalendarEvent::make($wo)
                        ->title('🛠 RN ' . ($wo->work_order_number ?? $wo->id) . ' • ' . optional($wo->launch_date)->format('d.m') . ' • ' . $percent . '%')
                        ->start($startDate)
                        ->end($endDate)
                        ->allDay(true)
                        ->url($editUrl)
                        ->backgroundColor(self::COLOR_WORK_ORDER)
                        ->textColor('#ffffff')
                        ->extendedProps([
                            'type'    => 'A_wo',
                            'percent' => $percent,
                        ])
                );
            }
        }

        // ===== PORUDŽBINE =====
        if ($this->showOrders) {
            $orders = OrderRequest::query()
                ->when($rangeStart && $rangeEnd, fn ($q) => $q->whereBetween('created_at', [$rangeStart, $rangeEnd]))
                ->limit(500)
                ->get();

            foreach ($orders as $or) {
                $startDate = Carbon::parse($or->created_at)->addDay()->toDateString();
                $endDate   = Carbon::parse($or->created_at)->addDay()->toDateString();

                $editUrl = $this->resourceUrlSafe(OrderRequestResource::class, 'edit', $or);

                $events->push(
                    CalendarEvent::make($or)
                        ->title('🧾 PO ' . ($or->order_code ?? $or->id) . ' • ' . ($or->customer_name ?? 'Kupac'))
                        ->start($startDate)
                        ->end($endDate)
                        ->allDay(true)
                        ->url($editUrl)
                        ->backgroundColor(self::COLOR_ORDER)
                        ->textColor('#ffffff')
                        ->extendedProps([
                            'type' => 'C_order',   // << prioritet 3
                        ])
                );
            }
        }

        // ===== TASKOVI =====
        if ($this->showTasks) {
            $currentUserId = auth()->id();

            $tasks = Task::query()
                ->when($rangeStart && $rangeEnd, fn ($q) => $q->whereBetween('due_date', [$rangeStart, $rangeEnd]))
                ->whereNotNull('due_date')
                // ⬇️ PRAVI FILTER: vidi samo svoje (kreator) ili dodeljene (pivot)
                ->where(function ($q) use ($currentUserId) {
                    $q->where('creator_id', $currentUserId)
                    ->orWhereHas('users', fn ($uq) => $uq->where('user_id', $currentUserId));
                })
                // učitaj samo moju pivot vezu (radi rutiranja na MyTask)
                ->with(['users' => fn ($q) => $q->where('users.id', $currentUserId)->select('users.id')])
                ->limit(500)
                ->get();

            foreach ($tasks as $task) {
                // all-day jednodnevni: end je ekskluzivan
                $startDate = \Illuminate\Support\Carbon::parse($task->due_date)->addDay()->toDateString();
                $endDate   = \Illuminate\Support\Carbon::parse($task->due_date)->addDay()->toDateString();

                $isCreator     = (int) $task->creator_id === (int) $currentUserId;
                $assignedToMe  = $task->users?->contains('id', $currentUserId) ?? false;

                // Ako mi je dodeljen a nisam kreator → EditMyTask, inače EditTask
                $editUrl = ($assignedToMe && ! $isCreator)
                    ? \App\Filament\Resources\MyTaskResource::getUrl('edit', ['record' => $task])
                    : \App\Filament\Resources\TaskResource::getUrl('edit', ['record' => $task]);

                $events->push(
                    \Guava\Calendar\ValueObjects\CalendarEvent::make($task)
                        ->title('📌 ' . $task->title)
                        ->start($startDate)->end($endDate)->allDay(true)
                        ->url($editUrl)
                        ->backgroundColor(self::COLOR_TASK)->textColor('#ffffff')
                        ->extendedProps([
                            'type' => 'B_task',    // << prioritet 2
                        ])
                );
            }
        }

        return $events->values();
    }

    public function getEventClickContextMenuActions(): array
    {
        return []; // nema context menija
    }

    /** View URL sa fallback-om na edit ako view ne postoji */
    private function resourceUrlSafe(string $resourceClass, string $page, $record, string $fallbackPage = 'edit'): string
    {
        try {
            return $resourceClass::getUrl($page, ['record' => $record]);
        } catch (\Throwable $e) {
            return $resourceClass::getUrl($fallbackPage, ['record' => $record]);
        }
    }

    // (Opciono) Ako više ne koristiš slideOver pregled, možeš obrisati ceo getSchema().
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
                    ->content(fn (?OrderRequest $r) => $r?->customer_name ?? '—'),
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
