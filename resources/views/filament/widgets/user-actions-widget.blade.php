<x-filament::widget>
    <x-filament::card>
        <h2 class="text-lg font-bold mb-4">Poslednje aktivnosti</h2>

        <ul class="space-y-2">
            @forelse ($this->getActions() as $action)
                <li class="text-sm text-gray-700">
                    <span class="font-semibold">{{ $action->description }}</span>
                    na modelu <span class="italic">{{ class_basename($action->subject_type) }}</span>
                    (ID: {{ $action->subject_id }})<br>
                    <span class="text-xs text-gray-500">{{ $action->created_at->diffForHumans() }}</span>
                </li>
            @empty
                <li class="text-sm text-gray-500">Nema aktivnosti.</li>
            @endforelse
        </ul>
    </x-filament::card>
</x-filament::widget>
