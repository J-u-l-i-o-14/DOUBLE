<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== EXPLORATION DE LA BASE DE DONNÉES SQLite ===\n\n";

// Obtenir toutes les tables
$tables = \DB::select("SELECT name FROM sqlite_master WHERE type='table'");

echo "TABLES DISPONIBLES :\n";
foreach ($tables as $table) {
    $count = \DB::table($table->name)->count();
    echo "  - {$table->name} ({$count} lignes)\n";
}

echo "\n=== DONNÉES PRINCIPALES ===\n\n";

echo "UTILISATEURS :\n";
\DB::table('users')->limit(5)->get()->each(function($user) {
    echo "  ID: {$user->id} | {$user->name} | {$user->role} | Centre: {$user->center_id}\n";
});

echo "\nCENTRES :\n";
\DB::table('centers')->get()->each(function($center) {
    echo "  ID: {$center->id} | {$center->name} | {$center->address}\n";
});

echo "\nCOMMANDES (Orders) :\n";
\DB::table('orders')->get()->each(function($order) {
    echo "  ID: {$order->id} | User: {$order->user_id} | Centre: {$order->center_id} | Montant: {$order->total_amount} | Status: {$order->payment_status}\n";
});

echo "\nRÉSERVATIONS :\n";
\DB::table('reservation_requests')->get()->each(function($reservation) {
    echo "  ID: {$reservation->id} | Order: {$reservation->order_id} | User: {$reservation->user_id} | Centre: {$reservation->center_id} | Status: {$reservation->status}\n";
});

echo "\nTYPES DE SANG :\n";
\DB::table('blood_types')->get()->each(function($type) {
    echo "  ID: {$type->id} | Groupe: {$type->group}\n";
});

echo "\nITEMS DE RÉSERVATION :\n";
\DB::table('reservation_items')->join('blood_types', 'reservation_items.blood_type_id', '=', 'blood_types.id')
    ->select('reservation_items.*', 'blood_types.group')
    ->get()->each(function($item) {
        echo "  Réservation: {$item->request_id} | Type: {$item->group} | Quantité: {$item->quantity}\n";
    });
