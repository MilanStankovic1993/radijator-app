@foreach ($items as $item)
<!-- print_r($item);
die(); -->
    <table border="1" cellpadding="5" cellspacing="0" style="border-collapse:collapse; width:100%;">
        <tr>
            <td colspan="6" style="background-color:#CCFFCC;"><strong>RADNA LISTA-IZVEŠTAJ O RADU</strong></td>
            <td rowspan="3" colspan="2" style="text-align:center;"><img src="{{ public_path('logo.png') }}" width="150"></td>
        </tr>
        <tr>
            <td>RN BR:</td>
            <td colspan="2">{{ $workOrder->full_name }}</td>
            <td>Kotao:</td>
            <td colspan="2">{{ $item->product_name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td>Datum:</td>
            <td colspan="2">{{ \Carbon\Carbon::parse($workOrder->launch_date)->format('d.m.Y') }}</td>
            <td>Količina:</td>
            <td colspan="2">{{ $workOrder->quantity }}</td>
        </tr>
        <tr style="background-color:#CCFFCC;">
            <td colspan="2">OPERACIJA BR</td>
            <td colspan="2">{{ $item->operation_number ?? 'N/A' }}</td> 
            <td colspan="2">Tprz[min]</td>
            <td colspan="2">urađena količina</td>
        </tr>
        <tr>
            <td colspan="2">Šifra posla</td>
            <td>Tk[min]</td>
            <td>{{ $item->time_norm ?? '' }}</td>
            <td>datum</td>
            <td colspan="3"></td>
        </tr>
        <tr>
            <td colspan="2">Šifra posla</td>
            <td>{{ $item->work_phases_name ?? '' }}</td>
        </tr>
        <tr>
            <td colspan="2">Br.radnika</td>
            <td>{{ $item->number_of_workers	 ?? '' }}</td>
            <td>Vreme početka</td>
            <td colspan="4"></td>
        </tr>
        <tr>
            <td colspan="2"></td>
            <td></td>
            <td>Vreme završetka</td>
            <td colspan="4"></td>
        </tr>
        <tr>
            <td colspan="8"><strong>{{ $item->operation_description ?? 'N/A' }}</strong></td>
        </tr>
        <tr style="background-color:#CCFFCC;">
            <td colspan="2">OPERACIJA</td>
            <td colspan="2"></td>
            <td>obim kontrole</td>
            <td>100%</td>
            <td>50%</td>
            <td>10% uzork</td>
        </tr>
        <tr>
            <td colspan="2">Kontrola</td>
            <td colspan="2"></td>
            <td>datum</td>
            <td colspan="3"></td>
        </tr>
        <tr>
            <td colspan="2">Izveštaj o doradi br:</td>
            <td colspan="2"></td>
            <td>kontrolor</td>
            <td colspan="3"></td>
        </tr>
        <tr>
            <td colspan="2">Izveštaj o škartu br:</td>
            <td colspan="2"></td>
            <td colspan="4"></td>
        </tr>
    </table>
    <br><br>
@endforeach