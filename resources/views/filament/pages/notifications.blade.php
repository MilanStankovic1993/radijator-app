<x-filament::page>
    <div>
        <h2 class="text-xl font-bold mb-4">🛎 Notifikacije</h2>

        <div class="space-y-4">
            @foreach ($this->getNotifications() as $notification)
                <div class="p-4 bg-white dark:bg-gray-800 shadow rounded border border-gray-200 dark:border-gray-700">
                    <div class="font-semibold text-primary-600 dark:text-primary-400">
                        {{ $notification->data['title'] ?? 'Bez naslova' }}
                    </div>
                    <div class="text-sm text-gray-700 dark:text-gray-300">
                        {{ $notification->data['message'] ?? 'Bez poruke' }}
                    </div>
                    @if(isset($notification->data['url']))
                        <a href="{{ $notification->data['url'] }}" class="text-sm text-blue-600 underline mt-1 inline-block">Detalji</a>
                    @endif
                    <div class="text-xs text-gray-500 mt-1">
                        {{ $notification->created_at->diffForHumans() }}
                    </div>
                </div>
            @endforeach

            @if ($this->getNotifications()->isEmpty())
                <div class="text-gray-500 dark:text-gray-400">Nema notifikacija.</div>
            @endif
        </div>
    </div>
</x-filament::page>
