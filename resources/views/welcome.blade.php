<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CargoTrace — Global Cocoa & Coffee Shipment Visibility Platform</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        darkCoffee: '#38251D',
                        cocoaBrown: '#795548',
                        latte: '#E8D7B9',
                        cream: '#FAF7F0',
                        gold: '#C49A55',
                        darkCharcoal: '#292421',
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #FAF7F0; }
        .font-serif-heading { font-family: 'Playfair Display', serif; }
    </style>
</head>
<body class="text-[#292421] antialiased selection:bg-[#C49A55] selection:text-slate-900">

    <!-- Top Navigation Bar -->
    <header class="sticky top-0 z-50 bg-[#38251D]/95 backdrop-blur-md border-b border-[#C49A55]/30 text-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <!-- Brand Logo -->
            <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-[#C49A55] to-[#795548] p-0.5 shadow-md flex items-center justify-center text-xl transition transform group-hover:scale-105">
                    🚢
                </div>
                <div>
                    <div class="flex items-center gap-1.5">
                        <span class="text-xl font-extrabold tracking-wider text-white">CARGO<span class="text-[#C49A55]">TRACE</span></span>
                        <span class="text-[9px] uppercase tracking-widest font-black px-1.5 py-0.5 rounded bg-[#C49A55] text-slate-900">SEA FREIGHT</span>
                    </div>
                    <div class="text-[10px] uppercase tracking-widest text-[#E8D7B9] font-medium">Coffee & Cocoa Global Trade</div>
                </div>
            </a>

            <!-- Navigation Links -->
            <nav class="hidden md:flex items-center gap-6 text-xs font-bold tracking-wide text-[#E8D7B9]">
                <a href="#tracking" class="hover:text-white transition flex items-center gap-1">
                    <span>🔍</span> Lacak Kargo
                </a>
                <a href="{{ route('map.index') }}" class="hover:text-white transition flex items-center gap-1 text-[#C49A55]">
                    <span>🌍</span> Peta Kargo Global
                </a>
                <a href="#commodities" class="hover:text-white transition">Komoditas</a>
                <a href="#corridors" class="hover:text-white transition">Rute Maritim</a>
                <a href="#features" class="hover:text-white transition">Fitur Platform</a>
            </nav>

            <!-- Auth CTA -->
            <div class="flex items-center gap-3">
                @auth
                    <a href="{{ route('dashboard') }}" class="px-5 py-2.5 rounded-xl bg-[#C49A55] hover:bg-[#b38944] text-slate-900 font-extrabold text-xs transition shadow-md flex items-center gap-2">
                        <span>Dashboard Operasional</span>
                        <span>&rarr;</span>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="px-4 py-2 text-xs font-bold text-[#E8D7B9] hover:text-white transition">
                        Masuk
                    </a>
                    <a href="{{ route('login') }}" class="px-5 py-2.5 rounded-xl bg-[#C49A55] hover:bg-[#b38944] text-slate-900 font-extrabold text-xs transition shadow-md">
                        Demo Login &rarr;
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Hero Section with Quick Tracking -->
    <section class="relative bg-gradient-to-b from-[#38251D] via-[#4A3227] to-[#FAF7F0] pt-16 pb-24 text-white overflow-hidden">
        <!-- Subtle Background Decorative Waves -->
        <div class="absolute inset-0 opacity-10 pointer-events-none bg-[radial-gradient(#C49A55_1px,transparent_1px)] [background-size:24px_24px]"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center max-w-3xl mx-auto space-y-4">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 border border-[#C49A55]/40 text-[#E8D7B9] text-xs font-semibold">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                    Global Cocoa & Coffee Shipment Visibility Platform
                </div>

                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-white font-serif-heading leading-tight">
                    Connecting Global <br>
                    <span class="text-[#C49A55] italic">Coffee & Cocoa</span> Markets
                </h1>

                <p class="text-sm sm:text-base text-[#E8D7B9] leading-relaxed max-w-2xl mx-auto">
                    Platform manajemen pengiriman kargo laut internasional untuk eksportir dan importir. Pantau rute maritim, status transit, ETA deterministik, dan anomali muatan secara terpusat.
                </p>
            </div>

            <!-- Interactive Quick Tracking Box (Hero Feature) -->
            <div id="tracking" class="mt-10 max-w-2xl mx-auto bg-white rounded-2xl p-4 sm:p-5 shadow-2xl border border-[#C49A55]/40 text-slate-900">
                <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100 text-xs">
                    <span class="font-bold text-[#38251D] flex items-center gap-1.5 uppercase tracking-wider text-[11px]">
                        <span>📍</span> Lacak Kargo Laut (Maritime AIS Tracking)
                    </span>
                    <span class="text-slate-400 text-[10px]">Real-Time Waypoint Progress</span>
                </div>

                <form action="{{ route('tracking.public') }}" method="GET" class="flex flex-col sm:flex-row gap-2">
                    <div class="relative flex-1">
                        <input type="text" name="q" placeholder="Masukkan nomor kargo (contoh: CTC-2026-0001)..."
                               class="w-full px-4 py-3 pl-10 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 placeholder-slate-400 text-xs sm:text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-[#38251D]">
                        <span class="absolute left-3.5 top-3.5 text-slate-400 text-sm">🚢</span>
                    </div>
                    <button type="submit" class="px-6 py-3 bg-[#38251D] hover:bg-[#292421] text-[#E8D7B9] font-extrabold rounded-xl text-xs sm:text-sm transition flex items-center justify-center gap-2 shadow-sm">
                        <span>Lacak Kargo</span>
                        <span>&rarr;</span>
                    </button>
                </form>

                <!-- Quick Click Sample Pills -->
                <div class="mt-3.5 pt-3 border-t border-slate-100 flex flex-wrap items-center gap-1.5 text-[11px]">
                    <span class="text-slate-400 font-bold">Contoh Cepat:</span>
                    <a href="{{ route('tracking.public', ['q' => 'CTC-2026-0001']) }}" class="px-2 py-0.5 rounded bg-amber-50 hover:bg-amber-100 text-[#38251D] border border-amber-200 transition font-mono font-bold">
                        ☕ CTC-2026-0001 (Arabica to Rotterdam)
                    </a>
                    <a href="{{ route('tracking.public', ['q' => 'CTC-2026-0002']) }}" class="px-2 py-0.5 rounded bg-orange-50 hover:bg-orange-100 text-[#795548] border border-orange-200 transition font-mono font-bold">
                        🍫 CTC-2026-0002 (Cocoa to Hamburg)
                    </a>
                    <a href="{{ route('tracking.public', ['q' => 'CTC-2026-0005']) }}" class="px-2 py-0.5 rounded bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 transition font-mono font-bold">
                        ✅ CTC-2026-0005 (Delivered Genoa)
                    </a>
                </div>
            </div>

            <!-- Key System Stats -->
            <div class="mt-12 grid grid-cols-2 md:grid-cols-4 gap-4 max-w-4xl mx-auto">
                <div class="p-4 rounded-xl bg-white/10 backdrop-blur-sm border border-white/15 text-center">
                    <div class="text-2xl sm:text-3xl font-black text-[#C49A55]">15</div>
                    <div class="text-[11px] font-bold uppercase tracking-wider text-[#E8D7B9] mt-0.5">Kapal & Muatan Terverifikasi</div>
                </div>
                <div class="p-4 rounded-xl bg-white/10 backdrop-blur-sm border border-white/15 text-center">
                    <div class="text-2xl sm:text-3xl font-black text-[#C49A55]">13</div>
                    <div class="text-[11px] font-bold uppercase tracking-wider text-[#E8D7B9] mt-0.5">Pelabuhan Internasional</div>
                </div>
                <div class="p-4 rounded-xl bg-white/10 backdrop-blur-sm border border-white/15 text-center">
                    <div class="text-2xl sm:text-3xl font-black text-[#C49A55]">100%</div>
                    <div class="text-[11px] font-bold uppercase tracking-wider text-[#E8D7B9] mt-0.5">Append-Only AIS Audit</div>
                </div>
                <div class="p-4 rounded-xl bg-white/10 backdrop-blur-sm border border-white/15 text-center">
                    <div class="text-2xl sm:text-3xl font-black text-[#C49A55]">0.0s</div>
                    <div class="text-[11px] font-bold uppercase tracking-wider text-[#E8D7B9] mt-0.5">Kalkulasi Keterlambatan Real-Time</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Commodity Domain Section -->
    <section id="commodities" class="py-16 bg-[#FAF7F0]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-12">
                <span class="text-xs font-bold uppercase tracking-widest text-[#795548]">Spesialisasi Domain Logistik</span>
                <h2 class="text-3xl font-extrabold text-[#38251D] font-serif-heading mt-1">
                    Dua Komoditas Global Utama
                </h2>
                <p class="text-xs sm:text-sm text-slate-600 mt-2">
                    CargoTrace dirancang khusus untuk memenuhi standar sanitari, ventilasi kontainer, kelembapan, dan penanganan kargo laut biji kakao dan kopi dunia.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Cocoa Card -->
                <div class="bg-white rounded-2xl p-6 sm:p-8 border border-[#795548]/20 shadow-sm relative overflow-hidden group hover:border-[#795548] transition">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-3xl">🍫</span>
                        <span class="px-2.5 py-1 rounded-full text-xs font-black bg-[#795548]/10 text-[#795548] border border-[#795548]/20">
                            COCOA LOGISTICS
                        </span>
                    </div>
                    <h3 class="text-xl font-extrabold text-[#38251D] mb-2">Biji & Produk Kakao (Cocoa)</h3>
                    <p class="text-xs text-slate-600 leading-relaxed mb-4">
                        Pemantauan ketat kadar air (maksimal 7.5%) dan ventilasi kontainer untuk mencegah timbulnya jamur selama pelayaran lintas khatulistiwa.
                    </p>
                    <div class="space-y-2 text-xs font-semibold text-slate-700 border-t border-slate-100 pt-3">
                        <div class="flex items-center justify-between"><span>&bull; Fermented Cocoa Beans</span> <span class="text-[10px] text-slate-400">HS 1801.00</span></div>
                        <div class="flex items-center justify-between"><span>&bull; Dried Cocoa Beans</span> <span class="text-[10px] text-slate-400">HS 1801.00</span></div>
                        <div class="flex items-center justify-between"><span>&bull; Pure Prime Pressed Cocoa Butter</span> <span class="text-[10px] text-slate-400">HS 1804.00</span></div>
                        <div class="flex items-center justify-between"><span>&bull; Alkalized Cocoa Powder</span> <span class="text-[10px] text-slate-400">HS 1805.00</span></div>
                        <div class="flex items-center justify-between"><span>&bull; Natural Cocoa Liquor</span> <span class="text-[10px] text-slate-400">HS 1803.10</span></div>
                    </div>
                </div>

                <!-- Coffee Card -->
                <div class="bg-white rounded-2xl p-6 sm:p-8 border border-[#38251D]/20 shadow-sm relative overflow-hidden group hover:border-[#38251D] transition">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-3xl">☕</span>
                        <span class="px-2.5 py-1 rounded-full text-xs font-black bg-[#38251D]/10 text-[#38251D] border border-[#38251D]/20">
                            COFFEE LOGISTICS
                        </span>
                    </div>
                    <h3 class="text-xl font-extrabold text-[#38251D] mb-2">Biji Kopi Hijau & Sangrai (Coffee)</h3>
                    <p class="text-xs text-slate-600 leading-relaxed mb-4">
                        Perlindungan GrainPro multi-layer liner dan kontainer ventilasi 20ft untuk mempertahankan cita rasa specialty kopi dan kelembapan stabil 11-12.5%.
                    </p>
                    <div class="space-y-2 text-xs font-semibold text-slate-700 border-t border-slate-100 pt-3">
                        <div class="flex items-center justify-between"><span>&bull; Green Arabica Coffee Beans (Gayo, Mandheling, Toraja)</span> <span class="text-[10px] text-slate-400">HS 0901.11</span></div>
                        <div class="flex items-center justify-between"><span>&bull; Green Robusta Coffee Beans (Lampung, Vietnam)</span> <span class="text-[10px] text-slate-400">HS 0901.11</span></div>
                        <div class="flex items-center justify-between"><span>&bull; Specialty Roasted Arabica</span> <span class="text-[10px] text-slate-400">HS 0901.21</span></div>
                        <div class="flex items-center justify-between"><span>&bull; Roasted Robusta Espresso Grade</span> <span class="text-[10px] text-slate-400">HS 0901.21</span></div>
                        <div class="flex items-center justify-between"><span>&bull; Rare Liberica Coffee Beans</span> <span class="text-[10px] text-slate-400">HS 0901.11</span></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Maritime Corridors Section -->
    <section id="corridors" class="py-16 bg-white border-y border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-10">
                <div>
                    <span class="text-xs font-bold uppercase tracking-widest text-[#C49A55]">Global Shipping Corridors</span>
                    <h2 class="text-3xl font-extrabold text-[#38251D] font-serif-heading mt-1">
                        Jalur Pelayaran Internasional
                    </h2>
                    <p class="text-xs text-slate-500 mt-1">Rute laut primer penghubung produsen sabuk ekuator dengan pelabuhan konsumen Eropa & Amerika</p>
                </div>
                <a href="{{ route('map.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-[#38251D] hover:text-[#C49A55] transition">
                    <span>Buka Peta Kargo Interaktif Seluruh Dunia</span>
                    <span>&rarr;</span>
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 hover:border-[#C49A55] transition">
                    <div class="text-[10px] uppercase font-bold text-slate-400">Rute 01 &bull; Asia - Eropa</div>
                    <div class="text-sm font-extrabold text-[#38251D] mt-1">Indonesia &rarr; Belanda</div>
                    <div class="text-xs text-slate-600 mt-0.5">Belawan / Priok &rarr; Port of Rotterdam</div>
                    <div class="mt-2 text-[10px] text-slate-500 flex items-center justify-between pt-2 border-t border-slate-200">
                        <span>Via Selat Malaka & Terusan Suez</span>
                        <span class="font-bold text-slate-700">~22 Hari</span>
                    </div>
                </div>

                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 hover:border-[#C49A55] transition">
                    <div class="text-[10px] uppercase font-bold text-slate-400">Rute 02 &bull; Asia - Eropa</div>
                    <div class="text-sm font-extrabold text-[#38251D] mt-1">Indonesia &rarr; Jerman</div>
                    <div class="text-xs text-slate-600 mt-0.5">Tanjung Perak &rarr; Port of Hamburg</div>
                    <div class="mt-2 text-[10px] text-slate-500 flex items-center justify-between pt-2 border-t border-slate-200">
                        <span>Via Samudra Hindia & Laut Utara</span>
                        <span class="font-bold text-slate-700">~24 Hari</span>
                    </div>
                </div>

                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 hover:border-[#C49A55] transition">
                    <div class="text-[10px] uppercase font-bold text-slate-400">Rute 03 &bull; Afrika Barat - Eropa</div>
                    <div class="text-sm font-extrabold text-[#38251D] mt-1">Ghana &rarr; Belgia</div>
                    <div class="text-xs text-slate-600 mt-0.5">Tema Port &rarr; Port of Antwerp</div>
                    <div class="mt-2 text-[10px] text-slate-500 flex items-center justify-between pt-2 border-t border-slate-200">
                        <span>Via Jalur Atlantik Timur</span>
                        <span class="font-bold text-slate-700">~14 Hari</span>
                    </div>
                </div>

                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 hover:border-[#C49A55] transition">
                    <div class="text-[10px] uppercase font-bold text-slate-400">Rute 04 &bull; Asia - Mediterania</div>
                    <div class="text-sm font-extrabold text-[#38251D] mt-1">Vietnam &rarr; Italia</div>
                    <div class="text-xs text-slate-600 mt-0.5">Haiphong &rarr; Port of Genoa</div>
                    <div class="mt-2 text-[10px] text-slate-500 flex items-center justify-between pt-2 border-t border-slate-200">
                        <span>Via Laut Merah & Mediterania</span>
                        <span class="font-bold text-slate-700">~19 Hari</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Engineering Features Grid (Slide 03-14 Compliance) -->
    <section id="features" class="py-16 bg-[#FAF7F0]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-12">
                <span class="text-xs font-bold uppercase tracking-widest text-[#795548]">Arsitektur & Spesifikasi Rekayasa</span>
                <h2 class="text-3xl font-extrabold text-[#38251D] font-serif-heading mt-1">
                    Fitur Inti Logistik Maritim CargoTrace
                </h2>
                <p class="text-xs text-slate-600 mt-1">Memenuhi seluruh tolok ukur spesifikasi tugas besar rekayasa perangkat lunak logistik laut internasional</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Feature 1: Deterministic Delay Formula -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-2">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 border border-amber-200 text-amber-700 flex items-center justify-center font-bold text-lg mb-3">
                        ⏱️
                    </div>
                    <h3 class="text-base font-extrabold text-slate-900">Kalkulasi Keterlambatan Deterministik</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Formula baku <span class="font-mono font-bold text-slate-800">delay_hours = max(0, ETA - STA)</span> dengan klasifikasi keparahan 4-tingkat (NONE, LOW, MEDIUM, HIGH) dan ambang batas yang dapat dikonfigurasi.
                    </p>
                </div>

                <!-- Feature 2: Append-only Waypoints -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-2">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 border border-blue-200 text-blue-700 flex items-center justify-center font-bold text-lg mb-3">
                        🔒
                    </div>
                    <h3 class="text-base font-extrabold text-slate-900">Timeline & State Machine Immutability</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Peristiwa pelacakan bersifat <span class="font-bold">append-only</span> yang terkunci terhadap modifikasi dan penghapusan sembarangan untuk integritas audit maritim internasional.
                    </p>
                </div>

                <!-- Feature 3: Interactive Leaflet Map -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-2">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 flex items-center justify-center font-bold text-lg mb-3">
                        🗺️
                    </div>
                    <h3 class="text-base font-extrabold text-slate-900">Peta Kargo & Posisi Barang Interaktif</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Visualisasi rute kapal laut Leaflet + OpenStreetMap dengan penanda radar berkedip (pulsing beacon) yang menunjukkan secara persis posisi kargo saat ini.
                    </p>
                </div>

                <!-- Feature 4: Open-Meteo Weather -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-2">
                    <div class="w-10 h-10 rounded-xl bg-sky-50 border border-sky-200 text-sky-700 flex items-center justify-center font-bold text-lg mb-3">
                        🌤️
                    </div>
                    <h3 class="text-base font-extrabold text-slate-900">Integrasi Cuaca Maritim Eksternal</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Koneksi Open-Meteo API yang di-cache untuk menampilkan temperatur, angin, dan kondisi cuaca di pelabuhan dan titik koordinat kapal laut.
                    </p>
                </div>

                <!-- Feature 5: Role-Based Access Control -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-2">
                    <div class="w-10 h-10 rounded-xl bg-purple-50 border border-purple-200 text-purple-700 flex items-center justify-center font-bold text-lg mb-3">
                        🛡️
                    </div>
                    <h3 class="text-base font-extrabold text-slate-900">Role-Based Access Control (RBAC)</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Segregasi hak akses data secara ketat antara Administrator, Operator Logistik, dan Customer (importir/eksportir hanya melihat muatannya sendiri).
                    </p>
                </div>

                <!-- Feature 6: REST API & Postman -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-2">
                    <div class="w-10 h-10 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 flex items-center justify-center font-bold text-lg mb-3">
                        🔌
                    </div>
                    <h3 class="text-base font-extrabold text-slate-900">REST API & Postman Collection v2.1</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        16+ endpoint REST API lengkap dengan Sanctum Bearer Token, status transition, webhook support, dan koleksi Postman siap impor.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Interactive Demo Credentials Section for Reviewers -->
    <section class="py-16 bg-white border-t border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-gradient-to-r from-[#38251D] via-[#4A3227] to-[#292421] rounded-3xl p-6 sm:p-10 text-white shadow-xl border border-[#C49A55]/30">
                <div class="max-w-2xl">
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#C49A55] text-slate-900">
                        AKSES PENGUJIAN PENILAI / DOSEN
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-white font-serif-heading mt-2">
                        Akun Demo Siap Uji (Quick Login)
                    </h2>
                    <p class="text-xs sm:text-sm text-[#E8D7B9] mt-1">
                        Sistem telah disiapkan dengan kredensial untuk seluruh tingkatan pengguna (Admin, Operator, Customer).
                    </p>
                </div>

                <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Admin -->
                    <div class="bg-white/10 p-4 rounded-xl border border-white/20">
                        <div class="text-[10px] uppercase font-bold text-[#C49A55]">Role: Administrator</div>
                        <div class="text-sm font-bold text-white mt-1">admin@cargotrace.com</div>
                        <div class="text-xs text-[#E8D7B9] font-mono mt-0.5">password: password123</div>
                        <div class="mt-3">
                            <a href="{{ route('login') }}" class="w-full inline-block text-center px-3 py-1.5 rounded-lg bg-[#C49A55] hover:bg-[#b38944] text-slate-900 text-xs font-extrabold transition">
                                Masuk sebagai Admin
                            </a>
                        </div>
                    </div>

                    <!-- Operator -->
                    <div class="bg-white/10 p-4 rounded-xl border border-white/20">
                        <div class="text-[10px] uppercase font-bold text-[#C49A55]">Role: Operator Logistik</div>
                        <div class="text-sm font-bold text-white mt-1">operator@cargotrace.com</div>
                        <div class="text-xs text-[#E8D7B9] font-mono mt-0.5">password: password123</div>
                        <div class="mt-3">
                            <a href="{{ route('login') }}" class="w-full inline-block text-center px-3 py-1.5 rounded-lg bg-white hover:bg-slate-100 text-slate-900 text-xs font-extrabold transition">
                                Masuk sebagai Operator
                            </a>
                        </div>
                    </div>

                    <!-- Customer -->
                    <div class="bg-white/10 p-4 rounded-xl border border-white/20">
                        <div class="text-[10px] uppercase font-bold text-[#C49A55]">Role: Customer (Importer)</div>
                        <div class="text-sm font-bold text-white mt-1">customer1@cargotrace.com</div>
                        <div class="text-xs text-[#E8D7B9] font-mono mt-0.5">password: password123</div>
                        <div class="mt-3">
                            <a href="{{ route('login') }}" class="w-full inline-block text-center px-3 py-1.5 rounded-lg bg-white hover:bg-slate-100 text-slate-900 text-xs font-extrabold transition">
                                Masuk sebagai Customer
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-[#292421] text-white py-10 border-t border-slate-800 text-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <span class="text-lg">🚢</span>
                <span class="font-extrabold tracking-wider">CARGOTRACE &bull; COFFEE & COCOA GLOBAL TRADE</span>
            </div>
            <div class="text-slate-400 text-center sm:text-right">
                <div>Aplikasi Tugas Besar Rekayasa Perangkat Lunak Logistik Maritim</div>
                <div class="text-[10px] text-slate-500 mt-0.5">&copy; 2026 CargoTrace Platform. All rights reserved.</div>
            </div>
        </div>
    </footer>

</body>
</html>
