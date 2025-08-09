<?php

require_once 'vendor/autoload.php';

// Configuration de l'application Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Order;
use App\Models\Center;
use App\Models\ReservationRequest;

echo "=== ANALYSE DES PAIEMENTS ET RESERVATIONS PAR CENTRE ===\n\n";

// 1. Vérifier les commandes par centre
echo "📊 COMMANDES PAR CENTRE :\n";
echo "========================\n";

$orders = Order::with(['center', 'user'])->get();

$centerStats = [];
foreach ($orders as $order) {
    $centerId = $order->center_id;
    $centerName = $order->center ? $order->center->name : "Centre inconnu";
    
    if (!isset($centerStats[$centerId])) {
        $centerStats[$centerId] = [
            'name' => $centerName,
            'orders_count' => 0,
            'total_amount' => 0,
            'payment_statuses' => []
        ];
    }
    
    $centerStats[$centerId]['orders_count']++;
    $centerStats[$centerId]['total_amount'] += $order->total_amount;
    
    if (!isset($centerStats[$centerId]['payment_statuses'][$order->payment_status])) {
        $centerStats[$centerId]['payment_statuses'][$order->payment_status] = 0;
    }
    $centerStats[$centerId]['payment_statuses'][$order->payment_status]++;
    
    echo "  - Commande #{$order->id} | Centre: {$centerName} | Montant: {$order->total_amount} F CFA | Statut: {$order->payment_status}\n";
}

echo "\n📈 RÉSUMÉ PAR CENTRE :\n";
echo "=====================\n";
foreach ($centerStats as $centerId => $stats) {
    echo "🏥 {$stats['name']} (Centre {$centerId}) :\n";
    echo "   - Commandes: {$stats['orders_count']}\n";
    echo "   - Revenus: {$stats['total_amount']} F CFA\n";
    echo "   - Statuts de paiement:\n";
    foreach ($stats['payment_statuses'] as $status => $count) {
        echo "     * {$status}: {$count} commande(s)\n";
    }
    echo "\n";
}

// 2. Vérifier les réservations
echo "📋 RESERVATIONS :\n";
echo "=================\n";

$reservations = ReservationRequest::with(['center', 'user', 'order'])->get();

if ($reservations->count() == 0) {
    echo "❌ PROBLÈME: Aucune réservation trouvée !\n";
    echo "   Les commandes ne sont pas converties en réservations.\n\n";
    
    echo "🔧 CONVERSION DES COMMANDES EN RESERVATIONS:\n";
    echo "============================================\n";
    
    foreach ($orders as $order) {
        // Vérifier si cette commande a déjà une réservation
        $existingReservation = ReservationRequest::where('order_id', $order->id)->first();
        
        if (!$existingReservation) {
            echo "   - Conversion commande #{$order->id}...\n";
            try {
                $reservation = $order->createReservationRequest();
                echo "     ✅ Réservation #{$reservation->id} créée\n";
            } catch (Exception $e) {
                echo "     ❌ Erreur: " . $e->getMessage() . "\n";
            }
        } else {
            echo "   - Commande #{$order->id} déjà convertie (Réservation #{$existingReservation->id})\n";
        }
    }
} else {
    echo "✅ {$reservations->count()} réservations trouvées:\n";
    foreach ($reservations as $reservation) {
        $centerName = $reservation->center ? $reservation->center->name : "Centre inconnu";
        echo "  - Réservation #{$reservation->id} | Centre: {$centerName} | Statut: {$reservation->status}\n";
    }
}

// 3. Vérifier les paiements partiels
echo "\n💰 PAIEMENTS PARTIELS À COMPLÉTER :\n";
echo "===================================\n";

$partialPayments = Order::where('payment_status', 'partial')->get();

if ($partialPayments->count() == 0) {
    echo "✅ Aucun paiement partiel en attente\n";
} else {
    echo "📌 {$partialPayments->count()} paiement(s) partiel(s) trouvé(s):\n";
    foreach ($partialPayments as $order) {
        $remaining = $order->original_price - $order->total_amount;
        echo "  - Commande #{$order->id}: {$order->total_amount} F CFA payés / {$order->original_price} F CFA total\n";
        echo "    Reste à payer: {$remaining} F CFA\n";
    }
}

echo "\n=== RECOMMENDATIONS ===\n";
echo "1. Implémenter la conversion automatique des commandes en réservations\n";
echo "2. Ajouter la logique de complétion des paiements partiels lors du retrait\n";
echo "3. Mettre à jour les dashboards pour afficher les réservations par centre\n";
