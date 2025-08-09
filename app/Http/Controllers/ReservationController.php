<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
        $query = ReservationRequest::with(['user', 'center', 'items.bloodType']);

        if ($user->role === 'client') {
            $query->where('user_id', $user->id);
        } elseif (in_array($user->role, ['admin', 'manager'])) {
            $query->where('center_id', $user->center_id);
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

    public function store(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.blood_type_id' => 'required|exists:blood_types,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);
        $centerId = $user->role === 'client' ? $request->center_id : $user->center_id;
        
        // Vérifier la disponibilité et réserver temporairement les poches
        $totalAmount = 0;
        $reservedBloodBags = []; // Pour tracker les poches réservées
        
        try {
            \DB::transaction(function () use ($request, $centerId, $user, &$totalAmount, &$reservedBloodBags) {
                foreach ($request->items as $item) {
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
            'status' => 'required|in:pending,confirmed,cancelled,completed,expired',
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
            $this->completePayment($reservation);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Statut mis à jour avec succès',
            'new_status' => $request->status
        ]);
    }

    /**
     * Libérer les poches de sang réservées
     */
    private function releaseBloodBags(ReservationRequest $reservation)
    {
        $bloodBagIds = $reservation->reservationBloodBags()->pluck('blood_bag_id');
        
        // Remettre les poches comme disponibles
        BloodBag::whereIn('id', $bloodBagIds)->update(['status' => 'available']);
        
        // Mettre à jour l'inventaire
        $bloodTypeIds = BloodBag::whereIn('id', $bloodBagIds)->distinct()->pluck('blood_type_id');
        
        foreach ($bloodTypeIds as $bloodTypeId) {
            $this->updateInventory($reservation->center_id, $bloodTypeId);
        }
    }

    /**
     * Mettre à jour plusieurs réservations en lot
     */
    public function bulkUpdateStatus(Request $request)
    {
        $user = auth()->user();
        
        if (!in_array($user->role, ['admin', 'manager'])) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé'], 403);
        }
        
        $request->validate([
            'reservation_ids' => 'required|array',
            'reservation_ids.*' => 'exists:reservation_requests,id',
            'status' => 'required|in:confirmed,cancelled,completed,expired'
        ]);
        
        $query = ReservationRequest::whereIn('id', $request->reservation_ids);
        
        // Si c'est un manager, filtrer par centre
        if ($user->role === 'manager') {
            $query->where('center_id', $user->center_id);
        }
        
        $reservations = $query->get();
        
        foreach ($reservations as $reservation) {
            $oldStatus = $reservation->status;
            $reservation->update([
                'status' => $request->status,
                'updated_by' => $user->id
            ]);
            
            // Si la réservation est annulée ou expirée, libérer les poches de sang
            if (in_array($request->status, ['cancelled', 'expired']) && $oldStatus !== $request->status) {
                $this->releaseBloodBags($reservation);
            }
            
            // Si la réservation est complétée (retrait effectué), finaliser le paiement
            if ($request->status === 'completed' && $oldStatus !== 'completed') {
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

        CenterBloodTypeInventory::updateOrCreate(
            [
                'center_id' => $centerId,
                'blood_type_id' => $bloodTypeId,
            ],
            [
                'available_quantity' => $availableCount,
            ]
        );
    }
}