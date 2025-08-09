@extends('layouts.main')

@section('page-title', 'Détail de la commande #' . $order->id)

@push('styles')
<!-- Tailwind CSS pour cette page spécifique -->
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
@endpush

@php
use Illuminate\Support\Facades\Storage;
@endphp

@section('content')
<div class="space-y-6">
    <!-- En-tête avec informations principales -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-start mb-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    <i class="fas fa-file-medical mr-2 text-red-600"></i>
                    Commande #{{ $order->id }}
                </h1>
                <p class="text-gray-600">Créée le {{ $order->created_at->format('d/m/Y à H:i') }}</p>
                @if($order->prescription_number)
                <p class="text-sm text-gray-500 font-mono">Ordonnance: {{ $order->prescription_number }}</p>
                @endif
            </div>
            <div class="text-right">
                <a href="{{ route('orders.index') }}" 
                   class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg transition-colors inline-flex items-center mb-2">
                    <i class="fas fa-arrow-left mr-2"></i>Retour aux commandes
                </a>
                <div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                        @if($order->status === 'pending') bg-yellow-100 text-yellow-800
                        @elseif($order->status === 'confirmed') bg-blue-100 text-blue-800
                        @elseif($order->status === 'ready') bg-green-100 text-green-800
                        @elseif($order->status === 'completed') bg-gray-100 text-gray-800
                        @elseif($order->status === 'cancelled') bg-red-100 text-red-800
                        @else bg-gray-100 text-gray-800
                        @endif">
                        {{ $order->status_label }}
                    </span>
                    @if($order->payment_status === 'partial')
                    <div class="mt-2">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                            <i class="fas fa-coins mr-1"></i>Acompte payé
                        </span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Progression de la commande -->
        <div class="bg-gray-50 rounded-lg p-4">
            <h3 class="text-sm font-medium text-gray-900 mb-3">Progression de la commande</h3>
            @php
                $reservationStatus = $order->reservationRequest ? $order->reservationRequest->status : 'pending';
                $isReservationCancelled = $reservationStatus === 'cancelled';
                $isReservationConfirmed = in_array($reservationStatus, ['confirmed', 'completed']);
                $isReservationCompleted = $reservationStatus === 'completed';
            @endphp
            <div class="flex items-center justify-between">
                <div class="flex items-center text-sm">
                    <div class="flex items-center {{ !$isReservationCancelled ? 'text-green-600' : 'text-gray-400' }}">
                        <i class="fas fa-check-circle mr-2"></i>
                        <span>Commandée</span>
                    </div>
                </div>
                <div class="flex-1 mx-4">
                    <div class="h-2 bg-gray-200 rounded-full">
                        <div class="h-2 rounded-full 
                            @if($reservationStatus === 'pending') bg-yellow-500 w-1/4
                            @elseif($reservationStatus === 'confirmed') bg-blue-500 w-3/4
                            @elseif($reservationStatus === 'completed') bg-green-600 w-full
                            @elseif($reservationStatus === 'cancelled') bg-red-500 w-1/4
                            @else bg-gray-400 w-1/4
                            @endif">
                        </div>
                    </div>
                </div>
                <div class="flex items-center text-sm">
                    <div class="flex items-center {{ $isReservationCompleted ? 'text-green-600' : 'text-gray-400' }}">
                        <i class="fas fa-handshake mr-2"></i>
                        <span>Récupérée</span>
                    </div>
                </div>
            </div>
            <div class="mt-2 text-xs text-gray-500 text-center">
                @if($reservationStatus === 'pending') 
                    <span class="text-yellow-600">⏳ En attente de validation par le centre</span>
                @elseif($reservationStatus === 'confirmed') 
                    <span class="text-blue-600">✅ Confirmée - Prête pour récupération</span>
                @elseif($reservationStatus === 'completed') 
                    <span class="text-green-600">🎉 Commande terminée - Sang récupéré</span>
                @elseif($reservationStatus === 'cancelled') 
                    <span class="text-red-600">❌ Réservation annulée</span>
                @else
                    <span class="text-gray-600">📋 Statut: {{ $reservationStatus }}</span>
                @endif
            </div>
            
            @if($order->reservationRequest)
            <div class="mt-3 text-xs text-gray-600 flex items-center justify-between">
                <span>Réservation #{{ $order->reservationRequest->id }}</span>
                @if($order->reservationRequest->updated_at != $order->reservationRequest->created_at)
                    <span>Dernière mise à jour: {{ $order->reservationRequest->updated_at->format('d/m/Y à H:i') }}</span>
                @endif
            </div>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Informations médicales -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-user-md mr-2 text-blue-600"></i>Informations Médicales
            </h2>
            <div class="space-y-4">
                @if($order->doctor_name)
                <div>
                    <label class="text-sm font-medium text-gray-500">Médecin prescripteur</label>
                    <p class="text-gray-900 flex items-center">
                        <i class="fas fa-stethoscope mr-2 text-blue-500"></i>
                        Dr. {{ $order->doctor_name }}
                    </p>
                </div>
                @endif

                @if($order->prescription_number)
                <div>
                    <label class="text-sm font-medium text-gray-500">Numéro d'ordonnance</label>
                    <p class="text-gray-900 font-mono">{{ $order->prescription_number }}</p>
                </div>
                @endif

                <div>
                    <label class="text-sm font-medium text-gray-500">Groupes sanguins demandés</label>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @if($order->reservationRequest && $order->reservationRequest->items)
                            @foreach($order->reservationRequest->items as $item)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                {{ $item->bloodType->group }} 
                                <span class="ml-1 text-xs">({{ $item->quantity }})</span>
                            </span>
                            @endforeach
                        @elseif($order->blood_type)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                {{ $order->blood_type }} 
                                <span class="ml-1 text-xs">({{ $order->quantity }})</span>
                            </span>
                        @else
                            <span class="text-gray-500">Informations non disponibles</span>
                        @endif
                    </div>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-gray-500">Date de prescription</label>
                    <p class="text-gray-900">{{ $order->created_at->format('d/m/Y') }}</p>
                </div>
            </div>
        </div>

        <!-- Informations du client -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-user mr-2 text-purple-600"></i>Informations du Client
            </h2>
            <div class="space-y-4">
                <div>
                    <label class="text-sm font-medium text-gray-500">Nom complet</label>
                    <p class="text-gray-900 flex items-center">
                        <i class="fas fa-user-circle mr-2 text-purple-500"></i>
                        {{ $order->user->name }}
                    </p>
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-500">Email</label>
                    <p class="text-gray-900 flex items-center">
                        <i class="fas fa-envelope mr-2 text-purple-500"></i>
                        {{ $order->user->email }}
                    </p>
                </div>

                @if($order->phone_number)
                <div>
                    <label class="text-sm font-medium text-gray-500">Téléphone</label>
                    <p class="text-gray-900 flex items-center">
                        <i class="fas fa-phone mr-2 text-purple-500"></i>
                        {{ $order->phone_number }}
                    </p>
                </div>
                @elseif($order->user->phone)
                <div>
                    <label class="text-sm font-medium text-gray-500">Téléphone</label>
                    <p class="text-gray-900 flex items-center">
                        <i class="fas fa-phone mr-2 text-purple-500"></i>
                        {{ $order->user->phone }}
                    </p>
                </div>
                @endif

                <div>
                    <label class="text-sm font-medium text-gray-500">Centre de collecte</label>
                    <p class="text-gray-900 flex items-center">
                        <i class="fas fa-hospital mr-2 text-purple-500"></i>
                        {{ $order->center->name ?? 'Non défini' }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-1 gap-6">
        <!-- Informations financières -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-credit-card mr-2 text-green-600"></i>Informations Financières
            </h2>
            <div class="space-y-4">
                <div class="border-b pb-3">
                    <label class="text-sm font-medium text-gray-500">Prix total</label>
                    <p class="text-xl font-bold text-gray-900">{{ number_format($order->original_price ?? $order->total_amount ?? 0, 0) }} F CFA</p>
                </div>

                @php
                    $prixTotal = $order->original_price ?? $order->total_amount ?? 0;
                    // Si payment_status est 'partial', l'acompte payé est 50% du prix total
                    if ($order->payment_status === 'partial') {
                        $acomptePaye = $prixTotal * 0.5;
                        $resteAPayer = $prixTotal * 0.5;
                    } elseif ($order->payment_status === 'paid') {
                        $acomptePaye = $prixTotal;
                        $resteAPayer = 0;
                    } else {
                        $acomptePaye = 0;
                        $resteAPayer = $prixTotal;
                    }
                @endphp

                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-green-50 p-3 rounded-lg">
                        <label class="text-sm font-medium text-green-700">Acompte payé</label>
                        <p class="text-lg font-semibold text-green-800">{{ number_format($acomptePaye, 0) }} F CFA</p>
                        <p class="text-xs text-green-600">✓ {{ $order->payment_status === 'partial' ? 'Payé lors de la commande' : ($order->payment_status === 'paid' ? 'Intégralement payé' : 'En attente') }}</p>
                    </div>
                    
                    <div class="bg-orange-50 p-3 rounded-lg">
                        <label class="text-sm font-medium text-orange-700">Reste à payer</label>
                        <p class="text-lg font-semibold text-orange-800">{{ number_format($resteAPayer, 0) }} F CFA</p>
                        <p class="text-xs text-orange-600">💰 {{ $resteAPayer > 0 ? 'Lors du retrait' : 'Soldé' }}</p>
                    </div>
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-500">Méthode de paiement</label>
                    <p class="text-gray-900">
                        @if($order->payment_method === 'mobile_money') 📱 Mobile Money
                        @elseif($order->payment_method === 'bank_transfer') 🏦 Virement bancaire
                        @elseif($order->payment_method === 'cash') 💵 Espèces
                        @elseif($order->payment_method === 'card') 💳 Carte bancaire
                        @else {{ ucfirst($order->payment_method ?? 'Non spécifié') }}
                        @endif
                    </p>
                </div>

                @if($order->payment_reference)
                <div>
                    <label class="text-sm font-medium text-gray-500">Référence de paiement</label>
                    <p class="text-gray-900 font-mono text-sm">{{ $order->payment_reference }}</p>
                </div>
                @endif

                @if($order->transaction_id)
                <div>
                    <label class="text-sm font-medium text-gray-500">ID Transaction</label>
                    <p class="text-gray-900 font-mono text-sm">{{ $order->transaction_id }}</p>
                </div>
                @endif

                <div>
                    <label class="text-sm font-medium text-gray-500">Statut de paiement</label>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                        @if($order->payment_status === 'paid') bg-green-100 text-green-800
                        @elseif($order->payment_status === 'partial') bg-yellow-100 text-yellow-800
                        @elseif($order->payment_status === 'pending') bg-blue-100 text-blue-800
                        @else bg-red-100 text-red-800
                        @endif">
                        @if($order->payment_status === 'partial') Acompte payé (50%)
                        @elseif($order->payment_status === 'paid') Entièrement payé
                        @else {{ ucfirst($order->payment_status) }}
                        @endif
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Documents joints -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">
            <i class="fas fa-file-medical-alt mr-2 text-purple-600"></i>Documents Joints
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Ordonnance -->
            @php
                $prescriptionImages = [];
                
                // Prioriser prescription_images (nouveau champ) par rapport à prescription_image (ancien)
                if ($order->prescription_images) {
                    $decodedImages = is_string($order->prescription_images) ? json_decode($order->prescription_images, true) : $order->prescription_images;
                    if (is_array($decodedImages) && !empty($decodedImages)) {
                        $prescriptionImages = $decodedImages;
                    }
                }
                
                // Utiliser prescription_image seulement si prescription_images est vide
                if (empty($prescriptionImages) && $order->prescription_image) {
                    $prescriptionImages[] = $order->prescription_image;
                }
            @endphp
            
            @if(!empty($prescriptionImages))
            <div class="bg-gray-50 rounded-lg p-4">
                <h3 class="font-medium text-gray-900 mb-3 flex items-center">
                    <i class="fas fa-prescription-bottle-alt mr-2 text-green-600"></i>
                    Ordonnance médicale
                </h3>
                <div class="space-y-2">
                    @foreach($prescriptionImages as $image)
                    <div class="mb-2">
                        <img src="{{ asset('storage/prescriptions/' . $image) }}" 
                             alt="Ordonnance" 
                             class="w-full h-32 object-cover rounded-lg border cursor-pointer hover:opacity-75 transition-opacity"
                             onclick="openImageModal('{{ asset('storage/prescriptions/' . $image) }}', 'Ordonnance médicale')">
                        <p class="text-xs text-gray-500 text-center">{{ $image }}</p>
                    </div>
                    @endforeach
                    <p class="text-xs text-gray-500 text-center">Cliquer pour agrandir</p>
                </div>
            </div>
            @endif

            <!-- Pièce d'identité -->
            @if($order->patient_id_image)
            <div class="bg-gray-50 rounded-lg p-4">
                <h3 class="font-medium text-gray-900 mb-3 flex items-center">
                    <i class="fas fa-id-card mr-2 text-blue-600"></i>
                    Pièce d'identité
                </h3>
                <div class="space-y-2">
                    <img src="{{ asset('storage/' . $order->patient_id_image) }}" 
                         alt="Pièce d'identité" 
                         class="w-full h-32 object-cover rounded-lg border cursor-pointer hover:opacity-75 transition-opacity"
                         onclick="openImageModal('{{ asset('storage/' . $order->patient_id_image) }}', 'Pièce d\'identité')">
                    <p class="text-xs text-gray-500 text-center">Cliquer pour agrandir</p>
                </div>
            </div>
            @endif

            <!-- Certificat médical -->
            @if($order->medical_certificate)
            <div class="bg-gray-50 rounded-lg p-4">
                <h3 class="font-medium text-gray-900 mb-3 flex items-center">
                    <i class="fas fa-certificate mr-2 text-red-600"></i>
                    Certificat médical
                </h3>
                <div class="space-y-2">
                    <img src="{{ asset('storage/' . $order->medical_certificate) }}" 
                         alt="Certificat médical" 
                         class="w-full h-32 object-cover rounded-lg border cursor-pointer hover:opacity-75 transition-opacity"
                         onclick="openImageModal('{{ asset('storage/' . $order->medical_certificate) }}', 'Certificat médical')">
                    <p class="text-xs text-gray-500 text-center">Cliquer pour agrandir</p>
                </div>
            </div>
            @endif

            @if(empty($prescriptionImages) && !$order->patient_id_image && !$order->medical_certificate)
            <div class="col-span-full text-center py-8 text-gray-500">
                <i class="fas fa-file-alt text-4xl mb-4"></i>
                <p class="text-lg">Aucun document joint à cette commande</p>
                <p class="text-sm">Les documents seront requis pour le retrait</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Historique et timeline -->
    @if($order->reservationRequest)
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">
            <i class="fas fa-history mr-2 text-gray-600"></i>Historique de la commande
        </h2>
        
        <div class="flow-root">
            <ul class="-mb-8">
                <li>
                    <div class="relative pb-8">
                        <div class="relative flex space-x-3">
                            <div>
                                <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                                    <i class="fas fa-plus text-white text-xs"></i>
                                </span>
                            </div>
                            <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                <div>
                                    <p class="text-sm text-gray-500">Commande créée</p>
                                    <p class="text-xs text-gray-400">ID: #{{ $order->id }}</p>
                                </div>
                                <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                    {{ $order->created_at->format('d/m/Y H:i') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </li>

                @if($order->payment_status === 'partial' || $order->payment_status === 'paid')
                <li>
                    <div class="relative pb-8">
                        <div class="relative flex space-x-3">
                            <div>
                                <span class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center ring-8 ring-white">
                                    <i class="fas fa-credit-card text-white text-xs"></i>
                                </span>
                            </div>
                            <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                <div>
                                    <p class="text-sm text-gray-500">Acompte payé (50%)</p>
                                    <p class="text-xs text-gray-400">{{ number_format($order->original_price * 0.5, 0) }} F CFA</p>
                                </div>
                                <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                    {{ $order->created_at->format('d/m/Y H:i') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
                @endif

                @if($order->reservationRequest->status === 'confirmed')
                <li>
                    <div class="relative pb-8">
                        <div class="relative flex space-x-3">
                            <div>
                                <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                                    <i class="fas fa-check text-white text-xs"></i>
                                </span>
                            </div>
                            <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                <div>
                                    <p class="text-sm text-gray-500">Réservation confirmée</p>
                                    <p class="text-xs text-gray-400">Stock réservé</p>
                                </div>
                                <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                    {{ $order->reservationRequest->updated_at->format('d/m/Y H:i') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
                @endif

                <li>
                    <div class="relative">
                        <div class="relative flex space-x-3">
                            <div>
                                <span class="h-8 w-8 rounded-full 
                                    @if($order->status === 'completed') bg-green-500
                                    @elseif($order->status === 'ready') bg-yellow-500
                                    @elseif($order->status === 'cancelled') bg-red-500
                                    @else bg-gray-300
                                    @endif
                                    flex items-center justify-center ring-8 ring-white">
                                    @if($order->status === 'completed')
                                        <i class="fas fa-handshake text-white text-xs"></i>
                                    @elseif($order->status === 'ready')
                                        <i class="fas fa-box text-white text-xs"></i>
                                    @elseif($order->status === 'cancelled')
                                        <i class="fas fa-times text-white text-xs"></i>
                                    @else
                                        <i class="fas fa-clock text-white text-xs"></i>
                                    @endif
                                </span>
                            </div>
                            <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                <div>
                                    <p class="text-sm text-gray-500">
                                        @if($order->status === 'completed') Commande récupérée
                                        @elseif($order->status === 'ready') Prête pour récupération
                                        @elseif($order->status === 'cancelled') Commande annulée
                                        @else En attente
                                        @endif
                                    </p>
                                </div>
                                <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                    @if($order->status !== 'pending')
                                        {{ $order->updated_at->format('d/m/Y H:i') }}
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div>
    @endif

    <!-- Actions selon le statut -->
    @if($order->status === 'ready' && $order->payment_status === 'partial')
    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
        <div class="flex items-start">
            <i class="fas fa-info-circle text-green-600 mt-0.5 mr-3"></i>
            <div>
                <h4 class="text-sm font-medium text-green-800">Votre commande est prête !</h4>
                <p class="text-sm text-green-700 mt-1">
                    Vous pouvez maintenant vous rendre au centre pour récupérer votre commande. 
                    N'oubliez pas d'apporter :
                </p>
                <ul class="mt-2 text-sm text-green-700 list-disc list-inside">
                    <li>{{ number_format($resteAPayer, 0) }} F CFA pour le solde</li>
                    <li>Votre pièce d'identité</li>
                    <li>L'ordonnance originale</li>
                </ul>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Modal pour agrandir les images -->
<div id="imageModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black bg-opacity-75">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="relative max-w-4xl max-h-full">
            <button onclick="closeImageModal()" class="absolute -top-10 right-0 text-white text-2xl hover:text-gray-300">
                <i class="fas fa-times"></i>
            </button>
            <div class="bg-white rounded-lg p-4">
                <h3 id="modalTitle" class="text-lg font-semibold mb-4"></h3>
                <img id="modalImage" src="" alt="Image agrandie" class="max-w-full max-h-full rounded-lg">
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function openImageModal(imageSrc, title) {
        document.getElementById('modalImage').src = imageSrc;
        document.getElementById('modalTitle').textContent = title;
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
        }
    });
</script>
@endpush

@endsection
