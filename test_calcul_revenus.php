<?php

/**
 * Script pour tester le calcul des revenus en attente
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\ReservationRequest;
use App\Models\Order;

// Configuration Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🧮 TEST DU CALCUL DES REVENUS EN ATTENTE\n";
echo "=" . str_repeat("=", 50) . "\n\n";

// Test 1: Toutes les réservations avec orders
echo "📊 TOUTES LES RÉSERVATIONS AVEC COMMANDES:\n";
echo "-" . str_repeat("-", 40) . "\n";

$allReservations = ReservationRequest::with('order')->get();

foreach ($allReservations as $reservation) {
    if ($reservation->order) {
        $total = $reservation->order->total_amount ?? 0;
        $deposit = $reservation->order->deposit_amount ?? 0;
        $remaining = $reservation->order->remaining_amount ?? ($total - $deposit);
        
        echo sprintf("Réservation #%d (%s) - Commande #%d (%s, %s):\n", 
            $reservation->id, 
            $reservation->status, 
            $reservation->order->id,
            $reservation->order->status,
            $reservation->order->payment_status
        );
        echo sprintf("  Total: %s F CFA, Dépôt: %s F CFA, Restant: %s F CFA\n\n", 
            number_format($total), 
            number_format($deposit), 
            number_format($remaining)
        );
    }
}

// Test 2: Réservations qui doivent être comptées
echo "💰 RÉSERVATIONS COMPTÉES DANS PENDING_REVENUE:\n";
echo "-" . str_repeat("-", 50) . "\n";

$pendingReservations = ReservationRequest::whereIn('status', ['pending', 'confirmed'])
    ->whereHas('order', function($q) {
        $q->whereIn('payment_status', ['pending', 'partial'])
          ->whereNotIn('status', ['expired', 'cancelled', 'terminated', 'completed']);
    })
    ->with('order')
    ->get();

$totalPending = 0;

foreach ($pendingReservations as $reservation) {
    if ($reservation->order) {
        $total = $reservation->order->total_amount ?? 0;
        $deposit = $reservation->order->deposit_amount ?? 0;
        $remaining = $reservation->order->remaining_amount ?? ($total - $deposit);
        $toCount = max(0, $remaining);
        
        $totalPending += $toCount;
        
        echo sprintf("✅ Réservation #%d (%s) - Commande #%d (%s, %s):\n", 
            $reservation->id, 
            $reservation->status, 
            $reservation->order->id,
            $reservation->order->status,
            $reservation->order->payment_status
        );
        echo sprintf("  À compter: %s F CFA\n\n", number_format($toCount));
    }
}

echo "💵 TOTAL REVENUS EN ATTENTE: " . number_format($totalPending) . " F CFA\n\n";

// Test 3: Réservations qui ne doivent PAS être comptées
echo "❌ RÉSERVATIONS EXCLUES (finalisées):\n";
echo "-" . str_repeat("-", 40) . "\n";

$excludedReservations = ReservationRequest::whereIn('status', ['expired', 'cancelled', 'terminated', 'completed'])
    ->with('order')
    ->get();

foreach ($excludedReservations as $reservation) {
    if ($reservation->order) {
        $total = $reservation->order->total_amount ?? 0;
        $deposit = $reservation->order->deposit_amount ?? 0;
        $remaining = $reservation->order->remaining_amount ?? ($total - $deposit);
        
        echo sprintf("❌ Réservation #%d (%s) - Commande #%d (%s, %s):\n", 
            $reservation->id, 
            $reservation->status, 
            $reservation->order->id,
            $reservation->order->status,
            $reservation->order->payment_status
        );
        echo sprintf("  Restant: %s F CFA (ne doit pas être compté)\n\n", 
            number_format($remaining)
        );
    }
}

echo str_repeat("=", 60) . "\n";
echo "🎯 ANALYSE DU CALCUL DES REVENUS EN ATTENTE TERMINÉE\n";
echo str_repeat("=", 60) . "\n\n";
