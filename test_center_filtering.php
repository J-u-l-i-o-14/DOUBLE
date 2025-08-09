<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== RÉPARTITION DES DONNÉES PAR CENTRE ===\n\n";

echo "ORDERS (Commandes) par centre:\n";
App\Models\Order::selectRaw('center_id, COUNT(*) as count, SUM(total_amount) as total_revenue')
    ->groupBy('center_id')
    ->get()
    ->each(function($stat) {
        $center = App\Models\Center::find($stat->center_id);
        $centerName = $center ? $center->name : 'Inconnu';
        echo "  Centre {$stat->center_id} ({$centerName}): {$stat->count} commandes, {$stat->total_revenue} F CFA\n";
    });

echo "\nRESERVATIONS par centre:\n";
App\Models\ReservationRequest::selectRaw('center_id, COUNT(*) as count, SUM(total_amount) as total_amount')
    ->groupBy('center_id')
    ->get()
    ->each(function($stat) {
        $center = App\Models\Center::find($stat->center_id);
        $centerName = $center ? $center->name : 'Inconnu';
        echo "  Centre {$stat->center_id} ({$centerName}): {$stat->count} réservations, {$stat->total_amount} F CFA\n";
    });

echo "\n=== EXEMPLE FILTRAGE POUR MANAGER CENTER 1 ===\n";
$managerId = 2; // Manager CHU SO
$manager = App\Models\User::find($managerId);
echo "Manager: {$manager->name} (Centre: {$manager->center_id})\n\n";

$orders = App\Models\Order::where('center_id', $manager->center_id)->count();
$reservations = App\Models\ReservationRequest::where('center_id', $manager->center_id)->count();

echo "Commandes visibles par ce manager: {$orders}\n";
echo "Réservations visibles par ce manager: {$reservations}\n";
