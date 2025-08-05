
 <section>
     <script>
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

</section>
