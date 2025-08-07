<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Seeders de base (tables de référence)
        $this->call([
            RegionSeeder::class,
            BloodTypeSeeder::class,
            CenterSeeder::class,
        ]);

        // Seeders des utilisateurs et données principales
        $this->call([
            UserSeeder::class,
            DonorSeeder::class,
            PatientSeeder::class,
        ]);

        // Seeders des données opérationnelles
        $this->call([
            BloodBagSeeder::class,
            CampaignSeeder::class,
            AppointmentSeeder::class,
            TransfusionSeeder::class,
        ]);

        // Seeders des données de gestion
        $this->call([
            StockThresholdSeeder::class,
            CenterScheduleSeeder::class,
            CenterBloodTypeInventorySeeder::class,
        ]);

        // Sprint 4 - Commandes et notifications
        $this->call([
            Sprint4Seeder::class,
        ]);
    }
}