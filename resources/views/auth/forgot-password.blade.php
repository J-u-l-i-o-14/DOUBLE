<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'LifeSaver') }} - Mot de passe oublié</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-10px);
            }
        }

        .animate-fadeInUp {
            animation: fadeInUp 0.8s ease-out;
        }

        .animate-slideInLeft {
            animation: slideInLeft 0.8s ease-out;
        }

        .animate-pulse-custom {
            animation: pulse 2s infinite;
        }

        .animate-float {
            animation: float 3s ease-in-out infinite;
        }

        .gradient-bg {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 50%, #991b1b 100%);
        }

        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .input-focus {
            transition: all 0.3s ease;
        }

        .input-focus:focus {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(220, 38, 38, 0.2);
        }

        .btn-hover {
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-hover:before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .btn-hover:hover:before {
            left: 100%;
        }

        .blood-drop {
            position: absolute;
            width: 20px;
            height: 20px;
            background: #dc2626;
            border-radius: 50% 50% 50% 50% / 60% 60% 40% 40%;
            animation: float 4s ease-in-out infinite;
        }

        .blood-drop:nth-child(1) { top: 10%; left: 10%; animation-delay: 0s; }
        .blood-drop:nth-child(2) { top: 20%; right: 10%; animation-delay: 1s; }
        .blood-drop:nth-child(3) { bottom: 30%; left: 15%; animation-delay: 2s; }
        .blood-drop:nth-child(4) { bottom: 10%; right: 20%; animation-delay: 3s; }
    </style>
</head>

<body class="font-sans antialiased">
    <div class="min-h-screen gradient-bg relative overflow-hidden">
        <!-- Animated Background Elements -->
        <div class="blood-drop opacity-20"></div>
        <div class="blood-drop opacity-20"></div>
        <div class="blood-drop opacity-20"></div>
        <div class="blood-drop opacity-20"></div>

        <!-- Main Container -->
        <div class="min-h-screen flex items-center justify-center px-4 sm:px-6 lg:px-8">
            <div class="max-w-md w-full space-y-8">

                <!-- Logo Section -->
                <div class="text-center animate-fadeInUp">
                    <div class="mx-auto h-20 w-20 bg-white rounded-full flex items-center justify-center shadow-2xl animate-pulse-custom">
                        <i class="fas fa-tint text-red-600 text-3xl"></i>
                    </div>
                    <h2 class="mt-6 text-3xl font-bold text-white">
                        Réinitialiser votre mot de passe
                    </h2>
                    <p class="mt-2 text-sm text-red-100">
                        Entrez votre adresse email pour recevoir un lien de réinitialisation
                    </p>
                </div>

                <!-- Reset Form -->
                <div class="glass-effect rounded-2xl shadow-2xl p-8 animate-slideInLeft">
                    <div class="mb-6 text-sm text-gray-600 bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                        Mot de passe oublié ? Aucun problème. Indiquez-nous simplement votre adresse email et nous vous enverrons un lien de réinitialisation qui vous permettra d'en choisir un nouveau.
                    </div>

                    <!-- Session Status -->
                    <x-auth-session-status class="mb-4" :status="session('status')" />

                    <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
                        @csrf

                        <!-- Email Address -->
                        <div class="space-y-2">
                            <label for="email" class="block text-sm font-medium text-gray-700">
                                <i class="fas fa-envelope mr-2 text-red-600"></i>
                                Adresse email
                            </label>
                            <input id="email"
                                   name="email"
                                   type="email"
                                   value="{{ old('email') }}"
                                   required
                                   autofocus
                                   class="input-focus appearance-none relative block w-full px-4 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 focus:z-10 sm:text-sm"
                                   placeholder="votre@email.com">
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <!-- Submit Button -->
                        <div>
                            <button type="submit"
                                    class="btn-hover group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transform hover:scale-105 transition-all duration-200">
                                <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                                    <i class="fas fa-paper-plane text-red-300 group-hover:text-red-200 transition-colors duration-200"></i>
                                </span>
                                Envoyer le lien de réinitialisation
                            </button>
                        </div>

                        <!-- Back to Login Link -->
                        <div class="text-center">
                            <p class="text-sm text-gray-600">
                                Vous vous souvenez de votre mot de passe ?
                                <a href="{{ route('login') }}"
                                   class="font-medium text-red-600 hover:text-red-500 transition-colors duration-200">
                                    <i class="fas fa-arrow-left mr-1"></i>
                                    Retour à la connexion
                                </a>
                            </p>
                        </div>
                    </form>
                </div>

                <!-- Back to Home -->
                <div class="text-center animate-fadeInUp">
                    <a href="{{ route('dashboard') }}"
                       class="inline-flex items-center text-sm text-red-100 hover:text-white transition-colors duration-200">
                        <i class="fas fa-home mr-2"></i>
                        Retour à l'accueil
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
