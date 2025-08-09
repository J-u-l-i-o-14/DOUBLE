<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ReservationRequest;
use App\Models\BloodType;

class FixReservationItems extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reservations:fix-items';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Corriger les items de réservation manquants';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Correction des items de réservation...');

        $reservations = ReservationRequest::with(['order', 'items'])->get();
        $fixed = 0;

        foreach ($reservations as $reservation) {
            if ($reservation->items->isEmpty() && $reservation->order) {
                $order = $reservation->order;
                
                // Trouver le blood_type_id
                $bloodTypeId = $order->blood_type_id;
                if (!$bloodTypeId && $order->blood_type) {
                    $bloodType = BloodType::where('group', $order->blood_type)->first();
                    $bloodTypeId = $bloodType ? $bloodType->id : null;
                    
                    // Mettre à jour l'order aussi
                    if ($bloodTypeId) {
                        $order->update(['blood_type_id' => $bloodTypeId]);
                    }
                }

                if ($bloodTypeId) {
                    $reservation->items()->create([
                        'blood_type_id' => $bloodTypeId,
                        'quantity' => $order->quantity ?? 1,
                        'unit_price' => $order->unit_price ?? ($order->total_amount / ($order->quantity ?? 1)),
                        'total_price' => $order->total_amount,
                    ]);

                    $this->info("✓ Items créés pour réservation #{$reservation->id} (Order #{$order->id})");
                    $fixed++;
                } else {
                    $this->error("✗ Impossible de trouver blood_type_id pour réservation #{$reservation->id}");
                }
            }
        }

        $this->info("\n{$fixed} réservations corrigées avec succès.");
        
        return Command::SUCCESS;
    }
}
