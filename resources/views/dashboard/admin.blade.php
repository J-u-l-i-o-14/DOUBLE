@extends('layouts.main')

@section('page-title', 'Dashboard Administrateur')

@section('content')
    <!-- Bouton Gérer les utilisateurs
    <div class="mb-8 flex justify-end">
        <a href="{{ route('users.index') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-6 rounded-lg shadow inline-flex items-center">
            <i class="fas fa-users mr-2"></i> Gérer les utilisateurs
        </a>
    </div> -->
    <!-- Statistiques principales -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <x-stat-card 
            title="Total Donneurs" 
            :value="$stats['total_donors']" 
            icon="fas fa-users" 
            color="blue" />
        <x-stat-card 
            title="Poches Disponibles" 
            :value="$stats['total_blood_bags']" 
            icon="fas fa-tint" 
            color="red" />
        <x-stat-card 
            title="Dons ce Mois" 
            :value="$stats['total_donations_this_month']" 
            icon="fas fa-heart" 
            color="green" />
        <x-stat-card 
            title="Transfusions ce Mois" 
            :value="$stats['total_transfusions_this_month']" 
            icon="fas fa-syringe" 
            color="purple" />
    </div>

    <!-- Statistiques financières Sprint 5 -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-300 bg-opacity-30">
                    <i class="fas fa-coins fa-2x"></i>
                </div>
                <div class="ml-4">
                    <h4 class="text-lg font-semibold">Chiffre d'affaires total</h4>
                    <p class="text-2xl font-bold">{{ number_format($stats['total_revenue'], 0, ',', ' ') }} F CFA</p>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-300 bg-opacity-30">
                    <i class="fas fa-chart-line fa-2x"></i>
                </div>
                <div class="ml-4">
                    <h4 class="text-lg font-semibold">Revenus ce mois</h4>
                    <p class="text-2xl font-bold">{{ number_format($stats['monthly_revenue'], 0, ',', ' ') }} F CFA</p>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-orange-300 bg-opacity-30">
                    <i class="fas fa-clock fa-2x"></i>
                </div>
                <div class="ml-4">
                    <h4 class="text-lg font-semibold">Revenus en attente</h4>
                    <p class="text-2xl font-bold">{{ number_format($stats['pending_revenue'], 0, ',', ' ') }} F CFA</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Graphique des dons -->
        <x-chart-card 
            title="Évolution des Dons (6 derniers mois)" 
            type="line" 
            :data="$donationsChart['data']" 
            :labels="$donationsChart['labels']" 
            id="donationsChart" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Graphique des revenus Sprint 5 -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">
                <i class="fas fa-chart-bar mr-2"></i>Évolution du Chiffre d'Affaires (6 derniers mois)
            </h3>
            <canvas id="revenueChart" width="400" height="200"></canvas>
        </div>

        <!-- Prochaines campagnes -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Prochaines Campagnes</h3>
            @if($upcomingCampaigns->count() > 0)
                <div class="space-y-4">
                    @foreach($upcomingCampaigns as $campaign)
                        <div class="border-l-4 border-red-500 pl-4">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="font-medium text-gray-900">{{ $campaign->name }}</h4>
                                    <p class="text-sm text-gray-600 mt-1">
                                        <i class="fas fa-map-marker-alt mr-1"></i>{{ $campaign->location }}
                                    </p>
                                    <p class="text-sm text-gray-500 mt-1">
                                        <i class="fas fa-calendar mr-1"></i>{{ optional($campaign->campaign_date)->format('d/m/Y') }}
                                    </p>
                                </div>
                                <x-status-badge :status="$campaign->status" type="campaign" />
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500">Aucune campagne prévue</p>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Transactions récentes Sprint 5 -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">
                <i class="fas fa-money-bill-wave mr-2"></i>Transactions Récentes
            </h3>
            @if($recentTransactions->count() > 0)
                <div class="space-y-3">
                    @foreach($recentTransactions->take(5) as $transaction)
                        <div class="flex justify-between items-center border-b border-gray-100 pb-2">
                            <div>
                                <p class="font-medium text-gray-900">{{ $transaction->user->name ?? 'Client' }}</p>
                                <p class="text-sm text-gray-500">
                                    {{ $transaction->prescription_number }} - {{ $transaction->blood_type }}
                                </p>
                                <p class="text-xs text-gray-400">{{ $transaction->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                            <div class="text-right">
                                @if($transaction->payment_status === 'partial')
                                    <p class="font-bold text-orange-600">
                                        {{ number_format($transaction->deposit_amount ?? ($transaction->total_amount * 0.5), 0, ',', ' ') }} F CFA
                                        <span class="text-xs text-gray-500">(Acompte 50%)</span>
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        Total: {{ number_format($transaction->total_amount, 0, ',', ' ') }} F CFA
                                    </p>
                                @else
                                    <p class="font-bold text-green-600">{{ number_format($transaction->total_amount, 0, ',', ' ') }} F CFA</p>
                                @endif
                                <span class="text-xs px-2 py-1 rounded-full 
                                    {{ $transaction->payment_status === 'paid' ? 'bg-green-100 text-green-800' : 
                                       ($transaction->payment_status === 'partial' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                    {{ $transaction->payment_status_label }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-4 text-center">
                    <a href="{{ route('orders.index') }}" class="text-red-600 hover:text-red-800 text-sm font-medium">
                        Voir toutes les transactions →
                    </a>
                </div>
            @else
                <p class="text-gray-500">Aucune transaction récente</p>
            @endif
        </div>

        <!-- Rendez-vous récents -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Rendez-vous Récents</h3>
            @if($recentAppointments->count() > 0)
                <div class="space-y-4">
                    @foreach($recentAppointments->take(5) as $appointment)
                        <div class="border-l-4 border-blue-500 pl-4">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="font-medium text-gray-900">{{ $appointment->donor->name }}</h4>
                                    <p class="text-sm text-gray-600 mt-1">
                                        {{ $appointment->type_display }}
                                        @if($appointment->campaign)
                                            - {{ $appointment->campaign->name }}
                                        @endif
                                    </p>
                                    <p class="text-sm text-gray-500 mt-1">{{ $appointment->formatted_date }}</p>
                                </div>
                                <x-status-badge :status="$appointment->status" type="appointment" />
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500">Aucun rendez-vous récent</p>
            @endif
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
        <a href="{{ route('users.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-3 px-4 rounded-lg text-center transition-colors duration-200">
            <i class="fas fa-user-plus mr-2"></i>Ajouter Donneur
        </a>
        <a href="{{ route('blood-bags.create') }}" class="bg-red-500 hover:bg-red-600 text-white font-medium py-3 px-4 rounded-lg text-center transition-colors duration-200">
            <i class="fas fa-tint mr-2"></i>Ajouter Poche
        </a>
        <a href="{{ route('campaigns.create') }}" class="bg-green-500 hover:bg-green-600 text-white font-medium py-3 px-4 rounded-lg text-center transition-colors duration-200">
            <i class="fas fa-bullhorn mr-2"></i>Nouvelle Campagne
        </a>
        <a href="{{ route('stock-thresholds.index') }}" class="bg-yellow-500 hover:bg-yellow-600 text-white font-medium py-3 px-4 rounded-lg text-center transition-colors duration-200">
            <i class="fas fa-cog mr-2"></i>Seuils d'Alerte
        </a>
        <a href="{{ route('blood-bags.stock') }}" class="bg-indigo-500 hover:bg-indigo-600 text-white font-medium py-3 px-4 rounded-lg text-center transition-colors duration-200">
            <i class="fas fa-chart-bar mr-2"></i>Voir Stock
        </a>
    </div>

    <!-- Scripts Chart.js pour les revenus -->
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Graphique des revenus
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        new Chart(revenueCtx, {
            type: 'bar',
            data: {
                labels: @json($revenueChart['labels']),
                datasets: [{
                    label: 'Chiffre d\'affaires (F CFA)',
                    data: @json($revenueChart['data']),
                    backgroundColor: 'rgba(34, 197, 94, 0.8)',
                    borderColor: 'rgba(34, 197, 94, 1)',
                    borderWidth: 2,
                    borderRadius: 8,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + new Intl.NumberFormat('fr-FR').format(context.parsed.y) + ' F CFA';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('fr-FR', { 
                                    notation: 'compact',
                                    compactDisplay: 'short'
                                }).format(value) + ' F CFA';
                            }
                        }
                    }
                }
            }
        });
    </script>
    @endpush
@endsection