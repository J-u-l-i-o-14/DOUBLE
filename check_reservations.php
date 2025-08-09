<?php
echo "=== TEST MISE À JOUR STATUT ===\n";

try {
    $pdo = new PDO('sqlite:database/database.sqlite');
    
    // Changer le statut à 'completed'
    $pdo->exec("UPDATE reservation_requests SET status='completed', updated_at=datetime('now') WHERE id=1");
    echo "✅ Statut de la réservation #1 changé à 'completed'\n";
    
    // Vérifier le changement
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
    
    echo "\n🔄 Maintenant, rafraîchissez votre page de commande pour voir la progression mise à jour!\n";
    echo "La barre devrait maintenant être à 100% (verte) avec le message 'Commande terminée'\n";
    
} catch(Exception $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
}
?>
