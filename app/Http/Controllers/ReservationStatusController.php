<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReservationStatusController extends Controller
{
    /**
     * Afficher la liste des réservations avec possibilité de mise à jour des statuts
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = Order::with(['user', 'center'])
            ->orderBy('created_at', 'desc');
        
        // Filtrer par statut si spécifié
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }
        
        // Filtrer par groupe sanguin si spécifié
        if ($request->has('blood_type') && $request->blood_type != '') {
            $query->where('blood_type', $request->blood_type);
        }
        
        // Si gestionnaire, filtrer par centre
        if ($user->is_manager && $user->center_id) {
            $query->whereHas('center', function($q) use ($user) {
                $q->where('id', $user->center_id);
            });
        }
        
        $reservations = $query->paginate(20);
        
        // Statistiques pour les filtres
        $statusStats = [
            'all' => Order::count(),
            'pending' => Order::where('status', 'pending')->count(),
            'confirmed' => Order::where('status', 'confirmed')->count(),
            'completed' => Order::where('status', 'completed')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
            'expired' => Order::where('status', 'expired')->count(),
        ];
        
        return view('reservations.status-management', compact('reservations', 'statusStats'));
    }
    
    /**
     * Mettre à jour le statut d'une réservation
     */
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,completed,cancelled,expired',
            'notes' => 'nullable|string|max:500'
        ]);
        
        $user = Auth::user();
        
        // Vérifier les permissions
        if ($user->is_manager && $order->center_id !== $user->center_id) {
            abort(403, 'Vous ne pouvez pas modifier cette réservation.');
        }
        
        $previousStatus = $order->status;
        
        // Mettre à jour le statut
        $order->update([
            'status' => $request->status,
            'notes' => $request->notes ? $order->notes . "\n[" . now()->format('d/m/Y H:i') . "] " . $request->notes : $order->notes,
        ]);
        
        // Si le statut change vers "confirmé", on peut aussi mettre à jour le document_status
        if ($request->status === 'confirmed' && $order->document_status === 'pending') {
            $order->update([
                'document_status' => 'approved',
                'validated_by' => $user->id,
                'validated_at' => now(),
                'validation_notes' => 'Approuvé automatiquement lors de la confirmation'
            ]);
        }
        
        // Créer une notification pour le client si le statut change
        if ($previousStatus !== $request->status) {
            \App\Models\Notification::create([
                'user_id' => $order->user_id,
                'type' => 'reservation_status_changed',
                'title' => 'Statut de réservation mis à jour',
                'message' => "Votre réservation #{$order->id} est maintenant: " . $order->status_label,
            ]);
        }
        
        return redirect()->back()->with('success', 'Statut de la réservation mis à jour avec succès.');
    }
    
    /**
     * Marquer plusieurs réservations comme expirées
     */
    public function markExpired(Request $request)
    {
        $request->validate([
            'reservation_ids' => 'required|array',
            'reservation_ids.*' => 'exists:orders,id'
        ]);
        
        $user = Auth::user();
        $query = Order::whereIn('id', $request->reservation_ids);
        
        // Si gestionnaire, filtrer par centre
        if ($user->is_manager && $user->center_id) {
            $query->whereHas('center', function($q) use ($user) {
                $q->where('id', $user->center_id);
            });
        }
        
        $count = $query->update([
            'status' => 'expired',
            'notes' => \DB::raw("CONCAT(IFNULL(notes, ''), '\n[" . now()->format('d/m/Y H:i') . "] Marqué comme expiré par " . $user->name . "')")
        ]);
        
        return redirect()->back()->with('success', "$count réservation(s) marquée(s) comme expirée(s).");
    }
}
