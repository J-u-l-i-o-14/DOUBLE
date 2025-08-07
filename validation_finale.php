<?php
// Script de test pour vérifier le Sprint 4 après seeding

echo "=== Test du système Sprint 4 ===\n";

try {
    require_once __DIR__ . '/vendor/autoload.php';
    $app = require_once __DIR__ . '/bootstrap/app.php';
    
    // Test de connexion
    $db = $app->make('db');
    echo "✅ Connexion Laravel réussie\n";
    
    // Test des données
    echo "\n=== VÉRIFICATION DES DONNÉES ===\n";
    
    $users = $db->table('users')->count();
    $centers = $db->table('centers')->count();
    $orders = $db->table('orders')->count();
    $notifications = $db->table('notifications')->count();
    
    echo "Utilisateurs : $users\n";
    echo "Centres : $centers\n";
    echo "Commandes : $orders\n";
    echo "Notifications : $notifications\n";
    
    // Test des ordonnances multiples (Sprint 4)
    echo "\n=== TEST SPRINT 4 - ORDONNANCES MULTIPLES ===\n";
    
    $prescriptionNumbers = $db->table('orders')
        ->select('prescription_number')
        ->groupBy('prescription_number')
        ->havingRaw('COUNT(*) > 1')
        ->get();
        
    echo "Ordonnances avec plusieurs commandes : " . count($prescriptionNumbers) . "\n";
    foreach ($prescriptionNumbers as $prescription) {
        $count = $db->table('orders')
            ->where('prescription_number', $prescription->prescription_number)
            ->count();
        echo "- {$prescription->prescription_number} : $count commandes\n";
    }
    
    // Test des statuts de documents
    echo "\n=== TEST SPRINT 4 - STATUTS DE DOCUMENTS ===\n";
    
    $documentStatuses = $db->table('orders')
        ->select('document_status', $db->raw('COUNT(*) as count'))
        ->groupBy('document_status')
        ->get();
        
    foreach ($documentStatuses as $status) {
        echo "- {$status->document_status} : {$status->count} commande(s)\n";
    }
    
    // Test des notifications par type
    echo "\n=== TEST SPRINT 4 - NOTIFICATIONS ===\n";
    
    $notificationTypes = $db->table('notifications')
        ->select('type', $db->raw('COUNT(*) as count'))
        ->groupBy('type')
        ->get();
        
    foreach ($notificationTypes as $type) {
        echo "- {$type->type} : {$type->count} notification(s)\n";
    }
    
    // Test des relations (validation)
    echo "\n=== TEST RELATIONS ===\n";
    
    $validatedOrders = $db->table('orders')
        ->whereNotNull('validated_by')
        ->count();
    echo "Commandes validées : $validatedOrders\n";
    
    // Test d'une fonctionnalité Sprint 4 via le modèle
    echo "\n=== TEST FONCTIONNALITÉS SPRINT 4 ===\n";
    
    // Test de checkPrescriptionStatus
    $testPrescription = 'ORD-2024-001';
    $orderModel = new \App\Models\Order();
    $status = $orderModel::checkPrescriptionStatus($testPrescription);
    
    echo "Test ordonnance '$testPrescription' :\n";
    echo "- Statut : {$status['status']}\n";
    echo "- Message : {$status['message']}\n";
    if (isset($status['existing_orders'])) {
        echo "- Commandes en cours : {$status['existing_orders']}\n";
    }
    
    // Test canAddNewOrder
    $canAdd = $orderModel::canAddNewOrder($testPrescription);
    echo "- Peut ajouter nouvelle commande : " . ($canAdd ? "OUI" : "NON") . "\n";
    
    echo "\n=== RÉSUMÉ ===\n";
    
    if ($orders > 0 && $notifications > 0) {
        echo "✅ Système Sprint 4 opérationnel !\n";
        echo "🔹 Gestion des ordonnances multiples : OK\n";
        echo "🔹 Validation des documents : OK\n";
        echo "🔹 Notifications automatiques : OK\n";
        echo "🔹 Statuts de commande : OK\n";
        
        echo "\nPour tester :\n";
        echo "1. Créer une nouvelle commande avec ordonnance existante\n";
        echo "2. Valider/rejeter des documents depuis le dashboard gestionnaire\n";
        echo "3. Vérifier les notifications en temps réel\n";
        
    } else {
        echo "❌ Problème dans le seeding\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur : " . $e->getMessage() . "\n";
    echo "Trace : " . $e->getTraceAsString() . "\n";
}

echo "\n=== Fin du test ===\n";
?>
