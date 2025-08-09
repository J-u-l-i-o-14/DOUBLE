<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Region;
use App\Models\BloodType;
use App\Models\Center;
use App\Models\CenterBloodTypeInventory;
use App\Mail\ReservationConfirmation;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SearchBloodController extends Controller
{
    public function showReservationForm()
    {
        $regions = Region::all();
        $bloodTypes = BloodType::all();
        
        return view('blood-reservation', compact('regions', 'bloodTypes'));
    }

    public function searchBlood(Request $request)
    {
        return $this->searchAjax($request);
    }

    public function search(Request $request)
    {
        $regions = Region::all();
        $bloodTypes = BloodType::all();
        $results = collect();

        // Recherche avancée : plusieurs groupes sanguins et quantités
        $searchBloodTypes = collect($request->input('blood_types', []))
            ->filter(fn($row) => !empty($row['blood_type_id']) && !empty($row['quantity']) && $row['quantity'] > 0)
            ->map(fn($row) => [
                'blood_type_id' => (int)$row['blood_type_id'],
                'quantity' => (int)$row['quantity'],
            ]);

        if ($searchBloodTypes->count() > 0) {
            // On récupère tous les centres (filtrés par région si besoin)
            $centersQuery = Center::with(['region']);
            if ($request->filled('region_id')) {
                $centersQuery->where('region_id', $request->region_id);
            }
            if ($request->filled('center_id')) {
                $centersQuery->where('id', $request->center_id);
            }
            $centers = $centersQuery->get();

            // Pour chaque centre, vérifier s'il a assez de stock pour chaque type demandé
            $matchingCenters = [];
            foreach ($centers as $center) {
                $matchingStocks = collect();
                $ok = true;
                foreach ($searchBloodTypes as $search) {
                    $stock = CenterBloodTypeInventory::where('center_id', $center->id)
                        ->where('blood_type_id', $search['blood_type_id'])
                        ->first();
                    if (!$stock || $stock->available_quantity < $search['quantity']) {
                        $ok = false;
                        break;
                    }
                    $matchingStocks->push($stock);
                }
                if ($ok && $matchingStocks->count()) {
                    $center->matchingStocks = $matchingStocks;
                    $matchingCenters[] = $center;
                }
            }
            $results = collect($matchingCenters);
        }

        return view('welcome', compact('regions', 'bloodTypes', 'results'));
    }

    public function searchAjax(Request $request)
    {
        $results = collect();
        $searchBloodTypes = collect($request->input('blood_types', []))
            ->filter(fn($row) => !empty($row['blood_type_id']) && !empty($row['quantity']) && $row['quantity'] > 0)
            ->map(fn($row) => [
                'blood_type_id' => (int)$row['blood_type_id'],
                'quantity' => (int)$row['quantity'],
            ]);

        if ($searchBloodTypes->count() > 0) {
            $centersQuery = \App\Models\Center::with(['region']);
            if ($request->filled('region_id')) {
                $centersQuery->where('region_id', $request->region_id);
            }
            if ($request->filled('center_id')) {
                $centersQuery->where('id', $request->center_id);
            }
            $centers = $centersQuery->get();
            $matchingCenters = [];
            foreach ($searchBloodTypes as $search) {
                foreach ($centers as $center) {
                    $stock = \App\Models\CenterBloodTypeInventory::where('center_id', $center->id)
                        ->where('blood_type_id', $search['blood_type_id'])
                        ->first();
                    if ($stock && $stock->available_quantity > 0) {
                        $canProvide = min($stock->available_quantity, $search['quantity']);
                        $matchingCenters[] = [
                            'id' => $center->id,
                            'name' => $center->name,
                            'region' => $center->region->name ?? '',
                            'address' => $center->address,
                            'phone' => $center->phone ?? '',
                            'blood_type' => $stock->bloodType->group,
                            'requested_quantity' => $search['quantity'],
                            'can_provide' => $canProvide,
                        ];
                    }
                }
            }
            $results = collect($matchingCenters);
        }
        return response()->json(['results' => $results]);
    }

    public function centersByRegion($regionId)
    {
        $centers = Center::where('region_id', $regionId)->get(['id', 'name']);
        return response()->json(['centers' => $centers]);
    }

    public function storeReservation(Request $request)
    {
        // 1. Valider les données de la requête
        // La gestion d'erreur de Laravel retournera automatiquement du JSON pour les requêtes AJAX
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.blood_type_id' => 'required|exists:blood_types,id',
            'items.*.quantity' => 'required|integer|min:1',
            'center_id' => 'required|exists:centers,id',
            'client_name' => 'required|string|max:255',
            'client_email' => 'required|email|max:255',
            'client_phone' => 'required|string|max:30',
            'payment_method' => 'required|string|max:50',
            'client_docs' => 'required|array',
            'client_docs.*' => 'file|mimes:pdf,jpg,png|max:2048',
        ]);

        try {
            // Démarrer une transaction pour garantir l'intégrité des données
            DB::beginTransaction();

            // 2. Trouver ou créer l'utilisateur client
            $user = \App\Models\User::firstOrCreate(
                ['email' => $validated['client_email']],
                [
                    'name' => $validated['client_name'],
                    'password' => Hash::make(Str::random(12)), // Mot de passe aléatoire
                    'role' => 'client',
                    'phone' => $validated['client_phone'],
                ]
            );

            $centerId = $validated['center_id'];
            $totalAmount = 0;

            // 3. Vérifier la disponibilité et calculer le montant total
            foreach ($validated['items'] as $item) {
                $inventory = \App\Models\CenterBloodTypeInventory::where('center_id', $centerId)
                    ->where('blood_type_id', $item['blood_type_id'])
                    ->first();

                if (!$inventory || $inventory->available_quantity < $item['quantity']) {
                    throw new \Exception('Stock insuffisant pour un des types sanguins demandés.');
                }
                // Prix fixe par poche, à ajuster si nécessaire
                $totalAmount += $item['quantity'] * 5000;
            }

            // 4. Gérer l'upload des documents multiples
            $documentPaths = [];
            if ($request->hasFile('client_docs')) {
                foreach ($request->file('client_docs') as $file) {
                    $documentPaths[] = $file->store('documents', 'public');
                }
            }

            // 5. Créer la demande de réservation
            $reservation = new \App\Models\ReservationRequest();
            $reservation->user_id = $user->id; // Utiliser l'ID du client
            $reservation->center_id = $centerId;
            $reservation->status = 'pending';
            $reservation->total_amount = $totalAmount;
            $reservation->paid_amount = $totalAmount * 0.5; // Paiement partiel de 50%
            $reservation->document_path = json_encode($documentPaths); // Stocker les chemins en JSON
            $reservation->client_name = $validated['client_name'];
            $reservation->client_email = $validated['client_email'];
            $reservation->client_phone = $validated['client_phone'];
            $reservation->payment_method = $validated['payment_method'];
            $reservation->expires_at = \Carbon\Carbon::now()->addHours(72);
            $reservation->save();

            // 6. Créer les items de la réservation
            foreach ($validated['items'] as $item) {
                $reservation->items()->create([
                    'blood_type_id' => $item['blood_type_id'],
                    'quantity' => $item['quantity'],
                ]);
            }

            // 7. Créer l'entrée de paiement simulé
            $payment = new \App\Models\Payment();
            $payment->reservation_id = $reservation->id;
            $payment->amount = $reservation->paid_amount;
            $payment->method = $validated['payment_method'];
            $payment->status = 'completed'; // Simuler le paiement réussi
            $payment->paid_at = now();
            $payment->save();

            // Valider la transaction
            DB::commit();

            $successMessage = 'Réservation effectuée avec succès.';

            // Gérer la réponse AJAX ou la redirection standard
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $successMessage,
                    'redirectUrl' => route('dashboard.client')
                ]);
            }
            return redirect()->route('dashboard.client')->with('success', $successMessage);

        } catch (\Exception $e) {
            // Annuler la transaction en cas d'erreur
            DB::rollBack();
            \Log::error('Erreur de réservation: ' . $e->getMessage());

            $errorMessage = $e->getMessage() ?: 'Une erreur est survenue lors de la création de la réservation.';
            
            // Gérer la réponse d'erreur pour AJAX ou redirection standard
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $errorMessage], 500);
            }
            return back()->with('error', $errorMessage)->withInput();
        }
    }
} 