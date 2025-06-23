<div class="space-y-4">
    <h3 class="text-lg font-semibold text-gray-100">Stavke porudžbine</h3>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach ($order->items as $item)
            <div class="bg-gray-800 rounded-lg p-4 shadow-sm border border-gray-700">
                <div class="text-sm text-gray-300">
                    <span class="block text-white font-semibold truncate">
                        {{ $item->product->name ?? 'Nepoznat proizvod' }}
                    </span>
                    <span class="text-sm text-gray-400">Količina: <strong>{{ $item->quantity }}</strong> kom.</span>
                </div>
            </div>
        @endforeach
    </div>
</div>
