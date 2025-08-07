<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Cart;
use App\Models\CenterBloodTypeInventory;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
    /**
     * Créer une commande à partir du panier
     */
    public function store(Request $request)
    {
        // Log de début
        Log::info('OrderController::store - Début', [
            'user_id' => Auth::id(),
            'request_data' => $request->except(['prescription_images']),
            'files_count' => $request->hasFile('prescription_images') ? count($request->file('prescription_images')) : 0
        ]);

        // Vérifier l'authentification
        if (!Auth::check()) {
            Log::warning('OrderController::store - Utilisateur non authentifié');
            return response()->json([
                'success' => false,
                'message' => 'Vous devez être connecté pour passer une commande'
            ], 401);
        }

        try {
            // Vérifier l'état de l'ordonnance AVANT la validation des données
            $prescriptionStatus = Order::checkPrescriptionStatus($request->prescription_number);
            
            if ($prescriptionStatus['status'] === 'completed') {
                Log::warning('OrderController::store - Ordonnance terminée', [
                    'prescription_number' => $request->prescription_number,
                    'status' => $prescriptionStatus
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Cette ordonnance a déjà été entièrement traitée. Veuillez utiliser une nouvelle ordonnance.',
                    'error_type' => 'prescription_completed',
                    'details' => $prescriptionStatus
                ], 422);
            }

            // Valider les données
            $validated = $request->validate([
                'prescription_number' => 'required|string|max:255',
                'phone_number' => 'required|string|max:20',
                'prescription_images' => 'required|array|min:1',
                'prescription_images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max par image
                'payment_method' => 'required|in:tmoney,flooz,carte_bancaire',
                'notes' => 'nullable|string|max:1000'
            ]);
            
            Log::info('OrderController::store - Validation réussie', [
                'validated' => $validated,
                'prescription_status' => $prescriptionStatus
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('OrderController::store - Erreur validation', [
                'errors' => $e->errors(),
                'input' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        }

        try {
            // Commencer une transaction
            DB::beginTransaction();

            // Gérer l'upload des images d'ordonnance
            $prescriptionImagePaths = [];
            if ($request->hasFile('prescription_images')) {
                foreach ($request->file('prescription_images') as $index => $image) {
                    $imageName = 'prescription_' . time() . '_' . ($index + 1) . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                    $imagePath = $image->storeAs('prescriptions', $imageName, 'public');
                    $prescriptionImagePaths[] = $imagePath;
                }
            }

            // Convertir les chemins en JSON pour stockage
            $prescriptionImagesJson = json_encode($prescriptionImagePaths);

            // Récupérer les articles du panier
            $cartItems = Cart::where('user_id', Auth::id())
                ->with('center')
                ->get();

            if ($cartItems->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Votre panier est vide'
                ], 400);
            }

            $orders = [];
            $finalPayableAmount = 0;

            foreach ($cartItems as $cartItem) {
                // Vérifier la disponibilité du stock
                $inventory = CenterBloodTypeInventory::where('center_id', $cartItem->center_id)
                    ->whereHas('bloodType', function($query) use ($cartItem) {
                        $query->where('group', $cartItem->blood_type);
                    })
                    ->first();

                if (!$inventory || $inventory->available_quantity < $cartItem->quantity) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "Stock insuffisant pour {$cartItem->blood_type} au centre {$cartItem->center->name}"
                    ], 400);
                }

                // Calculer l'acompte de 50% et le solde restant
                $unitPrice = 5000; // 5000 F CFA par poche
                $totalAmount = $cartItem->quantity * $unitPrice;
                $acompteAmount = $totalAmount * 0.5; // 50% d'acompte
                $soldeRestant = $totalAmount - $acompteAmount;
                $finalPayableAmount += $acompteAmount;

                // Créer la commande
                $order = Order::create([
                    'user_id' => Auth::id(),
                    'center_id' => $cartItem->center_id,
                    'prescription_number' => $validated['prescription_number'],
                    'phone_number' => $validated['phone_number'],
                    'prescription_images' => $prescriptionImagesJson, // Corrigé: utilise prescription_images
                    'blood_type' => $cartItem->blood_type,
                    'quantity' => $cartItem->quantity,
                    'unit_price' => $unitPrice,
                    'total_amount' => $totalAmount,
                    'deposit_amount' => $acompteAmount, // Montant de l'acompte
                    'remaining_amount' => $soldeRestant, // Solde restant
                    'payment_method' => $validated['payment_method'],
                    'payment_status' => 'partial', // Paiement partiel (acompte)
                    'status' => 'pending', // Statut par défaut
                    'document_status' => 'pending', // Documents en attente de validation
                    'notes' => $validated['notes'] ?? null
                ]);

                $orders[] = $order;

                // Décrémenter le stock
                $inventory->decrement('available_quantity', $cartItem->quantity);

                // Créer une notification pour le gestionnaire du centre
                $this->createCenterNotification($cartItem->center_id, $order);
            }

            // Vider le panier
            Cart::where('user_id', Auth::id())->delete();

            // Valider la transaction
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Réservation créée avec succès',
                'orders_count' => count($orders),
                'total_amount' => $finalPayableAmount,
                'formatted_total' => number_format($finalPayableAmount, 0, ',', ' ') . ' F CFA'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création de commande: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de la commande: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Créer une notification pour le gestionnaire du centre
     */
    private function createCenterNotification($centerId, $order)
    {
        try {
            // Trouver les gestionnaires du centre
            $managers = \App\Models\User::where('center_id', $centerId)
                ->whereIn('role', ['manager', 'admin'])
                ->get();

            foreach ($managers as $manager) {
                Notification::create([
                    'user_id' => $manager->id,
                    'type' => 'new_order',
                    'title' => 'Nouvelle commande de sang',
                    'message' => "Nouvelle commande de {$order->quantity} poche(s) de {$order->blood_type} - Ordonnance: {$order->prescription_number}",
                    'data' => json_encode([
                        'order_id' => $order->id,
                        'prescription_number' => $order->prescription_number,
                        'blood_type' => $order->blood_type,
                        'quantity' => $order->quantity
                    ]),
                    'read_at' => null
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors de la création de notification: ' . $e->getMessage());
        }
    }

    /**
     * Afficher les commandes de l'utilisateur connecté
     */
    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $orders = Order::where('user_id', Auth::id())
            ->with('center')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('orders.index', compact('orders'));
    }

    /**
     * Afficher une commande spécifique
     */
    public function show(Order $order)
    {
        // Vérifier que l'utilisateur peut voir cette commande
        if ($order->user_id !== Auth::id() && !Auth::user()->can_manage_center) {
            abort(403);
        }

        return view('orders.show', compact('order'));
    }

    /**
     * SPRINT 4: Valider les documents d'une commande (gestionnaire uniquement)
     */
    public function validateDocuments(Request $request, Order $order)
    {
        // Vérifier que l'utilisateur peut valider (gestionnaire ou admin du centre)
        if (!Auth::user()->can_manage_center || 
            (Auth::user()->role === 'manager' && Auth::user()->center_id !== $order->center_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Vous n\'avez pas les droits pour valider cette commande'
            ], 403);
        }

        $validated = $request->validate([
            'action' => 'required|in:approve,reject',
            'notes' => 'nullable|string|max:1000'
        ]);

        try {
            if ($validated['action'] === 'approve') {
                $order->markAsConfirmed(Auth::id(), $validated['notes']);
                $message = 'Documents approuvés et commande confirmée';
            } else {
                $order->markAsRejected(Auth::id(), $validated['notes']);
                $message = 'Documents rejetés et commande annulée';
            }

            // Créer une notification pour le client
            Notification::create([
                'user_id' => $order->user_id,
                'type' => 'order_validation',
                'title' => $validated['action'] === 'approve' ? 'Commande confirmée' : 'Commande rejetée',
                'message' => $message . ($validated['notes'] ? " - {$validated['notes']}" : ''),
                'data' => json_encode([
                    'order_id' => $order->id,
                    'action' => $validated['action'],
                    'validator' => Auth::user()->name
                ])
            ]);

            return response()->json([
                'success' => true,
                'message' => $message,
                'order_status' => $order->fresh()->status,
                'document_status' => $order->fresh()->document_status
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur validation documents: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la validation'
            ], 500);
        }
    }

    /**
     * SPRINT 4: Obtenir le statut en temps réel d'une commande
     */
    public function getOrderStatus(Order $order)
    {
        // Vérifier que l'utilisateur peut voir cette commande
        if ($order->user_id !== Auth::id() && !Auth::user()->can_manage_center) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'order' => [
                'id' => $order->id,
                'status' => $order->status,
                'status_label' => $order->status_label,
                'document_status' => $order->document_status,
                'document_status_label' => $order->document_status_label,
                'payment_status' => $order->payment_status,
                'payment_status_label' => $order->payment_status_label,
                'validated_at' => $order->validated_at,
                'validation_notes' => $order->validation_notes,
                'validator' => $order->validator ? $order->validator->name : null,
                'updated_at' => $order->updated_at
            ]
        ]);
    }

    /**
     * SPRINT 4: Vérifier le statut d'une ordonnance (API)
     */
    public function apiCheckPrescriptionStatus(Request $request)
    {
        $validated = $request->validate([
            'prescription_number' => 'required|string|max:255'
        ]);

        $status = Order::checkPrescriptionStatus($validated['prescription_number']);
        
        return response()->json([
            'success' => true,
            'prescription_status' => $status
        ]);
    }

    /**
     * Vérifier l'état d'une ordonnance
     */
    public function checkPrescriptionStatus(Request $request)
    {
        $request->validate([
            'prescription_number' => 'required|string'
        ]);

        $status = Order::checkPrescriptionStatus($request->prescription_number);
        
        return response()->json([
            'success' => true,
            'prescription_status' => $status
        ]);
    }

    /**
     * Mettre à jour le statut d'une commande
     */
    public function updateStatus(Request $request, Order $order)
    {
        // Vérifier les permissions
        if (!Auth::user()->can('manage', $order->center)) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé'
            ], 403);
        }

        $request->validate([
            'status' => 'required|in:pending,confirmed,processing,completed,cancelled,expired'
        ]);

        try {
            $oldStatus = $order->status;
            $order->update(['status' => $request->status]);

            // Créer une notification pour le client
            Notification::create([
                'user_id' => $order->user_id,
                'type' => 'status_update',
                'title' => 'Mise à jour de commande',
                'message' => "Le statut de votre commande #{$order->id} est passé de '{$oldStatus}' à '{$request->status}'",
                'data' => json_encode([
                    'order_id' => $order->id,
                    'old_status' => $oldStatus,
                    'new_status' => $request->status
                ])
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Statut mis à jour',
                'order' => $order->fresh()
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour du statut: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour'
            ], 500);
        }
    }

    /**
     * Obtenir les commandes en temps réel pour un utilisateur
     */
    public function getRealTimeStatus()
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Non authentifié'], 401);
        }

        $orders = Order::where('user_id', Auth::id())
            ->with(['center', 'validator'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'orders' => $orders,
            'summary' => [
                'total' => $orders->count(),
                'pending' => $orders->where('status', 'pending')->count(),
                'confirmed' => $orders->where('status', 'confirmed')->count(),
                'processing' => $orders->where('status', 'processing')->count(),
                'completed' => $orders->where('status', 'completed')->count(),
                'cancelled' => $orders->where('status', 'cancelled')->count(),
            ]
        ]);
    }
}
