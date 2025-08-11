<?php
require 'vendor/autoload.php';
require 'bootstrap/app.php';

$orders = App\Models\Order::latest()->take(5)->get([
    'id', 'total_amount', 'original_price', 'deposit_amount', 
    'remaining_amount', 'payment_status', 'created_at'
]);

echo "Vérification des données de commandes récentes:\n";
echo "=====================================\n";

foreach($orders as $order) {
    echo "Order {$order->id} (créée: {$order->created_at}):\n";
    echo "  - total_amount: {$order->total_amount}\n";
    echo "  - original_price: {$order->original_price}\n";
    echo "  - deposit_amount: {$order->deposit_amount}\n";
    echo "  - remaining_amount: {$order->remaining_amount}\n";
    echo "  - payment_status: {$order->payment_status}\n";
    echo "  ---\n";
}

// Vérifier aussi les ReservationRequests liées
echo "\nVérification des ReservationRequests récentes:\n";
echo "=====================================\n";

$requests = App\Models\ReservationRequest::latest()->take(3)->get([
    'id', 'order_id', 'total_price', 'status', 'created_at'
]);

foreach($requests as $request) {
    echo "ReservationRequest {$request->id} (order_id: {$request->order_id}):\n";
    echo "  - total_price: {$request->total_price}\n";
    echo "  - status: {$request->status}\n";
    echo "  - created_at: {$request->created_at}\n";
    echo "  ---\n";
}
