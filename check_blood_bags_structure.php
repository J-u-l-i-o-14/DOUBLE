<?php
try {
    $pdo = new PDO('sqlite:database/database.sqlite');
    echo "=== STRUCTURE TABLE BLOOD_BAGS ===\n";
    $stmt = $pdo->query('PRAGMA table_info(blood_bags)');
    while($row = $stmt->fetch()) {
        $null = $row['notnull'] == 1 ? ' NOT NULL' : '';
        echo "- {$row['name']} ({$row['type']})$null\n";
    }
} catch(Exception $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
}
?>
