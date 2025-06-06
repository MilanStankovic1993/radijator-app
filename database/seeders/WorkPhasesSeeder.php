<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkPhasesSeeder extends Seeder
{
    public function run()
    {
        $phases = [
            ['name' => 'Pakovanje poda', 'time_norm' => 20.0],
            ['name' => 'Priprema (obrada) stranica I plašta, krajcovanje', 'time_norm' => 15.0],
            ['name' => 'Pakovanje I plašt (CO2)', 'time_norm' => 16.8],
            ['name' => 'Zavarivanje I plašt (CO2)', 'time_norm' => 15.0],
            ['name' => 'Pakovanje sklopa priključaka (sa zadnje strane)', 'time_norm' => 5.0],
            ['name' => 'Pakovanje II plašt (CO2)', 'time_norm' => 105.0],
            ['name' => 'Zavarivanje II plašt (kutija, ojačanja)', 'time_norm' => 140.0],
            ['name' => 'Ispitivanje II plašta', 'time_norm' => 20.0],
            ['name' => 'Pakovanje i zavarivanje sklopa kutije primarnog vazduha', 'time_norm' => 10.0],
            ['name' => 'Pakovanje i zavarivanje kutije ventilatora (CO2)', 'time_norm' => 7.0],
            ['name' => 'Pakovanje turbulatora', 'time_norm' => 4.2],
            ['name' => 'Pakovanje, zavarivanje i obrada nosača turbulatora', 'time_norm' => 16.8],
            ['name' => 'Kompletiranje turbulatora', 'time_norm' => 35.0],
            ['name' => 'Pakovanje silosa', 'time_norm' => 40.0],
            ['name' => 'Pakovanje vrata (CO2)', 'time_norm' => 60.0],
            ['name' => 'Pakovanje poklopaca (CO2)', 'time_norm' => 8.0],
            ['name' => 'Pakovanje, zavarivanje i obrada ložišta', 'time_norm' => 35.0],
            ['name' => 'Kićenje I faza (pakovanje i zavarivanje)', 'time_norm' => 25.0],
            ['name' => 'Pakovanje i zavarivanje spirale dozera', 'time_norm' => 14.0],
            ['name' => 'Pakovanje i zavarivanje osnovnog dela dozera', 'time_norm' => 10.0],
            ['name' => 'Ispitivanje II faza', 'time_norm' => 20.0],
            ['name' => 'Kićenje I faza završna montaža', 'time_norm' => 70.0],
            ['name' => 'Kićenje II faza završna montaža', 'time_norm' => 168.0],
            ['name' => 'Završna montaža vrata', 'time_norm' => 15.0],
            ['name' => 'Završna montaža poklopca', 'time_norm' => 12.0],
            ['name' => 'Pakovanje i varenje oplate (CO2)', 'time_norm' => 10.0],
            ['name' => 'Plotna-zavarivanje i obrada', 'time_norm' => 15.0],
            ['name' => 'Punktovanje pozicija oplate', 'time_norm' => 4.0],
            ['name' => 'Brušenje oplate', 'time_norm' => 1.0],
            ['name' => 'Brušenje vrata', 'time_norm' => 10.0],
            ['name' => 'Pakovanje i brušenje pepeljare', 'time_norm' => 15.0],
            ['name' => 'Kačenje i plasticifiranje oplate', 'time_norm' => 4.0],
            ['name' => 'Skidanje sa linije i odlaganje u korpu', 'time_norm' => 4.0],
            ['name' => 'Farbanje kotla', 'time_norm' => 32.0],
            ['name' => 'Farbanje podsklopova i delova kotla', 'time_norm' => 32.0],
            ['name' => 'Sečenje izolacije-priprema izolacije', 'time_norm' => 14.0],
            ['name' => 'Priprema oplate za montažu', 'time_norm' => 70.0],
            ['name' => 'Završna montaža oplate', 'time_norm' => 84.0],
            ['name' => 'Montaža dozera', 'time_norm' => 25.0],
            ['name' => 'Priprema elektro podsklopova i programiranje automatike', 'time_norm' => 32.0],
            ['name' => 'Montaža elektro podsklopova na proizvod', 'time_norm' => 32.0],
            ['name' => 'Test bezbednosti i funkcionalnosti', 'time_norm' => 13.0],
            ['name' => 'Pakovanje i priprema kotla za transport', 'time_norm' => 30.0],
        ];

        foreach ($phases as &$phase) {
            $phase['location'] = 'Seovac';
            $phase['description'] = null;
        }

        DB::table('work_phases')->insert($phases);
    }
}
