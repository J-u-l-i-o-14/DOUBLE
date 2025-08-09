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
                            <a href="{{ route('dashboard') }}" 
                               class="flex items-center px-4 py-3 text-white rounded-lg transition-colors duration-200 {{ request()->routeIs('dashboard') ? 'bg-white bg-opacity-20' : 'hover:bg-white hover:bg-opacity-10' }}">
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
                            
                            @if(auth()->user()->is_admin || auth()->user()->is_manager)
                                <!-- Cloche de notifications -->
                                @php
                                    $activeAlertsCount = \App\Models\Alert::where('center_id', auth()->user()->center_id)->where('resolved', false)->count();
                                    $activeAlerts = \App\Models\Alert::with('bloodType')->where('center_id', auth()->user()->center_id)->where('resolved', false)->orderBy('created_at', 'desc')->limit(5)->get();
                                @endphp
                                <div class="relative">
                                    <button type="button" 
                                            class="relative p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors duration-200"
                                            onclick="toggleAlertsModal()">
                                        <i class="fas fa-bell text-lg"></i>
                                        @if($activeAlertsCount > 0)
                                            <span class="absolute -top-1 -right-1 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-red-600 rounded-full">
                                                {{ $activeAlertsCount > 99 ? '99+' : $activeAlertsCount }}
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
        <!-- Modal des Alertes (style Tailwind) -->
        @php
            $activeAlertsCount = \App\Models\Alert::where('center_id', auth()->user()->center_id)->where('resolved', false)->count();
            $activeAlerts = \App\Models\Alert::with('bloodType')->where('center_id', auth()->user()->center_id)->where('resolved', false)->orderBy('created_at', 'desc')->limit(5)->get();
        @endphp
        <div id="alertsModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" onclick="closeAlertsModal()"></div>
                
                <div class="inline-block w-full max-w-2xl p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium leading-6 text-gray-900 flex items-center">
                            <i class="fas fa-bell text-red-600 mr-2"></i>
                            Alertes du Centre
                            @if($activeAlertsCount > 0)
                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    {{ $activeAlertsCount }}
                                </span>
                            @endif
                        </h3>
                        <button type="button" 
                                class="text-gray-400 hover:text-gray-600"
                                onclick="closeAlertsModal()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    
                    <div class="mt-4">
                        @if($activeAlertsCount > 0)
                            <div class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                                <div class="flex items-center">
                                    <i class="fas fa-exclamation-triangle text-yellow-600 mr-2"></i>
                                    <div class="text-yellow-800">
                                        <strong>{{ $activeAlertsCount }}</strong> alerte(s) nécessitent votre attention.
                                    </div>
                                </div>
                            </div>
                            
                            <div class="space-y-3">
                                @foreach($activeAlerts as $alert)
                                    <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors duration-200">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <div class="flex items-center mb-2">
                                                    @if($alert->type === 'low_stock')
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 mr-2">
                                                            <i class="fas fa-tint mr-1"></i> Stock Faible
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
                                                <p class="text-gray-900 mb-1">{{ $alert->message }}</p>
                                                <small class="text-gray-500">
                                                    <i class="fas fa-calendar mr-1"></i>
                                                    {{ $alert->created_at->format('d/m/Y à H:i') }}
                                                </small>
                                            </div>
                                            <div class="ml-4">
                                                <button class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500" 
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
                                <div class="text-center mt-4">
                                    <p class="text-gray-500">Et {{ $activeAlertsCount - 5 }} autres alertes...</p>
                                </div>
                            @endif
                        @else
                            <div class="text-center py-8">
                                <i class="fas fa-check-circle text-green-500 text-5xl mb-4"></i>
                                <h3 class="text-lg font-medium text-green-900 mb-2">Aucune alerte active</h3>
                                <p class="text-green-700">Excellent ! Votre centre n'a aucune alerte critique en cours.</p>
                            </div>
                        @endif
                    </div>
                    
                    <div class="mt-6 flex items-center justify-between">
                        <div>
                            @if($activeAlertsCount > 0)
                                <button type="button" 
                                        class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                        onclick="refreshAlerts()">
                                    <i class="fas fa-sync-alt mr-2"></i>
                                    Actualiser
                                </button>
                            @endif
                        </div>
                        <div class="flex space-x-3">
                            <a href="{{ route('alerts.index') }}" 
                               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                <i class="fas fa-cog mr-2"></i>
                                Gérer toutes les alertes
                            </a>
                            <button type="button" 
                                    class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
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

            // Animation de la cloche quand il y a des alertes
            @if($activeAlertsCount > 0)
                setInterval(function() {
                    const bell = document.querySelector('.fa-bell');
                    if (bell) {
                        bell.classList.add('animate-pulse');
                        setTimeout(() => {
                            bell.classList.remove('animate-pulse');
                        }, 1000);
                    }
                }, 10000); // Animation toutes les 10 secondes
            @endif
        </script>
    @endif

    @stack('scripts')
</body>
</html>