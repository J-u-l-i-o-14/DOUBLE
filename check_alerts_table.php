<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== VÉRIFICATION DE LA STRUCTURE DE LA TABLE ALERTS ===\n\n";

try {
    // Vérifier les colonnes de la table alerts
    $columns = \Illuminate\Support\Facades\Schema::getColumnListing('alerts');
    
    echo "📊 Colonnes trouvées dans la table 'alerts':\n";
    foreach ($columns as $column) {
        echo "   - {$column}\n";
    }
    
    echo "\n🔍 ANALYSE DES COLONNES:\n";
    
    // Vérifier les colonnes importantes
    $expectedColumns = [
        'id' => 'Identifiant',
        'type' => 'Type d\'alerte',
        'message' => 'Message',
        'center_id' => 'Centre associé',
        'blood_type_id' => 'Type sanguin',
        'is_resolved' => 'Statut résolu',
        'resolved_at' => 'Date de résolution',
        'resolved_by' => 'Résolu par',
        'created_at' => 'Date de création',
        'updated_at' => 'Date de mise à jour'
    ];
    
    foreach ($expectedColumns as $column => $description) {
        $exists = in_array($column, $columns);
        $status = $exists ? '✅' : '❌';
        echo "   {$status} {$column}: {$description}\n";
    }
    
    // Si is_resolved n'existe pas, proposer une solution
    if (!in_array('is_resolved', $columns)) {
        echo "\n⚠️ PROBLÈME DÉTECTÉ:\n";
        echo "La colonne 'is_resolved' n'existe pas dans la table alerts.\n";
        echo "Cela explique l'erreur SQL dans les requêtes.\n\n";
        
        echo "💡 SOLUTIONS POSSIBLES:\n";
        echo "1. Ajouter la colonne manquante avec une migration\n";
        echo "2. Utiliser une colonne existante pour le statut\n";
        echo "3. Modifier le modèle Alert pour utiliser une autre logique\n\n";
        
        // Vérifier s'il y a une colonne similaire
        $statusColumns = array_filter($columns, function($col) {
            return str_contains(strtolower($col), 'status') || str_contains(strtolower($col), 'resolved');
        });
        
        if (!empty($statusColumns)) {
            echo "📋 Colonnes de statut trouvées:\n";
            foreach ($statusColumns as $col) {
                echo "   - {$col}\n";
            }
        }
    }
    
} catch (Exception $e) {
    echo "❌ Erreur lors de la vérification: " . $e->getMessage() . "\n";
}
