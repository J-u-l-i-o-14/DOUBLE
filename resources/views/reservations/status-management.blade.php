@extends('layouts.main')

@section('page-title', 'Gestion des Statuts de Réservation')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Statistiques des statuts -->
    <div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-8">
        <a href="{{ route('reservations.status.index') }}" 
           class="bg-gray-100 hover:bg-gray-200 p-4 rounded-lg text-center transition-colors {{ request('status') == '' ? 'ring-2 ring-red-500' : '' }}">
            <div class="text-2xl font-bold text-gray-700">{{ $statusStats['all'] }}</div>
            <div class="text-sm text-gray-600">Toutes</div>
        </a>
        
        <a href="{{ route('reservations.status.index', ['status' => 'pending']) }}" 
           class="bg-yellow-100 hover:bg-yellow-200 p-4 rounded-lg text-center transition-colors {{ request('status') == 'pending' ? 'ring-2 ring-yellow-500' : '' }}">
            <div class="text-2xl font-bold text-yellow-700">{{ $statusStats['pending'] }}</div>
            <div class="text-sm text-yellow-600">En attente</div>
        </a>
        
        <a href="{{ route('reservations.status.index', ['status' => 'confirmed']) }}" 
           class="bg-blue-100 hover:bg-blue-200 p-4 rounded-lg text-center transition-colors {{ request('status') == 'confirmed' ? 'ring-2 ring-blue-500' : '' }}">
            <div class="text-2xl font-bold text-blue-700">{{ $statusStats['confirmed'] }}</div>
            <div class="text-sm text-blue-600">Confirmées</div>
        </a>
        
        <a href="{{ route('reservations.status.index', ['status' => 'completed']) }}" 
           class="bg-green-100 hover:bg-green-200 p-4 rounded-lg text-center transition-colors {{ request('status') == 'completed' ? 'ring-2 ring-green-500' : '' }}">
            <div class="text-2xl font-bold text-green-700">{{ $statusStats['completed'] }}</div>
            <div class="text-sm text-green-600">Terminées</div>
        </a>
        
        <a href="{{ route('reservations.status.index', ['status' => 'cancelled']) }}" 
           class="bg-red-100 hover:bg-red-200 p-4 rounded-lg text-center transition-colors {{ request('status') == 'cancelled' ? 'ring-2 ring-red-500' : '' }}">
            <div class="text-2xl font-bold text-red-700">{{ $statusStats['cancelled'] }}</div>
            <div class="text-sm text-red-600">Annulées</div>
        </a>
        
        <a href="{{ route('reservations.status.index', ['status' => 'expired']) }}" 
           class="bg-gray-100 hover:bg-gray-200 p-4 rounded-lg text-center transition-colors {{ request('status') == 'expired' ? 'ring-2 ring-gray-500' : '' }}">
            <div class="text-2xl font-bold text-gray-700">{{ $statusStats['expired'] }}</div>
            <div class="text-sm text-gray-600">Expirées</div>
        </a>
    </div>

    <!-- Filtres et actions -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <form method="GET" class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Groupe sanguin</label>
                <select name="blood_type" class="border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500">
                    <option value="">Tous les groupes</option>
                    @foreach(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $type)
                        <option value="{{ $type }}" {{ request('blood_type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                    @endforeach
                </select>
            </div>
            
            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md transition-colors">
                <i class="fas fa-filter mr-2"></i>Filtrer
            </button>
            
            <a href="{{ route('reservations.status.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md transition-colors">
                <i class="fas fa-times mr-2"></i>Effacer
            </a>
        </form>
    </div>

    <!-- Liste des réservations -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">
                <i class="fas fa-list mr-2"></i>Liste des Réservations
            </h3>
        </div>
        
        @if($reservations->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <input type="checkbox" id="selectAll" class="rounded">
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Réservation
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Client
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Détails
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Statut Actuel
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Date de Création
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($reservations as $reservation)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="checkbox" name="reservation_ids[]" value="{{ $reservation->id }}" class="reservation-checkbox rounded">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">#{{ $reservation->id }}</div>
                                    <div class="text-sm text-gray-500">{{ $reservation->prescription_number }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $reservation->user->name ?? 'Client supprimé' }}</div>
                                    <div class="text-sm text-gray-500">{{ $reservation->phone_number }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <x-blood-type-badge :type="$reservation->blood_type" />
                                        {{ $reservation->quantity }} poche(s)
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ number_format($reservation->total_amount, 0, ',', ' ') }} F CFA
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <x-status-badge :status="$reservation->status" type="order" />
                                    @if($reservation->document_status)
                                        <div class="text-xs mt-1">
                                            Doc: {{ $reservation->document_status_label }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $reservation->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button onclick="openStatusModal({{ $reservation->id }}, '{{ $reservation->status }}', '{{ $reservation->user->name ?? 'Client' }}')" 
                                            class="text-indigo-600 hover:text-indigo-900 mr-3">
                                        <i class="fas fa-edit"></i> Modifier
                                    </button>
                                    <a href="{{ route('orders.show', $reservation) }}" 
                                       class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-eye"></i> Voir
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $reservations->appends(request()->query())->links() }}
            </div>
            
            <!-- Actions en lot -->
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                <div class="flex items-center justify-between">
                    <div id="bulkActions" class="hidden">
                        <span class="text-sm text-gray-600 mr-4">
                            <span id="selectedCount">0</span> réservation(s) sélectionnée(s)
                        </span>
                        <button onclick="bulkMarkExpired()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm">
                            <i class="fas fa-clock mr-2"></i>Marquer comme expirées
                        </button>
                    </div>
                </div>
            </div>
        @else
            <div class="p-6 text-center">
                <i class="fas fa-inbox fa-3x text-gray-300 mb-4"></i>
                <p class="text-gray-500">Aucune réservation trouvée</p>
            </div>
        @endif
    </div>
</div>

<!-- Modal de modification de statut -->
<div id="statusModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Modifier le Statut</h3>
        <form id="statusForm" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Client</label>
                <p id="clientName" class="text-sm text-gray-600 bg-gray-100 p-2 rounded"></p>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nouveau Statut</label>
                <select name="status" id="status" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500" required>
                    <option value="pending">En attente</option>
                    <option value="confirmed">Confirmée</option>
                    <option value="completed">Terminée</option>
                    <option value="cancelled">Annulée</option>
                    <option value="expired">Expirée</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes (optionnel)</label>
                <textarea name="notes" rows="3" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500" 
                          placeholder="Raison du changement de statut..."></textarea>
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeStatusModal()" 
                        class="px-4 py-2 text-gray-600 bg-gray-200 rounded-md hover:bg-gray-300">
                    Annuler
                </button>
                <button type="submit" 
                        class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                    Modifier
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Gestion des modals
function openStatusModal(id, currentStatus, clientName) {
    document.getElementById('statusForm').action = `/reservations/${id}/status`;
    document.getElementById('status').value = currentStatus;
    document.getElementById('clientName').textContent = clientName;
    document.getElementById('statusModal').classList.remove('hidden');
}

function closeStatusModal() {
    document.getElementById('statusModal').classList.add('hidden');
}

// Gestion des sélections en lot
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.reservation-checkbox');
    checkboxes.forEach(checkbox => checkbox.checked = this.checked);
    updateBulkActions();
});

document.addEventListener('change', function(e) {
    if (e.target.classList.contains('reservation-checkbox')) {
        updateBulkActions();
    }
});

function updateBulkActions() {
    const selected = document.querySelectorAll('.reservation-checkbox:checked');
    const bulkActions = document.getElementById('bulkActions');
    const selectedCount = document.getElementById('selectedCount');
    
    if (selected.length > 0) {
        bulkActions.classList.remove('hidden');
        selectedCount.textContent = selected.length;
    } else {
        bulkActions.classList.add('hidden');
    }
}

function bulkMarkExpired() {
    const selected = document.querySelectorAll('.reservation-checkbox:checked');
    if (selected.length === 0) return;
    
    if (confirm(`Êtes-vous sûr de vouloir marquer ${selected.length} réservation(s) comme expirée(s) ?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/reservations/mark-expired';
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        form.appendChild(csrfToken);
        
        selected.forEach(checkbox => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'reservation_ids[]';
            input.value = checkbox.value;
            form.appendChild(input);
        });
        
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endsection
