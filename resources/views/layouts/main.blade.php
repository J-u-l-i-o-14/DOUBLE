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

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .blood-type-A { @apply bg-blue-100 text-blue-800; }
        .blood-type-B { @apply bg-purple-100 text-purple-800; }
        .blood-type-AB { @apply bg-green-100 text-green-800; }
        .blood-type-O { @apply bg-orange-100 text-orange-800; }
        
        .sidebar-gradient {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        }
    </style>

    @stack('styles')
</head>
<body class="bg-gray-50 font-sans antialiased">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <nav class="w-64 sidebar-gradient shadow-lg">
            <div class="flex flex-col h-full">
                <!-- Logo et utilisateur -->
                <div class="p-6 text-center border-b border-red-500">
                    <div class="flex items-center justify-center mb-4">
                        <i class="fas fa-tint text-white text-2xl mr-2"></i>
                        <h1 class="text-xl font-bold text-white">Blood Bank</h1>
                    </div>
                    <div class="text-white">
                        <p class="text-sm opacity-90">{{ auth()->user()->name }}</p>
                        <span class="inline-block px-2 py-1 mt-1 text-xs bg-white bg-opacity-20 rounded-full">
                            {{ ucfirst(auth()->user()->role) }}
                        </span>
                    </div>
                </div>

                <!-- Navigation -->
                <div class="flex-1 px-4 py-6 overflow-y-auto">
                    <ul class="space-y-2">
                        <!-- Dashboard -->
                        <li>
                            <a href="{{ auth()->user()->role === 'client' ? route('dashboard.client') : route('dashboard') }}" 
                               class="flex items-center px-4 py-3 text-white rounded-lg transition-colors duration-200 {{ request()->routeIs('dashboard*') ? 'bg-white bg-opacity-20' : 'hover:bg-white hover:bg-opacity-10' }}">
                                <i class="fas fa-tachometer-alt mr-3"></i>
                                <span>Tableau de bord</span>
                            </a>
                        </li>

                        @if(auth()->user()->is_client)
                            <!-- Menu client -->
                            <li class="pt-4">
                                <p class="px-4 text-xs font-semibold text-white opacity-60 uppercase tracking-wider">Donneur</p>
                            </li>
                            <li>
                                <a href="{{ route('appointments.index') }}" 
                                   class="flex items-center px-4 py-3 text-white rounded-lg transition-colors duration-200 {{ request()->routeIs('appointments.*') ? 'bg-white bg-opacity-20' : 'hover:bg-white hover:bg-opacity-10' }}">
                                    <i class="fas fa-calendar-alt mr-3"></i>
                                    <span>Mes Rendez-vous</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('campaigns.public') }}" 
                                   class="flex items-center px-4 py-3 text-white rounded-lg transition-colors duration-200 {{ request()->routeIs('campaigns.public') ? 'bg-white bg-opacity-20' : 'hover:bg-white hover:bg-opacity-10' }}">
                                    <i class="fas fa-bullhorn mr-3"></i>
                                    <span>Campagnes</span>
                                </a>
                            </li>
                        @endif

                        @if(auth()->user()->is_manager || auth()->user()->is_admin)
                            <!-- Menu Manager -->
                            <li class="pt-4">
                                <p class="px-4 text-xs font-semibold text-white opacity-60 uppercase tracking-wider">Médical</p>
                            </li>
                            <li>
                                <a href="{{ route('patients.index') }}" 
                                   class="flex items-center px-4 py-3 text-white rounded-lg transition-colors duration-200 {{ request()->routeIs('patients.*') ? 'bg-white bg-opacity-20' : 'hover:bg-white hover:bg-opacity-10' }}">
                                    <i class="fas fa-user-injured mr-3"></i>
                                    <span>Patients</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('transfusions.index') }}" 
                                   class="flex items-center px-4 py-3 text-white rounded-lg transition-colors duration-200 {{ request()->routeIs('transfusions.*') ? 'bg-white bg-opacity-20' : 'hover:bg-white hover:bg-opacity-10' }}">
                                    <i class="fas fa-syringe mr-3"></i>
                                    <span>Transfusions</span>
                                </a>
                            </li>
                            @php
                                $alertCount = 0;
                                if(auth()->check() && (auth()->user()->is_admin || auth()->user()->is_manager)) {
                                    $alertCount = \App\Models\Alert::where('center_id', auth()->user()->center_id ?? null)
                                        ->where('resolved', false)
                                        ->count();
                                }
                            @endphp
                            <li>
                                <a href="{{ route('blood-bags.stock') }}" 
                                   class="flex items-center px-4 py-3 text-white rounded-lg transition-colors duration-200 {{ request()->routeIs('blood-bags.stock') ? 'bg-white bg-opacity-20' : 'hover:bg-white hover:bg-opacity-10' }}">
                                    <i class="fas fa-chart-bar mr-3"></i>
                                    <span>Stock de Sang</span>
                                    @if($alertCount > 0)
                                        <span class="ml-2 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-red-100 bg-red-600 rounded-full">{{ $alertCount }}</span>
                                    @endif
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('campaigns.index') }}" 
                                   class="flex items-center px-4 py-3 text-white rounded-lg transition-colors duration-200 {{ request()->routeIs('campaigns.*') ? 'bg-white bg-opacity-20' : 'hover:bg-white hover:bg-opacity-10' }}">
                                    <i class="fas fa-bullhorn mr-3"></i>
                                    <span>Campagnes</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('donations.index') }}" 
                                   class="flex items-center px-4 py-3 text-white rounded-lg transition-colors duration-200 {{ request()->routeIs('donations.*') ? 'bg-white bg-opacity-20' : 'hover:bg-white hover:bg-opacity-10' }}">
                                    <i class="fas fa-heart mr-3"></i>
                                    <span>Dons</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('blood-bags.index') }}" 
                                   class="flex items-center px-4 py-3 text-white rounded-lg transition-colors duration-200 {{ request()->routeIs('blood-bags.*') ? 'bg-white bg-opacity-20' : 'hover:bg-white hover:bg-opacity-10' }}">
                                    <i class="fas fa-tint mr-3"></i>
                                    <span>Poches de Sang</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('reservations.index') }}" 
                                   class="flex items-center px-4 py-3 text-white rounded-lg transition-colors duration-200 {{ request()->routeIs('reservations.*') ? 'bg-white bg-opacity-20' : 'hover:bg-white hover:bg-opacity-10' }}">
                                    <i class="fas fa-clipboard-list mr-3"></i>
                                    <span>Réservations</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('alerts.index', ['layout' => 'main']) }}" 
                                   class="flex items-center px-4 py-3 text-white rounded-lg transition-colors duration-200 {{ request()->routeIs('alerts.*') ? 'bg-white bg-opacity-20' : 'hover:bg-white hover:bg-opacity-10' }}">
                                    <i class="fas fa-exclamation-triangle mr-3"></i>
                                    <span>Gestion des Alertes</span>
                                    @if($alertCount > 0)
                                        <span class="ml-2 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-red-100 bg-red-600 rounded-full animate-pulse">{{ $alertCount }}</span>
                                    @endif
                                </a>
                            </li>
                        @endif

                        @if(auth()->user()->is_admin)
                            <!-- Menu Admin -->
                            <li class="pt-4">
                                <p class="px-4 text-xs font-semibold text-white opacity-60 uppercase tracking-wider">Administration</p>
                            </li>
                            <li>
                                <a href="{{ route('users.index') }}" 
                                   class="flex items-center px-4 py-3 text-white rounded-lg transition-colors duration-200 {{ request()->routeIs('users.*') ? 'bg-white bg-opacity-20' : 'hover:bg-white hover:bg-opacity-10' }}">
                                    <i class="fas fa-users mr-3"></i>
                                    <span>Utilisateurs</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('centers.index') }}" 
                                   class="flex items-center px-4 py-3 text-white rounded-lg transition-colors duration-200 {{ request()->routeIs('centers.*') ? 'bg-white bg-opacity-20' : 'hover:bg-white hover:bg-opacity-10' }}">
                                    <i class="fas fa-hospital mr-3"></i>
                                    <span>Centres</span>
                                </a>
                            </li>
                        @endif
                    </ul>
                </div>

                <!-- Menu utilisateur -->
                <div class="p-4 border-t border-red-500">
                    <ul class="space-y-2">
                        <li>
                            <a href="{{ route('profile.edit') }}" 
                               class="flex items-center px-4 py-3 text-white rounded-lg transition-colors duration-200 hover:bg-white hover:bg-opacity-10">
                                <i class="fas fa-user-cog mr-3"></i>
                                <span>Profil</span>
                            </a>
                        </li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" 
                                        class="flex items-center w-full px-4 py-3 text-white rounded-lg transition-colors duration-200 hover:bg-white hover:bg-opacity-10">
                                    <i class="fas fa-sign-out-alt mr-3"></i>
                                    <span>Déconnexion</span>
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Header -->
            <header class="bg-white shadow-sm border-b border-gray-200">
                <div class="px-6 py-4">
                    <div class="flex items-center justify-between">
                        <h1 class="text-2xl font-semibold text-gray-900">
                            @yield('page-title', 'Tableau de bord')
                        </h1>
                        <div class="flex items-center space-x-4">
                            @yield('page-actions')
                            
                                                        <!-- Bouton Dashboard pour tous les utilisateurs -->
                            @if(!request()->routeIs('dashboard*'))
                                <a href="{{ auth()->user()->role === 'client' ? route('dashboard.client') : route('dashboard') }}" 
                                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                                    <i class="fas fa-tachometer-alt mr-2"></i>
                                    {{ auth()->user()->role === 'client' ? 'Mon Dashboard' : 'Dashboard' }}
                                </a>
                            @endif
                            
                            @if(auth()->user()->is_admin || auth()->user()->is_manager)
                                <!-- Cloche de notifications améliorée -->
                                @php
                                    $activeAlertsCount = \App\Models\Alert::where('center_id', auth()->user()->center_id)->where('resolved', false)->count();
                                    $activeAlerts = \App\Models\Alert::with('bloodType')->where('center_id', auth()->user()->center_id)->where('resolved', false)->orderBy('created_at', 'desc')->limit(5)->get();
                                    
                                    // Ajouter les notifications de nouvelles commandes non lues
                                    $unreadNotificationsCount = \App\Models\Notification::where('user_id', auth()->id())->whereNull('read_at')->count();
                                    $unreadNotifications = \App\Models\Notification::where('user_id', auth()->id())->whereNull('read_at')->orderBy('created_at', 'desc')->limit(5)->get();
                                    
                                    // Compter les réservations pending pour le centre
                                    $pendingReservationsCount = \App\Models\ReservationRequest::where('center_id', auth()->user()->center_id)->where('status', 'pending')->count();
                                    $pendingReservations = \App\Models\ReservationRequest::with('order.user')->where('center_id', auth()->user()->center_id)->where('status', 'pending')->orderBy('created_at', 'desc')->limit(5)->get();
                                    // Nouveau total: uniquement alertes + notifications non lues
                                    $totalNotifications = $activeAlertsCount + $unreadNotificationsCount; // retiré $pendingReservationsCount
                                @endphp
                                
                                <!-- Bouton direct vers la gestion des alertes -->
                                @if($activeAlertsCount > 0)
                                    <a href="{{ route('alerts.index', ['layout' => 'main']) }}" 
                                       class="inline-flex items-center px-3 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors duration-200 mr-3"
                                       title="Gérer les {{ $activeAlertsCount }} alertes actives">
                                        <i class="fas fa-exclamation-triangle mr-2"></i>
                                        {{ $activeAlertsCount }} Alerte{{ $activeAlertsCount > 1 ? 's' : '' }}
                                    </a>
                                @endif
                                
                                <div class="relative">
                                    <button type="button" 
                                            class="relative p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors duration-200"
                                            onclick="toggleAlertsModal()"
                                            title="Notifications et alertes du centre">
                                        <i class="fas fa-bell text-lg {{ $totalNotifications > 0 ? 'text-red-500 animate-pulse' : '' }}"></i>
                                        @if($totalNotifications > 0)
                                            <span class="absolute -top-1 -right-1 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-red-600 rounded-full animate-bounce">
                                                {{ $totalNotifications > 99 ? '99+' : $totalNotifications }}
                                            </span>
                                        @endif
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <main class="flex-1 overflow-y-auto p-6">
                <!-- Alerts -->
                @if(session('success'))
                    <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                            <span class="text-green-800">{{ session('success') }}</span>
                        </div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>
                            <span class="text-red-800">{{ session('error') }}</span>
                        </div>
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <i class="fas fa-exclamation-triangle text-red-500 mr-2 mt-1"></i>
                            <div class="text-red-800">
                                <ul class="list-disc list-inside">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @if(auth()->user()->is_admin || auth()->user()->is_manager)
        <!-- Modal des Alertes et Notifications (style Tailwind) -->
        @php
            $activeAlertsCount = \App\Models\Alert::where('center_id', auth()->user()->center_id)->where('resolved', false)->count();
            $activeAlerts = \App\Models\Alert::with('bloodType')->where('center_id', auth()->user()->center_id)->where('resolved', false)->orderBy('created_at', 'desc')->limit(5)->get();
            
            // Réservations en attente
            $pendingReservations = \App\Models\ReservationRequest::with(['user', 'items.bloodType'])
                ->where('center_id', auth()->user()->center_id)
                ->where('status', 'pending')
                ->orderBy('created_at', 'desc')
                ->limit(3)
                ->get();
            $pendingReservationsCount = \App\Models\ReservationRequest::where('center_id', auth()->user()->center_id)->where('status', 'pending')->count();
            
            $totalNotifications = $activeAlertsCount + $pendingReservationsCount;
        @endphp
        <div id="alertsModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" onclick="closeAlertsModal()"></div>
                
                <div class="inline-block w-full max-w-4xl p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-semibold leading-6 text-gray-900 flex items-center">
                            <i class="fas fa-bell text-red-600 mr-3"></i>
                            Centre de Notifications
                            @if($totalNotifications > 0)
                                <span class="ml-3 inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                    {{ $totalNotifications }} notification{{ $totalNotifications > 1 ? 's' : '' }}
                                </span>
                            @endif
                        </h3>
                        <button type="button" 
                                class="text-gray-400 hover:text-gray-600 text-xl"
                                onclick="closeAlertsModal()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Section Alertes Stock -->
                        <div class="space-y-4">
                            <h4 class="text-lg font-medium text-gray-900 flex items-center">
                                <i class="fas fa-exclamation-triangle text-orange-500 mr-2"></i>
                                Alertes Stock
                                @if($activeAlertsCount > 0)
                                    <span class="ml-2 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                        {{ $activeAlertsCount }}
                                    </span>
                                @endif
                            </h4>
                            
                            @if($activeAlertsCount > 0)
                                <div class="space-y-3 max-h-80 overflow-y-auto">
                                    @foreach($activeAlerts as $alert)
                                        <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors duration-200">
                                            <div class="flex items-start justify-between">
                                                <div class="flex-1">
                                                    <div class="flex items-center mb-2">
                                                        @if($alert->type === 'low_stock')
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 mr-2">
                                                                <i class="fas fa-tint mr-1"></i> Stock Critique
                                                            </span>
                                                        @elseif($alert->type === 'expiration')
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 mr-2">
                                                                <i class="fas fa-clock mr-1"></i> Expiration
                                                            </span>
                                                        @endif
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                            {{ $alert->bloodType->group }}
                                                        </span>
                                                    </div>
                                                    <p class="text-gray-900 text-sm mb-1">{{ $alert->message }}</p>
                                                    <small class="text-gray-500">
                                                        <i class="fas fa-calendar mr-1"></i>
                                                        {{ $alert->created_at->format('d/m/Y à H:i') }}
                                                    </small>
                                                </div>
                                                <div class="ml-4">
                                                    <button class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-green-600 hover:bg-green-700 transition-colors" 
                                                            onclick="resolveAlert({{ $alert->id }})">
                                                        <i class="fas fa-check mr-1"></i>
                                                        Résoudre
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                
                                @if($activeAlertsCount > 5)
                                    <div class="text-center">
                                        <p class="text-gray-500 text-sm">Et {{ $activeAlertsCount - 5 }} autres alertes...</p>
                                    </div>
                                @endif
                            @else
                                <div class="text-center py-6">
                                    <i class="fas fa-check-circle text-green-500 text-3xl mb-2"></i>
                                    <h5 class="text-lg font-medium text-green-900 mb-1">Stock OK</h5>
                                    <p class="text-green-700 text-sm">Aucune alerte de stock active</p>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Section Réservations en Attente -->
                        <div class="space-y-4">
                            <h4 class="text-lg font-medium text-gray-900 flex items-center">
                                <i class="fas fa-clock text-blue-500 mr-2"></i>
                                Réservations en Attente
                                @if($pendingReservationsCount > 0)
                                    <span class="ml-2 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $pendingReservationsCount }}
                                    </span>
                                @endif
                            </h4>
                            
                            @if($pendingReservationsCount > 0)
                                <div class="space-y-3 max-h-80 overflow-y-auto">
                                    @foreach($pendingReservations as $reservation)
                                        <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors duration-200">
                                            <div class="flex items-start justify-between">
                                                <div class="flex-1">
                                                    <div class="flex items-center mb-2">
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 mr-2">
                                                            <i class="fas fa-user mr-1"></i> {{ $reservation->user->name }}
                                                        </span>
                                                        <span class="text-xs text-gray-500">#{$reservation->id}</span>
                                                    </div>
                                                    <div class="flex flex-wrap gap-1 mb-2">
                                                        @foreach($reservation->items as $item)
                                                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-red-100 text-red-800">
                                                                {{ $item->bloodType->group }} ({{ $item->quantity }})
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                    <small class="text-gray-500">
                                                        <i class="fas fa-calendar mr-1"></i>
                                                        {{ $reservation->created_at->format('d/m/Y à H:i') }}
                                                    </small>
                                                </div>
                                                <div class="ml-4 flex flex-col space-y-1">
                                                    <a href="{{ route('reservations.show', $reservation) }}" 
                                                       class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 transition-colors">
                                                        <i class="fas fa-eye mr-1"></i>
                                                        Voir
                                                    </a>
                                                    <button class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-green-600 hover:bg-green-700 transition-colors" 
                                                            onclick="quickConfirmReservation({{ $reservation->id }})">
                                                        <i class="fas fa-check mr-1"></i>
                                                        Confirmer
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                
                                @if($pendingReservationsCount > 3)
                                    <div class="text-center">
                                        <p class="text-gray-500 text-sm">Et {{ $pendingReservationsCount - 3 }} autres réservations...</p>
                                    </div>
                                @endif
                            @else
                                <div class="text-center py-6">
                                    <i class="fas fa-check-circle text-green-500 text-3xl mb-2"></i>
                                    <h5 class="text-lg font-medium text-green-900 mb-1">Aucune en attente</h5>
                                    <p class="text-green-700 text-sm">Toutes les réservations sont traitées</p>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <div class="mt-8 flex items-center justify-between border-t pt-6">
                        <div class="flex space-x-3">
                            @if($activeAlertsCount > 0)
                                <button type="button" 
                                        class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors"
                                        onclick="refreshAlerts()">
                                    <i class="fas fa-sync-alt mr-2"></i>
                                    Actualiser
                                </button>
                            @endif
                        </div>
                        <div class="flex space-x-3">
                            <!-- <a href="{{ route('alerts.index', ['layout' => 'main']) }}" 
                               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-orange-600 hover:bg-blue-700 transition-colors">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                Gérer les alertes
                            </a> -->
                            <a href="{{ route('alerts.index', ['layout' => 'main']) }}" 
                               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 transition-colors">
                                <i class="fas fa-boxes mr-2"></i>
                                Gérer les alertes
                            </a>
                            <a href="{{ route('reservations.index') }}" 
                               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 transition-colors">
                                <i class="fas fa-list mr-2"></i>
                                Gérer les réservations
                            </a>
                            <button type="button" 
                                    class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors"
                                    onclick="closeAlertsModal()">
                                Fermer
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            function toggleAlertsModal() {
                console.log('toggleAlertsModal called');
                const modal = document.getElementById('alertsModal');
                if (modal) {
                    modal.classList.toggle('hidden');
                    console.log('Modal toggled, hidden class:', modal.classList.contains('hidden'));
                } else {
                    console.error('Modal not found');
                }
            }

            function closeAlertsModal() {
                console.log('closeAlertsModal called');
                const modal = document.getElementById('alertsModal');
                if (modal) {
                    modal.classList.add('hidden');
                    console.log('Modal closed');
                } else {
                    console.error('Modal not found');
                }
            }

            function resolveAlert(alertId) {
                console.log('resolveAlert called with ID:', alertId);
                if (confirm('Marquer cette alerte comme résolue ?')) {
                    console.log('User confirmed, sending request to resolve alert');
                    fetch(`/alerts/${alertId}/resolve`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json',
                        }
                    })
                    .then(response => {
                        console.log('Response received:', response.status);
                        return response.json();
                    })
                    .then(data => {
                        console.log('Response data:', data);
                        if (data.success) {
                            closeAlertsModal();
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

            function quickConfirmReservation(reservationId) {
                console.log('quickConfirmReservation called with ID:', reservationId);
                if (confirm('Confirmer cette réservation ? Cela décrementera automatiquement le stock disponible.')) {
                    fetch(`/reservations/${reservationId}/confirm`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json',
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            closeAlertsModal();
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

            function refreshAlerts() {
                console.log('refreshAlerts called');
                fetch('/alerts/generate', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                    }
                })
                .then(response => {
                    console.log('Generate alerts response:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('Generate alerts data:', data);
                    if (data.success) {
                        closeAlertsModal();
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

            // Fonction pour résoudre une alerte
            function resolveAlert(alertId) {
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
                            closeAlertsModal();
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

            // Fonction pour marquer une notification comme lue (si nécessaire)
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

            // Animation de la cloche quand il y a des notifications
            @if($totalNotifications > 0)
                setInterval(function() {
                    const bell = document.querySelector('.fa-bell');
                    if (bell) {
                        bell.classList.add('animate-pulse');
                        setTimeout(() => {
                            bell.classList.remove('animate-pulse');
                        }, 1000);
                    }
                }, 8000); // Animation toutes les 8 secondes
            @endif
        </script>
    @endif

    @stack('scripts')
</body>
</html>