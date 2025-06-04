<?php

namespace App\Filament\Resources\WorkOrderResource\Widgets;

use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use App\Models\WorkOrder;

class WorkOrdersAreaChart extends ApexChartWidget
{
    protected static ?string $chartId = 'workOrdersAreaChart';

    protected static ?string $heading = 'Kumulativni rast radnih naloga po minutima';

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $data = WorkOrder::selectRaw('DATE_FORMAT(created_at, "%Y-%m-%d %H:%i") as vreme, COUNT(*) as broj')
            ->groupBy('vreme')
            ->orderBy('vreme')
            ->get();

        $kumulativniBrojevi = [];
        $suma = 0;
        foreach ($data as $row) {
            $suma += $row->broj;
            $kumulativniBrojevi[] = $suma;
        }

        return [
            'chart' => [
                'type' => 'area',
                'height' => 300,
                'toolbar' => ['show' => false],
            ],
            'series' => [
                [
                    'name' => 'Ukupno nalozi',
                    'data' => $kumulativniBrojevi,
                ],
            ],
            'xaxis' => [
                'categories' => $data->pluck('vreme')->toArray(),
                'title' => ['text' => 'Vreme (Y-m-d H:i)'],
                'labels' => [
                    'rotate' => -45,
                    'hideOverlappingLabels' => true,
                    'trim' => true,
                ],
            ],
            'tooltip' => [
                'x' => [
                    'format' => 'yyyy-MM-dd HH:mm',
                ],
            ],
        ];
    }
}
