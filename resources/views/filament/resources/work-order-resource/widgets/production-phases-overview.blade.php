<div class="space-y-2">
    @foreach ($data as $index => $item)
        <div x-data="{ open: false }" class="border rounded p-4 shadow">
            <div class="flex justify-between items-center">
                <div>
                    <strong>Radni nalog:</strong> {{ $item['work_order'] }} <br>
                    <strong>Proizvod:</strong> {{ $item['product'] }} <br>
                    <strong>Faza:</strong> {{ $item['phase'] }} <br>
                    <strong>Status:</strong> {!! $item['is_completed'] !!}
                </div>
                <button @click="open = !open" class="text-blue-500 hover:underline">
                    <span x-show="!open">Detalji ▼</span>
                    <span x-show="open">Sakrij ▲</span>
                </button>
            </div>

            <div x-show="open" x-transition class="mt-4 border-t pt-2 text-sm text-gray-600">
                <p><strong>ID proizvoda:</strong> {{ $item['product_id'] ?? 'N/A' }}</p>
                <p><strong>ID faze:</strong> {{ $item['phase_id'] ?? 'N/A' }}</p>
                <p><strong>Označeno kao završeno:</strong> {{ $item['is_completed'] === '✅' ? 'Da' : 'Ne' }}</p>
            </div>
        </div>
    @endforeach
</div>
