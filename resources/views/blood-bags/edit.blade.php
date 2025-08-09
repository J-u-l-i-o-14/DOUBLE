@extends('layouts.main')

@section('page-title', 'Modifier une poche de sang')

@section('content')
    <div class="max-w-xl mx-auto bg-white rounded-lg shadow border border-gray-200 p-6">
        <h2 class="text-lg font-semibold mb-4">Modifier une poche de sang</h2>
        <form action="{{ route('blood-bags.update', $bloodBag) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label for="blood_type_id" class="block text-sm font-medium text-gray-700">Groupe sanguin</label>
                <select name="blood_type_id" id="blood_type_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                    <option value="">Sélectionner</option>
                    @foreach($bloodTypes as $type)
                        <option value="{{ $type->id }}" {{ (old('blood_type_id') ?? $bloodBag->blood_type_id) == $type->id ? 'selected' : '' }}>{{ $type->group }}</option>
                    @endforeach
                </select>
                @error('blood_type_id')
                    <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                @enderror
            </div>
            <div>
                <label for="collected_at" class="block text-sm font-medium text-gray-700">Date de collecte</label>
                <input type="date" name="collected_at" id="collected_at" 
                       value="{{ old('collected_at', optional($bloodBag->collected_at)->format('Y-m-d')) }}" 
                       max="{{ date('Y-m-d') }}" 
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                @error('collected_at')
                    <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                @enderror
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Statut</label>
                <select name="status" id="status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                    @foreach(['available'=>'Disponible','reserved'=>'Réservée','transfused'=>'Transfusée','expired'=>'Expirée','discarded'=>'Jetée'] as $value=>$label)
                        <option value="{{ $value }}" {{ (old('status') ?? $bloodBag->status) == $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('status')
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
                            <option value="{{ $center->id }}" {{ (old('center_id') ?? $bloodBag->center_id) == $center->id ? 'selected' : '' }}>{{ $center->name }}</option>
                        @endforeach
                    </select>
                    @error('center_id')
                        <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>
            @endif
            <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg">Enregistrer</button>
        </form>
    </div>
@endsection 