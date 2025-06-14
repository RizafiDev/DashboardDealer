<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Sistem Absensi Digital</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            900: '#1e3a8a'
                        }
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-in-out',
                        'slide-up': 'slideUp 0.3s ease-out',
                        'pulse-glow': 'pulseGlow 2s ease-in-out infinite',
                        'float': 'float 3s ease-in-out infinite',
                        'bounce-gentle': 'bounceGentle 1s ease-in-out infinite'
                    }
                }
            }
        }
    </script>

    <!-- Leaflet CSS dan JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>

    <style>
        [x-cloak] {
            display: none !important;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideUp {
            from {
                transform: translateY(20px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @keyframes pulseGlow {

            0%,
            100% {
                box-shadow: 0 0 20px rgba(59, 130, 246, 0.3);
            }

            50% {
                box-shadow: 0 0 30px rgba(59, 130, 246, 0.6);
            }
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        @keyframes bounceGentle {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-5px);
            }
        }

        .glass-morphism {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .dark-gradient-bg {
            background: linear-gradient(135deg, #1e3a8a 0%, #3730a3 50%, #581c87 100%);
        }

        .gradient-border {
            background: linear-gradient(45deg, #3b82f6, #8b5cf6, #06b6d4);
            padding: 2px;
            border-radius: 16px;
        }

        .gradient-border-content {
            background: white;
            border-radius: 14px;
        }

        .dark .gradient-border-content {
            background: #1f2937;
        }

        /* Style untuk map */
        .map-container {
            position: relative;
            border-radius: 0.5rem;
            overflow: hidden;
        }

        #map {
            width: 100%;
            height: 300px;
            z-index: 1;
        }

        .location-marker {
            display: flex;
            align-items: center;
            padding: 5px 10px;
            border-radius: 20px;
            background: white;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .location-marker i {
            margin-right: 5px;
        }
    </style>
</head>

<body class="bg-gray-50 dark:bg-gray-900 transition-colors duration-300 font-sans antialiased">
    <!-- Modern Header with Glass Effect -->
    <header class="sticky top-0 z-50 glass-morphism border-b border-white/10 backdrop-blur-xl">
        <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo & App Name -->
                <div class="flex items-center space-x-3">
                    <div
                        class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center animate-float shadow-lg">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900 dark:text-white">
                            {{ config('app.name', 'AttendanceApp') }}
                        </h1>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Digital Presence System</p>
                    </div>
                </div>

                <!-- Header Actions -->
                <div class="flex items-center space-x-4">
                    <!-- Theme Toggle -->
                    <button id="theme-toggle"
                        class="p-2 rounded-lg bg-white/20 hover:bg-white/30 dark:bg-gray-700 dark:hover:bg-gray-600 transition-all duration-200 backdrop-blur-sm">
                        <svg id="theme-icon-light" class="w-5 h-5 text-gray-700 dark:text-gray-300 hidden dark:block"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <svg id="theme-icon-dark" class="w-5 h-5 text-gray-700 dark:text-gray-300 block dark:hidden"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </button>

                    <!-- User Info -->
                    @auth
                        <div class="flex items-center space-x-3">
                            <div
                                class="w-8 h-8 bg-gradient-to-br from-green-400 to-blue-500 rounded-full flex items-center justify-center shadow-lg">
                                <span class="text-white font-semibold text-sm">
                                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}{{ strtoupper(substr(explode(' ', Auth::user()->name)[1] ?? '', 0, 1)) }}
                                </span>
                            </div>
                            <div class="hidden md:block">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ Auth::user()->name }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ Auth::user()->email }}</p>
                            </div>
                        </div>

                        <!-- Logout Button -->
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit"
                                class="px-3 py-2 text-sm font-medium text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors duration-200">
                                <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3v1" />
                                </svg>
                                Keluar
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}"
                            class="px-4 py-2 text-sm font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors duration-200">
                            Masuk
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="min-h-screen pb-12">
        @yield('content')
    </main>

    <!-- Modern Footer -->
    <footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 mt-12">
        <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="flex items-center space-x-2 mb-4 md:mb-0">
                    <div
                        class="w-8 h-8 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ config('app.name', 'AttendanceApp') }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">© {{ date('Y') }} Sistem Absensi Digital</p>
                    </div>
                </div>

                <div class="flex items-center space-x-4 text-xs text-gray-500 dark:text-gray-400">
                    <span>Powered by Laravel</span>
                    <div class="w-1 h-1 bg-gray-400 rounded-full"></div>
                    <span>Made with ❤️</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Floating Elements -->
    <div id="floating-elements">
        <!-- Loading Overlay -->
        <div id="loading-overlay"
            class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center">
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-2xl">
                <div class="flex items-center space-x-3">
                    <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
                    <span class="text-gray-900 dark:text-white font-medium">Memproses...</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // Theme Management
        const themeToggle = document.getElementById('theme-toggle');
        const html = document.documentElement;

        // Load saved theme or default to system preference
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            html.classList.add('dark');
        }

        themeToggle?.addEventListener('click', () => {
            html.classList.toggle('dark');
            localStorage.setItem('theme', html.classList.contains('dark') ? 'dark' : 'light');
        });

        // Global Loading Functions
        window.showLoading = function (show = true) {
            const overlay = document.getElementById('loading-overlay');
            if (overlay) {
                overlay.classList.toggle('hidden', !show);
                overlay.classList.toggle('flex', show);
            }
        };

        // Global Notification Functions
        window.showNotification = function (message, type = 'info', duration = 3000) {
            const notification = document.createElement('div');
            notification.className = `fixed top-20 right-4 z-50 max-w-sm w-full bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 p-4 transform translate-x-full transition-transform duration-300`;

            const icons = {
                success: '✅',
                error: '❌',
                warning: '⚠️',
                info: 'ℹ️'
            };

            const colors = {
                success: 'text-green-600 dark:text-green-400',
                error: 'text-red-600 dark:text-red-400',
                warning: 'text-yellow-600 dark:text-yellow-400',
                info: 'text-blue-600 dark:text-blue-400'
            };

            notification.innerHTML = `
                <div class="flex items-center space-x-3">
                    <span class="text-xl">${icons[type] || icons.info}</span>
                    <p class="text-sm font-medium text-gray-900 dark:text-white flex-1">${message}</p>
                    <button onclick="this.parentElement.parentElement.remove()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            `;

            document.body.appendChild(notification);

            // Animate in
            setTimeout(() => {
                notification.classList.remove('translate-x-full');
            }, 100);

            // Auto remove
            setTimeout(() => {
                notification.classList.add('translate-x-full');
                setTimeout(() => notification.remove(), 300);
            }, duration);
        };

        // Intersection Observer for animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Initialize animations when DOM is ready
        document.addEventListener('DOMContentLoaded', function () {
            // Observe all animated elements
            document.querySelectorAll('.animate-slide-up').forEach(el => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(20px)';
                el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                observer.observe(el);
            });
        });

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>

    @stack('scripts')
</body>

</html>