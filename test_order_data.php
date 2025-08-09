<?php

require_once 'vendor/autoload.php';

// Initialiser Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Order;
use App\Models\User;

// Test pour vérifier les données des commandes
$user = User::where('role', 'client')->first();
if (!$user) {
    echo "Aucun client trouvé\n";
    return;
}

echo "=== Test des données de commande ===\n";
echo "Client: {$user->name} (ID: {$user->id})\n\n";

$orders = Order::where('user_id', $user->id)
    ->with(['reservationRequest.items.bloodType', 'center'])
    ->get();

echo "Nombre de commandes: " . $orders->count() . "\n\n";

foreach ($orders as $order) {
    echo "=== Commande #{$order->id} ===\n";
    echo "Original Price: " . ($order->original_price ?? 'NULL') . "\n";
    echo "Total Amount: " . ($order->total_amount ?? 'NULL') . "\n";
    echo "Blood Type: " . ($order->blood_type ?? 'NULL') . "\n";
    echo "Quantity: " . ($order->quantity ?? 'NULL') . "\n";
    echo "Doctor Name: " . ($order->doctor_name ?? 'NULL') . "\n";
    echo "Prescription Number: " . ($order->prescription_number ?? 'NULL') . "\n";
    echo "Payment Status: " . ($order->payment_status ?? 'NULL') . "\n";
    echo "Payment Method: " . ($order->payment_method ?? 'NULL') . "\n";
    
    // Documents
    echo "Prescription Image: " . ($order->prescription_image ? 'YES' : 'NO') . "\n";
    echo "Patient ID Image: " . ($order->patient_id_image ? 'YES' : 'NO') . "\n";
    echo "Medical Certificate: " . ($order->medical_certificate ? 'YES' : 'NO') . "\n";
    
    // Relation avec ReservationRequest
    if ($order->reservationRequest) {
        echo "Reservation Request ID: {$order->reservationRequest->id}\n";
        echo "Items count: " . $order->reservationRequest->items->count() . "\n";
        foreach ($order->reservationRequest->items as $item) {
            echo "  - {$item->bloodType->group}: {$item->quantity}\n";
        }
    } else {
        echo "Pas de ReservationRequest associée\n";
    }
    
    echo "\n";
}
