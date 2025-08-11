<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = User::query();

        // Sécurité : Seuls admin et manager peuvent voir les utilisateurs
        if (!in_array($user->role, ['admin', 'manager'])) {
            abort(403, 'Accès non autorisé');
        }

        // Admin et manager voient seulement leur centre
        if (in_array($user->role, ['admin', 'manager'])) {
            $query->where('center_id', $user->center_id);
        }

        // Filtres
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        $users = $query->latest()->paginate(15);
        
        // Récupérer la liste des centres pour les filtres
        $centers = [];
        if ($user->role === 'admin') {
            $centers = \App\Models\Center::all();
        }
        
        return view('users.index', compact('users', 'centers'));
    }

    public function create()
    {
        $user = auth()->user();
        
        // Sécurité : Seuls admin et manager peuvent créer des utilisateurs
        if (!in_array($user->role, ['admin', 'manager'])) {
            abort(403, 'Accès non autorisé');
        }

        // Récupérer la liste des centres pour les admins
        $centers = [];
        if ($user->role === 'admin') {
            $centers = \App\Models\Center::all();
        }

        return view('users.create', compact('centers'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        
        // Sécurité : Seuls admin et manager peuvent créer des utilisateurs
        if (!in_array($user->role, ['admin', 'manager'])) {
            abort(403, 'Accès non autorisé');
        }

        $request->validate([
            'name' => 'required|string|max:255|min:2',
            'email' => 'required|email|unique:users,email|max:255',
            'password' => 'required|string|min:8|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/',
            'role' => 'required|in:admin,manager,donor,patient,client',
            'phone' => 'nullable|string|max:20|min:8',
            'address' => 'nullable|string|max:500',
            'gender' => 'required|in:M,F',
        ], [
            'password.regex' => 'Le mot de passe doit contenir au moins une minuscule, une majuscule et un chiffre.',
            'gender.required' => 'Le genre est obligatoire.',
            'name.min' => 'Le nom doit contenir au moins 2 caractères.',
            'phone.min' => 'Le numéro de téléphone doit contenir au moins 8 caractères.',
        ]);

        $data = $request->only(['name', 'email', 'role', 'phone', 'address', 'gender']);
        
        // Assigner automatiquement le centre selon les permissions
        if ($user->role === 'manager') {
            $data['center_id'] = $user->center_id;
        } elseif ($user->role === 'admin' && $user->center_id) {
            $data['center_id'] = $user->center_id;
        }
        
        $data['password'] = Hash::make($request->password);
        
        User::create($data);
        return redirect()->route('users.index')
            ->with('success', 'Utilisateur créé avec succès.');
    }

    public function show(User $user)
    {
        $user->load(['donations', 'appointments', 'bloodBags']);
        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $bloodTypes = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
        
        // Récupérer la liste des centres pour les admins
        $centers = [];
        $authUser = auth()->user();
        if ($authUser->role === 'admin') {
            $centers = \App\Models\Center::all();
        }
        
        return view('users.edit', compact('user', 'bloodTypes', 'centers'));
    }

    public function update(Request $request, User $user)
    {
        $authUser = auth()->user();
        
        // Sécurité : Seuls admin et manager peuvent modifier des utilisateurs
        if (!in_array($authUser->role, ['admin', 'manager'])) {
            abort(403, 'Accès non autorisé');
        }

        // Manager ne peut modifier que les utilisateurs de son centre
        if ($authUser->role === 'manager' && $user->center_id !== $authUser->center_id) {
            abort(403, 'Vous ne pouvez modifier que les utilisateurs de votre centre');
        }

        $request->validate([
            'name' => 'required|string|max:255|min:2',
            'email' => 'required|email|unique:users,email,' . $user->id . '|max:255',
            'password' => 'nullable|string|min:8|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/',
            'role' => 'required|in:admin,manager,donor,patient,client',
            'phone' => 'nullable|string|max:20|min:8',
            'address' => 'nullable|string|max:500',
            'gender' => 'required|in:M,F',
        ], [
            'password.regex' => 'Le mot de passe doit contenir au moins une minuscule, une majuscule et un chiffre.',
            'gender.required' => 'Le genre est obligatoire.',
            'name.min' => 'Le nom doit contenir au moins 2 caractères.',
            'phone.min' => 'Le numéro de téléphone doit contenir au moins 8 caractères.',
        ]);

        $updateData = $request->only(['name', 'email', 'role', 'phone', 'address', 'gender']);
        
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }
        
        $user->update($updateData);
        return redirect()->route('users.index')
            ->with('success', 'Utilisateur mis à jour avec succès.');
    }

    public function destroy(User $user)
    {
        $authUser = auth()->user();
        
        // Sécurité : Seuls admin et manager peuvent supprimer des utilisateurs
        if (!in_array($authUser->role, ['admin', 'manager'])) {
            abort(403, 'Accès non autorisé');
        }

        // Manager ne peut supprimer que les utilisateurs de son centre
        if ($authUser->role === 'manager' && $user->center_id !== $authUser->center_id) {
            abort(403, 'Vous ne pouvez supprimer que les utilisateurs de votre centre');
        }

        // Empêcher la suppression si l'utilisateur a des relations importantes
        $hasRelations = false;
        $relationMessages = [];

        // Vérifier les donations via le donneur
        if ($user->donations()->exists()) {
            $hasRelations = true;
            $relationMessages[] = 'donations';
        }

        // Vérifier les réservations
        if ($user->reservationRequests()->exists()) {
            $hasRelations = true;
            $relationMessages[] = 'réservations';
        }

        if ($hasRelations) {
            return redirect()->route('users.index')
                ->with('error', 'Impossible de supprimer cet utilisateur car il a des ' . implode(', ', $relationMessages) . ' associées.');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'Utilisateur supprimé avec succès.');
    }
}