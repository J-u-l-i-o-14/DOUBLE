# Tests du Sprint 2 - Modal du panier et commande

## Tests fonctionnels à effectuer manuellement

### 1. Test du bouton "Commander"
- [ ] Rechercher du sang avec des critères valides
- [ ] Vérifier que le bouton "Commander" apparaît en bas du tableau de résultats
- [ ] Cliquer sur le bouton "Commander"
- [ ] Vérifier que le modal s'ouvre

### 2. Test du modal du panier
- [ ] Vérifier que le modal s'affiche correctement
- [ ] Vérifier la présence du titre "Mon Panier"
- [ ] Vérifier la présence du bouton "Fermer" (X)
- [ ] Vérifier la présence du bouton "Vider le panier"
- [ ] Vérifier la présence du bouton "Payer"

### 3. Test d'ajout d'articles au panier
- [ ] Ajouter des articles au panier depuis les résultats de recherche
- [ ] Ouvrir le modal du panier
- [ ] Vérifier que les articles ajoutés s'affichent dans le modal
- [ ] Vérifier que le total des quantités est correct

### 4. Test de suppression d'un article
- [ ] Dans le modal, cliquer sur le bouton supprimer d'un article
- [ ] Vérifier que l'article est retiré de la liste
- [ ] Vérifier que le total est mis à jour

### 5. Test de vidage du panier
- [ ] Cliquer sur "Vider le panier"
- [ ] Confirmer dans la boîte de dialogue
- [ ] Vérifier que tous les articles sont supprimés
- [ ] Vérifier l'affichage du message "Votre panier est vide"

### 6. Test du bouton "Payer"
- [ ] Ajouter des articles au panier
- [ ] Cliquer sur le bouton "Payer"
- [ ] Vérifier l'affichage du message de succès
- [ ] Vérifier que le panier est vidé après paiement
- [ ] Vérifier que le modal se ferme

### 7. Tests d'erreur
- [ ] Tester le paiement avec un panier vide
- [ ] Vérifier les messages d'erreur appropriés
- [ ] Tester sans être connecté (si applicable)

## Vérifications techniques

### Routes disponibles
- GET /cart (cart.index) ✅
- POST /cart/add (cart.add) ✅  
- DELETE /cart/{id} (cart.remove) ✅
- DELETE /cart (cart.clear) ✅
- POST /cart/payment (cart.payment) ✅

### Fichiers modifiés
- ✅ CartController.php - Toutes les méthodes ajoutées
- ✅ routes/web.php - Routes du panier configurées
- ✅ blood-reservation.blade.php - Modal et JavaScript ajoutés
- ✅ partials/_cart-modal.blade.php - Template du modal créé

## Statut : Prêt pour les tests
Toutes les fonctionnalités du Sprint 2 ont été implémentées et sont prêtes à être testées.
