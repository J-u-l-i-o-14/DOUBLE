<!-- Modal du panier -->
<div id="cart-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center" onclick="closeCartModal()">
    <!-- Panel du modal -->
    <div class="bg-white rounded-lg shadow-xl w-full max-w-lg mx-4 max-h-96 overflow-y-auto" onclick="event.stopPropagation()">
        <!-- En-tête -->
        <div class="flex justify-between items-center p-6 border-b border-gray-200">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Mon Panier</h3>
            <button type="button" onclick="closeCartModal()" class="close-modal text-gray-400 hover:text-gray-500">
                <span class="sr-only">Fermer</span>
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        
        <!-- Corps du modal -->
        <div class="p-6">
            <!-- Bouton vider le panier -->
            <div class="mb-4 text-right">
                <button type="button" 
                        onclick="clearCart()"
                        class="text-red-600 hover:text-red-800 text-sm font-medium">
                    Vider le panier
                </button>
            </div>

            <!-- Liste des articles -->
            <div id="cart-items" class="space-y-4">
                <!-- Les articles seront insérés ici dynamiquement -->
            </div>

            <!-- Message panier vide -->
            <div id="empty-cart-message" class="hidden py-4 text-center text-gray-500">
                Votre panier est vide
            </div>

            <!-- Total et bouton payer -->
            <div id="cart-footer" class="mt-6 border-t pt-4">
                <div class="flex justify-between items-center mb-4">
                    <span class="font-semibold text-lg">Total:</span>
                    <span id="cart-total" class="font-bold text-xl text-red-600">0 poches</span>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" 
                            onclick="closeCartModal()"
                            class="bg-gray-300 hover:bg-gray-400 text-gray-700 font-bold py-2 px-4 rounded">
                        Fermer
                    </button>
                    <button type="button" 
                            onclick="processPayment()"
                            class="btn-red">
                        Payer
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
