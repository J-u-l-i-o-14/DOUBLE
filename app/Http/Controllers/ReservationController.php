<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ReservationStoreRequest;
use App\Http\Requests\ReservationUpdateRequest;
use App\Http\Requests\ReservationBulkUpdateRequest;
use App\Models\ReservationRequest;
use App\Models\ReservationItem;
use App\Models\ReservationBloodBag;
use App\Models\Center;
use App\Models\BloodType;
use App\Models\BloodBag;
use App\Models\CenterBloodTypeInventory;
use Carbon\Carbon;

class ReservationController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = ReservationRequest::with(['user', 'center', 'items.bloodType', 'order']);

        if ($user->role === 'client') {
            $query->where('user_id', $user->id);
        } elseif (in_array($user->role, ['admin', 'manager'])) {
            $query->where('center_id', $user->center_id);
        }

        // Recherche par ID (réservation ou commande)
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                // Recherche par ID de réservation
                $q->where('id', 'like', '%' . $searchTerm . '%')
                  // Ou par ID de commande associée
                  ->orWhereHas('order', function($orderQuery) use ($searchTerm) {
                      $orderQuery->where('id', 'like', '%' . $searchTerm . '%');
                  });
            });
        }

        // Recherche par nom de client
        if ($request->filled('client_name')) {
            $query->whereHas('user', function($userQuery) use ($request) {
                $userQuery->where('name', 'like', '%' . $request->client_name . '%')
                          ->orWhere('email', 'like', '%' . $request->client_name . '%');
            });
        }

        // Filtres de recherche
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $reservations = $query->latest()->paginate(15);
        $centers = Center::all();

        return view('reservations.index', compact('reservations', 'centers'));
    }

    public function create()
    {
        $centers = Center::all();
        $bloodTypes = BloodType::all();
        
        return view('reservations.create', compact('centers', 'bloodTypes'));
    }

    public function store(ReservationStoreRequest $request)
    {
        $user = auth()->user();
        $validatedData = $request->validated();
        
        $centerId = $user->role === 'client' ? $validatedData['center_id'] : $user->center_id;
        
        // Vérifier la disponibilité et réserver temporairement les poches
        $totalAmount = 0;
        $reservedBloodBags = []; // Pour tracker les poches réservées
        
        try {
            \DB::transaction(function () use ($validatedData, $centerId, $user, &$totalAmount, &$reservedBloodBags) {
                foreach ($validatedData['items'] as $item) {
                    $available = CenterBloodTypeInventory::where('center_id', $centerId)
                        ->where('blood_type_id', $item['blood_type_id'])
                        ->value('available_quantity') ?? 0;
                    
                    if ($available < $item['quantity']) {
                        throw new \Exception("Stock insuffisant pour le groupe sanguin sélectionné.");
                    }
                    
                    // Réserver temporairement les poches lors de la création
                    $bloodBags = BloodBag::where('center_id', $centerId)
                        ->where('blood_type_id', $item['blood_type_id'])
                        ->where('status', 'available')
                        ->lockForUpdate()
                        ->limit($item['quantity'])
                        ->get();
                    
                    if ($bloodBags->count() < $item['quantity']) {
                        throw new \Exception("Stock insuffisant pour le groupe sanguin sélectionné.");
                    }
                    
                    // Marquer comme temporairement réservées (pending)
                    $bloodBagIds = $bloodBags->pluck('id');
                    BloodBag::whereIn('id', $bloodBagIds)->update(['status' => 'pending_reservation']);
                    
                    $reservedBloodBags[$item['blood_type_id']] = $bloodBagIds->toArray();
                    $totalAmount += $item['quantity'] * 5000; // Prix par poche
                }
                
                // Créer la réservation
                $reservation = ReservationRequest::create([
                    'user_id' => $user->id,
                    'center_id' => $centerId,
                    'status' => 'pending',
                    'total_amount' => $totalAmount,
                ]);
                
                // Créer les items
                foreach ($request->items as $item) {
                    ReservationItem::create([
                        'request_id' => $reservation->id,
                        'blood_type_id' => $item['blood_type_id'],
                        'quantity' => $item['quantity'],
                    ]);
                }
                
                // Créer les liens réservation-poches avec statut temporaire
                foreach ($request->items as $item) {
                    $bloodBagIds = $reservedBloodBags[$item['blood_type_id']];
                    foreach ($bloodBagIds as $bloodBagId) {
                        ReservationBloodBag::create([
                            'reservation_id' => $reservation->id,
                            'blood_bag_id' => $bloodBagId,
                        ]);
                    }
                }
                
                // Mettre à jour l'inventaire pour refléter les réservations temporaires
                foreach ($request->items as $item) {
                    $this->updateInventory($centerId, $item['blood_type_id']);
                }
                
                // Stocker l'ID de réservation pour la redirection
                $this->reservationId = $reservation->id;
            });
            
            return redirect()->route('reservations.show', $this->reservationId)
                ->with('success', 'Demande de réservation créée avec succès. Les poches sont temporairement réservées en attente de confirmation.');
                
        } catch (\Exception $e) {
            return back()->withErrors(['items' => $e->getMessage()]);
        }
    }
    
    private $reservationId; // Variable temporaire pour la transaction

    public function show(ReservationRequest $reservation)
    {
        $reservation->load(['user', 'center', 'items.bloodType', 'order', 'updatedBy']);
        
        return view('reservations.show', compact('reservation'));
    }

    public function confirm(ReservationRequest $reservation)
    {
        $user = auth()->user();
        
        // Vérifier les permissions
        if (!in_array($user->role, ['admin', 'manager'])) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé'], 403);
        }
        
        if ($reservation->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Cette réservation ne peut pas être confirmée.']);
        }

        try {
            \DB::transaction(function () use ($reservation, $user) {
                \Log::info('Début confirmation réservation', ['reservation_id' => $reservation->id]);
                
                // 1. Confirmer la réservation
                $reservation->update([
                    'status' => 'confirmed',
                    'manager_notes' => 'Réservation confirmée - Stock réservé',
                    'updated_by' => $user->id,
                    'expires_at' => \Carbon\Carbon::now()->addHours(72), // 72 heures
                ]);

                // 2. Réserver les poches spécifiques et décrémenter le stock
                $this->reserveBloodBagsAndDecrementStock($reservation);

                \Log::info('Fin confirmation réservation', ['reservation_id' => $reservation->id]);
            });
            
            return response()->json(['success' => true, 'message' => 'Réservation confirmée avec succès. Stock décrémenté.']);
            
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la confirmation de réservation', [
                'reservation_id' => $reservation->id, 
                'error' => $e->getMessage()
            ]);
            return response()->json(['success' => false, 'message' => 'Erreur lors de la confirmation : ' . $e->getMessage()]);
        }
    }

    /**
     * Réserver les poches de sang spécifiques et décrémenter le stock
     */
    private function reserveBloodBagsAndDecrementStock($reservation)
    {
        foreach ($reservation->items as $item) {
            \Log::info('Réservation de poches avec décrémentation stock', [
                'reservation_id' => $reservation->id,
                'blood_type_id' => $item->blood_type_id,
                'quantity' => $item->quantity
            ]);
            
            // Sélectionner les poches disponibles avec verrouillage
            $bloodBags = BloodBag::where('center_id', $reservation->center_id)
                ->where('blood_type_id', $item->blood_type_id)
                ->where('status', 'available')
                ->lockForUpdate()
                ->limit($item->quantity)
                ->get();

            if ($bloodBags->count() < $item->quantity) {
                throw new \Exception("Stock insuffisant pour le groupe sanguin {$item->bloodType->group}. Requis: {$item->quantity}, Disponible: {$bloodBags->count()}");
            }

            // Marquer les poches comme réservées
            $bloodBagIds = $bloodBags->pluck('id');
            BloodBag::whereIn('id', $bloodBagIds)->update(['status' => 'reserved']);

            // Créer les liens réservation-poches
            foreach ($bloodBags as $bloodBag) {
                ReservationBloodBag::create([
                    'reservation_id' => $reservation->id,  // Corrigé: utiliser reservation_id au lieu de reservation_request_id
                    'blood_bag_id' => $bloodBag->id,
                ]);
            }

            // Décrémenter l'inventaire du centre
            $this->updateInventory($reservation->center_id, $item->blood_type_id);
        }
    }

    public function updateStatus(Request $request, ReservationRequest $reservation)
    {
        $user = auth()->user();
        
        if (!in_array($user->role, ['admin', 'manager'])) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé'], 403);
        }
        
        // Si c'est un manager, vérifier qu'il gère le bon centre
        if ($user->role === 'manager' && $reservation->center_id !== $user->center_id) {
            return response()->json(['success' => false, 'message' => 'Vous ne pouvez modifier que les réservations de votre centre'], 403);
        }
        
        $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled,completed,expired,terminated',
            'note' => 'nullable|string|max:1000'
        ]);
        
        $oldStatus = $reservation->status;
        
        $reservation->update([
            'status' => $request->status,
            'manager_notes' => $request->note,
            'updated_by' => $user->id
        ]);
        
        // Si la réservation est confirmée, réserver les poches de sang
        if ($request->status === 'confirmed' && $oldStatus !== 'confirmed') {
            try {
                $this->reserveBloodBagsAndDecrementStock($reservation);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la réservation des poches: ' . $e->getMessage()
                ]);
            }
        }
        
        // Si la réservation est annulée ou expirée, libérer les poches de sang
        if (in_array($request->status, ['cancelled', 'expired']) && $oldStatus !== $request->status) {
            $this->releaseBloodBags($reservation);
        }
        
        // Si la réservation est complétée (retrait effectué), finaliser le paiement
        if ($request->status === 'completed' && $oldStatus !== 'completed') {
            $this->completeReservation($reservation);
        }
        
        // Si la réservation passe à terminé, marquer comme finalisée
        if ($request->status === 'terminated' && $oldStatus !== 'terminated') {
            $this->terminateReservation($reservation);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Statut mis à jour avec succès',
            'new_status' => $request->status
        ]);
    }

    /**
     * Libérer les poches de sang réservées et restaurer le stock
     */
    private function releaseBloodBags(ReservationRequest $reservation)
    {
        \Log::info('Début libération des poches de sang', [
            'reservation_id' => $reservation->id,
            'status' => $reservation->status
        ]);
        
        try {
            \DB::transaction(function () use ($reservation) {
                $bloodBagIds = $reservation->bloodBags()->pluck('blood_bag_id');
                
                if ($bloodBagIds->isNotEmpty()) {
                    // Remettre les poches comme disponibles
                    $releasedCount = BloodBag::whereIn('id', $bloodBagIds)
                        ->where('status', 'reserved')
                        ->update(['status' => 'available']);
                    
                    \Log::info('Poches libérées', [
                        'reservation_id' => $reservation->id,
                        'blood_bag_ids' => $bloodBagIds->toArray(),
                        'released_count' => $releasedCount
                    ]);
                    
                    // Mettre à jour l'inventaire pour chaque type sanguin
                    $bloodTypeIds = BloodBag::whereIn('id', $bloodBagIds)->distinct()->pluck('blood_type_id');
                    
                    foreach ($bloodTypeIds as $bloodTypeId) {
                        $availableCountBefore = CenterBloodTypeInventory::where('center_id', $reservation->center_id)
                            ->where('blood_type_id', $bloodTypeId)
                            ->value('available_quantity') ?? 0;
                        
                        $this->updateInventory($reservation->center_id, $bloodTypeId);
                        
                        $availableCountAfter = CenterBloodTypeInventory::where('center_id', $reservation->center_id)
                            ->where('blood_type_id', $bloodTypeId)
                            ->value('available_quantity') ?? 0;
                        
                        \Log::info('Inventaire mis à jour', [
                            'reservation_id' => $reservation->id,
                            'center_id' => $reservation->center_id,
                            'blood_type_id' => $bloodTypeId,
                            'available_before' => $availableCountBefore,
                            'available_after' => $availableCountAfter,
                            'difference' => $availableCountAfter - $availableCountBefore
                        ]);
                    }
                    
                    // Supprimer les liens de réservation
                    $reservation->bloodBags()->delete();
                    
                    \Log::info('Liens de réservation supprimés', [
                        'reservation_id' => $reservation->id
                    ]);
                } else {
                    \Log::info('Aucune poche de sang à libérer', [
                        'reservation_id' => $reservation->id
                    ]);
                }
            });
            
            \Log::info('Fin libération des poches de sang - Succès', [
                'reservation_id' => $reservation->id
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la libération des poches de sang', [
                'reservation_id' => $reservation->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Mettre à jour plusieurs réservations en lot
     */
    public function bulkUpdateStatus(ReservationBulkUpdateRequest $request)
    {
        $user = auth()->user();
        
        if (!in_array($user->role, ['admin', 'manager'])) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé'], 403);
        }
        
        $validatedData = $request->validated();
        
        $query = ReservationRequest::whereIn('id', $validatedData['reservation_ids']);
        
        // Si c'est un manager, filtrer par centre
        if ($user->role === 'manager') {
            $query->where('center_id', $user->center_id);
        }
        
        $reservations = $query->get();
        
        foreach ($reservations as $reservation) {
            $oldStatus = $reservation->status;
            $reservation->update([
                'status' => $validatedData['status'],
                'updated_by' => $user->id
            ]);
            
            // Si la réservation est annulée ou expirée, libérer les poches de sang
            if (in_array($validatedData['status'], ['cancelled', 'expired']) && $oldStatus !== $validatedData['status']) {
                $this->releaseBloodBags($reservation);
            }
            
            // Si la réservation est complétée (retrait effectué), finaliser le paiement
            if ($validatedData['status'] === 'completed' && $oldStatus !== 'completed') {
                $this->completePayment($reservation);
            }
        }
        
        return response()->json([
            'success' => true,
            'message' => count($reservations) . ' réservation(s) mise(s) à jour'
        ]);
    }

    /**
     * Compléter le paiement lors du retrait des poches
     */
    private function completePayment(ReservationRequest $reservation)
    {
        if ($reservation->order) {
            $order = $reservation->order;
            
            // Si le paiement est partiel, calculer le montant restant
            if ($order->payment_status === 'partial') {
                $remainingAmount = $order->original_price - $order->total_amount;
                
                if ($remainingAmount > 0) {
                    // Mettre à jour le montant total et le statut
                    $order->update([
                        'total_amount' => $order->original_price,
                        'payment_status' => 'paid',
                        'payment_completed_at' => now()
                    ]);
                    
                    \Log::info('Paiement complété lors du retrait', [
                        'order_id' => $order->id,
                        'remaining_amount' => $remainingAmount,
                        'total_amount' => $order->original_price
                    ]);
                }
            }
        }
    }

    private function updateInventory($centerId, $bloodTypeId)
    {
        $availableCount = BloodBag::where('center_id', $centerId)
            ->where('blood_type_id', $bloodTypeId)
            ->where('status', 'available')
            ->count();

        $reservedCount = BloodBag::where('center_id', $centerId)
            ->where('blood_type_id', $bloodTypeId)
            ->where('status', 'reserved')
            ->count();

        CenterBloodTypeInventory::updateOrCreate(
            [
                'center_id' => $centerId,
                'blood_type_id' => $bloodTypeId,
            ],
            [
                'available_quantity' => $availableCount,
                'reserved_quantity' => $reservedCount,
            ]
        );
    }

    /**
     * Finaliser une réservation complétée
     */
    private function completeReservation(ReservationRequest $reservation)
    {
        \Log::info('Finalisation de la réservation', ['reservation_id' => $reservation->id]);
        
        try {
            \DB::transaction(function () use ($reservation) {
                // Marquer les poches comme transfusées
                $bloodBagIds = $reservation->bloodBags()->pluck('blood_bag_id');
                
                if ($bloodBagIds->isNotEmpty()) {
                    BloodBag::whereIn('id', $bloodBagIds)
                        ->update(['status' => 'transfused']);
                    
                    \Log::info('Poches marquées comme transfusées', [
                        'reservation_id' => $reservation->id,
                        'blood_bag_count' => $bloodBagIds->count()
                    ]);
                }
                
                // Mettre à jour la commande associée
                if ($reservation->order) {
                    // Seules les réservations COMPLETED doivent être marquées comme payées intégralement
                    $reservation->order->update([
                        'status' => 'completed',
                        'payment_status' => 'paid',
                        'deposit_amount' => $reservation->order->total_amount,
                        'remaining_amount' => 0
                    ]);
                }
                
                // Mettre à jour les inventaires
                $bloodTypeIds = BloodBag::whereIn('id', $bloodBagIds)->distinct()->pluck('blood_type_id');
                foreach ($bloodTypeIds as $bloodTypeId) {
                    $this->updateInventory($reservation->center_id, $bloodTypeId);
                }
            });
            
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la finalisation de réservation', [
                'reservation_id' => $reservation->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Terminer une réservation (statut final)
     */
    private function terminateReservation(ReservationRequest $reservation)
    {
        \Log::info('Terminaison de la réservation', ['reservation_id' => $reservation->id]);
        
        try {
            \DB::transaction(function () use ($reservation) {
                if ($reservation->order) {
                    // Statut final (terminé) => paiement intégral confirmé
                    $reservation->order->update([
                        'status' => 'terminated',
                        'payment_status' => 'paid',
                        'deposit_amount' => $reservation->order->total_amount,
                        'remaining_amount' => 0
                    ]);
                }
                
                $reservation->update([
                    'manager_notes' => ($reservation->manager_notes ?? '') . ' | Terminée le ' . \Carbon\Carbon::now()->format('d/m/Y H:i')
                ]);
            });
            
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la terminaison de réservation', [
                'reservation_id' => $reservation->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Vérifier et marquer les réservations expirées
     */
    public function checkExpiredReservations()
    {
        try {
            $expiredReservations = ReservationRequest::where('status', 'confirmed')
                ->where('expires_at', '<', \Carbon\Carbon::now())
                ->get();

            $count = 0;
            foreach ($expiredReservations as $reservation) {
                \DB::transaction(function () use ($reservation) {
                    $reservation->update([
                        'status' => 'expired',
                        'manager_notes' => ($reservation->manager_notes ?? '') . ' | Expirée automatiquement le ' . \Carbon\Carbon::now()->format('d/m/Y H:i')
                    ]);
                    
                    // Libérer les poches de sang
                    $this->releaseBloodBags($reservation);
                    
                    // Mettre à jour la commande associée et éliminer le montant restant
                    if ($reservation->order) {
                        $reservation->order->update([
                            'status' => 'expired'
                            // On ne touche pas à payment_status, deposit_amount ni remaining_amount
                        ]);
                    }
                });
                
                $count++;
            }

            \Log::info('Vérification des expirations terminée', ['expired_count' => $count]);
            
            return response()->json([
                'success' => true,
                'message' => "{$count} réservations expirées traitées",
                'expired_count' => $count
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur lors de la vérification des expirations', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la vérification des expirations: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Annuler une réservation
     */
    public function cancel(ReservationRequest $reservation)
    {
        \Log::info('Annulation de la réservation', ['reservation_id' => $reservation->id]);
        
        try {
            \DB::transaction(function () use ($reservation) {
                // Libérer les poches de sang
                $this->releaseBloodBags($reservation);
                
                // Mettre à jour la réservation
                $reservation->update([
                    'status' => 'cancelled',
                    'manager_notes' => ($reservation->manager_notes ?? '') . ' | Annulée le ' . \Carbon\Carbon::now()->format('d/m/Y H:i')
                ]);
                
                // Mettre à jour la commande associée et éliminer le montant restant
                if ($reservation->order) {
                    $reservation->order->update([
                        'status' => 'cancelled'
                        // On conserve payment_status, deposit_amount et remaining_amount
                    ]);
                }
            });
            
            return response()->json([
                'success' => true,
                'message' => 'Réservation annulée avec succès'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Erreur lors de l\'annulation de réservation', [
                'reservation_id' => $reservation->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'annulation: ' . $e->getMessage()
            ]);
        }
    }
}