<?php

namespace App\Http\Controllers;

use App\Models\StockThreshold;
use App\Models\Center;
use App\Models\BloodType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockThresholdController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        $thresholds = StockThreshold::where('center_id', $user->center_id)
            ->with(['center', 'bloodType'])
            ->get();
            
        $bloodTypes = BloodType::all();
        
        return view('stock-thresholds.index', compact('thresholds', 'bloodTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'blood_type_id' => 'required|exists:blood_types,id',
            'warning_threshold' => 'required|integer|min:1',
            'critical_threshold' => 'required|integer|min:1|lt:warning_threshold',
        ]);

        $user = Auth::user();

        StockThreshold::updateOrCreate(
            [
                'center_id' => $user->center_id,
                'blood_type_id' => $request->blood_type_id,
            ],
            [
                'warning_threshold' => $request->warning_threshold,
                'critical_threshold' => $request->critical_threshold,
            ]
        );

        return redirect()->back()->with('success', 'Seuil d\'alerte configuré avec succès');
    }

    public function update(Request $request, StockThreshold $threshold)
    {
        $request->validate([
            'warning_threshold' => 'required|integer|min:1',
            'critical_threshold' => 'required|integer|min:1|lt:warning_threshold',
        ]);

        // Vérifier que l'utilisateur peut modifier ce seuil
        if ($threshold->center_id !== Auth::user()->center_id) {
            abort(403);
        }

        $threshold->update($request->only(['warning_threshold', 'critical_threshold']));

        return redirect()->back()->with('success', 'Seuil d\'alerte mis à jour avec succès');
    }

    public function destroy(StockThreshold $threshold)
    {
        // Vérifier que l'utilisateur peut supprimer ce seuil
        if ($threshold->center_id !== Auth::user()->center_id) {
            abort(403);
        }

        $threshold->delete();

        return redirect()->back()->with('success', 'Seuil d\'alerte supprimé avec succès');
    }
}
