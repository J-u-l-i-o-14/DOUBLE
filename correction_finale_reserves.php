<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 DIAGNOSTIC PRÉCIS DES POCHES RÉSERVÉES\n";
echo "==========================================\n\n";

$center1 = 1;

echo "📦 Détail des poches réservées pour centre {$center1}:\n";
$reserved = \App\Models\BloodBag::where('center_id', $center1)->where('status', 'reserved')->get();
foreach($reserved as $bag) {
    echo "   Poche ID: {$bag->id}, Type: {$bag->bloodType->group}, Statut: {$bag->status}\n";
}
echo "   Total réservées dans blood_bags: " . $reserved->count() . "\n\n";

echo "📊 Détail inventaire pour centre {$center1}:\n";
$inventory = \App\Models\CenterBloodTypeInventory::where('center_id', $center1)->get();
$totalReservedInv = 0;
foreach($inventory as $inv) {
    if($inv->reserved_quantity > 0) {
        echo "   Type: {$inv->bloodType->group}, Réservées dans inventaire: {$inv->reserved_quantity}\n";
        $totalReservedInv += $inv->reserved_quantity;
    }
}
echo "   Total réservées dans inventaire: {$totalReservedInv}\n\n";

// Correction finale
if($reserved->count() != $totalReservedInv) {
    echo "🔧 CORRECTION FINALE NÉCESSAIRE\n";
    echo "===============================\n";
    
    // Remettre à zéro toutes les quantités réservées dans l'inventaire
    \App\Models\CenterBloodTypeInventory::where('center_id', $center1)->update(['reserved_quantity' => 0]);
    
    // Recalculer à partir des vraies données
    foreach($reserved as $bag) {
        $inventory = \App\Models\CenterBloodTypeInventory::where('center_id', $center1)
            ->where('blood_type_id', $bag->blood_type_id)
            ->first();
        
        if($inventory) {
            $inventory->increment('reserved_quantity');
            echo "   ✅ Incrémenté {$bag->bloodType->group} dans l'inventaire\n";
        }
    }
    
    echo "\n🎉 CORRECTION FINALE TERMINÉE!\n";
    
    // Vérification finale
    $newTotalReserved = \App\Models\CenterBloodTypeInventory::where('center_id', $center1)->sum('reserved_quantity');
    echo "   📊 Nouveau total réservées dans inventaire: {$newTotalReserved}\n";
    echo "   📦 Total réservées dans blood_bags: " . $reserved->count() . "\n";
    
    if($reserved->count() == $newTotalReserved) {
        echo "   ✅ PARFAITEMENT SYNCHRONISÉ!\n";
    } else {
        echo "   ❌ Encore une différence: " . abs($reserved->count() - $newTotalReserved) . "\n";
    }
} else {
    echo "✅ Déjà synchronisé!\n";
}

?>
