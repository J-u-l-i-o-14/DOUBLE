<?php
try {
    $pdo = new PDO('sqlite:database/database.sqlite');
    
    echo "=== VÉRIFICATION WORKFLOW COMPLET ===\n";
    
    // Tester avec plusieurs statuts pour voir le problème
    $statuses = ['pending', 'confirmed', 'completed', 'cancelled'];
    
    foreach($statuses as $status) {
        echo "\n--- Test avec statut: $status ---\n";
        
        // Mettre à jour la réservation de test
        $pdo->exec("UPDATE reservation_requests SET status='$status', updated_at=datetime('now') WHERE id=1");
        
        // Vérifier la relation
        $stmt = $pdo->query("
            SELECT o.id as order_id, o.status as order_status, 
                   r.id as reservation_id, r.status as reservation_status,
                   r.updated_at as reservation_updated
            FROM orders o 
            LEFT JOIN reservation_requests r ON r.order_id = o.id 
            WHERE o.id = 1
        ");
        
        $row = $stmt->fetch();
        echo "Commande #{$row['order_id']} (status: {$row['order_status']}) → ";
        echo "Réservation #{$row['reservation_id']} (status: {$row['reservation_status']}) | ";
        echo "MAJ: {$row['reservation_updated']}\n";
        
        // Calculer la progression attendue
        $progression = match($status) {
            'pending' => '25% (jaune)',
            'confirmed' => '75% (bleu)', 
            'completed' => '100% (vert)',
            'cancelled' => '25% (rouge)',
            default => 'Inconnu'
        };
        echo "→ Progression attendue: $progression\n";
    }
    
    echo "\n=== VÉRIFICATION DU DASHBOARD CLIENT ===\n";
    echo "Si le dashboard client ne tient pas compte des changements de statut,\n";
    echo "c'est probablement parce que la page n'utilise pas encore la nouvelle logique.\n";
    
} catch(Exception $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
}
?>
