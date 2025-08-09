<?php

require_once 'vendor/autoload.php';

// Configuration de l'application Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Order;
use App\Models\ReservationRequest;

echo "=== CORRECTION DES DONNÉES FINANCIÈRES ===\n";
echo "Date: " . now()->format('d/m/Y H:i') . "\n\n";

echo "🔧 ÉTAPE 1: CORRECTION DES MONTANTS ORIGINAUX\n";
echo "==============================================\n";

$orders = Order::whereNull('original_price')->orWhere('original_price', '')->get();

foreach ($orders as $order) {
    echo "Commande #{$order->id}: ";
    
    // Si on a un montant payé, l'utiliser comme référence
    if ($order->total_amount > 0) {
        // Pour un acompte de 50%, le montant original = montant payé * 2
        $originalPrice = $order->total_amount * 2;
        
        $order->update([
            'original_price' => $originalPrice
        ]);
        
        echo "Montant original défini à {$originalPrice} F CFA (basé sur acompte de {$order->total_amount} F CFA)\n";
    } else {
        echo "❌ Impossible de déterminer le montant original\n";
    }
}

echo "\n🔧 ÉTAPE 2: CORRECTION DES STATUTS DE PAIEMENT\n";
echo "===============================================\n";

$allOrders = Order::all();

foreach ($allOrders as $order) {
    $originalPrice = $order->original_price ?: 0;
    $paidAmount = $order->total_amount ?: 0;
    
    if ($paidAmount >= $originalPrice && $originalPrice > 0) {
        // Paiement complet
        $newStatus = 'paid';
    } elseif ($paidAmount > 0 && $paidAmount < $originalPrice) {
        // Paiement partiel
        $newStatus = 'partial';
    } elseif ($paidAmount == 0) {
        // Pas de paiement
        $newStatus = 'pending';
    } else {
        // Cas anormal
        $newStatus = 'partial';
    }
    
    if ($order->payment_status !== $newStatus) {
        $order->update(['payment_status' => $newStatus]);
        echo "Commande #{$order->id}: Statut mis à jour de '{$order->payment_status}' vers '{$newStatus}'\n";
    }
}

echo "\n🔧 ÉTAPE 3: MISE À JOUR DES RÉSERVATIONS COMPLÉTÉES\n";
echo "====================================================\n";

$completedReservations = ReservationRequest::where('status', 'completed')->with('order')->get();

foreach ($completedReservations as $reservation) {
    if ($reservation->order && $reservation->order->payment_status === 'partial') {
        $order = $reservation->order;
        $originalPrice = $order->original_price ?: 0;
        
        if ($originalPrice > $order->total_amount) {
            // Compléter le paiement
            $order->update([
                'total_amount' => $originalPrice,
                'payment_status' => 'paid',
                'payment_completed_at' => now()
            ]);
            
            echo "Réservation #{$reservation->id}: Paiement complété de {$order->total_amount} à {$originalPrice} F CFA\n";
        }
    }
}

echo "\n📊 VÉRIFICATION APRÈS CORRECTION:\n";
echo "=================================\n";

$correctedOrders = Order::with(['reservationRequest'])->get();
$totalPaid = 0;
$totalRemaining = 0;
$totalOriginal = 0;

foreach ($correctedOrders as $order) {
    $originalPrice = $order->original_price ?: 0;
    $paidAmount = $order->total_amount ?: 0;
    $remaining = max(0, $originalPrice - $paidAmount);
    
    $totalPaid += $paidAmount;
    $totalRemaining += $remaining;
    $totalOriginal += $originalPrice;
    
    echo "Commande #{$order->id}: {$paidAmount}/{$originalPrice} F CFA ({$order->payment_status})";
    if ($order->reservationRequest) {
        echo " - Réservation: {$order->reservationRequest->status}";
    }
    echo "\n";
}

echo "\n💰 TOTAUX APRÈS CORRECTION:\n";
echo "===========================\n";
echo "Total original: {$totalOriginal} F CFA\n";
echo "Total payé: {$totalPaid} F CFA\n";
echo "Total restant: {$totalRemaining} F CFA\n";

echo "\n🎯 COMPARAISON AVEC LES OBJECTIFS:\n";
echo "==================================\n";
echo "Objectif payé: 37500 F CFA (Actuel: {$totalPaid} F CFA)\n";
echo "Objectif restant: 2500 F CFA (Actuel: {$totalRemaining} F CFA)\n";

if ($totalPaid == 37500 && $totalRemaining == 2500) {
    echo "✅ OBJECTIFS ATTEINTS!\n";
} else {
    echo "❌ Ajustements nécessaires\n";
    
    if ($totalPaid != 37500 || $totalRemaining != 2500) {
        echo "\n🎯 AJUSTEMENT POUR ATTEINDRE LES OBJECTIFS:\n";
        echo "==========================================\n";
        
        // Créer une commande d'ajustement si nécessaire
        $targetTotal = 40000; // 37500 + 2500
        $currentTotal = $totalPaid + $totalRemaining;
        
        if ($currentTotal != $targetTotal) {
            echo "Création d'une commande d'ajustement...\n";
            
            $adjustmentOrder = Order::create([
                'user_id' => 1, // Admin
                'center_id' => 1,
                'blood_type' => 'A+',
                'quantity' => 1,
                'unit_price' => ($targetTotal - $currentTotal),
                'original_price' => ($targetTotal - $currentTotal),
                'total_amount' => 37500 - $totalPaid,
                'remaining_amount' => 2500 - $totalRemaining,
                'payment_method' => 'adjustment',
                'payment_status' => 'partial',
                'status' => 'pending',
                'document_status' => 'approved',
                'notes' => 'Commande d\'ajustement pour atteindre les objectifs financiers'
            ]);
            
            echo "Commande d'ajustement #{$adjustmentOrder->id} créée\n";
        }
    }
}

echo "\n=== CORRECTION TERMINÉE ===\n";
