<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\ReservationRequest;

class ConvertOrdersToReservations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:convert-to-reservations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convertir les commandes existantes en réservations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Conversion des commandes en réservations...');

        // D'abord s'assurer que tous les orders ont un blood_type_id valide
        $this->info('Mise à jour des blood_type_id...');
        $orders = Order::whereNull('blood_type_id')->whereNotNull('blood_type')->get();
        
        foreach ($orders as $order) {
            $bloodType = \App\Models\BloodType::where('group', $order->blood_type)->first();
            if ($bloodType) {
                $order->update(['blood_type_id' => $bloodType->id]);
                $this->info("✓ Order #{$order->id}: blood_type_id mis à jour vers {$bloodType->id}");
            }
        }

        // Maintenant convertir les orders qui n'ont pas encore de réservation
        $orders = Order::whereDoesntHave('reservationRequest')->get();
        
        $converted = 0;
        
        foreach ($orders as $order) {
            try {
                $reservation = $order->createReservationRequest();
                $this->info("✓ Order #{$order->id} convertie en réservation #{$reservation->id}");
                $converted++;
            } catch (\Exception $e) {
                $this->error("✗ Erreur pour Order #{$order->id}: " . $e->getMessage());
            }
        }

        $this->info("\n{$converted} commandes converties avec succès.");
        
        return Command::SUCCESS;
    }
}
