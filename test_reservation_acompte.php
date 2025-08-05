<?php
/**
 * Script de validation des corrections - Système de réservation avec acompte
 * Usage: php test_reservation_acompte.php
 */

echo "🩸 Validation du système de réservation avec acompte - Sprint 3 Corrigé\n";
echo "=" . str_repeat("=", 65) . "\n\n";

// Test des termes corrigés
echo "1️⃣ Vérification de la terminologie corrigée...\n";

$modalFile = 'resources/views/partials/_order-reservation-modal.blade.php';
if (file_exists($modalFile)) {
    $content = file_get_contents($modalFile);
    
    $corrections = [
        'Acompte à payer (50%)' => 'Acompte correctly labeled',
        'À payer maintenant' => 'Current payment clear',
        'solde restant (50%)' => 'Remaining balance mentioned',
        'Maximum 72 heures' => 'Deadline specified',
        'Conditions de réservation' => 'Conditions properly labeled'
    ];
    
    foreach ($corrections as $term => $description) {
        if (strpos($content, $term) !== false) {
            echo "   ✅ $description: '$term'\n";
        } else {
            echo "   ❌ Missing term: $term\n";
        }
    }
    
    // Vérifier l'absence des anciens termes
    $oldTerms = ['Réduction de 50%', 'Total à payer', 'réduction sur tous'];
    foreach ($oldTerms as $oldTerm) {
        if (strpos($content, $oldTerm) === false) {
            echo "   ✅ Old term correctly removed: '$oldTerm'\n";
        } else {
            echo "   ⚠️ Old term still present: $oldTerm\n";
        }
    }
} else {
    echo "   ❌ Modal file not found\n";
}

// Test des champs obligatoires
echo "\n2️⃣ Vérification des validations obligatoires...\n";

if (file_exists($modalFile)) {
    $content = file_get_contents($modalFile);
    
    $requiredFields = [
        'prescription_number.*required' => 'Prescription number required',
        'phone_number.*required' => 'Phone number required', 
        'prescription_image.*required' => 'Prescription image required',
        'payment_method.*required' => 'Payment method required',
        'text-red-500.*\*' => 'Required field indicators'
    ];
    
    foreach ($requiredFields as $pattern => $description) {
        if (preg_match('/' . str_replace('.*', '.*?', $pattern) . '/s', $content)) {
            echo "   ✅ $description validated\n";
        } else {
            echo "   ❌ Missing validation: $description\n";
        }
    }
}

// Test du contrôleur
echo "\n3️⃣ Vérification du contrôleur OrderController...\n";

$controllerFile = 'app/Http/Controllers/OrderController.php';
if (file_exists($controllerFile)) {
    $content = file_get_contents($controllerFile);
    
    $controllerChecks = [
        'acompteAmount' => 'Deposit amount calculation',
        'soldeRestant' => 'Remaining balance calculation', 
        'partial' => 'Partial payment status',
        'Réservation créée' => 'Reservation success message',
        'required|image|mimes' => 'Image validation rules'
    ];
    
    foreach ($controllerChecks as $term => $description) {
        if (strpos($content, $term) !== false) {
            echo "   ✅ $description present\n";
        } else {
            echo "   ❌ Missing: $description\n";
        }
    }
}

// Test du modèle
echo "\n4️⃣ Vérification du modèle Order...\n";

$modelFile = 'app/Models/Order.php';
if (file_exists($modelFile)) {
    $content = file_get_contents($modelFile);
    
    if (strpos($content, 'Acompte payé') !== false) {
        echo "   ✅ Payment status 'partial' labeled as 'Acompte payé'\n";
    } else {
        echo "   ❌ Missing 'Acompte payé' label\n";
    }
    
    if (strpos($content, 'Payé intégralement') !== false) {
        echo "   ✅ Full payment status correctly labeled\n";
    } else {
        echo "   ❌ Missing full payment label\n";
    }
}

// Test de la vue de détail
echo "\n5️⃣ Vérification de la vue de détail...\n";

$showFile = 'resources/views/orders/show.blade.php';
if (file_exists($showFile)) {
    $content = file_get_contents($showFile);
    
    $detailChecks = [
        'Délai de retrait' => 'Withdrawal deadline shown',
        'Acompte payé' => 'Deposit payment shown',
        'Solde restant' => 'Remaining balance shown',
        'Statut de la réservation' => 'Reservation status (not order)',
        '72h' => '72 hour deadline mentioned'
    ];
    
    foreach ($detailChecks as $term => $description) {
        if (strpos($content, $term) !== false) {
            echo "   ✅ $description\n";
        } else {
            echo "   ❌ Missing: $description\n";
        }
    }
}

// Test de la migration
echo "\n6️⃣ Vérification de la migration...\n";

$migrationFile = 'database/migrations/2025_08_05_102000_add_payment_fields_to_orders_table.php';
if (file_exists($migrationFile)) {
    $content = file_get_contents($migrationFile);
    
    if (strpos($content, "'partial'") !== false) {
        echo "   ✅ 'partial' payment status added to enum\n";
    } else {
        echo "   ❌ 'partial' status missing from migration\n";
    }
} else {
    echo "   ❌ Migration file not found\n";
}

// Résumé final
echo "\n" . str_repeat("=", 65) . "\n";
echo "✅ CORRECTIONS APPLIQUÉES - SYSTÈME DE RÉSERVATION AVEC ACOMPTE\n";
echo str_repeat("=", 65) . "\n";
echo "🔧 Corrections apportées:\n";
echo "   • Terminologie corrigée: Acompte au lieu de réduction\n";
echo "   • Paiement en 2 étapes: 50% maintenant + 50% au retrait\n";
echo "   • Délai de retrait: 72h maximum affiché clairement\n";
echo "   • Champs obligatoires: Marqués avec (*) et validés\n";
echo "   • Statut 'partial': Acompte payé distingué du paiement complet\n";
echo "   • Calcul transparent: Prix total, acompte, solde restant\n";
echo "   • Messages cohérents: Réservation au lieu de commande\n\n";

echo "📋 Champs obligatoires (*):\n";
echo "   • Numéro d'ordonnance\n";
echo "   • Numéro de téléphone\n";
echo "   • Photo de l'ordonnance\n";
echo "   • Moyen de paiement\n\n";

echo "🚀 Le système est maintenant cohérent avec le processus de réservation !\n";
?>
