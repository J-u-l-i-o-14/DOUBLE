<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== CORRECTION DIRECTE EN SQL ===\n";

// Mettre à jour directement avec SQL pour être sûr
$orders = [
    1 => ['original' => 10000, 'deposit' => 5000],
    2 => ['original' => 5000, 'deposit' => 2500],
    3 => ['original' => 15000, 'deposit' => 7500],
    4 => ['original' => 5000, 'deposit' => 2500],
];

foreach ($orders as $id => $amounts) {
    $result = DB::table('orders')
        ->where('id', $id)
        ->update([
            'total_amount' => $amounts['deposit'], // Seulement l'acompte
            'deposit_amount' => $amounts['deposit'],
            'remaining_amount' => $amounts['original'] - $amounts['deposit'],
            'payment_status' => 'partial'
        ]);
    
    echo "Commande #{$id}: {$result} ligne(s) mise(s) à jour\n";
    echo "  total_amount fixé à: {$amounts['deposit']}\n";
    echo "  deposit_amount: {$amounts['deposit']}\n";
    echo "  remaining_amount: " . ($amounts['original'] - $amounts['deposit']) . "\n";
}

echo "\n=== VÉRIFICATION APRÈS CORRECTION ===\n";
$orders = DB::table('orders')->select('id', 'total_amount', 'original_price')->get();
foreach ($orders as $order) {
    $percentage = round(($order->total_amount / $order->original_price) * 100, 1);
    echo "Commande #{$order->id}: {$order->total_amount}/{$order->original_price} = {$percentage}%\n";
}
