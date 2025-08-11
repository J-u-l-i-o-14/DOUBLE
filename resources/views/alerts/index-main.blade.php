@extends('layouts.main')

@section('page-title', 'Gestion des Alertes')

@section('content')
<div class="space-y-6">
    <!-- Header avec actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Gestion des Alertes</h1>
            <p class="mt-2 text-sm text-gray-600">
                Surveillez et gérez les alertes de stock et d'expiration de votre centre
            </p>
        </div>
        <div class="mt-4 sm:mt-0 flex flex-col sm:flex-row gap-3">
            <button type="button" 
                    class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors duration-200"
                    onclick="generateAlerts()">
                <i class="fas fa-sync-alt mr-2"></i>
                Générer les alertes
            </button>
            @if(auth()->user()->role === 'admin')
                <div class="relative inline-block text-left">
                    <button type="button" 
                            class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors duration-200"
                            onclick="toggleResolveAllDropdown()">
                        <i class="fas fa-check-double mr-2"></i>
                        Résoudre tout
                        <i class="fas fa-chevron-down ml-2"></i>
                    </button>
                    <div id="resolveAllDropdown" class="hidden absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50">
                        <div class="py-1">
                            <a href="#" 
                               class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                               onclick="resolveAllByType('low_stock')">
                                <i class="fas fa-tint mr-2 text-red-500"></i>
                                Alertes de stock faible
                            </a>
                            <a href="#" 
                               class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                               onclick="resolveAllByType('expiration')">
                                <i class="fas fa-clock mr-2 text-yellow-500"></i>
                                Alertes d'expiration
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Statistiques des alertes -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white overflow-hidden shadow-lg rounded-lg border-l-4 border-blue-500">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-bell text-2xl text-blue-500"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Alertes</dt>
                            <dd class="text-3xl font-bold text-gray-900">{{ $stats['total'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-lg rounded-lg border-l-4 border-red-500">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-2xl text-red-500"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Alertes Actives</dt>
                            <dd class="text-3xl font-bold text-gray-900">{{ $stats['active'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-lg rounded-lg border-l-4 border-orange-500">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-tint text-2xl text-orange-500"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Stock Faible</dt>
                            <dd class="text-3xl font-bold text-gray-900">{{ $stats['low_stock'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-lg rounded-lg border-l-4 border-yellow-500">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-clock text-2xl text-yellow-500"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Expirations</dt>
                            <dd class="text-3xl font-bold text-gray-900">{{ $stats['expiration'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres et recherche -->
    <div class="bg-white shadow-sm rounded-lg">
        <div class="p-6">
            <form method="GET" action="{{ route('alerts.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                        <select name="status" id="status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                            <option value="">Tous les statuts</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Actives</option>
                            <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Résolues</option>
                        </select>
                    </div>

                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                        <select name="type" id="type" class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                            <option value="">Tous les types</option>
                            <option value="low_stock" {{ request('type') == 'low_stock' ? 'selected' : '' }}>Stock faible</option>
                            <option value="expiration" {{ request('type') == 'expiration' ? 'selected' : '' }}>Expiration</option>
                        </select>
                    </div>

                    <div>
                        <label for="blood_type" class="block text-sm font-medium text-gray-700 mb-1">Groupe sanguin</label>
                        <select name="blood_type" id="blood_type" class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                            <option value="">Tous les groupes</option>
                            @foreach(\App\Models\BloodType::all() as $bloodType)
                                <option value="{{ $bloodType->id }}" {{ request('blood_type') == $bloodType->id ? 'selected' : '' }}>
                                    {{ $bloodType->group }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Date depuis</label>
                        <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" 
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                    </div>

                    <div class="flex items-end">
                        <button type="submit" 
                                class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors duration-200">
                            <i class="fas fa-search mr-2"></i>
                            Filtrer
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des alertes -->
    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">
                Alertes 
                @if($alerts->count() > 0)
                    <span class="text-sm text-gray-500">({{ $alerts->total() }} résultats)</span>
                @endif
            </h3>
        </div>
        
        @if($alerts->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type & Statut</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Groupe sanguin</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Message</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($alerts as $alert)
                            <tr class="hover:bg-gray-50 transition-colors duration-200">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col space-y-1">
                                        @if($alert->type === 'low_stock')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                <i class="fas fa-tint mr-1"></i> Stock Critique
                                            </span>
                                        @elseif($alert->type === 'expiration')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                <i class="fas fa-clock mr-1"></i> Expiration
                                            </span>
                                        @endif
                                        @if($alert->resolved)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <i class="fas fa-check mr-1"></i> Résolu
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                                <i class="fas fa-exclamation mr-1"></i> Actif
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($alert->bloodType)
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium 
                                            @if(str_contains($alert->bloodType->group, 'A+') || str_contains($alert->bloodType->group, 'A-')) bg-blue-100 text-blue-800
                                            @elseif(str_contains($alert->bloodType->group, 'B+') || str_contains($alert->bloodType->group, 'B-')) bg-purple-100 text-purple-800
                                            @elseif(str_contains($alert->bloodType->group, 'AB')) bg-green-100 text-green-800
                                            @elseif(str_contains($alert->bloodType->group, 'O')) bg-orange-100 text-orange-800
                                            @else bg-gray-100 text-gray-800 @endif">
                                            {{ $alert->bloodType->group }}
                                        </span>
                                    @else
                                        <span class="text-gray-500">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">{{ $alert->message }}</div>
                                    @if($alert->resolved_at)
                                        <div class="text-xs text-gray-500 mt-1">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            Résolu le {{ $alert->resolved_at->format('d/m/Y à H:i') }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div>{{ $alert->created_at->format('d/m/Y') }}</div>
                                    <div class="text-xs">{{ $alert->created_at->format('H:i') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        @if(!$alert->resolved)
                                            <button class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-green-600 hover:bg-green-700 transition-colors" 
                                                    onclick="resolveAlert({{ $alert->id }})">
                                                <i class="fas fa-check mr-1"></i>
                                                Résoudre
                                            </button>
                                        @else
                                            <button class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-orange-600 hover:bg-orange-700 transition-colors" 
                                                    onclick="unresolveAlert({{ $alert->id }})">
                                                <i class="fas fa-undo mr-1"></i>
                                                Réactiver
                                            </button>
                                        @endif
                                        @if(auth()->user()->role === 'admin')
                                            <button class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-red-600 hover:bg-red-700 transition-colors" 
                                                    onclick="deleteAlert({{ $alert->id }})">
                                                <i class="fas fa-trash mr-1"></i>
                                                Supprimer
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $alerts->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <i class="fas fa-check-circle text-green-500 text-4xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Aucune alerte trouvée</h3>
                <p class="text-gray-500">
                    @if(request()->hasAny(['status', 'type', 'blood_type', 'date_from']))
                        Aucune alerte ne correspond à vos critères de recherche.
                    @else
                        Excellente nouvelle ! Votre centre n'a aucune alerte active.
                    @endif
                </p>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    // Fonctions pour gérer les alertes
    function resolveAlert(alertId) {
        if (confirm('Marquer cette alerte comme résolue ?')) {
            fetch(`/alerts/${alertId}/resolve`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Erreur: ' + (data.message || 'Une erreur est survenue'));
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Erreur de communication avec le serveur');
            });
        }
    }

    function unresolveAlert(alertId) {
        if (confirm('Réactiver cette alerte ?')) {
            fetch(`/alerts/${alertId}/unresolve`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Erreur: ' + (data.message || 'Une erreur est survenue'));
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Erreur de communication avec le serveur');
            });
        }
    }

    function deleteAlert(alertId) {
        if (confirm('Êtes-vous sûr de vouloir supprimer définitivement cette alerte ?')) {
            fetch(`/alerts/${alertId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Erreur: ' + (data.message || 'Une erreur est survenue'));
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Erreur de communication avec le serveur');
            });
        }
    }

    function generateAlerts() {
        if (confirm('Générer les alertes basées sur le stock actuel ?')) {
            fetch('/alerts/generate', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Alertes générées avec succès !');
                    location.reload();
                } else {
                    alert('Erreur: ' + (data.message || 'Une erreur est survenue'));
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Erreur de communication avec le serveur');
            });
        }
    }

    function resolveAllByType(type) {
        const typeNames = {
            'low_stock': 'de stock faible',
            'expiration': 'd\'expiration'
        };
        
        if (confirm(`Résoudre toutes les alertes ${typeNames[type]} ?`)) {
            fetch('/alerts/resolve-all', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    type: type
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(`${data.resolved_count} alertes ${typeNames[type]} ont été résolues !`);
                    location.reload();
                } else {
                    alert('Erreur: ' + (data.message || 'Une erreur est survenue'));
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Erreur de communication avec le serveur');
            });
        }
    }

    function toggleResolveAllDropdown() {
        const dropdown = document.getElementById('resolveAllDropdown');
        dropdown.classList.toggle('hidden');
    }

    // Fermer le dropdown si on clique ailleurs
    document.addEventListener('click', function(event) {
        const dropdown = document.getElementById('resolveAllDropdown');
        const button = event.target.closest('button');
        
        if (!button || !button.onclick || button.onclick.toString().indexOf('toggleResolveAllDropdown') === -1) {
            dropdown.classList.add('hidden');
        }
    });
</script>
@endpush
@endsection
