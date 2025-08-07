<?php
// Script de diagnostic complet Sprint 4

echo "=== Diagnostic Blood Bank System - Sprint 4 ===\n";

try {
    // 1. Test de connexion MySQL
    echo "\n1. TEST CONNEXION MYSQL\n";
    echo "========================\n";
    
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=blood_bank', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Connexion MySQL réussie\n";
    
    // 2. Vérifier les tables existantes
    echo "\n2. TABLES EXISTANTES\n";
    echo "====================\n";
    
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Nombre de tables : " . count($tables) . "\n";
    foreach ($tables as $table) {
        echo "- $table\n";
    }
    
    // 3. Vérifier spécifiquement la table orders pour Sprint 4
    echo "\n3. TABLE ORDERS - SPRINT 4\n";
    echo "==========================\n";
    
    if (in_array('orders', $tables)) {
        echo "✅ Table 'orders' existe\n";
        
        $columns = $pdo->query("DESCRIBE orders")->fetchAll();
        echo "\nColonnes existantes :\n";
        
        $sprint4Fields = ['document_status', 'validated_by', 'validated_at', 'validation_notes'];
        $existingColumns = [];
        
        foreach ($columns as $column) {
            $existingColumns[] = $column['Field'];
            echo "- {$column['Field']} ({$column['Type']})\n";
        }
        
        echo "\nVérification des champs Sprint 4 :\n";
        foreach ($sprint4Fields as $field) {
            if (in_array($field, $existingColumns)) {
                echo "✅ $field : EXISTE\n";
            } else {
                echo "❌ $field : MANQUANT\n";
            }
        }
        
    } else {
        echo "❌ Table 'orders' : MANQUANTE\n";
    }
    
    // 4. Vérifier les données de test
    echo "\n4. DONNÉES DE TEST\n";
    echo "==================\n";
    
    if (in_array('orders', $tables)) {
        $orderCount = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
        echo "Nombre de commandes : $orderCount\n";
        
        if ($orderCount > 0) {
            $sampleOrder = $pdo->query("SELECT id, prescription_number, status, document_status FROM orders LIMIT 1")->fetch();
            echo "Exemple de commande :\n";
            echo "- ID: {$sampleOrder['id']}\n";
            echo "- Ordonnance: {$sampleOrder['prescription_number']}\n";
            echo "- Status: {$sampleOrder['status']}\n";
            echo "- Document Status: " . ($sampleOrder['document_status'] ?? 'NULL') . "\n";
        }
    }
    
    if (in_array('users', $tables)) {
        $userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $managerCount = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'manager'")->fetchColumn();
        echo "Nombre d'utilisateurs : $userCount\n";
        echo "Nombre de gestionnaires : $managerCount\n";
    }
    
    if (in_array('notifications', $tables)) {
        $notifCount = $pdo->query("SELECT COUNT(*) FROM notifications")->fetchColumn();
        echo "Nombre de notifications : $notifCount\n";
    }
    
    // 5. État des migrations
    echo "\n5. MIGRATIONS\n";
    echo "=============\n";
    
    if (in_array('migrations', $tables)) {
        $migrations = $pdo->query("SELECT migration FROM migrations ORDER BY id")->fetchAll();
        echo "Migrations exécutées (" . count($migrations) . ") :\n";
        foreach ($migrations as $migration) {
            echo "- {$migration['migration']}\n";
        }
        
        // Vérifier si notre migration Sprint 4 est présente
        $sprint4Migration = 'add_sprint4_fields_to_orders_table';
        $found = false;
        foreach ($migrations as $migration) {
            if (strpos($migration['migration'], $sprint4Migration) !== false) {
                $found = true;
                break;
            }
        }
        echo "\nMigration Sprint 4: " . ($found ? "✅ EXÉCUTÉE" : "❌ MANQUANTE") . "\n";
        
    } else {
        echo "❌ Table 'migrations' introuvable\n";
    }
    
    echo "\n6. RECOMMANDATIONS\n";
    echo "==================\n";
    
    if (count($tables) == 0) {
        echo "📋 Base de données vide\n";
        echo "   1. Exécuter : php artisan migrate:fresh\n";
        echo "   2. Puis : php artisan db:seed (optionnel)\n";
    } elseif (!in_array('orders', $tables)) {
        echo "📋 Tables manquantes\n";
        echo "   Exécuter : php artisan migrate:fresh\n";
    } else {
        // Vérifier les champs Sprint 4
        $columns = $pdo->query("DESCRIBE orders")->fetchAll();
        $existingColumns = array_column($columns, 'Field');
        $sprint4Fields = ['document_status', 'validated_by', 'validated_at', 'validation_notes'];
        $missingFields = array_diff($sprint4Fields, $existingColumns);
        
        if (!empty($missingFields)) {
            echo "📋 Champs Sprint 4 manquants : " . implode(', ', $missingFields) . "\n";
            echo "   Exécuter : php artisan migrate\n";
        } else {
            echo "✅ Système prêt pour le Sprint 4\n";
            echo "   Tester : Créer une commande et valider les documents\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Erreur : " . $e->getMessage() . "\n";
}

echo "\n=== Fin du diagnostic ===\n";
?>
