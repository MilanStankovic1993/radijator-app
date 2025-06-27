<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <title>Faktura - {{ $order->order_code }}</title>
    <style>
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f0f0f0; }
    </style>
</head>
<body>
    <h2>Faktura</h2>
    <p><strong>Šifra narudžbine:</strong> {{ $order->order_code }}</p>
    <p><strong>Kupac:</strong> {{ $order->customer->name ?? 'Nepoznat' }}</p>

    <table>
        <thead>
            <tr>
                <th>Proizvod</th>
                <th>Količina</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
                <tr>
                    <td>{{ $item->product->name ?? 'Nepoznat proizvod' }}</td>
                    <td>{{ $item->quantity }} kom.</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
