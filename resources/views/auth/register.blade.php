<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'LifeSaver') }} - Inscription</title>

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

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(50px);
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

        .animate-slideInRight {
            animation: slideInRight 0.8s ease-out;
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
        .blood-drop:nth-child(5) { top: 50%; left: 5%; animation-delay: 2.5s; }
        .blood-drop:nth-child(6) { top: 60%; right: 5%; animation-delay: 1.5s; }
    </style>
</head>

<body class="font-sans antialiased">
    <div class="min-h-screen gradient-bg relative overflow-hidden">
        <!-- Animated Background Elements -->
        <div class="blood-drop opacity-20"></div>
        <div class="blood-drop opacity-20"></div>
        <div class="blood-drop opacity-20"></div>
        <div class="blood-drop opacity-20"></div>
        <div class="blood-drop opacity-20"></div>
        <div class="blood-drop opacity-20"></div>

        <!-- Main Container -->
        <div class="min-h-screen flex items-center justify-center px-4 sm:px-6 lg:px-8 py-12">
            <div class="max-w-md w-full space-y-8">

                <!-- Logo Section -->
                <div class="text-center animate-fadeInUp">
                    <div class="mx-auto h-20 w-20 bg-white rounded-full flex items-center justify-center shadow-2xl animate-pulse-custom">
                        <i class="fas fa-user-plus text-red-600 text-3xl"></i>
                    </div>
                    <h2 class="mt-6 text-3xl font-bold text-white">
                        Rejoignez LifeSaver
                    </h2>
                    <p class="mt-2 text-sm text-red-100">
                        Créez votre compte pour commencer à sauver des vies
                    </p>
                </div>

                <!-- Register Form -->
                <div class="glass-effect rounded-2xl shadow-2xl p-8 animate-slideInRight">
                    <form method="POST" action="{{ route('register') }}" class="space-y-6">
                        @csrf

                        <!-- Name -->
                        <div class="space-y-2">
                            <label for="name" class="block text-sm font-medium text-gray-700">
                                <i class="fas fa-user mr-2 text-red-600"></i>
                                Nom complet
                            </label>
                            <input id="name"
                                   name="name"
                                   type="text"
                                   value="{{ old('name') }}"
                                   required
                                   autofocus
                                   autocomplete="name"
                                   class="input-focus appearance-none relative block w-full px-4 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 focus:z-10 sm:text-sm"
                                   placeholder="Votre nom complet">
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

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
                                   autocomplete="username"
                                   class="input-focus appearance-none relative block w-full px-4 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 focus:z-10 sm:text-sm"
                                   placeholder="votre@email.com">
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <!-- Role Selection -->
                        <div class="space-y-2">
                            <label for="role" class="block text-sm font-medium text-gray-700">
                                <i class="fas fa-users mr-2 text-red-600"></i>
                                Type de compte
                            </label>
                            <select id="role"
                                    name="role"
                                    required
                                    class="input-focus appearance-none relative block w-full px-4 py-3 border border-gray-300 text-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 focus:z-10 sm:text-sm">
                                <option value="">Sélectionnez votre rôle</option>
                                <option value="donor" {{ old('role') == 'donor' ? 'selected' : '' }}>Donneur</option>
                                <option value="patient" {{ old('role') == 'patient' ? 'selected' : '' }}>Patient</option>
                                <option value="client" {{ old('role') == 'client' ? 'selected' : '' }}>Client </option>
                            </select>
                            <x-input-error :messages="$errors->get('role')" class="mt-2" />
                        </div>

                        <!-- Password -->
                        <div class="space-y-2">
                            <label for="password" class="block text-sm font-medium text-gray-700">
                                <i class="fas fa-lock mr-2 text-red-600"></i>
                                Mot de passe
                            </label>
                            <div class="relative">
                                <input id="password"
                                       name="password"
                                       type="password"
                                       required
                                       autocomplete="new-password"
                                       class="input-focus appearance-none relative block w-full px-4 py-3 pr-12 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 focus:z-10 sm:text-sm"
                                       placeholder="••••••••">
                                <button type="button"
                                        onclick="togglePassword('password', 'password-icon')"
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <i id="password-icon" class="fas fa-eye text-gray-400 hover:text-red-600 transition-colors duration-200"></i>
                                </button>
                            </div>
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <!-- Confirm Password -->
                        <div class="space-y-2">
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                                <i class="fas fa-lock mr-2 text-red-600"></i>
                                Confirmer le mot de passe
                            </label>
                            <div class="relative">
                                <input id="password_confirmation"
                                       name="password_confirmation"
                                       type="password"
                                       required
                                       autocomplete="new-password"
                                       class="input-focus appearance-none relative block w-full px-4 py-3 pr-12 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 focus:z-10 sm:text-sm"
                                       placeholder="••••••••">
                                <button type="button"
                                        onclick="togglePassword('password_confirmation', 'password-confirmation-icon')"
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <i id="password-confirmation-icon" class="fas fa-eye text-gray-400 hover:text-red-600 transition-colors duration-200"></i>
                                </button>
                            </div>
                            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                        </div>

                        <!-- Terms and Conditions -->
                        <div class="flex items-start">
                            <input id="terms"
                                   name="terms"
                                   type="checkbox"
                                   required
                                   class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded transition-all duration-200 mt-1">
                            <label for="terms" class="ml-2 block text-sm text-gray-700">
                                J'accepte les
                                <a href="#" class="font-medium text-red-600 hover:text-red-500 transition-colors duration-200">
                                    conditions d'utilisation
                                </a>
                                et la
                                <a href="#" class="font-medium text-red-600 hover:text-red-500 transition-colors duration-200">
                                    politique de confidentialité
                                </a>
                            </label>
                        </div>

                        <!-- Submit Button -->
                        <div>
                            <button type="submit"
                                    class="btn-hover group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transform hover:scale-105 transition-all duration-200">
                                <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                                    <i class="fas fa-user-plus text-red-300 group-hover:text-red-200 transition-colors duration-200"></i>
                                </span>
                                Créer mon compte
                            </button>
                        </div>

                        <!-- Login Link -->
                        <div class="text-center">
                            <p class="text-sm text-gray-600">
                                Déjà un compte ?
                                <a href="{{ route('login') }}"
                                   class="font-medium text-red-600 hover:text-red-500 transition-colors duration-200">
                                    Se connecter
                                </a>
                            </p>
                        </div>
                    </form>
                </div>

                <!-- Back to Home -->
                <div class="text-center animate-fadeInUp">
                    <a href="{{ route('home') }}"
                       class="inline-flex items-center text-red-100 hover:text-white transition-colors duration-200">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Retour à l'accueil
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(inputId, iconId) {
            const passwordInput = document.getElementById(inputId);
            const passwordIcon = document.getElementById(iconId);

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordIcon.classList.remove('fa-eye');
                passwordIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                passwordIcon.classList.remove('fa-eye-slash');
                passwordIcon.classList.add('fa-eye');
            }
        }

        // Password strength indicator
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthIndicator = document.getElementById('password-strength');

            let strength = 0;
            if (password.length >= 8) strength++;
            if (password.match(/[a-z]/)) strength++;
            if (password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;

            // You can add visual feedback here
        });

        // Password confirmation validation
        document.getElementById('password_confirmation').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmation = this.value;

            if (password !== confirmation && confirmation.length > 0) {
                this.setCustomValidity('Les mots de passe ne correspondent pas');
            } else {
                this.setCustomValidity('');
            }
        });

        // Add loading animation on form submit
        document.querySelector('form').addEventListener('submit', function(e) {
            const submitBtn = document.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Création du compte...';
            submitBtn.disabled = true;
        });

        // Add focus animations
        document.querySelectorAll('input, select').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('transform', 'scale-105');
            });

            input.addEventListener('blur', function() {
                this.parentElement.classList.remove('transform', 'scale-105');
            });
        });

        // Role selection helper
        document.getElementById('role').addEventListener('change', function() {
            const roleDescriptions = {
                'donor': 'Vous pourrez prendre des rendez-vous pour donner votre sang',
                'patient': 'Vous pourrez rechercher et réserver du sang selon vos besoins',
                'client': 'Accès aux fonctionnalités de gestion pour les établissements de santé'
            };

            // You can add role description display here
        });
    </script>
</body>
</html>
