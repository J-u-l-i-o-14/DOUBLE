@extends('layouts.main')

@section('page-title', 'Mes Commandes')

@section('content')
<div class="space-y-6">
    <!-- En-tête avec statistiques -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-start mb-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    <i class="fas fa-shopping-cart mr-2 text-red-600"></i>Mes Commandes
                </h1>
                <p class="text-gray-600">Gérez vos commandes de poches de sang</p>
            </div>
            <div class="text-right">
                <a href="{{ route('blood.reservation') }}" 
                   class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg transition-colors inline-flex items-center">
                    <i class="fas fa-plus mr-2"></i>Nouvelle commande
                </a>
            </div>
        </div>

        @if($orders->count() > 0)
            <!-- Statistiques rapides -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                @php
                    $totalCommandes = $orders->count();
                    $enAttente = $orders->filter(function($order) { 
                        return $order->reservationRequest ? $order->reservationRequest->status === 'pending' : true; 
                    })->count();
                    $confirmees = $orders->filter(function($order) { 
                        return $order->reservationRequest ? $order->reservationRequest->status === 'confirmed' : false; 
                    })->count();
                    $completees = $orders->filter(function($order) { 
                        return $order->reservationRequest ? $order->reservationRequest->status === 'completed' : false; 
                    })->count();
                @endphp
                <div class="bg-blue-50 p-4 rounded-lg">
                    <div class="text-blue-600 text-2xl font-bold">{{ $totalCommandes }}</div>
                    <div class="text-blue-800 text-sm">Total commandes</div>
                </div>
                <div class="bg-yellow-50 p-4 rounded-lg">
                    <div class="text-yellow-600 text-2xl font-bold">{{ $enAttente }}</div>
                    <div class="text-yellow-800 text-sm">En attente</div>
                </div>
                <div class="bg-green-50 p-4 rounded-lg">
                    <div class="text-green-600 text-2xl font-bold">{{ $confirmees }}</div>
                    <div class="text-green-800 text-sm">Confirmées</div>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <div class="text-gray-600 text-2xl font-bold">{{ $completees }}</div>
                    <div class="text-gray-800 text-sm">Terminées</div>
                </div>
            </div>
        @endif
    </div>

        @if($orders->count() > 0)
            <!-- Liste des commandes en style carte -->
            <div class="grid grid-cols-1 gap-6">
                @foreach($orders as $order)
                <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-200">
                    <div class="p-6">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">
                                    <i class="fas fa-file-medical mr-2 text-red-600"></i>
                                    Commande #{{ $order->id }}
                                </h3>
                                <p class="text-sm text-gray-600">{{ $order->prescription_number }}</p>
                            </div>
                            <div class="text-right">
                                @php
                                    $reservationStatus = $order->reservationRequest ? $order->reservationRequest->status : 'pending';
                                @endphp
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                    @if($reservationStatus === 'pending') bg-yellow-100 text-yellow-800
                                    @elseif($reservationStatus === 'confirmed') bg-blue-100 text-blue-800
                                    @elseif($reservationStatus === 'completed') bg-green-100 text-green-800
                                    @elseif($reservationStatus === 'cancelled') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    @if($reservationStatus === 'pending') ⏳ En attente
                                    @elseif($reservationStatus === 'confirmed') ✅ Confirmée
                                    @elseif($reservationStatus === 'completed') 🎉 Terminée
                                    @elseif($reservationStatus === 'cancelled') ❌ Annulée
                                    @else {{ $reservationStatus }}
                                    @endif
                                </span>
                                @if($order->payment_status === 'partial')
                                <div class="mt-2">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <i class="fas fa-coins mr-1"></i>Acompte payé
                                    </span>
                                </div>
                                @endif
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <!-- Informations médicales -->
                            <div class="space-y-2">
                                <div class="flex items-center text-sm">
                                    <i class="fas fa-tint mr-2 text-red-600"></i>
                                    <span class="font-medium text-gray-700">Groupe:</span>
                                    <span class="ml-1 font-bold text-red-600">{{ $order->blood_type }}</span>
                                </div>
                                <div class="flex items-center text-sm">
                                    <i class="fas fa-vial mr-2 text-blue-600"></i>
                                    <span class="font-medium text-gray-700">Quantité:</span>
                                    <span class="ml-1">{{ $order->quantity }} poche(s)</span>
                                </div>
                                @if($order->doctor_name)
                                <div class="flex items-center text-sm">
                                    <i class="fas fa-user-md mr-2 text-green-600"></i>
                                    <span class="font-medium text-gray-700">Dr.</span>
                                    <span class="ml-1">{{ $order->doctor_name }}</span>
                                </div>
                                @endif
                            </div>

                            <!-- Centre de collecte -->
                            <div class="space-y-2">
                                <div class="flex items-center text-sm">
                                    <i class="fas fa-hospital mr-2 text-purple-600"></i>
                                    <span class="font-medium text-gray-700">Centre:</span>
                                </div>
                                <div class="text-sm text-gray-900 ml-6">{{ $order->center->name }}</div>
                                @if($order->center->region)
                                <div class="text-xs text-gray-500 ml-6">
                                    <i class="fas fa-map-marker-alt mr-1"></i>{{ $order->center->region->name }}
                                </div>
                                @endif
                            </div>

                            <!-- Informations financières -->
                            <div class="space-y-2">
                                @php
                                    $totalAmount = $order->total_amount ?? 0;
                                    $acompte = $order->deposit_amount ?? ($totalAmount * 0.5);
                                    $solde = $order->remaining_amount ?? ($totalAmount - $acompte);
                                @endphp
                                @if($order->payment_status === 'partial')
                                <div class="bg-blue-50 p-3 rounded-lg">
                                    <div class="text-sm font-medium text-blue-800 mb-1">
                                        <i class="fas fa-money-bill-wave mr-1"></i>Acompte payé (50%)
                                    </div>
                                    <div class="text-xs text-blue-700">
                                        Payé: {{ number_format($acompte, 0) }} F CFA
                                    </div>
                                    <div class="text-xs text-blue-700">
                                        Total: {{ number_format($totalAmount, 0) }} F CFA
                                    </div>
                                    <div class="text-xs text-orange-600 font-medium">
                                        Reste: {{ number_format($solde, 0) }} F CFA
                                    </div>
                                </div>
                                @elseif($order->payment_status === 'paid')
                                <div class="bg-green-50 p-3 rounded-lg">
                                    <div class="text-sm font-medium text-green-800">
                                        <i class="fas fa-check-circle mr-1"></i>{{ number_format($totalAmount, 0) }} F CFA
                                    </div>
                                    <div class="text-xs text-green-700">Paiement complet</div>
                                </div>
                                @else
                                <div class="bg-yellow-50 p-3 rounded-lg">
                                    <div class="text-sm font-medium text-yellow-800">
                                        <i class="fas fa-clock mr-1"></i>{{ number_format($order->original_price, 0) }} F CFA
                                    </div>
                                    <div class="text-xs text-yellow-700">En attente de paiement</div>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Informations de paiement -->
                        @if($order->payment_method)
                        <div class="bg-gray-50 p-3 rounded-lg mb-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center text-sm">
                                    <i class="fas fa-credit-card mr-2 text-gray-600"></i>
                                    <span class="font-medium text-gray-700">{{ $order->payment_method_label }}</span>
                                    @if($order->payment_reference)
                                    <span class="ml-2 text-gray-500">• {{ $order->payment_reference }}</span>
                                    @endif
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ $order->created_at->format('d/m/Y H:i') }}
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Actions -->
                        <div class="flex justify-between items-center">
                            <div class="text-sm text-gray-500">
                                <i class="fas fa-calendar mr-1"></i>
                                {{ $order->created_at->format('d/m/Y à H:i') }}
                            </div>
                            <a href="{{ route('orders.show', $order) }}" 
                               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors inline-flex items-center">
                                <i class="fas fa-eye mr-2"></i>Voir détails
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if($orders->hasPages())
            <div class="bg-white rounded-lg shadow-md p-6">
                {{ $orders->links() }}
            </div>
            @endif
        @else
            <!-- Aucune commande -->
            <div class="bg-white rounded-lg shadow-md p-8 text-center">
                <div class="mb-6">
                    <i class="fas fa-shopping-cart text-6xl text-gray-300"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-4">Aucune commande trouvée</h3>
                <p class="text-gray-600 mb-6">Vous n'avez encore passé aucune commande de sang. Commencez dès maintenant pour réserver vos poches de sang.</p>
                <a href="{{ route('blood.reservation') }}" 
                   class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-lg transition-colors inline-flex items-center">
                    <i class="fas fa-plus mr-2"></i>Passer ma première commande
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
