<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

echo "=== Correction des ReservationRequests manquantes ===\n\n";

try {
    // Trouver les commandes sans ReservationRequest
    $ordersWithoutReservation = \App\Models\Order::doesntHave('reservationRequest')->get();
    
    echo "Commandes sans ReservationRequest: " . $ordersWithoutReservation->count() . "\n\n";
    
    if ($ordersWithoutReservation->count() > 0) {
        $fixed = 0;
        foreach ($ordersWithoutReservation as $order) {
            echo "Traitement de la commande ID: {$order->id}\n";
            
            try {
                $reservation = $order->createReservationRequest();
                
                if ($reservation) {
                    echo "✓ ReservationRequest créée (ID: {$reservation->id})\n";
                    $fixed++;
                } else {
                    echo "✗ Échec de création pour la commande {$order->id}\n";
                }
            } catch (\Exception $e) {
                echo "✗ Erreur pour la commande {$order->id}: " . $e->getMessage() . "\n";
            }
        }
        
        echo "\n=== Résumé ===\n";
        echo "Commandes traitées: " . $ordersWithoutReservation->count() . "\n";
        echo "ReservationRequests créées: {$fixed}\n";
    } else {
        echo "Toutes les commandes ont déjà leur ReservationRequest.\n";
    }
    
    // Vérification finale
    echo "\n=== Vérification finale ===\n";
    $totalOrders = \App\Models\Order::count();
    $totalReservations = \App\Models\ReservationRequest::count();
    $ordersWithReservation = \App\Models\Order::has('reservationRequest')->count();
    
    echo "Total commandes: {$totalOrders}\n";
    echo "Total ReservationRequests: {$totalReservations}\n";
    echo "Commandes avec ReservationRequest: {$ordersWithReservation}\n";
    echo "Commandes sans ReservationRequest: " . ($totalOrders - $ordersWithReservation) . "\n";
    
    echo "\n=== Correction terminée ===\n";
    
} catch (\Exception $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
