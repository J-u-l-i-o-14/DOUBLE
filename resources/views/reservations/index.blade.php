@extends('layouts.main')

@section('page-title', 'Gestion des Réservations')

@section('content')
<div class="space-y-6">
    <!-- Statistiques des réservations -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-clipboard-list text-2xl text-gray-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $reservations->total() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-clock text-2xl text-blue-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">En attente</p>
                    <p class="text-2xl font-semibold text-blue-600">{{ $reservations->where('status', 'pending')->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-check-circle text-2xl text-green-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Confirmées</p>
                    <p class="text-2xl font-semibold text-green-600">{{ $reservations->where('status', 'confirmed')->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-times-circle text-2xl text-red-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Annulées</p>
                    <p class="text-2xl font-semibold text-red-600">{{ $reservations->where('status', 'cancelled')->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-flag-checkered text-2xl text-purple-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Terminées</p>
                    <p class="text-2xl font-semibold text-purple-600">{{ $reservations->where('status', 'completed')->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="bg-white p-6 rounded-lg shadow">
        <form method="GET" class="space-y-4">
            <!-- Ligne 1: Recherche par ID -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Recherche par ID</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="ID Réservation ou ID Commande (ex: 123)"
                               class="w-full pl-10 pr-4 py-2 border-gray-300 rounded-md shadow-sm focus:border-red-500 focus:ring-red-500">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                    </div>
                    <small class="text-gray-500 text-xs">Tapez un numéro pour chercher dans les réservations ou commandes</small>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Recherche par nom client</label>
                    <div class="relative">
                        <input type="text" name="client_name" value="{{ request('client_name') }}" 
                               placeholder="Nom du client"
                               class="w-full pl-10 pr-4 py-2 border-gray-300 rounded-md shadow-sm focus:border-red-500 focus:ring-red-500">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-user text-gray-400"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ligne 2: Filtres par statut et date -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                    <select name="status" class="w-full border-gray-300 rounded-md shadow-sm focus:border-red-500 focus:ring-red-500">
                        <option value="">Tous les statuts</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>En attente</option>
                        <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmées</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Annulées</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Terminées</option>
                        <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expirées</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date début</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" 
                           min="{{ date('Y-m-d', strtotime('-2 years')) }}"
                           max="{{ date('Y-m-d') }}"
                           class="w-full border-gray-300 rounded-md shadow-sm focus:border-red-500 focus:ring-red-500">
                    <small class="text-gray-500 text-xs">Date de création</small>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date fin</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" 
                           min="{{ date('Y-m-d', strtotime('-2 years')) }}"
                           max="{{ date('Y-m-d') }}"
                           class="w-full border-gray-300 rounded-md shadow-sm focus:border-red-500 focus:ring-red-500">
                    <small class="text-gray-500 text-xs">Date de création</small>
                </div>

                <div class="flex items-end space-x-2">
                    <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 flex-1">
                        <i class="fas fa-search mr-2"></i>
                        Rechercher
                    </button>
                    <a href="{{ route('reservations.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </div>

            @if(request()->hasAny(['search', 'client_name', 'status', 'date_from', 'date_to']))
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                            <span class="text-blue-800 text-sm">
                                Filtres actifs : 
                                @if(request('search')) <span class="font-medium">ID: {{ request('search') }}</span> @endif
                                @if(request('client_name')) <span class="font-medium">Client: {{ request('client_name') }}</span> @endif
                                @if(request('status')) <span class="font-medium">Statut: {{ ucfirst(request('status')) }}</span> @endif
                                @if(request('date_from') || request('date_to')) 
                                    <span class="font-medium">
                                        Période: {{ request('date_from') ? date('d/m/Y', strtotime(request('date_from'))) : 'Début' }} 
                                        - {{ request('date_to') ? date('d/m/Y', strtotime(request('date_to'))) : 'Fin' }}
                                    </span> 
                                @endif
                            </span>
                        </div>
                        <span class="text-blue-600 text-sm font-medium">{{ $reservations->total() }} résultat(s)</span>
                    </div>
                </div>
            @endif
        </form>
    </div>

    <!-- Liste des réservations -->
    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Liste des Réservations</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-red-600 shadow-sm focus:border-red-500 focus:ring-red-500">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Centre</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Montant</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($reservations as $reservation)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" name="reservation_ids[]" value="{{ $reservation->id }}" 
                                       class="reservation-checkbox rounded border-gray-300 text-red-600 shadow-sm focus:border-red-500 focus:ring-red-500">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    Réservation #{{ $reservation->id }}
                                </div>
                                @if($reservation->order)
                                    <div class="text-xs text-gray-500">
                                        <i class="fas fa-receipt mr-1"></i>
                                        Commande #{{ $reservation->order->id }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $reservation->user->name }}</div>
                                <div class="text-sm text-gray-500">{{ $reservation->user->email }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $reservation->center->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ number_format($reservation->total_amount, 0) }} F CFA
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($reservation->status === 'pending') bg-blue-100 text-blue-800
                                    @elseif($reservation->status === 'confirmed') bg-green-100 text-green-800
                                    @elseif($reservation->status === 'cancelled') bg-red-100 text-red-800
                                    @elseif($reservation->status === 'completed') bg-purple-100 text-purple-800
                                    @elseif($reservation->status === 'expired') bg-gray-100 text-gray-800
                                    @endif">
                                    {{ $reservation->status_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $reservation->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <a href="{{ route('reservations.show', $reservation) }}" 
                                   class="text-blue-600 hover:text-blue-900">
                                    <i class="fas fa-eye"></i>
                                </a>
                                
                                @if($reservation->canBeUpdated())
                                    <button onclick="openStatusModal({{ $reservation->id }}, '{{ $reservation->status }}')" 
                                            class="text-green-600 hover:text-green-900">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                Aucune réservation trouvée
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            {{ $reservations->appends(request()->query())->links() }}
        </div>
    </div>

    <!-- Actions en lot -->
    <div class="bg-white p-6 rounded-lg shadow" id="bulkActions" style="display: none;">
        <h4 class="text-lg font-medium text-gray-900 mb-4">Actions en lot</h4>
        <div class="flex items-center space-x-4">
            <select id="bulkStatus" class="border-gray-300 rounded-md shadow-sm focus:border-red-500 focus:ring-red-500">
                <option value="">Changer le statut vers...</option>
                <option value="confirmed">Confirmée</option>
                <option value="cancelled">Annulée</option>
                <option value="completed">Terminée</option>
                <option value="expired">Expirée</option>
            </select>
            <button onclick="bulkUpdateStatus()" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">
                Appliquer
            </button>
        </div>
    </div>
</div>

<!-- Modal de changement de statut -->
<div id="statusModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"></div>
        <div class="inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-lg">
            <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Modifier le statut</h3>
            
            <form id="statusForm">
                <input type="hidden" id="reservationId">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nouveau statut</label>
                    <select id="newStatus" class="w-full border-gray-300 rounded-md shadow-sm focus:border-red-500 focus:ring-red-500">
                        <option value="pending">En attente</option>
                        <option value="confirmed">Confirmée</option>
                        <option value="cancelled">Annulée</option>
                        <option value="completed">Terminée</option>
                        <option value="expired">Expirée</option>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Note (optionnel)</label>
                    <textarea id="statusNote" rows="3" class="w-full border-gray-300 rounded-md shadow-sm focus:border-red-500 focus:ring-red-500" placeholder="Raison du changement de statut..."></textarea>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeStatusModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                        Annuler
                    </button>
                    <button type="button" onclick="updateStatus()" class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700">
                        Mettre à jour
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Gestion des checkboxes
    document.getElementById('selectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.reservation-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        toggleBulkActions();
    });

    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('reservation-checkbox')) {
            toggleBulkActions();
        }
    });

    function toggleBulkActions() {
        const checkedBoxes = document.querySelectorAll('.reservation-checkbox:checked');
        const bulkActions = document.getElementById('bulkActions');
        bulkActions.style.display = checkedBoxes.length > 0 ? 'block' : 'none';
    }

    // Modal de statut
    function openStatusModal(reservationId, currentStatus) {
        document.getElementById('reservationId').value = reservationId;
        document.getElementById('newStatus').value = currentStatus;
        document.getElementById('statusNote').value = '';
        document.getElementById('statusModal').classList.remove('hidden');
    }

    function closeStatusModal() {
        document.getElementById('statusModal').classList.add('hidden');
    }

    function updateStatus() {
        const reservationId = document.getElementById('reservationId').value;
        const newStatus = document.getElementById('newStatus').value;
        const note = document.getElementById('statusNote').value;

        fetch(`/reservations/${reservationId}/update-status`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                status: newStatus,
                note: note
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeStatusModal();
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

    function bulkUpdateStatus() {
        const checkedBoxes = document.querySelectorAll('.reservation-checkbox:checked');
        const status = document.getElementById('bulkStatus').value;

        if (!status) {
            alert('Veuillez sélectionner un statut');
            return;
        }

        const reservationIds = Array.from(checkedBoxes).map(cb => cb.value);

        if (reservationIds.length === 0) {
            alert('Veuillez sélectionner au moins une réservation');
            return;
        }

        if (confirm(`Mettre à jour ${reservationIds.length} réservation(s) ?`)) {
            fetch('/reservations/bulk-update-status', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    reservation_ids: reservationIds,
                    status: status
                })
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
</script>
@endpush
@endsection
