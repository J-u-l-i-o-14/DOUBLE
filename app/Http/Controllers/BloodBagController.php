<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BloodBag;
use App\Models\BloodType;
use App\Models\Center;
use App\Models\Donor;
use App\Models\CenterBloodTypeInventory;
use Carbon\Carbon;

class BloodBagController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Vérifier que l'utilisateur a un centre assigné
        if (in_array($user->role, ['admin', 'manager']) && !$user->center_id) {
            abort(403, 'Aucun centre assigné à votre compte.');
        }
        
        $query = BloodBag::with(['bloodType', 'center', 'donor']);

        // Filtrer par centre pour admin et manager (ils gèrent chacun leur centre)
        if (in_array($user->role, ['admin', 'manager'])) {
            $query->where('center_id', $user->center_id);
        }

        // Filtres
        if ($request->filled('blood_type_id')) {
            $query->where('blood_type_id', $request->blood_type_id);
        }
        if ($request->filled('center_id')) {
            $query->where('center_id', $request->center_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        // Note: Recherche de donneur retirée car plus de liaison avec les donneurs
        
        $bloodBags = $query->latest()->paginate(25); // Augmenté à 25 par page
        $bloodTypes = BloodType::all();
        $centers = Center::all();
        
        // Statistiques pour l'utilisateur
        $stats = [];
        if (in_array($user->role, ['admin', 'manager'])) {
            $stats = [
                'total' => BloodBag::where('center_id', $user->center_id)->count(),
                'available' => BloodBag::where('center_id', $user->center_id)->where('status', 'available')->count(),
                'reserved' => BloodBag::where('center_id', $user->center_id)->where('status', 'reserved')->count(),
                'expired' => BloodBag::where('center_id', $user->center_id)->where('status', 'expired')->count(),
            ];
        }
        
        return view('blood-bags.index', compact('bloodBags', 'bloodTypes', 'centers', 'stats'));
    }

    public function create()
    {
        $bloodTypes = BloodType::all();
        $centers = Center::all();

        return view('blood-bags.create', compact('bloodTypes', 'centers'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        
        // Vérifier que l'utilisateur a un centre assigné
        if (in_array($user->role, ['admin', 'manager']) && !$user->center_id) {
            return back()->withErrors(['center_id' => 'Aucun centre assigné à votre compte.']);
        }
        
        $request->validate([
            'blood_type_id' => 'required|exists:blood_types,id',
            'collected_at' => 'required|date',
            'center_id' => 'required|exists:centers,id',
            'quantity' => 'required|integer|min:1|max:1000',
        ]);

        $centerId = $user->center_id ?? $request->center_id;
        $quantity = $request->quantity;
        
        // Calculer la date d'expiration (42 jours après la collecte)
        $collectedAt = \Carbon\Carbon::parse($request->collected_at);
        $expiresAt = $collectedAt->copy()->addDays(42);
        
        // Créer les poches en lot pour optimiser les performances
        $bloodBags = [];
        $batchSize = 500; // Traiter par batch de 500 pour éviter les timeouts
        $totalCreated = 0;
        
        for ($batch = 0; $batch < ceil($quantity / $batchSize); $batch++) {
            $currentBatchSize = min($batchSize, $quantity - ($batch * $batchSize));
            $bloodBags = [];
            
            for ($i = 0; $i < $currentBatchSize; $i++) {
                $bloodBags[] = [
                    'blood_type_id' => $request->blood_type_id,
                    'center_id' => $centerId,
                    'donor_id' => null,
                    'volume' => 450,
                    'collected_at' => $collectedAt,
                    'expires_at' => $expiresAt,
                    'status' => 'available',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            
            // Insertion en lot
            \App\Models\BloodBag::insert($bloodBags);
            $totalCreated += $currentBatchSize;
        }
        
        // Mettre à jour l'inventaire
        $this->updateInventory($centerId, $request->blood_type_id);
        
        return redirect()->route('blood-bags.index')
            ->with('success', "{$totalCreated} poche(s) de sang créée(s) avec succès pour le groupe " . 
                   \App\Models\BloodType::find($request->blood_type_id)->group . ".");
    }

    public function show(BloodBag $bloodBag)
    {
        $bloodBag->load(['bloodType', 'center', 'donor', 'transfusion', 'donationHistory']);
        return view('blood-bags.show', compact('bloodBag'));
    }

    public function edit(BloodBag $bloodBag)
    {
        $bloodTypes = BloodType::all();
        $centers = Center::all();

        return view('blood-bags.edit', compact('bloodBag', 'bloodTypes', 'centers'));
    }

    public function update(Request $request, BloodBag $bloodBag)
    {
        $user = auth()->user();
        
        // Vérifier que l'utilisateur a un centre assigné
        if (in_array($user->role, ['admin', 'manager']) && !$user->center_id) {
            return back()->withErrors(['center_id' => 'Aucun centre assigné à votre compte.']);
        }
        
        $request->validate([
            'blood_type_id' => 'required|exists:blood_types,id',
            'collected_at' => 'required|date',
            'status' => 'required|in:available,reserved,transfused,expired,discarded',
            'center_id' => 'required|exists:centers,id',
        ]);
        
        $centerId = $user->center_id ?? $request->center_id;
        $collectedAt = Carbon::parse($request->collected_at);
        $expiresAt = $collectedAt->copy()->addDays(42);
        
        $bloodBag->update([
            'blood_type_id' => $request->blood_type_id,
            'center_id' => $centerId,
            'collected_at' => $collectedAt,
            'expires_at' => $expiresAt,
            'status' => $request->status,
        ]);
        
        // Mettre à jour l'inventaire
        $this->updateInventory($centerId, $request->blood_type_id);
        
        return redirect()->route('blood-bags.index')
            ->with('success', 'Poche de sang mise à jour avec succès.');
    }

    public function destroy(BloodBag $bloodBag)
    {
        $bloodBag->delete();

        // Mettre à jour l'inventaire
        $this->updateInventory($bloodBag->center_id, $bloodBag->blood_type_id);

        return redirect()->route('blood-bags.index')
            ->with('success', 'Poche de sang supprimée avec succès.');
    }

    public function stock()
    {
        $user = auth()->user();
        if (in_array($user->role, ['admin', 'manager'])) {
            $stockByCenter = Center::with(['inventory.bloodType'])
                ->where('id', $user->center_id)
                ->get();
            $alerts = \App\Models\Alert::where('center_id', $user->center_id)
                ->where('resolved', false)
                ->latest()
                ->get();
        } else {
            $stockByCenter = Center::with(['inventory.bloodType'])->get();
            $alerts = \App\Models\Alert::where('resolved', false)->latest()->get();
        }
        $expiredBags = BloodBag::expired()
            ->when(in_array($user->role, ['admin', 'manager']), function($q) use ($user) {
                $q->where('center_id', $user->center_id);
            })
            ->count();
        $expiringSoonBags = BloodBag::expiringSoon()
            ->when(in_array($user->role, ['admin', 'manager']), function($q) use ($user) {
                $q->where('center_id', $user->center_id);
            })
            ->count();
        return view('blood-bags.stock', compact('stockByCenter', 'expiredBags', 'expiringSoonBags', 'alerts'));
    }

    public function markExpired()
    {
        $expiredBags = BloodBag::available()
            ->where('expires_at', '<', Carbon::now())
            ->update(['status' => 'expired']);

        return redirect()->back()
            ->with('success', "{$expiredBags} poches marquées comme expirées.");
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