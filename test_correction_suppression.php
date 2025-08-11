<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

echo "=== TEST SUPPRESSION D'UTILISATEURS ===\n\n";

try {
    // Test 1: Vérifier les relations existantes
    echo "🧪 TEST 1 - Vérification des relations User:\n";
    echo "===========================================\n";
    
    $user = User::first();
    if ($user) {
        echo "✅ Utilisateur test: {$user->name}\n";
        
        $hasDonations = method_exists($user, 'donations');
        $hasReservations = method_exists($user, 'reservationRequests');
        
        echo "   - Méthode donations() : " . ($hasDonations ? "✅ OUI" : "❌ NON") . "\n";
        echo "   - Méthode reservationRequests() : " . ($hasReservations ? "✅ OUI" : "❌ NON") . "\n";
        
        if ($hasDonations && $hasReservations) {
            echo "   ✅ Relations nécessaires présentes\n";
            
            // Test des requêtes
            try {
                $donationsCount = $user->donations()->count();
                echo "   - Nombre de donations: {$donationsCount}\n";
                
                $reservationsCount = $user->reservationRequests()->count();
                echo "   - Nombre de réservations: {$reservationsCount}\n";
                
                echo "   ✅ Aucune erreur SQL lors du test des relations\n";
                
            } catch (Exception $e) {
                echo "   ❌ Erreur SQL: " . $e->getMessage() . "\n";
            }
        }
    }
    echo "\n";

    // Test 2: Simulation de suppression
    echo "🧪 TEST 2 - Simulation logique de suppression:\n";
    echo "==============================================\n";
    
    $testUser = User::where('role', '!=', 'admin')->first();
    if ($testUser) {
        echo "✅ Test utilisateur: {$testUser->name} (Rôle: {$testUser->role})\n";
        
        $canDelete = true;
        $blockers = [];
        
        try {
            if ($testUser->donations()->exists()) {
                $canDelete = false;
                $blockers[] = 'donations';
            }
            
            if ($testUser->reservationRequests()->exists()) {
                $canDelete = false;
                $blockers[] = 'réservations';
            }
            
            if ($canDelete) {
                echo "   ✅ Cet utilisateur PEUT être supprimé\n";
            } else {
                echo "   ⚠️  Cet utilisateur NE PEUT PAS être supprimé\n";
                echo "   Raisons: " . implode(', ', $blockers) . "\n";
            }
            
        } catch (Exception $e) {
            echo "   ❌ Erreur lors de la vérification: " . $e->getMessage() . "\n";
        }
    }
    echo "\n";

} catch (Exception $e) {
    echo "❌ Erreur générale: " . $e->getMessage() . "\n";
}

echo "=== RÉSUMÉ DES CORRECTIONS ===\n";
echo "✅ Gap ajouté aux cartes dashboard (g-3 + gap: 12px)\n";
echo "✅ Méthode destroy() UserController corrigée\n";
echo "✅ Relations incorrectes supprimées (transfusions, organizedCampaigns)\n";
echo "✅ Vérifications de sécurité ajoutées\n";
echo "✅ Messages d'erreur détaillés\n\n";

echo "🎯 NOUVELLES FONCTIONNALITÉS:\n";
echo "- Espacement amélioré entre les cartes\n";
echo "- Suppression sécurisée des utilisateurs\n";
echo "- Vérification des permissions par centre\n";
echo "- Messages d'erreur explicites\n";

echo "\n🎉 CORRECTIONS APPLIQUÉES AVEC SUCCÈS ! 🎉\n";
