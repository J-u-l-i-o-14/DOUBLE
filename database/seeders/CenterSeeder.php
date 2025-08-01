<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Center;
use App\Models\Region;

class CenterSeeder extends Seeder
{
    public function run(): void
    {
        $regions = Region::all();
        $centers = [
            // Un seul centre par région
            1 => [
                'name' => 'CHU SO - Tokoin Doumassesse',
                'address' => 'Tokoin Doumassessé, Lomé',
                'phone' => '+228 22 21 45 67',
                'email' => 'chu.so@cts-togo.tg',
                'capacity' => 800,
                'is_active' => true,
            ],
            2 => [
                'name' => 'CHR Atakpame',
                'address' => 'Atakpamé, Région des Plateaux',
                'phone' => '+228 24 45 67 89',
                'email' => 'chr.atakpame@cts-togo.tg',
                'capacity' => 600,
                'is_active' => true,
            ],
            3 => [
                'name' => 'CHR Sokode',
                'address' => 'Sokodé, Région Centrale',
                'phone' => '+228 26 67 89 01',
                'email' => 'chr.sokode@cts-togo.tg',
                'capacity' => 700,
                'is_active' => true,
            ],
            4 => [
                'name' => 'CHU Kara',
                'address' => 'Kara, Région de la Kara',
                'phone' => '+228 27 89 01 23',
                'email' => 'chu.kara@cts-togo.tg',
                'capacity' => 750,
                'is_active' => true,
            ],
            5 => [
                'name' => 'CHR Dapaong',
                'address' => 'Dapaong, Région des Savanes',
                'phone' => '+228 27 01 23 45',
                'email' => 'chr.dapaong@cts-togo.tg',
                'capacity' => 600,
                'is_active' => true,
            ],
        ];

        foreach ($regions as $region) {
            $centerData = $centers[$region->id];
            $centerData['region_id'] = $region->id;
            Center::create($centerData);
        }
    }
} 