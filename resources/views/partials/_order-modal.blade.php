<!-- Modal de réservation et paiement -->
<div id="order-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4 max-h-screen overflow-y-auto">
        <!-- En-tête du modal -->
        <div class="flex items-center justify-between p-6 border-b border-gray-200">
            <h3 class="text-xl font-semibold text-gray-900">
                🩸 Finaliser la commande
            </h3>
            <button type="button" onclick="closeOrderModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Contenu du modal -->
        <form id="order-form" class="p-6">
            @csrf
            
            <!-- Récapitulatif du panier -->
            <div class="mb-6">
                <h4 class="font-semibold text-gray-900 mb-3">📋 Récapitulatif de votre commande</h4>
                <div id="order-summary" class="bg-gray-50 rounded-lg p-4 space-y-2">
                    <!-- Sera rempli dynamiquement -->
                </div>
                <div class="mt-3 text-right">
                    <span class="text-lg font-bold text-red-600">Total: </span>
                    <span id="order-total" class="text-lg font-bold text-red-600">0 F CFA</span>
                </div>
            </div>

            <!-- Numéro d'ordonnance -->
            <div class="mb-6">
                <label for="prescription_number" class="block text-sm font-medium text-gray-700 mb-2">
                    📋 Numéro d'ordonnance *
                </label>
                <input type="text" 
                       id="prescription_number" 
                       name="prescription_number" 
                       required 
                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
                       placeholder="Ex: ORD-2025-001234">
                <p class="text-xs text-gray-500 mt-1">Entrez le numéro d'ordonnance prescrite par votre médecin</p>
            </div>

            <!-- Notes additionnelles -->
            <div class="mb-6">
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                    📝 Notes additionnelles (optionnel)
                </label>
                <textarea id="notes" 
                          name="notes" 
                          rows="3"
                          class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
                          placeholder="Informations complémentaires..."></textarea>
            </div>

            <!-- Informations importantes -->
            <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h5 class="font-semibold text-blue-900 mb-2">ℹ️ Informations importantes</h5>
                <ul class="text-sm text-blue-800 space-y-1">
                    <li>• Votre commande sera traitée sous 24-48h</li>
                    <li>• Vous pouvez suivre l'etat de votre réservation</li>
                    <li>• Prix: 5 000 F CFA par poche de sang</li>
                    <li>• Le centre vous contactera pour la collecte</li>
                </ul>
            </div>
        </form>

        <!-- Pied du modal -->
        <div class="flex items-center justify-between p-6 border-t border-gray-200 bg-gray-50">
            <button type="button" 
                    onclick="closeOrderModal()" 
                    class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500">
                Annuler
            </button>
            <button type="button" 
                    onclick="submitOrder()" 
                    id="submit-order-btn"
                    class="px-6 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed">
                🛒 Confirmer la commande
            </button>
        </div>
    </div>
</div>

<!-- Script pour le modal de commande -->
<script>
// Variables globales pour le modal de commande
let orderModalLoading = false;

// Ouvrir le modal de commande
window.openOrderModal = function() {
    console.log('Ouverture du modal de commande...');
    
    // D'abord charger le récapitulatif du panier
    loadOrderSummary();
    
    // Afficher le modal
    document.getElementById('order-modal').classList.remove('hidden');
    
    // Focus sur le champ d'ordonnance
    setTimeout(() => {
        document.getElementById('prescription_number').focus();
    }, 100);
};

// Fermer le modal de commande
window.closeOrderModal = function() {
    document.getElementById('order-modal').classList.add('hidden');
    
    // Réinitialiser le formulaire
    document.getElementById('order-form').reset();
    document.getElementById('order-summary').innerHTML = '';
    document.getElementById('order-total').textContent = '0 F CFA';
};

// Charger le récapitulatif du panier
window.loadOrderSummary = function() {
    fetch('/cart', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        const summaryDiv = document.getElementById('order-summary');
        const totalSpan = document.getElementById('order-total');
        
        if (data.success && data.items && data.items.length > 0) {
            let html = '';
            let total = 0;
            
            data.items.forEach(item => {
                const itemTotal = item.quantity * 5000; // 5000 F CFA par poche
                total += itemTotal;
                
                html += `
                    <div class="flex justify-between items-center py-2 border-b border-gray-200 last:border-b-0">
                        <div>
                            <div class="font-medium text-gray-900">${item.center_name}</div>
                            <div class="text-sm text-gray-600">${item.blood_type} - ${item.quantity} poche(s)</div>
                        </div>
                        <div class="font-semibold text-gray-900">
                            ${itemTotal.toLocaleString('fr-FR')} F CFA
                        </div>
                    </div>
                `;
            });
            
            summaryDiv.innerHTML = html;
            totalSpan.textContent = total.toLocaleString('fr-FR') + ' F CFA';
        } else {
            summaryDiv.innerHTML = '<p class="text-gray-500 text-center py-4">Aucun article dans le panier</p>';
            totalSpan.textContent = '0 F CFA';
        }
    })
    .catch(error => {
        console.error('Erreur lors du chargement du récapitulatif:', error);
        document.getElementById('order-summary').innerHTML = '<p class="text-red-500 text-center py-4">Erreur lors du chargement</p>';
    });
};

// Soumettre la commande
window.submitOrder = function() {
    const form = document.getElementById('order-form');
    const submitBtn = document.getElementById('submit-order-btn');
    const prescriptionNumber = document.getElementById('prescription_number').value.trim();
    
    // Validation
    if (!prescriptionNumber) {
        showToast('Veuillez entrer le numéro d\'ordonnance', true);
        document.getElementById('prescription_number').focus();
        return;
    }
    
    if (orderModalLoading) return;
    
    // Désactiver le bouton et afficher le loading
    orderModalLoading = true;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '⏳ Traitement en cours...';
    
    const formData = new FormData(form);
    const data = {
        prescription_number: formData.get('prescription_number'),
        notes: formData.get('notes')
    };
    
    fetch('/order', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        console.log('Réponse commande:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP Error: ${response.status}`);
        }
        return response.json();
    })
    .then(responseData => {
        console.log('Données de la commande:', responseData);
        
        if (responseData.success) {
            // Succès
            showToast(`✅ ${responseData.message} - Total: ${responseData.formatted_total}`, false);
            
            // Fermer le modal
            closeOrderModal();
            
            // Mettre à jour l'affichage
            if (typeof updateButtonStatesAfterCartChange === 'function') {
                updateButtonStatesAfterCartChange();
            }
            
            // Recharger la page après 2 secondes
            setTimeout(() => {
                window.location.reload();
            }, 2000);
            
        } else {
            showToast(`❌ ${responseData.message}`, true);
        }
    })
    .catch(error => {
        console.error('Erreur lors de la commande:', error);
        showToast('❌ Erreur lors de la commande', true);
    })
    .finally(() => {
        // Réactiver le bouton
        orderModalLoading = false;
        submitBtn.disabled = false;
        submitBtn.innerHTML = '🛒 Confirmer la commande';
    });
};

// Gestionnaire pour fermer le modal avec Échap
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('order-modal');
        if (modal && !modal.classList.contains('hidden')) {
            closeOrderModal();
        }
    }
});
</script>
