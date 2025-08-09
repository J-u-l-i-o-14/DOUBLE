@extends('layouts.main')

@section('page-title', 'Détails de la Réservation')

@section('content')
<div class="space-y-6">
    <!-- En-tête avec informations principales -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-start mb-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Réservation #{{ $reservation->id }}</h1>
                <p class="text-gray-600">Créée le {{ $reservation->created_at->format('d/m/Y à H:i') }}</p>
            </div>
            <div class="text-right">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                    @if($reservation->status === 'pending') bg-yellow-100 text-yellow-800
                    @elseif($reservation->status === 'confirmed') bg-blue-100 text-blue-800
                    @elseif($reservation->status === 'completed') bg-green-100 text-green-800
                    @elseif($reservation->status === 'cancelled') bg-red-100 text-red-800
                    @elseif($reservation->status === 'expired') bg-gray-100 text-gray-800
                    @endif">
                    {{ $reservation->status_label }}
                </span>
            </div>
        </div>

        <!-- Actions pour admin/manager -->
        @if(auth()->user()->is_admin || auth()->user()->is_manager)
            <div class="flex space-x-3 mb-4">
                @if($reservation->canBeUpdated())
                    <button onclick="openStatusModal({{ $reservation->id }}, '{{ $reservation->status }}')" 
                            class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                        <i class="fas fa-edit mr-2"></i>Modifier le statut
                    </button>
                @endif
                
                @if($reservation->status === 'pending')
                    <button onclick="confirmReservation({{ $reservation->id }})" 
                            class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
                        <i class="fas fa-check mr-2"></i>Confirmer
                    </button>
                @endif
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Informations client -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-user mr-2 text-blue-600"></i>Informations Client
            </h2>
            <div class="space-y-3">
                <div>
                    <label class="text-sm font-medium text-gray-500">Nom</label>
                    <p class="text-gray-900">{{ $reservation->user->name }}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500">Email</label>
                    <p class="text-gray-900">{{ $reservation->user->email }}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500">Téléphone</label>
                    <p class="text-gray-900">{{ $reservation->user->phone ?? 'Non renseigné' }}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500">Rôle</label>
                    <p class="text-gray-900">{{ ucfirst($reservation->user->role) }}</p>
                </div>
            </div>
        </div>

        <!-- Informations centre -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-hospital mr-2 text-red-600"></i>Centre de Collecte
            </h2>
            <div class="space-y-3">
                <div>
                    <label class="text-sm font-medium text-gray-500">Nom du centre</label>
                    <p class="text-gray-900">{{ $reservation->center->name }}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500">Adresse</label>
                    <p class="text-gray-900">{{ $reservation->center->address ?? 'Non renseignée' }}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500">Téléphone</label>
                    <p class="text-gray-900">{{ $reservation->center->phone ?? 'Non renseigné' }}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500">Email</label>
                    <p class="text-gray-900">{{ $reservation->center->email ?? 'Non renseigné' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Articles commandés -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">
            <i class="fas fa-list mr-2 text-purple-600"></i>Articles Commandés
        </h2>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Groupe Sanguin</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantité</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prix Unitaire</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sous-total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($reservation->items as $item)
                    <tr>
                        <td class="px-4 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                {{ $item->bloodType->group }}
                            </span>
                        </td>
                        <td class="px-4 py-4 text-sm text-gray-900">{{ $item->quantity }}</td>
                        <td class="px-4 py-4 text-sm text-gray-900">{{ number_format($item->unit_price ?? 5000, 0) }} F CFA</td>
                        <td class="px-4 py-4 text-sm text-gray-900">{{ number_format(($item->unit_price ?? 5000) * $item->quantity, 0) }} F CFA</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td colspan="3" class="px-4 py-4 text-sm font-medium text-gray-900 text-right">Total:</td>
                        <td class="px-4 py-4 text-sm font-bold text-gray-900">{{ number_format($reservation->total_amount, 0) }} F CFA</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Informations de paiement et commande -->
    @if($reservation->order)
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">
            <i class="fas fa-credit-card mr-2 text-green-600"></i>Informations de Paiement et Commande
        </h2>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Détails financiers -->
            <div class="space-y-3">
                <h3 class="font-medium text-gray-900 border-b pb-2">Détails Financiers</h3>
                <div>
                    <label class="text-sm font-medium text-gray-500">Commande associée</label>
                    <p class="text-gray-900">#{{ $reservation->order->id }}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500">Prix total</label>
                    <p class="text-gray-900 font-semibold">{{ number_format($reservation->order->total_amount ?? 0, 0) }} F CFA</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500">Acompte payé (50%)</label>
                    <p class="text-green-600 font-semibold">{{ number_format($reservation->order->deposit_amount ?? ($reservation->order->total_amount * 0.5), 0) }} F CFA</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500">Reste à payer lors du retrait</label>
                    <p class="text-red-600 font-semibold">{{ number_format($reservation->order->remaining_amount ?? ($reservation->order->total_amount * 0.5), 0) }} F CFA</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500">Statut de paiement</label>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        @if($reservation->order->payment_status === 'paid') bg-green-100 text-green-800
                        @elseif($reservation->order->payment_status === 'partial') bg-yellow-100 text-yellow-800
                        @elseif($reservation->order->payment_status === 'pending') bg-blue-100 text-blue-800
                        @else bg-red-100 text-red-800
                        @endif">
                        @if($reservation->order->payment_status === 'partial') Acompte payé
                        @else {{ ucfirst($reservation->order->payment_status) }}
                        @endif
                    </span>
                </div>
            </div>

            <!-- Informations de paiement -->
            <div class="space-y-3">
                <h3 class="font-medium text-gray-900 border-b pb-2">Moyens de Paiement</h3>
                <div>
                    <label class="text-sm font-medium text-gray-500">Méthode de paiement</label>
                    <p class="text-gray-900">
                        @if($reservation->order->payment_method === 'mobile_money') 💳 Mobile Money
                        @elseif($reservation->order->payment_method === 'bank_transfer') 🏦 Virement bancaire
                        @elseif($reservation->order->payment_method === 'cash') 💵 Espèces
                        @elseif($reservation->order->payment_method === 'card') 💳 Carte bancaire
                        @else {{ ucfirst($reservation->order->payment_method ?? 'Non spécifié') }}
                        @endif
                    </p>
                </div>
                @if($reservation->order->payment_reference)
                <div>
                    <label class="text-sm font-medium text-gray-500">Référence de paiement</label>
                    <p class="text-gray-900 font-mono">{{ $reservation->order->payment_reference }}</p>
                </div>
                @endif
                @if($reservation->order->transaction_id)
                <div>
                    <label class="text-sm font-medium text-gray-500">ID Transaction</label>
                    <p class="text-gray-900 font-mono">{{ $reservation->order->transaction_id }}</p>
                </div>
                @endif
                <div>
                    <label class="text-sm font-medium text-gray-500">Date de paiement</label>
                    <p class="text-gray-900">{{ $reservation->order->created_at->format('d/m/Y à H:i') }}</p>
                </div>
                @if($reservation->order->payment_completed_at)
                <div>
                    <label class="text-sm font-medium text-gray-500">Paiement complété le</label>
                    <p class="text-gray-900">{{ $reservation->order->payment_completed_at->format('d/m/Y à H:i') }}</p>
                </div>
                @endif
            </div>

            <!-- Documents et ordonnances -->
            <div class="space-y-3">
                <h3 class="font-medium text-gray-900 border-b pb-2">Documents Joints</h3>
                @if($reservation->order->prescription_number)
                <div>
                    <label class="text-sm font-medium text-gray-500">Numéro d'ordonnance</label>
                    <p class="text-gray-900 font-mono">{{ $reservation->order->prescription_number }}</p>
                </div>
                @endif
                
                @if($reservation->order->doctor_name)
                <div>
                    <label class="text-sm font-medium text-gray-500">Médecin prescripteur</label>
                    <p class="text-gray-900">Dr. {{ $reservation->order->doctor_name }}</p>
                </div>
                @endif

                @php
                    // Logique pour afficher les images de prescription (nouvelle version prioritaire)
                    $prescriptionImages = [];
                    if ($reservation->order->prescription_images) {
                        $decodedImages = is_string($reservation->order->prescription_images) ? json_decode($reservation->order->prescription_images, true) : $reservation->order->prescription_images;
                        if (is_array($decodedImages) && !empty($decodedImages)) {
                            $prescriptionImages = $decodedImages;
                        }
                    }
                    // Fallback vers l'ancien champ si aucune nouvelle image
                    if (empty($prescriptionImages) && $reservation->order->prescription_image) {
                        $prescriptionImages[] = $reservation->order->prescription_image;
                    }
                @endphp

                @if(!empty($prescriptionImages))
                <div>
                    <label class="text-sm font-medium text-gray-500">Photos d'ordonnance ({{ count($prescriptionImages) }})</label>
                    <div class="mt-2 grid grid-cols-2 gap-2">
                        @foreach($prescriptionImages as $image)
                        <div>
                            <img src="{{ asset('storage/' . $image) }}" 
                                 alt="Ordonnance {{ $loop->iteration }}" 
                                 class="w-32 h-32 object-cover rounded-lg border cursor-pointer"
                                 onclick="openImageModal('{{ asset('storage/' . $image) }}')">
                            <p class="text-xs text-gray-500 mt-1">Image {{ $loop->iteration }} - Cliquer pour agrandir</p>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                @if($reservation->order->patient_id_image)
                <div>
                    <label class="text-sm font-medium text-gray-500">Pièce d'identité patient</label>
                    <div class="mt-2">
                        <img src="{{ asset('storage/' . $reservation->order->patient_id_image) }}" 
                             alt="Pièce d'identité" 
                             class="w-32 h-32 object-cover rounded-lg border cursor-pointer"
                             onclick="openImageModal('{{ asset('storage/' . $reservation->order->patient_id_image) }}')">
                        <p class="text-xs text-gray-500 mt-1">Cliquer pour agrandir</p>
                    </div>
                </div>
                @endif

                @if($reservation->order->medical_certificate)
                <div>
                    <label class="text-sm font-medium text-gray-500">Certificat médical</label>
                    <div class="mt-2">
                        <img src="{{ asset('storage/' . $reservation->order->medical_certificate) }}" 
                             alt="Certificat médical" 
                             class="w-32 h-32 object-cover rounded-lg border cursor-pointer"
                             onclick="openImageModal('{{ asset('storage/' . $reservation->order->medical_certificate) }}')">
                        <p class="text-xs text-gray-500 mt-1">Cliquer pour agrandir</p>
                    </div>
                </div>
                @endif

                @if(empty($prescriptionImages) && !$reservation->order->patient_id_image && !$reservation->order->medical_certificate)
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-file-alt text-3xl mb-2"></i>
                    <p>Aucun document joint</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Alert de validation pour les gestionnaires -->
        @if(($reservation->status === 'pending') && (auth()->user()->is_admin || auth()->user()->is_manager))
        <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
            <div class="flex items-start">
                <i class="fas fa-exclamation-triangle text-yellow-600 mt-0.5 mr-3"></i>
                <div>
                    <h4 class="text-sm font-medium text-yellow-800">Validation requise</h4>
                    <p class="text-sm text-yellow-700 mt-1">
                        Vérifiez tous les documents et informations ci-dessus avant de confirmer cette réservation. 
                        La confirmation décrémentera automatiquement le stock disponible.
                    </p>
                    <div class="mt-3">
                        <div class="flex items-center space-x-4 text-xs text-yellow-700">
                            <span>✅ Acompte de 50% payé</span>
                            <span>✅ Documents vérifiés</span>
                            <span>⏳ Confirmation en attente</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
    @endif

    <!-- Notes et historique -->
    @if($reservation->manager_notes || auth()->user()->is_admin || auth()->user()->is_manager)
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">
            <i class="fas fa-sticky-note mr-2 text-yellow-600"></i>Notes et Historique
        </h2>
        
        @if($reservation->manager_notes)
        <div class="mb-4">
            <label class="text-sm font-medium text-gray-500">Notes du gestionnaire</label>
            <div class="mt-1 p-3 bg-gray-50 rounded-md">
                <p class="text-gray-900">{{ $reservation->manager_notes }}</p>
            </div>
        </div>
        @endif

        @if($reservation->updated_by)
        <div class="text-sm text-gray-500">
            Dernière mise à jour par: {{ $reservation->updatedBy->name ?? 'Utilisateur inconnu' }} le {{ $reservation->updated_at->format('d/m/Y à H:i') }}
        </div>
        @endif
    </div>
    @endif

    <!-- Actions de navigation -->
    <div class="flex justify-between">
        <a href="{{ route('reservations.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">
            <i class="fas fa-arrow-left mr-2"></i>Retour à la liste
        </a>
        
        @if(auth()->user()->role === 'client' && $reservation->user_id === auth()->id())
        <div class="space-x-3">
            @if($reservation->status === 'pending')
            <button onclick="cancelReservation({{ $reservation->id }})" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">
                <i class="fas fa-times mr-2"></i>Annuler
            </button>
            @endif
        </div>
        @endif
    </div>
</div>

<!-- Modal de changement de statut -->
<div id="statusModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"></div>
        <div class="inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-lg">
            <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Modifier le statut</h3>
            
            <form id="statusForm">
                <input type="hidden" id="reservationId" value="{{ $reservation->id }}">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nouveau statut</label>
                    <select id="newStatus" class="w-full border-gray-300 rounded-md shadow-sm focus:border-red-500 focus:ring-red-500">
                        <option value="pending" {{ $reservation->status === 'pending' ? 'selected' : '' }}>En attente</option>
                        <option value="confirmed" {{ $reservation->status === 'confirmed' ? 'selected' : '' }}>Confirmée</option>
                        <option value="cancelled" {{ $reservation->status === 'cancelled' ? 'selected' : '' }}>Annulée</option>
                        <option value="completed" {{ $reservation->status === 'completed' ? 'selected' : '' }}>Terminée</option>
                        <option value="expired" {{ $reservation->status === 'expired' ? 'selected' : '' }}>Expirée</option>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Note (optionnel)</label>
                    <textarea id="statusNote" rows="3" class="w-full border-gray-300 rounded-md shadow-sm focus:border-red-500 focus:ring-red-500" placeholder="Raison du changement de statut...">{{ $reservation->manager_notes }}</textarea>
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

<!-- Modal pour agrandir les images -->
<div id="imageModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black bg-opacity-75">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="relative max-w-4xl max-h-full">
            <button onclick="closeImageModal()" class="absolute -top-10 right-0 text-white text-2xl hover:text-gray-300">
                <i class="fas fa-times"></i>
            </button>
            <img id="modalImage" src="" alt="Image agrandie" class="max-w-full max-h-full rounded-lg">
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Modal de statut
    function openStatusModal(reservationId, currentStatus) {
        document.getElementById('reservationId').value = reservationId;
        document.getElementById('newStatus').value = currentStatus;
        document.getElementById('statusModal').classList.remove('hidden');
    }

    function closeStatusModal() {
        document.getElementById('statusModal').classList.add('hidden');
    }

    // Modal d'images
    function openImageModal(imageSrc) {
        document.getElementById('modalImage').src = imageSrc;
        document.getElementById('imageModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeImageModal() {
        document.getElementById('imageModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // Fermer le modal avec Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeImageModal();
            closeStatusModal();
        }
    });

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

    // Modal de statut
    function openStatusModal(reservationId, currentStatus) {
        document.getElementById('reservationId').value = reservationId;
        document.getElementById('newStatus').value = currentStatus;
        document.getElementById('statusModal').classList.remove('hidden');
    }

    function closeStatusModal() {
        document.getElementById('statusModal').classList.add('hidden');
    }

    // Modal d'images
    function openImageModal(imageSrc) {
        document.getElementById('modalImage').src = imageSrc;
        document.getElementById('imageModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeImageModal() {
        document.getElementById('imageModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // Fermer le modal avec Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeImageModal();
            closeStatusModal();
        }
    });

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

    function confirmReservation(reservationId) {
        if (confirm('Confirmer cette réservation ? Cela validera la demande et permettra au client de procéder au paiement.')) {
            fetch(`/reservations/${reservationId}/update-status`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    status: 'confirmed',
                    note: 'Réservation confirmée par l\'administrateur'
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

    function cancelReservation(reservationId) {
        if (confirm('Êtes-vous sûr de vouloir annuler cette réservation ?')) {
            fetch(`/reservations/${reservationId}/update-status`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    status: 'cancelled',
                    note: 'Annulée par le client'
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
