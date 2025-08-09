<?php

require_once 'vendor/autoload.php';

// Configuration de l'application Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Order;

echo "=== CORRECTION DES MONTANTS D'ACOMPTE ===\n\n";

$orders = Order::all();

foreach ($orders as $order) {
    echo "🛒 Correction de la commande #{$order->id}\n";
    echo "   Prix original: " . number_format($order->original_price, 0) . " F CFA\n";
    
    // Calculer l'acompte de 50%
    $depositAmount = $order->original_price * 0.5;
    $remainingAmount = $order->original_price - $depositAmount;
    
    // Mettre à jour avec seulement l'acompte de 50%
    $order->update([
        'total_amount' => $depositAmount, // SEULEMENT l'acompte
        'deposit_amount' => $depositAmount,
        'remaining_amount' => $remainingAmount,
        'payment_status' => 'partial' // Statut partiel
    ]);
    
    echo "   ✅ Acompte (50%): " . number_format($depositAmount, 0) . " F CFA\n";
    echo "   ✅ Reste à payer: " . number_format($remainingAmount, 0) . " F CFA\n";
    echo "   ✅ Statut: {$order->payment_status}\n";
    echo "\n";
}

echo "=== VÉRIFICATION DES POURCENTAGES ===\n";
foreach (Order::all() as $order) {
    $percentage = $order->getPaymentPercentage();
    echo "Commande #{$order->id}: {$percentage}% payé\n";
}
