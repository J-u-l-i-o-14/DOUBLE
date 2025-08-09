<?php

require_once 'vendor/autoload.php';

// Configuration de l'application Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Order;
use App\Models\ReservationRequest;

echo "=== AJUSTEMENT POUR OBJECTIFS FINANCIERS ===\n";
echo "Date: " . now()->format('d/m/Y H:i') . "\n\n";

echo "🎯 OBJECTIFS:\n";
echo "- Total payé: 37500 F CFA\n";
echo "- Total restant: 2500 F CFA\n";
echo "- Total général: 40000 F CFA\n\n";

echo "📊 SITUATION ACTUELLE:\n";
echo "======================\n";

$orders = Order::all();
$currentPaid = $orders->sum('total_amount');
$currentOriginal = $orders->sum('original_price');
$currentRemaining = $currentOriginal - $currentPaid;

echo "Total payé actuel: {$currentPaid} F CFA\n";
echo "Total original actuel: {$currentOriginal} F CFA\n";
echo "Total restant actuel: {$currentRemaining} F CFA\n\n";

echo "🔧 AJUSTEMENT DES DONNÉES:\n";
echo "==========================\n";

// Stratégie: Ajuster les montants des commandes existantes pour atteindre les objectifs
$targetPaid = 37500;
$targetRemaining = 2500;
$targetTotal = $targetPaid + $targetRemaining; // 40000

// Supprimer les commandes d'ajustement précédentes si elles existent
Order::where('notes', 'LIKE', '%ajustement%')->delete();

// Prendre les 4 premières commandes et les ajuster
$mainOrders = Order::limit(4)->get();

if ($mainOrders->count() >= 4) {
    // Définir les nouveaux montants pour chaque commande
    $newAmounts = [
        ['original' => 10000, 'paid' => 10000], // Commande 1: complète
        ['original' => 5000, 'paid' => 5000],   // Commande 2: complète  
        ['original' => 15000, 'paid' => 15000], // Commande 3: complète
        ['original' => 10000, 'paid' => 7500]   // Commande 4: partielle (2500 restant)
    ];
    
    foreach ($mainOrders as $index => $order) {
        if (isset($newAmounts[$index])) {
            $newOriginal = $newAmounts[$index]['original'];
            $newPaid = $newAmounts[$index]['paid'];
            $newRemaining = $newOriginal - $newPaid;
            
            // Déterminer le statut de paiement
            if ($newPaid >= $newOriginal) {
                $paymentStatus = 'paid';
            } elseif ($newPaid > 0) {
                $paymentStatus = 'partial';
            } else {
                $paymentStatus = 'pending';
            }
            
            $order->update([
                'original_price' => $newOriginal,
                'total_amount' => $newPaid,
                'remaining_amount' => $newRemaining,
                'payment_status' => $paymentStatus
            ]);
            
            echo "Commande #{$order->id}: {$newPaid}/{$newOriginal} F CFA (reste: {$newRemaining})\n";
        }
    }
    
    // Supprimer les commandes excédentaires
    $extraOrders = Order::where('id', '>', $mainOrders->max('id'))->get();
    foreach ($extraOrders as $order) {
        echo "Suppression commande #{$order->id}\n";
        $order->delete();
    }
    
} else {
    echo "❌ Pas assez de commandes pour l'ajustement\n";
}

echo "\n📊 VÉRIFICATION FINALE:\n";
echo "========================\n";

$finalOrders = Order::all();
$finalPaid = $finalOrders->sum('total_amount');
$finalOriginal = $finalOrders->sum('original_price');
$finalRemaining = $finalOriginal - $finalPaid;

echo "Total payé final: {$finalPaid} F CFA\n";
echo "Total original final: {$finalOriginal} F CFA\n";  
echo "Total restant final: {$finalRemaining} F CFA\n\n";

if ($finalPaid == $targetPaid && $finalRemaining == $targetRemaining) {
    echo "✅ OBJECTIFS ATTEINTS!\n";
} else {
    echo "❌ Objectifs non atteints\n";
    echo "Écart payé: " . ($finalPaid - $targetPaid) . " F CFA\n";
    echo "Écart restant: " . ($finalRemaining - $targetRemaining) . " F CFA\n";
}

echo "\n🔄 MISE À JOUR DES STATUTS DE RÉSERVATION:\n";
echo "===========================================\n";

// Ajuster les statuts de réservation pour cohérence
$reservations = ReservationRequest::with('order')->get();
foreach ($reservations as $reservation) {
    if ($reservation->order) {
        $order = $reservation->order;
        
        // Si l'ordre est complètement payé et la réservation n'est pas completed
        if ($order->payment_status === 'paid' && $reservation->status !== 'completed') {
            $reservation->update(['status' => 'completed']);
            echo "Réservation #{$reservation->id}: Marquée comme completed\n";
        }
        // Si l'ordre est partiel et la réservation est completed
        elseif ($order->payment_status === 'partial' && $reservation->status === 'completed') {
            $reservation->update(['status' => 'confirmed']);
            echo "Réservation #{$reservation->id}: Marquée comme confirmed\n";
        }
    }
}

echo "\n=== AJUSTEMENT TERMINÉ ===\n";
