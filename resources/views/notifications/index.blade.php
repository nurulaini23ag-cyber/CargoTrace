@extends('layouts.app')

@section('title', 'Pusat Notifikasi & Cuaca Maritim Real-Time — CargoTrace')

@section('content')
<div class="max-w-7xl mx-auto space-y-6 font-sans">

    <style>
        /* CSS Animations for Maritime Weather Visuals */
        @keyframes lightning-flash {
            0%, 100% { opacity: 0; filter: drop-shadow(0 0 0px transparent); }
            14% { opacity: 0; }
            15% { opacity: 1; filter: drop-shadow(0 0 12px #facc15) drop-shadow(0 0 24px #38bdf8); }
            17% { opacity: 0.15; }
            19% { opacity: 1; filter: drop-shadow(0 0 16px #facc15) drop-shadow(0 0 28px #60a5fa); }
            23% { opacity: 0; }
            68% { opacity: 0; }
            70% { opacity: 1; filter: drop-shadow(0 0 14px #facc15); }
            73% { opacity: 0; }
        }

        @keyframes cloud-float {
            0%, 100% { transform: translateX(0px); }
            50% { transform: translateX(10px); }
        }

        @keyframes cloud-float-reverse {
            0%, 100% { transform: translateX(0px); }
            50% { transform: translateX(-8px); }
        }

        @keyframes rain-fall-slow {
            0% { transform: translateY(-6px) translateX(0px); opacity: 0; }
            40% { opacity: 0.9; }
            100% { transform: translateY(14px) translateX(-5px); opacity: 0; }
        }

        @keyframes rain-fall-fast {
            0% { transform: translateY(-8px) translateX(0px); opacity: 0; }
            50% { opacity: 1; }
            100% { transform: translateY(18px) translateX(-6px); opacity: 0; }
        }

        @keyframes sun-glow {
            0%, 100% { transform: scale(1); filter: drop-shadow(0 0 12px rgba(245, 158, 11, 0.65)); }
            50% { transform: scale(1.08); filter: drop-shadow(0 0 24px rgba(245, 158, 11, 0.95)); }
        }

        @keyframes sun-rays-rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        @keyframes mist-drift {
            0%, 100% { opacity: 0.4; transform: translateX(-6px); }
            50% { opacity: 0.85; transform: translateX(6px); }
        }

        .anim-lightning {
            animation: lightning-flash 3.5s infinite ease-in-out;
            transform-origin: center;
        }

        .anim-cloud-1 {
            animation: cloud-float 6s infinite ease-in-out;
        }

        .anim-cloud-2 {
            animation: cloud-float-reverse 5s infinite ease-in-out;
        }

        .anim-rain-1 {
            animation: rain-fall-slow 1.2s infinite linear;
        }

        .anim-rain-2 {
            animation: rain-fall-fast 0.9s infinite linear;
            animation-delay: 0.3s;
        }

        .anim-rain-3 {
            animation: rain-fall-slow 1.1s infinite linear;
            animation-delay: 0.6s;
        }

        .anim-sun-core {
            animation: sun-glow 4s infinite ease-in-out;
            transform-origin: center;
        }

        .anim-sun-rays {
            animation: sun-rays-rotate 24s infinite linear;
            transform-origin: center;
        }

        .anim-mist {
            animation: mist-drift 4s infinite ease-in-out;
        }
    </style>

    <!-- Top Header & Live Radar Banner -->
    <div class="bg-gradient-to-r from-[#38251D] via-[#4E342E] to-[#292421] rounded-2xl p-6 md:p-8 text-white shadow-xl border border-[#C49A55]/30">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex flex-wrap items-center gap-2 mb-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-black bg-[#C49A55] text-slate-900 uppercase tracking-wider shadow-sm">
                        RADAR CUACA MARITIM & BERITA INTELIJEN REAL-TIME
                    </span>
                    <span class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-400 bg-emerald-950/80 px-2.5 py-0.5 rounded-full border border-emerald-500/30">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                        Satelit Terkoneksi Real-Time
                    </span>
                </div>
                <h1 class="text-2xl md:text-3xl font-black tracking-tight text-white">
                    Pusat Notifikasi & Cuaca Maritim Pelabuhan Dunia
                </h1>
                <p class="text-xs md:text-sm text-[#E8D7B9] mt-1.5 max-w-3xl leading-relaxed">
                    Pantau visualisasi atmosfer cuaca pelabuhan maritim internasional (awan mendung, badai awan petir, hujan lebat, dan langit cerah), fluktuasi kurs mata uang dunia (SGD, IDR, BRL, EUR), serta notifikasi operasional kontainer kargo kopi dan kakao.
                </p>
                <div class="mt-2.5 text-[11px] text-[#E8D7B9]/70 flex flex-wrap items-center gap-2">
                    <span>📡 Sumber Kurs: <strong>{{ $fxSource }}</strong></span>
                    <span>&bull;</span>
                    <span>🌊 Sumber Satelit Cuaca: <strong>Open-Meteo Real-Time Marine AIS</strong></span>
                    <span>&bull;</span>
                    <span id="label-last-updated" class="font-mono text-emerald-300">Pembaruan: {{ $generatedAt }}</span>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-wrap items-center gap-2 shrink-0">
                <button type="button" onclick="refreshLiveFeeds()" id="btn-refresh-feeds"
                        class="px-4 py-2.5 bg-[#C49A55] hover:bg-[#b38944] text-[#292421] font-black rounded-xl text-xs transition shadow-lg flex items-center gap-2 cursor-pointer border border-[#f5d998]/40">
                    <span id="refresh-icon" class="text-sm">🔄</span>
                    <span id="refresh-text">Perbarui Real-Time</span>
                </button>

                @if($unreadSystemCount > 0)
                <form action="{{ route('notifications.readAll') }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2.5 bg-white/10 hover:bg-white/20 text-white font-bold rounded-xl text-xs transition border border-white/20 flex items-center gap-1.5">
                        <span>✓</span>
                        <span>Tandai Semua Dibaca</span>
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>

    <!-- Live Toast Notification Feedback -->
    <div id="live-toast" class="hidden transition-all duration-300 p-3.5 bg-emerald-600 text-white text-xs font-bold rounded-xl shadow-xl flex items-center justify-between border border-emerald-400/40">
        <div class="flex items-center gap-2.5">
            <span class="text-base">✅</span>
            <span id="toast-message">Data cuaca pelabuhan dunia & kurs mata uang real-time berhasil diperbarui.</span>
        </div>
        <button type="button" onclick="document.getElementById('live-toast').classList.add('hidden')" class="text-white hover:text-slate-200 text-lg font-black leading-none px-2">&times;</button>
    </div>

    <!-- Real-Time Currency Rates Ticker Bar -->
    <div class="bg-white rounded-2xl p-4 md:p-5 border border-slate-200 shadow-sm space-y-3">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-xl">💱</span>
                <div>
                    <h2 class="text-sm font-black text-slate-900 uppercase tracking-wide">
                        Kurs Valuta Asing Real-Time (Foreign Exchange Ticker)
                    </h2>
                    <p class="text-[11px] text-slate-500">Mata uang utama perdagangan internasional kopi, kakao, & biaya freight perkapalan</p>
                </div>
            </div>
            <span class="text-[10px] font-extrabold uppercase px-2.5 py-0.5 rounded bg-blue-50 text-blue-700 border border-blue-200">
                Base USD ($)
            </span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3" id="currency-ticker-container">
            @foreach($currencyRates as $code => $rate)
            <div class="p-3 rounded-xl border border-slate-200 bg-slate-50 hover:bg-blue-50/50 hover:border-blue-300 transition shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-base">{{ $rate['flag'] }}</span>
                    <span class="text-[10px] font-mono font-bold px-1.5 py-0.5 rounded
                        @if($rate['trend_direction'] === 'up') bg-emerald-100 text-emerald-800
                        @elseif($rate['trend_direction'] === 'down') bg-rose-100 text-rose-800
                        @else bg-slate-200 text-slate-700 @endif">
                        {{ $rate['trend'] }}
                    </span>
                </div>
                <div class="text-[11px] text-slate-500 font-bold mt-1 truncate">{{ $rate['name'] }}</div>
                <div class="text-sm font-black text-slate-900 tracking-tight mt-0.5">{{ $rate['formatted'] }}</div>
                <div class="text-[9px] text-slate-400 font-mono mt-0.5">1 USD = {{ $rate['code'] }}</div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- ========================================================================================= -->
    <!-- VISUAL WEATHER RADAR SHOWCASE: AWAN MENDUNG, AWAN PETIR, HUJAN & CERAH                   -->
    <!-- ========================================================================================= -->
    <div class="bg-slate-900 text-white rounded-3xl p-6 md:p-8 shadow-2xl border border-slate-800 space-y-6 relative overflow-hidden" id="weather-radar-section">
        
        <!-- Subtle Oceanic Background Glow -->
        <div class="absolute -top-32 -right-32 w-96 h-96 bg-blue-600/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-32 -left-32 w-96 h-96 bg-purple-600/10 rounded-full blur-3xl pointer-events-none"></div>

        <!-- Section Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-800 pb-4 relative z-10">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-800 flex items-center justify-center text-xl shadow-inner border border-blue-400/30">
                    🌊
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-base sm:text-lg font-black text-white tracking-wide">
                            Radar Visual Cuaca Pelabuhan Maritim Dunia
                        </h2>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-blue-500/20 text-blue-300 border border-blue-500/30">
                            VISUAL ATMOSFER
                        </span>
                    </div>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Representasi visual awan mendung, awan petir, hujan, dan cerah untuk menjaga mutu muatan kopi dan kakao
                    </p>
                </div>
            </div>

            <!-- Visual Legend Badges -->
            <div class="flex flex-wrap items-center gap-2 text-[11px] text-slate-300">
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-purple-950/70 border border-purple-700/50 text-purple-200">
                    <span class="text-yellow-400">⚡</span> Awan Petir
                </span>
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-800 border border-slate-700 text-slate-300">
                    <span>☁️</span> Awan Mendung
                </span>
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-sky-950/70 border border-sky-800 text-sky-300">
                    <span>🌧️</span> Hujan
                </span>
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-amber-950/60 border border-amber-700 text-amber-300">
                    <span>☀️</span> Cerah
                </span>
            </div>
        </div>

        <!-- Visual Weather Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 relative z-10" id="port-weather-cards-grid">
            @foreach($weatherPorts as $port)
            <div class="rounded-2xl p-5 border relative overflow-hidden transition-all duration-300 hover:scale-[1.02] hover:shadow-2xl flex flex-col justify-between
                @if($port['visual_theme'] === 'thunderstorm')
                    bg-gradient-to-br from-slate-950 via-purple-950/90 to-slate-900 border-purple-700/60 hover:border-purple-400
                @elseif($port['visual_theme'] === 'overcast')
                    bg-gradient-to-br from-slate-900 via-slate-800 to-zinc-900 border-slate-700 hover:border-slate-400
                @elseif($port['visual_theme'] === 'rain')
                    bg-gradient-to-br from-slate-950 via-sky-950/80 to-slate-900 border-sky-800/80 hover:border-sky-400
                @elseif($port['visual_theme'] === 'sunny')
                    bg-gradient-to-br from-[#451a03]/80 via-slate-900 to-slate-950 border-amber-600/50 hover:border-amber-400
                @else
                    bg-gradient-to-br from-slate-900 via-slate-850 to-blue-950 border-blue-800/60 hover:border-blue-400
                @endif">

                <!-- Top Row: Port Info & Country Flag -->
                <div>
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <div class="flex items-center gap-1.5 text-xs font-black text-slate-300">
                                <span class="text-lg">{{ $port['flag'] }}</span>
                                <span class="truncate">{{ $port['city'] }}, {{ $port['country'] }}</span>
                            </div>
                            <h3 class="text-sm font-extrabold text-white mt-1 leading-snug">
                                {{ $port['port_name'] }}
                            </h3>
                            <span class="inline-block mt-1 text-[10px] font-bold px-2 py-0.5 rounded bg-white/10 text-[#E8D7B9] border border-white/10">
                                {{ $port['commodity'] }}
                            </span>
                        </div>

                        <!-- Atmospheric Status Pill -->
                        <span class="text-[9px] font-black uppercase px-2 py-0.5 rounded-full shrink-0
                            @if($port['sea_badge_color'] === 'rose') bg-rose-500/20 text-rose-300 border border-rose-500/40
                            @elseif($port['sea_badge_color'] === 'slate') bg-slate-700/50 text-slate-300 border border-slate-500/40
                            @elseif($port['sea_badge_color'] === 'amber') bg-amber-500/20 text-amber-300 border border-amber-500/40
                            @elseif($port['sea_badge_color'] === 'emerald') bg-emerald-500/20 text-emerald-300 border border-emerald-500/40
                            @else bg-blue-500/20 text-blue-300 border border-blue-500/40 @endif">
                            {{ $port['sea_status'] }}
                        </span>
                    </div>

                    <!-- Center Animated Weather Illustration & Big Temperature Display -->
                    <div class="my-5 flex items-center justify-between gap-2 py-2 px-3 rounded-xl bg-black/25 backdrop-blur-sm border border-white/5">
                        
                        <!-- Rich SVG Animated Weather Scene -->
                        <div class="w-24 h-20 flex items-center justify-center shrink-0">
                            @if($port['visual_theme'] === 'thunderstorm')
                                <!-- AWAN PETIR: Dark Storm Cloud with Flashing Lightning & Rain Streaks -->
                                <svg class="w-22 h-20 drop-shadow-xl" viewBox="0 0 100 80" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <!-- Background Cloud Shadow -->
                                    <path class="anim-cloud-2" opacity="0.6" d="M22 36a14 14 0 0112-13.8 17 17 0 0133 1.3A13 13 0 0178 36H22z" fill="#475569"/>
                                    <!-- Dark Storm Cloud -->
                                    <path class="anim-cloud-1" d="M14 42a15 15 0 0113-14.7 18 18 0 0135 1.4A14 14 0 0174 42H14z" fill="#1e293b"/>
                                    <path d="M18 44a12 12 0 0110-11.8 15 15 0 0129 1.2A11 11 0 0166 44H18z" fill="#0f172a"/>
                                    
                                    <!-- Animated Glowing Lightning Bolt -->
                                    <polygon points="46,38 34,55 43,55 38,76 57,50 48,50" class="anim-lightning" fill="#facc15" stroke="#fef08a" stroke-width="1.5"/>
                                    
                                    <!-- Electric Rain Streaks -->
                                    <line x1="22" y1="50" x2="16" y2="66" stroke="#38bdf8" stroke-width="2" stroke-linecap="round" class="anim-rain-1"/>
                                    <line x1="64" y1="48" x2="58" y2="64" stroke="#38bdf8" stroke-width="2" stroke-linecap="round" class="anim-rain-2"/>
                                    <line x1="74" y1="52" x2="68" y2="68" stroke="#38bdf8" stroke-width="2" stroke-linecap="round" class="anim-rain-3"/>
                                </svg>

                            @elseif($port['visual_theme'] === 'overcast')
                                <!-- AWAN MENDUNG: Layered Dense Overcast Clouds & Maritime Fog -->
                                <svg class="w-22 h-20 drop-shadow-md" viewBox="0 0 100 70" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <!-- Rear Dense Grey Cloud -->
                                    <path class="anim-cloud-2" opacity="0.6" d="M26 38a16 16 0 0114-15.7 19 19 0 0137 1.5A15 15 0 0186 38H26z" fill="#475569"/>
                                    <!-- Front Overcast Charcoal Cloud -->
                                    <path class="anim-cloud-1" d="M14 46a15 15 0 0113-14.7 18 18 0 0135 1.4A14 14 0 0174 46H14z" fill="#64748b"/>
                                    <path d="M18 48a12 12 0 0110-11.8 15 15 0 0129 1.2A11 11 0 0166 48H18z" fill="#475569"/>
                                    <!-- Maritime Fog / Mist Haze -->
                                    <g class="anim-mist">
                                        <line x1="12" y1="56" x2="78" y2="56" stroke="#94a3b8" stroke-width="2.5" stroke-linecap="round" stroke-dasharray="6 4" opacity="0.7"/>
                                        <line x1="20" y1="62" x2="86" y2="62" stroke="#cbd5e1" stroke-width="2" stroke-linecap="round" stroke-dasharray="4 6" opacity="0.5"/>
                                    </g>
                                </svg>

                            @elseif($port['visual_theme'] === 'rain')
                                <!-- AWAN HUJAN: Rain Cloud with Diagonal Rain Streaks -->
                                <svg class="w-22 h-20 drop-shadow-md" viewBox="0 0 100 70" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path class="anim-cloud-1" d="M16 38a14 14 0 0112-13.8 17 17 0 0133 1.3A13 13 0 0172 38H16z" fill="#475569"/>
                                    <path d="M20 40a11 11 0 019.5-10.8 14 14 0 0127 1.1A10.5 10.5 0 0164 40H20z" fill="#334155"/>
                                    <!-- Animated Rain Streaks -->
                                    <line x1="26" y1="46" x2="20" y2="64" stroke="#60a5fa" stroke-width="2.5" stroke-linecap="round" class="anim-rain-1"/>
                                    <line x1="38" y1="44" x2="32" y2="62" stroke="#60a5fa" stroke-width="2.5" stroke-linecap="round" class="anim-rain-2"/>
                                    <line x1="50" y1="47" x2="44" y2="65" stroke="#60a5fa" stroke-width="2.5" stroke-linecap="round" class="anim-rain-3"/>
                                    <line x1="62" y1="44" x2="56" y2="62" stroke="#60a5fa" stroke-width="2.5" stroke-linecap="round" class="anim-rain-1"/>
                                </svg>

                            @elseif($port['visual_theme'] === 'sunny')
                                <!-- LANGIT CERAH: Radiant Glowing Sun with Rotating Beams -->
                                <svg class="w-22 h-20 drop-shadow-xl" viewBox="0 0 100 70" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="50" cy="35" r="16" fill="#f59e0b" class="anim-sun-core"/>
                                    <circle cx="50" cy="35" r="23" stroke="#fbbf24" stroke-width="1.5" stroke-dasharray="4 4" class="anim-sun-core" opacity="0.8"/>
                                    <!-- Rotating Solar Rays -->
                                    <g class="anim-sun-rays" stroke="#fbbf24" stroke-width="2.5" stroke-linecap="round">
                                        <line x1="50" y1="6" x2="50" y2="14"/>
                                        <line x1="50" y1="56" x2="50" y2="64"/>
                                        <line x1="21" y1="35" x2="29" y2="35"/>
                                        <line x1="71" y1="35" x2="79" y2="35"/>
                                        <line x1="29" y1="14" x2="35" y2="20"/>
                                        <line x1="65" y1="50" x2="71" y2="56"/>
                                        <line x1="29" y1="56" x2="35" y2="50"/>
                                        <line x1="65" y1="20" x2="71" y2="14"/>
                                    </g>
                                </svg>

                            @else
                                <!-- CERAH BERAWAN: Sun with Drifting White Cloud -->
                                <svg class="w-22 h-20 drop-shadow-md" viewBox="0 0 100 70" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="62" cy="28" r="14" fill="#f59e0b" class="anim-sun-core"/>
                                    <path class="anim-cloud-1" d="M16 46a14 14 0 0112-13.8 17 17 0 0133 1.3A13 13 0 0172 46H16z" fill="#94a3b8"/>
                                    <path d="M20 48a11 11 0 019.5-10.8 14 14 0 0127 1.1A10.5 10.5 0 0164 48H20z" fill="#cbd5e1"/>
                                </svg>
                            @endif
                        </div>

                        <!-- Temperature & Condition Label -->
                        <div class="text-right">
                            <div class="text-3xl font-black text-white tracking-tighter">
                                {{ round($port['temperature'], 1) }}°C
                            </div>
                            <div class="text-[11px] font-bold text-slate-300 mt-0.5 line-clamp-1">
                                {{ $port['condition'] }}
                            </div>
                            <div class="text-[10px] text-slate-400 font-mono mt-0.5">
                                {{ $port['theme_label'] }}
                            </div>
                        </div>
                    </div>

                    <!-- Environmental Meters (Humidity, Wind, Status) -->
                    <div class="grid grid-cols-2 gap-2 text-[11px] text-slate-300 mb-3">
                        <div class="flex items-center gap-1.5 p-2 rounded-lg bg-white/5 border border-white/5">
                            <span class="text-blue-400">💧</span>
                            <div>
                                <div class="text-[9px] text-slate-400 uppercase font-semibold">Kelembapan</div>
                                <div class="font-black text-white">{{ $port['humidity'] }}%</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5 p-2 rounded-lg bg-white/5 border border-white/5">
                            <span class="text-teal-400">💨</span>
                            <div>
                                <div class="text-[9px] text-slate-400 uppercase font-semibold">Kecepatan Angin</div>
                                <div class="font-black text-white">{{ round($port['wind_speed'], 1) }} km/h</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bottom Advisory Box for Cargo Coffee & Cocoa Moisture Protection -->
                <div class="pt-3 border-t border-white/10 text-[11px] text-slate-300 leading-relaxed">
                    <p class="italic text-slate-300 line-clamp-2">
                        "{{ $port['advisory'] }}"
                    </p>
                </div>

            </div>
            @endforeach
        </div>

    </div>

    <!-- Filter Navigation Tabs -->
    <div class="flex flex-wrap items-center gap-2 border-b border-slate-200 pb-2">
        <button type="button" onclick="switchTab('ALL')" id="tab-btn-ALL"
                class="tab-btn px-4 py-2 rounded-xl text-xs font-black transition bg-blue-600 text-white shadow-sm flex items-center gap-1.5 cursor-pointer">
            <span>🌐</span> Semua Berita & Notifikasi (<span id="count-all">{{ count($newsItems) + $notifications->total() }}</span>)
        </button>
        <button type="button" onclick="switchTab('CURRENCY')" id="tab-btn-CURRENCY"
                class="tab-btn px-4 py-2 rounded-xl text-xs font-bold transition bg-white text-slate-700 border border-slate-200 hover:bg-slate-50 flex items-center gap-1.5 cursor-pointer">
            <span>💱</span> Berita Kurs & Valuta
        </button>
        <button type="button" onclick="switchTab('WEATHER')" id="tab-btn-WEATHER"
                class="tab-btn px-4 py-2 rounded-xl text-xs font-bold transition bg-white text-slate-700 border border-slate-200 hover:bg-slate-50 flex items-center gap-1.5 cursor-pointer">
            <span>🌊</span> Berita Cuaca Pelabuhan Dunia
        </button>
        <button type="button" onclick="switchTab('SYSTEM')" id="tab-btn-SYSTEM"
                class="tab-btn px-4 py-2 rounded-xl text-xs font-bold transition bg-white text-slate-700 border border-slate-200 hover:bg-slate-50 flex items-center gap-1.5 cursor-pointer">
            <span>📦</span> Notifikasi Operasional Kargo ({{ $notifications->total() }})
            @if($unreadSystemCount > 0)
                <span class="w-2 h-2 rounded-full bg-rose-500"></span>
            @endif
        </button>
    </div>

    <!-- Main Feed Items List -->
    <div class="space-y-4" id="news-feed-container">

        <!-- 1. Real-Time Market & Weather Intelligence News Items -->
        @foreach($newsItems as $news)
        <div class="news-feed-card p-4 sm:p-5 rounded-2xl border border-slate-200 bg-white hover:border-blue-400 transition shadow-sm"
             data-category="{{ $news['category'] }}">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-start gap-3.5">

                    <!-- Visual Icon / Mini Weather Illustration for News Items -->
                    @if($news['category'] === 'WEATHER')
                        <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0 shadow-inner border overflow-hidden
                            @if(($news['visual_theme'] ?? '') === 'thunderstorm') bg-purple-950 border-purple-800
                            @elseif(($news['visual_theme'] ?? '') === 'overcast') bg-slate-800 border-slate-700
                            @elseif(($news['visual_theme'] ?? '') === 'rain') bg-sky-950 border-sky-800
                            @elseif(($news['visual_theme'] ?? '') === 'sunny') bg-amber-950 border-amber-800
                            @else bg-slate-900 border-slate-800 @endif">
                            
                            @if(($news['visual_theme'] ?? '') === 'thunderstorm')
                                <svg class="w-8 h-8" viewBox="0 0 100 80" fill="none">
                                    <path d="M14 42a15 15 0 0113-14.7 18 18 0 0135 1.4A14 14 0 0174 42H14z" fill="#475569"/>
                                    <polygon points="46,38 34,55 43,55 38,76 57,50 48,50" class="anim-lightning" fill="#facc15"/>
                                </svg>
                            @elseif(($news['visual_theme'] ?? '') === 'overcast')
                                <svg class="w-8 h-8" viewBox="0 0 100 70" fill="none">
                                    <path d="M14 46a15 15 0 0113-14.7 18 18 0 0135 1.4A14 14 0 0174 46H14z" fill="#64748b"/>
                                    <line x1="12" y1="56" x2="78" y2="56" stroke="#94a3b8" stroke-width="4" stroke-linecap="round" stroke-dasharray="6 4" class="anim-mist"/>
                                </svg>
                            @elseif(($news['visual_theme'] ?? '') === 'rain')
                                <svg class="w-8 h-8" viewBox="0 0 100 70" fill="none">
                                    <path d="M16 38a14 14 0 0112-13.8 17 17 0 0133 1.3A13 13 0 0172 38H16z" fill="#64748b"/>
                                    <line x1="26" y1="46" x2="20" y2="64" stroke="#60a5fa" stroke-width="3" stroke-linecap="round" class="anim-rain-1"/>
                                    <line x1="50" y1="47" x2="44" y2="65" stroke="#60a5fa" stroke-width="3" stroke-linecap="round" class="anim-rain-2"/>
                                </svg>
                            @elseif(($news['visual_theme'] ?? '') === 'sunny')
                                <svg class="w-8 h-8" viewBox="0 0 100 70" fill="none">
                                    <circle cx="50" cy="35" r="16" fill="#f59e0b" class="anim-sun-core"/>
                                    <circle cx="50" cy="35" r="23" stroke="#fbbf24" stroke-width="2" stroke-dasharray="4 4"/>
                                </svg>
                            @else
                                <span class="text-xl">{{ $news['icon'] }}</span>
                            @endif
                        </div>
                    @else
                        <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center text-2xl shrink-0 shadow-inner">
                            {{ $news['icon'] }}
                        </div>
                    @endif

                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase
                                @if($news['badge_color'] === 'emerald') bg-emerald-100 text-emerald-800 border border-emerald-200
                                @elseif($news['badge_color'] === 'rose') bg-rose-100 text-rose-800 border border-rose-200
                                @elseif($news['badge_color'] === 'amber') bg-amber-100 text-amber-800 border border-amber-200
                                @elseif($news['badge_color'] === 'slate') bg-slate-200 text-slate-800 border border-slate-300
                                @else bg-blue-100 text-blue-800 border border-blue-200 @endif">
                                {{ $news['badge'] }}
                            </span>
                            <span class="text-xs text-slate-400 font-semibold">&bull; {{ $news['source'] }}</span>
                            <span class="text-xs text-slate-400 font-mono">({{ $news['timestamp'] }})</span>
                        </div>

                        <h3 class="text-sm sm:text-base font-extrabold text-slate-900 mt-1">
                            {{ $news['title'] }}
                        </h3>

                        <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                            {{ $news['summary'] }}
                        </p>

                        <div class="mt-2.5 flex flex-wrap items-center gap-3 text-[11px]">
                            <span class="font-bold text-slate-500">Kategori: <strong class="text-slate-800">{{ $news['category_label'] }}</strong></span>
                            @if(!empty($news['theme_label']))
                            <span>&bull;</span>
                            <span class="text-indigo-600 font-bold">{{ $news['theme_label'] }}</span>
                            @endif
                            @if(!empty($news['action_label']))
                            <span>&bull;</span>
                            <span class="text-blue-600 font-bold hover:underline cursor-pointer" onclick="scrollToWeatherRadar()">
                                {{ $news['action_label'] }} &rarr;
                            </span>
                            @endif
                        </div>
                    </div>
                </div>

                <span class="px-2.5 py-0.5 rounded text-[9px] font-black uppercase shrink-0
                    @if($news['impact_level'] === 'CRITICAL') bg-rose-600 text-white
                    @elseif($news['impact_level'] === 'HIGH') bg-amber-500 text-slate-900
                    @else bg-slate-200 text-slate-700 @endif">
                    {{ $news['impact_level'] }}
                </span>
            </div>
        </div>
        @endforeach

        <!-- 2. System Operational Cargo Notifications (Database) -->
        @foreach($notifications as $notif)
        <div class="news-feed-card p-4 sm:p-5 rounded-2xl border border-slate-200 {{ $notif->is_read ? 'bg-white' : 'bg-blue-50/40 border-blue-300' }} transition shadow-sm"
             data-category="SYSTEM">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-start gap-3.5">
                    <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center text-xl shrink-0 shadow-inner">
                        @if($notif->type === 'DELAY_ALERT') ⏱️
                        @elseif($notif->type === 'EXCEPTION_ALERT') ⚠️
                        @else 📦 @endif
                    </div>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase
                                @if($notif->type === 'DELAY_ALERT') bg-amber-100 text-amber-800 border border-amber-300
                                @elseif($notif->type === 'EXCEPTION_ALERT') bg-rose-100 text-rose-800 border border-rose-300
                                @else bg-blue-100 text-blue-800 border border-blue-300 @endif">
                                {{ $notif->type }}
                            </span>
                            <span class="text-xs text-slate-400">{{ $notif->created_at->diffForHumans() }}</span>
                        </div>

                        <h3 class="text-sm sm:text-base font-extrabold text-slate-900 mt-1">
                            {{ $notif->title }}
                        </h3>

                        <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                            {{ $notif->message }}
                        </p>

                        @if($notif->shipment_id)
                        <div class="mt-2.5">
                            <a href="{{ route('shipments.show', $notif->shipment_id) }}" class="text-xs font-bold text-blue-600 hover:text-blue-800 hover:underline flex items-center gap-1">
                                <span>Periksa Status & Peta Kargo</span>
                                <span>&rarr;</span>
                            </a>
                        </div>
                        @endif
                    </div>
                </div>

                @if(!$notif->is_read)
                <form action="{{ route('notifications.read', $notif->id) }}" method="POST" class="shrink-0">
                    @csrf
                    <button type="submit" class="px-2.5 py-1 text-xs text-slate-500 hover:text-slate-800 bg-slate-100 hover:bg-slate-200 rounded-lg font-bold transition">
                        Tandai Dibaca
                    </button>
                </form>
                @endif
            </div>
        </div>
        @endforeach

        @if(count($newsItems) === 0 && $notifications->isEmpty())
        <div class="p-12 text-center text-slate-400 text-sm bg-white rounded-2xl border border-slate-200">
            Tidak ada berita atau notifikasi saat ini.
        </div>
        @endif

    </div>

    <!-- Paged Navigation for System Notifications -->
    @if($notifications->hasPages())
    <div class="p-2">
        {{ $notifications->links() }}
    </div>
    @endif

</div>
@endsection

@section('scripts')
<script>
    /**
     * Switch category filter tabs
     */
    function switchTab(cat) {
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('bg-blue-600', 'text-white', 'shadow-sm');
            btn.classList.add('bg-white', 'text-slate-700', 'border', 'border-slate-200');
        });

        const activeBtn = document.getElementById('tab-btn-' + cat);
        if (activeBtn) {
            activeBtn.classList.add('bg-blue-600', 'text-white', 'shadow-sm');
            activeBtn.classList.remove('bg-white', 'text-slate-700', 'border-slate-200');
        }

        const cards = document.querySelectorAll('.news-feed-card');
        cards.forEach(card => {
            const cardCat = card.getAttribute('data-category');
            if (cat === 'ALL' || cardCat === cat) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });

        // When user clicks WEATHER tab, ensure weather radar section is highlighted
        const radarSection = document.getElementById('weather-radar-section');
        if (radarSection) {
            if (cat === 'CURRENCY' || cat === 'SYSTEM') {
                radarSection.style.display = 'none';
            } else {
                radarSection.style.display = 'block';
            }
        }
    }

    function scrollToWeatherRadar() {
        const elem = document.getElementById('weather-radar-section');
        if (elem) {
            elem.scrollIntoView({ behavior: 'smooth' });
        }
    }

    /**
     * Refresh live market & weather intelligence feeds via AJAX Fetch API
     */
    async function refreshLiveFeeds() {
        const btn = document.getElementById('btn-refresh-feeds');
        const icon = document.getElementById('refresh-icon');
        const text = document.getElementById('refresh-text');
        const toast = document.getElementById('live-toast');
        const toastMsg = document.getElementById('toast-message');

        try {
            if (icon) icon.classList.add('animate-spin');
            if (text) text.innerText = 'Mengambil Data Real-Time...';
            btn.disabled = true;

            const response = await fetch("{{ route('notifications.liveFeeds') }}?refresh=1", {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.ok) {
                const res = await response.json();
                if (res.status === 'success' && res.data) {
                    const data = res.data;

                    // Update timestamp
                    const updatedElem = document.getElementById('label-last-updated');
                    if (updatedElem) {
                        updatedElem.innerText = `Pembaruan: ${data.generated_at}`;
                    }

                    // Show success toast
                    if (toast && toastMsg) {
                        toastMsg.innerText = 'Berhasil memperbarui kurs SGD, IDR, BRL, EUR & cuaca visual pelabuhan dunia secara real-time!';
                        toast.classList.remove('hidden');
                        setTimeout(() => toast.classList.add('hidden'), 5000);
                    }

                    // Reload page smoothly to reflect fresh cards & themes
                    setTimeout(() => {
                        window.location.reload();
                    }, 800);
                }
            } else {
                alert('Gagal mengambil data real-time terbaru. Silakan coba kembali.');
            }
        } catch (err) {
            console.error('Error fetching real-time feeds:', err);
        } finally {
            if (icon) icon.classList.remove('animate-spin');
            if (text) text.innerText = 'Perbarui Real-Time';
            btn.disabled = false;
        }
    }
</script>
@endsection
