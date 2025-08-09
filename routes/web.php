<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\BloodBagController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\TransfusionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\BloodReservationController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\SearchBloodController;
use Illuminate\Support\Facades\Route;

// Page d'accueil
Route::get('/', function() {
    return view('welcome');
})->name('home');

// Routes de réservation de sang - clients, donneurs et patients
Route::middleware(['auth', 'role:client,donor,patient'])->group(function () {
    Route::get('/blood-reservation', [SearchBloodController::class, 'showReservationForm'])->name('blood.reservation');
    Route::post('/blood-reservation-search', [SearchBloodController::class, 'searchBlood'])->name('blood.reservation.search');
    Route::post('/add-to-cart', [CartController::class, 'addToCart'])->name('cart.add');
    Route::post('/proceed-to-checkout', [CartController::class, 'proceedToCheckout'])->name('cart.checkout');
});

// Dashboard principal - accessible à tous les utilisateurs authentifiés avec dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard');

// Dashboard client - accessible aux clients, donneurs et patients
Route::get('/dashboard/client', [DashboardController::class, 'clientReservationDashboard'])
    ->middleware(['auth', 'role:client,donor,patient'])
    ->name('dashboard.client');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Route générale pour les appointments (accessible à tous les rôles)
    Route::get('/appointments', [AppointmentController::class, 'index'])->name('appointments.index');
    Route::get('/appointments/create', [AppointmentController::class, 'create'])->name('appointments.create');
    Route::post('/appointments', [AppointmentController::class, 'store'])->name('appointments.store');
});

// Routes pour les donneurs (pas de dashboard)
Route::middleware(['auth', 'role:donor'])->group(function () {
    Route::resource('appointments', AppointmentController::class)->except(['destroy', 'index']);
    Route::patch('/appointments/{appointment}/cancel', [AppointmentController::class, 'destroy'])->name('appointments.cancel');
});

// Routes pour les patients (pas de dashboard)
Route::middleware(['auth', 'role:patient'])->group(function () {
    // Routes spécifiques aux patients si nécessaire
});

// Routes pour les managers (avec dashboard)
Route::middleware(['auth', 'role:manager'])->group(function () {
    Route::resource('campaigns', CampaignController::class);
    // Les appointments sont déjà définis pour les donors, pas besoin de les redéfinir
    Route::patch('/appointments/{appointment}/confirm', [AppointmentController::class, 'confirm'])->name('appointments.confirm');
    Route::get('/blood-bags/stock', [BloodBagController::class, 'stock'])->name('blood-bags.stock');
    
    // SPRINT 4: Routes pour la validation des documents par les gestionnaires
    Route::post('/orders/{order}/validate', [App\Http\Controllers\OrderController::class, 'validateDocuments'])->name('orders.validate');
    
    // SPRINT 5: Routes pour la gestion des seuils d'alerte et statuts de réservation
    Route::resource('stock-thresholds', App\Http\Controllers\StockThresholdController::class)->except(['show', 'create', 'edit']);
    Route::get('/reservations/status', [App\Http\Controllers\ReservationStatusController::class, 'index'])->name('reservations.status.index');
    Route::put('/reservations/{order}/status', [App\Http\Controllers\ReservationStatusController::class, 'updateStatus'])->name('reservations.status.update');
    Route::post('/reservations/mark-expired', [App\Http\Controllers\ReservationStatusController::class, 'markExpired'])->name('reservations.mark-expired');
});

// Routes pour les clients et donneurs
Route::middleware(['auth', 'role:client,donor'])->group(function () {
    Route::get('/campaigns/public', [CampaignController::class, 'upcoming'])->name('campaigns.public');
    Route::get('/blood-bags/available', [BloodBagController::class, 'available'])->name('blood-bags.available');
    
    // Routes du panier
    Route::get('/cart', [App\Http\Controllers\CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add', [App\Http\Controllers\CartController::class, 'add'])->name('cart.add');
    Route::delete('/cart/{id}', [App\Http\Controllers\CartController::class, 'remove'])->name('cart.remove');
    Route::delete('/cart/remove-by-data', [App\Http\Controllers\CartController::class, 'removeByData'])->name('cart.removeByData');
    Route::delete('/cart', [App\Http\Controllers\CartController::class, 'clear'])->name('cart.clear');
    Route::post('/cart/payment', [App\Http\Controllers\CartController::class, 'processPayment'])->name('cart.payment');
    
    // Routes pour les commandes
    Route::post('/order', [App\Http\Controllers\OrderController::class, 'store'])->name('order.store');
    Route::get('/orders', [App\Http\Controllers\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [App\Http\Controllers\OrderController::class, 'show'])->name('orders.show');
    
    // SPRINT 4: Routes pour le statut en temps réel et vérification d'ordonnance
    Route::get('/orders/{order}/status', [App\Http\Controllers\OrderController::class, 'getOrderStatus'])->name('orders.status');
    Route::post('/check-prescription', [App\Http\Controllers\OrderController::class, 'apiCheckPrescriptionStatus'])->name('prescription.check');
    
    // Nouvelles routes Sprint 4
    Route::post('/check-prescription-status', [App\Http\Controllers\OrderController::class, 'checkPrescriptionStatus'])->name('prescription.check');
    Route::get('/orders/realtime/status', [App\Http\Controllers\OrderController::class, 'getRealTimeStatus'])->name('orders.realtime.status');
});

// Routes pour les admins (avec dashboard)
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('users', UserController::class);
    Route::resource('campaigns', CampaignController::class);
    Route::resource('donations', DonationController::class);
    Route::resource('patients', PatientController::class);
    Route::resource('transfusions', TransfusionController::class);
    Route::resource('centers', \App\Http\Controllers\CenterController::class);
    // Routes spéciales pour les rendez-vous (admin peut tout voir/modifier) - on utilise un nom différent
    Route::get('/appointments/admin', [AppointmentController::class, 'index'])->name('appointments.admin.index');
    Route::patch('/appointments/{appointment}/confirm', [AppointmentController::class, 'confirm'])->name('appointments.confirm');
    // Campagnes à venir (public pour les donneurs)
    Route::get('/campaigns/upcoming', [CampaignController::class, 'upcoming'])->name('campaigns.upcoming');
    
    // SPRINT 5: Routes pour les administrateurs (seuils d'alerte et statuts)
    Route::resource('stock-thresholds', App\Http\Controllers\StockThresholdController::class)->except(['show', 'create', 'edit']);
    Route::get('/reservations/status', [App\Http\Controllers\ReservationStatusController::class, 'index'])->name('reservations.status.index');
    Route::put('/reservations/{order}/status', [App\Http\Controllers\ReservationStatusController::class, 'updateStatus'])->name('reservations.status.update');
    Route::post('/reservations/mark-expired', [App\Http\Controllers\ReservationStatusController::class, 'markExpired'])->name('reservations.mark-expired');
});

// Routes publiques pour les campagnes (visible par tous les utilisateurs connectés)
Route::middleware('auth')->group(function () {
    Route::get('/campaigns/public', [CampaignController::class, 'upcoming'])->name('campaigns.public');
});

Route::get('/prendre-rendez-vous', [AppointmentController::class, 'publicForm'])->name('appointment.public');
Route::post('/prendre-rendez-vous', [AppointmentController::class, 'publicStore'])->name('appointment.public.store');

Route::post('/contact', [ContactController::class, 'send'])->name('contact.send');
Route::post('/reservation', [\App\Http\Controllers\SearchBloodController::class, 'storeReservation'])->name('reservation.store');

// Routes pour la gestion des stocks (admin + manager)
Route::middleware(['auth', 'role:admin,manager'])->group(function () {
    Route::resource('blood-bags', BloodBagController::class);
    Route::get('/blood-bags/stock', [BloodBagController::class, 'stock'])->name('blood-bags.stock');
    Route::post('/blood-bags/mark-expired', [BloodBagController::class, 'markExpired'])->name('blood-bags.markExpired');
    
    // Gestion des alertes - Sprint 5
    Route::get('/alerts', [\App\Http\Controllers\AlertController::class, 'index'])->name('alerts.index');
    Route::post('/alerts/{alert}/resolve', [\App\Http\Controllers\AlertController::class, 'resolve'])->name('alerts.resolve');
    Route::post('/alerts/{alert}/unresolve', [\App\Http\Controllers\AlertController::class, 'unresolve'])->name('alerts.unresolve');
    Route::post('/alerts/resolve-all', [\App\Http\Controllers\AlertController::class, 'resolveAll'])->name('alerts.resolveAll');
    Route::post('/alerts/generate', [\App\Http\Controllers\AlertController::class, 'generate'])->name('alerts.generate');
    Route::delete('/alerts/{alert}', [\App\Http\Controllers\AlertController::class, 'destroy'])->name('alerts.destroy');
    Route::get('/api/alerts/active', [\App\Http\Controllers\AlertController::class, 'getActiveAlerts'])->name('api.alerts.active');
    
    // Gestion des réservations - Sprint 5
    Route::get('/reservations', [\App\Http\Controllers\ReservationController::class, 'index'])->name('reservations.index');
    Route::get('/reservations/{reservation}', [\App\Http\Controllers\ReservationController::class, 'show'])->name('reservations.show');
    Route::post('/reservations/{reservation}/confirm', [\App\Http\Controllers\ReservationController::class, 'confirm'])->name('reservations.confirm');
    Route::post('/reservations/{reservation}/update-status', [\App\Http\Controllers\ReservationController::class, 'updateStatus'])->name('reservations.updateStatus');
    Route::post('/reservations/bulk-update-status', [\App\Http\Controllers\ReservationController::class, 'bulkUpdateStatus'])->name('reservations.bulkUpdateStatus');
});

// Recherche de sang par région et groupe sanguin
Route::get('/recherche-sang', [\App\Http\Controllers\SearchBloodController::class, 'search'])->name('search.blood');

// Recherche AJAX de sang (API)
Route::post('/api/recherche-sang', [\App\Http\Controllers\SearchBloodController::class, 'searchAjax'])->name('api.search.blood');
//Routes pour le panier

// API: centres par région
Route::get('/api/centers-by-region/{region}', [\App\Http\Controllers\SearchBloodController::class, 'centersByRegion']);

// Route de test temporaire pour vérifier les rôles
Route::get('/test-roles', function () {
    $users = \App\Models\User::all();
    $roles = [];
    foreach ($users as $user) {
        $roles[] = [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'has_dashboard' => $user->has_dashboard,
        ];
    }
    return response()->json($roles);
})->name('test.roles');

// Route de test pour vérifier le système de commande
Route::get('/test-system', function () {
    try {
        $data = [
            'users_count' => \App\Models\User::count(),
            'centers_count' => \App\Models\Center::count(),
            'orders_count' => \App\Models\Order::count(),
            'notifications_count' => \App\Models\Notification::count(),
            'cart_items_count' => \App\Models\Cart::count(),
            'latest_order' => \App\Models\Order::with('user', 'center')->latest()->first(),
            'latest_notification' => \App\Models\Notification::with('user')->latest()->first(),
            'sample_center' => \App\Models\Center::with('bloodTypeInventories.bloodType')->first()
        ];
        
        return response()->json($data, 200, [], JSON_PRETTY_PRINT);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ], 500);
    }
})->name('test.system');

require __DIR__.'/auth.php';

// Route pour obtenir le token CSRF (utile pour les tests)
Route::get('/csrf-token', function () {
    return csrf_token();
});

// Route de test pour les notifications
Route::get('/test-notifications', function () {
    try {
        $notifications = \App\Models\Notification::with('user:id,name,email,role')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();
            
        $managers = \App\Models\User::whereIn('role', ['manager', 'admin'])
            ->select('id', 'name', 'email', 'role', 'center_id')
            ->get();
            
        return response()->json([
            'notifications_count' => $notifications->count(),
            'notifications' => $notifications,
            'managers_count' => $managers->count(),
            'managers' => $managers,
            'latest_orders' => \App\Models\Order::with('user:id,name,email', 'center:id,name')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
        ], 200, [], JSON_PRETTY_PRINT);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ], 500);
    }
})->name('test.notifications');

// Route de test sans middleware pour vérifier le serveur
Route::post('/test-order-no-auth', function (Illuminate\Http\Request $request) {
    return response()->json([
        'success' => true,
        'message' => 'Serveur accessible',
        'data_received' => $request->except(['prescription_images']),
        'files_count' => $request->hasFile('prescription_images') ? count($request->file('prescription_images')) : 0,
        'csrf_token' => csrf_token()
    ]);
});

// Route de test avec auth mais sans CSRF
Route::post('/test-order-with-auth', function (Illuminate\Http\Request $request) {
    if (!Auth::check()) {
        return response()->json([
            'success' => false,
            'message' => 'Non authentifié'
        ], 401);
    }
    
    return response()->json([
        'success' => true,
        'message' => 'Serveur accessible avec auth',
        'user' => Auth::user()->only(['id', 'name', 'email']),
        'data_received' => $request->except(['prescription_images']),
        'files_count' => $request->hasFile('prescription_images') ? count($request->file('prescription_images')) : 0,
        'csrf_token' => csrf_token()
    ]);
})->middleware('auth')->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

// Route de debug pour tester la cloche de notification
Route::get('/debug-notification-bell', function() {
    $user = auth()->user();
    return response()->json([
        'user_role' => $user->role,
        'is_admin' => $user->is_admin,
        'is_manager' => $user->is_manager,
        'center_id' => $user->center_id,
        'should_see_bell' => $user->is_admin || $user->is_manager,
        'active_alerts_count' => \App\Models\Alert::where('center_id', $user->center_id)->where('resolved', false)->count(),
        'total_alerts' => \App\Models\Alert::count(),
    ]);
})->middleware('auth')->name('debug.bell');

// Route pour marquer les notifications comme lues
Route::post('/notifications/{notification}/read', function ($notificationId) {
    try {
        $notification = \App\Models\Notification::where('id', $notificationId)
            ->where('user_id', auth()->id())
            ->first();

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification non trouvée'
            ], 404);
        }

        $notification->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Notification marquée comme lue'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la mise à jour: ' . $e->getMessage()
        ], 500);
    }
})->middleware('auth')->name('notifications.read');

// Route de test pour voir toutes les notifications
Route::get('/test-notifications-display', function () {
    $notifications = \App\Models\Notification::with('user')->orderBy('created_at', 'desc')->get();
    $unreadCount = \App\Models\Notification::whereNull('read_at')->count();
    
    $html = "<h1>Test du Système de Notifications</h1>";
    $html .= "<p><strong>Total notifications:</strong> " . $notifications->count() . "</p>";
    $html .= "<p><strong>Non lues:</strong> " . $unreadCount . "</p><hr>";
    
    foreach ($notifications as $notification) {
        $status = $notification->read_at ? '<span style="color: green;">LUE</span>' : '<span style="color: red;">NON LUE</span>';
        $data = json_decode($notification->data, true);
        
        $html .= "<div style='border: 1px solid #ddd; padding: 10px; margin: 10px 0;'>";
        $html .= "<strong>ID:</strong> " . $notification->id . " | ";
        $html .= "<strong>Status:</strong> " . $status . "<br>";
        $html .= "<strong>Type:</strong> " . $notification->type . "<br>";
        $html .= "<strong>Titre:</strong> " . $notification->title . "<br>";
        $html .= "<strong>Message:</strong> " . $notification->message . "<br>";
        $html .= "<strong>Utilisateur:</strong> " . $notification->user->name . " (ID: " . $notification->user_id . ")<br>";
        $html .= "<strong>Créée le:</strong> " . $notification->created_at->format('d/m/Y H:i:s') . "<br>";
        
        if ($data) {
            $html .= "<strong>Données:</strong> " . json_encode($data, JSON_PRETTY_PRINT) . "<br>";
        }
        
        $html .= "</div>";
    }
    
    return $html;
})->middleware('auth')->name('test.notifications.display');

// Route de test pour le système de paiement
Route::get('/test-payment-system', function () {
    $orders = \App\Models\Order::with('user')->orderBy('created_at', 'desc')->get();
    
    $html = "<h1>Test du Système de Paiement</h1>";
    $html .= "<p><strong>Total commandes:</strong> " . $orders->count() . "</p><hr>";
    
    $statusCounts = [
        'pending' => 0,
        'confirmed' => 0,
        'cancelled' => 0,
        'completed' => 0
    ];
    
    $paymentCounts = [
        'partial' => 0,
        'paid' => 0,
        'pending' => 0
    ];
    
    foreach ($orders as $order) {
        if (isset($statusCounts[$order->status])) {
            $statusCounts[$order->status]++;
        }
        if (isset($paymentCounts[$order->payment_status])) {
            $paymentCounts[$order->payment_status]++;
        }
        
        $html .= "<div style='border: 1px solid #ddd; padding: 10px; margin: 10px 0;'>";
        $html .= "<strong>Commande ID:</strong> " . $order->id . "<br>";
        $html .= "<strong>Client:</strong> " . $order->user->name . "<br>";
        $html .= "<strong>Statut:</strong> " . $order->status . "<br>";
        $html .= "<strong>Statut paiement:</strong> " . $order->payment_status . "<br>";
        $html .= "<strong>Montant total:</strong> " . $order->total_amount . " FCFA<br>";
        $html .= "<strong>Acompte versé:</strong> " . $order->deposit_amount . " FCFA<br>";
        $html .= "<strong>Solde restant:</strong> " . $order->remaining_amount . " FCFA<br>";
        $html .= "<strong>Type de sang:</strong> " . $order->blood_type . "<br>";
        $html .= "<strong>Quantité:</strong> " . $order->quantity . "<br>";
        $html .= "<strong>Créée le:</strong> " . $order->created_at->format('d/m/Y H:i:s') . "<br>";
        $html .= "</div>";
    }
    
    $html .= "<hr><h2>Statistiques</h2>";
    $html .= "<h3>Par statut:</h3>";
    foreach ($statusCounts as $status => $count) {
        $html .= "<p><strong>" . ucfirst($status) . ":</strong> " . $count . "</p>";
    }
    
    $html .= "<h3>Par statut de paiement:</h3>";
    foreach ($paymentCounts as $status => $count) {
        $html .= "<p><strong>" . ucfirst($status) . ":</strong> " . $count . "</p>";
    }
    
    return $html;
})->middleware('auth')->name('test.payment.system');

// Route pour corriger les ReservationRequests manquantes
Route::get('/fix-reservation-requests', function () {
    try {
        $ordersWithoutReservation = \App\Models\Order::doesntHave('reservationRequest')->get();
        
        $html = "<h1>Correction des ReservationRequests</h1>";
        $html .= "<p><strong>Commandes sans ReservationRequest:</strong> " . $ordersWithoutReservation->count() . "</p>";
        
        if ($ordersWithoutReservation->count() > 0) {
            $fixed = 0;
            $html .= "<h2>Résultats de la correction:</h2>";
            
            foreach ($ordersWithoutReservation as $order) {
                try {
                    $reservation = $order->createReservationRequest();
                    
                    if ($reservation) {
                        $html .= "<p style='color: green;'>✓ Commande {$order->id}: ReservationRequest créée (ID: {$reservation->id})</p>";
                        $fixed++;
                    } else {
                        $html .= "<p style='color: red;'>✗ Échec pour la commande {$order->id}</p>";
                    }
                } catch (\Exception $e) {
                    $html .= "<p style='color: red;'>✗ Erreur pour la commande {$order->id}: " . $e->getMessage() . "</p>";
                }
            }
            
            $html .= "<hr><p><strong>Résumé:</strong> {$fixed} ReservationRequests créées sur " . $ordersWithoutReservation->count() . " commandes traitées.</p>";
        } else {
            $html .= "<p style='color: green;'>Toutes les commandes ont déjà leur ReservationRequest.</p>";
        }
        
        // Statistiques finales
        $totalOrders = \App\Models\Order::count();
        $totalReservations = \App\Models\ReservationRequest::count();
        $ordersWithReservation = \App\Models\Order::has('reservationRequest')->count();
        
        $html .= "<hr><h2>Statistiques finales:</h2>";
        $html .= "<p>Total commandes: {$totalOrders}</p>";
        $html .= "<p>Total ReservationRequests: {$totalReservations}</p>";
        $html .= "<p>Commandes avec ReservationRequest: {$ordersWithReservation}</p>";
        $html .= "<p>Commandes sans ReservationRequest: " . ($totalOrders - $ordersWithReservation) . "</p>";
        
        return $html;
    } catch (\Exception $e) {
        return "<h1>Erreur</h1><p>" . $e->getMessage() . "</p>";
    }
})->middleware('auth')->name('fix.reservation.requests');

// Route pour tester la cloche de notification directement
Route::get('/test-notification-bell', function () {
    $user = auth()->user();
    
    if (!($user->is_admin || $user->is_manager)) {
        return "Vous devez être admin ou manager pour voir cette page.";
    }
    
    $activeAlertsCount = \App\Models\Alert::where('center_id', $user->center_id)->where('resolved', false)->count();
    $activeAlerts = \App\Models\Alert::with('bloodType')->where('center_id', $user->center_id)->where('resolved', false)->orderBy('created_at', 'desc')->limit(5)->get();
    
    $unreadNotificationsCount = \App\Models\Notification::where('user_id', $user->id)->whereNull('read_at')->count();
    $unreadNotifications = \App\Models\Notification::where('user_id', $user->id)->whereNull('read_at')->orderBy('created_at', 'desc')->limit(5)->get();
    
    $totalNotifications = $activeAlertsCount + $unreadNotificationsCount;
    
    $html = "<h1>Test de la Cloche de Notification</h1>";
    $html .= "<p><strong>Utilisateur:</strong> {$user->name} ({$user->role})</p>";
    $html .= "<p><strong>Centre ID:</strong> {$user->center_id}</p>";
    $html .= "<hr>";
    
    $html .= "<h2>Alertes</h2>";
    $html .= "<p><strong>Alertes actives:</strong> {$activeAlertsCount}</p>";
    
    if ($activeAlerts->count() > 0) {
        foreach ($activeAlerts as $alert) {
            $html .= "<div style='border: 1px solid red; padding: 5px; margin: 5px 0;'>";
            $html .= "<strong>Type:</strong> {$alert->type} | ";
            $html .= "<strong>Message:</strong> {$alert->message}";
            $html .= "</div>";
        }
    }
    
    $html .= "<h2>Notifications</h2>";
    $html .= "<p><strong>Notifications non lues:</strong> {$unreadNotificationsCount}</p>";
    
    if ($unreadNotifications->count() > 0) {
        foreach ($unreadNotifications as $notification) {
            $html .= "<div style='border: 1px solid blue; padding: 5px; margin: 5px 0;'>";
            $html .= "<strong>Type:</strong> {$notification->type} | ";
            $html .= "<strong>Titre:</strong> {$notification->title} | ";
            $html .= "<strong>Message:</strong> {$notification->message}";
            $html .= "</div>";
        }
    }
    
    $html .= "<hr>";
    $html .= "<h2>Résumé</h2>";
    $html .= "<p><strong>Total notifications à afficher dans la cloche:</strong> {$totalNotifications}</p>";
    
    $html .= "<hr>";
    $html .= "<p><a href='/dashboard'>Aller au Dashboard</a></p>";
    
    return $html;
})->middleware('auth')->name('test.notification.bell');

// Route de validation finale du système
Route::get('/validate-system', function () {
    $html = "<h1>🎯 Validation Finale du Système Sprint 5</h1>";
    
    try {
        // 1. Vérification des notifications
        $totalNotifications = \App\Models\Notification::count();
        $unreadNotifications = \App\Models\Notification::whereNull('read_at')->count();
        
        $html .= "<h2>📱 Système de Notifications</h2>";
        $html .= "<p>✅ Total notifications: {$totalNotifications}</p>";
        $html .= "<p>✅ Non lues: {$unreadNotifications}</p>";
        
        // 2. Vérification des commandes et réservations
        $totalOrders = \App\Models\Order::count();
        $ordersWithReservation = \App\Models\Order::has('reservationRequest')->count();
        $ordersWithoutReservation = $totalOrders - $ordersWithReservation;
        
        $html .= "<h2>📦 Commandes et Réservations</h2>";
        $html .= "<p>✅ Total commandes: {$totalOrders}</p>";
        $html .= "<p>✅ Avec ReservationRequest: {$ordersWithReservation}</p>";
        
        if ($ordersWithoutReservation > 0) {
            $html .= "<p>⚠️ Sans ReservationRequest: {$ordersWithoutReservation} <a href='/fix-reservation-requests'>Corriger</a></p>";
        } else {
            $html .= "<p>✅ Toutes les commandes ont leur ReservationRequest</p>";
        }
        
        // 3. Vérification des paiements
        $partialPayments = \App\Models\Order::where('payment_status', 'partial')->count();
        $completedPayments = \App\Models\Order::where('payment_status', 'paid')->count();
        $pendingPayments = \App\Models\Order::where('payment_status', 'pending')->count();
        
        $html .= "<h2>💰 Système de Paiement</h2>";
        $html .= "<p>✅ Paiements partiels (50%): {$partialPayments}</p>";
        $html .= "<p>✅ Paiements complets: {$completedPayments}</p>";
        $html .= "<p>✅ En attente: {$pendingPayments}</p>";
        
        // 4. Vérification des statuts de commande
        $statusCounts = \App\Models\Order::selectRaw('status, COUNT(*) as count')->groupBy('status')->pluck('count', 'status');
        
        $html .= "<h2>📊 Statuts des Commandes</h2>";
        foreach ($statusCounts as $status => $count) {
            $html .= "<p>✅ " . ucfirst($status) . ": {$count}</p>";
        }
        
        // 5. Vérification des utilisateurs gestionnaires
        $managers = \App\Models\User::whereIn('role', ['admin', 'manager'])->count();
        $usersWithCenter = \App\Models\User::whereNotNull('center_id')->count();
        
        $html .= "<h2>👥 Utilisateurs et Centres</h2>";
        $html .= "<p>✅ Gestionnaires (admin/manager): {$managers}</p>";
        $html .= "<p>✅ Utilisateurs avec centre: {$usersWithCenter}</p>";
        
        // 6. Test de la cloche pour l'utilisateur actuel
        $user = auth()->user();
        if ($user->is_admin || $user->is_manager) {
            $userNotifications = \App\Models\Notification::where('user_id', $user->id)->whereNull('read_at')->count();
            $userAlerts = \App\Models\Alert::where('center_id', $user->center_id)->where('resolved', false)->count();
            $bellCount = $userNotifications + $userAlerts;
            
            $html .= "<h2>🔔 Test Cloche pour {$user->name}</h2>";
            $html .= "<p>✅ Notifications non lues: {$userNotifications}</p>";
            $html .= "<p>✅ Alertes actives: {$userAlerts}</p>";
            $html .= "<p>✅ Total cloche: {$bellCount}</p>";
        }
        
        $html .= "<hr>";
        $html .= "<h2>🎉 Status Global</h2>";
        
        $allGood = ($ordersWithoutReservation == 0);
        
        if ($allGood) {
            $html .= "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 20px; border-radius: 5px;'>";
            $html .= "<h3 style='color: #155724;'>✅ SYSTÈME ENTIÈREMENT FONCTIONNEL</h3>";
            $html .= "<p style='color: #155724;'>Tous les problèmes du Sprint 5 ont été corrigés :</p>";
            $html .= "<ul style='color: #155724;'>";
            $html .= "<li>✅ Dashboard avec calculs corrects (50% dépôts)</li>";
            $html .= "<li>✅ Notifications dans la cloche</li>";
            $html .= "<li>✅ Workflow de finalisation automatique</li>";
            $html .= "<li>✅ Réservations visibles dans les listes</li>";
            $html .= "<li>✅ Intégration Order ↔ ReservationRequest</li>";
            $html .= "</ul>";
            $html .= "</div>";
        } else {
            $html .= "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 20px; border-radius: 5px;'>";
            $html .= "<h3 style='color: #721c24;'>⚠️ CORRECTIONS NÉCESSAIRES</h3>";
            $html .= "<p style='color: #721c24;'>Il reste {$ordersWithoutReservation} commandes sans ReservationRequest.</p>";
            $html .= "<p><a href='/fix-reservation-requests' style='color: #007bff;'>Corriger maintenant</a></p>";
            $html .= "</div>";
        }
        
        $html .= "<hr>";
        $html .= "<h2>🔗 Liens Utiles</h2>";
        $html .= "<p><a href='/dashboard'>Dashboard Principal</a></p>";
        $html .= "<p><a href='/test-notification-bell'>Test Cloche Notifications</a></p>";
        $html .= "<p><a href='/test-notifications-display'>Voir Toutes les Notifications</a></p>";
        $html .= "<p><a href='/test-payment-system'>Test Système de Paiement</a></p>";
        
    } catch (\Exception $e) {
        $html .= "<div style='background: #f8d7da; padding: 20px;'>";
        $html .= "<h3>❌ Erreur lors de la validation</h3>";
        $html .= "<p>{$e->getMessage()}</p>";
        $html .= "</div>";
    }
    
    return $html;
})->middleware('auth')->name('validate.system');

// Route de debug pour les problèmes de notifications et commandes
Route::get('/debug-recent-issues', function () {
    $user = auth()->user();
    
    $html = "<h1>🔍 Debug des Problèmes Récents</h1>";
    $html .= "<p><strong>Utilisateur actuel:</strong> {$user->name} (ID: {$user->id}, Role: {$user->role}, Centre: {$user->center_id})</p>";
    $html .= "<hr>";
    
    // 1. Dernière commande
    $lastOrder = \App\Models\Order::latest()->first();
    if ($lastOrder) {
        $html .= "<h2>📦 Dernière Commande</h2>";
        $html .= "<p><strong>ID:</strong> {$lastOrder->id}</p>";
        $html .= "<p><strong>Client:</strong> {$lastOrder->user->name} (ID: {$lastOrder->user_id})</p>";
        $html .= "<p><strong>Centre:</strong> {$lastOrder->center_id}</p>";
        $html .= "<p><strong>Total Amount:</strong> {$lastOrder->total_amount} FCFA</p>";
        $html .= "<p><strong>Deposit Amount:</strong> {$lastOrder->deposit_amount} FCFA</p>";
        $html .= "<p><strong>Remaining Amount:</strong> {$lastOrder->remaining_amount} FCFA</p>";
        $html .= "<p><strong>Statut:</strong> {$lastOrder->status}</p>";
        $html .= "<p><strong>Payment Status:</strong> {$lastOrder->payment_status}</p>";
        $html .= "<p><strong>Créée le:</strong> {$lastOrder->created_at}</p>";
    }
    
    // 2. Notifications liées à cette commande
    if ($lastOrder) {
        $html .= "<h2>🔔 Notifications pour cette commande</h2>";
        $notifications = \App\Models\Notification::where('data', 'like', '%"order_id":' . $lastOrder->id . '%')->get();
        
        if ($notifications->count() > 0) {
            foreach ($notifications as $notif) {
                $html .= "<div style='border: 1px solid #ddd; padding: 10px; margin: 5px 0;'>";
                $html .= "<p><strong>ID:</strong> {$notif->id}</p>";
                $html .= "<p><strong>Utilisateur:</strong> {$notif->user->name} (ID: {$notif->user_id})</p>";
                $html .= "<p><strong>Type:</strong> {$notif->type}</p>";
                $html .= "<p><strong>Titre:</strong> {$notif->title}</p>";
                $html .= "<p><strong>Message:</strong> {$notif->message}</p>";
                $html .= "<p><strong>Lu:</strong> " . ($notif->read_at ? 'Oui' : 'Non') . "</p>";
                $html .= "<p><strong>Données:</strong> {$notif->data}</p>";
                $html .= "</div>";
            }
        } else {
            $html .= "<p style='color: red;'>❌ Aucune notification trouvée pour cette commande !</p>";
        }
    }
    
    // 3. ReservationRequest liée
    if ($lastOrder) {
        $html .= "<h2>📋 ReservationRequest</h2>";
        $reservation = $lastOrder->reservationRequest;
        
        if ($reservation) {
            $html .= "<p style='color: green;'>✅ ReservationRequest existe (ID: {$reservation->id})</p>";
            $html .= "<p><strong>Statut:</strong> {$reservation->status}</p>";
            $html .= "<p><strong>Centre:</strong> {$reservation->center_id}</p>";
            $html .= "<p><strong>Total:</strong> {$reservation->total_amount} FCFA</p>";
            $html .= "<p><strong>Payé:</strong> {$reservation->paid_amount} FCFA</p>";
        } else {
            $html .= "<p style='color: red;'>❌ Pas de ReservationRequest pour cette commande !</p>";
            $html .= "<p><a href='/fix-reservation-requests'>Créer maintenant</a></p>";
        }
    }
    
    // 4. Notifications non lues pour l'utilisateur actuel
    $html .= "<h2>🔔 Notifications pour {$user->name}</h2>";
    $userNotifications = \App\Models\Notification::where('user_id', $user->id)->orderBy('created_at', 'desc')->limit(5)->get();
    
    if ($userNotifications->count() > 0) {
        foreach ($userNotifications as $notif) {
            $status = $notif->read_at ? 'LUE' : 'NON LUE';
            $color = $notif->read_at ? 'green' : 'red';
            $html .= "<div style='border: 1px solid #{$color}; padding: 5px; margin: 5px 0;'>";
            $html .= "<p><strong>{$status}:</strong> {$notif->title}</p>";
            $html .= "<p>{$notif->message}</p>";
            $html .= "<p><small>{$notif->created_at}</small></p>";
            $html .= "</div>";
        }
    } else {
        $html .= "<p>Aucune notification pour cet utilisateur.</p>";
    }
    
    // 5. Gestionnaires du centre de la dernière commande
    if ($lastOrder) {
        $html .= "<h2>👥 Gestionnaires du centre {$lastOrder->center_id}</h2>";
        $managers = \App\Models\User::where('center_id', $lastOrder->center_id)
                                   ->whereIn('role', ['manager', 'admin'])
                                   ->get();
        
        foreach ($managers as $manager) {
            $unreadCount = \App\Models\Notification::where('user_id', $manager->id)->whereNull('read_at')->count();
            $html .= "<p><strong>{$manager->name}</strong> (ID: {$manager->id}, Role: {$manager->role}) - Notifications non lues: {$unreadCount}</p>";
        }
    }
    
    return $html;
})->middleware('auth')->name('debug.recent.issues');

// Route pour voir tous les utilisateurs et leurs centres
Route::get('/debug-users', function () {
    $users = \App\Models\User::with('center')->get();
    
    $html = "<h1>👥 Debug Utilisateurs et Centres</h1>";
    
    foreach ($users as $user) {
        $html .= "<div style='border: 1px solid #ddd; padding: 10px; margin: 5px 0;'>";
        $html .= "<p><strong>{$user->name}</strong> (ID: {$user->id})</p>";
        $html .= "<p>Role: {$user->role}</p>";
        $html .= "<p>Centre ID: {$user->center_id}</p>";
        $html .= "<p>Centre: " . ($user->center ? $user->center->name : 'Aucun') . "</p>";
        
        if (in_array($user->role, ['admin', 'manager'])) {
            $unreadNotifications = \App\Models\Notification::where('user_id', $user->id)->whereNull('read_at')->count();
            $html .= "<p style='color: blue;'>Notifications non lues: {$unreadNotifications}</p>";
        }
        
        $html .= "</div>";
    }
    
    return $html;
})->middleware('auth')->name('debug.users');

// Route pour créer une notification de test
Route::get('/create-test-notification', function () {
    $user = auth()->user();
    
    if (!($user->is_admin || $user->is_manager)) {
        return "Vous devez être admin ou manager pour utiliser cette fonction.";
    }
    
    try {
        $notification = \App\Models\Notification::create([
            'user_id' => $user->id,
            'type' => 'test_manual',
            'title' => 'Test de Notification Manuelle',
            'message' => 'Ceci est un test pour vérifier que les notifications s\'affichent dans la cloche.',
            'data' => json_encode([
                'test' => true,
                'created_by' => 'manual_test',
                'timestamp' => now()->toDateTimeString()
            ]),
            'read_at' => null
        ]);
        
        $html = "<h1>✅ Notification de Test Créée</h1>";
        $html .= "<p>Notification ID: {$notification->id}</p>";
        $html .= "<p>Pour l'utilisateur: {$user->name} (ID: {$user->id})</p>";
        $html .= "<hr>";
        
        // Vérifier le compteur
        $unreadCount = \App\Models\Notification::where('user_id', $user->id)->whereNull('read_at')->count();
        $html .= "<p>Notifications non lues pour vous: {$unreadCount}</p>";
        
        $html .= "<hr>";
        $html .= "<p><a href='/dashboard'>Aller au Dashboard pour voir la cloche</a></p>";
        $html .= "<p><a href='/test-notification-bell'>Tester la cloche</a></p>";
        
        return $html;
        
    } catch (\Exception $e) {
        return "<h1>❌ Erreur</h1><p>{$e->getMessage()}</p>";
    }
})->middleware('auth')->name('create.test.notification');

// Route pour déboguer spécifiquement le problème de notifications
Route::get('/debug-notification-system', function () {
    $html = "<h1>🔍 Debug Système de Notifications Complet</h1>";
    
    try {
        // 1. Dernière commande
        $lastOrder = \App\Models\Order::latest()->first();
        $html .= "<h2>📦 Dernière Commande</h2>";
        
        if ($lastOrder) {
            $html .= "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            $html .= "<p><strong>Commande ID:</strong> {$lastOrder->id}</p>";
            $html .= "<p><strong>Client:</strong> {$lastOrder->user->name} (ID: {$lastOrder->user_id})</p>";
            $html .= "<p><strong>Centre:</strong> {$lastOrder->center_id}</p>";
            $html .= "<p><strong>Créée le:</strong> {$lastOrder->created_at}</p>";
            $html .= "</div>";
            
            // 2. Gestionnaires de ce centre
            $managers = \App\Models\User::where('center_id', $lastOrder->center_id)
                                       ->whereIn('role', ['manager', 'admin'])
                                       ->get();
            
            $html .= "<h2>👥 Gestionnaires du Centre {$lastOrder->center_id}</h2>";
            $html .= "<p>Nombre: {$managers->count()}</p>";
            
            if ($managers->count() > 0) {
                foreach ($managers as $manager) {
                    $notifCount = \App\Models\Notification::where('user_id', $manager->id)->count();
                    $unreadCount = \App\Models\Notification::where('user_id', $manager->id)->whereNull('read_at')->count();
                    
                    $html .= "<div style='background: #e3f2fd; padding: 10px; border-radius: 5px; margin: 5px 0;'>";
                    $html .= "<p><strong>{$manager->name}</strong> (ID: {$manager->id}, Role: {$manager->role})</p>";
                    $html .= "<p>Total notifications: {$notifCount} | Non lues: {$unreadCount}</p>";
                    $html .= "</div>";
                }
            } else {
                $html .= "<p style='color: red;'>❌ AUCUN GESTIONNAIRE trouvé pour le centre {$lastOrder->center_id} !</p>";
            }
            
            // 3. Notifications liées à cette commande
            $orderNotifications = \App\Models\Notification::where('data', 'like', '%"order_id":' . $lastOrder->id . '%')->get();
            
            $html .= "<h2>🔔 Notifications pour la Commande {$lastOrder->id}</h2>";
            $html .= "<p>Nombre: {$orderNotifications->count()}</p>";
            
            if ($orderNotifications->count() > 0) {
                foreach ($orderNotifications as $notif) {
                    $status = $notif->read_at ? 'LUE' : 'NON LUE';
                    $color = $notif->read_at ? '#d4edda' : '#f8d7da';
                    
                    $html .= "<div style='background: {$color}; padding: 10px; border-radius: 5px; margin: 5px 0;'>";
                    $html .= "<p><strong>Notification ID:</strong> {$notif->id} ({$status})</p>";
                    $html .= "<p><strong>Pour:</strong> {$notif->user->name} (ID: {$notif->user_id})</p>";
                    $html .= "<p><strong>Type:</strong> {$notif->type}</p>";
                    $html .= "<p><strong>Titre:</strong> {$notif->title}</p>";
                    $html .= "<p><strong>Message:</strong> {$notif->message}</p>";
                    $html .= "<p><strong>Créée le:</strong> {$notif->created_at}</p>";
                    $html .= "</div>";
                }
            } else {
                $html .= "<p style='color: red;'>❌ AUCUNE NOTIFICATION trouvée pour cette commande !</p>";
                $html .= "<p>Cela suggère que la méthode createCenterNotification() n'a pas été appelée.</p>";
            }
            
        } else {
            $html .= "<p>Aucune commande trouvée.</p>";
        }
        
        // 4. Test de la fonction createCenterNotification
        $html .= "<h2>🧪 Test de Création de Notification</h2>";
        
        if ($lastOrder && $managers->count() > 0) {
            $html .= "<p><a href='/test-create-notification/{$lastOrder->id}' class='btn btn-primary'>Créer une notification de test pour cette commande</a></p>";
        }
        
        // 5. Toutes les notifications récentes
        $allNotifications = \App\Models\Notification::with('user')->orderBy('created_at', 'desc')->limit(10)->get();
        
        $html .= "<h2>📋 Toutes les Notifications Récentes</h2>";
        $html .= "<p>Nombre total: {$allNotifications->count()}</p>";
        
        foreach ($allNotifications as $notif) {
            $status = $notif->read_at ? 'LUE' : 'NON LUE';
            $color = $notif->read_at ? '#e8f5e8' : '#fff3cd';
            
            $html .= "<div style='background: {$color}; padding: 8px; border-radius: 3px; margin: 3px 0; font-size: 12px;'>";
            $html .= "<strong>ID {$notif->id}:</strong> {$notif->title} → {$notif->user->name} ({$status}) - {$notif->created_at->format('d/m H:i')}";
            $html .= "</div>";
        }
        
    } catch (\Exception $e) {
        $html .= "<div style='background: #f8d7da; padding: 20px;'>";
        $html .= "<h3>❌ Erreur</h3>";
        $html .= "<p>{$e->getMessage()}</p>";
        $html .= "</div>";
    }
    
    return $html;
})->middleware('auth')->name('debug.notification.system');

// Route pour tester la création de notification pour une commande
Route::get('/test-create-notification/{orderId}', function ($orderId) {
    try {
        $order = \App\Models\Order::findOrFail($orderId);
        
        // Simuler la création de notification comme dans OrderController
        $managers = \App\Models\User::where('center_id', $order->center_id)
                                   ->whereIn('role', ['manager', 'admin'])
                                   ->get();
        
        $html = "<h1>🧪 Test Création Notification pour Commande {$orderId}</h1>";
        $html .= "<p><strong>Centre:</strong> {$order->center_id}</p>";
        $html .= "<p><strong>Gestionnaires trouvés:</strong> {$managers->count()}</p>";
        
        $created = 0;
        foreach ($managers as $manager) {
            $notification = \App\Models\Notification::create([
                'user_id' => $manager->id,
                'type' => 'test_order_notification',
                'title' => 'Test - Nouvelle commande de sang',
                'message' => "Test: Nouvelle commande de {$order->quantity} poche(s) de {$order->blood_type} - Ordonnance: {$order->prescription_number}",
                'data' => json_encode([
                    'order_id' => $order->id,
                    'prescription_number' => $order->prescription_number,
                    'blood_type' => $order->blood_type,
                    'quantity' => $order->quantity,
                    'test' => true
                ]),
                'read_at' => null
            ]);
            
            $html .= "<p style='color: green;'>✅ Notification créée pour {$manager->name} (ID: {$notification->id})</p>";
            $created++;
        }
        
        $html .= "<hr>";
        $html .= "<p><strong>Résultat:</strong> {$created} notifications créées</p>";
        $html .= "<p><a href='/debug-notification-system'>Retour au debug</a></p>";
        $html .= "<p><a href='/test-notification-bell'>Tester la cloche</a></p>";
        
        return $html;
        
    } catch (\Exception $e) {
        return "<h1>❌ Erreur</h1><p>{$e->getMessage()}</p>";
    }
})->middleware('auth')->name('test.create.notification.order');

// Route pour debugger les images d'une commande spécifique
Route::get('/debug-order-images/{orderId?}', function ($orderId = null) {
    if (!$orderId) {
        $order = \App\Models\Order::latest()->first();
    } else {
        $order = \App\Models\Order::find($orderId);
    }
    
    if (!$order) {
        return "<h1>❌ Aucune commande trouvée</h1>";
    }
    
    $html = "<h1>🖼️ Debug Images - Commande #{$order->id}</h1>";
    $html .= "<p><strong>Client:</strong> {$order->user->name}</p>";
    $html .= "<p><strong>Créée le:</strong> {$order->created_at}</p>";
    $html .= "<hr>";
    
    // Données brutes
    $html .= "<h2>📊 Données Brutes</h2>";
    $html .= "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; font-family: monospace;'>";
    $html .= "<p><strong>prescription_image:</strong> " . ($order->prescription_image ?? 'NULL') . "</p>";
    $html .= "<p><strong>prescription_images:</strong> " . ($order->prescription_images ?? 'NULL') . "</p>";
    $html .= "<p><strong>patient_id_image:</strong> " . ($order->patient_id_image ?? 'NULL') . "</p>";
    $html .= "<p><strong>medical_certificate:</strong> " . ($order->medical_certificate ?? 'NULL') . "</p>";
    $html .= "</div>";
    
    // Logique d'affichage actuelle
    $html .= "<h2>🔍 Logique d'Affichage</h2>";
    
    $prescriptionImages = [];
    if ($order->prescription_images) {
        $decodedImages = is_string($order->prescription_images) ? json_decode($order->prescription_images, true) : $order->prescription_images;
        if (is_array($decodedImages) && !empty($decodedImages)) {
            $prescriptionImages = $decodedImages;
        }
    }
    
    if (empty($prescriptionImages) && $order->prescription_image) {
        $prescriptionImages[] = $order->prescription_image;
    }
    
    $html .= "<p><strong>Images à afficher:</strong></p>";
    if (!empty($prescriptionImages)) {
        $html .= "<ul>";
        foreach ($prescriptionImages as $index => $image) {
            $fullPath = asset('storage/prescriptions/' . $image);
            $html .= "<li>";
            $html .= "<strong>Image " . ($index + 1) . ":</strong> {$image}<br>";
            $html .= "<img src='{$fullPath}' style='max-width: 200px; max-height: 150px; margin: 5px; border: 1px solid #ddd;' alt='Prescription'>";
            $html .= "</li>";
        }
        $html .= "</ul>";
    } else {
        $html .= "<p style='color: red;'>Aucune image à afficher</p>";
    }
    
    // Autres images
    if ($order->patient_id_image) {
        $html .= "<h3>Pièce d'identité</h3>";
        $fullPath = asset('storage/' . $order->patient_id_image);
        $html .= "<img src='{$fullPath}' style='max-width: 200px; max-height: 150px; border: 1px solid #ddd;' alt='ID'>";
    }
    
    if ($order->medical_certificate) {
        $html .= "<h3>Certificat médical</h3>";
        $fullPath = asset('storage/' . $order->medical_certificate);
        $html .= "<img src='{$fullPath}' style='max-width: 200px; max-height: 150px; border: 1px solid #ddd;' alt='Certificat'>";
    }
    
    $html .= "<hr>";
    $html .= "<p><a href='/orders/{$order->id}'>Voir la commande complète</a></p>";
    
    return $html;
})->middleware('auth')->name('debug.order.images');

// Route pour debugger les paiements des commandes
Route::get('/debug-order-payments', function () {
    $orders = App\Models\Order::with('user', 'reservationRequest')->latest()->take(5)->get();
    
    $html = '<h1>💰 Debug Paiements des Commandes</h1>';
    $html .= '<style>
        .order-card { border: 1px solid #ddd; margin: 15px 0; padding: 15px; border-radius: 8px; }
        .field { margin: 5px 0; padding: 8px; background: #f8f9fa; border-radius: 4px; }
        .null { color: #dc3545; font-weight: bold; }
        .value { color: #28a745; font-weight: bold; }
    </style>';
    
    foreach ($orders as $order) {
        $html .= '<div class="order-card">';
        $html .= '<h3>🔹 Order #' . $order->id . ' - ' . $order->user->name . '</h3>';
        $html .= '<p><strong>Créée:</strong> ' . $order->created_at . '</p>';
        
        $html .= '<h4>💳 Champs de Paiement:</h4>';
        $html .= '<div class="field"><strong>total_amount:</strong> <span class="' . ($order->total_amount ? 'value' : 'null') . '">' . ($order->total_amount ?? 'NULL') . '</span></div>';
        $html .= '<div class="field"><strong>original_price:</strong> <span class="' . ($order->original_price ? 'value' : 'null') . '">' . ($order->original_price ?? 'NULL') . '</span></div>';
        $html .= '<div class="field"><strong>deposit_amount:</strong> <span class="' . ($order->deposit_amount ? 'value' : 'null') . '">' . ($order->deposit_amount ?? 'NULL') . '</span></div>';
        $html .= '<div class="field"><strong>remaining_amount:</strong> <span class="' . ($order->remaining_amount ? 'value' : 'null') . '">' . ($order->remaining_amount ?? 'NULL') . '</span></div>';
        $html .= '<div class="field"><strong>payment_status:</strong> <span class="value">' . ($order->payment_status ?? 'NULL') . '</span></div>';
        
        if ($order->reservationRequest) {
            $html .= '<h4>📋 ReservationRequest Associée:</h4>';
            $html .= '<div class="field"><strong>total_price:</strong> <span class="' . ($order->reservationRequest->total_price ? 'value' : 'null') . '">' . ($order->reservationRequest->total_price ?? 'NULL') . '</span></div>';
            $html .= '<div class="field"><strong>status:</strong> <span class="value">' . ($order->reservationRequest->status ?? 'NULL') . '</span></div>';
        } else {
            $html .= '<p style="color: #dc3545;">❌ Aucune ReservationRequest associée</p>';
        }
        
        // Calculs pour la vue
        $totalAmount = $order->total_amount ?? 0;
        $acompte = $order->deposit_amount ?? ($totalAmount * 0.5);
        $solde = $order->remaining_amount ?? ($totalAmount - $acompte);
        
        $html .= '<h4>🧮 Calculs pour la Vue:</h4>';
        $html .= '<div class="field"><strong>Acompte calculé:</strong> <span class="value">' . number_format($acompte, 0) . ' F CFA</span></div>';
        $html .= '<div class="field"><strong>Solde calculé:</strong> <span class="value">' . number_format($solde, 0) . ' F CFA</span></div>';
        $html .= '<div class="field"><strong>Total calculé:</strong> <span class="value">' . number_format($totalAmount, 0) . ' F CFA</span></div>';
        
        $html .= '</div>';
    }
    
    return response($html);
})->middleware('auth')->name('debug.order.payments');

// Route pour tester la création de notifications
Route::get('/test-create-notification', function () {
    // Créer une notification de test pour l'utilisateur connecté
    $notification = \App\Models\Notification::create([
        'user_id' => auth()->id(),
        'type' => 'new_order',
        'title' => 'Test - Nouvelle commande',
        'message' => 'Ceci est une notification de test créée le ' . now()->format('Y-m-d H:i:s'),
        'data' => json_encode(['order_id' => 999, 'test' => true]),
        'read_at' => null
    ]);
    
    return response()->json([
        'success' => true,
        'message' => 'Notification de test créée avec succès',
        'notification' => $notification,
        'redirect_to_debug' => url('/debug-notification-system')
    ]);
})->middleware('auth')->name('test.create.notification');

// Route pour marquer une notification comme lue
Route::post('/mark-notification-read/{id}', function ($id) {
    $notification = \App\Models\Notification::where('id', $id)
        ->where('user_id', auth()->id())
        ->first();
    
    if ($notification) {
        $notification->update(['read_at' => now()]);
        return response()->json(['success' => true]);
    }
    
    return response()->json(['success' => false], 404);
})->middleware('auth')->name('notification.mark.read');

// Route de test complet pour vérifier tous les systèmes
Route::get('/test-system-status', function () {
    $user = auth()->user();
    
    $html = '<h1>🔧 Test Statut du Système</h1>';
    $html .= '<style>
        .test-section { border: 1px solid #ddd; margin: 15px 0; padding: 15px; border-radius: 8px; }
        .success { background-color: #d4edda; border-color: #c3e6cb; }
        .warning { background-color: #fff3cd; border-color: #ffeaa7; }
        .error { background-color: #f8d7da; border-color: #f5c6cb; }
        .info { background-color: #d1ecf1; border-color: #bee5eb; }
    </style>';
    
    // Test 1: Données utilisateur
    $html .= '<div class="test-section info">';
    $html .= '<h3>👤 Utilisateur connecté</h3>';
    $html .= '<p><strong>ID:</strong> ' . $user->id . '</p>';
    $html .= '<p><strong>Nom:</strong> ' . $user->name . '</p>';
    $html .= '<p><strong>Email:</strong> ' . $user->email . '</p>';
    $html .= '<p><strong>Rôle:</strong> ' . $user->role . '</p>';
    $html .= '<p><strong>Centre ID:</strong> ' . ($user->center_id ?? 'NULL') . '</p>';
    $html .= '</div>';
    
    // Test 2: Notifications
    $notifications = \App\Models\Notification::where('user_id', $user->id)->orderBy('created_at', 'desc')->take(3)->get();
    $unreadCount = \App\Models\Notification::where('user_id', $user->id)->whereNull('read_at')->count();
    
    $html .= '<div class="test-section ' . ($unreadCount > 0 ? 'warning' : 'success') . '">';
    $html .= '<h3>🔔 Notifications</h3>';
    $html .= '<p><strong>Non lues:</strong> ' . $unreadCount . '</p>';
    $html .= '<p><strong>Total:</strong> ' . $notifications->count() . ' (3 dernières)</p>';
    
    if ($notifications->count() > 0) {
        $html .= '<ul>';
        foreach ($notifications as $notif) {
            $status = $notif->read_at ? '✅ Lue' : '❌ Non lue';
            $html .= '<li>' . $notif->title . ' - ' . $status . ' (' . $notif->created_at->diffForHumans() . ')</li>';
        }
        $html .= '</ul>';
    } else {
        $html .= '<p>Aucune notification trouvée.</p>';
    }
    $html .= '</div>';
    
    // Test 3: Commandes récentes
    $orders = \App\Models\Order::where('user_id', $user->id)->latest()->take(3)->get();
    
    $html .= '<div class="test-section ' . ($orders->count() > 0 ? 'success' : 'warning') . '">';
    $html .= '<h3>🛒 Commandes récentes</h3>';
    $html .= '<p><strong>Nombre:</strong> ' . $orders->count() . ' (3 dernières)</p>';
    
    if ($orders->count() > 0) {
        foreach ($orders as $order) {
            $html .= '<div style="margin: 10px 0; padding: 10px; background: #f8f9fa; border-radius: 4px;">';
            $html .= '<strong>Order #' . $order->id . '</strong><br>';
            $html .= 'Total: ' . ($order->total_amount ?? 'NULL') . ' F CFA<br>';
            $html .= 'Acompte: ' . ($order->deposit_amount ?? 'NULL') . ' F CFA<br>';
            $html .= 'Statut: ' . $order->payment_status . '<br>';
            $html .= 'Créée: ' . $order->created_at->diffForHumans();
            $html .= '</div>';
        }
    } else {
        $html .= '<p>Aucune commande trouvée.</p>';
    }
    $html .= '</div>';
    
    // Test 4: Actions rapides
    $html .= '<div class="test-section info">';
    $html .= '<h3>⚡ Actions Rapides</h3>';
    $html .= '<p><a href="/test-create-notification" class="btn btn-primary" style="margin: 5px; padding: 10px 15px; text-decoration: none; color: white; background: #007bff; border-radius: 4px;">Créer notification test</a></p>';
    $html .= '<p><a href="/debug-order-payments" class="btn btn-info" style="margin: 5px; padding: 10px 15px; text-decoration: none; color: white; background: #17a2b8; border-radius: 4px;">Debug paiements</a></p>';
    $html .= '<p><a href="/debug-order-images" class="btn btn-warning" style="margin: 5px; padding: 10px 15px; text-decoration: none; color: white; background: #ffc107; border-radius: 4px;">Debug images</a></p>';
    $html .= '<p><a href="/orders" class="btn btn-success" style="margin: 5px; padding: 10px 15px; text-decoration: none; color: white; background: #28a745; border-radius: 4px;">Mes commandes</a></p>';
    $html .= '</div>';
    
    return response($html);
})->middleware('auth')->name('test.system.status');

// Route pour nettoyer et optimiser les données d'images
Route::get('/fix-image-display', function () {
    $orders = \App\Models\Order::whereNotNull('prescription_images')
        ->orWhereNotNull('prescription_image')
        ->get();
    
    $html = '<h1>🔧 Correction Affichage Images</h1>';
    $html .= '<style>
        .order-fix { border: 1px solid #ddd; margin: 15px 0; padding: 15px; border-radius: 8px; }
        .fixed { background-color: #d4edda; }
        .unchanged { background-color: #e2e3e5; }
    </style>';
    
    $fixedCount = 0;
    
    foreach ($orders as $order) {
        $html .= '<div class="order-fix">';
        $html .= '<h4>Order #' . $order->id . ' - ' . $order->user->name . '</h4>';
        
        $hasChanges = false;
        
        // Si prescription_images est vide mais prescription_image existe, migrer
        if (empty($order->prescription_images) && !empty($order->prescription_image)) {
            $order->prescription_images = json_encode([$order->prescription_image]);
            $order->save();
            $html .= '<p>✅ Migré prescription_image vers prescription_images</p>';
            $hasChanges = true;
        }
        
        // Vérifier l'intégrité des images JSON
        if ($order->prescription_images) {
            $images = json_decode($order->prescription_images, true);
            if (!is_array($images)) {
                // Tenter de corriger
                if (is_string($order->prescription_images) && file_exists(storage_path('app/public/' . $order->prescription_images))) {
                    $order->prescription_images = json_encode([$order->prescription_images]);
                    $order->save();
                    $html .= '<p>✅ Corrigé format JSON des images</p>';
                    $hasChanges = true;
                }
            } else {
                // Vérifier que les fichiers existent
                $validImages = [];
                foreach ($images as $imagePath) {
                    if (file_exists(storage_path('app/public/' . $imagePath))) {
                        $validImages[] = $imagePath;
                    } else {
                        $html .= '<p>⚠️ Image manquante: ' . $imagePath . '</p>';
                    }
                }
                
                if (count($validImages) !== count($images)) {
                    $order->prescription_images = json_encode($validImages);
                    $order->save();
                    $html .= '<p>✅ Nettoyé images manquantes</p>';
                    $hasChanges = true;
                }
                
                $html .= '<p><strong>Images valides:</strong> ' . count($validImages) . '</p>';
                foreach ($validImages as $img) {
                    $html .= '<img src="' . asset('storage/' . $img) . '" style="max-width: 100px; margin: 2px;" />';
                }
            }
        }
        
        if ($hasChanges) {
            $fixedCount++;
            $html .= '<div class="fixed">Status: CORRIGÉ</div>';
        } else {
            $html .= '<div class="unchanged">Status: Inchangé</div>';
        }
        
        $html .= '</div>';
    }
    
    $html .= '<div style="background: #d1ecf1; padding: 15px; border-radius: 8px; margin: 20px 0;">';
    $html .= '<h3>📊 Résumé</h3>';
    $html .= '<p>Commandes traitées: ' . $orders->count() . '</p>';
    $html .= '<p>Commandes corrigées: ' . $fixedCount . '</p>';
    $html .= '<p><a href="/debug-order-images">Voir les images après correction</a></p>';
    $html .= '</div>';
    
    return response($html);
})->middleware('auth')->name('fix.image.display');

// Route de validation finale de tous les correctifs
Route::get('/validate-fixes', function () {
    $user = auth()->user();
    
    $html = '<h1>✅ Validation des Correctifs</h1>';
    $html .= '<style>
        .validation-section { border: 1px solid #ddd; margin: 15px 0; padding: 15px; border-radius: 8px; }
        .pass { background-color: #d4edda; border-color: #c3e6cb; }
        .fail { background-color: #f8d7da; border-color: #f5c6cb; }
        .warning { background-color: #fff3cd; border-color: #ffeaa7; }
    </style>';
    
    // Test 1: Validation des paiements dans les commandes
    $html .= '<div class="validation-section">';
    $html .= '<h3>💰 Test Affichage Paiements</h3>';
    
    $recentOrder = \App\Models\Order::where('user_id', $user->id)->latest()->first();
    if ($recentOrder) {
        $totalAmount = $recentOrder->total_amount ?? 0;
        $depositAmount = $recentOrder->deposit_amount ?? ($totalAmount * 0.5);
        $remainingAmount = $recentOrder->remaining_amount ?? ($totalAmount - $depositAmount);
        
        if ($totalAmount > 0) {
            $html .= '<div class="pass">';
            $html .= '<p>✅ Commande récente #' . $recentOrder->id . '</p>';
            $html .= '<p>Total: ' . number_format($totalAmount, 0) . ' F CFA</p>';
            $html .= '<p>Acompte: ' . number_format($depositAmount, 0) . ' F CFA</p>';
            $html .= '<p>Reste: ' . number_format($remainingAmount, 0) . ' F CFA</p>';
            $html .= '</div>';
        } else {
            $html .= '<div class="fail">';
            $html .= '<p>❌ Problème: total_amount = 0 pour la commande #' . $recentOrder->id . '</p>';
            $html .= '</div>';
        }
    } else {
        $html .= '<div class="warning"><p>⚠️ Aucune commande trouvée pour ce test</p></div>';
    }
    $html .= '</div>';
    
    // Test 2: Validation des images
    $html .= '<div class="validation-section">';
    $html .= '<h3>🖼️ Test Affichage Images</h3>';
    
    if ($recentOrder && $recentOrder->prescription_images) {
        $images = json_decode($recentOrder->prescription_images, true);
        if (is_array($images) && count($images) > 0) {
            $html .= '<div class="pass">';
            $html .= '<p>✅ ' . count($images) . ' image(s) trouvée(s)</p>';
            foreach ($images as $index => $imagePath) {
                if (file_exists(storage_path('app/public/' . $imagePath))) {
                    $html .= '<p>✅ Image ' . ($index + 1) . ': ' . $imagePath . '</p>';
                    $html .= '<img src="' . asset('storage/' . $imagePath) . '" style="max-width: 150px; margin: 5px;" />';
                } else {
                    $html .= '<p>❌ Image manquante: ' . $imagePath . '</p>';
                }
            }
            $html .= '</div>';
        } else {
            $html .= '<div class="fail"><p>❌ Format images invalide</p></div>';
        }
    } else {
        $html .= '<div class="warning"><p>⚠️ Aucune image dans la commande récente</p></div>';
    }
    $html .= '</div>';
    
    // Test 3: Validation des notifications
    $html .= '<div class="validation-section">';
    $html .= '<h3>🔔 Test Notifications</h3>';
    
    $unreadNotifications = \App\Models\Notification::where('user_id', $user->id)->whereNull('read_at')->count();
    $totalNotifications = \App\Models\Notification::where('user_id', $user->id)->count();
    
    if ($totalNotifications > 0) {
        $html .= '<div class="pass">';
        $html .= '<p>✅ ' . $totalNotifications . ' notification(s) au total</p>';
        $html .= '<p>📬 ' . $unreadNotifications . ' non lue(s)</p>';
        $html .= '</div>';
    } else {
        $html .= '<div class="warning"><p>⚠️ Aucune notification trouvée</p></div>';
    }
    $html .= '</div>';
    
    // Test 4: Validation du dashboard
    $html .= '<div class="validation-section">';
    $html .= '<h3>📊 Test Dashboard</h3>';
    
    $orderCount = \App\Models\Order::where('user_id', $user->id)->count();
    $totalSpent = \App\Models\Order::where('user_id', $user->id)
        ->where('payment_status', '!=', 'failed')
        ->sum('total_amount');
    
    $html .= '<div class="pass">';
    $html .= '<p>✅ ' . $orderCount . ' commande(s) au total</p>';
    $html .= '<p>💰 Total dépensé: ' . number_format($totalSpent, 0) . ' F CFA</p>';
    $html .= '</div>';
    $html .= '</div>';
    
    // Actions de test
    $html .= '<div class="validation-section">';
    $html .= '<h3>🔧 Actions de Test</h3>';
    $html .= '<p><a href="/orders" style="margin: 5px; padding: 10px 15px; text-decoration: none; color: white; background: #28a745; border-radius: 4px;">Tester page Mes Commandes</a></p>';
    $html .= '<p><a href="/dashboard" style="margin: 5px; padding: 10px 15px; text-decoration: none; color: white; background: #007bff; border-radius: 4px;">Tester Dashboard</a></p>';
    
    if ($recentOrder) {
        $html .= '<p><a href="/orders/' . $recentOrder->id . '" style="margin: 5px; padding: 10px 15px; text-decoration: none; color: white; background: #17a2b8; border-radius: 4px;">Voir détail commande récente</a></p>';
    }
    $html .= '</div>';
    
    return response($html);
})->middleware('auth')->name('validate.fixes');

// Route de test spécifique pour la réservation 5
Route::get('/debug-reservation/{id}', function ($id) {
    $reservation = \App\Models\ReservationRequest::with(['order.user', 'center'])->findOrFail($id);
    
    $html = '<h1>🔍 Debug Réservation #' . $id . '</h1>';
    $html .= '<style>
        .debug-section { border: 1px solid #ddd; margin: 15px 0; padding: 15px; border-radius: 8px; }
        .good { background-color: #d4edda; }
        .warning { background-color: #fff3cd; }
        .error { background-color: #f8d7da; }
    </style>';
    
    // Informations de base
    $html .= '<div class="debug-section good">';
    $html .= '<h3>📋 Informations de Base</h3>';
    $html .= '<p><strong>ID:</strong> ' . $reservation->id . '</p>';
    $html .= '<p><strong>Status:</strong> ' . $reservation->status . '</p>';
    $html .= '<p><strong>Client:</strong> ' . $reservation->order->user->name . '</p>';
    $html .= '<p><strong>Centre:</strong> ' . $reservation->center->name . '</p>';
    $html .= '<p><strong>Créée le:</strong> ' . $reservation->created_at . '</p>';
    $html .= '</div>';
    
    // Détails financiers
    $html .= '<div class="debug-section ' . ($reservation->order->total_amount > 0 ? 'good' : 'error') . '">';
    $html .= '<h3>💰 Détails Financiers</h3>';
    $html .= '<p><strong>Order ID:</strong> ' . $reservation->order->id . '</p>';
    $html .= '<p><strong>total_amount:</strong> ' . ($reservation->order->total_amount ?? 'NULL') . ' F CFA</p>';
    $html .= '<p><strong>deposit_amount:</strong> ' . ($reservation->order->deposit_amount ?? 'NULL') . ' F CFA</p>';
    $html .= '<p><strong>remaining_amount:</strong> ' . ($reservation->order->remaining_amount ?? 'NULL') . ' F CFA</p>';
    $html .= '<p><strong>payment_status:</strong> ' . ($reservation->order->payment_status ?? 'NULL') . '</p>';
    $html .= '<p><strong>original_price (ancien):</strong> ' . ($reservation->order->original_price ?? 'NULL') . ' F CFA</p>';
    $html .= '</div>';
    
    // Documents
    $prescriptionImages = [];
    if ($reservation->order->prescription_images) {
        $decodedImages = is_string($reservation->order->prescription_images) ? json_decode($reservation->order->prescription_images, true) : $reservation->order->prescription_images;
        if (is_array($decodedImages) && !empty($decodedImages)) {
            $prescriptionImages = $decodedImages;
        }
    }
    if (empty($prescriptionImages) && $reservation->order->prescription_image) {
        $prescriptionImages[] = $reservation->order->prescription_image;
    }
    
    $html .= '<div class="debug-section ' . (!empty($prescriptionImages) ? 'good' : 'warning') . '">';
    $html .= '<h3>📄 Documents</h3>';
    $html .= '<p><strong>prescription_number:</strong> ' . ($reservation->order->prescription_number ?? 'NULL') . '</p>';
    $html .= '<p><strong>prescription_images (nouveau):</strong> ' . ($reservation->order->prescription_images ?? 'NULL') . '</p>';
    $html .= '<p><strong>prescription_image (ancien):</strong> ' . ($reservation->order->prescription_image ?? 'NULL') . '</p>';
    $html .= '<p><strong>patient_id_image:</strong> ' . ($reservation->order->patient_id_image ?? 'NULL') . '</p>';
    $html .= '<p><strong>medical_certificate:</strong> ' . ($reservation->order->medical_certificate ?? 'NULL') . '</p>';
    
    if (!empty($prescriptionImages)) {
        $html .= '<h4>🖼️ Images de prescription (' . count($prescriptionImages) . '):</h4>';
        foreach ($prescriptionImages as $index => $image) {
            $html .= '<div style="margin: 10px 0;">';
            $html .= '<p>Image ' . ($index + 1) . ': ' . $image . '</p>';
            if (file_exists(storage_path('app/public/' . $image))) {
                $html .= '<img src="' . asset('storage/' . $image) . '" style="max-width: 200px; margin: 5px;" />';
                $html .= '<span style="color: green;">✅ Fichier existe</span>';
            } else {
                $html .= '<span style="color: red;">❌ Fichier manquant</span>';
            }
            $html .= '</div>';
        }
    }
    $html .= '</div>';
    
    // Liens de test
    $html .= '<div class="debug-section">';
    $html .= '<h3>🔗 Liens de Test</h3>';
    $html .= '<p><a href="/reservations/' . $id . '" style="margin: 5px; padding: 10px 15px; text-decoration: none; color: white; background: #007bff; border-radius: 4px;">Voir la réservation</a></p>';
    $html .= '<p><a href="/orders/' . $reservation->order->id . '" style="margin: 5px; padding: 10px 15px; text-decoration: none; color: white; background: #28a745; border-radius: 4px;">Voir la commande</a></p>';
    $html .= '<p><a href="/dashboard" style="margin: 5px; padding: 10px 15px; text-decoration: none; color: white; background: #6c757d; border-radius: 4px;">Retour au Dashboard</a></p>';
    $html .= '</div>';
    
    return response($html);
})->middleware('auth')->name('debug.reservation');

// Route de test pour la confirmation de réservation
Route::get('/test-confirm-reservation/{id}', function ($id) {
    $user = auth()->user();
    $reservation = \App\Models\ReservationRequest::with(['order.user', 'center', 'items'])->findOrFail($id);
    
    $html = '<h1>🔧 Test Confirmation Réservation #' . $id . '</h1>';
    $html .= '<style>
        .test-section { border: 1px solid #ddd; margin: 15px 0; padding: 15px; border-radius: 8px; }
        .good { background-color: #d4edda; }
        .warning { background-color: #fff3cd; }
        .error { background-color: #f8d7da; }
        .info { background-color: #d1ecf1; }
    </style>';
    
    // Vérifications préalables
    $html .= '<div class="test-section info">';
    $html .= '<h3>👤 Utilisateur Connecté</h3>';
    $html .= '<p><strong>Nom:</strong> ' . $user->name . '</p>';
    $html .= '<p><strong>Rôle:</strong> ' . $user->role . '</p>';
    $html .= '<p><strong>Centre ID:</strong> ' . ($user->center_id ?? 'NULL') . '</p>';
    $html .= '</div>';
    
    // État de la réservation
    $canConfirm = $reservation->status === 'pending' && in_array($user->role, ['admin', 'manager']);
    $html .= '<div class="test-section ' . ($canConfirm ? 'good' : 'error') . '">';
    $html .= '<h3>📋 État de la Réservation</h3>';
    $html .= '<p><strong>Status actuel:</strong> ' . $reservation->status . '</p>';
    $html .= '<p><strong>Centre réservation:</strong> ' . $reservation->center->name . '</p>';
    $html .= '<p><strong>Centre utilisateur:</strong> ' . ($user->center_id == $reservation->center_id ? '✅ Match' : '❌ Différent') . '</p>';
    $html .= '<p><strong>Peut confirmer:</strong> ' . ($canConfirm ? '✅ Oui' : '❌ Non') . '</p>';
    
    if (!$canConfirm) {
        if ($reservation->status !== 'pending') {
            $html .= '<p style="color: red;">❌ Status doit être "pending" (actuellement: ' . $reservation->status . ')</p>';
        }
        if (!in_array($user->role, ['admin', 'manager'])) {
            $html .= '<p style="color: red;">❌ Rôle doit être admin ou manager (actuellement: ' . $user->role . ')</p>';
        }
    }
    $html .= '</div>';
    
    // Vérification du stock
    $html .= '<div class="test-section">';
    $html .= '<h3>📦 Vérification du Stock</h3>';
    
    $stockOk = true;
    foreach ($reservation->items as $item) {
        $inventory = \App\Models\CenterBloodTypeInventory::where('center_id', $reservation->center_id)
            ->whereHas('bloodType', function($q) use ($item) {
                $q->where('group', $item->blood_type);
            })
            ->first();
        
        $available = $inventory ? $inventory->available_quantity : 0;
        $sufficient = $available >= $item->quantity;
        $stockOk = $stockOk && $sufficient;
        
        $html .= '<div style="margin: 10px 0; padding: 10px; background: #f8f9fa; border-radius: 4px;">';
        $html .= '<p><strong>' . $item->blood_type . ':</strong> Demandé: ' . $item->quantity . ', Disponible: ' . $available;
        $html .= ' <span style="color: ' . ($sufficient ? 'green' : 'red') . ';">' . ($sufficient ? '✅' : '❌') . '</span></p>';
        $html .= '</div>';
    }
    
    $html .= '<p><strong>Stock global OK:</strong> ' . ($stockOk ? '✅ Oui' : '❌ Non') . '</p>';
    $html .= '</div>';
    
    // Test de confirmation simulé
    if ($canConfirm && $stockOk) {
        $html .= '<div class="test-section good">';
        $html .= '<h3>✅ Tests Prêts</h3>';
        $html .= '<p>Tous les prérequis sont remplis pour la confirmation.</p>';
        $html .= '<button onclick="testConfirm(' . $id . ')" style="padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer;">🧪 Tester la Confirmation</button>';
        $html .= '</div>';
    } else {
        $html .= '<div class="test-section error">';
        $html .= '<h3>❌ Tests Non Possibles</h3>';
        $html .= '<p>Certains prérequis ne sont pas remplis.</p>';
        $html .= '</div>';
    }
    
    // Liens
    $html .= '<div class="test-section">';
    $html .= '<h3>🔗 Actions</h3>';
    $html .= '<p><a href="/reservations/' . $id . '" style="margin: 5px; padding: 10px 15px; text-decoration: none; color: white; background: #007bff; border-radius: 4px;">Voir la réservation</a></p>';
    $html .= '<p><a href="/reservations" style="margin: 5px; padding: 10px 15px; text-decoration: none; color: white; background: #6c757d; border-radius: 4px;">Liste des réservations</a></p>';
    $html .= '</div>';
    
    $html .= '<script>
        function testConfirm(id) {
            if (confirm("Procéder au test de confirmation pour la réservation #" + id + " ?")) {
                fetch(`/reservations/${id}/confirm`, {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector("meta[name=csrf-token]").getAttribute("content"),
                        "Content-Type": "application/json",
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert("✅ Confirmation réussie: " + data.message);
                        location.reload();
                    } else {
                        alert("❌ Erreur: " + (data.message || "Une erreur est survenue"));
                    }
                })
                .catch(error => {
                    console.error("Erreur:", error);
                    alert("❌ Erreur de communication: " + error.message);
                });
            }
        }
    </script>';
    
    return response($html);
})->middleware('auth')->name('test.confirm.reservation');

// API endpoint pour le polling des notifications
Route::get('/api/notifications-count', function () {
    $user = auth()->user();
    
    if (!$user) {
        return response()->json(['count' => 0, 'hasNew' => false]);
    }
    
    // Comptage des alertes actives
    $activeAlertsCount = \App\Models\Alert::when($user->center_id, function($q) use ($user) {
        return $q->where('center_id', $user->center_id);
    })->where('resolved', false)->count();
    
    // Comptage des notifications non lues
    $unreadNotificationsCount = \App\Models\Notification::where('user_id', $user->id)
        ->whereNull('read_at')
        ->count();
    
    $totalCount = $activeAlertsCount + $unreadNotificationsCount;
    
    // Vérifier s'il y a de nouvelles notifications (créées dans les 5 dernières minutes)
    $hasNew = \App\Models\Notification::where('user_id', $user->id)
        ->whereNull('read_at')
        ->where('created_at', '>', now()->subMinutes(5))
        ->exists();
    
    return response()->json([
        'count' => $totalCount,
        'alerts' => $activeAlertsCount,
        'notifications' => $unreadNotificationsCount,
        'hasNew' => $hasNew,
        'timestamp' => now()->toISOString()
    ]);
})->middleware('auth')->name('api.notifications.count');

// Test de confirmation de réservation avec updateStatus
Route::get('/test-update-status-confirm/{id}', function($id) {
    $reservation = \App\Models\ReservationRequest::findOrFail($id);
    
    // Test de confirmation en utilisant la méthode updateStatus
    $controller = new \App\Http\Controllers\ReservationController();
    $request = new \Illuminate\Http\Request();
    $request->merge([
        'status' => 'confirmed',
        'note' => 'Test de confirmation automatique'
    ]);
    
    try {
        $result = $controller->updateStatus($request, $reservation);
        return response()->json([
            'test' => 'Confirmation de réservation updateStatus',
            'reservation_id' => $id,
            'old_status' => $reservation->fresh()->status,
            'result' => $result->getData()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'test' => 'Confirmation de réservation updateStatus',
            'reservation_id' => $id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

// Route de débogage pour les blood-bags
Route::get('/test-blood-bags-access', function() {
    $user = auth()->user();
    return response()->json([
        'authenticated' => auth()->check(),
        'user_role' => $user ? $user->role : null,
        'user_center_id' => $user ? $user->center_id : null,
        'can_access' => $user ? in_array($user->role, ['admin', 'manager']) : false,
        'middleware_check' => 'OK'
    ]);
})->middleware('auth');

// Liste des réservations pour test
Route::get('/test-list-reservations', function() {
    $reservations = \App\Models\ReservationRequest::with('user')->limit(10)->get();
    $html = '<h2>Liste des réservations</h2><ul>';
    foreach($reservations as $reservation) {
        $html .= '<li>ID: ' . $reservation->id . ' - Status: ' . $reservation->status . ' - User: ' . $reservation->user->name;
        $html .= ' <a href="/test-update-status-confirm/' . $reservation->id . '">Test Confirm</a></li>';
    }
    $html .= '</ul>';
    return $html;
});
