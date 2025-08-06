<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route de test pour vérifier le système
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
        
        return view('test-system', $data);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ], 500);
    }
});
