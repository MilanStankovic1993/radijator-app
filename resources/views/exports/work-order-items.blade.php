@foreach ($items as $item)
<table border="1" cellspacing="0" cellpadding="5" style="border-collapse: collapse; width: 100%; font-size: 12px;">
    <colgroup>
        <col style="width: 100px;">
        <col style="width: 100px;">
        <col style="width: 80px;">
        <col style="width: 110px;">
        <col style="width: 40px;">
        <col style="width: 110px;">
        <col style="width: 50px;">
        <col style="width: 50px;">
        <col style="width: 50px;">
        <col style="width: 55px;">
        <col style="width: 80px;">
        <col style="width: 60px;">
        <col style="width: 100px;">
    </colgroup>

    <tr>
        <td colspan="13" style="background-color: #EEEEEE; text-align: left; font-weight: bold; font-size: 14px; border: 2px solid #999;">
            STAVKA {{ $loop->iteration }}
        </td>
    </tr>

    <tr>
        <td colspan="3" style="background-color: #CCFFCC; font-weight: bold; border: 2px solid black;">RADNA LISTA-IZVEŠTAJ O RADU</td>
        <td colspan="7" rowspan="5" style="border: 2px solid black;"></td>
        <td style="background-color: #CCFFCC; font-weight: bold; border: 2px solid black;">RN BR:</td>
        <td colspan="2" style="font-weight: bold; border: 2px solid black;">{{ $workOrder->full_name }}</td>
    </tr>

    <tr>
        <td colspan="2" style="font-weight: bold; border: 2px solid black;">Linija montaže br:</td>
        <td style="font-weight: bold; border: 2px solid black;"></td>
        <td style="background-color: #CCFFCC; font-weight: bold; border: 2px solid black;">Kotao:</td>
        <td colspan="2" style="font-weight: bold; border: 2px solid black;">{{ $item->product->name ?? 'N/A' }}</td>
    </tr>

    <tr>
        <td colspan="2" style="font-weight: bold; border: 2px solid black;">Ime i prezime radnika / potpis:</td>
        <td style="font-weight: bold; border: 2px solid black;"></td>
        <td style="background-color: #CCFFCC; font-weight: bold; border: 2px solid black;">Datum:</td>
        <td colspan="2" style="font-weight: bold; border: 2px solid black;">{{ \Carbon\Carbon::parse($workOrder->launch_date)->format('d.m.Y') }}</td>
    </tr>

    <tr>
        <td colspan="2" style="font-weight: bold; border: 2px solid black;">Matični br. radnika:</td>
        <td style="font-weight: bold; border: 2px solid black;"></td>
        <td style="background-color: #CCFFCC; font-weight: bold; border: 2px solid black;">Količina:</td>
        <td colspan="2" style="font-weight: bold; text-align: left; border: 2px solid black;">{{ $workOrder->quantity }}</td>
    </tr>

    <tr>@for ($i = 0; $i < 13; $i++) <td ></td> @endfor</tr>
    <tr>@for ($i = 0; $i < 13; $i++) <td ></td> @endfor</tr>

    <tr>
        <td colspan="3" style="background-color: #CCFFCC; font-weight: bold; border: 2px solid black;">OPERACIJA BR: {{ $loop->iteration * 10 }}</td>
        <td style="font-weight: bold; border: 2px solid black;">Tprz[min]</td>
        <td style="font-weight: bold; border: 2px solid black;"></td>
        <td style="font-weight: bold; border: 2px solid black;">Urađena količina</td>
        <td colspan="7" style="font-weight: bold; border: 2px solid black;"></td>
    </tr>

    <tr>
        <td style="font-weight: bold; border: 2px solid black;">Šifra posla</td>
        <td colspan="2" style="font-weight: bold; border: 2px solid black;"></td>
        <td style="font-weight: bold; border: 2px solid black;">Tk[min]</td>
        <td style="font-weight: bold; border: 2px solid black;">{{ $item->workPhase->time_norm }}</td>
        <td style="font-weight: bold; border: 2px solid black;">Datum</td>
        <td colspan="7" style="font-weight: bold; border: 2px solid black;"></td>
    </tr>

    <tr>
        <td style="font-weight: bold; border: 2px solid black;">Br. radnika</td>
        <td style="font-weight: bold; border: 2px solid black;">{{ $item->workPhase->number_of_workers }}</td>
        <td style="font-weight: bold; border: 2px solid black;"></td>
        <td style="font-weight: bold; border: 2px solid black;">Vreme početka</td>
        <td colspan="2" style="font-weight: bold; border: 2px solid black;"></td>
        <td colspan="7" style="font-weight: bold; border: 2px solid black;"></td>
    </tr>

    <tr>
        <td colspan="3"></td>
        <td style="font-weight: bold; border: 2px solid black;">Vreme završetka</td>
        <td colspan="2" style="font-weight: bold; border: 2px solid black;"></td>
        <td colspan="7" style="font-weight: bold; border: 2px solid black;"></td>
    </tr>

    <tr>
        <td colspan="13" style="font-weight: bold; font-size: 14px; border: 2px solid black;">{{ $item->workPhase->name }}</td>
    </tr>

    <tr>@for ($i = 0; $i < 13; $i++) <td style="font-weight: bold; border: 2px solid black;"></td> @endfor</tr>
    <tr>@for ($i = 0; $i < 13; $i++) <td style="font-weight: bold; border: 2px solid black;"></td> @endfor</tr>

    <tr>
        <td colspan="3" style="background-color: #CCFFCC; font-weight: bold; border: 2px solid black;">OPERACIJA</td>
        <td colspan="3" style="font-weight: bold; border: 2px solid black;"></td>
        <td colspan="2" style="font-weight: bold; border: 2px solid black;">OBIM KONTROLE</td>
        <td style="font-weight: bold; border: 2px solid black;">100%</td>
        <td style="font-weight: bold; border: 2px solid black;">50%</td>
        <td style="font-weight: bold; border: 2px solid black;">10%</td>
        <td style="font-weight: bold; border: 2px solid black;">UZROK</td>
        <td style="font-weight: bold; border: 2px solid black;">OSTALO</td>
    </tr>

    <tr>
        <td style="font-weight: bold; border: 2px solid black;">KONTROLA</td>
        <td colspan="5" style="font-weight: bold; border: 2px solid black;"></td>
        <td colspan="2" style="font-weight: bold; border: 2px solid black;">Datum</td>
        <td colspan="1" style="font-weight: bold; border: 2px solid black;"></td>
        <td colspan="1" style="font-weight: bold; border: 2px solid black;"></td>
        <td colspan="1" style="font-weight: bold; border: 2px solid black;"></td>
        <td colspan="1" style="font-weight: bold; border: 2px solid black;"></td>
        <td colspan="1" style="font-weight: bold; border: 2px solid black;"></td>
    </tr>

    <tr>
        <td colspan="2" style="font-weight: bold; border: 2px solid black;">Izveštaj o doradi br:</td>
        <td colspan="2" style="font-weight: bold; border: 2px solid black;"></td>
        <td style="font-weight: bold; border: 2px solid black;"></td>
        <td style="font-weight: bold; border: 2px solid black;">1xRadnik</td>
        <td colspan="2" style="font-weight: bold; border: 2px solid black;">Kontrolor</td>
        <td colspan="1" style="font-weight: bold; border: 2px solid black;"></td>
        <td colspan="1" style="font-weight: bold; border: 2px solid black;"></td>
        <td colspan="1" style="font-weight: bold; border: 2px solid black;"></td>
        <td colspan="1" style="font-weight: bold; border: 2px solid black;"></td>
        <td colspan="1" style="font-weight: bold; border: 2px solid black;"></td>
    </tr>

    <tr>
        <td colspan="2" style="font-weight: bold; border: 2px solid black;">Izveštaj o škartu br:</td>
        <td colspan="2" style="font-weight: bold; border: 2px solid black;"></td>
        <td style="font-weight: bold; border: 2px solid black;"></td>
        <td style="font-weight: bold; border: 2px solid black;">1xRJ</td>
        <td colspan="7" style="font-weight: bold; border: 2px solid black;"></td>
    </tr>

    <tr>@for ($i = 0; $i < 13; $i++) <td style="font-weight: bold; border: 2px solid black;"></td> @endfor</tr>

    <tr>
        <td colspan="3" style="font-weight: bold; border: 2px solid black;"></td>
        <td style="font-weight: bold; border: 2px solid black;">Poslovođa</td>
        <td colspan="9" style="font-weight: bold; border: 2px solid black;"></td>
    </tr>
</table>
<br>
@endforeach
