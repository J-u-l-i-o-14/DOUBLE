@extends('layouts.public')

@section('title', 'Réservation de poches de sang')

@section('content')
    <!-- En-tête de la page -->
    <div class="bg-gradient-to-r from-red-600 to-red-800 text-white py-6 mb-8">
        <div class="max-w-7xl mx-auto px-4">
            <h1 class="text-3xl font-bold mb-2">Réservation de poches de sang</h1>
            <p class="text-lg opacity-90">Recherchez et réservez les poches de sang disponibles dans nos centres</p>
        </div>
    </div>

    <!-- Formulaire de recherche multicritère sang avancé -->
    <section class="max-w-3xl mx-auto mt-8 mb-8 p-6 bg-white rounded-lg shadow">
        <h2 class="text-xl font-bold text-center text-red-700 mb-4">Rechercher du sang disponible</h2>
        <form method="POST" action="{{ route('blood.reservation.search') }}" id="blood-search-form">
            @csrf
            <div class="mb-4">
                <label for="region_id" class="block text-gray-700 font-medium mb-1">Région</label>
                <select name="region_id" id="region_id" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500">
                    <option value="">Toutes les régions</option>
                    @foreach($regions ?? [] as $region)
                        <option value="{{ $region->id }}" {{ request('region_id') == $region->id ? 'selected' : '' }}>{{ $region->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-4">
                <label for="center_id" class="block text-gray-700 font-medium mb-1">Centre</label>
                <select name="center_id" id="center_id" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500">
                    <option value="">Tous les centres</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 font-medium mb-1">Groupes sanguins recherchés et quantités</label>
                <table class="w-full border rounded mb-2" id="blood-types-table">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="p-2">Groupe sanguin</th>
                            <th class="p-2">Quantité</th>
                            <th class="p-2"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $selected = old('blood_types', request('blood_types', [['blood_type_id'=>'','quantity'=>'']])); @endphp
                        @foreach($selected as $i => $row)
                        <tr>
                            <td class="input-cell">
                                <select name="blood_types[{{ $i }}][blood_type_id]" class="border rounded px-2 py-1">
                                    <option value="">Choisir</option>
                                    @foreach($bloodTypes ?? [] as $type)
                                        <option value="{{ $type->id }}" {{ (isset($row['blood_type_id']) && $row['blood_type_id'] == $type->id) ? 'selected' : '' }}>{{ $type->group }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="input-cell">
                                <input type="number" min="1" name="blood_types[{{ $i }}][quantity]" class="border rounded px-2 py-1 w-24" value="{{ $row['quantity'] ?? '' }}">
                            </td>
                            <td>
                                <button type="button" class="remove-row text-red-600 font-bold">&times;</button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <button type="button" id="add-row" class="btn btn-secondary">Ajouter un groupe</button>
            </div>
            <div class="flex justify-center">
                <button type="submit" class="btn btn-red">Rechercher</button>
            </div>
        </form>
    </section>

    <!-- Résultats de la recherche (AJAX) -->
    <div id="search-results"></div>

    <!-- Loader résultats -->
    <div id="results-loader" class="w-full flex justify-center items-center py-8 hidden">
      <div class="animate-spin rounded-full h-12 w-12 border-t-4 border-b-4 border-red-600 opacity-70"></div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Stockage global des quantités demandées et ajoutées
            const bloodTypeQuantities = {
                requested: {},  // Quantités demandées par type de sang
                added: {}      // Quantités déjà ajoutées au panier
            };

            // Fonction pour mettre à jour les quantités demandées
            function updateRequestedQuantities() {
                const rows = document.querySelectorAll('#blood-types-table tr');
                bloodTypeQuantities.requested = {};

                rows.forEach(row => {
                    const bloodTypeSelect = row.querySelector('select[name^="blood_types"]');
                    const quantityInput = row.querySelector('input[type="number"]');
                    if (bloodTypeSelect && quantityInput && bloodTypeSelect.value) {
                        const bloodType = bloodTypeSelect.options[bloodTypeSelect.selectedIndex].text;
                        bloodTypeQuantities.requested[bloodType] = (bloodTypeQuantities.requested[bloodType] || 0) + parseInt(quantityInput.value);
                    }
                });
            }

            // Gérer l'ajout/retrait du panier (optimisé)
            document.addEventListener('click', function(e) {
                if (e.target && e.target.classList.contains('add-to-cart-btn')) {
                    const btn = e.target;
                    const bloodType = btn.dataset.bloodType;
                    const centerId = btn.dataset.centerId;
                    const quantity = parseInt(btn.dataset.quantity);
                    const isInCart = btn.classList.contains('in-cart');

                    // Désactiver le bouton pendant le traitement
                    btn.disabled = true;
                    const originalText = btn.textContent;
                    btn.textContent = 'Traitement...';

                    // Si on retire du panier
                    if (isInCart) {
                        // Mise à jour immédiate de l'UI (optimisation UX)
                        bloodTypeQuantities.added[bloodType] = (bloodTypeQuantities.added[bloodType] || 0) - quantity;
                        btn.classList.remove('in-cart', 'bg-red-600', 'hover:bg-red-700');
                        btn.classList.add('bg-blue-600', 'hover:bg-blue-700');
                        btn.textContent = `Ajouter (${quantity})`;
                        btn.disabled = false;
                        showToast('Article retiré du panier', false);
                    }
                    // Si on ajoute au panier
                    else {
                        const currentTotal = (bloodTypeQuantities.added[bloodType] || 0) + quantity;
                        const requestedQuantity = bloodTypeQuantities.requested[bloodType] || 0;

                        if (currentTotal > requestedQuantity) {
                            btn.disabled = false;
                            btn.textContent = originalText;
                            showToast(`Erreur: La quantité totale (${currentTotal}) dépasserait la quantité demandée (${requestedQuantity})`, true);
                            return;
                        }

                        // Mise à jour immédiate de l'UI (optimisation UX)
                        bloodTypeQuantities.added[bloodType] = currentTotal;
                        btn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
                        btn.classList.add('in-cart', 'bg-red-600', 'hover:bg-red-700');
                        btn.textContent = 'Retirer';
                        btn.disabled = false;
                        showToast(`${quantity} poche(s) ajoutée(s) au panier`, false);

                        // Envoi de la requête en arrière-plan (non bloquant)
                        fetch('/cart/add', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                center_id: centerId,
                                blood_type: bloodType,
                                quantity: quantity
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (!data.success) {
                                // En cas d'erreur serveur, annuler la mise à jour UI
                                bloodTypeQuantities.added[bloodType] = (bloodTypeQuantities.added[bloodType] || 0) - quantity;
                                btn.classList.remove('in-cart', 'bg-red-600', 'hover:bg-red-700');
                                btn.classList.add('bg-blue-600', 'hover:bg-blue-700');
                                btn.textContent = `Ajouter (${quantity})`;
                                showToast('Erreur: ' + (data.message || 'Erreur lors de l\'ajout au panier'), true);
                            }
                        })
                        .catch(error => {
                            // En cas d'erreur réseau, annuler la mise à jour UI
                            console.error('Erreur réseau:', error);
                            bloodTypeQuantities.added[bloodType] = (bloodTypeQuantities.added[bloodType] || 0) - quantity;
                            btn.classList.remove('in-cart', 'bg-red-600', 'hover:bg-red-700');
                            btn.classList.add('bg-blue-600', 'hover:bg-blue-700');
                            btn.textContent = `Ajouter (${quantity})`;
                            showToast('Erreur de connexion', true);
                        });
                    }
                }
            });

            // Fonction pour afficher les toast messages (optimisée)
            function showToast(message, isError) {
                // Réutiliser l'élément toast existant pour éviter la création répétée
                let toast = document.getElementById('global-toast');
                if (!toast) {
                    toast = document.createElement('div');
                    toast.id = 'global-toast';
                    toast.className = 'fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 transition-opacity duration-300';
                    document.body.appendChild(toast);
                }

                // Annuler le timeout précédent s'il existe
                if (toast.timeoutId) {
                    clearTimeout(toast.timeoutId);
                }

                // Mettre à jour le style et le contenu
                toast.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 transition-opacity duration-300 ${isError ? 'bg-red-100 text-red-700 border border-red-300' : 'bg-green-100 text-green-700 border border-green-300'}`;
                toast.textContent = message;
                toast.style.opacity = '1';

                // Programmer la disparition
                toast.timeoutId = setTimeout(() => {
                    toast.style.opacity = '0';
                    setTimeout(() => {
                        if (toast.parentNode) {
                            toast.parentNode.removeChild(toast);
                        }
                    }, 300);
                }, 2000);
            }

            // Mise à jour initiale des quantités demandées
            updateRequestedQuantities();

            // Mettre à jour les quantités quand le formulaire change
            document.getElementById('blood-search-form').addEventListener('change', updateRequestedQuantities);

            // Gestion de l'ajout de ligne
            document.getElementById('add-row').onclick = function() {
                const rowCount = document.querySelector('#blood-types-table tbody').rows.length;
                const newRow = document.createElement('tr');
                newRow.innerHTML = `
                    <td class="input-cell">
                        <select name="blood_types[${rowCount}][blood_type_id]" class="border rounded px-2 py-1">
                            <option value="">Choisir</option>
                            @foreach($bloodTypes ?? [] as $type)
                                <option value="{{ $type->id }}">{{ $type->group }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td class="input-cell">
                        <input type="number" min="1" name="blood_types[${rowCount}][quantity]" class="border rounded px-2 py-1 w-24">
                    </td>
                    <td>
                        <button type="button" class="remove-row text-red-600 font-bold">&times;</button>
                    </td>
                `;
                document.querySelector('#blood-types-table tbody').appendChild(newRow);
                updateBloodTypeOptions();
            };

            // Gestion de la suppression de ligne
            document.querySelector('#blood-types-table tbody').addEventListener('click', function(e) {
                if(e.target && e.target.classList.contains('remove-row')) {
                    e.target.closest('tr').remove();
                    setTimeout(updateBloodTypeOptions, 10);
                }
            });

            // Fonction pour mettre à jour les options de type de sang
            function updateBloodTypeOptions() {
                const selections = Array.from(document.querySelectorAll('select[name*="blood_type_id"]'))
                    .map(select => select.value)
                    .filter(value => value !== "");

                document.querySelectorAll('select[name*="blood_type_id"]').forEach(select => {
                    Array.from(select.options).forEach(option => {
                        if(option.value === "") return;
                        option.disabled = selections.includes(option.value) && select.value !== option.value;
                    });
                });
            }

            // Gestion de la recherche AJAX
            const form = document.getElementById('blood-search-form');
            const resultsDiv = document.getElementById('search-results');
            const resultsLoader = document.getElementById('results-loader');

            form.onsubmit = function(e) {
                e.preventDefault();
                resultsLoader.classList.remove('hidden');
                resultsDiv.innerHTML = '';
                updateRequestedQuantities(); // Mettre à jour les quantités avant la recherche

                const formData = new FormData(form);
                const formDataObject = {};

                // Convertir FormData en objet avec gestion des tableaux
                formData.forEach((value, key) => {
                    // Gestion des clés avec notation de tableau (ex: blood_types[0][blood_type_id])
                    const keys = key.match(/([^\[\]]+)/g);
                    let current = formDataObject;

                    keys.forEach((k, i) => {
                        if (i === keys.length - 1) {
                            current[k] = value;
                        } else {
                            current[k] = current[k] || {};
                            current = current[k];
                        }
                    });
                });

                fetch('{{ route("blood.reservation.search") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value
                    },
                    body: JSON.stringify(formDataObject)
                })
                .then(r => r.json())
                .then(res => {
                    resultsLoader.classList.add('hidden');
                    if(res.results && res.results.length > 0) {
                        let html = `<section class='max-w-7xl mx-auto mb-8 p-6 bg-white rounded-lg shadow overflow-x-auto'>`;
                        html += `<table class='min-w-full divide-y divide-gray-200'>
                                <thead>
                                    <tr class='bg-gray-50'>
                                        <th class='p-4 text-left text-xs font-medium text-gray-500 uppercase'>Centre</th>
                                        <th class='p-4 text-left text-xs font-medium text-gray-500 uppercase'>Région</th>
                                        <th class='p-4 text-left text-xs font-medium text-gray-500 uppercase'>Groupe sanguin</th>
                                        <th class='p-4 text-center text-xs font-medium text-gray-500 uppercase'>Disponible</th>
                                        <th class='p-4'></th>
                                    </tr>
                                </thead>
                                <tbody class='bg-white divide-y divide-gray-200'>`;

                        res.results.forEach(center => {
                            const bloodType = center.blood_type;
                            const isInCart = (bloodTypeQuantities.added[bloodType] || 0) > 0;

                            html += `<tr class='hover:bg-gray-50'>
                                <td class='p-4'>${center.name}</td>
                                <td class='p-4'>${center.region}</td>
                                <td class='p-4'>${bloodType}</td>
                                <td class='p-4 text-center font-semibold'>${center.can_provide}</td>
                                <td class='p-4 text-center'>
                                    <button type='button'
                                            class='add-to-cart-btn ${isInCart ? 'in-cart bg-red-600 hover:bg-red-700' : 'bg-blue-600 hover:bg-blue-700'} text-white font-bold py-2 px-4 rounded transition-colors duration-200'
                                            data-blood-type='${bloodType}'
                                            data-center-id='${center.id}'
                                            data-quantity='${center.can_provide}'>
                                        ${isInCart ? 'Retirer' : `Ajouter (${center.can_provide})`}
                                    </button>
                                </td>
                            </tr>`;
                        });

                        html += `</tbody></table>`;
                        
                        // Bouton Commander en bas du tableau
                        html += `<div class="mt-6 flex justify-center">
                            <button type="button" 
                                    onclick="openCartModal()"
                                    class="btn-red">
                                Commander
                            </button>
                        </div>`;
                        
                        html += `</section>`;
                        resultsDiv.innerHTML = html;
                    } else {
                        resultsDiv.innerHTML = `<section class='max-w-4xl mx-auto mb-8 p-6'>
                            <div class='w-full bg-red-600 text-white text-lg font-semibold rounded-lg p-6 text-center shadow-lg'>
                                <span class='block'>Aucune poche de sang n'est disponible dans les centres pour les critères demandés.</span>
                            </div>
                        </section>`;
                    }
                })
                .catch(error => {
                    resultsLoader.classList.add('hidden');
                    resultsDiv.innerHTML = `<div class='text-center text-red-600 p-4'>Une erreur est survenue</div>`;
                });
            };

            // Fonction pour ouvrir le modal et charger les articles
            window.openCartModal = function() {
                console.log('Ouverture du modal du panier...');
                document.getElementById('cart-modal').classList.remove('hidden');
                loadCartItems();
            };

            window.closeCartModal = function() {
                document.getElementById('cart-modal').classList.add('hidden');
            };

            window.loadCartItems = function() {
                console.log('Chargement des articles du panier...');
                fetch('/cart', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                    .then(response => {
                        console.log('Réponse reçue:', response.status);
                        if (!response.ok) {
                            throw new Error(`Erreur HTTP: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Données du panier:', data);
                        const cartItemsDiv = document.getElementById('cart-items');
                        const emptyMessage = document.getElementById('empty-cart-message');
                        const cartFooter = document.getElementById('cart-footer');
                        const cartTotal = document.getElementById('cart-total');

                        if (data.success && data.items && data.items.length > 0) {
                            let html = '';
                            data.items.forEach(item => {
                                html += `
                                    <div class="flex items-center justify-between bg-gray-50 p-4 rounded border">
                                        <div class="flex-1">
                                            <h4 class="font-semibold text-gray-900">${item.center_name || 'Centre inconnu'}</h4>
                                            <p class="text-sm text-gray-600"><strong>Groupe sanguin:</strong> ${item.blood_type}</p>
                                            <p class="text-sm text-gray-600"><strong>Quantité:</strong> ${item.quantity} poche(s)</p>
                                        </div>
                                        <button type="button" 
                                                onclick="removeCartItem(${item.id})"
                                                class="ml-4 text-red-600 hover:text-red-800 hover:bg-red-50 p-2 rounded"
                                                title="Supprimer cet article">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                `;
                            });
                            cartItemsDiv.innerHTML = html;
                            cartItemsDiv.classList.remove('hidden');
                            emptyMessage.classList.add('hidden');
                            cartFooter.classList.remove('hidden');
                            cartTotal.textContent = `${data.total_quantity || 0} poche(s)`;
                        } else {
                            cartItemsDiv.innerHTML = '';
                            cartItemsDiv.classList.add('hidden');
                            emptyMessage.classList.remove('hidden');
                            cartFooter.classList.add('hidden');
                        }
                    })
                    .catch(error => {
                        console.error('Erreur lors du chargement du panier:', error);
                        showToast('Erreur lors du chargement du panier', true);
                        // Afficher un message d'erreur dans le modal
                        const cartItemsDiv = document.getElementById('cart-items');
                        cartItemsDiv.innerHTML = `
                            <div class="text-center py-4 text-red-600">
                                <p>Erreur lors du chargement du panier</p>
                                <button onclick="loadCartItems()" class="text-blue-600 underline mt-2">Réessayer</button>
                            </div>
                        `;
                        cartItemsDiv.classList.remove('hidden');
                        document.getElementById('empty-cart-message').classList.add('hidden');
                        document.getElementById('cart-footer').classList.add('hidden');
                    });
            };

            window.removeCartItem = function(itemId) {
                fetch(`/cart/${itemId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadCartItems();
                        showToast('Article supprimé du panier', false);
                    } else {
                        showToast(data.message || 'Erreur lors de la suppression', true);
                    }
                })
                .catch(error => {
                    showToast('Erreur lors de la suppression', true);
                });
            };

            window.clearCart = function() {
                if (confirm('Êtes-vous sûr de vouloir vider le panier ?')) {
                    fetch('/cart', {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            loadCartItems();
                            showToast('Panier vidé', false);
                        } else {
                            showToast(data.message || 'Erreur lors du vidage', true);
                        }
                    })
                    .catch(error => {
                        showToast('Erreur lors du vidage', true);
                    });
                }
            };

            window.processPayment = function() {
                fetch('/cart/payment', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Paiement effectué avec succès!', false);
                        closeCartModal();
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    } else {
                        showToast(data.message || 'Erreur lors du paiement', true);
                    }
                })
                .catch(error => {
                    showToast('Erreur lors du paiement', true);
                });
            };

            // Gestionnaire pour fermer le modal avec la touche Échap
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    const modal = document.getElementById('cart-modal');
                    if (!modal.classList.contains('hidden')) {
                        closeCartModal();
                    }
                }
            });

        });
    </script>
    @endpush

    <!-- Inclusion du modal du panier -->
    @include('partials._cart-modal')

@endsection

@push('styles')
<style>
@keyframes bounce-infinite {
    0%, 100%   { transform: scale(1); }
    20%  { transform: scale(1.15, 0.85); }
    40%  { transform: scale(0.95, 1.05); }
    60%  { transform: scale(1.05, 0.95); }
    80%  { transform: scale(1.02, 0.98); }
}
.animate-bounce-infinite {
    animation: bounce-infinite 1.2s cubic-bezier(.68,-0.55,.27,1.55) infinite;
}
.reserve-btn:hover {
    animation: bounce-infinite 0.7s cubic-bezier(.68,-0.55,.27,1.55) infinite;
}
@media (max-width: 900px) {
    .overflow-x-auto { overflow-x: auto; }
}
#blood-types-table {
  background: #fff7f7;
  border-radius: 0.75rem;
  border: 2px solid #fecaca;
  box-shadow: 0 2px 12px 0 #fca5a533;
  overflow: hidden;
}
#blood-types-table th, #blood-types-table td {
  border-bottom: 1px solid #fecaca;
}
#blood-types-table tr:last-child td {
  border-bottom: none;
}
#blood-types-table input, #blood-types-table select {
  border-radius: 0.5rem;
  border: 1.5px solid #fca5a5;
  background: #fff;
}
#blood-types-table tr:hover {
  background: #ffe4e6;
  transition: background 0.2s;
}
.btn-red {
  background: #dc2626;
  color: #fff;
  border-radius: 0.5rem;
  font-weight: bold;
  padding: 0.5rem 1.5rem;
  box-shadow: 0 2px 8px 0 #dc262633;
  transition: background 0.2s, transform 0.15s;
}
.btn-red:hover {
  background: #b91c1c;
  transform: translateY(-2px) scale(1.04);
}
.btn-secondary {
  background: #fca5a5;
  color: #b91c1c;
  border-radius: 0.5rem;
  font-weight: bold;
  padding: 0.5rem 1.2rem;
  box-shadow: 0 2px 8px 0 #fca5a533;
  transition: background 0.2s, color 0.2s, transform 0.15s;
}
.btn-secondary:hover {
  background: #fecaca;
  color: #7f1d1d;
  transform: translateY(-2px) scale(1.04);
}
#blood-types-table td.input-cell {
  text-align: center;
}
#blood-types-table select,
#blood-types-table input[type=number] {
  text-align: center;
}
</style>
@endpush

