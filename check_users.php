<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 Vérification des utilisateurs admin\n";
echo "===================================\n";

$admins = App\Models\User::where('role', 'admin')->get(['id', 'name', 'email', 'center_id', 'role']);

foreach($admins as $admin) {
    echo "Admin ID {$admin->id}: {$admin->name}\n";
    echo "  - Email: {$admin->email}\n";
    echo "  - Center ID: " . ($admin->center_id ?? 'NULL') . "\n";
    echo "  - Role: {$admin->role}\n";
    echo "  ---\n";
}

echo "\n🏥 Centres disponibles\n";
echo "==================\n";

$centers = App\Models\Center::all(['id', 'name']);
foreach($centers as $center) {
    echo "Centre {$center->id}: {$center->name}\n";
}

echo "\n💰 Commandes par centre\n";
echo "===================\n";

$ordersByCenter = App\Models\Order::selectRaw('center_id, COUNT(*) as count, SUM(total_amount) as total')
    ->groupBy('center_id')
    ->get();

foreach($ordersByCenter as $stat) {
    $centerName = App\Models\Center::find($stat->center_id)->name ?? 'Centre inconnu';
    echo "Centre {$stat->center_id} ({$centerName}): {$stat->count} commandes, Total: {$stat->total} F CFA\n";
}

echo "\n=== TOUS LES CENTRES ===\n";
App\Models\Center::all(['id', 'name'])->each(function($center) {
    echo "Centre {$center->id}: {$center->name}\n";
});

echo "\n=== TEST MOT DE PASSE ===\n";
$testUser = App\Models\User::where('email', 'admin@bloodbank.com')->first();
if ($testUser) {
    echo "Utilisateur trouvé: {$testUser->name}\n";
    echo "Hash du mot de passe: " . $testUser->password . "\n";
    echo "Test 'password': " . (Hash::check('password', $testUser->password) ? 'OK' : 'NOK') . "\n";
    echo "Test 'admin': " . (Hash::check('admin', $testUser->password) ? 'OK' : 'NOK') . "\n";
} else {
    echo "Aucun utilisateur avec email admin@bloodbank.com\n";
}
