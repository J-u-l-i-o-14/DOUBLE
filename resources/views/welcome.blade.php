@extends('layouts.public')

@section('title', 'Accueil')
@section('content')
    <!-- Gros Carrousel sous la navbar -->
    <div class="relative w-full h-96 overflow-hidden">
        <div class="absolute inset-0 z-0">
            <div class="w-full h-full carousel" id="mainCarousel">
                <div class="w-full h-full">
                    <img src="images/savelife.webp" class="object-cover w-full h-96 block carousel-img" alt="Don de sang">
                    <div class="absolute inset-0 flex flex-col items-center justify-center bg-black/40">
                        <h2 class="text-3xl md:text-5xl font-bold text-white mb-2 drop-shadow">Sauvez des vies, donnez votre sang !</h2>
                        <p class="text-lg md:text-2xl text-white">Un petit geste, un grand impact.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <section class="py-10 bg-gradient-to-b from-red-100 to-white text-center">
        <div class="max-w-4xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-8">
            <a href="{{ route('appointment.public') }}" class="transform transition-all duration-300 hover:scale-105">
                <div class="bg-white rounded-lg shadow p-8 cursor-pointer hover:shadow-lg">
                    <div class="text-4xl font-extrabold text-red-600 mb-4">
                        <i class="fas fa-calendar-plus"></i>
                    </div>
                    <div class="text-2xl font-bold text-gray-800">Prendre Rendez-Vous</div>
                    <div class="text-gray-600 mt-2">Pour donner votre sang et sauver des vies</div>
                </div>
            </a>
            <a href="{{ route('blood.reservation') }}" class="transform transition-all duration-300 hover:scale-105">
                <div class="bg-white rounded-lg shadow p-8 cursor-pointer hover:shadow-lg">
                    <div class="text-4xl font-extrabold text-blue-600 mb-4">
                        <i class="fas fa-tint"></i>
                    </div>
                    <div class="text-2xl font-bold text-gray-800">Réserver des poches de sang</div>
                    <div class="text-gray-600 mt-2">Accéder aux stocks disponibles</div>
                </div>
            </a>
        </div>
    </section>

    <!-- Corps du site : 3 parties (conditions, contre-indication, étapes) -->
            {{-- @csrf
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
        </form> --}}
    {{-- </section>
    @push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- Empêcher la sélection de groupes sanguins déjà choisis ---
    function updateBloodTypeOptions() {
        // Récupère tous les selects de type sanguin
        const selects = document.querySelectorAll('#blood-types-table select[name^="blood_types"]');
        // Liste des valeurs déjà sélectionnées
        const selectedValues = Array.from(selects).map(s => s.value).filter(v => v);
        selects.forEach(select => {
            const currentValue = select.value;
            Array.from(select.options).forEach(opt => {
                if(opt.value === "" || opt.value === currentValue) {
                    opt.disabled = false;
                } else {
                    // Désactive si déjà sélectionné ailleurs
                    opt.disabled = selectedValues.includes(opt.value);
                }
            });
        });
    }
    // Sur ajout d'une ligne, mettre à jour les options
    document.getElementById('add-row').addEventListener('click', function() {
        setTimeout(updateBloodTypeOptions, 10);
    });
    // Sur changement d'un select, mettre à jour les autres
    document.querySelector('#blood-types-table tbody').addEventListener('change', function(e) {
        if(e.target && e.target.tagName === 'SELECT') {
            updateBloodTypeOptions();
        }
    });
    // Sur suppression d'une ligne, mettre à jour les options
    document.querySelector('#blood-types-table tbody').addEventListener('click', function(e) {
        if(e.target && e.target.classList.contains('remove-row')) {
            setTimeout(updateBloodTypeOptions, 10);
        }
    });
    // Initial update au chargement
    updateBloodTypeOptions();
});
</script>
@endpush
    <!-- Fin formulaire recherche avancé -->


    <!-- Résultats de la recherche (AJAX) -->
    <div id="search-results"></div>

    <!-- Loader résultats -->
    <div id="results-loader" class="w-full flex justify-center items-center py-8 hidden">
      <div class="animate-spin rounded-full h-12 w-12 border-t-4 border-b-4 border-red-600 opacity-70"></div>
    </div> --}}

    <!-- Modal de réservation -->
    <div id="reservation-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 hidden">
      <div class="bg-gradient-to-br from-red-50 to-white rounded-xl shadow-2xl max-w-4xl w-full px-12 py-4 relative flex flex-col items-center max-h-[80vh] overflow-y-auto">
        <button id="close-modal" class="absolute top-3 right-4 text-gray-400 hover:text-red-600 text-3xl font-bold">&times;</button>
        <h2 class="text-3xl font-bold text-red-700 mb-6 text-center">Réserver des poches de sang</h2>
        <div class="mb-6 w-full max-w-2xl flex flex-col md:flex-row md:items-center md:justify-between gap-12">
          <div class="flex-1 flex items-center gap-2">
            <span class="font-semibold">Centre :</span>
            <span id="modal-center" class="text-gray-700"></span>
          </div>
          <div class="flex-1 flex items-center gap-2">
            <span class="font-semibold">Groupe sanguin :</span>
            <span id="modal-blood-type" class="text-gray-700"></span>
          </div>
          <div class="flex-1 flex items-center gap-2">
            <span class="font-semibold">Quantité :</span>
            <span id="modal-quantity" class="text-gray-700"></span>
          </div>
        </div>
        <form id="reservation-form" enctype="multipart/form-data" class="w-full max-w-lg">
          <input type="hidden" name="center_id" id="modal-center-id">
          <input type="hidden" name="blood_type" id="modal-blood-type-input">
          <input type="hidden" name="quantity" id="modal-quantity-input">
          <div class="mb-4">
            <label class="block text-gray-700 font-medium mb-1" for="client-name">Nom <span class="text-red-500">*</span></label>
            <input type="text" name="client_name" id="client-name" class="w-full border rounded px-3 py-2" required>
          </div>
          <div class="mb-4">
            <label class="block text-gray-700 font-medium mb-1" for="client-email">Email <span class="text-red-500">*</span></label>
            <input type="email" name="client_email" id="client-email" class="w-full border rounded px-3 py-2" required>
          </div>
          <div class="mb-4">
            <label class="block text-gray-700 font-medium mb-1" for="client-phone">Téléphone <span class="text-red-500">*</span></label>
            <input type="tel" name="client_phone" id="client-phone" class="w-full border rounded px-3 py-2" pattern="^(9|7|2)[0-9]{7}$" maxlength="8" minlength="8" placeholder="Ex: 90123456" required>
            <div class="text-xs text-gray-500">Format : 8 chiffres</div>
          </div>
          <div class="mb-4">
            <label class="block text-gray-700 font-medium mb-1" for="client-docs">Documents justificatifs <span class="text-red-500">*</span></label>
            <input type="file" name="client_docs[]" id="client-docs" class="w-full border rounded px-3 py-2" accept=".pdf,.jpg,.jpeg,.png" multiple required>
            <div id="docs-list" class="text-xs text-gray-600 mt-1"></div>
          </div>
          <div class="mb-4">
            <label class="block text-gray-700 font-medium mb-1">Moyen de paiement <span class="text-red-500">*</span></label>
            <div class="flex flex-col gap-2 md:flex-row md:gap-6">
              <label class="inline-flex items-center gap-2 cursor-pointer">
                <img src="/images/yas.png" alt="T Money" class="w-8 h-8 object-contain"> <input type="radio" name="payment_method" value="Yas" required class="accent-red-600"> Mix by Yas
              </label>
              <label class="inline-flex items-center gap-2 cursor-pointer">
                <img src="/images/flooz.png" alt="Flooz" class="w-8 h-8 object-contain"> <input type="radio" name="payment_method" value="Moov Money" required class="accent-red-600"> Moov Money
              </label>
              <label class="inline-flex items-center gap-2 cursor-pointer">
                <img src="/images/carte.jpg" alt="Carte Bancaire" class="w-8 h-8 object-contain"> <input type="radio" name="payment_method" value="Carte Bancaire" required class="accent-red-600"> Carte Bancaire
              </label>
            </div>
          </div>
          <div class="mb-2 text-lg font-semibold text-center">
            Montant total : <span id="modal-total" class="text-black">0 F CFA</span>
          </div>
          <div class="mb-6 text-lg font-semibold text-center">
            Montant à payer : <span id="modal-amount" class="text-green-700">0 F CFA</span> (50% du total)
          </div>
          <button type="submit" class="btn btn-red w-full mt-4">Payer et réserver</button>
        </form>
      </div>
    </div>

    <!-- Toast paiement -->
    <div id="toast" class="fixed top-8 left-1/2 z-[9999] -translate-x-1/2 bg-white/80 backdrop-blur-md border border-green-600 text-green-700 font-bold px-8 py-5 rounded-xl shadow-2xl hidden transition-all duration-300 text-center max-w-lg w-full"></div>

    <!-- Overlay paiement -->
    <div id="payment-overlay" class="fixed inset-0 z-[9998] bg-white/60 backdrop-blur-sm flex items-center justify-center hidden">
      <div class="animate-spin rounded-full h-20 w-20 border-t-4 border-b-4 border-red-600 opacity-80"></div>
    </div>

    <script>
    // Générer dynamiquement les options des groupes sanguins côté JS
    let bloodTypeOptions = '';
    @foreach($bloodTypes ?? [] as $type)
        bloodTypeOptions += `<option value="{{ $type->id }}">{{ $type->group }}</option>`;
    @endforeach

    // JS pour ajouter/supprimer des lignes dynamiquement
    document.addEventListener('DOMContentLoaded', function() {
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
        };
        table.addEventListener('click', function(e) {
            if(e.target && e.target.classList.contains('remove-row')) {
                e.target.closest('tr').remove();
            }
        });

        // --- Chargement dynamique des centres selon la région ---
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
        // Charger les centres si une région est déjà sélectionnée (ex: retour formulaire)
        if(regionSelect.value) loadCenters(regionSelect.value);

        // --- Recherche AJAX ---
        const form = document.getElementById('blood-search-form');
        const resultsDiv = document.getElementById('search-results');
        const resultsLoader = document.getElementById('results-loader');
        function showResultsLoader(show) {
          if(show) resultsLoader.classList.remove('hidden');
          else resultsLoader.classList.add('hidden');
        }
        form.onsubmit = function(e) {
            e.preventDefault();
            showResultsLoader(true);
            resultsDiv.innerHTML = '';
            const formData = new FormData(form);
            // Construction des données pour l'API
            const data = {
                region_id: formData.get('region_id'),
                center_id: formData.get('center_id'),
                blood_types: []
            };
            // Récupérer dynamiquement les lignes du tableau
            const rows = table.querySelectorAll('tr');
            rows.forEach(row => {
                const bloodTypeId = row.querySelector('select')?.value;
                const quantity = row.querySelector('input[type=number]')?.value;
                if(bloodTypeId && quantity) {
                    data.blood_types.push({ blood_type_id: bloodTypeId, quantity: quantity });
                }
            });
            fetch("{{ route('api.search.blood') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value
                },
                body: JSON.stringify(data)
            })
            .then(r => r.json())
            .then(res => {
                showResultsLoader(false);
                if(res.results && res.results.length > 0) {
                    let html = `<section class='max-w-full md:max-w-7xl mx-auto mb-8 p-6 bg-white rounded-lg shadow overflow-x-auto'>`;
                    html += `<h3 class='text-lg font-bold mb-4 text-red-700'>Centres pouvant contribuer à votre demande</h3>`;
                    html += `<table class='min-w-[1200px] w-full border rounded-lg overflow-hidden'><thead><tr class='bg-gray-100'>`;
                    html += `<th class='p-2'></th><th class='p-2'>Centre</th><th class='p-2'>Région</th><th class='p-2'>Adresse</th><th class='p-2'>Téléphone</th>`;
                    html += `<th class='p-2 text-center'>Groupe sanguin</th><th class='p-2 text-center'>Demandé</th><th class='p-2 text-center'>Peut fournir</th><th class='p-2'></th></tr></thead><tbody>`;
                    res.results.forEach(center => {
                        html += `<tr class='hover:bg-red-50 transition'>`;
                        html += `<td class='p-2 font-semibold'>${center.id}</td>`;
                        html += `<td class='p-2 font-semibold'>${center.name}</td>`;
                        html += `<td class='p-2'>${center.region}</td>`;
                        html += `<td class='p-2'>${center.address}</td>`;
                        html += `<td class='p-2'>${center.phone ?? ''}</td>`;
                        html += `<td class='p-2 text-center'>${center.blood_type}</td>`;
                        html += `<td class='p-2 text-center'>${center.requested_quantity}</td>`;
                        html += `<td class='p-2 font-bold text-green-700 text-center'>${center.can_provide}</td>`;
                        html += `<td class='p-2 text-center'>
                            <button type="button"
                                    class="add-to-cart-btn inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                                    data-center-id="${center.id}"
                                    data-blood-type="${center.blood_type}"
                                    data-center-name="${center.name}"
                                    data-region="${center.region}"
                                    data-quantity="${center.can_provide}"
                                    data-requested-quantity="${center.requested_quantity}"
                                    data-in-cart="false"
                            >
                                Ajouter (${center.can_provide})
                            </button>
                        </td>`;
                        html += `</tr>`;
                    });
                    html += `</tbody></table></section>`;
                    resultsDiv.innerHTML = html;
                } else {
                    resultsDiv.innerHTML = `<section class='max-w-4xl mx-auto mb-8 p-6'><div class='w-full bg-red-600 text-white text-lg font-semibold rounded-lg p-6 text-center shadow-lg'><span class='block'>Aucune poche de sang n'est disponible dans les centres pour les critères demandés.</span></div></section>`;
                }
            })
            .catch(() => {
                showResultsLoader(false);
                resultsDiv.innerHTML = `<div class='w-full bg-red-600 text-white text-lg font-semibold rounded-lg p-6 text-center shadow-lg'>Erreur lors de la recherche.</div>`;
            });

        };

        // Gestion du panier
    document.addEventListener('click', function(e) {
    if(e.target && e.target.classList.contains('add-to-cart-btn')) {
        const btn = e.target;
        const quantity = parseInt(btn.dataset.quantity);

        const data = {
            center_id: btn.dataset.centerId,
            blood_type: btn.dataset.bloodType,
            quantity: quantity

        };

        fetch('/cart/add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(data)
        })
        .then(response => {
            if (response.status === 401) {
                // Sauvegarder l'état de la recherche
                const searchParams = new FormData(document.getElementById('blood-search-form'));
                const params = {};
                searchParams.forEach((value, key) => {
                    if (value) params[key] = value;
                });

            }
            return response.json();
        })
        .then(data => {
            if (data && data.success) {
                showToast(`✔️ ${quantity} poche(s) ajoutée(s) au panier`, false);
                // Désactiver le bouton après l'ajout
                btn.disabled = true;
                btn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
                btn.classList.add('bg-gray-400');
                btn.textContent = 'Ajouté';
            } else {
                showToast(data.message || 'Erreur lors de l\'ajout au panier', true);
            }
        })
        .catch(error => {
            showToast('Erreur lors de l\'ajout au panier', true);
        });
        }
    });
    });











    // Modal réservation
    function openReservationModal(center) {
      document.getElementById('modal-center').textContent = center.name + ' (' + center.region + ')';
      document.getElementById('modal-blood-type').textContent = center.blood_type;
      document.getElementById('modal-quantity').textContent = center.can_provide;
      document.getElementById('modal-center-id').value = center.id;
      document.getElementById('modal-blood-type-input').value = center.blood_type;
      document.getElementById('modal-quantity-input').value = center.can_provide;
      // Calcul du montant total et à payer (5000 F CFA par poche × quantité)
      const qty = parseInt(center.can_provide, 10) || 0;
      const total = qty * 5000;
      const toPay = Math.round(total * 0.5);
      document.getElementById('modal-total').textContent = total.toLocaleString('fr-FR') + ' F CFA';
      document.getElementById('modal-amount').textContent = toPay.toLocaleString('fr-FR') + ' F CFA';
      document.getElementById('reservation-modal').classList.remove('hidden');
    }
    document.addEventListener('click', function(e) {
      if(e.target && e.target.classList.contains('reserve-btn')) {
        e.preventDefault();
        // Récupérer les infos du centre à partir de la ligne du tableau
        const row = e.target.closest('tr');
        const tds = row.querySelectorAll('td');
        const center = {
          id: tds[0].textContent.trim(),
          name: tds[1].textContent.trim(),
          region: tds[2].textContent.trim(),
          address: tds[3].textContent.trim(),
          phone: tds[4].textContent.trim(),
          blood_type: tds[5].textContent.trim(),
          requested_quantity: tds[6].textContent.trim(),
          can_provide: tds[7].textContent.trim(),
        };
        openReservationModal(center);
      }
    });
    document.getElementById('close-modal').onclick = function() {
      document.getElementById('reservation-modal').classList.add('hidden');
    };
    document.getElementById('reservation-modal').addEventListener('click', function(e) {
      if(e.target === this) this.classList.add('hidden');
    });
    // Affichage des fichiers sélectionnés (plusieurs fichiers)
    if(document.getElementById('client-docs')) {
      document.getElementById('client-docs').addEventListener('change', function() {
        const files = Array.from(this.files);
        const list = files.map(f => `<span class='inline-block bg-gray-100 rounded px-2 py-1 mr-1 mb-1'>${f.name}</span>`).join('');
        document.getElementById('docs-list').innerHTML = list;
      });
    }
    // (Optionnel) Empêcher la soumission réelle pour l'instant
    const paymentOverlay = document.getElementById('payment-overlay');
    if(document.getElementById('reservation-form')) {
      document.getElementById('reservation-form').onsubmit = async function(e) {
        e.preventDefault();
        paymentOverlay.classList.remove('hidden');
        const form = this;
        const btn = form.querySelector('button[type=submit]');
        btn.disabled = true;
        btn.textContent = 'Envoi...';
        // Vérifier qu'un moyen de paiement est sélectionné
        const paymentMethod = form.querySelector('input[name=payment_method]:checked');
        if(!paymentMethod) {
          btn.disabled = false;
          btn.textContent = 'Payer et réserver';
          showToast('Veuillez choisir un moyen de paiement.', true);
          return;
        }
        // Validation téléphone front (8 chiffres, commence par 9, 7 ou 2)
        const phone = form.querySelector('#client-phone').value.trim();
        if(!/^(9|7|2)[0-9]{7}$/.test(phone)) {
          btn.disabled = false;
          btn.textContent = 'Payer et réserver';
          showToast('Numéro de téléphone invalide (8 chiffres, commence par 9, 7 ou 2).', true);
          return;
        }
        // Construction du FormData avec la structure attendue
        const formData = new FormData();
        formData.append('center_id', document.getElementById('modal-center-id').value);
        formData.append('client_name', document.getElementById('client-name').value);
        formData.append('client_email', document.getElementById('client-email').value);
        formData.append('client_phone', document.getElementById('client-phone').value);
        formData.append('payment_method', paymentMethod.value);
        // Ajout des items (un seul dans le modal, mais structure tableau)
        formData.append('items[0][blood_type_id]', document.getElementById('modal-blood-type-input').value);
        formData.append('items[0][quantity]', document.getElementById('modal-quantity-input').value);
        // Ajout des fichiers multiples
        const files = document.getElementById('client-docs').files;
        for(let i=0; i<files.length; i++) {
          formData.append('client_docs[]', files[i]);
        }
        // Envoi AJAX
        let msg = '';
        try {
          const res = await fetch("{{ route('reservation.store') }}", {
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': document.querySelector('input[name=_token]')?.value || document.querySelector('meta[name=csrf-token]')?.content
            },
            body: formData
          });
          const data = await res.json();
          if(data.success) {
            msg = `✔️ ${data.message}<br>Moyen de paiement choisi : <span class='underline'>${paymentMethod.value}</span>`;
            form.reset();
            document.getElementById('docs-list').textContent = '';
            showToast(msg, false);
          } else {
            msg = `❌ Erreur lors de la réservation.`;
            showToast(msg, true);
          }
        } catch (err) {
          msg = `❌ Erreur lors de la réservation.`;
          showToast(msg, true);
        }
        btn.disabled = false;
        btn.textContent = 'Payer et réserver';
        setTimeout(() => {
          paymentOverlay.classList.add('hidden');
          document.getElementById('reservation-modal').classList.add('hidden');
        }, 2000);
      };
    }
    // Toast paiement
    function showToast(message, isError) {
      const toast = document.getElementById('toast');
      toast.innerHTML = message;
      toast.classList.remove('hidden');
      toast.classList.toggle('border-green-600', !isError);
      toast.classList.toggle('text-green-700', !isError);
      toast.classList.toggle('border-red-600', isError);
      toast.classList.toggle('text-red-700', isError);
      toast.classList.toggle('backdrop-blur-md', true);
      toast.classList.toggle('bg-white/80', true);
      setTimeout(() => {
        toast.classList.add('hidden');
      }, 3500);
    }
    </script>

    <!-- Statistiques (donneurs, poches)
    <section class="py-10 bg-gradient-to-b from-red-100 to-white text-center">
        <div class="max-w-4xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-4xl font-extrabold text-red-600">120</div>
                <div class="text-lg font-medium mt-2">Donneurs inscrits</div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-4xl font-extrabold text-blue-600">45</div>
                <div class="text-lg font-medium mt-2">Poches disponibles</div>
            </div>
        </div>
    </section> -->

    <!-- Corps du site : 3 parties (conditions, contre-indication, étapes) -->
    <div class="max-w-7xl mx-auto px-4 py-10">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Partie 1 : Conditions pour donner son sang -->
            <div class="bg-white rounded-lg shadow p-6 flex flex-col gap-4">
                <h3 class="text-2xl font-bold text-red-600 mb-2">Puis-je donner mon sang aujourd'hui ?</h3>
                <img src="images/coeur.jpg" alt="Don de sang" class="rounded-lg w-full h-40 object-cover">
                <ul class="list-disc list-inside text-gray-700 mt-2 space-y-1">
                    <li>Vous pesez au minimum 50 kilos</li>
                    <li>Vous avez entre 18 et 70 ans</li>
                    <li>Vous n'êtes pas malade, ou si vous l'avez été ce mois-ci, les symptômes ont disparu depuis 15 jours au moins</li>
                    <li>Vous n'avez pas d'extraction dentaire datant de moins d'une semaine</li>
                    <li>Vous ne suivez pas de traitement d'insuline pour soigner un diabète</li>
                    <li>Vous n'avez aucun problème cardiaque</li>
                    <li>Vous n'êtes pas enceinte</li>
                    <li>Vous n'avez pas accouché il y a moins de 6 mois</li>
                </ul>
            </div>
            <!-- Partie 2 : Pas de contre-indication ? -->
            <div class="bg-white rounded-lg shadow p-6 flex flex-col gap-4">
                <h3 class="text-2xl font-bold text-blue-700 mb-2">Pas de contre-indication ?</h3>
                <ul class="list-disc list-inside text-gray-700 mt-2 space-y-1">
                    <li>Votre organisme a eu le temps d'éliminer une éventuelle consommation d'alcool</li>
                    <li>Vous n'êtes pas à jeun ou déshydraté</li>
                    <li>Vous disposez d'un justificatif d'identité (obligatoire pour un premier don), ou de votre carte de donneur (facultatif)</li>
                </ul>
                <div class="mt-4 p-3 rounded bg-green-100 text-green-800 font-semibold text-center">Vous pouvez prendre rendez-vous pour donner votre sang !</div>
            </div>
        </div>
        <!-- Partie 3 : Étapes du don de sang -->
        <div class="bg-white rounded-lg shadow p-6 mt-8">
            <h3 class="text-2xl font-bold text-center text-blue-600 mb-6">Étapes du don de sang</h3>
            <div class="flex flex-col md:flex-row items-center gap-6">
                <img src="images/don.jpg" alt="Etapes don de sang" class="rounded-lg w-full md:w-1/3 h-40 object-cover">
                <ol class="list-decimal list-inside text-gray-700 space-y-2 w-full md:w-2/3">
                    <li><span class="font-bold text-red-600">Accueil et inscription :</span> Un <em>questionnaire</em> vous est remis afin de constituer le dossier de donneur, et de servir de base à l'entretien médical.</li>
                    <li><span class="font-bold text-red-600">L'entretien médical :</span> Confidentiel et couvert par le secret médical, l'entretien médical est essentiel pour la sécurité transfusionnelle.</li>
                    <li><span class="font-bold text-red-600">Le don :</span> Le prélèvement dure entre 8 et 10 minutes. Il est réalisé dans des conditions de sécurité et d'hygiène optimales.</li>
                    <li><span class="font-bold text-red-600">La collation :</span> Après le don, une collation vous est offerte pour reprendre des forces.</li>
                </ol>
            </div>
        </div>
    </div>

    <!-- Carrousel Section Témoignages & Actualités -->
    <section class="py-10 bg-white">
        <div class="max-w-5xl mx-auto">
            <h2 class="text-2xl font-bold text-center mb-6 text-red-700">Témoignages & Actualités</h2>
            <div class="relative w-full h-72 rounded-lg overflow-hidden shadow">
                <div class="absolute inset-0 flex items-center justify-center bg-black/40 z-10">
                    <div class="text-center">
                        <h5 class="text-xl md:text-2xl font-semibold text-white mb-2">"Donner son sang, c'est offrir la vie"</h5>
                        <p class="text-white">- Sarah, donneuse régulière</p>
                    </div>
                </div>
                <img src="https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=800&q=80" class="object-cover w-full h-72" alt="Don de sang">
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                <div class="relative h-60 rounded-lg overflow-hidden shadow">
                    <div class="absolute inset-0 flex items-center justify-center bg-black/40 z-10">
                        <div class="text-center">
                            <h5 class="text-lg font-semibold text-white mb-2">Collecte spéciale ce samedi !</h5>
                            <p class="text-white">Venez nombreux à notre centre pour la grande collecte mensuelle.</p>
                        </div>
                    </div>
                    <img src="https://images.unsplash.com/photo-1519494026892-80bbd2d6fd21?auto=format&fit=crop&w=800&q=80" class="object-cover w-full h-60" alt="Actualité">
                </div>
                <div class="relative h-60 rounded-lg overflow-hidden shadow">
                    <div class="absolute inset-0 flex items-center justify-center bg-black/40 z-10">
                        <div class="text-center">
                            <h5 class="text-lg font-semibold text-white mb-2">"Grâce à un donneur, j'ai retrouvé la santé"</h5>
                            <p class="text-white">- Ahmed, bénéficiaire</p>
                        </div>
                    </div>
                    <img src="avif.avif" class="object-cover w-full h-60" alt="Témoignage">
                </div>
            </div>
        </div>
    </section>

    <!-- Formulaire de contact
    <section class="py-10 bg-gray-100" id="contact">
        <div class="max-w-xl mx-auto bg-white rounded-lg shadow p-8">
            <h2 class="text-2xl font-bold text-center mb-6 text-red-700">Contactez-nous</h2>

            <!-- Messages de succès et d'erreur -->
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('contact.send') }}" class="mx-auto max-w-lg bg-white p-4 rounded shadow">
                @csrf
                <div>
                    <label for="name" class="block text-gray-700 font-medium mb-1">Nom</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500" placeholder="Votre nom" required>
                </div>
                <div>
                    <label for="email" class="block text-gray-700 font-medium mb-1">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500" placeholder="Votre email" required>
                </div>
                <div>
                    <label for="message" class="block text-gray-700 font-medium mb-1">Message</label>
                    <textarea name="message" id="message" rows="4" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500" placeholder="Votre message" required>{{ old('message') }}</textarea>
                </div>
                @if(session('success'))
                    <div class="alert alert-success mt-2">{{ session('success') }}</div>
                @endif
                <button type="submit" class="btn btn-red w-100">Envoyer</button>
            </form>
        </div>
    </section>

    <!-- Formulaire de recherche multicritère sang -->

    <!-- Fin formulaire recherche -->

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
@endsection
