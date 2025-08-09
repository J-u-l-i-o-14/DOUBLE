<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== MIGRATION SQLite vers MySQL ===\n\n";

// Sauvegarder les données SQLite
echo "1. Sauvegarde des données SQLite...\n";

$data = [];

try {
    // Vérifier la connexion actuelle
    $currentConnection = config('database.default');
    echo "Connexion actuelle: $currentConnection\n";
    
    if ($currentConnection === 'sqlite') {
        // Sauvegarder les données importantes
        $data['users'] = \DB::table('users')->get()->toArray();
        $data['centers'] = \DB::table('centers')->get()->toArray();
        $data['orders'] = \DB::table('orders')->get()->toArray();
        $data['blood_types'] = \DB::table('blood_types')->get()->toArray();
        
        echo "✓ Utilisateurs: " . count($data['users']) . "\n";
        echo "✓ Centres: " . count($data['centers']) . "\n";
        echo "✓ Commandes: " . count($data['orders']) . "\n";
        echo "✓ Types de sang: " . count($data['blood_types']) . "\n";
        
        // Sauvegarder dans un fichier JSON
        file_put_contents('sqlite_backup.json', json_encode($data, JSON_PRETTY_PRINT));
        echo "✓ Données sauvegardées dans sqlite_backup.json\n\n";
        
    } else {
        echo "Vous utilisez déjà MySQL!\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

echo "PROCHAINES ÉTAPES:\n";
echo "1. Installez XAMPP et démarrez MySQL\n";
echo "2. Créez la base 'blood_bank' via phpMyAdmin\n";
echo "3. Exécutez: php artisan migrate:fresh\n";
echo "4. Puis: php restore_mysql_data.php\n";
