@php
    $value = is_callable($state) && !is_string($state) ? $state() : $state;
@endphp

@if ($value)
    <a href="{{ asset('storage/' . $value) }}" target="_blank" class="text-primary-600 hover:underline">
        Prikaži fajl
    </a>
@else
    <span>-</span>
@endif
