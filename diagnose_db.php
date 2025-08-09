<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== DIAGNOSTIC CONFIGURATION BASE DE DONNÉES ===\n\n";

// Vérifier les variables d'environnement
echo "Variables d'environnement :\n";
echo "DB_CONNECTION: " . env('DB_CONNECTION', 'non défini') . "\n";
echo "DB_HOST: " . env('DB_HOST', 'non défini') . "\n";
echo "DB_PORT: " . env('DB_PORT', 'non défini') . "\n";
echo "DB_DATABASE: " . env('DB_DATABASE', 'non défini') . "\n";
echo "DB_USERNAME: " . env('DB_USERNAME', 'non défini') . "\n\n";

// Vérifier la configuration actuelle
echo "Configuration Laravel :\n";
echo "default: " . config('database.default') . "\n";
echo "mysql host: " . config('database.connections.mysql.host') . "\n";
echo "mysql database: " . config('database.connections.mysql.database') . "\n\n";

// Tester la connexion MySQL
echo "Test de connexion MySQL :\n";
try {
    $mysql = config('database.connections.mysql');
    $pdo = new PDO(
        "mysql:host={$mysql['host']};port={$mysql['port']};",
        $mysql['username'],
        $mysql['password']
    );
    echo "✓ Connexion au serveur MySQL réussie\n";
    
    // Vérifier si la base existe
    $databases = $pdo->query("SHOW DATABASES")->fetchAll(PDO::FETCH_COLUMN);
    if (in_array($mysql['database'], $databases)) {
        echo "✓ Base de données '{$mysql['database']}' existe\n";
    } else {
        echo "❌ Base de données '{$mysql['database']}' n'existe pas\n";
        echo "Bases disponibles: " . implode(', ', $databases) . "\n";
    }
} catch (Exception $e) {
    echo "❌ Erreur de connexion MySQL: " . $e->getMessage() . "\n";
    echo "Vérifiez que MySQL est démarré (XAMPP/WAMP/etc.)\n";
}

echo "\nTest de connexion SQLite :\n";
try {
    $sqlite = config('database.connections.sqlite');
    if (file_exists($sqlite['database'])) {
        echo "✓ Fichier SQLite existe: " . $sqlite['database'] . "\n";
    } else {
        echo "❌ Fichier SQLite introuvable\n";
    }
} catch (Exception $e) {
    echo "❌ Erreur SQLite: " . $e->getMessage() . "\n";
}
