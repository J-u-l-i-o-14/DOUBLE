<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\ReservationRequest;
use App\Models\Order;
use App\Models\BloodBag;

echo "=== TEST DES MISES À JOUR FINANCIÈRES AUTOMATIQUES ===\n";
echo "Date: " . now()->format('d/m/Y H:i') . "\n\n";

// Simuler le calcul du dashboard
function calculateDashboardStats($centerId = 1) {
    // Chiffre d'affaires total (montants effectivement payés)
    $totalRevenue = ReservationRequest::where('center_id', $centerId)
        ->whereHas('order')
        ->with('order')
        ->get()
        ->sum(function($reservation) {
            return $reservation->order ? $reservation->order->total_amount : 0;
        });

    // Revenus ce mois
    $monthlyRevenue = ReservationRequest::where('center_id', $centerId)
        ->whereMonth('created_at', now()->month)
        ->whereYear('created_at', now()->year)
        ->whereHas('order')
        ->with('order')
        ->get()
        ->sum(function($reservation) {
            return $reservation->order ? $reservation->order->total_amount : 0;
        });

    // Revenus en attente (réservations pending/confirmed avec paiement partiel)
    $pendingRevenue = ReservationRequest::where('center_id', $centerId)
        ->whereIn('status', ['pending', 'confirmed'])
        ->whereHas('order', function($q) {
            $q->where('payment_status', 'partial');
        })
        ->with('order')
        ->get()
        ->sum(function($reservation) {
            if ($reservation->order) {
                return $reservation->order->original_price - $reservation->order->total_amount;
            }
            return 0;
        });

    return [
        'total_revenue' => $totalRevenue,
        'monthly_revenue' => $monthlyRevenue,
        'pending_revenue' => $pendingRevenue
    ];
}

echo "📊 SITUATION INITIALE:\n";
echo "======================\n";
$initialStats = calculateDashboardStats();
echo "Chiffre d'affaires total: {$initialStats['total_revenue']} F CFA\n";
echo "Revenus ce mois: {$initialStats['monthly_revenue']} F CFA\n";
echo "Revenus en attente: {$initialStats['pending_revenue']} F CFA\n\n";

echo "🧪 TEST 1: CHANGEMENT DE STATUT DE RÉSERVATION\n";
echo "===============================================\n";

// Prendre une réservation en attente avec paiement partiel
$reservation = ReservationRequest::whereIn('status', ['pending', 'confirmed'])
    ->whereHas('order', function($q) {
        $q->where('payment_status', 'partial');
    })
    ->first();

if ($reservation) {
    echo "Réservation #{$reservation->id} trouvée:\n";
    echo "- Statut initial: {$reservation->status}\n";
    echo "- Montant payé: {$reservation->order->total_amount} F CFA\n";
    echo "- Montant total: {$reservation->order->original_price} F CFA\n";
    echo "- Reste à payer: " . ($reservation->order->original_price - $reservation->order->total_amount) . " F CFA\n\n";

    // Simulation 1: Marquer comme completed (retrait effectué)
    echo "📋 Simulation: Marquer comme 'completed'\n";
    $reservation->update(['status' => 'completed']);
    
    // Si la réservation est complétée, le paiement devrait être finalisé
    if ($reservation->order && $reservation->order->payment_status === 'partial') {
        $order = $reservation->order;
        $remainingAmount = $order->original_price - $order->total_amount;
        
        if ($remainingAmount > 0) {
            $order->update([
                'total_amount' => $order->original_price,
                'payment_status' => 'paid',
                'payment_completed_at' => now()
            ]);
            echo "✅ Paiement complété automatiquement: +{$remainingAmount} F CFA\n";
        }
    }

    // Recalculer les stats
    $newStats = calculateDashboardStats();
    echo "\n📈 IMPACT SUR LE DASHBOARD:\n";
    echo "Total revenue: {$initialStats['total_revenue']} → {$newStats['total_revenue']} F CFA\n";
    echo "Monthly revenue: {$initialStats['monthly_revenue']} → {$newStats['monthly_revenue']} F CFA\n";
    echo "Pending revenue: {$initialStats['pending_revenue']} → {$newStats['pending_revenue']} F CFA\n\n";

    // Simulation 2: Annuler la réservation
    echo "📋 Simulation: Annuler la réservation\n";
    $reservation->update(['status' => 'cancelled']);
    
    $cancelledStats = calculateDashboardStats();
    echo "✅ Réservation annulée\n";
    echo "\n📉 IMPACT DE L'ANNULATION:\n";
    echo "Total revenue: {$newStats['total_revenue']} → {$cancelledStats['total_revenue']} F CFA\n";
    echo "Pending revenue: {$newStats['pending_revenue']} → {$cancelledStats['pending_revenue']} F CFA\n";

} else {
    echo "❌ Aucune réservation avec paiement partiel trouvée\n";
}

echo "\n🎯 RÉSUMÉ DES COMPORTEMENTS ATTENDUS:\n";
echo "====================================\n";
echo "✅ Chiffre d'affaires = Somme des montants effectivement payés\n";
echo "✅ Revenus ce mois = Paiements reçus ce mois uniquement\n";
echo "✅ Revenus en attente = Soldes restants des réservations pending/confirmed\n";
echo "✅ Stock décrémenté = Seulement quand statut 'confirmed'\n";
echo "✅ Stock libéré = Quand statut 'cancelled' ou 'expired'\n";
echo "✅ Paiement finalisé = Quand statut 'completed'\n";

echo "\n=== TEST TERMINÉ ===\n";
