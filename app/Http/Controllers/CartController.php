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
            'blood_type' => 'required|exists:blood_types,group',
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
}