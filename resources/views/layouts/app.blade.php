<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'CargoTrace') - Global Shipment Visibility & Tracking Platform</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f0f7ff',
                            100: '#e0effe',
                            500: '#0284c7',
                            600: '#0369a1',
                            700: '#075985',
                            800: '#0c4a6e',
                            900: '#082f49',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Leaflet CSS & JS for OpenStreetMap -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        .leaflet-container { font-family: inherit; }
    </style>
</head>
<body class="h-full flex flex-col text-slate-800 antialiased font-sans">

    <!-- Top Navigation Bar -->
    <header class="bg-slate-900 text-white shadow-md sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Brand / Logo -->
                <div class="flex items-center space-x-3">
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-2">
                        <div class="w-9 h-9 rounded-lg bg-sky-500 flex items-center justify-center font-bold text-white shadow">
                            CT
                        </div>
                        <span class="text-xl font-bold tracking-tight text-white">Cargo<span class="text-sky-400">Trace</span></span>
                    </a>
                    <span class="hidden md:inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold bg-sky-950 text-sky-300 border border-sky-800/60">
                        Global Visibility MVP
                    </span>
                </div>

                <!-- Navigation Links -->
                @auth
                <nav class="hidden md:flex items-center space-x-1 lg:space-x-2 text-sm font-medium">
                    <a href="{{ route('dashboard') }}" class="px-3 py-2 rounded-md transition {{ request()->routeIs('dashboard') ? 'bg-slate-800 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        Dashboard
                    </a>
                    <a href="{{ route('shipments.index') }}" class="px-3 py-2 rounded-md transition {{ request()->routeIs('shipments.*') ? 'bg-slate-800 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        Shipments
                    </a>

                    @if(Auth::user()->isAdmin())
                        <a href="{{ route('carriers.index') }}" class="px-3 py-2 rounded-md transition {{ request()->routeIs('carriers.*') ? 'bg-slate-800 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            Carriers
                        </a>
                        <a href="{{ route('locations.index') }}" class="px-3 py-2 rounded-md transition {{ request()->routeIs('locations.*') ? 'bg-slate-800 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            Locations
                        </a>
                        <a href="{{ route('users.index') }}" class="px-3 py-2 rounded-md transition {{ request()->routeIs('users.*') ? 'bg-slate-800 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            Users
                        </a>
                        <a href="{{ route('audit_logs.index') }}" class="px-3 py-2 rounded-md transition {{ request()->routeIs('audit_logs.*') ? 'bg-slate-800 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            Audit Logs
                        </a>
                        <a href="{{ route('settings.index') }}" class="px-3 py-2 rounded-md transition {{ request()->routeIs('settings.*') ? 'bg-slate-800 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            Rules & Config
                        </a>
                    @endif
                </nav>

                <!-- Right profile & notifications -->
                <div class="flex items-center space-x-3">
                    <!-- Notifications Link -->
                    <a href="{{ route('notifications.index') }}" class="relative p-2 text-slate-300 hover:text-white transition rounded-full hover:bg-slate-800" title="Notifications">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        @php $unreadCount = Auth::user()->unreadNotificationsCount(); @endphp
                        @if($unreadCount > 0)
                            <span class="absolute top-1 right-1 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-white transform translate-x-1/4 -translate-y-1/4 bg-red-600 rounded-full animate-pulse">
                                {{ $unreadCount }}
                            </span>
                        @endif
                    </a>

                    <!-- User role badge -->
                    <div class="flex items-center space-x-2 border-l border-slate-700 pl-3">
                        <div class="text-right hidden sm:block">
                            <div class="text-xs font-semibold text-white">{{ Auth::user()->name }}</div>
                            <span class="inline-block px-1.5 py-0.2 rounded text-[10px] font-bold uppercase
                                @if(Auth::user()->isAdmin()) bg-purple-900 text-purple-200 border border-purple-700
                                @elseif(Auth::user()->isOperator()) bg-emerald-900 text-emerald-200 border border-emerald-700
                                @else bg-sky-900 text-sky-200 border border-sky-700 @endif">
                                {{ Auth::user()->role }}
                            </span>
                        </div>

                        <!-- Logout Form -->
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="px-2.5 py-1.5 rounded text-xs font-medium bg-slate-800 hover:bg-red-900/60 hover:text-red-300 text-slate-300 transition border border-slate-700">
                                Sign Out
                            </button>
                        </form>
                    </div>
                </div>
                @else
                <div class="flex items-center space-x-3 text-sm">
                    <a href="{{ route('login') }}" class="text-slate-300 hover:text-white px-3 py-2 font-medium">Log in</a>
                    <a href="{{ route('register') }}" class="bg-sky-500 hover:bg-sky-400 text-white font-medium px-4 py-2 rounded-lg shadow transition">Register</a>
                </div>
                @endauth
            </div>
        </div>
    </header>

    <!-- Flash Messages -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
        @if(session('success'))
            <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 p-4 rounded-md shadow-sm mb-4 flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900">&times;</button>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-rose-50 border-l-4 border-rose-500 text-rose-800 p-4 rounded-md shadow-sm mb-4 flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-rose-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    <span>{{ session('error') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-900">&times;</button>
            </div>
        @endif

        @if(session('warning'))
            <div class="bg-amber-50 border-l-4 border-amber-500 text-amber-800 p-4 rounded-md shadow-sm mb-4 flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>{{ session('warning') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-amber-600 hover:text-amber-900">&times;</button>
            </div>
        @endif

        @if(session('info'))
            <div class="bg-sky-50 border-l-4 border-sky-500 text-sky-800 p-4 rounded-md shadow-sm mb-4 flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-sky-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('info') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-sky-600 hover:text-sky-900">&times;</button>
            </div>
        @endif

        @if($errors->any())
            <div class="bg-rose-50 border-l-4 border-rose-500 text-rose-800 p-4 rounded-md shadow-sm mb-4">
                <div class="font-semibold mb-1">Please correct the following errors:</div>
                <ul class="list-disc list-inside text-sm space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <!-- Main Content Area -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 mt-auto py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center justify-between text-xs text-slate-500 gap-2">
            <div>
                <strong>CargoTrace</strong> &bull; Global Shipment Visibility & Tracking Platform &bull; Web Programming 2 UAS Final Project.
            </div>
            <div>
                Map data &copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener" class="underline hover:text-slate-800">OpenStreetMap contributors</a> | Weather via <a href="https://open-meteo.com/" target="_blank" rel="noopener" class="underline hover:text-slate-800">Open-Meteo API</a>.
            </div>
        </div>
    </footer>

    @yield('scripts')
</body>
</html>
