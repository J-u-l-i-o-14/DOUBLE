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
        // Vérifier l'authentification
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Vous devez être connecté pour passer une commande'
            ], 401);
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
                    'prescription_image' => $prescriptionImagesJson, // Stockage des multiples images en JSON
                    'blood_type' => $cartItem->blood_type,
                    'quantity' => $cartItem->quantity,
                    'unit_price' => $unitPrice,
                    'original_price' => $totalAmount,
                    'discount_amount' => $acompteAmount, // Montant de l'acompte
                    'total_amount' => $acompteAmount, // Montant payé maintenant
                    'payment_method' => $validated['payment_method'],
                    'payment_status' => 'partial', // Paiement partiel (acompte)
                    'status' => 'pending',
                    'notes' => $validated['notes'] ?? null,
                    'order_date' => now()
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
}
