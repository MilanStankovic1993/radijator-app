<h2>{{ $isPre ? 'Uskoro: ' : '' }}{{ $reminder->title }}</h2>

@if($reminder->notes)
<p style="white-space:pre-wrap">{{ $reminder->notes }}</p>
@endif

@php
    $tz = config('app.timezone');
    $start = $reminder->starts_at?->timezone($tz);
    $end   = $reminder->ends_at?->timezone($tz);
@endphp

<p>
    Termin:
    {{ $start ? $start->format('d.m.Y H:i') : '—' }}
    @if($end) — {{ $end->format('d.m.Y H:i') }} @endif
    @if($reminder->all_day) (celodnevno) @endif
</p>
