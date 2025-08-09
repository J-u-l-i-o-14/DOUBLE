<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

// Charger l'application Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

echo "=== Test du Système de Notifications ===\n\n";

try {
    // 1. Vérifier les notifications existantes
    echo "1. Vérification des notifications existantes:\n";
    $notifications = \App\Models\Notification::with('user')->orderBy('created_at', 'desc')->limit(10)->get();
    
    echo "Nombre total de notifications: " . \App\Models\Notification::count() . "\n";
    echo "Notifications non lues: " . \App\Models\Notification::whereNull('read_at')->count() . "\n\n";
    
    if ($notifications->count() > 0) {
        echo "Dernières notifications:\n";
        foreach ($notifications as $notification) {
            $status = $notification->read_at ? 'LUE' : 'NON LUE';
            echo "- ID: {$notification->id} | Type: {$notification->type} | Status: {$status}\n";
            echo "  Titre: {$notification->title}\n";
            echo "  Message: {$notification->message}\n";
            echo "  Utilisateur: {$notification->user->name} (ID: {$notification->user_id})\n";
            echo "  Créée le: {$notification->created_at->format('d/m/Y H:i:s')}\n\n";
        }
    } else {
        echo "Aucune notification trouvée.\n\n";
    }
    
    // 2. Vérifier les gestionnaires de centre
    echo "2. Vérification des gestionnaires de centre:\n";
    $managers = \App\Models\User::whereIn('role', ['manager', 'admin'])->with('center')->get();
    
    foreach ($managers as $manager) {
        echo "- {$manager->name} ({$manager->role}) - Centre: " . 
             ($manager->center ? $manager->center->name : 'Aucun') . 
             " (ID: {$manager->center_id})\n";
    }
    echo "\n";
    
    // 3. Vérifier les commandes récentes
    echo "3. Vérification des commandes récentes:\n";
    $recentOrders = \App\Models\Order::with(['user', 'center'])
                                    ->orderBy('created_at', 'desc')
                                    ->limit(5)
                                    ->get();
    
    if ($recentOrders->count() > 0) {
        foreach ($recentOrders as $order) {
            echo "- Commande ID: {$order->id}\n";
            echo "  Client: {$order->user->name}\n";
            echo "  Centre: " . ($order->center ? $order->center->name : 'Inconnu') . "\n";
            echo "  Type de sang: {$order->blood_type}\n";
            echo "  Quantité: {$order->quantity}\n";
            echo "  Statut: {$order->status}\n";
            echo "  Créée le: {$order->created_at->format('d/m/Y H:i:s')}\n\n";
        }
    } else {
        echo "Aucune commande récente trouvée.\n\n";
    }
    
    // 4. Test de création d'une notification
    echo "4. Test de création d'une notification:\n";
    
    // Trouver un gestionnaire pour le test
    $testManager = \App\Models\User::whereIn('role', ['manager', 'admin'])->first();
    
    if ($testManager) {
        $testNotification = \App\Models\Notification::create([
            'user_id' => $testManager->id,
            'type' => 'test_notification',
            'title' => 'Test de notification',
            'message' => 'Ceci est un test automatique du système de notifications.',
            'data' => json_encode([
                'test' => true,
                'timestamp' => now()->toDateTimeString()
            ]),
            'read_at' => null
        ]);
        
        echo "Notification de test créée avec succès (ID: {$testNotification->id})\n";
        echo "Pour l'utilisateur: {$testManager->name} (ID: {$testManager->id})\n\n";
        
        // Compter les notifications non lues pour ce gestionnaire
        $unreadCount = \App\Models\Notification::where('user_id', $testManager->id)
                                              ->whereNull('read_at')
                                              ->count();
        echo "Notifications non lues pour {$testManager->name}: {$unreadCount}\n\n";
        
    } else {
        echo "Aucun gestionnaire trouvé pour le test.\n\n";
    }
    
    // 5. Vérifier les alertes actives
    echo "5. Vérification des alertes actives:\n";
    $activeAlerts = \App\Models\Alert::where('resolved', false)->with('bloodType')->get();
    
    echo "Nombre d'alertes actives: " . $activeAlerts->count() . "\n";
    
    if ($activeAlerts->count() > 0) {
        foreach ($activeAlerts as $alert) {
            echo "- Alert ID: {$alert->id}\n";
            echo "  Type: {$alert->type}\n";
            echo "  Message: {$alert->message}\n";
            echo "  Type de sang: " . ($alert->bloodType ? $alert->bloodType->group : 'Inconnu') . "\n";
            echo "  Centre ID: {$alert->center_id}\n";
            echo "  Créée le: {$alert->created_at->format('d/m/Y H:i:s')}\n\n";
        }
    } else {
        echo "Aucune alerte active.\n\n";
    }
    
    echo "=== Test terminé avec succès ===\n";
    
} catch (\Exception $e) {
    echo "Erreur lors du test: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
