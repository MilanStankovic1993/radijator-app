<table class="w-full table-auto border-collapse border border-gray-200">
    <thead>
        <tr>
            <th class="border border-gray-300 p-2 text-left">Šifra</th>
            <th class="border border-gray-300 p-2 text-left">Faza</th>
            <th class="border border-gray-300 p-2 text-left">Potvrđeno</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($workOrderItems as $item)
            <tr>
                <td class="border border-gray-300 p-2">{{ $item->code }}</td>
                <td class="border border-gray-300 p-2">{{ $item->workPhase->name }}</td>
                <td class="border border-gray-300 p-2 text-center">
                    @if ($item->is_confirmed)
                        ✔️
                    @else
                        ❌
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
