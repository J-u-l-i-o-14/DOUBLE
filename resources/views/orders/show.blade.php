@extends('layouts.app')

@section('title', 'Détail de la commande #' . $order->id)

@php
use Illuminate\Support\Facades\Storage;
@endphp

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- En-tête -->
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900">🛒 Commande #{{ $order->id }}</h1>
            <a href="{{ route('orders.index') }}" 
               class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded transition-colors">
                ← Retour aux commandes
            </a>
        </div>

        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <!-- Statut de la commande -->
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Statut de la réservation</h2>
                        <p class="text-sm text-gray-600">Réservée le {{ $order->created_at->format('d/m/Y à H:i') }}</p>
                        @php
                            $delaiRetrait = $order->created_at->addHours(72);
                            $maintenant = now();
                            $expire = $maintenant->gt($delaiRetrait);
                            $heuresRestantes = $expire ? 0 : $maintenant->diffInHours($delaiRetrait);
                        @endphp
                        @if($order->status === 'pending' || $order->status === 'confirmed')
                            <p class="text-sm {{ $expire ? 'text-red-600' : ($heuresRestantes <= 24 ? 'text-orange-600' : 'text-blue-600') }}">
                                @if($expire)
                                    ⚠️ Délai de retrait expiré (72h dépassées)
                                @else
                                    ⏰ Délai de retrait : {{ $heuresRestantes }}h restantes (limite: {{ $delaiRetrait->format('d/m/Y à H:i') }})
                                @endif
                            </p>
                        @endif
                    </div>
                    <div class="text-right">
                        <span class="px-4 py-2 text-sm font-semibold rounded-full
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
                            <span class="px-3 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                                Acompte payé
                            </span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Détails de la commande -->
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Informations générales -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">📋 Informations générales</h3>
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Numéro d'ordonnance</dt>
                                <dd class="text-sm text-gray-900 font-mono">{{ $order->prescription_number }}</dd>
                            </div>
                            @if($order->phone_number)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Téléphone</dt>
                                <dd class="text-sm text-gray-900">{{ $order->phone_number }}</dd>
                            </div>
                            @endif
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Groupe sanguin</dt>
                                <dd class="text-sm text-gray-900 font-bold text-red-600">{{ $order->blood_type }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Quantité</dt>
                                <dd class="text-sm text-gray-900">{{ $order->quantity }} poche(s)</dd>
                            </div>
                            @if($order->original_price)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Prix total</dt>
                                <dd class="text-sm text-gray-900">{{ $order->formatted_original_price }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Acompte payé (50%)</dt>
                                <dd class="text-sm text-blue-600 font-medium">{{ $order->formatted_discount }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Solde restant</dt>
                                <dd class="text-sm text-orange-600 font-medium">{{ number_format($order->original_price - $order->total_amount, 0, ',', ' ') }} F CFA</dd>
                            </div>
                            @endif
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Montant payé</dt>
                                <dd class="text-lg font-bold text-red-600">{{ $order->formatted_total }}</dd>
                            </div>
                            @if($order->payment_method)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Moyen de paiement</dt>
                                <dd class="text-sm text-gray-900">{{ $order->payment_method_label }}</dd>
                            </div>
                            @endif
                        </dl>
                    </div>

                    <!-- Informations du centre -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">🏥 Centre de collecte</h3>
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Nom du centre</dt>
                                <dd class="text-sm text-gray-900 font-semibold">{{ $order->center->name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Région</dt>
                                <dd class="text-sm text-gray-900">{{ $order->center->region->name ?? 'Non spécifiée' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Adresse</dt>
                                <dd class="text-sm text-gray-900">{{ $order->center->address }}</dd>
                            </div>
                            @if($order->delivery_date)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Date de collecte prévue</dt>
                                <dd class="text-sm text-gray-900 font-semibold">{{ $order->delivery_date->format('d/m/Y à H:i') }}</dd>
                            </div>
                            @endif
                        </dl>
                    </div>
                </div>

                <!-- Notes -->
                @if($order->notes)
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">📝 Notes</h3>
                    <p class="text-sm text-gray-700 bg-gray-50 p-4 rounded-lg">{{ $order->notes }}</p>
                </div>
                @endif

                <!-- Image d'ordonnance -->
                @if($order->prescription_image)
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">📸 Ordonnance médicale</h3>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <img src="{{ Storage::url($order->prescription_image) }}" 
                             alt="Ordonnance médicale" 
                             class="max-w-full h-auto max-h-96 object-contain mx-auto rounded-lg border border-gray-200 shadow-sm cursor-pointer"
                             onclick="openImageModal(this.src)">
                        <p class="text-xs text-gray-500 text-center mt-2">Cliquez sur l'image pour l'agrandir</p>
                    </div>
                </div>
                @endif

                <!-- Chronologie du statut -->
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">📅 Chronologie</h3>
                    <div class="space-y-3">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 w-2 h-2 bg-green-500 rounded-full"></div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-900">Commande passée</p>
                                <p class="text-sm text-gray-500">{{ $order->created_at->format('d/m/Y à H:i') }}</p>
                            </div>
                        </div>
                        
                        @if($order->status !== 'pending')
                        <div class="flex items-center">
                            <div class="flex-shrink-0 w-2 h-2 bg-blue-500 rounded-full"></div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-900">Commande confirmée</p>
                                <p class="text-sm text-gray-500">Confirmée par le centre</p>
                            </div>
                        </div>
                        @endif

                        @if(in_array($order->status, ['ready', 'completed']))
                        <div class="flex items-center">
                            <div class="flex-shrink-0 w-2 h-2 bg-green-500 rounded-full"></div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-900">Commande prête</p>
                                <p class="text-sm text-gray-500">Prête pour la collecte</p>
                            </div>
                        </div>
                        @endif

                        @if($order->status === 'completed')
                        <div class="flex items-center">
                            <div class="flex-shrink-0 w-2 h-2 bg-gray-500 rounded-full"></div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-900">Commande terminée</p>
                                <p class="text-sm text-gray-500">Collecte effectuée</p>
                            </div>
                        </div>
                        @endif

                        @if($order->status === 'cancelled')
                        <div class="flex items-center">
                            <div class="flex-shrink-0 w-2 h-2 bg-red-500 rounded-full"></div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-900">Commande annulée</p>
                                <p class="text-sm text-gray-500">Annulée</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Actions -->
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <div class="flex justify-between items-center">
                        <div>
                            @if($order->status === 'pending')
                            <span class="text-sm text-gray-600">ℹ️ Votre commande est en attente de confirmation par le centre.</span>
                            @elseif($order->status === 'confirmed')
                            <span class="text-sm text-gray-600">✅ Votre commande a été confirmée. Le centre vous contactera.</span>
                            @elseif($order->status === 'ready')
                            <span class="text-sm text-gray-600">🎉 Votre commande est prête ! Vous pouvez aller la collecter.</span>
                            @elseif($order->status === 'completed')
                            <span class="text-sm text-gray-600">✅ Commande terminée. Merci pour votre confiance !</span>
                            @elseif($order->status === 'cancelled')
                            <span class="text-sm text-red-600">❌ Cette commande a été annulée.</span>
                            @endif
                        </div>
                        
                        @if($order->status === 'pending')
                        <div class="flex space-x-3">
                            <button class="text-red-600 hover:text-red-800 text-sm font-medium">
                                Annuler la commande
                            </button>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour afficher l'image d'ordonnance en grand -->
<div id="image-modal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 hidden" onclick="closeImageModal()">
    <div class="max-w-4xl max-h-full p-4">
        <img id="modal-image" src="" alt="Ordonnance médicale" class="max-w-full max-h-full object-contain">
        <div class="text-center mt-4">
            <button onclick="closeImageModal()" class="bg-white text-gray-900 px-4 py-2 rounded-lg hover:bg-gray-100">
                Fermer
            </button>
        </div>
    </div>
</div>

<script>
function openImageModal(imageSrc) {
    document.getElementById('modal-image').src = imageSrc;
    document.getElementById('image-modal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeImageModal() {
    document.getElementById('image-modal').classList.add('hidden');
    document.body.style.overflow = '';
}

// Fermer le modal avec la touche Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeImageModal();
    }
});
</script>
@endsection
