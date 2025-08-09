<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Order;

echo "=== VÉRIFICATION DES DONNÉES EN BASE ===\n";

$orders = Order::all();
foreach ($orders as $order) {
    echo "Commande #{$order->id}:\n";
    echo "  total_amount (BD): {$order->total_amount}\n";
    echo "  original_price (BD): {$order->original_price}\n";
    echo "  Calcul %: " . $order->getPaymentPercentage() . "%\n";
    echo "  Attributes: " . json_encode($order->getAttributes()) . "\n";
    echo "---\n";
}
