@extends('layouts.main')

@section('page-title', 'Configuration des Seuils d\'Alerte')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Configuration des seuils -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
        <h3 class="text-lg font-medium text-gray-900 mb-4">
            <i class="fas fa-cog mr-2"></i>Nouveau Seuil d'Alerte
        </h3>
        
        <form action="{{ route('stock-thresholds.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Groupe sanguin</label>
                <select name="blood_type_id" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500" required>
                    <option value="">Choisir un groupe</option>
                    @foreach($bloodTypes as $bloodType)
                        <option value="{{ $bloodType->id }}">{{ $bloodType->group }}</option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Seuil d'avertissement</label>
                <input type="number" name="warning_threshold" min="1" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-500" placeholder="Ex: 10" required>
                <small class="text-gray-500">Nombre de poches pour déclencher un avertissement</small>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Seuil critique</label>
                <input type="number" name="critical_threshold" min="1" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500" placeholder="Ex: 3" required>
                <small class="text-gray-500">Nombre de poches pour déclencher une alerte critique</small>
            </div>
            
            <div class="flex items-end">
                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-md transition-colors duration-200">
                    <i class="fas fa-plus mr-2"></i>Ajouter
                </button>
            </div>
        </form>
    </div>

    <!-- Liste des seuils configurés -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">
            <i class="fas fa-list mr-2"></i>Seuils Configurés
        </h3>
        
        @if($thresholds->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Groupe Sanguin
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Seuil d'Avertissement
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Seuil Critique
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Stock Actuel
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Statut
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($thresholds as $threshold)
                            @php
                                $currentStock = \App\Models\BloodBag::available()
                                    ->where('center_id', auth()->user()->center_id)
                                    ->where('blood_type_id', $threshold->blood_type_id)
                                    ->count();
                                    
                                $status = 'normal';
                                $statusColor = 'text-green-600';
                                $statusIcon = 'fas fa-check-circle';
                                
                                if ($currentStock <= $threshold->critical_threshold) {
                                    $status = 'critique';
                                    $statusColor = 'text-red-600';
                                    $statusIcon = 'fas fa-exclamation-triangle';
                                } elseif ($currentStock <= $threshold->warning_threshold) {
                                    $status = 'avertissement';
                                    $statusColor = 'text-yellow-600';
                                    $statusIcon = 'fas fa-exclamation-circle';
                                }
                            @endphp
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <x-blood-type-badge :type="$threshold->bloodType->group" />
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full text-xs font-medium">
                                        {{ $threshold->warning_threshold }} poches
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <span class="bg-red-100 text-red-800 px-2 py-1 rounded-full text-xs font-medium">
                                        {{ $threshold->critical_threshold }} poches
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $currentStock }} poches
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="flex items-center {{ $statusColor }}">
                                        <i class="{{ $statusIcon }} mr-2"></i>
                                        {{ ucfirst($status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button onclick="openEditModal({{ $threshold->id }}, {{ $threshold->warning_threshold }}, {{ $threshold->critical_threshold }})" 
                                            class="text-indigo-600 hover:text-indigo-900 mr-3">
                                        <i class="fas fa-edit"></i> Modifier
                                    </button>
                                    <form action="{{ route('stock-thresholds.destroy', $threshold) }}" method="POST" class="inline" 
                                          onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce seuil ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">
                                            <i class="fas fa-trash"></i> Supprimer
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-8">
                <i class="fas fa-cog fa-3x text-gray-300 mb-4"></i>
                <p class="text-gray-500">Aucun seuil d'alerte configuré</p>
                <p class="text-sm text-gray-400">Configurez des seuils pour recevoir des alertes automatiques</p>
            </div>
        @endif
    </div>
</div>

<!-- Modal de modification -->
<div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Modifier le Seuil</h3>
        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Seuil d'avertissement</label>
                <input type="number" id="edit_warning_threshold" name="warning_threshold" min="1" 
                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-500" required>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Seuil critique</label>
                <input type="number" id="edit_critical_threshold" name="critical_threshold" min="1" 
                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500" required>
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeEditModal()" 
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
function openEditModal(id, warningThreshold, criticalThreshold) {
    document.getElementById('editForm').action = `/stock-thresholds/${id}`;
    document.getElementById('edit_warning_threshold').value = warningThreshold;
    document.getElementById('edit_critical_threshold').value = criticalThreshold;
    document.getElementById('editModal').classList.remove('hidden');
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}
</script>
@endsection
