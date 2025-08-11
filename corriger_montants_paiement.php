<?php

/**
 * Script pour corriger les montants de paiement des réservations expirées/annulées/terminées
 * Les commandes avec statut final (expired, cancelled, terminated, completed) doivent avoir un montant restant à 0
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\ReservationRequest;
use App\Models\Order;
use Carbon\Carbon;

// Configuration Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔧 CORRECTION DES MONTANTS DE PAIEMENT\n";
echo "=" . str_repeat("=", 50) . "\n\n";

echo "📊 ÉTAT INITIAL:\n";
echo "-" . str_repeat("-", 20) . "\n";

// Statistiques initiales
$pendingRevenueBefore = ReservationRequest::whereIn('status', ['pending', 'confirmed'])
    ->whereHas('order', function($q) {
        $q->whereIn('payment_status', ['pending', 'partial']);
    })
    ->with('order')
    ->get()
    ->sum(function($reservation) {
        if ($reservation->order) {
            // Utiliser remaining_amount ou calculer à partir de total_amount - deposit_amount
            $remaining = $reservation->order->remaining_amount ?? 
                        ($reservation->order->total_amount - ($reservation->order->deposit_amount ?? 0));
            return max(0, $remaining);
        }
        return 0;
    });

echo "Revenus en attente avant correction: " . number_format($pendingRevenueBefore) . " F CFA\n\n";

try {
    DB::transaction(function () {
        echo "🔄 CORRECTION EN COURS...\n";
        echo "-" . str_repeat("-", 30) . "\n";
        
        // 1. Corriger les commandes liées aux réservations finalisées
        $finalStatuses = ['expired', 'cancelled', 'terminated', 'completed'];
        
        $reservationsToFix = ReservationRequest::whereIn('status', $finalStatuses)
            ->whereHas('order', function($q) {
                $q->whereIn('payment_status', ['pending', 'partial'])
                  ->where(function($query) {
                      $query->whereNotNull('remaining_amount')
                            ->where('remaining_amount', '>', 0)
                            ->orWhereRaw('total_amount > COALESCE(deposit_amount, 0)');
                  });
            })
            ->with('order')
            ->get();
        
        echo "Réservations finalisées à corriger: " . $reservationsToFix->count() . "\n";
        
        $correctedOrders = 0;
        $totalCorrectedAmount = 0;
        
        foreach ($reservationsToFix as $reservation) {
            if ($reservation->order) {
                $oldRemaining = $reservation->order->remaining_amount ?? 
                               ($reservation->order->total_amount - ($reservation->order->deposit_amount ?? 0));
                
                // Marquer comme payé intégralement pour éliminer le montant restant
                $reservation->order->update([
                    'deposit_amount' => $reservation->order->total_amount,
                    'remaining_amount' => 0,
                    'payment_status' => 'paid',
                    'status' => $reservation->status // Synchroniser le statut
                ]);
                
                $correctedOrders++;
                $totalCorrectedAmount += $oldRemaining;
                
                echo "  ✅ Réservation #{$reservation->id} ({$reservation->status}): {$oldRemaining} F CFA → 0 F CFA\n";
            }
        }
        
        // 2. Marquer les commandes orphelines avec statuts finaux
        $orphanOrders = Order::whereNotIn('status', ['pending', 'confirmed'])
            ->whereIn('payment_status', ['pending', 'partial'])
            ->where(function($query) {
                $query->whereNotNull('remaining_amount')
                      ->where('remaining_amount', '>', 0)
                      ->orWhereRaw('total_amount > COALESCE(deposit_amount, 0)');
            })
            ->get();
        
        echo "\nCommandes orphelines à corriger: " . $orphanOrders->count() . "\n";
        
        foreach ($orphanOrders as $order) {
            $oldRemaining = $order->remaining_amount ?? 
                           ($order->total_amount - ($order->deposit_amount ?? 0));
            
            $order->update([
                'deposit_amount' => $order->total_amount,
                'remaining_amount' => 0,
                'payment_status' => 'paid'
            ]);
            
            $correctedOrders++;
            $totalCorrectedAmount += $oldRemaining;
            
            echo "  ✅ Commande #{$order->id} ({$order->status}): {$oldRemaining} F CFA → 0 F CFA\n";
        }
        
        echo "\n📈 RÉSUMÉ:\n";
        echo "-" . str_repeat("-", 15) . "\n";
        echo "Commandes corrigées: {$correctedOrders}\n";
        echo "Montant total éliminé: " . number_format($totalCorrectedAmount) . " F CFA\n\n";
    });
    
    // Statistiques finales
    echo "📊 ÉTAT FINAL:\n";
    echo "-" . str_repeat("-", 20) . "\n";
    
    $pendingRevenueAfter = ReservationRequest::whereIn('status', ['pending', 'confirmed'])
        ->whereHas('order', function($q) {
            $q->whereIn('payment_status', ['pending', 'partial'])
              ->whereNotIn('status', ['expired', 'cancelled', 'terminated', 'completed']);
        })
        ->with('order')
        ->get()
        ->sum(function($reservation) {
            if ($reservation->order) {
                $remaining = $reservation->order->remaining_amount ?? 
                            ($reservation->order->total_amount - ($reservation->order->deposit_amount ?? 0));
                return max(0, $remaining);
            }
            return 0;
        });
    
    echo "Revenus en attente après correction: " . number_format($pendingRevenueAfter) . " F CFA\n";
    echo "Différence: " . number_format($pendingRevenueBefore - $pendingRevenueAfter) . " F CFA éliminés\n\n";
    
    // Vérifications finales
    $remainingProblems = Order::whereNotIn('status', ['pending', 'confirmed'])
        ->whereIn('payment_status', ['pending', 'partial'])
        ->where(function($query) {
            $query->whereNotNull('remaining_amount')
                  ->where('remaining_amount', '>', 0)
                  ->orWhereRaw('total_amount > COALESCE(deposit_amount, 0)');
        })
        ->count();
    
    if ($remainingProblems == 0) {
        echo "✅ CORRECTION RÉUSSIE - Aucun problème de paiement restant!\n";
    } else {
        echo "⚠️  {$remainingProblems} problèmes de paiement restants à vérifier.\n";
    }
    
} catch (Exception $e) {
    echo "\n❌ ERREUR: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "🎯 CORRECTION DES MONTANTS DE PAIEMENT TERMINÉE\n";
echo str_repeat("=", 60) . "\n\n";
