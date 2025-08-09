<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Structure de la table orders pour les champs téléphone:\n";
$columns = \DB::select('DESCRIBE orders');
foreach ($columns as $column) {
    if (strpos($column->Field, 'phone') !== false) {
        echo "- {$column->Field} ({$column->Type})\n";
    }
}

echo "\nVérification des dernières commandes:\n";
$order = \App\Models\Order::latest()->first();
if ($order) {
    echo "Commande #{$order->id}:\n";
    echo "  phone_number: " . ($order->phone_number ?? 'NULL') . "\n";
    if (isset($order->phone)) {
        echo "  phone: " . ($order->phone ?? 'NULL') . "\n";
    }
}
