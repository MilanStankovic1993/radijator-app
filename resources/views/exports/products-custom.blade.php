<table>
    <thead>
        <tr>
            <th>Šifra</th>
            <th>Naziv</th>
            <th>Opis</th>
            <th>Cena</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($products as $product)
        <tr>
            <td>{{ $product->code }}</td>
            <td>{{ $product->name }}</td>
            <td>{{ $product->description }}</td>
            <td>{{ number_format($product->price, 2) }}</td>
            <td>{{ $product->status }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
