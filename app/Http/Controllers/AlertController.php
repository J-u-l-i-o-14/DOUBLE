<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\Center;
use App\Models\BloodType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AlertController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin,manager')->except(['index']);
    }

    /**
     * Afficher toutes les alertes pour le centre de l'utilisateur
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = Alert::with(['center', 'bloodType'])
            ->where('center_id', $user->center_id)
            ->orderBy('created_at', 'desc');

        // Filtrer par statut
        if ($request->has('status')) {
            if ($request->status === 'unresolved') {
                $query->where('resolved', false);
            } elseif ($request->status === 'resolved') {
                $query->where('resolved', true);
            }
        }

        // Filtrer par type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $alerts = $query->paginate(20);

        // Statistiques des alertes
        $stats = [
            'total' => Alert::where('center_id', $user->center_id)->count(),
            'unresolved' => Alert::where('center_id', $user->center_id)->where('resolved', false)->count(),
            'low_stock' => Alert::where('center_id', $user->center_id)->where('type', 'low_stock')->where('resolved', false)->count(),
            'expiration' => Alert::where('center_id', $user->center_id)->where('type', 'expiration')->where('resolved', false)->count(),
        ];

        return view('alerts.index', compact('alerts', 'stats'));
    }

    /**
     * Marquer une alerte comme résolue
     */
    public function resolve(Alert $alert)
    {
        $user = Auth::user();
        
        // Vérifier que l'alerte appartient au centre de l'utilisateur
        if ($alert->center_id !== $user->center_id) {
            return response()->json(['error' => 'Accès non autorisé'], 403);
        }

        $alert->resolved = true;
        $alert->resolved_at = now();
        $alert->resolved_by = $user->id;
        $alert->save();

        return response()->json([
            'success' => true,
            'message' => 'Alerte marquée comme résolue'
        ]);
    }

    /**
     * Marquer une alerte comme non résolue
     */
    public function unresolve(Alert $alert)
    {
        $user = Auth::user();
        
        // Vérifier que l'alerte appartient au centre de l'utilisateur
        if ($alert->center_id !== $user->center_id) {
            return response()->json(['error' => 'Accès non autorisé'], 403);
        }

        $alert->resolved = false;
        $alert->resolved_at = null;
        $alert->resolved_by = null;
        $alert->save();

        return response()->json([
            'success' => true,
            'message' => 'Alerte marquée comme non résolue'
        ]);
    }

    /**
     * Résoudre toutes les alertes d'un type donné
     */
    public function resolveAll(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'type' => 'required|in:low_stock,expiration'
        ]);

        $count = Alert::where('center_id', $user->center_id)
            ->where('type', $request->type)
            ->where('resolved', false)
            ->update([
                'resolved' => true,
                'resolved_at' => now(),
                'resolved_by' => $user->id
            ]);

        return response()->json([
            'success' => true,
            'message' => "$count alerte(s) marquée(s) comme résolue(s)"
        ]);
    }

    /**
     * Supprimer une alerte (admin seulement)
     */
    public function destroy(Alert $alert)
    {
        $user = Auth::user();
        
        // Seuls les admins peuvent supprimer
        if ($user->role !== 'admin') {
            return response()->json(['error' => 'Accès non autorisé'], 403);
        }

        // Vérifier que l'alerte appartient au centre de l'utilisateur
        if ($alert->center_id !== $user->center_id) {
            return response()->json(['error' => 'Accès non autorisé'], 403);
        }

        $alert->delete();

        return response()->json([
            'success' => true,
            'message' => 'Alerte supprimée'
        ]);
    }

    /**
     * API: Récupérer les alertes actives pour les notifications
     */
    public function getActiveAlerts()
    {
        $user = Auth::user();
        
        $alerts = Alert::with(['bloodType'])
            ->where('center_id', $user->center_id)
            ->where('resolved', false)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json($alerts);
    }

    /**
     * Génération manuelle des alertes
     */
    public function generate()
    {
        // Exécuter la commande de génération d'alertes
        \Artisan::call('blood:generate-alerts');
        
        return response()->json([
            'success' => true,
            'message' => 'Alertes générées avec succès'
        ]);
    }
}
