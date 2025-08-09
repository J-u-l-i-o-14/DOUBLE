@extends('layouts.main')

@section('page-title', 'Gestion des poches de sang')

@section('content')
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <h2 class="text-xl font-semibold">
            <i class="fas fa-tint mr-2 text-red-600"></i>Gestion des poches de sang
            @if(isset($stats))
                <span class="text-sm font-normal text-gray-600">({{ number_format($stats['total']) }} poches)</span>
            @endif
        </h2>
        <form method="GET" action="" class="flex flex-wrap gap-2 items-center">
            <select name="blood_type_id" class="border rounded px-2 py-1 text-sm">
                <option value="">Tous groupes</option>
                @foreach($bloodTypes as $type)
                    <option value="{{ $type->id }}" @if(request('blood_type_id') == $type->id) selected @endif>{{ $type->group }}</option>
                @endforeach
            </select>
            <select name="status" class="border rounded px-2 py-1 text-sm">
                <option value="">Tous statuts</option>
                <option value="available" @if(request('status')=='available') selected @endif>Disponible</option>
                <option value="reserved" @if(request('status')=='reserved') selected @endif>Réservée</option>
                <option value="transfused" @if(request('status')=='transfused') selected @endif>Transfusée</option>
                <option value="expired" @if(request('status')=='expired') selected @endif>Expirée</option>
                <option value="discarded" @if(request('status')=='discarded') selected @endif>Jetée</option>
            </select>
            <button type="submit" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold px-3 py-1 rounded">Filtrer</button>
        </form>
        <a href="{{ route('blood-bags.create') }}" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg inline-flex items-center">
            <i class="fas fa-plus mr-2"></i> Ajouter Poche(s)
        </a>
    </div>
    
    @if(auth()->user()->is_admin || auth()->user()->is_manager)
    <div class="mb-4 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <h3 class="text-lg font-semibold text-blue-800 mb-2">
            <i class="fas fa-hospital mr-2"></i>Centre : {{ auth()->user()->center->name }}
        </h3>
        <p class="text-blue-600 text-sm mb-3">Vous gérez les poches de sang de ce centre uniquement.</p>
        
        @if(isset($stats))
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-lg p-3 text-center border">
                <div class="text-2xl font-bold text-gray-800">{{ number_format($stats['total']) }}</div>
                <div class="text-sm text-gray-600">Total poches</div>
            </div>
            <div class="bg-white rounded-lg p-3 text-center border">
                <div class="text-2xl font-bold text-green-600">{{ number_format($stats['available']) }}</div>
                <div class="text-sm text-gray-600">Disponibles</div>
            </div>
            <div class="bg-white rounded-lg p-3 text-center border">
                <div class="text-2xl font-bold text-yellow-600">{{ number_format($stats['reserved']) }}</div>
                <div class="text-sm text-gray-600">Réservées</div>
            </div>
            <div class="bg-white rounded-lg p-3 text-center border">
                <div class="text-2xl font-bold text-red-600">{{ number_format($stats['expired']) }}</div>
                <div class="text-sm text-gray-600">Expirées</div>
            </div>
        </div>
        @endif
    </div>
    @endif
    <div class="bg-white rounded-lg shadow border border-gray-200 p-4">
        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Groupe</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date collecte</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Expiration</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bloodBags as $bag)
                    <tr class="border-b">
                        <td class="px-4 py-2">{{ optional($bag->bloodType)->group }}</td>
                        <td class="px-4 py-2">{{ optional($bag->collected_at)->format('d/m/Y') }}</td>
                        <td class="px-4 py-2">{{ optional($bag->expires_at)->format('d/m/Y') }}</td>
                        <td class="px-4 py-2">
                            @switch($bag->status)
                                @case('available') <span class="text-green-600">Disponible</span> @break
                                @case('reserved') <span class="text-yellow-600">Réservée</span> @break
                                @case('transfused') <span class="text-blue-600">Transfusée</span> @break
                                @case('expired') <span class="text-red-600">Expirée</span> @break
                                @case('discarded') <span class="text-gray-600">Jetée</span> @break
                                @default <span>{{ $bag->status }}</span>
                            @endswitch
                        </td>
                        <td class="px-4 py-2 flex space-x-2">
                            <a href="{{ route('blood-bags.edit', $bag) }}" class="text-blue-600 hover:underline"><i class="fas fa-edit"></i> Modifier</a>
                            <form action="{{ route('blood-bags.destroy', $bag) }}" method="POST" onsubmit="return confirm('Supprimer cette poche ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline"><i class="fas fa-trash"></i> Supprimer</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="mt-4">
            {{ $bloodBags->appends(request()->query())->links() }}
        </div>
    </div>
@endsection 