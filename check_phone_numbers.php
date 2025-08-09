<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Vérification des numéros de téléphone dans les commandes:\n";
$orders = \App\Models\Order::with('user')->latest()->limit(5)->get();
foreach ($orders as $order) {
    echo "Commande #{$order->id}:\n";
    echo "  - Client: {$order->user->name}\n";
    echo "  - Téléphone commande: " . ($order->phone ?? 'NULL') . "\n";
    echo "  - Téléphone utilisateur: " . ($order->user->phone ?? 'NULL') . "\n\n";
}
