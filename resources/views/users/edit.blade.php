@extends('layouts.main')

@section('page-title', 'Modifier un utilisateur')

@section('content')
    <div class="max-w-xl mx-auto bg-white rounded-lg shadow border border-gray-200 p-6">
        <h2 class="text-lg font-semibold mb-4">Modifier un utilisateur</h2>
        <form action="{{ route('users.update', $user) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Nom</label>
                <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Nouveau mot de passe</label>
                <input type="password" name="password" id="password" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                <span class="text-xs text-gray-500">Laisser vide pour ne pas changer</span>
            </div>
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmer le mot de passe</label>
                <input type="password" name="password_confirmation" id="password_confirmation" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            </div>
            <div>
                <label for="role" class="block text-sm font-medium text-gray-700">Rôle</label>
                <select name="role" id="role" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                    <option value="admin" @if($user->role == 'admin') selected @endif>Administrateur</option>
                    <option value="manager" @if($user->role == 'manager') selected @endif>Manager</option>
                    <option value="donor" @if($user->role == 'donor') selected @endif>Donneur</option>
                    <option value="patient" @if($user->role == 'patient') selected @endif>Patient</option>
                    <option value="client" @if($user->role == 'client') selected @endif>Client</option>
                </select>
            </div>
            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700">Téléphone</label>
                <input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            </div>
            <div>
                <label for="birth_date" class="block text-sm font-medium text-gray-700">Date de naissance</label>
                <input type="date" name="birth_date" id="birth_date" 
                       value="{{ old('birth_date', $user->birth_date) }}"
                       min="{{ date('Y-m-d', strtotime('-100 years')) }}" 
                       max="{{ date('Y-m-d', strtotime('-16 years')) }}"
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            </div>
            <div>
                <label for="address" class="block text-sm font-medium text-gray-700">Adresse</label>
                <input type="text" name="address" id="address" value="{{ old('address', $user->address) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            </div>
            <div>
                <label for="gender" class="block text-sm font-medium text-gray-700">Genre</label>
                <select name="gender" id="gender" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    <option value="" @if(empty($user->gender)) selected @endif>Sélectionner</option>
                    <option value="M" @if($user->gender == 'M') selected @endif>Homme</option>
                    <option value="F" @if($user->gender == 'F') selected @endif>Femme</option>
                </select>
            </div>
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg">Enregistrer</button>
        </form>
    </div>
@endsection 