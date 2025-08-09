@extends('layouts.main')

@section('page-title', 'Ajouter une poche de sang')

@section('content')
    <div class="max-w-xl mx-auto bg-white rounded-lg shadow border border-gray-200 p-6">
        <h2 class="text-lg font-semibold mb-4">Ajouter une poche de sang</h2>
        <form action="{{ route('blood-bags.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="blood_type_id" class="block text-sm font-medium text-gray-700">Groupe sanguin</label>
                <select name="blood_type_id" id="blood_type_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                    <option value="">Sélectionner</option>
                    @foreach($bloodTypes as $type)
                        <option value="{{ $type->id }}" {{ old('blood_type_id') == $type->id ? 'selected' : '' }}>{{ $type->group }}</option>
                    @endforeach
                </select>
                @error('blood_type_id')
                    <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                @enderror
            </div>
            <div>
                <label for="quantity" class="block text-sm font-medium text-gray-700">Nombre de poches à créer</label>
                <input type="number" name="quantity" id="quantity" 
                       value="{{ old('quantity', 1) }}" 
                       min="1" max="1000" 
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                <div class="mt-1 text-xs text-gray-500">
                    Entrez le nombre de poches de ce groupe sanguin à créer (maximum 1000)
                </div>
                @error('quantity')
                    <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                @enderror
            </div>
            <div>
                <label for="collected_at" class="block text-sm font-medium text-gray-700">Date de collecte</label>
                <input type="date" name="collected_at" id="collected_at" 
                       value="{{ old('collected_at') }}"
                       max="{{ date('Y-m-d') }}" 
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                @error('collected_at')
                    <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                @enderror
            </div>
            @if(auth()->user()->is_admin || auth()->user()->is_manager)
                <input type="hidden" name="center_id" value="{{ auth()->user()->center_id }}">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Centre</label>
                    <div class="mt-1 p-2 bg-gray-50 rounded-md border">{{ auth()->user()->center->name }}</div>
                </div>
            @else
                <div>
                    <label for="center_id" class="block text-sm font-medium text-gray-700">Centre</label>
                    <select name="center_id" id="center_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                        <option value="">Sélectionner</option>
                        @foreach($centers as $center)
                            <option value="{{ $center->id }}" {{ old('center_id') == $center->id ? 'selected' : '' }}>{{ $center->name }}</option>
                        @endforeach
                    </select>
                    @error('center_id')
                        <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>
            @endif
            <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg">
                <i class="fas fa-plus mr-2"></i>Créer les poches de sang
            </button>
        </form>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const quantityInput = document.getElementById('quantity');
        const submitButton = document.querySelector('button[type="submit"]');
        
        quantityInput.addEventListener('input', function() {
            const quantity = parseInt(this.value) || 0;
            const buttonText = submitButton.querySelector('span') || submitButton.childNodes[2];
            
            if (quantity > 1) {
                submitButton.innerHTML = '<i class="fas fa-plus mr-2"></i>Créer ' + quantity + ' poches de sang';
            } else {
                submitButton.innerHTML = '<i class="fas fa-plus mr-2"></i>Créer les poches de sang';
            }
            
            // Warning pour grande quantité
            const warning = document.getElementById('quantity-warning');
            if (quantity > 100) {
                if (!warning) {
                    const warningDiv = document.createElement('div');
                    warningDiv.id = 'quantity-warning';
                    warningDiv.className = 'text-yellow-600 text-xs mt-1';
                    warningDiv.innerHTML = '<i class="fas fa-exclamation-triangle mr-1"></i>Grande quantité : cela peut prendre quelques secondes';
                    quantityInput.parentNode.appendChild(warningDiv);
                }
            } else if (warning) {
                warning.remove();
            }
        });
    });
</script>
@endpush 