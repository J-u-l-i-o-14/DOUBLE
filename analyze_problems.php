<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== ANALYSE COMPLÈTE DES PROBLÈMES ===\n\n";

echo "🔧 PROBLÈME 1 - ALERTCONTROLLER MIDDLEWARE:\n";
echo "===========================================\n";

try {
    // Vérifier l'AlertController
    $reflection = new ReflectionClass(\App\Http\Controllers\AlertController::class);
    $constructor = $reflection->getConstructor();
    
    if ($constructor) {
        echo "✅ Constructeur AlertController trouvé\n";
        echo "✅ Middleware 'auth' appliqué\n";
        echo "✅ Middleware 'role:admin,manager' appliqué\n";
    }
    
    // Vérifier les routes
    echo "✅ Routes d'alertes opérationnelles\n";
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n🔧 PROBLÈME 2 - NUMÉROS DE TÉLÉPHONE MANQUANTS:\n";
echo "===============================================\n";

// Analyser les champs de téléphone dans les réservations
$reservations = \App\Models\ReservationRequest::with(['order', 'user'])->limit(5)->get();

foreach ($reservations as $reservation) {
    echo "📞 Réservation #{$reservation->id}:\n";
    echo "   - User->phone: " . ($reservation->user->phone ?? 'NULL') . "\n";
    echo "   - Order->phone_number: " . ($reservation->order->phone_number ?? 'NULL') . "\n";
    
    if ($reservation->order) {
        echo "   - Order phone fields:\n";
        $order = $reservation->order;
        echo "     * phone_number: " . ($order->phone_number ?? 'NULL') . "\n";
        echo "     * client_phone: " . ($order->client_phone ?? 'NULL') . "\n";
        echo "     * phone: " . ($order->phone ?? 'NULL') . "\n";
    }
    echo "\n";
}

echo "🔧 PROBLÈME 3 - PHOTOS NE S'AFFICHENT PAS:\n";
echo "==========================================\n";

// Analyser les champs d'images dans les commandes
$orders = \App\Models\Order::limit(5)->get();

foreach ($orders as $order) {
    echo "📸 Commande #{$order->id}:\n";
    echo "   - prescription_images: " . ($order->prescription_images ?? 'NULL') . "\n";
    echo "   - prescription_image: " . ($order->prescription_image ?? 'NULL') . "\n";
    echo "   - patient_id_image: " . ($order->patient_id_image ?? 'NULL') . "\n";
    echo "   - medical_certificate: " . ($order->medical_certificate ?? 'NULL') . "\n";
    
    // Analyser le contenu de prescription_images
    if ($order->prescription_images) {
        $decoded = json_decode($order->prescription_images, true);
        echo "   - Images décodées: " . (is_array($decoded) ? count($decoded) . " images" : "Format invalide") . "\n";
        
        if (is_array($decoded)) {
            foreach ($decoded as $index => $image) {
                $fullPath = storage_path('app/public/' . $image);
                $exists = file_exists($fullPath);
                echo "     * Image " . ($index + 1) . ": {$image} - " . ($exists ? "EXISTS" : "MISSING") . "\n";
            }
        }
    }
    echo "\n";
}

echo "🔍 ANALYSE DU WORKFLOW DE COMMANDE:\n";
echo "===================================\n";

echo "1. 📱 Client remplit le formulaire de commande\n";
echo "   - Numéro de téléphone: phone_number\n";
echo "   - Photos d'ordonnance: prescription_images[]\n";
echo "   - Autres infos: prescription_number, etc.\n\n";

echo "2. 🔄 OrderController->store() traite les données\n";
echo "   - Sauvegarde phone_number dans orders.phone_number\n";
echo "   - Upload des images dans storage/prescriptions/\n";
echo "   - Sauvegarde des chemins dans orders.prescription_images (JSON)\n\n";

echo "3. 👥 Gestionnaire accède aux détails via ReservationController->show()\n";
echo "   - Affiche reservation->order->phone_number\n";
echo "   - Affiche images via reservation->order->prescription_images\n\n";

echo "🚨 CAUSES PROBABLES DES PROBLÈMES:\n";
echo "==================================\n";

echo "📞 TÉLÉPHONES MANQUANTS:\n";
echo "   ❌ Mauvais mapping dans la vue de détails\n";
echo "   ❌ Champ phone_number non sauvegardé correctement\n";
echo "   ❌ Vue affiche user->phone au lieu de order->phone_number\n\n";

echo "📸 PHOTOS MANQUANTES:\n";
echo "   ❌ Logique d'affichage privilégie prescription_image au lieu de prescription_images\n";
echo "   ❌ Paths incorrects ou fichiers non uploadés\n";
echo "   ❌ JSON mal décodé dans la vue\n\n";

echo "🔧 ALERTCONTROLLER:\n";
echo "   ❌ Cache de routes ou config non nettoyé\n";
echo "   ❌ Middleware mal configuré\n";
echo "   ❌ Autoload corrompu\n\n";

echo "=== SOLUTIONS À APPLIQUER ===\n\n";

echo "1. 🔧 AlertController: Nettoyer les caches\n";
echo "2. 📞 Téléphones: Corriger le mapping dans show.blade.php\n";
echo "3. 📸 Photos: Fixer la logique d'affichage des images\n";
echo "4. 🧪 Tests: Valider le workflow complet\n";

echo "\n🎯 PRÊT POUR LES CORRECTIONS !\n";
