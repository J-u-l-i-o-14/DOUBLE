<?php

require_once 'vendor/autoload.php';

// Configurer Laravel
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== TEST GÉNÉRATION D'ALERTES POUR MAIN.BLADE.PHP ===\n";
echo "======================================================\n";

// Trouver un utilisateur admin
$admin = \App\Models\User::where('role', 'admin')->first();

if (!$admin) {
    echo "❌ Aucun utilisateur admin trouvé\n";
    exit;
}

echo "👤 Utilisateur: {$admin->name}\n";
echo "🏥 Centre: {$admin->center_id}\n\n";

// Vérifier les alertes existantes
$existingAlerts = \App\Models\Alert::where('center_id', $admin->center_id)->where('resolved', false)->count();
echo "📊 Alertes actives existantes: {$existingAlerts}\n\n";

if ($existingAlerts === 0) {
    echo "🔧 Génération d'alertes de test...\n";
    echo "===================================\n";
    
    // Créer quelques alertes de test
    $bloodTypes = \App\Models\BloodType::all();
    $alertsCreated = 0;
    
    foreach ($bloodTypes as $bloodType) {
        // Créer une alerte de stock faible
        $alert = \App\Models\Alert::create([
            'center_id' => $admin->center_id,
            'blood_type_id' => $bloodType->id,
            'type' => 'low_stock',
            'message' => "Stock critique pour le groupe sanguin {$bloodType->group} - Seulement 2 unités disponibles",
            'resolved' => false,
            'created_at' => now(),
        ]);
        
        if ($alert) {
            echo "✅ Alerte créée: {$bloodType->group} - Stock faible\n";
            $alertsCreated++;
        }
        
        // Limiter à 5 alertes pour le test
        if ($alertsCreated >= 5) break;
    }
    
    echo "\n📊 {$alertsCreated} alertes de test créées\n\n";
}

// Vérifier les alertes après création
$currentAlerts = \App\Models\Alert::where('center_id', $admin->center_id)->where('resolved', false)->get();
echo "🔔 ALERTES ACTIVES DÉTAILLÉES:\n";
echo "==============================\n";

foreach ($currentAlerts as $alert) {
    $bloodType = $alert->bloodType ? $alert->bloodType->group : 'N/A';
    echo "⚠️ Alerte #{$alert->id}:\n";
    echo "   Type: {$alert->type}\n";
    echo "   Groupe sanguin: {$bloodType}\n";
    echo "   Message: {$alert->message}\n";
    echo "   Date: {$alert->created_at->format('d/m/Y H:i')}\n";
    echo "   Résolue: " . ($alert->resolved ? 'Oui' : 'Non') . "\n\n";
}

echo "🎯 VÉRIFICATION AFFICHAGE MAIN.BLADE.PHP:\n";
echo "==========================================\n";

$totalAlerts = $currentAlerts->count();
echo "✅ Total alertes actives: {$totalAlerts}\n";

if ($totalAlerts > 0) {
    echo "✅ Le bouton rouge '{$totalAlerts} Alerte(s)' devrait apparaître\n";
    echo "✅ La cloche devrait être rouge et animée\n";
    echo "✅ Le badge devrait afficher le nombre total\n";
    echo "✅ Cliquer sur 'Gérer les alertes' → Page de gestion\n";
} else {
    echo "ℹ️ Aucune alerte → Interface normale sans bouton rouge\n";
}

echo "\n🔗 LIENS DISPONIBLES:\n";
echo "======================\n";
echo "🔴 Bouton rouge d'alerte: /alerts?layout=main\n";
echo "🔔 Cloche → Modal → 'Gérer les alertes': /alerts?layout=main\n";
echo "📱 Accès direct: http://localhost/alerts?layout=main\n\n";

echo "🧪 TEST DE NAVIGATION:\n";
echo "======================\n";
echo "1. Ouvrez votre navigateur\n";
echo "2. Allez sur une page utilisant main.blade.php\n";
echo "3. Vous devriez voir:\n";
echo "   - Un bouton rouge '{$totalAlerts} Alerte(s)' si {$totalAlerts} > 0\n";
echo "   - Une cloche avec badge rouge\n";
echo "   - Cliquer dessus → Page de gestion des alertes\n\n";

if ($totalAlerts > 0) {
    echo "🚨 ATTENTION: {$totalAlerts} alertes actives détectées !\n";
    echo "Cliquez sur le bouton rouge ou la cloche pour les gérer.\n\n";
}

echo "💡 POUR SUPPRIMER LES ALERTES DE TEST:\n";
echo "=======================================\n";
echo "Vous pouvez:\n";
echo "1. Les résoudre via l'interface web\n";
echo "2. Ou les supprimer via: \App\Models\Alert::where('center_id', {$admin->center_id})->delete();\n";
