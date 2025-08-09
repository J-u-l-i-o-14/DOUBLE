@extends('layouts.main')

@section('title', 'Dashboard Manager')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-gray-50 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <h2 class="text-2xl font-bold mb-6">Dashboard Manager</h2>

                <!-- Statistiques principales -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4 mb-8">
                    <div class="bg-slate-50 border border-slate-200 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-slate-700">{{ $stats['total_campaigns'] }}</div>
                        <div class="text-sm text-gray-600">Campagnes totales</div>
                    </div>
                    <div class="bg-gray-50 border border-gray-200 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-gray-700">{{ $stats['upcoming_campaigns'] }}</div>
                        <div class="text-sm text-gray-600">Campagnes à venir</div>
                    </div>
                    <div class="bg-stone-50 border border-stone-200 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-stone-700">{{ $stats['pending_appointments'] }}</div>
                        <div class="text-sm text-gray-600">RDV en attente</div>
                    </div>
                    <div class="bg-zinc-50 border border-zinc-200 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-zinc-700">{{ $stats['total_donors'] }}</div>
                        <div class="text-sm text-gray-600">Donneurs</div>
                    </div>
                    <div class="bg-neutral-50 border border-neutral-200 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-neutral-700">{{ $stats['total_blood_bags'] }}</div>
                        <div class="text-sm text-gray-600">Poches disponibles</div>
                    </div>
                    <div class="bg-slate-50 border border-slate-200 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-slate-700">{{ $stats['total_reservations'] }}</div>
                        <div class="text-sm text-gray-600">Réservations totales</div>
                    </div>
                </div>

                <!-- Statistiques des réservations -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                    <div class="bg-amber-100 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-amber-600">{{ $stats['pending_reservations'] }}</div>
                        <div class="text-sm text-gray-600">Réservations en attente</div>
                    </div>
                    <div class="bg-emerald-100 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-emerald-600">{{ $stats['confirmed_reservations'] }}</div>
                        <div class="text-sm text-gray-600">Réservations confirmées</div>
                    </div>
                </div>

                <!-- Statistiques financières - Sprint 5 -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                    <div class="bg-emerald-50 border border-emerald-200 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-emerald-700">💰 {{ number_format($stats['total_revenue']) }} F CFA</div>
                        <div class="text-sm text-emerald-600">💼 Chiffre d'affaires total</div>
                    </div>
                    <div class="bg-green-50 border border-green-200 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-green-700">💵 {{ number_format($stats['monthly_revenue']) }} F CFA</div>
                        <div class="text-sm text-green-600">📅 Revenus ce mois</div>
                    </div>
                    <div class="bg-teal-50 border border-teal-200 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-teal-700">⏳ {{ number_format($stats['pending_revenue']) }} F CFA</div>
                        <div class="text-sm text-teal-600">💳 Revenus en attente</div>
                    </div>
                </div>

                <!-- Alertes -->
                @if($alerts['expired_bags'] > 0 || $alerts['expiring_soon_bags'] > 0 || !empty($alerts['low_stock_types']))
                <div class="mb-8">
                    <h3 class="text-lg font-semibold mb-4 text-gray-700">⚠️ Alertes</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @if($alerts['expired_bags'] > 0)
                        <div class="bg-orange-50 border border-orange-200 p-4 rounded-lg">
                            <div class="text-lg font-bold text-orange-700">{{ $alerts['expired_bags'] }}</div>
                            <div class="text-sm text-orange-600">Poches expirées</div>
                        </div>
                        @endif
                        @if($alerts['expiring_soon_bags'] > 0)
                        <div class="bg-amber-50 border border-amber-200 p-4 rounded-lg">
                            <div class="text-lg font-bold text-amber-700">{{ $alerts['expiring_soon_bags'] }}</div>
                            <div class="text-sm text-amber-600">Poches expirant bientôt</div>
                        </div>
                        @endif
                        @if(!empty($alerts['low_stock_types']))
                        <div class="bg-yellow-50 border border-yellow-200 p-4 rounded-lg">
                            <div class="text-lg font-bold text-yellow-700">{{ count($alerts['low_stock_types']) }}</div>
                            <div class="text-sm text-yellow-600">Groupes en stock faible</div>
                        </div>
                        @endif
                    </div>
                </div>
                @endif
    @if(isset($alerts['active_alerts']) && $alerts['active_alerts']->count() > 0)
        <div class="mb-8">
            <h3 class="text-lg font-semibold mb-2 text-gray-700 flex items-center"><i class="fas fa-bell mr-2"></i> Alertes actives du centre</h3>
            <ul class="space-y-2">
                @foreach($alerts['active_alerts'] as $alert)
                    <li class="bg-orange-50 border-l-4 border-orange-300 p-3 rounded flex items-center">
                        <i class="fas fa-bell text-orange-600 mr-2"></i>
                        <span class="text-orange-800">[{{ ucfirst($alert->type) }}] {{ $alert->message }}</span>
                        <span class="ml-auto text-xs text-gray-500">{{ $alert->created_at->format('d/m/Y H:i') }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Prochaines campagnes -->
                    <div class="bg-white border rounded-lg p-6">
                        <h3 class="text-lg font-semibold mb-4">Prochaines campagnes</h3>
                        <div class="space-y-3">
                            @forelse($upcomingCampaigns as $campaign)
                            <div class="border-l-4 border-blue-500 pl-4">
                                <div class="font-medium">{{ $campaign->name }}</div>
                                <div class="text-sm text-gray-600">
                                    {{ optional($campaign->campaign_date)->format('d/m/Y H:i') }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ $campaign->location }}
                                </div>
                            </div>
                            @empty
                            <div class="text-gray-500 text-center py-4">
                                Aucune campagne à venir
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Rendez-vous récents -->
                <div class="mt-8">
                    <h3 class="text-lg font-semibold mb-4">Rendez-vous récents</h3>
                    <div class="bg-white border rounded-lg overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Donneur
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Date
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Campagne
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Statut
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($recentAppointments as $appointment)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $appointment->donor->name }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                {{ optional($appointment->appointment_date)->format('d/m/Y H:i') }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                {{ $appointment->campaign->name ?? 'Centre' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                {{ $appointment->status === 'planifie' ? 'bg-slate-100 text-slate-700' : '' }}
                                                {{ $appointment->status === 'confirme' ? 'bg-gray-100 text-gray-700' : '' }}
                                                {{ $appointment->status === 'complete' ? 'bg-stone-100 text-stone-700' : '' }}
                                                {{ $appointment->status === 'annule' ? 'bg-zinc-100 text-zinc-700' : '' }}">
                                                {{ ucfirst($appointment->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                            Aucun rendez-vous récent
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Actions rapides -->
                <div class="mt-6 mb-8">
                    <h3 class="text-lg font-semibold mb-4 text-gray-700">Actions rapides</h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <a href="{{ route('users.create') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 hover:text-gray-800 font-medium py-3 px-4 rounded-lg text-center transition-colors duration-200 border border-gray-300">
                            <i class="fas fa-user-plus mr-2 text-gray-600"></i>Ajouter Donneur
                        </a>
                        <a href="{{ route('blood-bags.create') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 hover:text-gray-800 font-medium py-3 px-4 rounded-lg text-center transition-colors duration-200 border border-gray-300">
                            <i class="fas fa-tint mr-2 text-gray-600"></i>Ajouter Poche
                        </a>
                        <a href="{{ route('campaigns.create') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 hover:text-gray-800 font-medium py-3 px-4 rounded-lg text-center transition-colors duration-200 border border-gray-300">
                            <i class="fas fa-bullhorn mr-2 text-gray-600"></i>Nouvelle Campagne
                        </a>
                        <a href="{{ route('blood-bags.stock') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 hover:text-gray-800 font-medium py-3 px-4 rounded-lg text-center transition-colors duration-200 border border-gray-300">
                            <i class="fas fa-chart-bar mr-2 text-gray-600"></i>Voir Stock
                        </a>
                    </div>
                </div>

                <!-- Graphiques de chiffre d'affaires - Sprint 5 -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <!-- Graphique des revenus -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold mb-4 text-green-700">
                            <i class="fas fa-chart-line mr-2 text-green-600"></i>💹 Évolution du Chiffre d'Affaires
                        </h3>
                        <canvas id="revenueChart" width="400" height="200"></canvas>
                    </div>

                    <!-- Transactions récentes -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold mb-4 text-gray-900">
                            <i class="fas fa-money-bill-wave mr-2 text-green-600"></i>💰 Transactions Récentes
                        </h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Montant</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($recentTransactions as $transaction)
                                    <tr>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $transaction->user->name }}
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                            @if($transaction->payment_status === 'partial')
                                                <div>
                                                    <span class="font-bold text-orange-600">
                                                        {{ number_format($transaction->deposit_amount ?? ($transaction->total_amount * 0.5)) }} F CFA
                                                    </span>
                                                    <span class="text-xs text-gray-500">(Acompte)</span>
                                                    <br>
                                                    <span class="text-xs text-gray-500">
                                                        Total: {{ number_format($transaction->total_amount) }} F CFA
                                                    </span>
                                                </div>
                                            @else
                                                {{ number_format($transaction->total_amount) }} F CFA
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $transaction->created_at->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                {{ $transaction->payment_status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                                                {{ $transaction->payment_status === 'partial' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                {{ $transaction->payment_status === 'failed' ? 'bg-red-100 text-red-800' : '' }}">
                                                @if($transaction->payment_status === 'completed')
                                                    💰 {{ ucfirst($transaction->payment_status) }}
                                                @elseif($transaction->payment_status === 'partial')
                                                    💳 {{ ucfirst($transaction->payment_status) }}
                                                @else
                                                    {{ ucfirst($transaction->payment_status) }}
                                                @endif
                                            </span>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-4 text-center text-gray-500">
                                            Aucune transaction récente
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Réservations récentes -->
                    <div class="bg-white rounded-lg shadow-md">
                        <div class="p-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800">
                                <i class="fas fa-calendar-check mr-2 text-green-600"></i>Réservations Récentes
                            </h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Articles</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @forelse($recentReservations as $reservation)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $reservation->user->name }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ $reservation->user->email }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-4">
                                            <div class="text-sm text-gray-900">
                                                @foreach($reservation->items as $item)
                                                    <div>{{ $item->quantity }}x {{ $item->bloodType->group }}</div>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td class="px-4 py-4">
                                            @php
                                                $statusClasses = [
                                                    'pending' => 'bg-slate-100 text-slate-700',
                                                    'confirmed' => 'bg-gray-100 text-gray-700',
                                                    'completed' => 'bg-stone-100 text-stone-700',
                                                    'cancelled' => 'bg-zinc-100 text-zinc-700',
                                                    'expired' => 'bg-neutral-100 text-neutral-700'
                                                ];
                                                $statusLabels = [
                                                    'pending' => 'En attente',
                                                    'confirmed' => 'Confirmée',
                                                    'completed' => 'Complétée',
                                                    'cancelled' => 'Annulée',
                                                    'expired' => 'Expirée'
                                                ];
                                            @endphp
                                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $statusClasses[$reservation->status] ?? 'bg-gray-100 text-gray-800' }}">
                                                {{ $statusLabels[$reservation->status] ?? ucfirst($reservation->status) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 text-sm text-gray-500">
                                            {{ $reservation->created_at->format('d/m/Y H:i') }}
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-4 text-center text-gray-500">
                                            Aucune réservation récente
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Graphique des revenus
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    const revenueChart = new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($revenueChart['labels']) !!},
            datasets: [{
                label: 'Chiffre d\'affaires (F CFA)',
                data: {!! json_encode($revenueChart['data']) !!},
                borderColor: 'rgb(34, 197, 94)',
                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Évolution des revenus (6 derniers mois)'
                },
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return new Intl.NumberFormat('fr-FR').format(value) + ' F CFA';
                        }
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });
</script>
@endsection 