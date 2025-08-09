<!-- Modal de réservation et paiement élargi -->
<div id="order-reservation-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden overflow-y-auto">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4 my-8 max-h-screen overflow-y-auto">
        <!-- En-tête du modal -->
        <div class="flex items-center justify-between p-6 border-b border-gray-200 bg-red-50">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mr-4">
                    🩸
                </div>
                <div>
                    <h3 class="text-xl font-semibold text-gray-900">Finaliser votre réservation</h3>
                    <p class="text-sm text-gray-600">Remplissez vos informations pour commander</p>
                </div>
            </div>
            <button type="button" onclick="closeOrderReservationModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Contenu du modal -->
        <form id="order-reservation-form" class="p-6 space-y-6" enctype="multipart/form-data">
            @csrf
            
            <!-- Récapitulatif du panier -->
            <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="font-semibold text-gray-900 mb-3 flex items-center">
                    📋 Récapitulatif de votre commande
                </h4>
                <div id="order-reservation-summary" class="space-y-2 mb-4">
                    <!-- Les articles seront insérés ici -->
                </div>
                <div class="border-t pt-3 space-y-2">
                    <div class="flex justify-between text-sm text-gray-600">
                        <span>Prix total :</span>
                        <span id="original-price-display">0 F CFA</span>
                    </div>
                    <div class="flex justify-between text-sm text-blue-600 font-medium">
                        <span>💰 Acompte à payer (50%) :</span>
                        <span id="discount-display">0 F CFA</span>
                    </div>
                    <div class="flex justify-between text-lg font-bold text-red-600 border-t pt-2">
                        <span>À payer maintenant :</span>
                        <span id="final-price-display">0 F CFA</span>
                    </div>
                    <div class="text-xs text-gray-500 mt-2 bg-yellow-50 p-2 rounded">
                        ⚠️ <strong>Important :</strong> Le solde restant (50%) sera à régler lors du retrait dans un délai de 72h maximum.
                    </div>
                </div>
            </div>

            <!-- Informations personnelles -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Numéro d'ordonnance -->
                <div>
                    <label for="prescription_number" class="block text-sm font-medium text-gray-700 mb-2">
                        📄 Numéro d'ordonnance <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="prescription_number" 
                           name="prescription_number"
                           required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                           placeholder="Ex: ORD-2025-001234">
                    <p class="text-xs text-gray-500 mt-1">Numéro inscrit sur votre ordonnance médicale</p>
                </div>

                <!-- Numéro de téléphone -->
                <div>
                    <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-2">
                        📱 Numéro de téléphone <span class="text-red-500">*</span>
                    </label>
                    <input type="tel" 
                           id="phone_number" 
                           name="phone_number"
                           required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                           placeholder="Ex: +228 XX XX XX XX">
                    <p class="text-xs text-gray-500 mt-1">Pour vous contacter lors de la préparation</p>
                </div>
            </div>

            <!-- Upload de l'image d'ordonnance -->
            <div>
                <label for="prescription_images" class="block text-sm font-medium text-gray-700 mb-2">
                    📸 Photos de l'ordonnance <span class="text-red-500">*</span>
                </label>
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-red-400 transition-colors">
                    <input type="file" 
                           id="prescription_images" 
                           name="prescription_images[]"
                           accept="image/*"
                           multiple
                           required
                           class="hidden"
                           onchange="handleMultipleImageUpload(this)">
                    
                    <!-- Zone d'upload -->
                    <div id="upload-area" class="cursor-pointer">
                        <div class="mx-auto w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                            📷
                        </div>
                        <p class="text-sm text-gray-600 mb-2"><strong>Obligatoire :</strong> Ajoutez une ou plusieurs photos de votre ordonnance</p>
                        <p class="text-xs text-gray-500 mb-4">JPG, PNG, WebP (max 5MB par image)</p>
                        
                        <!-- Boutons d'action -->
                        <div class="flex flex-col sm:flex-row gap-3 justify-center items-center">
                            <button type="button" 
                                    onclick="document.getElementById('prescription_images').click()"
                                    class="flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                📁 Choisir fichiers
                            </button>
                            <button type="button" 
                                    onclick="startCamera()"
                                    class="flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                                📸 Prendre photo
                            </button>
                        </div>
                    </div>
                    
                    <!-- Aperçu des images -->
                    <div id="images-preview" class="hidden mt-4">
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4" id="images-grid">
                            <!-- Les images seront insérées ici -->
                        </div>
                        <div class="mt-4 flex justify-center">
                            <button type="button" 
                                    onclick="addMoreImages()"
                                    class="text-blue-600 text-sm hover:underline mr-4">
                                ➕ Ajouter plus d'images
                            </button>
                            <button type="button" 
                                    onclick="clearAllImages()"
                                    class="text-red-600 text-sm hover:underline">
                                🗑️ Supprimer toutes
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Caméra pour prise de photo immédiate -->
                <div id="camera-modal" class="hidden fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-[60]">
                    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
                        <h3 class="text-lg font-semibold mb-4">📸 Prendre une photo</h3>
                        <video id="camera-video" autoplay class="w-full rounded-lg mb-4"></video>
                        <canvas id="camera-canvas" class="hidden"></canvas>
                        <div class="flex justify-between">
                            <button type="button" 
                                    onclick="closeCamera()"
                                    class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                                ❌ Annuler
                            </button>
                            <button type="button" 
                                    onclick="capturePhoto()"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                📸 Capturer
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Moyens de paiement -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-4">
                    💳 Choisissez votre moyen de paiement <span class="text-red-500">*</span>
                </label>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- T-Money -->
                    <div class="relative">
                        <input type="radio" 
                               id="tmoney" 
                               name="payment_method" 
                               value="tmoney"
                               class="hidden peer"
                               required>
                        <label for="tmoney" 
                               class="block p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-red-300 peer-checked:border-red-500 peer-checked:bg-red-50 transition-all">
                            <div class="text-center">
                                <img src="{{ asset('images/tmoney.png') }}" 
                                     alt="T-Money" 
                                     class="w-16 h-16 mx-auto mb-2 object-contain"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                <div class="text-4xl mb-2 hidden">💰</div>
                                <p class="font-medium text-gray-900">T-Money</p>
                                <p class="text-xs text-gray-500">Paiement mobile</p>
                            </div>
                            <div class="absolute top-2 right-2 w-4 h-4 bg-white border-2 border-gray-300 rounded-full peer-checked:border-red-500 peer-checked:bg-red-500"></div>
                        </label>
                    </div>

                    <!-- Flooz -->
                    <div class="relative">
                        <input type="radio" 
                               id="flooz" 
                               name="payment_method" 
                               value="flooz"
                               class="hidden peer"
                               required>
                        <label for="flooz" 
                               class="block p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-red-300 peer-checked:border-red-500 peer-checked:bg-red-50 transition-all">
                            <div class="text-center">
                                <img src="{{ asset('images/flooz.png') }}" 
                                     alt="Flooz" 
                                     class="w-16 h-16 mx-auto mb-2 object-contain"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                <div class="text-4xl mb-2 hidden">📱</div>
                                <p class="font-medium text-gray-900">Flooz</p>
                                <p class="text-xs text-gray-500">Paiement mobile</p>
                            </div>
                            <div class="absolute top-2 right-2 w-4 h-4 bg-white border-2 border-gray-300 rounded-full peer-checked:border-red-500 peer-checked:bg-red-500"></div>
                        </label>
                    </div>

                    <!-- Carte bancaire -->
                    <div class="relative">
                        <input type="radio" 
                               id="carte_bancaire" 
                               name="payment_method" 
                               value="carte_bancaire"
                               class="hidden peer"
                               required>
                        <label for="carte_bancaire" 
                               class="block p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-red-300 peer-checked:border-red-500 peer-checked:bg-red-50 transition-all">
                            <div class="text-center">
                                <img src="{{ asset('images/carte.jpg') }}" 
                                     alt="Carte Bancaire" 
                                     class="w-16 h-16 mx-auto mb-2 object-contain"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                <div class="text-4xl mb-2 hidden">💳</div>
                                <p class="font-medium text-gray-900">Carte Bancaire</p>
                                <p class="text-xs text-gray-500">Visa, MasterCard</p>
                            </div>
                            <div class="absolute top-2 right-2 w-4 h-4 bg-white border-2 border-gray-300 rounded-full peer-checked:border-red-500 peer-checked:bg-red-500"></div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Notes additionnelles -->
            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                    📝 Notes additionnelles (optionnel)
                </label>
                <textarea id="notes" 
                          name="notes"
                          rows="3"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                          placeholder="Informations supplémentaires, allergies, urgence..."></textarea>
            </div>

            <!-- Informations importantes -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h5 class="font-semibold text-blue-900 mb-2 flex items-center">
                    ℹ️ Conditions de réservation et paiement
                </h5>
                <ul class="text-sm text-blue-800 space-y-1">
                    <li>• <strong>Acompte :</strong> Vous payez 50% du montant total maintenant</li>
                    <li>• <strong>Solde :</strong> Les 50% restants seront à régler lors du retrait</li>
                    <li>• <strong>Délai de retrait :</strong> Maximum 72 heures après confirmation</li>
                    <li>• <strong>Préparation :</strong> Votre commande sera prête sous 24h</li>
                    <li>• <strong>Notification :</strong> SMS envoyé quand la commande est prête</li>
                    <li>• <strong>Documents :</strong> Munissez-vous de votre ordonnance originale</li>
                    <li>• <strong>Expiration :</strong> Réservation annulée si non retirée dans les délais</li>
                </ul>
            </div>
        </form>

        <!-- Pied du modal -->
        <div class="flex items-center justify-between p-6 border-t border-gray-200 bg-gray-50">
            <button type="button" 
                    onclick="closeOrderReservationModal()" 
                    class="px-6 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-colors">
                ❌ Annuler
            </button>
            <button type="button" 
                    onclick="submitOrderReservation()" 
                    id="submit-order-reservation-btn"
                    class="px-8 py-2 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 transition-colors">
                🛒 Confirmer ma commande
            </button>
        </div>
    </div>
</div>

<!-- Script pour le modal de réservation élargi -->
<script>
// Variables globales pour le modal de réservation
let orderReservationModalLoading = false;
let selectedImages = [];
let cameraStream = null;

// Afficher un toast de notification
function showToast(message, isError = false) {
    // Supprimer les anciens toasts
    const existingToasts = document.querySelectorAll('.toast-notification');
    existingToasts.forEach(toast => toast.remove());
    
    // Créer le nouveau toast
    const toast = document.createElement('div');
    toast.className = `toast-notification fixed top-4 right-4 z-[9999] px-6 py-3 rounded-lg shadow-lg text-white font-medium transition-all duration-300 transform translate-x-full ${
        isError ? 'bg-red-500' : 'bg-green-500'
    }`;
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    // Animation d'entrée
    setTimeout(() => {
        toast.classList.remove('translate-x-full');
    }, 100);
    
    // Suppression automatique après 4 secondes
    setTimeout(() => {
        toast.classList.add('translate-x-full');
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

// Ouvrir le modal de réservation élargi
window.openOrderReservationModal = function() {
    console.log('Ouverture du modal de réservation élargi...');
    
    // Charger le récapitulatif du panier avec calculs de prix
    loadOrderReservationSummary();
    
    // Afficher le modal
    document.getElementById('order-reservation-modal').classList.remove('hidden');
    
    // Focus sur le premier champ
    setTimeout(() => {
        document.getElementById('prescription_number').focus();
    }, 100);
};

// Fermer le modal de réservation élargi
window.closeOrderReservationModal = function() {
    document.getElementById('order-reservation-modal').classList.add('hidden');
    
    // Réinitialiser le formulaire
    document.getElementById('order-reservation-form').reset();
    clearAllImages();
    closeCamera();
    document.getElementById('order-reservation-summary').innerHTML = '';
    resetPriceDisplays();
};

// Charger le récapitulatif avec calculs de prix
window.loadOrderReservationSummary = function() {
    fetch('/cart', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        const summaryDiv = document.getElementById('order-reservation-summary');
        const originalPriceSpan = document.getElementById('original-price-display');
        const discountSpan = document.getElementById('discount-display');
        const finalPriceSpan = document.getElementById('final-price-display');
        
        if (data.success && data.items && data.items.length > 0) {
            let html = '';
            let originalTotal = 0;
            const unitPrice = 5000; // 5000 F CFA par poche
            
            data.items.forEach(item => {
                const itemTotal = item.quantity * unitPrice;
                originalTotal += itemTotal;
                
                html += `
                    <div class="flex justify-between items-center py-2 border-b border-gray-200">
                        <div class="flex-1">
                            <p class="font-medium text-gray-900">${item.center_name}</p>
                            <p class="text-sm text-gray-600">${item.blood_type} - ${item.quantity} poche(s)</p>
                        </div>
                        <div class="text-right">
                            <p class="font-medium text-gray-900">${itemTotal.toLocaleString('fr-FR')} F CFA</p>
                        </div>
                    </div>
                `;
            });
            
            <!-- Calculs avec acompte de 50% -->
            const totalAmount = originalTotal;
            const acompteAmount = originalTotal * 0.5;
            const soldeRestant = originalTotal - acompteAmount;
            
            summaryDiv.innerHTML = html;
            originalPriceSpan.textContent = totalAmount.toLocaleString('fr-FR') + ' F CFA';
            discountSpan.textContent = acompteAmount.toLocaleString('fr-FR') + ' F CFA';
            finalPriceSpan.textContent = acompteAmount.toLocaleString('fr-FR') + ' F CFA';
        } else {
            summaryDiv.innerHTML = '<p class="text-gray-500 text-center py-4">Aucun article dans le panier</p>';
            resetPriceDisplays();
        }
    })
    .catch(error => {
        console.error('Erreur lors du chargement du récapitulatif:', error);
        document.getElementById('order-reservation-summary').innerHTML = '<p class="text-red-500 text-center py-4">Erreur lors du chargement</p>';
        resetPriceDisplays();
    });
};

// Réinitialiser les affichages de prix
function resetPriceDisplays() {
    document.getElementById('original-price-display').textContent = '0 F CFA';
    document.getElementById('discount-display').textContent = '0 F CFA';
    document.getElementById('final-price-display').textContent = '0 F CFA';
}

// Gérer l'upload de multiples images
window.handleMultipleImageUpload = function(input) {
    const files = Array.from(input.files);
    if (!files.length) return;
    
    files.forEach(file => {
        // Vérifier la taille (5MB max)
        if (file.size > 5 * 1024 * 1024) {
            showToast('❌ Une image ne peut pas dépasser 5MB: ' + file.name, true);
            return;
        }
        
        // Vérifier le type
        if (!file.type.startsWith('image/')) {
            showToast('❌ Fichier non valide: ' + file.name, true);
            return;
        }
        
        // Ajouter à la liste
        selectedImages.push(file);
    });
    
    // Mettre à jour l'affichage
    updateImagesPreview();
    showToast(`✅ ${files.length} image(s) ajoutée(s)`, false);
};

// Mettre à jour l'aperçu des images
function updateImagesPreview() {
    const uploadArea = document.getElementById('upload-area');
    const previewArea = document.getElementById('images-preview');
    const imagesGrid = document.getElementById('images-grid');
    
    if (selectedImages.length > 0) {
        uploadArea.classList.add('hidden');
        previewArea.classList.remove('hidden');
        
        // Vider la grille
        imagesGrid.innerHTML = '';
        
        selectedImages.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const imageDiv = document.createElement('div');
                imageDiv.className = 'relative group';
                imageDiv.innerHTML = `
                    <img src="${e.target.result}" 
                         alt="Ordonnance ${index + 1}" 
                         class="w-full h-32 object-cover rounded-lg border-2 border-gray-200">
                    <button type="button" 
                            onclick="removeImage(${index})"
                            class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                        ×
                    </button>
                    <div class="absolute bottom-2 left-2 bg-black bg-opacity-50 text-white text-xs px-2 py-1 rounded">
                        ${index + 1}
                    </div>
                `;
                imagesGrid.appendChild(imageDiv);
            };
            reader.readAsDataURL(file);
        });
    } else {
        uploadArea.classList.remove('hidden');
        previewArea.classList.add('hidden');
    }
}

// Supprimer une image
window.removeImage = function(index) {
    selectedImages.splice(index, 1);
    updateImagesPreview();
    
    // Mettre à jour l'input file
    updateFileInput();
    showToast('🗑️ Image supprimée', false);
};

// Ajouter plus d'images
window.addMoreImages = function() {
    document.getElementById('prescription_images').click();
};

// Effacer toutes les images
window.clearAllImages = function() {
    selectedImages = [];
    document.getElementById('prescription_images').value = '';
    updateImagesPreview();
    showToast('🗑️ Toutes les images supprimées', false);
};

// Mettre à jour l'input file avec les images sélectionnées
function updateFileInput() {
    const input = document.getElementById('prescription_images');
    const dataTransfer = new DataTransfer();
    
    selectedImages.forEach(file => {
        dataTransfer.items.add(file);
    });
    
    input.files = dataTransfer.files;
}

// Démarrer la caméra
window.startCamera = function() {
    const modal = document.getElementById('camera-modal');
    const video = document.getElementById('camera-video');
    
    navigator.mediaDevices.getUserMedia({ 
        video: { 
            width: { ideal: 1280 },
            height: { ideal: 720 },
            facingMode: 'environment' // Caméra arrière par défaut
        } 
    })
    .then(stream => {
        cameraStream = stream;
        video.srcObject = stream;
        modal.classList.remove('hidden');
    })
    .catch(error => {
        console.error('Erreur caméra:', error);
        showToast('❌ Impossible d\'accéder à la caméra', true);
    });
};

// Fermer la caméra
window.closeCamera = function() {
    const modal = document.getElementById('camera-modal');
    
    if (cameraStream) {
        cameraStream.getTracks().forEach(track => track.stop());
        cameraStream = null;
    }
    
    modal.classList.add('hidden');
};

// Capturer une photo
window.capturePhoto = function() {
    const video = document.getElementById('camera-video');
    const canvas = document.getElementById('camera-canvas');
    const ctx = canvas.getContext('2d');
    
    // Ajuster la taille du canvas
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    
    // Dessiner l'image
    ctx.drawImage(video, 0, 0);
    
    // Convertir en blob
    canvas.toBlob(blob => {
        const timestamp = new Date().getTime();
        const file = new File([blob], `photo_ordonnance_${timestamp}.jpg`, { type: 'image/jpeg' });
        
        selectedImages.push(file);
        updateImagesPreview();
        updateFileInput();
        
        showToast('📸 Photo capturée avec succès!', false);
        closeCamera();
    }, 'image/jpeg', 0.8);
};

// Ancienne fonction maintenue pour compatibilité (vide maintenant)
window.handleImageUpload = function(input) {
    // Fonction remplacée par handleMultipleImageUpload
};

window.clearImageUpload = function() {
    clearAllImages();
};

// Soumettre la commande avec les nouvelles informations
window.submitOrderReservation = function() {
    const form = document.getElementById('order-reservation-form');
    const submitBtn = document.getElementById('submit-order-reservation-btn');
    const prescriptionNumber = document.getElementById('prescription_number').value.trim();
    const phoneNumber = document.getElementById('phone_number').value.trim();
    const paymentMethod = document.querySelector('input[name="payment_method"]:checked');
    
    // Validations
    if (!prescriptionNumber) {
        showToast('❌ Veuillez entrer le numéro d\'ordonnance', true);
        document.getElementById('prescription_number').focus();
        return;
    }
    
    if (!phoneNumber) {
        showToast('❌ Veuillez entrer votre numéro de téléphone', true);
        document.getElementById('phone_number').focus();
        return;
    }
    
    if (selectedImages.length === 0) {
        showToast('❌ Veuillez télécharger au moins une photo de votre ordonnance', true);
        return;
    }
    
    if (!paymentMethod) {
        showToast('❌ Veuillez choisir un moyen de paiement', true);
        return;
    }
    
    if (orderReservationModalLoading) return;
    
    // Désactiver le bouton et afficher le loading
    orderReservationModalLoading = true;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '⏳ Traitement en cours...';
    
    // Préparer les données avec FormData pour l'upload
    const formData = new FormData(form);
    
    // Retirer l'ancien champ prescription_image et ajouter les nouvelles images
    formData.delete('prescription_image');
    selectedImages.forEach((file, index) => {
        formData.append('prescription_images[]', file);
    });
    
    fetch('/order', {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: formData
    })
    .then(response => {
        console.log('Réponse commande:', response.status);
        console.log('Headers:', response.headers);
        
        // Essayer de lire le texte de la réponse même si elle n'est pas OK
        return response.text().then(text => {
            console.log('Contenu de la réponse:', text);
            
            if (!response.ok) {
                // Essayer de parser en JSON pour voir si c'est une erreur Laravel
                try {
                    const errorData = JSON.parse(text);
                    console.log('Erreur parsée:', errorData);
                    throw new Error(errorData.message || `HTTP Error: ${response.status}`);
                } catch (parseError) {
                    console.log('Erreur de parsing JSON:', parseError);
                    throw new Error(`HTTP Error ${response.status}: ${text.substring(0, 200)}`);
                }
            }
            
            try {
                return JSON.parse(text);
            } catch (parseError) {
                console.log('Erreur parsing JSON succès:', parseError);
                throw new Error('Réponse invalide du serveur');
            }
        });
    })
    .then(responseData => {
        console.log('Données de la commande:', responseData);
        
        if (responseData.success) {
            // Succès
            showToast(`✅ ${responseData.message} - Acompte payé: ${responseData.formatted_total}`, false);
            
            // Fermer le modal
            closeOrderReservationModal();
            
            // Mettre à jour l'affichage
            if (typeof updateButtonStatesAfterCartChange === 'function') {
                updateButtonStatesAfterCartChange();
            }
            
            // Afficher un message de confirmation détaillé
            setTimeout(() => {
                alert(`🎉 Réservation confirmée !\n\n` +
                      `💰 Acompte payé : ${responseData.formatted_total}\n` +
                      `⏰ Délai de retrait : 72h maximum\n` +
                      `📱 Vous recevrez un SMS de confirmation\n\n` +
                      `Redirection vers vos commandes...`);
                window.location.href = '/dashboard/client';
            }, 1000);
            
        } else {
            showToast(`❌ ${responseData.message}`, true);
        }
    })
    .catch(error => {
        console.error('Erreur lors de la commande:', error);
        showToast('❌ Erreur lors de la commande. Veuillez réessayer.', true);
    })
    .finally(() => {
        // Réactiver le bouton
        orderReservationModalLoading = false;
        submitBtn.disabled = false;
        submitBtn.innerHTML = '🛒 Confirmer ma commande';
    });
};

// Gestionnaire pour fermer le modal avec Échap
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('order-reservation-modal');
        if (modal && !modal.classList.contains('hidden')) {
            closeOrderReservationModal();
        }
    }
});
</script>

<style>
/* Styles pour les images des moyens de paiement */
.payment-method-card {
    transition: all 0.3s ease;
}

.payment-method-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* Animation pour la sélection */
input[type="radio"]:checked + label .payment-method-card {
    animation: pulse 0.5s ease-in-out;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

/* Style pour l'upload d'image */
#image-preview img {
    border-radius: 8px;
    border: 2px solid #e5e7eb;
}

/* Style pour les radio buttons personnalisés */
input[type="radio"]:checked + label::after {
    content: '✓';
    position: absolute;
    top: 8px;
    right: 8px;
    color: white;
    font-size: 12px;
    font-weight: bold;
}
</style>
