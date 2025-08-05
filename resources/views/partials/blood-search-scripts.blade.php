<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- Empêcher la sélection de groupes sanguins déjà choisis ---
    function updateBloodTypeOptions() {
        //Recupere tous les select de types sanguins
        const selects = document.querySelectorAll('#blood-types-table select[name^="blood_types"]');
        //Liste les valeurs déja sélectionnées
        const selectedValues = Array.from(selects).map(s => s.value).filter(v => v);
        selects.forEach(select => {
            const currentValue = select.value;
            Array.from(select.options).forEach(opt => {
                if(opt.value === "" || opt.value === currentValue) {
                    opt.disabled = false;
                } else {
                    //Desactiver si c est deja selectionner ailleurs
                    opt.disabled = selectedValues.includes(opt.value);
                }
            });
        });
    }

    // Générer dynamiquement les options des groupes sanguins
    let bloodTypeOptions = '';
    @foreach($bloodTypes ?? [] as $type)
        bloodTypeOptions += `<option value="{{ $type->id }}">{{ $type->group }}</option>`;
    @endforeach

    // Gestion du tableau dynamique
    const table = document.getElementById('blood-types-table').getElementsByTagName('tbody')[0];
    
    document.getElementById('add-row').onclick = function() {
        const rowCount = table.rows.length;
        const row = table.insertRow();
        row.innerHTML = `
            <td class="input-cell">
                <select name="blood_types[${rowCount}][blood_type_id]" class="border rounded px-2 py-1">
                    <option value="">Choisir</option>
                    ${bloodTypeOptions}
                </select>
            </td>
            <td class="input-cell">
                <input type="number" min="1" name="blood_types[${rowCount}][quantity]" class="border rounded px-2 py-1 w-24">
            </td>
            <td>
                <button type="button" class="remove-row text-red-600 font-bold">&times;</button>
            </td>
        `;
        setTimeout(updateBloodTypeOptions, 10);
    };

    table.addEventListener('click', function(e) {
        if(e.target && e.target.classList.contains('remove-row')) {
            e.target.closest('tr').remove();
            setTimeout(updateBloodTypeOptions, 10);
        }
    });

    // Chargement dynamique des centres selon la région
    const regionSelect = document.getElementById('region_id');
    const centerSelect = document.getElementById('center_id');
    
    function loadCenters(regionId) {
        centerSelect.innerHTML = '<option value="">Tous les centres</option>';
        if(!regionId) return;
        fetch(`/api/centers-by-region/${regionId}`)
            .then(r => r.json())
            .then(data => {
                data.centers.forEach(center => {
                    const opt = document.createElement('option');
                    opt.value = center.id;
                    opt.textContent = center.name;
                    centerSelect.appendChild(opt);
                });
            });
    }

    regionSelect.addEventListener('change', function() {
        loadCenters(this.value);
    });

    if(regionSelect.value) loadCenters(regionSelect.value);

    // Mise à jour initiale des options
    updateBloodTypeOptions();

    // Recherche AJAX
    const form = document.getElementById('blood-search-form');
    const resultsDiv = document.getElementById('search-results');
    const resultsLoader = document.getElementById('results-loader');

    function showResultsLoader(show) {
        resultsLoader.classList.toggle('hidden', !show);
    }

    form.onsubmit = function(e) {
        e.preventDefault();
        showResultsLoader(true);
        resultsDiv.innerHTML = '';

        const formData = new FormData(form);
        const data = {
            region_id: formData.get('region_id'),
            center_id: formData.get('center_id'),
            blood_types: []
        };

        const rows = table.querySelectorAll('tr');
        rows.forEach(row => {
            const bloodTypeId = row.querySelector('select')?.value;
            const quantity = row.querySelector('input[type=number]')?.value;
            if(bloodTypeId && quantity) {
                data.blood_types.push({ blood_type_id: bloodTypeId, quantity: quantity });
            }
        });

        fetch("{{ route('blood.reservation.search') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(data)
        })
        .then(r => r.json())
        .then(res => {
            showResultsLoader(false);
            if(res.results && res.results.length > 0) {
                // Afficher les résultats sans vérifier l'état du panier pour éviter le changement automatique
                let html = `<section class='max-w-full md:max-w-7xl mx-auto mb-8 p-6 bg-white rounded-lg shadow overflow-x-auto'>`;
                html += `<h3 class='text-lg font-bold mb-4 text-red-700'>Centres pouvant contribuer à votre demande</h3>`;
                html += `<table class='min-w-[1200px] w-full border rounded-lg overflow-hidden'>
                            <thead>
                                <tr class='bg-gray-100'>
                                    <th class='p-2'>Centre</th>
                                    <th class='p-2'>Région</th>
                                    <th class='p-2'>Adresse</th>
                                    <th class='p-2'>Téléphone</th>
                                    <th class='p-2 text-center'>Groupe sanguin</th>
                                    <th class='p-2 text-center'>Demandé</th>
                                    <th class='p-2 text-center'>Disponible</th>
                                    <th class='p-2'></th>
                                </tr>
                            </thead>
                            <tbody>`;

                res.results.forEach(center => {
                    // Tous les boutons commencent en bleu (état par défaut)
                    html += `<tr class='hover:bg-red-50 transition'>
                        <td class='p-2 font-semibold'>${center.name}</td>
                        <td class='p-2'>${center.region}</td>
                        <td class='p-2'>${center.address}</td>
                        <td class='p-2'>${center.phone ?? ''}</td>
                        <td class='p-2 text-center'>${center.blood_type}</td>
                        <td class='p-2 text-center'>${center.requested_quantity}</td>
                        <td class='p-2 font-bold text-green-700 text-center'>${center.can_provide}</td>
                        <td class='p-2 text-center'>
                            <button type="button" 
                                    class="add-to-cart-btn inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                                    data-center-id="${center.id}"
                                    data-blood-type="${center.blood_type}"
                                    data-quantity="${center.can_provide}"
                                    data-requested-quantity="${center.requested_quantity}"
                            >
                                Ajouter (${center.can_provide})
                            </button>
                        </td>
                    </tr>`;
                });

                html += `</tbody></table>`;
                
                // Ajouter le bouton "Commander"
                html += `<div class="mt-6 flex justify-center">
                    <button type="button" 
                            onclick="openCartModal()"
                            class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg text-lg shadow-lg transition-colors">
                        📋 Commander
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
        .catch(() => {
            showResultsLoader(false);
            resultsDiv.innerHTML = `<div class='w-full bg-red-600 text-white text-lg font-semibold rounded-lg p-6 text-center shadow-lg'>
                Erreur lors de la recherche.
            </div>`;
        });
    };

    // Gestion du panier - CORRIGÉE
    document.addEventListener('click', function(e) {
        if(e.target && e.target.classList.contains('add-to-cart-btn')) {
            const btn = e.target;
            const originalText = btn.textContent;
            const isCurrentlyInCart = btn.classList.contains('in-cart');
            
            // Désactiver temporairement le bouton
            btn.disabled = true;
            btn.textContent = 'Traitement...';
            
            const data = {
                center_id: btn.dataset.centerId,
                blood_type: btn.dataset.bloodType,
                quantity: parseInt(btn.dataset.quantity)
            };

            // Si le bouton est rouge (article dans le panier), on utilise removeByData
            // Si le bouton est bleu (article pas dans le panier), on utilise add
            const url = isCurrentlyInCart ? '/cart/remove-by-data' : '/cart/add';
            const method = isCurrentlyInCart ? 'DELETE' : 'POST';

            fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(data)
            })
            .then(response => {
                console.log('Réponse du serveur:', response.status, response.statusText);
                if (!response.ok) {
                    throw new Error(`HTTP Error: ${response.status} ${response.statusText}`);
                }
                return response.json();
            })
            .then(responseData => {
                console.log('Données reçues:', responseData);
                if (responseData.success) {
                    if (responseData.action === 'added') {
                        // L'article a été ajouté au panier
                        btn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
                        btn.classList.add('in-cart', 'bg-red-600', 'hover:bg-red-700');
                        btn.textContent = 'Retirer';
                        showToast(`✔️ ${btn.dataset.quantity} poche(s) ajoutée(s) au panier`, false);
                    } else if (responseData.action === 'removed') {
                        // L'article a été retiré du panier
                        btn.classList.remove('in-cart', 'bg-red-600', 'hover:bg-red-700');
                        btn.classList.add('bg-blue-600', 'hover:bg-blue-700');
                        btn.textContent = `Ajouter (${btn.dataset.quantity})`;
                        showToast('✔️ Article retiré du panier', false);
                    }
                } else {
                    // En cas d'erreur, restaurer l'état original et afficher le message
                    btn.textContent = originalText;
                    if (responseData.action === 'already_exists') {
                        // Si l'article existe déjà, changer le bouton en rouge
                        btn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
                        btn.classList.add('in-cart', 'bg-red-600', 'hover:bg-red-700');
                        btn.textContent = 'Retirer';
                        showToast('ℹ️ Cet article est déjà dans votre panier', false);
                    } else {
                        showToast(responseData.message || 'Erreur lors de l\'opération', true);
                    }
                }
            })
            .catch(error => {
                // En cas d'erreur réseau, restaurer l'état original
                btn.textContent = originalText;
                showToast('Erreur lors de l\'opération', true);
                console.error('Erreur:', error);
            })
            .finally(() => {
                // Réactiver le bouton
                btn.disabled = false;
            });
        }
    });

    // Fonction pour afficher les toast messages
    function showToast(message, isError) {
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

    // Fonctions pour le modal du panier
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
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Recharger les articles du panier
                loadCartItems();
                
                // Mettre à jour l'état des boutons dans le tableau de résultats
                updateButtonStatesAfterCartChange();
                
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
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadCartItems();
                    
                    // Mettre à jour l'état des boutons dans le tableau de résultats
                    updateButtonStatesAfterCartChange();
                    
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

    // Fonction pour mettre à jour l'état des boutons après modification du panier
    window.updateButtonStatesAfterCartChange = function() {
        // Charger l'état actuel du panier
        fetch('/cart', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(cartData => {
            // Créer un Set des articles dans le panier
            const cartItems = new Set();
            if (cartData.success && cartData.items) {
                cartData.items.forEach(item => {
                    cartItems.add(`${item.center_id}-${item.blood_type}`);
                });
            }

            // Mettre à jour tous les boutons du tableau
            const buttons = document.querySelectorAll('.add-to-cart-btn');
            buttons.forEach(btn => {
                const centerId = btn.dataset.centerId;
                const bloodType = btn.dataset.bloodType;
                const quantity = btn.dataset.quantity;
                const isInCart = cartItems.has(`${centerId}-${bloodType}`);

                if (isInCart) {
                    // Article dans le panier - bouton rouge
                    btn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
                    btn.classList.add('in-cart', 'bg-red-600', 'hover:bg-red-700');
                    btn.textContent = 'Retirer';
                } else {
                    // Article pas dans le panier - bouton bleu
                    btn.classList.remove('in-cart', 'bg-red-600', 'hover:bg-red-700');
                    btn.classList.add('bg-blue-600', 'hover:bg-blue-700');
                    btn.textContent = `Ajouter (${quantity})`;
                }
            });
        })
        .catch(error => {
            console.error('Erreur lors de la mise à jour des boutons:', error);
        });
    };

    window.processPayment = function() {
        fetch('/cart/payment', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
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
            if (modal && !modal.classList.contains('hidden')) {
                closeCartModal();
            }
        }
    });
});
</script>
