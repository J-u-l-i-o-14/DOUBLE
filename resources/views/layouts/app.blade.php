<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Blood Bank') }} - @yield('title', 'Gestion de Stock de Sang')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 0.75rem 1rem;
            border-radius: 0.375rem;
            margin: 0.25rem 0;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255,255,255,0.1);
        }
        .blood-type-badge {
            font-weight: bold;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
        }
        .blood-A { background-color: #e3f2fd; color: #1976d2; }
        .blood-B { background-color: #f3e5f5; color: #7b1fa2; }
        .blood-AB { background-color: #e8f5e8; color: #388e3c; }
        .blood-O { background-color: #fff3e0; color: #f57c00; }
        .alert-low-stock { background-color: #ffebee; border-color: #f44336; color: #c62828; }
        
        /* Animation pour la cloche */
        .fa-shake {
            animation: shake 0.5s ease-in-out;
        }
        
        /* Animation pulse pour les notifications */
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        .notification-badge {
            transition: all 0.3s ease;
        }
        
        @keyframes shake {
            0%, 100% { transform: rotate(0deg); }
            25% { transform: rotate(-10deg); }
            75% { transform: rotate(10deg); }
        }
        
        /* Style pour les alertes dans le modal */
        .list-group-item:hover {
            background-color: #f8f9fa;
        }
        
        .modal-body .list-group-item {
            border-left: 4px solid transparent;
        }
        
        .modal-body .list-group-item:has(.badge.bg-danger) {
            border-left-color: #dc3545;
        }
        
        .modal-body .list-group-item:has(.badge.bg-warning) {
            border-left-color: #ffc107;
        }
    </style>

    @stack('styles')
</head>
<body class="bg-light">
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h4 class="text-white">
                            <i class="fas fa-tint me-2"></i>
                            Blood Bank
                        </h4>
                        <small class="text-white-50">{{ auth()->user()->name }}</small>
                        <br>
                        <span class="badge bg-light text-dark">{{ ucfirst(auth()->user()->role) }}</span>
                    </div>

                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                                <i class="fas fa-tachometer-alt me-2"></i>
                                Tableau de bord
                            </a>
                        </li>

                        @if(auth()->user()->is_donor)
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('appointments.*') ? 'active' : '' }}" href="{{ route('appointments.index') }}">
                                    <i class="fas fa-calendar-alt me-2"></i>
                                    Mes Rendez-vous
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('campaigns.public') ? 'active' : '' }}" href="{{ route('campaigns.public') }}">
                                    <i class="fas fa-bullhorn me-2"></i>
                                    Campagnes
                                </a>
                            </li>
                        @endif

                        @if(auth()->user()->is_manager || auth()->user()->is_admin)
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('patients.*') ? 'active' : '' }}" href="{{ route('patients.index') }}">
                                    <i class="fas fa-user-injured me-2"></i>
                                    Patients
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('transfusions.*') ? 'active' : '' }}" href="{{ route('transfusions.index') }}">
                                    <i class="fas fa-syringe me-2"></i>
                                    Transfusions
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('reservations.*') ? 'active' : '' }}" href="{{ route('reservations.index') }}">
                                    <i class="fas fa-clipboard-list me-2"></i>
                                    Réservations
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('blood-bags.stock') ? 'active' : '' }}" href="{{ route('blood-bags.stock') }}">
                                    <i class="fas fa-chart-bar me-2"></i>
                                    Stock de Sang
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('campaigns.*') ? 'active' : '' }}" href="{{ route('campaigns.index') }}">
                                    <i class="fas fa-bullhorn me-2"></i>
                                    Campagnes
                                </a>
                            </li>
                        @endif

                        @if(auth()->user()->is_admin)
                            <hr class="text-white-50">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                                    <i class="fas fa-users me-2"></i>
                                    Utilisateurs
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('blood-bags.*') ? 'active' : '' }}" href="{{ route('blood-bags.index') }}">
                                    <i class="fas fa-tint me-2"></i>
                                    Poches de Sang
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('donations.*') ? 'active' : '' }}" href="{{ route('donations.index') }}">
                                    <i class="fas fa-heart me-2"></i>
                                    Dons
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('campaigns.*') ? 'active' : '' }}" href="{{ route('campaigns.index') }}">
                                    <i class="fas fa-bullhorn me-2"></i>
                                    Campagnes
                                </a>
                            </li>
                        @endif

                        <hr class="text-white-50">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('profile.edit') }}">
                                <i class="fas fa-user-cog me-2"></i>
                                Profil
                            </a>
                        </li>
                        <li class="nav-item">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="nav-link border-0 bg-transparent w-100 text-start">
                                    <i class="fas fa-sign-out-alt me-2"></i>
                                    Déconnexion
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">@yield('page-title', 'Tableau de bord')</h1>
                    <div class="d-flex align-items-center">
                        @yield('page-actions')
                        
                        <!-- Bouton Dashboard pour tous les utilisateurs -->
                        @if(!request()->routeIs('dashboard'))
                            <a href="{{ route('dashboard') }}" class="btn btn-outline-primary me-3">
                                <i class="fas fa-home me-1"></i>
                                {{ auth()->user()->role === 'client' ? 'Accueil' : 'Dashboard' }}
                            </a>
                        @endif
                        
                        @if(auth()->user()->is_admin || auth()->user()->is_manager)
                            <!-- Cloche de notifications -->
                            <div class="position-relative me-3">
                                @php
                                    $activeAlertsCount = \App\Models\Alert::where('center_id', auth()->user()->center_id)->where('resolved', false)->count();
                                    $activeAlerts = \App\Models\Alert::with('bloodType')->where('center_id', auth()->user()->center_id)->where('resolved', false)->orderBy('created_at', 'desc')->limit(5)->get();
                                    
                                    // Ajouter les notifications de nouvelles commandes non lues
                                    $unreadNotificationsCount = \App\Models\Notification::where('user_id', auth()->id())->whereNull('read_at')->count();
                                    $unreadNotifications = \App\Models\Notification::where('user_id', auth()->id())->whereNull('read_at')->orderBy('created_at', 'desc')->limit(5)->get();
                                    
                                    $totalNotifications = $activeAlertsCount + $unreadNotificationsCount;
                                @endphp
                                <button type="button" class="btn btn-outline-secondary position-relative" data-bs-toggle="modal" data-bs-target="#alertsModal">
                                    <i class="fas fa-bell"></i>
                                    @if($totalNotifications > 0)
                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger notification-badge">
                                            {{ $totalNotifications > 99 ? '99+' : $totalNotifications }}
                                            <span class="visually-hidden">notifications non lues</span>
                                        </span>
                                    @endif
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Alerts -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @if(auth()->user()->is_admin || auth()->user()->is_manager)
        <!-- Modal des Alertes -->
        <div class="modal fade" id="alertsModal" tabindex="-1" aria-labelledby="alertsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="alertsModalLabel">
                            <i class="fas fa-bell me-2"></i>
                            Notifications & Alertes
                            @if($totalNotifications > 0)
                                <span class="badge bg-light text-danger ms-2">{{ $totalNotifications }}</span>
                            @endif
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        @if($totalNotifications > 0)
                            <div class="alert alert-info d-flex align-items-center mb-3">
                                <i class="fas fa-info-circle me-2"></i>
                                <div>
                                    <strong>{{ $totalNotifications }}</strong> notification(s) nécessitent votre attention.
                                </div>
                            </div>
                            
                            <!-- Onglets pour séparer les notifications et alertes -->
                            <ul class="nav nav-tabs mb-3" id="notificationTabs" role="tablist">
                                @if($unreadNotificationsCount > 0)
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="notifications-tab" data-bs-toggle="tab" data-bs-target="#notifications" type="button" role="tab" aria-controls="notifications" aria-selected="true">
                                        <i class="fas fa-shopping-cart me-1"></i>
                                        Nouvelles Commandes ({{ $unreadNotificationsCount }})
                                    </button>
                                </li>
                                @endif
                                @if($activeAlertsCount > 0)
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link {{ $unreadNotificationsCount == 0 ? 'active' : '' }}" id="alerts-tab" data-bs-toggle="tab" data-bs-target="#alerts" type="button" role="tab" aria-controls="alerts" aria-selected="{{ $unreadNotificationsCount == 0 ? 'true' : 'false' }}">
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        Alertes Stock ({{ $activeAlertsCount }})
                                    </button>
                                </li>
                                @endif
                            </ul>
                            
                            <div class="tab-content" id="notificationTabContent">
                                @if($unreadNotificationsCount > 0)
                                <div class="tab-pane fade show active" id="notifications" role="tabpanel" aria-labelledby="notifications-tab">
                                    <div class="list-group">
                                        @foreach($unreadNotifications as $notification)
                                            <div class="list-group-item list-group-item-action">
                                                <div class="d-flex w-100 justify-content-between align-items-start">
                                                    <div class="flex-grow-1">
                                                        <div class="d-flex align-items-center mb-2">
                                                            <span class="badge bg-primary me-2">
                                                                <i class="fas fa-shopping-cart me-1"></i> Nouvelle Commande
                                                            </span>
                                                        </div>
                                                        <h6 class="mb-1">{{ $notification->title }}</h6>
                                                        <p class="mb-1">{{ $notification->message }}</p>
                                                        <small class="text-muted">
                                                            <i class="fas fa-calendar me-1"></i>
                                                            {{ $notification->created_at->format('d/m/Y à H:i') }}
                                                        </small>
                                                    </div>
                                                    <div class="ms-2">
                                                        @php
                                                            $data = json_decode($notification->data, true);
                                                            $orderId = $data['order_id'] ?? 0;
                                                        @endphp
                                                        <button class="btn btn-sm btn-outline-primary me-1" onclick="viewOrder({{ $orderId }})">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-success" onclick="markNotificationAsRead({{ $notification->id }})">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                                
                                @if($activeAlertsCount > 0)
                                <div class="tab-pane fade {{ $unreadNotificationsCount == 0 ? 'show active' : '' }}" id="alerts" role="tabpanel" aria-labelledby="alerts-tab">
                                    <div class="list-group">
                                        @foreach($activeAlerts as $alert)
                                            <div class="list-group-item list-group-item-action">
                                                <div class="d-flex w-100 justify-content-between align-items-start">
                                                    <div class="flex-grow-1">
                                                        <div class="d-flex align-items-center mb-2">
                                                            @if($alert->type === 'low_stock')
                                                                <span class="badge bg-danger me-2">
                                                                    <i class="fas fa-tint me-1"></i> Stock Faible
                                                                </span>
                                                            @elseif($alert->type === 'expiration')
                                                                <span class="badge bg-warning me-2">
                                                                    <i class="fas fa-clock me-1"></i> Expiration
                                                                </span>
                                                            @endif
                                                            <span class="badge bg-primary">{{ $alert->bloodType->group }}</span>
                                                        </div>
                                                        <p class="mb-1">{{ $alert->message }}</p>
                                                        <small class="text-muted">
                                                            <i class="fas fa-calendar me-1"></i>
                                                            {{ $alert->created_at->format('d/m/Y à H:i') }}
                                                        </small>
                                                    </div>
                                                    <div class="ms-2">
                                                        <button class="btn btn-sm btn-success" onclick="resolveAlertModal({{ $alert->id }})">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    
                                    @if($activeAlertsCount > 5)
                                        <div class="text-center mt-3">
                                            <p class="text-muted">Et {{ $activeAlertsCount - 5 }} autres alertes...</p>
                                        </div>
                                    @endif
                                </div>
                                @endif
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                                <h5 class="mt-3 text-success">Aucune alerte active</h5>
                                <p class="text-muted">Excellent ! Votre centre n'a aucune alerte critique en cours.</p>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <div class="d-flex w-100 justify-content-between">
                            <div>
                                @if($activeAlertsCount > 0)
                                    <button type="button" class="btn btn-outline-success" onclick="refreshAlerts()">
                                        <i class="fas fa-sync-alt me-1"></i>
                                        Actualiser
                                    </button>
                                @endif
                            </div>
                            <div>
                                <a href="{{ route('alerts.index') }}" class="btn btn-primary">
                                    <i class="fas fa-cog me-1"></i>
                                    Gérer toutes les alertes
                                </a>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    @if(auth()->user()->is_admin || auth()->user()->is_manager)
        <script>
            function resolveAlertModal(alertId) {
                if (confirm('Marquer cette alerte comme résolue ?')) {
                    fetch(`/alerts/${alertId}/resolve`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json',
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Fermer le modal et recharger la page pour mettre à jour les compteurs
                            bootstrap.Modal.getInstance(document.getElementById('alertsModal')).hide();
                            setTimeout(() => {
                                location.reload();
                            }, 300);
                        } else {
                            alert('Erreur: ' + (data.message || 'Une erreur est survenue'));
                        }
                    })
                    .catch(error => {
                        console.error('Erreur:', error);
                        alert('Erreur de communication avec le serveur');
                    });
                }
            }

            // Fonction pour marquer une notification comme lue
            function markNotificationAsRead(notificationId) {
                fetch(`/notifications/${notificationId}/read`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Recharger la page pour mettre à jour les compteurs
                        location.reload();
                    } else {
                        alert('Erreur: ' + (data.message || 'Une erreur est survenue'));
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('Erreur de communication avec le serveur');
                });
            }

            // Fonction pour voir une commande
            function viewOrder(orderId) {
                if (orderId > 0) {
                    window.open(`/orders/${orderId}`, '_blank');
                }
            }

            function refreshAlerts() {
                // Générer les alertes puis recharger
                fetch('/alerts/generate', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        bootstrap.Modal.getInstance(document.getElementById('alertsModal')).hide();
                        setTimeout(() => {
                            location.reload();
                        }, 300);
                    } else {
                        alert('Erreur: ' + (data.message || 'Une erreur est survenue'));
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('Erreur de communication avec le serveur');
                });
            }

            // Animation de la cloche quand il y a des alertes
            @if($activeAlertsCount > 0)
                setInterval(function() {
                    const bell = document.querySelector('.fa-bell');
                    if (bell) {
                        bell.classList.add('fa-shake');
                        setTimeout(() => {
                            bell.classList.remove('fa-shake');
                        }, 1000);
                    }
                }, 10000); // Animation toutes les 10 secondes
            @endif

            // Polling automatique des notifications (toutes les 30 secondes)
            @if(auth()->user()->is_admin || auth()->user()->is_manager)
                setInterval(function() {
                    fetch('/api/notifications-count', {
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.count > parseInt(document.querySelector('.notification-badge')?.textContent || '0')) {
                            // Nouvelle notification détectée
                            const badge = document.querySelector('.notification-badge');
                            if (badge) {
                                badge.textContent = data.count;
                                badge.style.animation = 'pulse 1s infinite';
                                setTimeout(() => {
                                    badge.style.animation = '';
                                }, 3000);
                            }
                            
                            // Son de notification (optionnel)
                            if (data.hasNew) {
                                const audio = new Audio('/sounds/notification.mp3');
                                audio.volume = 0.3;
                                audio.play().catch(() => {}); // Ignore les erreurs de lecture
                            }
                        }
                    })
                    .catch(error => {
                        console.log('Erreur polling notifications:', error);
                    });
                }, 30000); // Vérifier toutes les 30 secondes
            @endif
        </script>
    @endif
    
    @stack('scripts')
</body>
</html>