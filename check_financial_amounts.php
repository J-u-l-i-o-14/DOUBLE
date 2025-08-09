<?php

require_once 'vendor/autoload.php';

// Configuration de l'application Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Order;
use App\Models\ReservationRequest;
use Carbon\Carbon;

echo "=== VÉRIFICATION DES MONTANTS FINANCIERS ===\n";
echo "Date: " . now()->format('d/m/Y H:i') . "\n\n";

echo "📊 ANALYSE DES COMMANDES ET PAIEMENTS:\n";
echo "=====================================\n";

$orders = Order::with(['reservationRequest', 'center', 'user'])->get();
$totalPaid = 0;
$totalRemaining = 0;
$totalOriginal = 0;

foreach ($orders as $order) {
    echo "Commande #{$order->id}:\n";
    echo "  - Client: {$order->user->name}\n";
    echo "  - Centre: " . ($order->center ? $order->center->name : 'N/A') . "\n";
    echo "  - Montant original: {$order->original_price} F CFA\n";
    echo "  - Montant payé: {$order->total_amount} F CFA\n";
    echo "  - Statut paiement: {$order->payment_status}\n";
    
    if ($order->reservationRequest) {
        echo "  - Statut réservation: {$order->reservationRequest->status}\n";
        echo "  - Date réservation: " . $order->reservationRequest->created_at->format('d/m/Y H:i') . "\n";
    } else {
        echo "  - ❌ Pas de réservation associée\n";
    }
    
    $remaining = $order->original_price - $order->total_amount;
    echo "  - Reste à payer: {$remaining} F CFA\n";
    echo "  - Créée le: " . $order->created_at->format('d/m/Y H:i') . "\n";
    
    $totalPaid += $order->total_amount;
    $totalRemaining += $remaining;
    $totalOriginal += $order->original_price;
    
    echo "\n";
}

echo "💰 TOTAUX GLOBAUX:\n";
echo "==================\n";
echo "Total original: {$totalOriginal} F CFA\n";
echo "Total payé: {$totalPaid} F CFA\n";
echo "Total restant: {$totalRemaining} F CFA\n";
echo "Vérification: " . ($totalPaid + $totalRemaining) . " = {$totalOriginal} F CFA\n\n";

echo "🎯 OBJECTIFS ATTENDUS:\n";
echo "======================\n";
echo "Attendu payé: 37500 F CFA\n";
echo "Attendu restant: 2500 F CFA\n";
echo "Différence payé: " . ($totalPaid - 37500) . " F CFA\n";
echo "Différence restant: " . ($totalRemaining - 2500) . " F CFA\n\n";

echo "📋 ANALYSE PAR STATUT DE RÉSERVATION:\n";
echo "=====================================\n";

$statusStats = [
    'pending' => ['count' => 0, 'paid' => 0, 'remaining' => 0],
    'confirmed' => ['count' => 0, 'paid' => 0, 'remaining' => 0],
    'completed' => ['count' => 0, 'paid' => 0, 'remaining' => 0],
    'cancelled' => ['count' => 0, 'paid' => 0, 'remaining' => 0],
    'no_reservation' => ['count' => 0, 'paid' => 0, 'remaining' => 0]
];

foreach ($orders as $order) {
    $status = $order->reservationRequest ? $order->reservationRequest->status : 'no_reservation';
    $remaining = $order->original_price - $order->total_amount;
    
    if (isset($statusStats[$status])) {
        $statusStats[$status]['count']++;
        $statusStats[$status]['paid'] += $order->total_amount;
        $statusStats[$status]['remaining'] += $remaining;
    }
}

foreach ($statusStats as $status => $stats) {
    if ($stats['count'] > 0) {
        echo "Statut '{$status}':\n";
        echo "  - Nombre: {$stats['count']}\n";
        echo "  - Payé: {$stats['paid']} F CFA\n";
        echo "  - Restant: {$stats['remaining']} F CFA\n\n";
    }
}

echo "🔧 CALCUL DASHBOARD ACTUEL:\n";
echo "===========================\n";

// Simuler le calcul du dashboard
$centerFilter = 1; // Supposons centre ID 1

$dashboardTotalRevenue = Order::where('center_id', $centerFilter)
    ->where('payment_status', '!=', 'failed')
    ->sum(DB::raw('CASE 
        WHEN payment_status = "partial" THEN COALESCE(deposit_amount, total_amount * 0.5, 0)
        WHEN payment_status = "paid" THEN COALESCE(total_amount, 0)
        ELSE 0 
    END'));

$dashboardPendingRevenue = ReservationRequest::where('center_id', $centerFilter)
    ->whereIn('status', ['pending', 'confirmed'])
    ->whereHas('order', function($q) {
        $q->where('payment_status', 'partial');
    })
    ->with('order')
    ->get()
    ->sum(function($reservation) {
        if ($reservation->order) {
            return $reservation->order->original_price - $reservation->order->total_amount;
        }
        return 0;
    });

echo "Dashboard Total Revenue: {$dashboardTotalRevenue} F CFA\n";
echo "Dashboard Pending Revenue: {$dashboardPendingRevenue} F CFA\n";

echo "\n=== FIN DE L'ANALYSE ===\n";
