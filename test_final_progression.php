<?php
try {
    $pdo = new PDO('sqlite:database/database.sqlite');
    
    // Mettre en statut completed
    $pdo->exec("UPDATE reservation_requests SET status='completed', updated_at=datetime('now') WHERE id=1");
    echo "✅ Réservation #1 mise en statut 'completed'\n";
    
    // Vérifier
    $stmt = $pdo->query("SELECT status, updated_at FROM reservation_requests WHERE id=1");
    $row = $stmt->fetch();
    echo "Statut actuel: {$row['status']} | MAJ: {$row['updated_at']}\n";
    
    echo "\n🎯 MAINTENANT TESTEZ :\n";
    echo "1. Allez sur http://localhost/orders/1 (page détails commande)\n";
    echo "2. La barre de progression devrait être à 100% (verte)\n";
    echo "3. Le message devrait être: '🎉 Commande terminée - Sang récupéré'\n";
    echo "4. Allez sur http://localhost/orders (liste des commandes)\n";
    echo "5. Les statistiques devraient refléter le nouveau statut\n";
    
} catch(Exception $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
}
?>
