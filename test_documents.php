<?php

require_once 'vendor/autoload.php';

// Initialiser Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Order;

echo "=== Test des images de documents ===\n";

$orders = Order::whereNotNull('prescription_image')
    ->orWhereNotNull('patient_id_image')
    ->orWhereNotNull('medical_certificate')
    ->get();

echo "Commandes avec documents: " . $orders->count() . "\n\n";

foreach ($orders as $order) {
    echo "=== Commande #{$order->id} ===\n";
    if ($order->prescription_image) {
        echo "Prescription Image: {$order->prescription_image}\n";
        $path = storage_path('app/public/' . $order->prescription_image);
        echo "File exists: " . (file_exists($path) ? 'YES' : 'NO') . "\n";
        echo "Full path: {$path}\n";
    }
    if ($order->patient_id_image) {
        echo "Patient ID Image: {$order->patient_id_image}\n";
        $path = storage_path('app/public/' . $order->patient_id_image);
        echo "File exists: " . (file_exists($path) ? 'YES' : 'NO') . "\n";
    }
    if ($order->medical_certificate) {
        echo "Medical Certificate: {$order->medical_certificate}\n";
        $path = storage_path('app/public/' . $order->medical_certificate);
        echo "File exists: " . (file_exists($path) ? 'YES' : 'NO') . "\n";
    }
    echo "\n";
}

// Vérifier aussi toutes les commandes pour voir si certaines ont des chemins vides ou null
echo "=== Analyse de toutes les commandes ===\n";
$allOrders = Order::all();
foreach ($allOrders as $order) {
    $hasAnyDoc = $order->prescription_image || $order->patient_id_image || $order->medical_certificate;
    if (!$hasAnyDoc) {
        echo "Commande #{$order->id}: Aucun document\n";
    }
}
