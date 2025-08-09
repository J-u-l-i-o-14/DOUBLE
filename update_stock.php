<?php

require_once 'vendor/autoload.php';

// Initialiser Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Center;
use App\Models\BloodType;
use App\Models\BloodBag;
use App\Models\CenterBloodTypeInventory;
use Carbon\Carbon;

echo "🩸 AUGMENTATION DU STOCK DE SANG\n";
echo "================================\n\n";

// Configuration
$targetStock = 20; // Stock cible par type sanguin par centre

// Récupérer tous les centres et types sanguins
$centers = Center::all();
$bloodTypes = BloodType::all();

echo "📊 Configuration:\n";
echo "   - Centres disponibles: {$centers->count()}\n";
echo "   - Types sanguins: {$bloodTypes->count()}\n";
echo "   - Stock cible par type/centre: {$targetStock} poches\n\n";

$totalBagsCreated = 0;
$totalCentersUpdated = 0;

foreach ($centers as $center) {
    echo "📍 CENTRE: {$center->name}\n";
    echo str_repeat("-", 50) . "\n";
    
    $centerBagsAdded = 0;
    
    foreach ($bloodTypes as $bloodType) {
        // Vérifier le stock actuel disponible
        $currentAvailableStock = BloodBag::where('center_id', $center->id)
            ->where('blood_type_id', $bloodType->id)
            ->where('status', 'available')
            ->count();

        echo "   🩸 {$bloodType->group}: Stock actuel = {$currentAvailableStock}";

        // Calculer combien de poches ajouter
        $bagsToAdd = max(0, $targetStock - $currentAvailableStock);

        if ($bagsToAdd > 0) {
            // Créer les nouvelles poches de sang
            for ($i = 1; $i <= $bagsToAdd; $i++) {
                BloodBag::create([
                    'center_id' => $center->id,
                    'blood_type_id' => $bloodType->id,
                    'collected_at' => Carbon::now()->subDays(rand(1, 30)),
                    'expires_at' => Carbon::now()->addDays(42),
                    'status' => 'available',
                    'volume' => 450,
                    'donor_id' => null,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
                
                // Petite pause pour éviter les conflits
                usleep(1000); // 1ms
            }

            // Mettre à jour l'inventaire du centre
            $inventory = CenterBloodTypeInventory::firstOrCreate([
                'center_id' => $center->id,
                'blood_type_id' => $bloodType->id
            ]);

            $inventory->update([
                'available_quantity' => $targetStock,
                'reserved_quantity' => $inventory->reserved_quantity ?? 0,
            ]);

            $totalBagsCreated += $bagsToAdd;
            $centerBagsAdded += $bagsToAdd;
            
            echo " → +{$bagsToAdd} poches ajoutées ✅\n";
        } else {
            echo " → Stock suffisant ✅\n";
        }
    }
    
    if ($centerBagsAdded > 0) {
        $totalCentersUpdated++;
    }
    
    echo "   📦 Total ajouté pour ce centre: {$centerBagsAdded} poches\n\n";
}

// Résumé final
echo "🎉 MISE À JOUR TERMINÉE AVEC SUCCÈS !\n";
echo "=====================================\n";
echo "📊 RÉSUMÉ:\n";
echo "   - Centres traités: {$centers->count()}\n";
echo "   - Centres mis à jour: {$totalCentersUpdated}\n";
echo "   - Types sanguins: {$bloodTypes->count()}\n";
echo "   - Nouvelles poches créées: {$totalBagsCreated}\n";
echo "   - Stock cible par type/centre: {$targetStock} poches\n\n";

// Afficher le stock total par type sanguin
echo "🩸 STOCK TOTAL PAR TYPE SANGUIN:\n";
echo str_repeat("-", 40) . "\n";
foreach ($bloodTypes as $bloodType) {
    $totalStock = BloodBag::where('blood_type_id', $bloodType->id)
        ->where('status', 'available')
        ->count();
    echo "   {$bloodType->group}: " . str_pad($totalStock, 3, ' ', STR_PAD_LEFT) . " poches\n";
}

echo "\n✅ Script terminé avec succès !\n";
