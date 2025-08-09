<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== RESTAURATION DES DONNÉES DANS MySQL ===\n\n";

try {
    // Vérifier que nous sommes sur MySQL
    $currentConnection = config('database.default');
    echo "Connexion actuelle: $currentConnection\n";
    
    if ($currentConnection !== 'mysql') {
        die("❌ Vous devez être configuré sur MySQL pour cette restauration\n");
    }
    
    // Tester la connexion MySQL
    \DB::connection()->getPdo();
    echo "✓ Connexion MySQL OK\n\n";
    
    // Charger les données sauvegardées
    if (!file_exists('sqlite_backup.json')) {
        die("❌ Fichier sqlite_backup.json introuvable. Exécutez d'abord backup_sqlite.php\n");
    }
    
    $data = json_decode(file_get_contents('sqlite_backup.json'), true);
    
    echo "Restauration des données...\n";
    
    // Désactiver les contraintes de clés étrangères temporairement
    \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
    
    // Restaurer les centres
    foreach ($data['centers'] as $center) {
        \DB::table('centers')->insert((array)$center);
    }
    echo "✓ Centres restaurés: " . count($data['centers']) . "\n";
    
    // Restaurer les types de sang
    foreach ($data['blood_types'] as $bloodType) {
        \DB::table('blood_types')->insert((array)$bloodType);
    }
    echo "✓ Types de sang restaurés: " . count($data['blood_types']) . "\n";
    
    // Restaurer les utilisateurs
    foreach ($data['users'] as $user) {
        \DB::table('users')->insert((array)$user);
    }
    echo "✓ Utilisateurs restaurés: " . count($data['users']) . "\n";
    
    // Restaurer les commandes
    foreach ($data['orders'] as $order) {
        \DB::table('orders')->insert((array)$order);
    }
    echo "✓ Commandes restaurées: " . count($data['orders']) . "\n";
    
    // Réactiver les contraintes
    \DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    
    echo "\n✅ Migration terminée avec succès!\n";
    echo "Votre application utilise maintenant MySQL avec toutes vos données.\n";
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    echo "Vérifiez que MySQL est démarré et la base 'blood_bank' existe.\n";
}
