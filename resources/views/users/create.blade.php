@extends('layouts.main')

@section('page-title', 'Ajouter un utilisateur')

@section('content')
    <div class="max-w-xl mx-auto bg-white rounded-lg shadow border border-gray-200 p-6">
        <h2 class="text-lg font-semibold mb-4">Ajouter un utilisateur</h2>
        
        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        <form action="{{ route('users.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Nom <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" 
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                       required minlength="2">
            </div>
            
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email <span class="text-red-500">*</span></label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" 
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                       required>
            </div>
            
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Mot de passe <span class="text-red-500">*</span></label>
                <input type="password" name="password" id="password" 
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                       required minlength="8">
                <p class="mt-1 text-xs text-gray-500">Minimum 8 caractères avec au moins 1 minuscule, 1 majuscule et 1 chiffre</p>
            </div>
            
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmer le mot de passe <span class="text-red-500">*</span></label>
                <input type="password" name="password_confirmation" id="password_confirmation" 
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                       required>
            </div>
            
            <div>
                <label for="role" class="block text-sm font-medium text-gray-700">Rôle <span class="text-red-500">*</span></label>
                <select name="role" id="role" 
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                        required>
                    <option value="">Sélectionner un rôle</option>
                    <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Administrateur</option>
                    <option value="manager" {{ old('role') == 'manager' ? 'selected' : '' }}>Manager</option>
                    <option value="donor" {{ old('role') == 'donor' ? 'selected' : '' }}>Donneur</option>
                    <option value="patient" {{ old('role') == 'patient' ? 'selected' : '' }}>Patient</option>
                    <option value="client" {{ old('role') == 'client' ? 'selected' : '' }}>Client</option>
                </select>
            </div>
            
            <div>
                <label for="gender" class="block text-sm font-medium text-gray-700">Genre <span class="text-red-500">*</span></label>
                <select name="gender" id="gender" 
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                        required>
                    <option value="">Sélectionner le genre</option>
                    <option value="M" {{ old('gender') == 'M' ? 'selected' : '' }}>Homme</option>
                    <option value="F" {{ old('gender') == 'F' ? 'selected' : '' }}>Femme</option>
                </select>
            </div>
            
            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700">Téléphone</label>
                <input type="text" name="phone" id="phone" value="{{ old('phone') }}" 
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                       minlength="8">
            </div>
            
            <div>
                <label for="address" class="block text-sm font-medium text-gray-700">Adresse</label>
                <textarea name="address" id="address" rows="3" 
                          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                          maxlength="500">{{ old('address') }}</textarea>
            </div>
            
            <div class="pt-4">
                <button type="submit" 
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-200">
                    Créer l'utilisateur
                </button>
            </div>
        </form>
    </div>
@endsection 