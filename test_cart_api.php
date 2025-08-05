<?php
// Script de test pour l'API du panier
// Exécuter avec: php test_cart_api.php

require_once 'vendor/autoload.php';

$baseUrl = 'http://localhost:8000';

// Test 1: Vérifier l'accès à l'API du panier sans authentification
echo "=== Test API du panier ===\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/cart');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Status Code: $httpCode\n";
echo "Response: $response\n";

if ($httpCode === 401) {
    echo "✅ L'authentification est requise (normal)\n";
} else {
    echo "❌ Problème d'authentification\n";
}

// Test 2: Vérifier les routes
echo "\n=== Vérification des routes ===\n";
echo "Routes à tester manuellement après connexion:\n";
echo "- GET /cart (index)\n";
echo "- POST /cart/add (add)\n";
echo "- DELETE /cart/remove-by-data (removeByData)\n";
echo "- DELETE /cart/{id} (remove)\n";
echo "- DELETE /cart (clear)\n";
echo "- POST /cart/payment (processPayment)\n";

echo "\n=== Instructions de test ===\n";
echo "1. Démarrer le serveur: php artisan serve\n";
echo "2. Se connecter sur l'application\n";
echo "3. Aller sur /blood-reservation\n";
echo "4. Effectuer une recherche\n";
echo "5. Tester les boutons d'ajout\n";
echo "6. Ouvrir les outils de développement (F12)\n";
echo "7. Vérifier la console pour les erreurs\n";
?>
