<?php

require_once __DIR__ . '/vendor/autoload.php';

// Configuration Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$order = App\Models\Order::find(4);
if($order) {
    $order->update([
        'remaining_amount' => 0, 
        'deposit_amount' => $order->total_amount
    ]);
    echo "✅ Commande #4 corrigée: remaining_amount = 0\n";
} else {
    echo "❌ Commande #4 non trouvée\n";
}
