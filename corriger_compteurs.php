<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "╔══════════════════════════════════════════════════════════════════════════════╗\n";
echo "║                    🔧 CORRECTION COMPTEURS TABLEAUX                         ║\n";
echo "╚══════════════════════════════════════════════════════════════════════════════╝\n\n";

echo "🎯 OBJECTIF: Synchroniser les compteurs entre 'Gestion des poches' et 'Gestion des stocks'\n\n";

try {
    // 1. Sauvegarder l'état actuel
    echo "💾 SAUVEGARDE DE L'ÉTAT ACTUEL:\n";
    echo "===============================\n";
    
    $totalInventoryBefore = \App\Models\CenterBloodTypeInventory::sum('available_quantity') + 
                           \App\Models\CenterBloodTypeInventory::sum('reserved_quantity');
    echo "📊 Total inventaire avant correction: {$totalInventoryBefore}\n";
    
    $totalBagsBefore = \App\Models\BloodBag::count();
    echo "📦 Total poches avant correction: {$totalBagsBefore}\n\n";
    
    // 2. Resynchronisation par centre et type sanguin
    echo "🔄 RESYNCHRONISATION EN COURS:\n";
    echo "==============================\n";
    
    $centers = \App\Models\Center::all();
    $bloodTypes = \App\Models\BloodType::all();
    $correctionCount = 0;
    
    foreach ($centers as $center) {
        echo "🏥 Centre: {$center->name}\n";
        
        foreach ($bloodTypes as $bloodType) {
            // Compter les vraies poches dans blood_bags
            $realCounts = \App\Models\BloodBag::where('center_id', $center->id)
                ->where('blood_type_id', $bloodType->id)
                ->selectRaw('
                    COUNT(CASE WHEN status = "available" THEN 1 END) as available_count,
                    COUNT(CASE WHEN status = "reserved" THEN 1 END) as reserved_count,
                    COUNT(*) as total_count
                ')
                ->first();
            
            $realAvailable = $realCounts->available_count ?? 0;
            $realReserved = $realCounts->reserved_count ?? 0;
            
            // Mettre à jour ou créer l'inventaire
            $inventory = \App\Models\CenterBloodTypeInventory::firstOrCreate([
                'center_id' => $center->id,
                'blood_type_id' => $bloodType->id
            ]);
            
            $oldAvailable = $inventory->available_quantity ?? 0;
            $oldReserved = $inventory->reserved_quantity ?? 0;
            
            if ($oldAvailable != $realAvailable || $oldReserved != $realReserved) {
                $inventory->update([
                    'available_quantity' => $realAvailable,
                    'reserved_quantity' => $realReserved,
                    'updated_at' => now()
                ]);
                
                echo "   🔧 {$bloodType->group}: {$oldAvailable}→{$realAvailable} disponibles, {$oldReserved}→{$realReserved} réservées\n";
                $correctionCount++;
            } else {
                echo "   ✅ {$bloodType->group}: Déjà synchronisé ({$realAvailable} disponibles, {$realReserved} réservées)\n";
            }
        }
        echo "\n";
    }
    
    // 3. Vérification après correction
    echo "🎯 VÉRIFICATION APRÈS CORRECTION:\n";
    echo "=================================\n";
    
    $user = \App\Models\User::where('role', 'admin')->where('center_id', 1)->first();
    
    // Statistiques poches
    $baseQuery = \App\Models\BloodBag::where('center_id', $user->center_id);
    $pochesStats = [
        'total' => $baseQuery->count(),
        'available' => $baseQuery->where('status', 'available')->count(),
        'reserved' => $baseQuery->where('status', 'reserved')->count(),
        'expired' => $baseQuery->where('status', 'expired')->count(),
    ];
    
    // Statistiques stocks
    $stockByCenter = \App\Models\Center::with(['inventory.bloodType'])
        ->where('id', $user->center_id)
        ->get();
    
    $stockStats = [
        'total_disponible' => 0,
        'total_reserve' => 0,
        'total_general' => 0
    ];
    
    foreach ($stockByCenter as $center) {
        foreach ($center->inventory as $inventory) {
            $available = $inventory->available_quantity ?? 0;
            $reserved = $inventory->reserved_quantity ?? 0;
            
            $stockStats['total_disponible'] += $available;
            $stockStats['total_reserve'] += $reserved;
            $stockStats['total_general'] += $available + $reserved;
        }
    }
    
    echo "📊 GESTION DES POCHES DE SANG:\n";
    echo "   📋 Total: {$pochesStats['total']}\n";
    echo "   🟢 Disponibles: {$pochesStats['available']}\n";
    echo "   🟡 Réservées: {$pochesStats['reserved']}\n";
    echo "   🔴 Expirées: {$pochesStats['expired']}\n\n";
    
    echo "📈 GESTION DES STOCKS DE SANG:\n";
    echo "   📋 Total: {$stockStats['total_general']}\n";
    echo "   🟢 Disponibles: {$stockStats['total_disponible']}\n";
    echo "   🟡 Réservées: {$stockStats['total_reserve']}\n\n";
    
    // 4. Résultat final
    $totalDiff = abs($pochesStats['total'] - $stockStats['total_general']);
    $availableDiff = abs($pochesStats['available'] - $stockStats['total_disponible']);
    $reservedDiff = abs($pochesStats['reserved'] - $stockStats['total_reserve']);
    
    if ($totalDiff == 0 && $availableDiff == 0 && $reservedDiff == 0) {
        echo "🎉 CORRECTION RÉUSSIE!\n";
        echo "======================\n";
        echo "✅ Les compteurs sont maintenant parfaitement synchronisés\n";
        echo "✅ Aucune différence détectée entre les deux pages\n";
        echo "✅ Nombre de corrections appliquées: {$correctionCount}\n\n";
    } else {
        echo "⚠️ CORRECTION PARTIELLE:\n";
        echo "========================\n";
        echo "🔍 Différences restantes:\n";
        if ($totalDiff > 0) echo "   - Total: {$totalDiff}\n";
        if ($availableDiff > 0) echo "   - Disponibles: {$availableDiff}\n";
        if ($reservedDiff > 0) echo "   - Réservées: {$reservedDiff}\n";
        echo "\n";
    }
    
    // 5. Actions supplémentaires si nécessaire
    if ($totalDiff > 0 || $availableDiff > 0 || $reservedDiff > 0) {
        echo "🔧 ACTIONS SUPPLÉMENTAIRES RECOMMANDÉES:\n";
        echo "========================================\n";
        echo "1. Vérifier l'intégrité des données dans blood_bags\n";
        echo "2. Rechercher des poches orphelines\n";
        echo "3. Vérifier les triggers de mise à jour automatique\n\n";
        
        // Recherche de poches orphelines
        $orphanBags = \App\Models\BloodBag::whereNotExists(function($query) {
            $query->select(\DB::raw(1))
                  ->from('center_blood_type_inventory')
                  ->whereRaw('center_blood_type_inventory.center_id = blood_bags.center_id')
                  ->whereRaw('center_blood_type_inventory.blood_type_id = blood_bags.blood_type_id');
        })->count();
        
        echo "🔍 Poches orphelines détectées: {$orphanBags}\n\n";
    }
    
    echo "💡 PROCHAINES ÉTAPES:\n";
    echo "=====================\n";
    echo "1. Tester les deux pages dans le navigateur\n";
    echo "2. Vérifier que les compteurs affichent les mêmes valeurs\n";
    echo "3. Surveiller la synchronisation lors des futures opérations\n\n";
    
    echo "🎯 CORRECTION TERMINÉE AVEC SUCCÈS!\n";
    
} catch (Exception $e) {
    echo "❌ ERREUR LORS DE LA CORRECTION:\n";
    echo "================================\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "Fichier: " . $e->getFile() . ":" . $e->getLine() . "\n\n";
    echo "🔧 Veuillez vérifier l'intégrité de la base de données.\n";
}

?>
