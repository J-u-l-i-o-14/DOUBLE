# Configuration pour WebSocket avec Laravel Echo + Pusher

## 1. Installation des dépendances
composer require pusher/pusher-php-server
npm install --save-dev laravel-echo pusher-js

## 2. Configuration .env
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=mt1

## 3. Créer un Event pour les notifications
php artisan make:event NewReservationCreated

## 4. Exemple d'Event (app/Events/NewReservationCreated.php)
<?php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\ReservationRequest;

class NewReservationCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $reservation;

    public function __construct(ReservationRequest $reservation)
    {
        $this->reservation = $reservation;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('center.' . $this->reservation->center_id);
    }

    public function broadcastWith()
    {
        return [
            'reservation_id' => $this->reservation->id,
            'client_name' => $this->reservation->order->user->name,
            'message' => 'Nouvelle réservation #' . $this->reservation->id,
            'timestamp' => now()->toISOString()
        ];
    }
}

## 5. JavaScript côté client (resources/js/app.js)
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: process.env.MIX_PUSHER_APP_KEY,
    cluster: process.env.MIX_PUSHER_APP_CLUSTER,
    forceTLS: true
});

// Écouter les nouvelles réservations pour un centre
if (window.userCenterId) {
    window.Echo.private(`center.${window.userCenterId}`)
        .listen('NewReservationCreated', (e) => {
            // Mettre à jour l'interface
            updateNotificationBell();
            showToast('Nouvelle réservation: ' + e.message);
        });
}

function updateNotificationBell() {
    // Recharger le compteur de notifications
    fetch('/api/notifications-count')
        .then(response => response.json())
        .then(data => {
            document.querySelector('.notification-badge').textContent = data.count;
        });
}

## 6. Déclencher l'event lors de la création (OrderController)
use App\Events\NewReservationCreated;

// Dans la méthode store après création de la commande
event(new NewReservationCreated($reservationRequest));
