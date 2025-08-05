<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function add(Request $request)
    {
        // Vérifier l'authentification
        if (!Auth::check()) {
            return response()->json(['message' => 'Vous devez être connecté'], 401);
        }

        // Valider la requête
        $validated = $request->validate([
            'center_id' => 'required|exists:centers,id',
            'blood_type' => 'required|string',
            'quantity' => 'required|integer|min:1'
        ]);

        try {
            // Vérifier si l'article existe déjà dans le panier
            $cartItem = Cart::where('user_id', Auth::id())
                          ->where('center_id', $validated['center_id'])
                          ->where('blood_type', $validated['blood_type'])
                          ->first();

            if ($cartItem) {
                // Mettre à jour la quantité
                $cartItem->quantity += $validated['quantity'];
                $cartItem->save();
            } else {
                // Créer un nouvel article
                Cart::create([
                    'user_id' => Auth::id(),
                    'center_id' => $validated['center_id'],
                    'blood_type' => $validated['blood_type'],
                    'quantity' => $validated['quantity']
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Article ajouté au panier'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'ajout au panier'
            ], 500);
        }
    }

    /**
     * Afficher le contenu du panier
     */
    public function index()
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Vous devez être connecté'], 401);
        }

        $cartItems = Cart::where('user_id', Auth::id())
            ->with('center')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'center_name' => $item->center->name,
                    'blood_type' => $item->blood_type,
                    'quantity' => $item->quantity
                ];
            });

        $totalQuantity = $cartItems->sum('quantity');

        return response()->json([
            'success' => true,
            'items' => $cartItems,
            'total_quantity' => $totalQuantity
        ]);
    }

    /**
     * Supprimer un article du panier
     */
    public function remove($id)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Vous devez être connecté'], 401);
        }

        try {
            $cartItem = Cart::where('user_id', Auth::id())
                          ->where('id', $id)
                          ->first();

            if (!$cartItem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Article non trouvé'
                ], 404);
            }

            $cartItem->delete();

            return response()->json([
                'success' => true,
                'message' => 'Article supprimé du panier'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression'
            ], 500);
        }
    }

    /**
     * Vider le panier
     */
    public function clear()
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Vous devez être connecté'], 401);
        }

        try {
            Cart::where('user_id', Auth::id())->delete();

            return response()->json([
                'success' => true,
                'message' => 'Panier vidé'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du vidage du panier'
            ], 500);
        }
    }

    /**
     * Traiter le paiement
     */
    public function processPayment()
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Vous devez être connecté'], 401);
        }

        try {
            $cartItems = Cart::where('user_id', Auth::id())->get();

            if ($cartItems->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Votre panier est vide'
                ], 400);
            }

            // Ici, vous pouvez ajouter la logique de paiement
            // Pour l'instant, nous simulons un paiement réussi
            
            // Vider le panier après paiement réussi
            Cart::where('user_id', Auth::id())->delete();

            return response()->json([
                'success' => true,
                'message' => 'Paiement effectué avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du paiement'
            ], 500);
        }
    }
}