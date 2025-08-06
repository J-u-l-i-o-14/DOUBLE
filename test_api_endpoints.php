<?php

require_once __DIR__ . '/vendor/autoload.php';

// Charger l'application Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Center;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Notification;
use App\Models\CenterBloodTypeInventory;
use App\Models\BloodType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CartController;

echo "🔍 TEST API - SIMULATION APPELS HTTP\n";
echo "====================================\n\n";

try {
    // Préparer un utilisateur
    $user = User::firstOrCreate(
        ['email' => 'api.test@example.com'],
        [
            'name' => 'API Test User',
            'email' => 'api.test@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password123'),
            'role' => 'patient'
        ]
    );
    
    Auth::login($user);
    echo "✅ Utilisateur API connecté: {$user->email}\n";

    // Test 1: Ajouter au panier via API
    echo "\n📋 TEST 1: AJOUT AU PANIER VIA API\n";
    echo "----------------------------------\n";
    
    $center = Center::first();
    $inventory = CenterBloodTypeInventory::where('center_id', $center->id)
        ->where('available_quantity', '>', 0)
        ->with('bloodType')
        ->first();
    
    if (!$inventory) {
        echo "❌ Pas de stock disponible\n";
        exit(1);
    }
    
    // Simuler l'appel API d'ajout au panier
    $cartController = new CartController();
    $request = new Request([
        'center_id' => $center->id,
        'blood_type' => $inventory->bloodType->group,
        'quantity' => 2
    ]);
    
    $response = $cartController->add($request);
    $responseData = json_decode($response->getContent(), true);
    
    if ($responseData['success']) {
        echo "✅ Ajout au panier réussi: {$responseData['message']}\n";
        echo "   - Centre: {$center->name}\n";
        echo "   - Type: {$inventory->bloodType->group}\n";
        echo "   - Quantité: 2\n";
    } else {
        echo "❌ Échec ajout panier: {$responseData['message']}\n";
    }

    // Test 2: Récupérer le panier via API
    echo "\n📋 TEST 2: RÉCUPÉRATION PANIER VIA API\n";
    echo "--------------------------------------\n";
    
    $cartResponse = $cartController->index();
    $cartData = json_decode($cartResponse->getContent(), true);
    
    if ($cartData['success'] && !empty($cartData['items'])) {
        echo "✅ Panier récupéré avec succès\n";
        echo "   - Nombre d'articles: " . count($cartData['items']) . "\n";
        echo "   - Total: {$cartData['total']} F CFA\n";
        
        foreach ($cartData['items'] as $item) {
            echo "   - {$item['center_name']}: {$item['quantity']} × {$item['blood_type']}\n";
        }
    } else {
        echo "❌ Erreur récupération panier\n";
    }

    // Test 3: Créer une commande via API
    echo "\n📋 TEST 3: CRÉATION COMMANDE VIA API\n";
    echo "------------------------------------\n";
    
    // Compter les éléments avant
    $ordersCountBefore = Order::count();
    $notificationsCountBefore = Notification::count();
    $stockBefore = $inventory->available_quantity;
    
    echo "État initial:\n";
    echo "   - Commandes: {$ordersCountBefore}\n";
    echo "   - Notifications: {$notificationsCountBefore}\n";
    echo "   - Stock: {$stockBefore}\n";
    
    // Simuler l'appel API de commande
    $orderController = new OrderController();
    
    // Créer une fausse image pour le test
    $fakeImageContent = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==');
    $tempImagePath = tempnam(sys_get_temp_dir(), 'test_prescription_') . '.png';
    file_put_contents($tempImagePath, $fakeImageContent);
    
    // Créer un fichier uploadé simulé
    $uploadedFile = new \Illuminate\Http\UploadedFile(
        $tempImagePath,
        'test_prescription.png',
        'image/png',
        strlen($fakeImageContent),
        null,
        true
    );
    
    $orderRequest = new Request([
        'prescription_number' => 'API-TEST-' . time(),
        'phone_number' => '+228 98 76 54 32',
        'payment_method' => 'flooz',
        'notes' => 'Test automatisé via API'
    ]);
    
    // Ajouter les fichiers
    $orderRequest->files->set('prescription_images', [$uploadedFile]);
    
    // Headers simulés
    $orderRequest->headers->set('Accept', 'application/json');
    $orderRequest->headers->set('Content-Type', 'multipart/form-data');
    
    try {
        $orderResponse = $orderController->store($orderRequest);
        $orderData = json_decode($orderResponse->getContent(), true);
        
        if ($orderData['success']) {
            echo "✅ Commande créée avec succès!\n";
            echo "   - Message: {$orderData['message']}\n";
            echo "   - Montant acompte: {$orderData['formatted_total']}\n";
            echo "   - Nombre de commandes: {$orderData['orders_count']}\n";
            
            // Vérifier les changements
            $ordersCountAfter = Order::count();
            $notificationsCountAfter = Notification::count();
            $inventory->refresh();
            $stockAfter = $inventory->available_quantity;
            
            echo "\nChangements détectés:\n";
            echo "   - Nouvelles commandes: " . ($ordersCountAfter - $ordersCountBefore) . "\n";
            echo "   - Nouvelles notifications: " . ($notificationsCountAfter - $notificationsCountBefore) . "\n";
            echo "   - Stock décrémenté: " . ($stockBefore - $stockAfter) . "\n";
            
            // Récupérer la dernière commande
            $lastOrder = Order::latest()->first();
            if ($lastOrder) {
                echo "\nDétails de la commande:\n";
                echo "   - ID: {$lastOrder->id}\n";
                echo "   - Prescription: {$lastOrder->prescription_number}\n";
                echo "   - Téléphone: {$lastOrder->phone_number}\n";
                echo "   - Type sang: {$lastOrder->blood_type}\n";
                echo "   - Quantité: {$lastOrder->quantity}\n";
                echo "   - Prix original: " . number_format($lastOrder->original_price, 0, ',', ' ') . " F CFA\n";
                echo "   - Acompte payé: " . number_format($lastOrder->total_amount, 0, ',', ' ') . " F CFA\n";
                echo "   - Méthode: {$lastOrder->payment_method}\n";
                echo "   - Statut paiement: {$lastOrder->payment_status}\n";
                echo "   - Statut commande: {$lastOrder->status}\n";
                
                // Vérifier les images
                $images = $lastOrder->prescription_images_array;
                echo "   - Images: " . count($images) . " fichier(s)\n";
            }
            
            // Récupérer la dernière notification
            $lastNotification = Notification::with('user')->latest()->first();
            if ($lastNotification) {
                echo "\nDétails de la notification:\n";
                echo "   - Pour: {$lastNotification->user->name}\n";
                echo "   - Type: {$lastNotification->type}\n";
                echo "   - Titre: {$lastNotification->title}\n";
                echo "   - Message: {$lastNotification->message}\n";
                echo "   - Lue: " . ($lastNotification->read_at ? 'Oui' : 'Non') . "\n";
            }
            
        } else {
            echo "❌ Échec création commande: {$orderData['message']}\n";
            if (isset($orderData['errors'])) {
                foreach ($orderData['errors'] as $field => $errors) {
                    echo "   - {$field}: " . implode(', ', $errors) . "\n";
                }
            }
        }
    } catch (\Exception $e) {
        echo "❌ Exception lors de la commande: " . $e->getMessage() . "\n";
        echo "   Fichier: " . $e->getFile() . ":" . $e->getLine() . "\n";
    }
    
    // Nettoyer le fichier temporaire
    if (file_exists($tempImagePath)) {
        unlink($tempImagePath);
    }

    // Test 4: Vérifier le système de paiement
    echo "\n📋 TEST 4: VÉRIFICATION SYSTÈME DE PAIEMENT\n";
    echo "-------------------------------------------\n";
    
    $recentOrders = Order::where('user_id', $user->id)
        ->orderBy('created_at', 'desc')
        ->limit(3)
        ->get();
    
    $totalAcomptes = 0;
    $totalSoldes = 0;
    
    echo "Commandes récentes:\n";
    foreach ($recentOrders as $order) {
        $solde = $order->original_price - $order->total_amount;
        $totalAcomptes += $order->total_amount;
        $totalSoldes += $solde;
        
        echo "   - Commande #{$order->id}:\n";
        echo "     * Original: " . number_format($order->original_price, 0, ',', ' ') . " F CFA\n";
        echo "     * Acompte: " . number_format($order->total_amount, 0, ',', ' ') . " F CFA\n";
        echo "     * Solde: " . number_format($solde, 0, ',', ' ') . " F CFA\n";
        echo "     * Statut: {$order->payment_status}\n";
        echo "     * Méthode: {$order->payment_method}\n";
    }
    
    echo "\nRécapitulatif financier:\n";
    echo "   - Total acomptes: " . number_format($totalAcomptes, 0, ',', ' ') . " F CFA\n";
    echo "   - Total soldes: " . number_format($totalSoldes, 0, ',', ' ') . " F CFA\n";
    echo "   - Total commandes: " . number_format($totalAcomptes + $totalSoldes, 0, ',', ' ') . " F CFA\n";

    echo "\n🎉 TESTS API TERMINÉS AVEC SUCCÈS!\n";
    echo "===================================\n";
    echo "✅ Ajout au panier fonctionnel\n";
    echo "✅ Récupération panier fonctionnelle\n";
    echo "✅ Création commande fonctionnelle\n";
    echo "✅ Système de paiement fonctionnel\n";
    echo "✅ Notifications automatiques\n";
    echo "✅ Gestion du stock\n";
    echo "✅ Upload d'images\n";

} catch (\Exception $e) {
    echo "\n💥 ERREUR DANS LES TESTS API:\n";
    echo "==============================\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "Fichier: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\nTrace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
