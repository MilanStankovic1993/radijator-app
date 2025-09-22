<?php

namespace App\Filament\Widgets;

use App\Models\OrderRequest;
use App\Models\Task;
use App\Models\WorkOrder;
use App\Models\Reminder;

use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Form;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;

use Guava\Calendar\ValueObjects\CalendarEvent;
use Guava\Calendar\Widgets\CalendarWidget;

use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

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
    private const COLOR_REMINDER   = '#f59e0b'; // amber

    public bool $showTasks = true;
    public bool $showWorkOrders = true;
    public bool $showOrders = true;
    public bool $showReminders = true;

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
        $noEnter = ['x-on:keydown.enter.stop' => '', 'x-on:keydown.enter.prevent' => ''];

        return [
            // ➕ Novi podsetnik
            Action::make('newReminder')
                ->label('Novi podsetnik')
                ->icon('heroicon-m-plus')
                ->slideOver()
                ->modalWidth('2xl')
                ->modalHeading('Kreiranje podsetnika')
                ->form([
                    TextInput::make('title')
                        ->label('Naslov')
                        ->required()
                        ->maxLength(150)
                        ->extraAttributes($noEnter),

                    Textarea::make('notes')
                        ->label('Beleška')
                        ->rows(3)
                        ->extraAttributes($noEnter),

                    Toggle::make('all_day')
                        ->label('Celodnevni')
                        ->reactive(),

                    // ⏰ Vreme (kada NIJE celodnevni)
                    DateTimePicker::make('starts_at')
                        ->label('Početak')
                        ->seconds(false)
                        ->native(false)
                        ->closeOnDateSelection(true)
                        ->required(fn (Get $get) => ! (bool) $get('all_day'))
                        ->visible(fn (Get $get) => ! (bool) $get('all_day'))
                        ->extraAttributes($noEnter),

                    DateTimePicker::make('ends_at')
                        ->label('Kraj')
                        ->seconds(false)
                        ->native(false)
                        ->closeOnDateSelection(true)
                        ->rule(function (Get $get) {
                            return Rule::when(
                                filled($get('ends_at')) && filled($get('starts_at')),
                                fn () => 'after_or_equal:starts_at'
                            );
                        })
                        ->visible(fn (Get $get) => ! (bool) $get('all_day'))
                        ->extraAttributes($noEnter),

                    // 📅 Datum (kada JESTE celodnevni)
                    DatePicker::make('start_date')
                        ->label('Datum početka')
                        ->native(false)
                        ->closeOnDateSelection(true)
                        ->required(fn (Get $get) => (bool) $get('all_day'))
                        ->visible(fn (Get $get) => (bool) $get('all_day'))
                        ->extraAttributes($noEnter),

                    DatePicker::make('end_date')
                        ->label('Datum kraja')
                        ->native(false)
                        ->closeOnDateSelection(true)
                        ->rule(function (Get $get) {
                            return Rule::when(
                                filled($get('end_date')) && filled($get('start_date')),
                                fn () => 'after_or_equal:start_date'
                            );
                        })
                        ->visible(fn (Get $get) => (bool) $get('all_day'))
                        ->extraAttributes($noEnter),

                    // 📧 Slanje mejla
                    Toggle::make('send_email')
                        ->label('Pošalji mejl u određeno vreme')
                        ->reactive(),

                    TextInput::make('email_to')
                        ->label('Email primaoca')
                        ->email()
                        ->required(fn (Get $get) => (bool) $get('send_email'))
                        ->visible(fn (Get $get) => (bool) $get('send_email'))
                        ->extraAttributes($noEnter),

                    DateTimePicker::make('email_at')
                        ->label('Vreme slanja mejla')
                        ->seconds(false)
                        ->native(false)
                        ->closeOnDateSelection(true)
                        ->required(fn (Get $get) => (bool) $get('send_email'))
                        ->visible(fn (Get $get) => (bool) $get('send_email'))
                        ->extraAttributes($noEnter),

                    // ⏲️ Pre-reminder X min ranije
                    Toggle::make('pre_email_enabled')
                        ->label('Pošalji i X min ranije')
                        ->default(true)
                        ->reactive(),

                    TextInput::make('pre_email_offset_minutes')
                        ->label('Minuta ranije')
                        ->numeric()
                        ->minValue(1)
                        ->default(15)
                        ->visible(fn (Get $get) => (bool) $get('pre_email_enabled'))
                        ->extraAttributes($noEnter),
                ])
                ->action(function (array $data) {
                    $user = auth()->user();

                    if (!empty($data['all_day'])) {
                        // datumi → 00:00 do 23:59:59 u app TZ, pa u UTC
                        $startLocal = Carbon::parse($data['start_date'], config('app.timezone'))->startOfDay();
                        $endLocal = filled($data['end_date'] ?? null)
                            ? Carbon::parse($data['end_date'], config('app.timezone'))->endOfDay()
                            : $startLocal->clone()->endOfDay();

                        $starts = $startLocal->utc();
                        $ends   = $endLocal->utc();
                    } else {
                        // datum+vreme u app TZ → UTC
                        $starts = Carbon::parse($data['starts_at'], config('app.timezone'))->utc();
                        $ends   = filled($data['ends_at'] ?? null)
                            ? Carbon::parse($data['ends_at'], config('app.timezone'))->utc()
                            : null;
                    }

                    $emailAt = (isset($data['send_email']) && $data['send_email'] && filled($data['email_at'] ?? null))
                        ? Carbon::parse($data['email_at'], config('app.timezone'))->utc()
                        : null;

                    Reminder::create([
                        'user_id'                   => $user->id,
                        'title'                     => $data['title'],
                        'notes'                     => $data['notes'] ?? null,
                        'all_day'                   => (bool)($data['all_day'] ?? false),
                        'starts_at'                 => $starts,
                        'ends_at'                   => $ends,
                        'email_to'                  => $data['send_email'] ? ($data['email_to'] ?? null) : null,
                        'email_at'                  => $emailAt,
                        'pre_email_enabled'         => (bool)($data['pre_email_enabled'] ?? true),
                        'pre_email_offset_minutes'  => (int)($data['pre_email_offset_minutes'] ?? 15),
                    ]);

                    $this->refreshRecords();
                    $this->dispatch('notify', type: 'success', message: 'Podsetnik kreiran.');
                }),

            // 🧰 Filter
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
                            'reminder'   => 'Podsetnici',
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
                    $this->showReminders  = in_array('reminder', $types, true);
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
        if ($this->showReminders)  $out[] = 'reminder';
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

        // 🔔 Lični podsetnici (samo za prijavljenog korisnika)
        if ($this->showReminders) {
            $reminders = Reminder::query()
                ->where('user_id', auth()->id())
                ->when($rangeStart && $rangeEnd, function ($q) use ($rangeStart, $rangeEnd) {
                    $q->where(function ($q2) use ($rangeStart, $rangeEnd) {
                        $q2->whereBetween('starts_at', [$rangeStart, $rangeEnd])
                           ->orWhereBetween('ends_at',   [$rangeStart, $rangeEnd])
                           ->orWhere(function ($q3) use ($rangeStart, $rangeEnd) {
                               $q3->where('starts_at', '<=', $rangeStart)
                                  ->where('ends_at', '>=', $rangeEnd);
                           });
                    });
                })
                ->limit(500)
                ->get();

            foreach ($reminders as $r) {
                $start = $r->starts_at?->clone()->timezone(config('app.timezone'));
                $end   = $r->ends_at?->clone()->timezone(config('app.timezone')) ?? $start;

                $events->push(
                    CalendarEvent::make($r)
                        ->title('🔔 ' . $r->title)
                        ->start($start->toIso8601String())
                        ->end($end->toIso8601String())
                        ->allDay((bool)$r->all_day)
                        ->backgroundColor(self::COLOR_REMINDER)
                        ->textColor('#111827')
                        ->extendedProps(['type' => 'reminder'])
                );
            }
        }

        return $events->values();
    }

    public function getEventClickContextMenuActions(): array
    {
        return [
            // ovde kasnije možemo dodati edit/brisanje za reminder
        ];
    }

    public function getSchema(?string $model = null): ?array
    {
        return match ($model) {
            \App\Models\Reminder::class => [
                Placeholder::make('naslov')->label('Naslov')
                    ->content(fn (?Reminder $r) => $r?->title ?? '—'),

                Placeholder::make('beleska')->label('Beleška')
                    ->content(fn (?Reminder $r) => $r?->notes ?: '—'),

                Placeholder::make('termin')->label('Termin')
                    ->content(function (?Reminder $r) {
                        if (!$r) return '—';
                        $tz = config('app.timezone');
                        $fmt = $r->all_day ? 'd.m.Y' : 'd.m.Y H:i';
                        $start = $r->starts_at?->clone()->timezone($tz)->format($fmt);
                        $end   = $r->ends_at?->clone()->timezone($tz)->format($fmt);
                        return $end ? "$start — $end" : ($start ?: '—');
                    }),

                Placeholder::make('celodnevni')->label('Celodnevni')
                    ->content(fn (?Reminder $r) => $r?->all_day ? 'Da' : 'Ne'),

                Placeholder::make('email')->label('Email primaoca')
                    ->content(fn (?Reminder $r) => $r?->email_to ?: '—'),

                Placeholder::make('email_at')->label('Vreme slanja mejla')
                    ->content(function (?Reminder $r) {
                        if (!$r || !$r->email_at) return '—';
                        return $r->email_at->timezone(config('app.timezone'))->format('d.m.Y H:i');
                    }),

                Placeholder::make('pre_reminder')->label('Pre-podsetnik')
                    ->content(fn (?Reminder $r) => $r?->pre_email_enabled
                        ? 'Da, ' . (int)($r->pre_email_offset_minutes ?? 15) . ' min ranije'
                        : 'Ne'),

                Placeholder::make('status_slanja')->label('Status slanja')
                    ->content(function (?Reminder $r) {
                        if (!$r) return '—';
                        $tz = config('app.timezone');
                        $pre = $r->pre_emailed_at
                            ? 'PRE poslato ' . $r->pre_emailed_at->timezone($tz)->format('d.m.Y H:i')
                            : 'PRE nije poslato';
                        $main = $r->emailed_at
                            ? 'GLAVNI poslato ' . $r->emailed_at->timezone($tz)->format('d.m.Y H:i')
                            : 'GLAVNI nije poslato';
                        return $pre . ' • ' . $main;
                    }),
            ],

            // postojeće šeme (ostavi kako već imaš):
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
