<?php
// Test du Sprint 4 - Gestion des ordonnances multiples et statuts

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

echo "=== Test Sprint 4: Gestion des ordonnances multiples ===\n";

try {
    $db = $app->make('db');
    
    // Test 1: Numéro d'ordonnance jamais utilisé
    echo "\n1. Test ordonnance nouvelle (jamais utilisée):\n";
    $newPrescription = 'ORD-NEW-' . time();
    $status1 = \App\Models\Order::checkPrescriptionStatus($newPrescription);
    echo "   Ordonnance: $newPrescription\n";
    echo "   Statut: {$status1['status']}\n";
    echo "   Message: {$status1['message']}\n";
    echo "   Peut ajouter: " . (\App\Models\Order::canAddNewOrder($newPrescription) ? "OUI" : "NON") . "\n";
    
    // Test 2: Créer une commande en cours et tester
    echo "\n2. Test ordonnance en cours:\n";
    $inProgressPrescription = 'ORD-PROGRESS-' . time();
    
    // Simuler la création d'une commande en cours
    $testOrder = $db->table('orders')->insertGetId([
        'user_id' => 1, // Supposant qu'il y a un utilisateur avec ID 1
        'center_id' => 1, // Supposant qu'il y a un centre avec ID 1
        'prescription_number' => $inProgressPrescription,
        'phone_number' => '0123456789',
        'prescription_images' => json_encode(['test-image.jpg']),
        'blood_type' => 'A+',
        'quantity' => 2,
        'unit_price' => 5000,
        'total_amount' => 10000,
        'deposit_amount' => 5000,
        'remaining_amount' => 5000,
        'payment_method' => 'tmoney',
        'payment_status' => 'partial',
        'status' => 'pending', // En cours
        'document_status' => 'pending',
        'notes' => 'Test Sprint 4',
        'created_at' => now(),
        'updated_at' => now()
    ]);
    
    echo "   Commande créée avec ID: $testOrder\n";
    
    $status2 = \App\Models\Order::checkPrescriptionStatus($inProgressPrescription);
    echo "   Ordonnance: $inProgressPrescription\n";
    echo "   Statut: {$status2['status']}\n";
    echo "   Message: {$status2['message']}\n";
    echo "   Commandes existantes: {$status2['existing_orders']}\n";
    echo "   Peut ajouter: " . (\App\Models\Order::canAddNewOrder($inProgressPrescription) ? "OUI" : "NON") . "\n";
    
    // Test 3: Marquer la commande comme terminée et tester
    echo "\n3. Test ordonnance terminée:\n";
    $db->table('orders')->where('id', $testOrder)->update(['status' => 'completed']);
    
    $status3 = \App\Models\Order::checkPrescriptionStatus($inProgressPrescription);
    echo "   Ordonnance: $inProgressPrescription (maintenant terminée)\n";
    echo "   Statut: {$status3['status']}\n";
    echo "   Message: {$status3['message']}\n";
    echo "   Commandes terminées: {$status3['completed_orders']}\n";
    echo "   Peut ajouter: " . (\App\Models\Order::canAddNewOrder($inProgressPrescription) ? "OUI" : "NON") . "\n";
    
    // Test 4: Vérifier les nouveaux statuts de document
    echo "\n4. Test des statuts de documents:\n";
    $order = \App\Models\Order::find($testOrder);
    if ($order) {
        echo "   Commande ID: {$order->id}\n";
        echo "   Statut: {$order->status} ({$order->status_label})\n";
        echo "   Document statut: {$order->document_status} ({$order->document_status_label})\n";
        echo "   Est en attente doc: " . ($order->isDocumentPending() ? "OUI" : "NON") . "\n";
        echo "   Est doc approuvé: " . ($order->isDocumentApproved() ? "OUI" : "NON") . "\n";
    }
    
    // Test 5: Tester la validation
    echo "\n5. Test de validation de documents:\n";
    if ($order) {
        $order->markAsConfirmed(1, 'Test de validation automatique');
        $order->refresh();
        echo "   Après validation:\n";
        echo "   Statut: {$order->status} ({$order->status_label})\n";
        echo "   Document statut: {$order->document_status} ({$order->document_status_label})\n";
        echo "   Validé par: {$order->validated_by}\n";
        echo "   Validé le: {$order->validated_at}\n";
        echo "   Notes: {$order->validation_notes}\n";
    }
    
    // Nettoyage: supprimer la commande de test
    echo "\n6. Nettoyage:\n";
    $db->table('orders')->where('id', $testOrder)->delete();
    echo "   Commande de test supprimée\n";
    
    echo "\n=== Tests Sprint 4 terminés avec succès! ===\n";
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    echo "Fichier: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
?>
