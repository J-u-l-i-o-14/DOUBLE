<?php

require_once __DIR__ . '/vendor/autoload.php';

// Charger l'application Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Center;
use App\Models\Cart;
use App\Models\BloodType;
use App\Models\CenterBloodTypeInventory;
use Illuminate\Support\Facades\Hash;

echo "🏗️ Création des données de test pour le système de commande\n";
echo "==========================================================\n\n";

try {
    // 1. Créer un utilisateur test
    echo "👤 Création d'un utilisateur test...\n";
    $user = User::firstOrCreate(
        ['email' => 'test@user.com'],
        [
            'name' => 'Utilisateur Test',
            'email' => 'test@user.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => 'patient'
        ]
    );
    echo "✅ Utilisateur: {$user->name} ({$user->email})\n";

    // 2. Vérifier qu'il y a des centres
    $centersCount = Center::count();
    echo "\n🏥 Centres disponibles: {$centersCount}\n";
    
    if ($centersCount == 0) {
        echo "❌ Aucun centre trouvé. Veuillez d'abord importer les données de base.\n";
        exit(1);
    }

    $center = Center::first();
    echo "✅ Centre sélectionné: {$center->name}\n";

    // 3. Créer un gestionnaire pour le centre
    echo "\n👨‍💼 Création d'un gestionnaire...\n";
    $manager = User::firstOrCreate(
        ['email' => 'manager@center.com'],
        [
            'name' => 'Gestionnaire Centre',
            'email' => 'manager@center.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => 'manager',
            'center_id' => $center->id
        ]
    );
    echo "✅ Gestionnaire: {$manager->name} ({$manager->email})\n";

    // 4. Vérifier les types de sang
    $bloodTypesCount = BloodType::count();
    echo "\n🩸 Types de sang disponibles: {$bloodTypesCount}\n";
    
    if ($bloodTypesCount == 0) {
        echo "❌ Aucun type de sang trouvé. Création des types de base...\n";
        $bloodTypes = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
        foreach ($bloodTypes as $type) {
            BloodType::firstOrCreate(['group' => $type]);
        }
        echo "✅ Types de sang créés\n";
    }

    // 5. Créer du stock pour le centre
    echo "\n📦 Vérification du stock...\n";
    $bloodTypes = BloodType::all();
    
    foreach ($bloodTypes as $bloodType) {
        $inventory = CenterBloodTypeInventory::firstOrCreate(
            [
                'center_id' => $center->id,
                'blood_type_id' => $bloodType->id
            ],
            [
                'total_quantity' => 50,
                'available_quantity' => 30,
                'reserved_quantity' => 5,
                'expired_quantity' => 0
            ]
        );
        
        // Assurer qu'il y a du stock disponible
        if ($inventory->available_quantity < 5) {
            $inventory->update(['available_quantity' => 10]);
        }
        
        echo "✅ Stock {$bloodType->group}: {$inventory->available_quantity} disponibles\n";
    }

    // 6. Ajouter des articles au panier de test
    echo "\n🛒 Ajout d'articles au panier de test...\n";
    Cart::where('user_id', $user->id)->delete(); // Vider le panier existant
    
    $sampleBloodType = $bloodTypes->first();
    $cartItem = Cart::create([
        'user_id' => $user->id,
        'center_id' => $center->id,
        'blood_type' => $sampleBloodType->group,
        'quantity' => 2
    ]);
    echo "✅ Panier: 2 poches de {$sampleBloodType->group}\n";

    echo "\n🎉 DONNÉES DE TEST CRÉÉES AVEC SUCCÈS!\n";
    echo "=====================================\n";
    echo "📋 Utilisateur test: test@user.com (mot de passe: password)\n";
    echo "📋 Gestionnaire: manager@center.com (mot de passe: password)\n";
    echo "📋 Centre: {$center->name}\n";
    echo "📋 Panier: 2 poches de {$sampleBloodType->group}\n";
    echo "\n🚀 Vous pouvez maintenant tester le système de commande!\n";

} catch (\Exception $e) {
    echo "\n❌ ERREUR:\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "Fichier: " . $e->getFile() . ":" . $e->getLine() . "\n";
    exit(1);
}
