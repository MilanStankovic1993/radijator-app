<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <title>Faktura - {{ $order->order_code }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            padding: 30px 35px 20px 35px;
            position: relative;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .logo { height: 60px; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #000;
            padding: 4px;
        }
        th {
            background: #f0f0f0;
        }
        .no-border td {
            border: none;
            padding: 2px;
            vertical-align: top;
        }
        .section {
            margin-top: 12px;
        }
        footer {
            position: absolute;
            bottom: 12px;
            left: 35px;
            right: 35px;
            font-size: 9px;
            text-align: center;
            border-top: 1px solid #999;
            padding-top: 4px;
        }
        .tight p {
            margin: 2px 0;
            padding: 0;
        }
    </style>
</head>
<body>

    {{-- Zaglavlje: logo + firma (levo), datum + kupac (desno) --}}
    <table class="no-border">
        <tr>
            <td style="width: 55%;">
                <img src="{{ public_path('images/logo.png') }}" class="logo" alt="Logo"><br>
                ul.Živojina Lazića-Solunca br.6-Kraljevo<br>
                Matični broj: 17388673<br>
                Registarski broj: 0501738673<br>
                Obveznik PDV: 134841870<br>
                Šifra delatnosti: 2521<br>
                PIB: 101070206
            </td>
            <td class="text-right">
                <strong>Datum i mesto izdavanja računa:</strong><br>
                Kraljevo, {{ now()->format('d.m.Y') }}. g.<br><br>

                <strong>{{ $order->customer->name ?? 'Nepoznat kupac' }}</strong><br>
                {{ $order->customer->address ?? 'Nemanjina 1' }}<br>
                {{ $order->customer->city ?? 'Beograd' }}<br>
                {{ $order->customer->country ?? 'Makedonija' }}<br>
                <strong>Danočen broj:</strong> {{ $order->customer->tax_number ?? '__________' }}
            </td>
        </tr>
    </table>

    <hr style="margin: 6px 0;">

    {{-- Naslov i tabela --}}

    <div class="section tight">
        <p><strong>Faktura br.:</strong> {{ $order->order_code }}</p>
        <p><strong>Datum prometa dobara:</strong> {{ \Carbon\Carbon::parse($order->created_at)->format('d.m.Y') }}. godine</p>
        <p>Poslali smo vam na vašu adresu niže navedenu robu</p>
        <p>Istovarna stanica: Prilep</p>
        <p>Način otpreme: -- KAMIONOM :</p>
    </div>

    {{-- Tabela sa artiklima --}}
    <table>
        <thead>
            <tr>
                <th>Broj artikla</th>
                <th>O P I S</th>
                <th>Količina</th>
                <th>Jed. mere</th>
                <th class="text-right">Cena (EUR)</th>
                <th class="text-right">Iznos (EUR)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}.</td>
                    <td>{{ $item->product->name ?? 'Nepoznat proizvod' }}</td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-center">kom</td>
                    <td class="text-right">{{ number_format($item->price ?? 0, 2, '.', ',') }}</td>
                    <td class="text-right">{{ number_format(($item->price ?? 0) * $item->quantity, 2, '.', ',') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            @php
                $total = $order->items->sum(fn($i) => ($i->price ?? 0) * $i->quantity);
            @endphp
            <tr>
                <td colspan="5" class="text-right"><strong>SVEGA :</strong></td>
                <td class="text-right">{{ number_format($total, 2, '.', ',') }}</td>
            </tr>
            <tr>
                <td colspan="5" class="text-right">Avans {{ now()->format('d.m.Y') }}.</td>
                <td class="text-right">{{ number_format($total, 2, '.', ',') }}</td>
            </tr>
            <tr>
                <td colspan="5" class="text-right"><strong>ZA UPLATU:</strong></td>
                <td class="text-right">0.00</td>
            </tr>
        </tfoot>
    </table>

    {{-- Završni deo --}}
    <div class="section tight">
        <p><strong>SLOVIMA:</strong> sesnaesthiljadaosamstotinadevedesetdevet EUR-a i 31/100.</p>
        <p><strong>DINARSKA PROTIVVREDNOST:</strong> 1.980.795.16 dinara</p>
        <p><strong>NAPOMENA:</strong> Oslobođeno plaćanja PDV na osnovu člana 24 stav 1 tačka 2</p>
        <p><strong>Paritet:</strong> EXW Kraljevo</p>
        <p><strong>Težina:</strong> 5.800 kg</p>
        <p><strong>PRILOG:</strong> uputstvo za plaćanje HALK BANKE</p>
        <p><strong>BROJ KOLETA:</strong></p>
        <p><strong>Plaćanje računa:</strong> avans 100%</p>
    </div>

    {{-- Footer --}}
    <footer>
        36000 Kraljevo, Živojina Lazića Solunca br.6, Srbija |
        Tel: +381 36 399 140 |
        Fax: +381 36 391 941 |
        <a href="http://www.radijator.rs">www.radijator.rs</a> |
        Email: bilja@radijator.rs
    </footer>

</body>
</html>
