<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== RÉSERVATIONS CRÉÉES ===\n";
App\Models\ReservationRequest::with(['user', 'center', 'order'])->get()->each(function($reservation) {
    echo "Réservation ID: {$reservation->id}\n";
    echo "  - Order ID: {$reservation->order_id}\n";
    echo "  - User: " . ($reservation->user->name ?? 'N/A') . "\n";
    echo "  - Center: " . ($reservation->center->name ?? 'N/A') . "\n";
    echo "  - Status: {$reservation->status}\n";
    echo "  - Total: {$reservation->total_amount}\n";
    echo "  - Paid: {$reservation->paid_amount}\n";
    echo "  - Expires: {$reservation->expires_at}\n";
    echo "---\n";
});

echo "\n=== ITEMS DE RÉSERVATION ===\n";
App\Models\ReservationItem::with(['bloodType', 'reservationRequest'])->get()->each(function($item) {
    echo "Item ID: {$item->id}\n";
    echo "  - Réservation: {$item->request_id}\n";
    echo "  - Type de sang: " . ($item->bloodType->group ?? 'N/A') . "\n";
    echo "  - Quantité: {$item->quantity}\n";
    echo "  - Prix unitaire: {$item->unit_price}\n";
    echo "  - Prix total: {$item->total_price}\n";
    echo "---\n";
});
