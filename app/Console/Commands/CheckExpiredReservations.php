<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ReservationRequest;
use App\Models\BloodBag;
use App\Models\CenterBloodTypeInventory;
use Carbon\Carbon;

class CheckExpiredReservations extends Command
{
    protected $signature = 'reservations:check-expired';
    protected $description = 'Vérifier et marquer comme expirées les réservations dépassées, libérer les stocks';

    public function handle()
    {
        $this->info('🔍 Vérification des réservations expirées...');
        
        try {
            $expiredReservations = ReservationRequest::where('status', 'confirmed')
                ->where('expires_at', '<', Carbon::now())
                ->get();
            
            $count = 0;
            $releasedBags = 0;
            
            foreach ($expiredReservations as $reservation) {
                $this->line("Traitement de la réservation #{$reservation->id}...");
                
                \DB::transaction(function () use ($reservation, &$releasedBags) {
                    // Récupérer les poches de sang réservées
                    $bloodBagIds = $reservation->bloodBags()->pluck('blood_bag_id');
                    $bagCount = $bloodBagIds->count();
                    
                    // Libérer les poches de sang
                    if ($bagCount > 0) {
                        BloodBag::whereIn('id', $bloodBagIds)
                            ->where('status', 'reserved')
                            ->update(['status' => 'available']);
                        
                        $releasedBags += $bagCount;
                    }
                    
                    // Marquer la réservation comme expirée
                    $reservation->update([
                        'status' => 'expired',
                        'manager_notes' => ($reservation->manager_notes ?? '') . ' | Expirée automatiquement le ' . Carbon::now()->format('d/m/Y H:i')
                    ]);
                    
                    // Mettre à jour la commande associée
                    if ($reservation->order) {
                        $reservation->order->update(['status' => 'expired']);
                    }
                    
                    // Mettre à jour les inventaires pour chaque type de sang affecté
                    $bloodTypeIds = BloodBag::whereIn('id', $bloodBagIds)->distinct()->pluck('blood_type_id');
                    foreach ($bloodTypeIds as $bloodTypeId) {
                        $this->updateInventory($reservation->center_id, $bloodTypeId);
                    }
                });
                
                $count++;
                $this->line("  ✅ Réservation #{$reservation->id} expirée, {$bloodBagIds->count()} poches libérées");
            }
            
            $this->info("📊 Vérification terminée:");
            $this->info("  - {$count} réservations expirées");
            $this->info("  - {$releasedBags} poches de sang libérées");
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('❌ Erreur lors de la vérification des expirations: ' . $e->getMessage());
            \Log::error('CheckExpiredReservations command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return Command::FAILURE;
        }
    }
    
    /**
     * Mettre à jour l'inventaire d'un centre pour un type de sang
     */
    private function updateInventory($centerId, $bloodTypeId)
    {
        $availableCount = BloodBag::where('center_id', $centerId)
            ->where('blood_type_id', $bloodTypeId)
            ->where('status', 'available')
            ->count();

        $reservedCount = BloodBag::where('center_id', $centerId)
            ->where('blood_type_id', $bloodTypeId)
            ->where('status', 'reserved')
            ->count();

        CenterBloodTypeInventory::updateOrCreate(
            [
                'center_id' => $centerId,
                'blood_type_id' => $bloodTypeId,
            ],
            [
                'available_quantity' => $availableCount,
                'reserved_quantity' => $reservedCount,
            ]
        );
    }
}