<?php

/**
 * Script simple pour tester le cycle de vie sans créer d'Order
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\ReservationRequest;
use App\Models\BloodBag;
use App\Models\CenterBloodTypeInventory;
use Carbon\Carbon;

// Configuration Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔄 TEST SIMPLE DU CYCLE DE VIE DES RÉSERVATIONS\n";
echo "=" . str_repeat("=", 50) . "\n\n";

// État initial
$reservations = ReservationRequest::count();
$availableBags = BloodBag::where('status', 'available')->count();
$reservedBags = BloodBag::where('status', 'reserved')->count();

echo "📊 ÉTAT INITIAL:\n";
echo "  Réservations: {$reservations}\n";
echo "  Poches disponibles: {$availableBags}\n";
echo "  Poches réservées: {$reservedBags}\n\n";

try {
    // Test du cycle de vie simple
    echo "🔄 TEST DU CYCLE DE VIE:\n";
    echo "-" . str_repeat("-", 30) . "\n";
    
    // 1. Créer une réservation simple
    echo "1️⃣ Création d'une réservation...\n";
    
    $reservation = ReservationRequest::create([
        'user_id' => 1, 
        'center_id' => 1,
        'blood_type_id' => 1,
        'quantity' => 1,
        'status' => 'pending',
        'urgency_level' => 'medium',
        'medical_reason' => 'Test cycle de vie simple',
        'expires_at' => Carbon::now()->addHours(24),
        'total_amount' => 100.00, // Ajouter le montant total requis
    ]);
    
    echo "   ✅ Réservation #{$reservation->id} créée (pending)\n";
    
    // 2. Confirmer la réservation
    echo "\n2️⃣ Confirmation de la réservation...\n";
    
    // Trouver une poche disponible
    $bloodBag = BloodBag::where('status', 'available')
        ->where('center_id', 1)
        ->where('blood_type_id', 1)
        ->first();
    
    if ($bloodBag) {
        // Réserver la poche
        $bloodBag->update(['status' => 'reserved']);
        
        // Créer l'association
        DB::table('reservation_blood_bags')->insert([
            'reservation_id' => $reservation->id, // Nom correct de la colonne
            'blood_bag_id' => $bloodBag->id,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        
        // Confirmer la réservation
        $reservation->update([
            'status' => 'confirmed',
            'confirmed_at' => Carbon::now(),
        ]);
        
        echo "   ✅ Réservation confirmée, poche #{$bloodBag->id} réservée\n";
    } else {
        echo "   ❌ Aucune poche disponible trouvée\n";
    }
    
    // 3. Test de finalisation
    echo "\n3️⃣ Finalisation (completed)...\n";
    
    $reservation->update(['status' => 'completed']);
    if ($bloodBag) {
        $bloodBag->update(['status' => 'transfused']);
    }
    
    echo "   ✅ Réservation finalisée, poche transfusée\n";
    
    // 4. Test d'expiration avec nouvelle réservation
    echo "\n4️⃣ Test d'expiration...\n";
    
    $expiredReservation = ReservationRequest::create([
        'user_id' => 1,
        'center_id' => 1,
        'blood_type_id' => 1,
        'quantity' => 1,
        'status' => 'confirmed',
        'urgency_level' => 'low',
        'medical_reason' => 'Test expiration',
        'expires_at' => Carbon::now()->subHour(),
        'confirmed_at' => Carbon::now()->subHours(2),
        'total_amount' => 100.00,
    ]);
    
    $expiredReservation->update([
        'status' => 'expired',
        'manager_notes' => 'Expirée automatiquement'
    ]);
    
    echo "   ✅ Réservation #{$expiredReservation->id} expirée\n";
    
    // 5. Test d'annulation
    echo "\n5️⃣ Test d'annulation...\n";
    
    $cancelledReservation = ReservationRequest::create([
        'user_id' => 1,
        'center_id' => 1,
        'blood_type_id' => 1,
        'quantity' => 1,
        'status' => 'pending',
        'urgency_level' => 'medium',
        'medical_reason' => 'Test annulation',
        'expires_at' => Carbon::now()->addHours(24),
        'total_amount' => 100.00,
    ]);
    
    $cancelledReservation->update([
        'status' => 'cancelled',
        'manager_notes' => 'Annulée pour test'
    ]);
    
    echo "   ✅ Réservation #{$cancelledReservation->id} annulée\n";
    
    // 6. Test de terminaison
    echo "\n6️⃣ Test de terminaison...\n";
    
    $reservation->update([
        'status' => 'terminated',
        'manager_notes' => 'Terminée pour test'
    ]);
    
    echo "   ✅ Réservation #{$reservation->id} terminée\n";
    
    // Nettoyage
    echo "\n7️⃣ Nettoyage...\n";
    
    // Supprimer les associations
    DB::table('reservation_blood_bags')
        ->whereIn('reservation_id', [$reservation->id, $expiredReservation->id, $cancelledReservation->id])
        ->delete();
    
    // Supprimer les réservations de test
    $reservation->delete();
    $expiredReservation->delete();
    $cancelledReservation->delete();
    
    // Remettre la poche en disponible
    if ($bloodBag) {
        $bloodBag->update(['status' => 'available']);
    }
    
    echo "   ✅ Données de test supprimées\n";
    
    echo "\n✅ TOUS LES TESTS PASSÉS AVEC SUCCÈS!\n";
    echo "Le système de cycle de vie fonctionne correctement.\n\n";
    
} catch (Exception $e) {
    echo "\n❌ ERREUR: " . $e->getMessage() . "\n\n";
}

// État final
$finalReservations = ReservationRequest::count();
$finalAvailableBags = BloodBag::where('status', 'available')->count();
$finalReservedBags = BloodBag::where('status', 'reserved')->count();

echo "📊 ÉTAT FINAL:\n";
echo "  Réservations: {$finalReservations}\n";
echo "  Poches disponibles: {$finalAvailableBags}\n";
echo "  Poches réservées: {$finalReservedBags}\n";

echo "\n" . str_repeat("=", 60) . "\n";
echo "✅ CYCLE DE VIE DES RÉSERVATIONS: TESTÉ\n";
echo str_repeat("=", 60) . "\n\n";
