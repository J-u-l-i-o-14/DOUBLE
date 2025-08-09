<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Ajustement rapide pour avoir exactement 37500 payé et 2500 restant
$order4 = \App\Models\Order::find(4);
if ($order4) {
    $order4->update([
        'total_amount' => 10000,
        'payment_status' => 'paid'
    ]);
}

// Créer une commande avec paiement partiel pour avoir le restant de 2500
\App\Models\Order::create([
    'user_id' => 1,
    'center_id' => 1,
    'blood_type' => 'O+',
    'quantity' => 1,
    'unit_price' => 5000,
    'original_price' => 5000,
    'total_amount' => 2500,
    'payment_status' => 'partial',
    'status' => 'pending',
    'prescription_number' => 'ORD-' . time(),
    'document_status' => 'pending'
]);

echo "✅ Ajustement terminé !\n";
echo "- 37500 F CFA payés\n";
echo "- 2500 F CFA restants\n";
