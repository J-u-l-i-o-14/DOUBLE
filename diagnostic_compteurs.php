<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "╔══════════════════════════════════════════════════════════════════════════════╗\n";
echo "║                    🔍 DIAGNOSTIC COMPTEURS TABLEAUX                         ║\n";
echo "╚══════════════════════════════════════════════════════════════════════════════╝\n\n";

echo "📊 ANALYSE DES DIFFÉRENCES ENTRE PAGES:\n";
echo "=======================================\n\n";

// Simuler l'utilisateur admin du premier centre
$user = \App\Models\User::where('role', 'admin')->where('center_id', 1)->first();
if (!$user) {
    echo "❌ Aucun utilisateur admin trouvé avec center_id = 1\n";
    exit(1);
}

echo "👤 Utilisateur testé: {$user->name} (ID: {$user->id}, Centre: {$user->center_id})\n\n";

// ========================================
// 1. PAGE "GESTION DES POCHES DE SANG" 
// ========================================
echo "🔗 PAGE 1: GESTION DES POCHES DE SANG (/blood-bags)\n";
echo "=====================================================\n";

// Reproduction de la logique BloodBagController::index()
$pochesStats = [
    'total' => \App\Models\BloodBag::where('center_id', $user->center_id)->count(),
    'available' => \App\Models\BloodBag::where('center_id', $user->center_id)->where('status', 'available')->count(),
    'reserved' => \App\Models\BloodBag::where('center_id', $user->center_id)->where('status', 'reserved')->count(),
    'expired' => \App\Models\BloodBag::where('center_id', $user->center_id)->where('status', 'expired')->count(),
];

echo "📦 Statistiques des POCHES DE SANG (table blood_bags):\n";
echo "   📋 Total: {$pochesStats['total']}\n";
echo "   🟢 Disponibles: {$pochesStats['available']}\n";
echo "   🟡 Réservées: {$pochesStats['reserved']}\n";
echo "   🔴 Expirées: {$pochesStats['expired']}\n\n";

// ========================================
// 2. PAGE "GESTION DES STOCKS DE SANG"
// ========================================
echo "🔗 PAGE 2: GESTION DES STOCKS DE SANG (/blood-bags/stock)\n";
echo "=========================================================\n";

// Reproduction de la logique BloodBagController::stock()
$stockByCenter = \App\Models\Center::with(['inventory.bloodType'])
    ->where('id', $user->center_id)
    ->get();

echo "📊 Statistiques des STOCKS DE SANG (table center_blood_type_inventory):\n";

$stockStats = [
    'total_disponible' => 0,
    'total_reserve' => 0,
    'total_general' => 0
];

foreach ($stockByCenter as $center) {
    echo "🏥 Centre: {$center->name}\n";
    
    if ($center->inventory->count() > 0) {
        foreach ($center->inventory as $inventory) {
            $available = $inventory->available_quantity ?? 0;
            $reserved = $inventory->reserved_quantity ?? 0;
            $total = $available + $reserved;
            
            $stockStats['total_disponible'] += $available;
            $stockStats['total_reserve'] += $reserved;
            $stockStats['total_general'] += $total;
            
            echo "   🩸 {$inventory->bloodType->group}: {$available} disponibles + {$reserved} réservées = {$total} total\n";
        }
    } else {
        echo "   ❌ Aucun inventaire trouvé\n";
    }
}

echo "\n📈 RÉSUMÉ STOCKS:\n";
echo "   📋 Total général: {$stockStats['total_general']}\n";
echo "   🟢 Total disponible: {$stockStats['total_disponible']}\n";
echo "   🟡 Total réservé: {$stockStats['total_reserve']}\n\n";

// ========================================
// 3. COMPARAISON ET DIAGNOSTIC
// ========================================
echo "⚖️ COMPARAISON DES COMPTEURS:\n";
echo "=============================\n";

$differences = [];

// Comparaison Total
if ($pochesStats['total'] != $stockStats['total_general']) {
    $diff = abs($pochesStats['total'] - $stockStats['total_general']);
    $differences[] = "Total: Poches({$pochesStats['total']}) vs Stocks({$stockStats['total_general']}) = Différence de {$diff}";
}

// Comparaison Disponible
if ($pochesStats['available'] != $stockStats['total_disponible']) {
    $diff = abs($pochesStats['available'] - $stockStats['total_disponible']);
    $differences[] = "Disponible: Poches({$pochesStats['available']}) vs Stocks({$stockStats['total_disponible']}) = Différence de {$diff}";
}

// Comparaison Réservé
if ($pochesStats['reserved'] != $stockStats['total_reserve']) {
    $diff = abs($pochesStats['reserved'] - $stockStats['total_reserve']);
    $differences[] = "Réservé: Poches({$pochesStats['reserved']}) vs Stocks({$stockStats['total_reserve']}) = Différence de {$diff}";
}

if (empty($differences)) {
    echo "✅ AUCUNE DIFFÉRENCE DÉTECTÉE!\n";
    echo "Les compteurs sont cohérents entre les deux pages.\n\n";
} else {
    echo "🚨 DIFFÉRENCES DÉTECTÉES:\n";
    foreach ($differences as $diff) {
        echo "   ❌ {$diff}\n";
    }
    echo "\n";
}

// ========================================
// 4. DIAGNOSTIC APPROFONDI
// ========================================
echo "🔬 DIAGNOSTIC APPROFONDI:\n";
echo "=========================\n";

// Vérifier la synchronisation entre les deux tables
echo "🔄 Vérification de la synchronisation:\n\n";

// Compter les poches par statut et type sanguin
$pochesByTypeStatus = \App\Models\BloodBag::where('center_id', $user->center_id)
    ->join('blood_types', 'blood_bags.blood_type_id', '=', 'blood_types.id')
    ->selectRaw('blood_types.group, blood_bags.status, COUNT(*) as count')
    ->groupBy('blood_types.group', 'blood_bags.status')
    ->get()
    ->groupBy('group');

echo "📊 DÉTAIL PAR GROUPE SANGUIN:\n";
foreach ($pochesByTypeStatus as $group => $statuses) {
    echo "🩸 Groupe {$group}:\n";
    $total_available = 0;
    $total_reserved = 0;
    $total_all = 0;
    
    foreach ($statuses as $status) {
        echo "   - {$status->status}: {$status->count}\n";
        if ($status->status == 'available') $total_available = $status->count;
        if ($status->status == 'reserved') $total_reserved = $status->count;
        $total_all += $status->count;
    }
    
    // Comparer avec l'inventaire
    $inventory = \App\Models\CenterBloodTypeInventory::where('center_id', $user->center_id)
        ->whereHas('bloodType', function($q) use ($group) {
            $q->where('group', $group);
        })
        ->first();
    
    if ($inventory) {
        $inv_available = $inventory->available_quantity ?? 0;
        $inv_reserved = $inventory->reserved_quantity ?? 0;
        echo "   📦 Inventaire: {$inv_available} disponibles, {$inv_reserved} réservées\n";
        
        if ($total_available != $inv_available || $total_reserved != $inv_reserved) {
            echo "   🚨 DÉSYNCHRONISATION DÉTECTÉE!\n";
        } else {
            echo "   ✅ Synchronisé\n";
        }
    } else {
        echo "   ❌ Pas d'inventaire trouvé\n";
    }
    echo "\n";
}

// ========================================
// 5. RECOMMANDATIONS
// ========================================
echo "💡 RECOMMANDATIONS:\n";
echo "===================\n";

if (!empty($differences)) {
    echo "🔧 Actions à effectuer:\n";
    echo "1. Resynchroniser les inventaires avec les poches existantes\n";
    echo "2. Vérifier les triggers de mise à jour automatique\n";
    echo "3. Exécuter une maintenance des données\n\n";
    
    echo "🛠️ Script de correction suggéré:\n";
    echo "   php artisan tinker\n";
    echo "   \App\Models\CenterBloodTypeInventory::truncate();\n";
    echo "   // Puis relancer la synchronisation\n\n";
} else {
    echo "✅ Système cohérent, aucune action requise.\n\n";
}

echo "🎯 ANALYSE TERMINÉE\n";
echo "===================\n";
echo "Cette analyse permet de diagnostiquer les incohérences entre:\n";
echo "- Page des poches (données raw de la table blood_bags)\n";
echo "- Page des stocks (données agrégées de center_blood_type_inventory)\n\n";

echo "💬 Les deux pages affichent des données différentes car elles utilisent\n";
echo "    des sources de données différentes qui peuvent se désynchroniser.\n";

?>
