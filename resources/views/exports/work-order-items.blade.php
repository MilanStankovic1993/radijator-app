@foreach ($items as $item)
<table border="1" cellspacing="0" cellpadding="5" style="border-collapse:collapse; width:100%; font-size:12px;">
    {{-- Naslov i gornji deo --}}
    <tr>
        <td colspan="4" rowspan="4" style="background-color:#CCFFCC;">
            <strong>RADNA LISTA-IZVEŠTAJ O RADU</strong><br>
            Linija montaže br:<br>
            Ime i prezime radnika/potpis:<br>
            Matični br. radnika:
        </td>
        <td colspan="2" style="text-align:center;" rowspan="4">
            <img src="{{ public_path('logo.png') }}" width="150">
        </td>
        <td><strong>RN BR:</strong></td>
        <td>{{ $workOrder->full_name }}</td>
    </tr>
    <tr>
        <td><strong>Kotao:</strong></td>
        <td>{{ $item->product_name ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td><strong>Datum:</strong></td>
        <td>{{ \Carbon\Carbon::parse($workOrder->launch_date)->format('d.m.Y') }}</td>
    </tr>
    <tr>
        <td><strong>Količina:</strong></td>
        <td>{{ $workOrder->quantity }}</td>
    </tr>

    {{-- Operacija --}}
    <tr style="background-color:#CCFFCC;">
        <td colspan="2"><strong>OPERACIJA BR</strong></td>
        <td colspan="2">{{ $item->operation_number ?? '' }}</td>
        <td><strong>Tprz[min]</strong></td>
        <td>&nbsp;</td>
        <td colspan="2"><strong>urađena količina</strong></td>
    </tr>
    <tr>
        <td colspan="2"><strong>Šifra posla</strong></td>
        <td><strong>Tk[min]</strong></td>
        <td>{{ $item->time_norm ?? '' }}</td>
        <td><strong>datum</strong></td>
        <td colspan="3">&nbsp;</td>
    </tr>
    <tr>
        <td colspan="2"><strong>Br.radnika</strong></td>
        <td>{{ $item->number_of_workers ?? '' }}</td>
        <td><strong>Vreme početka</strong></td>
        <td colspan="4">&nbsp;</td>
    </tr>
    <tr>
        <td colspan="3">&nbsp;</td>
        <td><strong>Vreme završetka</strong></td>
        <td colspan="4">&nbsp;</td>
    </tr>

    {{-- Opis operacije --}}
    <tr>
        <td colspan="8"><strong>{{ $item->operation_description ?? 'Pakovanje sklopa priključaka (sa zadnje strane)' }}</strong></td>
    </tr>

    {{-- Kontrola --}}
    <tr style="background-color:#CCFFCC;">
        <td colspan="2"><strong>OPERACIJA</strong></td>
        <td colspan="2">&nbsp;</td>
        <td><strong>obim kontrole</strong></td>
        <td>100%</td>
        <td>50%</td>
        <td>10% uzork</td>
    </tr>
    <tr>
        <td colspan="2"><strong>Kontrola</strong></td>
        <td colspan="2">&nbsp;</td>
        <td><strong>datum</strong></td>
        <td colspan="3">&nbsp;</td>
    </tr>
    <tr>
        <td colspan="2"><strong>Izveštaj o doradi br:</strong></td>
        <td colspan="2">1xRadnik</td>
        <td><strong>kontrolor</strong></td>
        <td colspan="3">&nbsp;</td>
    </tr>
    <tr>
        <td colspan="2"><strong>Izveštaj o škartu br:</strong></td>
        <td colspan="2">1xRJ</td>
        <td colspan="4">&nbsp;</td>
    </tr>
</table>
<br><br>
@endforeach
