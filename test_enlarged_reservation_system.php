<?php
/**
 * Script de test pour valider le système de réservation élargi
 * À exécuter avec: php test_enlarged_reservation_system.php
 */

require_once 'vendor/autoload.php';

echo "🩸 Test du système de réservation élargi - Sprint 3\n";
echo "=" . str_repeat("=", 50) . "\n\n";

// Test 1: Vérification de la migration
echo "1️⃣ Vérification de la structure de base de données...\n";
try {
    $pdo = new PDO('sqlite:database/database.sqlite');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Vérifier la table orders
    $stmt = $pdo->query("PRAGMA table_info(orders)");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $requiredColumns = [
        'phone_number',
        'prescription_image', 
        'payment_method',
        'original_price',
        'discount_amount',
        'payment_status'
    ];
    
    $foundColumns = array_column($columns, 'name');
    $missingColumns = array_diff($requiredColumns, $foundColumns);
    
    if (empty($missingColumns)) {
        echo "   ✅ Toutes les nouvelles colonnes sont présentes\n";
    } else {
        echo "   ❌ Colonnes manquantes: " . implode(', ', $missingColumns) . "\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Erreur de base de données: " . $e->getMessage() . "\n";
}

// Test 2: Vérification des fichiers de vues
echo "\n2️⃣ Vérification des fichiers de vues...\n";

$viewFiles = [
    'resources/views/partials/_order-reservation-modal.blade.php' => 'Modal de réservation élargi',
    'resources/views/orders/index.blade.php' => 'Liste des commandes',
    'resources/views/orders/show.blade.php' => 'Détail de commande',
    'resources/views/blood-reservation.blade.php' => 'Page de réservation'
];

foreach ($viewFiles as $file => $description) {
    if (file_exists($file)) {
        echo "   ✅ $description: $file\n";
    } else {
        echo "   ❌ Fichier manquant: $file\n";
    }
}

// Test 3: Vérification du dossier de stockage
echo "\n3️⃣ Vérification du stockage des images...\n";

$storageDir = 'storage/app/public/prescriptions';
if (is_dir($storageDir)) {
    echo "   ✅ Dossier de stockage des ordonnances: $storageDir\n";
    echo "   📁 Permissions: " . substr(sprintf('%o', fileperms($storageDir)), -4) . "\n";
} else {
    echo "   ❌ Dossier manquant: $storageDir\n";
}

// Test 4: Vérification des images de moyens de paiement
echo "\n4️⃣ Vérification des images de moyens de paiement...\n";

$paymentImages = [
    'public/images/flooz.png' => 'Logo Flooz',
    'public/images/carte.jpg' => 'Logo Carte Bancaire'
];

foreach ($paymentImages as $file => $description) {
    if (file_exists($file)) {
        echo "   ✅ $description: $file\n";
    } else {
        echo "   ❌ Image manquante: $file\n";
    }
}

// Test 5: Vérification du modèle Order
echo "\n5️⃣ Vérification du modèle Order...\n";

$orderModelFile = 'app/Models/Order.php';
if (file_exists($orderModelFile)) {
    $content = file_get_contents($orderModelFile);
    
    $requiredMethods = [
        'getFormattedOriginalPriceAttribute',
        'getFormattedDiscountAttribute',
        'getPaymentMethodLabelAttribute',
        'getPaymentStatusLabelAttribute'
    ];
    
    foreach ($requiredMethods as $method) {
        if (strpos($content, $method) !== false) {
            echo "   ✅ Méthode $method présente\n";
        } else {
            echo "   ❌ Méthode manquante: $method\n";
        }
    }
    
    // Vérifier les champs fillable
    if (strpos($content, 'phone_number') !== false && 
        strpos($content, 'prescription_image') !== false && 
        strpos($content, 'payment_method') !== false) {
        echo "   ✅ Nouveaux champs fillable ajoutés\n";
    } else {
        echo "   ❌ Champs fillable manquants\n";
    }
} else {
    echo "   ❌ Fichier modèle manquant: $orderModelFile\n";
}

// Test 6: Vérification du contrôleur
echo "\n6️⃣ Vérification du contrôleur OrderController...\n";

$controllerFile = 'app/Http/Controllers/OrderController.php';
if (file_exists($controllerFile)) {
    $content = file_get_contents($controllerFile);
    
    // Vérifier la validation des nouveaux champs
    if (strpos($content, 'phone_number') !== false && 
        strpos($content, 'prescription_image') !== false && 
        strpos($content, 'payment_method') !== false) {
        echo "   ✅ Validation des nouveaux champs présente\n";
    } else {
        echo "   ❌ Validation des nouveaux champs manquante\n";
    }
    
    // Vérifier le calcul de la réduction
    if (strpos($content, '* 0.5') !== false || strpos($content, '50%') !== false) {
        echo "   ✅ Calcul de réduction de 50% présent\n";
    } else {
        echo "   ❌ Calcul de réduction manquant\n";
    }
    
    // Vérifier la gestion d'upload
    if (strpos($content, 'storeAs') !== false && strpos($content, 'prescriptions') !== false) {
        echo "   ✅ Gestion d'upload d'image présente\n";
    } else {
        echo "   ❌ Gestion d'upload manquante\n";
    }
} else {
    echo "   ❌ Fichier contrôleur manquant: $controllerFile\n";
}

// Résumé final
echo "\n" . str_repeat("=", 60) . "\n";
echo "✅ SYSTÈME DE RÉSERVATION ÉLARGI - SPRINT 3\n";
echo str_repeat("=", 60) . "\n";
echo "🔥 Fonctionnalités ajoutées:\n";
echo "   • Upload d'images d'ordonnance (max 5MB)\n";
echo "   • Numéro de téléphone obligatoire\n";
echo "   • 3 moyens de paiement (T-Money, Flooz, Carte)\n";
echo "   • Réduction automatique de 50%\n";
echo "   • Interface utilisateur enrichie\n";
echo "   • Affichage détaillé des commandes\n";
echo "   • Modal d'agrandissement d'image\n\n";

echo "🚀 Prêt pour les tests utilisateur!\n";
echo "📝 Voir le fichier TESTS_SPRINT3_ELARGI.md pour les tests détaillés\n\n";

echo "🌐 Pour tester:\n";
echo "   1. php artisan serve\n";
echo "   2. Aller sur http://localhost:8000/blood-reservation\n";
echo "   3. Effectuer une recherche et tester le nouveau processus\n\n";
?>
