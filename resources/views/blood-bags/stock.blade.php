@extends('layouts.main')

@section('page-title', 'Gestion des stocks de sang')

@section('content')
<div class="space-y-6">
    <!-- En-tête simple -->
    <div class="bg-white rounded-lg shadow-sm border p-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                    <i class="fas fa-boxes mr-3 text-red-600"></i>Gestion des Stocks de Sang
                </h1>
                <p class="text-gray-600 mt-1">Surveillance des stocks par centre de collecte</p>
            </div>
            <div class="flex space-x-4">
                <a href="{{ route('blood-bags.index') }}" 
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                    <i class="fas fa-list mr-2"></i>Gérer les poches
                </a>
                <a href="{{ route('blood-bags.create') }}" 
                   class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition-colors">
                    <i class="fas fa-plus mr-2"></i>Ajouter des poches
                </a>
            </div>
        </div>
    </div>

    <!-- Résumé des alertes -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-clock text-yellow-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <div class="text-2xl font-bold text-gray-900">{{ $expiringSoonBags }}</div>
                    <div class="text-sm text-gray-600">Expirent bientôt (7 jours)</div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-red-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <div class="text-2xl font-bold text-gray-900">{{ $expiredBags }}</div>
                    <div class="text-sm text-gray-600">Poches expirées</div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-tint text-blue-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    @php
                        $totalStock = $stockByCenter->sum(function($center) {
                            return $center->inventory->sum('available_quantity');
                        });
                    @endphp
                    <div class="text-2xl font-bold text-gray-900">{{ $totalStock }}</div>
                    <div class="text-sm text-gray-600">Stock total disponible</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stocks par centre -->
    <div class="bg-white rounded-lg shadow-sm border">
        <div class="px-6 py-4 border-b">
            <h2 class="text-xl font-semibold text-gray-900">Stocks par Centre</h2>
        </div>
        
        <div class="p-6 space-y-6">
            @foreach($stockByCenter as $center)
                <div class="border rounded-lg p-4">
                    <!-- En-tête du centre -->
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-hospital mr-2 text-red-600"></i>
                                {{ $center->name }}
                            </h3>
                            @if($center->region)
                            <p class="text-sm text-gray-600 flex items-center mt-1">
                                <i class="fas fa-map-marker-alt mr-1"></i>
                                {{ $center->region->name }}
                            </p>
                            @endif
                        </div>
                        <div class="text-right">
                            @php
                                $centerTotal = $center->inventory->sum('available_quantity');
                                $centerReserved = $center->inventory->sum('reserved_quantity');
                            @endphp
                            <div class="text-sm text-gray-600">Total: {{ $centerTotal + $centerReserved }} poches</div>
                            <div class="text-xs text-gray-500">{{ $centerTotal }} disponibles • {{ $centerReserved }} réservées</div>
                        </div>
                    </div>

                    <!-- Tableau des stocks -->
                    @if($center->inventory->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Groupe sanguin</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Disponible</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Réservé</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Réservations actives</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">État</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($center->inventory as $inv)
                                @php
                                    $activeReservations = \App\Models\ReservationItem::with('reservationRequest')
                                        ->whereHas('reservationRequest', function($q) use ($center){
                                            $q->where('center_id', $center->id)
                                              ->whereIn('status',["pending","confirmed"]);
                                        })
                                        ->where('blood_type_id', $inv->blood_type_id)
                                        ->get();
                                @endphp
                                <tr class="hover:bg-gray-50 align-top">
                                    <td class="px-4 py-3">
                                        <div class="flex items-center">
                                            <div class="w-6 h-6 bg-red-100 rounded-full flex items-center justify-center mr-3">
                                                <span class="text-red-700 font-semibold text-xs">{{ optional($inv->bloodType)->group }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">{{ $inv->available_quantity }}</span></td>
                                    <td class="px-4 py-3"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">{{ $inv->reserved_quantity }}</span></td>
                                    <td class="px-4 py-3"><span class="font-medium text-gray-900">{{ $inv->available_quantity + $inv->reserved_quantity }}</span></td>
                                    <td class="px-4 py-3 text-xs text-gray-700 space-y-1">
                                        @forelse($activeReservations as $resItem)
                                            <div class="p-2 bg-gray-50 rounded border border-gray-200">
                                                <div class="flex justify-between">
                                                    <span>#{{ $resItem->reservation_request_id }}</span>
                                                    <span class="font-medium">x{{ $resItem->quantity }}</span>
                                                </div>
                                                <div class="text-[10px] text-gray-500 mt-1 uppercase">{{ $resItem->reservationRequest->status }}</div>
                                            </div>
                                        @empty
                                            <span class="text-gray-400">—</span>
                                        @endforelse
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($inv->available_quantity < 5)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800"><i class="fas fa-exclamation-circle mr-1"></i>Stock faible</span>
                                        @elseif($inv->available_quantity < 10)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800"><i class="fas fa-exclamation-triangle mr-1"></i>Stock moyen</span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"><i class="fas fa-check-circle mr-1"></i>Stock OK</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-inbox text-3xl mb-2"></i>
                        <p>Aucun stock enregistré pour ce centre</p>
                    </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <!-- Alertes du système -->
    @if(isset($alerts) && $alerts->count() > 0)
    <div class="bg-white rounded-lg shadow-sm border">
        <div class="px-6 py-4 border-b">
            <h2 class="text-xl font-semibold text-gray-900 flex items-center">
                <i class="fas fa-bell mr-2 text-yellow-600"></i>
                Alertes Système
            </h2>
        </div>
        <div class="p-6">
            <div class="space-y-3">
                @foreach($alerts as $alert)
                <div class="border-l-4 border-yellow-400 bg-yellow-50 p-4">
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="text-sm font-medium text-yellow-800">{{ ucfirst($alert->type) }}</div>
                            <div class="text-sm text-yellow-700 mt-1">{{ $alert->message }}</div>
                        </div>
                        <div class="text-xs text-gray-500">
                            {{ $alert->created_at->format('d/m/Y H:i') }}
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Actions -->
    <div class="bg-white rounded-lg shadow-sm border p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Actions de Maintenance</h2>
        <div class="flex space-x-4">
            <form action="{{ route('blood-bags.markExpired') }}" method="POST">
                @csrf
                <button type="submit" 
                        class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg transition-colors flex items-center"
                        onclick="return confirm('Voulez-vous marquer toutes les poches expirées ?')">
                    <i class="fas fa-times-circle mr-2"></i>
                    Marquer les poches expirées
                </button>
            </form>
        </div>
    </div>
</div>
@endsection