<?php
// Script pour exécuter les migrations Laravel

echo "=== Exécution des migrations Laravel ===\n";

try {
    // Charger Laravel
    require_once __DIR__ . '/vendor/autoload.php';
    $app = require_once __DIR__ . '/bootstrap/app.php';
    
    echo "✅ Laravel chargé\n";
    
    // Vérifier la connexion à la base de données
    $db = $app->make('db');
    $pdo = $db->connection()->getPdo();
    echo "✅ Connexion à la base de données OK\n";
    
    // Vérifier les fichiers de migration
    $migrationPath = __DIR__ . '/database/migrations';
    $migrations = glob($migrationPath . '/*.php');
    
    echo "\nFichiers de migration trouvés (" . count($migrations) . ") :\n";
    foreach ($migrations as $migration) {
        echo "- " . basename($migration) . "\n";
    }
    
    // Exécuter les migrations via Artisan
    echo "\n=== Exécution des migrations ===\n";
    
    // Utiliser la commande Artisan programmatiquement
    $artisan = $app->make('Illuminate\Contracts\Console\Kernel');
    
    // Exécuter migrate:fresh
    echo "Exécution de migrate:fresh...\n";
    $exitCode = $artisan->call('migrate:fresh', ['--force' => true]);
    
    if ($exitCode === 0) {
        echo "✅ Migrations exécutées avec succès !\n";
        
        // Afficher le output
        $output = $artisan->output();
        echo "\nOutput:\n$output\n";
        
        // Vérifier les tables créées
        $tables = $db->select("SHOW TABLES");
        echo "\nTables créées (" . count($tables) . ") :\n";
        foreach ($tables as $table) {
            $tableName = array_values((array)$table)[0];
            echo "- $tableName\n";
        }
        
    } else {
        echo "❌ Erreur lors de l'exécution des migrations\n";
        echo "Output:\n" . $artisan->output() . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur : " . $e->getMessage() . "\n";
    echo "Trace : " . $e->getTraceAsString() . "\n";
}

echo "\n=== Fin des migrations ===\n";
?>
