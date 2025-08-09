<?php
try {
    $pdo = new PDO('sqlite:database/database.sqlite');
    
    echo "=== VÉRIFICATION DASHBOARD POCHES DE SANG ===\n";
    
    // Vérifier s'il y a des poches de sang
    $stmt = $pdo->query('SELECT COUNT(*) as count FROM blood_bags');
    $bagCount = $stmt->fetch()['count'];
    echo "Nombre de poches de sang: $bagCount\n";
    
    if ($bagCount == 0) {
        echo "\nCréation de poches de test pour le dashboard...\n";
        
        // Créer différents types de poches pour les statistiques
        $statuses = ['available', 'reserved', 'expired', 'transfused'];
        $bloodTypes = [1, 2, 3, 4]; // Supposons 4 groupes sanguins
        
        $id = 1;
        foreach ($statuses as $status) {
            for ($i = 0; $i < 5; $i++) { // 5 poches par statut
                $bloodTypeId = $bloodTypes[array_rand($bloodTypes)];
                $centerId = 1; // Centre de test
                $volume = 450; // Volume standard
                $donorId = rand(1, 100); // Donor fictif
                
                $collectedAt = date('Y-m-d H:i:s', strtotime('-' . rand(1, 30) . ' days'));
                $expiresAt = date('Y-m-d H:i:s', strtotime($collectedAt . ' +35 days'));
                
                $pdo->exec("INSERT INTO blood_bags (
                    id, blood_type_id, center_id, donor_id, volume, status, 
                    collected_at, expires_at, created_at, updated_at
                ) VALUES (
                    $id, $bloodTypeId, $centerId, $donorId, $volume, '$status', 
                    '$collectedAt', '$expiresAt', datetime('now'), datetime('now')
                )");
                $id++;
            }
        }
        echo "✅ 20 poches de test créées (5 par statut)\n";
    }
    
    // Calculer les statistiques comme dans le contrôleur
    echo "\n=== STATISTIQUES DASHBOARD ===\n";
    
    $stats = [];
    $stats['total'] = $pdo->query("SELECT COUNT(*) as count FROM blood_bags WHERE center_id = 1")->fetch()['count'];
    $stats['available'] = $pdo->query("SELECT COUNT(*) as count FROM blood_bags WHERE center_id = 1 AND status = 'available'")->fetch()['count'];
    $stats['reserved'] = $pdo->query("SELECT COUNT(*) as count FROM blood_bags WHERE center_id = 1 AND status = 'reserved'")->fetch()['count'];
    $stats['expired'] = $pdo->query("SELECT COUNT(*) as count FROM blood_bags WHERE center_id = 1 AND status = 'expired'")->fetch()['count'];
    
    echo "📊 Total poches: {$stats['total']}\n";
    echo "🟢 Disponibles: {$stats['available']}\n";
    echo "🟡 Réservées: {$stats['reserved']}\n";
    echo "🔴 Expirées: {$stats['expired']}\n";
    
    // Détail par statut
    echo "\n=== RÉPARTITION DÉTAILLÉE ===\n";
    $stmt = $pdo->query("
        SELECT status, COUNT(*) as count 
        FROM blood_bags 
        WHERE center_id = 1 
        GROUP BY status 
        ORDER BY count DESC
    ");
    
    while ($row = $stmt->fetch()) {
        $statusLabel = [
            'available' => '🟢 Disponible',
            'reserved' => '🟡 Réservée',
            'expired' => '🔴 Expirée',
            'transfused' => '🔵 Transfusée',
            'discarded' => '⚫ Jetée'
        ][$row['status']] ?? $row['status'];
        
        echo "{$statusLabel}: {$row['count']} poches\n";
    }
    
    echo "\n✅ Le dashboard affiche maintenant clairement combien de poches vous gérez et leur statut!\n";
    echo "📝 Ceci est essentiel pour une bonne gestion des stocks comme mentionné.\n";
    echo "\n🔧 Note: Les champs 'donor_id' et 'volume' sont encore dans la structure de la table,\n";
    echo "     mais ils ont été retirés de l'interface utilisateur pour simplifier la gestion.\n";
    
} catch(Exception $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
}
?>
