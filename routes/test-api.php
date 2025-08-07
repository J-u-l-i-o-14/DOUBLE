<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\Order;
use App\Models\Center;
use App\Models\Cart;

/*
|--------------------------------------------------------------------------
| Test API Routes
|--------------------------------------------------------------------------
|
| Routes spécifiques pour les tests du système de commande
|
*/

// Test de la base de données
Route::get('/test/database', function () {
    try {
        // Test de connexion
        $connection = DB::connection();
        $pdo = $connection->getPdo();
        
        // Obtenir les tables
        $tables = DB::select('SHOW TABLES');
        $tableNames = array_map(function($table) {
            return array_values((array)$table)[0];
        }, $tables);
        
        // Test des modèles principaux
        $modelTests = [
            'users' => User::count(),
            'centers' => Center::count(),
            'orders' => Order::count(),
        ];
        
        return response()->json([
            'success' => true,
            'database' => [
                'driver' => config('database.default'),
                'name' => config('database.connections.mysql.database'),
                'tables' => count($tableNames),
                'table_list' => $tableNames,
                'models' => $modelTests
            ]
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur de connexion à la base de données: ' . $e->getMessage(),
            'error' => $e->getTraceAsString()
        ], 500);
    }
});

// Test des routes essentielles
Route::get('/test/routes', function () {
    $routes = [
        'orders_store' => route('orders.store'),
        'cart_add' => url('/api/cart/add'),
        'prescription_check' => url('/api/orders/check-prescription-status'),
    ];
    
    return response()->json([
        'success' => true,
        'routes' => $routes
    ]);
});

// Test de création de panier temporaire
Route::post('/test/simulate-cart', function (Request $request) {
    try {
        // Simuler un utilisateur
        $userId = 1; // Utilisateur test
        
        // Nettoyer le panier existant
        Cart::where('user_id', $userId)->delete();
        
        // Créer des articles de test
        $cartItems = [
            ['center_id' => 1, 'blood_type' => 'A+', 'quantity' => 2],
            ['center_id' => 1, 'blood_type' => 'O-', 'quantity' => 1],
        ];
        
        foreach ($cartItems as $item) {
            Cart::create([
                'user_id' => $userId,
                'center_id' => $item['center_id'],
                'blood_type' => $item['blood_type'],
                'quantity' => $item['quantity']
            ]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Panier de test créé',
            'cart_items' => count($cartItems),
            'items' => $cartItems
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la création du panier: ' . $e->getMessage()
        ], 500);
    }
});

// Test de validation d'ordonnance
Route::post('/test/validate-prescription', function (Request $request) {
    try {
        $prescriptionNumber = $request->input('prescription_number', 'TEST-001');
        
        // Tester la méthode du modèle Order
        $status = Order::checkPrescriptionStatus($prescriptionNumber);
        $canAdd = Order::canAddNewOrder($prescriptionNumber);
        
        return response()->json([
            'success' => true,
            'prescription_number' => $prescriptionNumber,
            'status' => $status,
            'can_add_new' => $canAdd,
            'timestamp' => now()
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la validation: ' . $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

// Test de création de commande simplifiée
Route::post('/test/create-order', function (Request $request) {
    try {
        // Simuler la création d'une commande sans authentification complète
        $orderData = [
            'user_id' => 1,
            'center_id' => 1,
            'prescription_number' => $request->input('prescription_number', 'TEST-' . time()),
            'phone_number' => $request->input('phone_number', '22890123456'),
            'prescription_images' => json_encode(['test-image.png']),
            'blood_type' => 'A+',
            'quantity' => 2,
            'unit_price' => 5000,
            'total_amount' => 10000,
            'deposit_amount' => 5000,
            'remaining_amount' => 5000,
            'payment_method' => $request->input('payment_method', 'tmoney'),
            'payment_status' => 'partial',
            'status' => 'pending',
            'document_status' => 'pending',
            'notes' => $request->input('notes')
        ];
        
        $order = Order::create($orderData);
        
        return response()->json([
            'success' => true,
            'message' => 'Commande de test créée',
            'order_id' => $order->id,
            'order_data' => $orderData
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la création: ' . $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

// Test des notifications
Route::get('/test/notifications', function () {
    try {
        $notifications = \App\Models\Notification::latest()->take(10)->get();
        
        return response()->json([
            'success' => true,
            'notifications_count' => $notifications->count(),
            'notifications' => $notifications
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur notifications: ' . $e->getMessage()
        ], 500);
    }
});

// Test de l'état général du système
Route::get('/test/system-status', function () {
    try {
        $status = [
            'database_connection' => 'OK',
            'tables_exist' => [
                'users' => Schema::hasTable('users'),
                'orders' => Schema::hasTable('orders'),
                'centers' => Schema::hasTable('centers'),
                'notifications' => Schema::hasTable('notifications'),
                'carts' => Schema::hasTable('carts'),
            ],
            'record_counts' => [
                'users' => User::count(),
                'orders' => Order::count(),
                'centers' => Center::count(),
            ],
            'laravel_version' => app()->version(),
            'php_version' => PHP_VERSION,
            'timestamp' => now()
        ];
        
        return response()->json([
            'success' => true,
            'system_status' => $status
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur système: ' . $e->getMessage()
        ], 500);
    }
});
