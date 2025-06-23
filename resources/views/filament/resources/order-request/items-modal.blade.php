<div class="space-y-4">
    <ul class="list-disc list-inside">
        @foreach ($order->items as $item)
            <li>{{ $item->product->name ?? 'Nepoznat proizvod' }} – {{ $item->quantity }} kom.</li>
        @endforeach
    </ul>
</div>
