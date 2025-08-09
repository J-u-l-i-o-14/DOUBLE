<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Center;
use App\Models\BloodType;
use App\Models\BloodBag;
use App\Models\CenterBloodTypeInventory;
use Carbon\Carbon;

class IncreaseStockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('🩸 Augmentation du stock de sang dans tous les centres...');

        // Récupérer tous les centres et types sanguins
        $centers = Center::all();
        $bloodTypes = BloodType::all();

        $totalBagsCreated = 0;
        $targetStock = 20; // Stock cible par type sanguin par centre

        foreach ($centers as $center) {
            $this->command->info("📍 Centre: {$center->name}");

            foreach ($bloodTypes as $bloodType) {
                // Vérifier le stock actuel disponible
                $currentAvailableStock = BloodBag::where('center_id', $center->id)
                    ->where('blood_type_id', $bloodType->id)
                    ->where('status', 'available')
                    ->count();

                $this->command->info("   🩸 {$bloodType->group}: Stock actuel = {$currentAvailableStock}");

                // Calculer combien de poches ajouter
                $bagsToAdd = max(0, $targetStock - $currentAvailableStock);

                if ($bagsToAdd > 0) {
                    // Créer les nouvelles poches de sang
                    for ($i = 1; $i <= $bagsToAdd; $i++) {
                        BloodBag::create([
                            'center_id' => $center->id,
                            'blood_type_id' => $bloodType->id,
                            'collected_at' => Carbon::now()->subDays(rand(1, 30)), // Date aléatoire dans les 30 derniers jours
                            'expires_at' => Carbon::now()->addDays(42), // Les poches de sang durent ~42 jours
                            'status' => 'available',
                            'volume' => 450, // Volume standard en ml
                            'donor_id' => null, // Pas de donneur spécifique pour ce seeder
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now()
                        ]);
                        $totalBagsCreated++;
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

                    $this->command->info("   ✅ +{$bagsToAdd} poches ajoutées (Total: {$targetStock})");
                } else {
                    $this->command->info("   ✅ Stock suffisant");
                }
            }
            
            $this->command->info(""); // Ligne vide pour la lisibilité
        }

        // Résumé final
        $this->command->info("🎉 Seeder terminé avec succès !");
        $this->command->info("📊 Résumé:");
        $this->command->info("   - Centres traités: {$centers->count()}");
        $this->command->info("   - Types sanguins: {$bloodTypes->count()}");
        $this->command->info("   - Nouvelles poches créées: {$totalBagsCreated}");
        $this->command->info("   - Stock cible par type/centre: {$targetStock} poches");
        
        // Afficher le stock total par type sanguin
        $this->command->info("\n🩸 Stock total par type sanguin:");
        foreach ($bloodTypes as $bloodType) {
            $totalStock = BloodBag::where('blood_type_id', $bloodType->id)
                ->where('status', 'available')
                ->count();
            $this->command->info("   {$bloodType->group}: {$totalStock} poches");
        }
    }
}
