<?php

require_once 'vendor/autoload.php';

// Initialiser Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Order;

echo "=== Ajout de données de test pour les documents ===\n";

// Mettre à jour la première commande avec des chemins de documents de test
$order = Order::first();
if ($order) {
    $order->update([
        'prescription_image' => 'documents/test_prescription.jpg',
        'patient_id_image' => 'documents/test_id.jpg', 
        'medical_certificate' => 'documents/test_certificate.jpg'
    ]);
    
    echo "Commande #{$order->id} mise à jour avec des documents de test\n";
    echo "Prescription: {$order->prescription_image}\n";
    echo "ID Patient: {$order->patient_id_image}\n";
    echo "Certificat: {$order->medical_certificate}\n";
} else {
    echo "Aucune commande trouvée\n";
}
