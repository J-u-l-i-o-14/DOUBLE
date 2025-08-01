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

                html += `</tbody></table></section>`;
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

    // Gestion du panier
    document.addEventListener('click', function(e) {
        if(e.target && e.target.classList.contains('add-to-cart-btn')) {
            const btn = e.target;
            const data = {
                center_id: btn.dataset.centerId,
                blood_type: btn.dataset.bloodType,
                quantity: parseInt(btn.dataset.quantity),
                requested_quantity: parseInt(btn.dataset.requestedQuantity)
            };

            fetch('/cart/add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const message = data.action === 'added' 
                        ? `✔️ ${btn.dataset.quantity} poche(s) ajoutée(s) au panier`
                        : '✔️ Article retiré du panier';
                    
                    showToast(message, false);
                    
                    // Mettre à jour l'apparence du bouton
                    if (data.action === 'added') {
                        btn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
                        btn.classList.add('bg-red-600', 'hover:bg-red-700');
                        btn.textContent = 'Retirer';
                    } else {
                        btn.classList.remove('bg-red-600', 'hover:bg-red-700');
                        btn.classList.add('bg-blue-600', 'hover:bg-blue-700');
                        btn.textContent = `Ajouter (${btn.dataset.quantity})`;
                    }
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
</script>
