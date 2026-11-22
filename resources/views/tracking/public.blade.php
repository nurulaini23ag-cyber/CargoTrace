@extends('layouts.app')

@section('title', 'Sedang Dikirim — Pelacakan Kargo Shopee-Style — CargoTrace')

@section('content')
<div class="max-w-4xl mx-auto space-y-4 font-sans">

    <!-- Top Bar Shopee-Style Header -->
    <div class="bg-white rounded-2xl px-5 py-3.5 border border-slate-200 shadow-sm flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('tracking.public') }}" class="w-9 h-9 rounded-full hover:bg-slate-100 flex items-center justify-center text-slate-700 text-lg transition" title="Kembali">
                &larr;
            </a>
            <div>
                <div class="flex items-center gap-2 mb-0.5">
                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-orange-100 text-orange-800 uppercase tracking-wider">
                        PUBLIC TRACKING PORTAL
                    </span>
                    <span class="text-[11px] text-slate-500 font-medium hidden sm:inline">
                        Lacak Posisi Kargo Kapal Laut Anda
                    </span>
                </div>
                <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">
                    Sedang Dikirim
                </h1>
                <p class="text-[11px] text-slate-500">Live Maritime Courier Tracking &bull; CargoTrace</p>
            </div>
        </div>

        <div class="flex items-center gap-3 text-slate-500">
            <!-- Customer Support Headset Icon -->
            <button type="button" class="w-9 h-9 rounded-full hover:bg-slate-100 flex items-center justify-center text-orange-600 transition" title="Layanan Bantuan Pelanggan">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243 4.243a9 9 0 01-12.728 0m0 0l2.829-2.829m-2.829 2.829L3 21m5.636-12.536a5 5 0 017.072 0"/>
                </svg>
            </button>
            <!-- Help Icon -->
            <button type="button" class="w-9 h-9 rounded-full hover:bg-slate-100 flex items-center justify-center text-slate-600 text-base font-bold transition" title="Pusat Bantuan">
                ?
            </button>
        </div>
    </div>

    <!-- Tracking Number Search Bar (if not provided or wanting to change) -->
    <div class="bg-white p-3.5 rounded-2xl border border-slate-200 shadow-sm">
        <form action="{{ route('tracking.public') }}" method="GET" class="flex items-center gap-2">
            <div class="relative flex-1">
                <input type="text" name="q" value="{{ $trackingNumber }}"
                       placeholder="Masukkan nomor pengiriman kargo (contoh: CTC-2026-0001)..."
                       class="w-full px-4 py-2.5 pl-10 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 placeholder-slate-400 text-xs sm:text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-orange-500 shadow-inner" required>
                <span class="absolute left-3.5 top-3 text-slate-400 text-sm">🔍</span>
            </div>
            <button type="submit" class="px-5 py-2.5 bg-orange-600 hover:bg-orange-700 text-white font-extrabold rounded-xl text-xs sm:text-sm transition shadow-sm flex items-center gap-1.5">
                <span>Cari</span>
            </button>
        </form>

        <!-- Quick Demo Pills -->
        <div class="mt-2.5 flex flex-wrap items-center gap-1.5 text-[11px]">
            <span class="text-slate-400 font-bold">Contoh Cepat:</span>
            <a href="{{ route('tracking.public', ['q' => 'CTC-2026-0001']) }}" class="px-2 py-0.5 rounded bg-orange-50 hover:bg-orange-100 text-orange-700 border border-orange-200 transition font-mono font-bold">
                ☕ CTC-2026-0001 (Arabica to Rotterdam)
            </a>
            <a href="{{ route('tracking.public', ['q' => 'CTC-2026-0002']) }}" class="px-2 py-0.5 rounded bg-orange-50 hover:bg-orange-100 text-orange-700 border border-orange-200 transition font-mono font-bold">
                🍫 CTC-2026-0002 (Cocoa to Hamburg)
            </a>
            <a href="{{ route('tracking.public', ['q' => 'CTC-2026-0005']) }}" class="px-2 py-0.5 rounded bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 transition font-mono font-bold">
                ✅ CTC-2026-0005 (Delivered Genoa)
            </a>
        </div>
    </div>

    @if($trackingNumber && !$shipment)
        <!-- Not Found State -->
        <div class="bg-white p-8 rounded-2xl border border-rose-200 text-center shadow-sm">
            <div class="w-16 h-16 bg-rose-50 text-rose-500 rounded-full flex items-center justify-center mx-auto text-2xl font-bold mb-3">
                ⚠️
            </div>
            <h3 class="text-base font-bold text-slate-900">Kargo Tidak Ditemukan</h3>
            <p class="text-xs text-slate-500 mt-1 max-w-md mx-auto">
                Nomor pengiriman <span class="font-mono font-bold text-rose-600">{{ $trackingNumber }}</span> tidak terdaftar dalam sistem logistik maritim CargoTrace. Silakan periksa kembali nomor B/L Anda.
            </p>
        </div>
    @elseif($shipment && $currentPos)

        <!-- SHOPEE-STYLE CONTAINER VIEWPORT -->
        <div class="bg-slate-900 rounded-3xl overflow-hidden shadow-2xl border border-slate-800 relative">

            <!-- Shopee Step-by-Step Simulator Bar -->
            <div class="px-4 py-2.5 bg-slate-950/80 backdrop-blur-md text-white flex flex-wrap items-center justify-between gap-2 text-xs border-b border-slate-800/80 z-20 relative">
                <div class="flex items-center gap-2">
                    <span class="text-[10px] font-black uppercase tracking-wider text-orange-400 flex items-center gap-1">
                        <span>⚡</span> SIMULASI KAPAL STEP-BY-STEP
                    </span>
                    <button id="shopee-play-btn" type="button" class="px-3 py-1 rounded-lg bg-orange-600 hover:bg-orange-700 text-white font-extrabold text-[11px] transition flex items-center gap-1 shadow-sm">
                        <span id="shopee-play-icon">▶️</span> <span id="shopee-play-text">Putar Step-by-Step</span>
                    </button>
                    <button id="shopee-reset-btn" type="button" class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white font-bold text-[11px] transition flex items-center gap-1">
                        <span>🔄</span> Awal
                    </button>
                </div>

                <div class="flex items-center gap-2">
                    <span class="text-[10px] text-slate-400 font-bold hidden sm:inline">Peta Dunia (Leaflet + OSM):</span>
                    <select id="shopee-map-style" onchange="changeShopeeTheme(this.value)" class="bg-slate-800 text-white border border-slate-700 rounded-lg px-2.5 py-1 text-[11px] font-semibold focus:outline-none focus:ring-1 focus:ring-orange-500 cursor-pointer">
                        <option value="osm" selected>🗺️ Google / OpenStreetMap Standard (PPT Slide 09)</option>
                        <option value="satellite">🛰️ Citra Satelit Laut</option>
                        <option value="voyager">🌊 Peta Maritim Pelayaran</option>
                        <option value="dark">🌙 Dark Maritime Chart</option>
                    </select>
                </div>
            </div>

            <!-- MAP CANVAS (Deep Marine Dark Navy) -->
            <div class="relative w-full">
                <!-- Top Left Status Floating Pill -->
                <div class="absolute top-4 left-4 z-20 bg-slate-950/80 backdrop-blur-md text-white px-3.5 py-2 rounded-2xl border border-white/10 shadow-xl text-xs flex items-center gap-2 pointer-events-none">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping"></span>
                    <div>
                        <div class="text-[9px] font-extrabold uppercase tracking-wider text-orange-400">STATUS MARITIM:</div>
                        <div class="font-extrabold text-white text-xs" id="shopee-floating-status">{{ $shipment->current_status }}</div>
                    </div>
                </div>

                <!-- Floating Crosshair GPS Target Button (Bottom-Right, just like Shopee Screenshot!) -->
                <button id="btn-recenter-ship" type="button" class="absolute bottom-6 right-5 z-20 w-11 h-11 bg-white hover:bg-slate-100 text-slate-800 rounded-full shadow-2xl flex items-center justify-center border border-slate-300 transition transform hover:scale-105" title="Pusatkan Kamera ke Kapal">
                    <svg class="w-6 h-6 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="7" stroke-width="2"/>
                        <circle cx="12" cy="12" r="2" fill="currentColor"/>
                        <path stroke-linecap="round" stroke-width="2" d="M12 2v3m0 14v3M2 12h3m14 0h3"/>
                    </svg>
                </button>

                <!-- Leaflet Map Container -->
                <div id="shopee-tracking-map" class="h-[460px] w-full z-10"></div>
            </div>

            <!-- SHOPEE BOTTOM SHEET TRACKING CARDS (Exactly matching the screenshot!) -->
            <div class="bg-white p-5 space-y-4 text-slate-900 border-t border-slate-200">

                <!-- CARD 1: Estimasi Tiba & 3-Step Horizontal Timeline -->
                <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm space-y-3">
                    <!-- Green Headline -->
                    <div class="flex items-center justify-between">
                        <div class="text-base sm:text-lg font-extrabold text-emerald-700 tracking-tight flex items-center gap-2">
                            <span>Estimasi tiba:</span>
                            <span>
                                {{ $shipment->expected_arrival ? $shipment->expected_arrival->format('d M') : '23 Nov' }} –
                                {{ $shipment->expected_arrival ? $shipment->expected_arrival->addDays(2)->format('d M') : '25 Nov' }}
                            </span>
                        </div>
                        <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider
                            @if($shipment->current_status === 'DELIVERED') bg-emerald-100 text-emerald-800
                            @elseif($shipment->current_status === 'IN_TRANSIT') bg-orange-100 text-orange-800
                            @else bg-slate-100 text-slate-700 @endif">
                            {{ $shipment->current_status }}
                        </span>
                    </div>

                    <!-- 3-Step Horizontal Progress Bar -->
                    <div class="pt-2">
                        <div class="flex items-center justify-between relative">
                            <!-- Progress line background -->
                            <div class="absolute left-6 right-6 top-4 h-1 bg-slate-200 -z-0"></div>
                            <!-- Active progress line filled -->
                            <div class="absolute left-6 top-4 h-1 bg-emerald-500 -z-0 transition-all duration-700"
                                 style="width: {{ $shipment->current_status === 'DELIVERED' ? '88%' : ($shipment->current_status === 'IN_TRANSIT' ? '45%' : '15%') }};"></div>

                            <!-- Step 1: Sedang Dikirim (Box icon) -->
                            <div class="flex flex-col items-center text-center z-10 cursor-pointer" onclick="jumpToStep(0)">
                                <div class="w-8 h-8 rounded-full bg-emerald-600 text-white flex items-center justify-center text-sm font-bold shadow-md">
                                    📦
                                </div>
                                <span class="text-[11px] font-bold text-emerald-700 mt-1.5">Sedang Dikirim</span>
                            </div>

                            <!-- Step 2: Menuju Pelabuhan / Alamatmu (Ship icon) -->
                            <div class="flex flex-col items-center text-center z-10 cursor-pointer" onclick="jumpToStep(1)">
                                <div class="w-8 h-8 rounded-full {{ in_array($shipment->current_status, ['IN_TRANSIT', 'DELIVERED']) ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-500' }} flex items-center justify-center text-sm font-bold shadow-sm">
                                    🚢
                                </div>
                                <span class="text-[11px] font-bold {{ in_array($shipment->current_status, ['IN_TRANSIT', 'DELIVERED']) ? 'text-emerald-700' : 'text-slate-400' }} mt-1.5">
                                    Menuju Pelabuhan
                                </span>
                            </div>

                            <!-- Step 3: Pesanan Tiba (Checkmark) -->
                            <div class="flex flex-col items-center text-center z-10 cursor-pointer" onclick="jumpToStep(2)">
                                <div class="w-8 h-8 rounded-full {{ $shipment->current_status === 'DELIVERED' ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-400' }} flex items-center justify-center text-sm font-bold shadow-sm">
                                    ✓
                                </div>
                                <span class="text-[11px] font-bold {{ $shipment->current_status === 'DELIVERED' ? 'text-emerald-700' : 'text-slate-400' }} mt-1.5">
                                    Pesanan Tiba
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Live Position & Coordinates -->
                    <div class="mt-4 pt-3 border-t border-slate-100 flex flex-wrap items-center justify-between text-xs text-slate-600 gap-2">
                        <div class="flex items-center gap-1.5">
                            <span class="text-rose-500 font-bold">📍</span>
                            <span class="font-extrabold text-slate-800 uppercase tracking-wide">POSISI KARGO SAAT INI:</span>
                            <span class="font-bold text-orange-600">{{ $currentPos['location_name'] }}</span>
                        </div>
                        <div class="font-mono text-[11px] bg-slate-100 px-2 py-0.5 rounded text-slate-600">
                            Lat: <span class="font-bold text-slate-800">{{ $currentPos['latitude'] }}</span> &bull; Lon: <span class="font-bold text-slate-800">{{ $currentPos['longitude'] }}</span>
                        </div>
                    </div>
                </div>

                <!-- CARD 2: Garansi Tiba SLA Banner (Direct from Shopee Screenshot) -->
                <div class="bg-emerald-50/70 border border-emerald-200/80 rounded-2xl p-3.5 flex items-center gap-3 text-xs text-slate-800">
                    <span class="text-lg text-emerald-600 shrink-0">⏱️</span>
                    <div class="flex-1 leading-snug">
                        <span class="font-extrabold text-slate-900">Garansi Tiba:</span>
                        Jaminan Tepat Waktu SLA Maritim & Mutu Kargo Biji Kopi/Kakao Terjaga.
                        <a href="{{ route('settings.index') }}" class="text-emerald-700 font-bold underline ml-1">Selengkapnya</a>
                    </div>
                </div>

                <!-- CARD 3: Service Card Header + Vertical Step-by-Step Milestone Timeline -->
                <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm space-y-4">
                    <!-- Service Header (Thumb, Service name, Tracking number, Rincian Pesanan) -->
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl bg-orange-50 border border-orange-200 flex items-center justify-center text-2xl shadow-inner shrink-0">
                                @if(str_contains(strtoupper($shipment->cargo_type), 'COCOA')) 🍫 @else ☕ @endif
                            </div>
                            <div>
                                <div class="text-xs font-extrabold text-slate-900">
                                    CargoTrace Ocean Express
                                </div>
                                <div class="text-[11px] text-slate-500 font-mono flex items-center gap-1.5 mt-0.5">
                                    <span>{{ $shipment->shipment_number }}</span>
                                    <button type="button" onclick="navigator.clipboard.writeText('{{ $shipment->shipment_number }}'); alert('Nomor pengiriman disalin!');" class="text-slate-400 hover:text-slate-700" title="Salin Nomor Pengiriman">
                                        📋
                                    </button>
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('shipments.show', $shipment->id) }}" class="px-3 py-1.5 rounded-lg border border-slate-300 text-xs font-bold text-slate-700 hover:bg-slate-50 transition">
                            Rincian Pesanan
                        </a>
                    </div>

                    <!-- Vertical Step-by-Step Live Milestone Timeline -->
                    <div class="space-y-4 relative before:absolute before:inset-0 before:left-2.5 before:w-0.5 before:bg-slate-200 pl-1">
                        <!-- Current Active Step (Teal Highlight) -->
                        <div class="relative flex items-start gap-4 cursor-pointer" onclick="jumpToCurrentRealPosition()">
                            <span class="w-5 h-5 rounded-full bg-emerald-500 border-4 border-emerald-100 mt-0.5 z-10 shrink-0 shadow-xs"></span>
                            <div class="flex-1">
                                <div class="text-xs font-bold text-emerald-700 leading-snug">
                                    {{ $currentPos['event_description'] ?? 'Kapal sedang berlayar menyusuri alur laut internasional.' }}
                                </div>
                                <div class="text-[11px] text-slate-500 mt-0.5 flex items-center gap-2">
                                    <span>Hari ini {{ now()->format('H:i') }}</span>
                                    <span>&bull;</span>
                                    <span class="font-semibold text-slate-700">{{ $currentPos['location_name'] }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Intermediate Checkpoints -->
                        @foreach($shipment->trackingEvents as $ev)
                        <div class="relative flex items-start gap-4 cursor-pointer" onclick="jumpToWaypoint({{ $ev->latitude ?? $currentPos['latitude'] }}, {{ $ev->longitude ?? $currentPos['longitude'] }}, '{{ addslashes($ev->location_name) }}')">
                            <span class="w-5 h-5 rounded-full bg-slate-300 border-4 border-slate-100 mt-0.5 z-10 shrink-0"></span>
                            <div class="flex-1">
                                <div class="text-xs font-semibold text-slate-700 leading-snug">
                                    {{ $ev->description }}
                                </div>
                                <div class="text-[11px] text-slate-400 mt-0.5 flex items-center gap-2">
                                    <span>{{ $ev->event_time->format('d M H:i') }}</span>
                                    <span>&bull;</span>
                                    <span>{{ $ev->location_name }}</span>
                                </div>
                            </div>
                        </div>
                        @endforeach

                        <!-- Origin Port Step -->
                        <div class="relative flex items-start gap-4 cursor-pointer" onclick="jumpToStep(0)">
                            <span class="w-5 h-5 rounded-full bg-slate-300 border-4 border-slate-100 mt-0.5 z-10 shrink-0"></span>
                            <div class="flex-1">
                                <div class="text-xs font-semibold text-slate-700 leading-snug">
                                    Pesanan kargo {{ $shipment->cargo_type }} diproses dan dimuat di Pelabuhan Asal {{ $shipment->origin->name }}.
                                </div>
                                <div class="text-[11px] text-slate-400 mt-0.5 flex items-center gap-2">
                                    <span>{{ $shipment->expected_departure->format('d M H:i') }}</span>
                                    <span>&bull;</span>
                                    <span>{{ $shipment->origin->city }}, {{ $shipment->origin->country }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    @endif

</div>
@endsection

@if($shipment && $currentPos)
@section('scripts')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<script>
    let shopeeMap;
    let shopeeCurrentTile;
    let shopeeTiles = {};
    let shopeeAnimInterval = null;
    let shopeeDenseSteps = [];
    let shopeeStepIdx = 0;
    let shopeeIsPlaying = false;
    let shopeeShipMarker = null;
    let shopeeTraveledLine = null;
    let shopeeFullLine = null;

    const originLat = {{ $shipment->origin->latitude }};
    const originLon = {{ $shipment->origin->longitude }};
    const destLat = {{ $shipment->destination->latitude }};
    const destLon = {{ $shipment->destination->longitude }};

    const realCurrentLat = {{ $currentPos['latitude'] }};
    const realCurrentLon = {{ $currentPos['longitude'] }};
    const realCurrentLocationName = {!! json_encode($currentPos['location_name']) !!};
    const vesselName = {!! json_encode($shipment->vessel_name ?? $shipment->vessel_carrier ?? 'MV MSC Amsterdam') !!};

    function changeShopeeTheme(theme) {
        if (!shopeeMap || !shopeeTiles[theme]) return;
        if (shopeeCurrentTile) {
            shopeeMap.removeLayer(shopeeCurrentTile);
        }
        shopeeCurrentTile = shopeeTiles[theme];
        shopeeCurrentTile.addTo(shopeeMap);
    }

    // Nautical bearing
    function shopeeBearing(lat1, lon1, lat2, lon2) {
        const toRad = Math.PI / 180;
        const toDeg = 180 / Math.PI;
        const dLon = (lon2 - lon1) * toRad;
        const y = Math.sin(dLon) * Math.cos(lat2 * toRad);
        const x = Math.cos(lat1 * toRad) * Math.sin(lat2 * toRad) -
                  Math.sin(lat1 * toRad) * Math.cos(lat2 * toRad) * Math.cos(dLon);
        return (Math.atan2(y, x) * toDeg + 360) % 360;
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Collect cleaned waypoints
        const waypoints = [
            [originLat, originLon]
        ];

        @foreach($shipment->trackingEvents as $ev)
            @if($ev->latitude && $ev->longitude)
                waypoints.push([{{ $ev->latitude }}, {{ $ev->longitude }}]);
            @endif
        @endforeach

        waypoints.push([realCurrentLat, realCurrentLon]);
        waypoints.push([destLat, destLon]);

        const cleanedWp = [];
        for (let i = 0; i < waypoints.length; i++) {
            if (i === 0 || waypoints[i][0] !== waypoints[i-1][0] || waypoints[i][1] !== waypoints[i-1][1]) {
                cleanedWp.push(waypoints[i]);
            }
        }

        // Initialize Map centered on ship and route
        shopeeMap = L.map('shopee-tracking-map', {
            center: [realCurrentLat, realCurrentLon],
            zoom: 5,
            minZoom: 2,
            maxZoom: 18,
            zoomControl: false,
            worldCopyJump: true
        });

        // Add sleek zoom control on top-right
        L.control.zoom({ position: 'topright' }).addTo(shopeeMap);

        // Tile Providers (Standard OpenStreetMap as DEFAULT per PPT Slide 09!)
        shopeeTiles = {
            osm: L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }),
            satellite: L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                maxZoom: 18,
                attribution: '&copy; Esri &mdash; World Imagery'
            }),
            voyager: L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                maxZoom: 19,
                subdomains: 'abcd',
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>'
            }),
            dark: L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                maxZoom: 19,
                subdomains: 'abcd',
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>'
            })
        };

        shopeeCurrentTile = shopeeTiles.osm;
        shopeeCurrentTile.addTo(shopeeMap);

        // 1. Origin Pin (Red Pin with white center just like Jakarta pin in Shopee screenshot!)
        const originPinHtml = `
            <div class="relative flex items-center justify-center" style="width: 24px; height: 24px; margin-left: -12px; margin-top: -12px;">
                <div class="w-5 h-5 rounded-full bg-rose-600 border-2 border-white shadow-lg flex items-center justify-center">
                    <div class="w-1.5 h-1.5 rounded-full bg-white"></div>
                </div>
            </div>
        `;
        L.marker([originLat, originLon], {
            icon: L.divIcon({ html: originPinHtml, className: 'shopee-origin-pin', iconSize: [24, 24] }),
            zIndexOffset: 500
        }).addTo(shopeeMap).bindPopup("<strong>PELABUHAN ASAL:</strong> {{ $shipment->origin->name }}");

        // 2. Destination Pin
        const destPinHtml = `
            <div class="relative flex items-center justify-center" style="width: 24px; height: 24px; margin-left: -12px; margin-top: -12px;">
                <div class="w-5 h-5 rounded-full bg-emerald-600 border-2 border-white shadow-lg flex items-center justify-center">
                    <div class="w-1.5 h-1.5 rounded-full bg-white"></div>
                </div>
            </div>
        `;
        L.marker([destLat, destLon], {
            icon: L.divIcon({ html: destPinHtml, className: 'shopee-dest-pin', iconSize: [24, 24] }),
            zIndexOffset: 500
        }).addTo(shopeeMap).bindPopup("<strong>PELABUHAN TUJUAN:</strong> {{ $shipment->destination->name }}");

        // 3. Shopee Signature Delivery Route Line (Bright Orange-Red with Soft Glow)
        shopeeFullLine = L.polyline(cleanedWp, {
            color: '#ea580c',
            weight: 4.5,
            opacity: 0.85,
            lineCap: 'round',
            lineJoin: 'round'
        }).addTo(shopeeMap);

        shopeeTraveledLine = L.polyline([], {
            color: '#fbbf24',
            weight: 5,
            opacity: 0.95
        }).addTo(shopeeMap);

        // 4. Generate Dense Steps for Smooth Sailing
        shopeeDenseSteps = [];
        for (let i = 0; i < cleanedWp.length - 1; i++) {
            const p1 = cleanedWp[i];
            const p2 = cleanedWp[i + 1];

            const dLat = (p2[0] - p1[0]) * 111;
            const dLon = (p2[1] - p1[1]) * 111 * Math.cos((p1[0] + p2[0]) * Math.PI / 360);
            const distKm = Math.sqrt(dLat * dLat + dLon * dLon);
            const steps = Math.max(70, Math.min(260, Math.round(distKm * 0.08)));

            for (let s = 0; s < steps; s++) {
                const t = s / steps;
                const curLat = p1[0] + (p2[0] - p1[0]) * t;
                const curLon = p1[1] + (p2[1] - p1[1]) * t;
                const bearing = shopeeBearing(curLat, curLon, p2[0], p2[1]);

                shopeeDenseSteps.push({
                    lat: curLat,
                    lng: curLon,
                    bearing: bearing,
                    segIdx: i
                });
            }
        }
        if (cleanedWp.length > 0) {
            const last = cleanedWp[cleanedWp.length - 1];
            shopeeDenseSteps.push({
                lat: last[0],
                lng: last[1],
                bearing: shopeeDenseSteps.length > 0 ? shopeeDenseSteps[shopeeDenseSteps.length - 1].bearing : 0,
                segIdx: cleanedWp.length - 1
            });
        }

        // 5. Find index of real current cargo location
        let realCurrentIdx = 0;
        let minDist = 999999;
        for (let i = 0; i < shopeeDenseSteps.length; i++) {
            const d = Math.abs(shopeeDenseSteps[i].lat - realCurrentLat) + Math.abs(shopeeDenseSteps[i].lng - realCurrentLon);
            if (d < minDist) {
                minDist = d;
                realCurrentIdx = i;
            }
        }
        shopeeStepIdx = realCurrentIdx;

        // 6. BUILD THE SHOPEE CARGO CONTAINER SHIP WITH WHITE SPEECH BUBBLE & RED PIN!
        function buildShopeeShipHtml(bubbleText, bearing) {
            return `
                <div class="shopee-ship-marker-wrapper relative flex flex-col items-center pointer-events-auto" style="width: 140px; margin-left: -70px; margin-top: -65px;">
                    <!-- White Speech Bubble Callout (Just like Shopee's 'Memproses pesanan') -->
                    <div class="relative bg-white text-slate-900 font-extrabold text-[11px] px-3 py-1 rounded-md shadow-xl border border-slate-200 whitespace-nowrap flex items-center justify-center">
                        <span id="shopee-bubble-label">${bubbleText}</span>
                        <!-- Downward pointing triangle -->
                        <div class="absolute -bottom-1.5 left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-4 border-r-4 border-t-6 border-l-transparent border-r-transparent border-t-white"></div>
                    </div>

                    <!-- Red Location Pin Pinpoint -->
                    <div class="w-3.5 h-3.5 rounded-full bg-rose-600 border border-white shadow-md flex items-center justify-center my-0.5">
                        <div class="w-1 h-1 rounded-full bg-white"></div>
                    </div>

                    <!-- Realistic Maritime Container Ship (Replaces Shopee Delivery Truck) -->
                    <div class="relative w-16 h-8 flex items-center justify-center transform transition-transform duration-75" style="transform: rotate(${bearing}deg);" id="shopee-ship-hull-rotator">
                        <!-- Bow waves / Sea wake glow -->
                        <div class="absolute -left-2 w-5 h-5 rounded-full bg-cyan-400/40 animate-ping"></div>

                        <!-- 3D Isometric Container Ship SVG -->
                        <svg width="60" height="28" viewBox="0 0 60 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <!-- Hull (Navy Marine) -->
                            <path d="M4 16L10 25H48L56 16H4Z" fill="#1e293b" stroke="#0f172a" stroke-width="1.2"/>
                            <path d="M4 16L12 11H50L56 16H4Z" fill="#334155"/>
                            <!-- Bridge / Deck House -->
                            <rect x="40" y="4" width="9" height="8" rx="1" fill="#f8fafc" stroke="#cbd5e1" stroke-width="0.8"/>
                            <rect x="42" y="6" width="5" height="2.5" rx="0.5" fill="#38bdf8"/>
                            <rect x="44" y="2" width="1.5" height="2.5" fill="#ef4444"/>
                            <!-- Stacked Coffee & Cocoa Containers -->
                            <rect x="12" y="7" width="8" height="5" rx="0.5" fill="#C49A55" stroke="#78350f" stroke-width="0.5"/>
                            <rect x="21" y="7" width="8" height="5" rx="0.5" fill="#795548" stroke="#3e2723" stroke-width="0.5"/>
                            <rect x="30" y="7" width="8" height="5" rx="0.5" fill="#0284c7" stroke="#0369a1" stroke-width="0.5"/>
                            <rect x="16" y="3" width="7" height="4" rx="0.5" fill="#ea580c" stroke="#9a3412" stroke-width="0.5"/>
                            <rect x="24" y="3" width="7" height="4" rx="0.5" fill="#10b981" stroke="#047857" stroke-width="0.5"/>
                        </svg>
                    </div>
                </div>
            `;
        }

        const initialPt = shopeeDenseSteps[shopeeStepIdx] || { lat: realCurrentLat, lng: realCurrentLon, bearing: 0 };
        const initialBubbleText = (realCurrentLocationName && realCurrentLocationName.length > 22)
            ? realCurrentLocationName.substring(0, 20) + '...'
            : (realCurrentLocationName || 'Kapal Sedang Berlayar');

        shopeeShipMarker = L.marker([initialPt.lat, initialPt.lng], {
            icon: L.divIcon({
                html: buildShopeeShipHtml(initialBubbleText, Math.round(initialPt.bearing)),
                className: 'shopee-ship-div-icon',
                iconSize: [140, 65],
                iconAnchor: [70, 60]
            }),
            zIndexOffset: 1500
        }).addTo(shopeeMap);

        // Update traveled line up to initial point
        const initialTrail = shopeeDenseSteps.slice(0, shopeeStepIdx + 1).map(p => [p.lat, p.lng]);
        shopeeTraveledLine.setLatLngs(initialTrail);

        // 7. Step-by-step sailing animation loop
        function runShopeeStepAnimation() {
            if (!shopeeIsPlaying) return;

            shopeeStepIdx += 1;
            if (shopeeStepIdx >= shopeeDenseSteps.length) {
                shopeeStepIdx = 0;
            }

            const pt = shopeeDenseSteps[shopeeStepIdx];
            if (pt) {
                shopeeShipMarker.setLatLng([pt.lat, pt.lng]);

                // Rotate ship hull to bearing
                const hullElem = document.getElementById('shopee-ship-hull-rotator');
                if (hullElem) {
                    hullElem.style.transform = `rotate(${Math.round(pt.bearing)}deg)`;
                }

                // Update speech bubble text dynamically
                const bubbleElem = document.getElementById('shopee-bubble-label');
                if (bubbleElem) {
                    const pct = Math.round((shopeeStepIdx / (shopeeDenseSteps.length - 1)) * 100);
                    if (pct < 15) {
                        bubbleElem.innerText = 'Memproses pesanan';
                    } else if (pct < 85) {
                        bubbleElem.innerText = 'Kapal Sedang Berlayar';
                    } else {
                        bubbleElem.innerText = 'Mendekati Pelabuhan';
                    }
                }

                // Update traveled line
                const trail = shopeeDenseSteps.slice(0, shopeeStepIdx + 1).map(p => [p.lat, p.lng]);
                shopeeTraveledLine.setLatLngs(trail);
            }
        }

        // Control button listeners
        const playBtn = document.getElementById('shopee-play-btn');
        const playIcon = document.getElementById('shopee-play-icon');
        const playText = document.getElementById('shopee-play-text');
        if (playBtn) {
            playBtn.addEventListener('click', function () {
                shopeeIsPlaying = !shopeeIsPlaying;
                if (shopeeIsPlaying) {
                    playIcon.innerText = '⏸️';
                    playText.innerText = 'Jeda';
                    shopeeAnimInterval = setInterval(runShopeeStepAnimation, 35);
                } else {
                    playIcon.innerText = '▶️';
                    playText.innerText = 'Putar Step-by-Step';
                    clearInterval(shopeeAnimInterval);
                }
            });
        }

        const resetBtn = document.getElementById('shopee-reset-btn');
        if (resetBtn) {
            resetBtn.addEventListener('click', function () {
                shopeeStepIdx = 0;
                shopeeTraveledLine.setLatLngs([]);
                const pt = shopeeDenseSteps[0];
                if (pt) {
                    shopeeShipMarker.setLatLng([pt.lat, pt.lng]);
                }
                const bubbleElem = document.getElementById('shopee-bubble-label');
                if (bubbleElem) bubbleElem.innerText = 'Memproses pesanan';
                shopeeMap.panTo([originLat, originLon]);
            });
        }

        // Re-center Crosshair Button (Shopee Target Button)
        const recenterBtn = document.getElementById('btn-recenter-ship');
        if (recenterBtn) {
            recenterBtn.addEventListener('click', function () {
                const curLatLng = shopeeShipMarker.getLatLng();
                shopeeMap.flyTo(curLatLng, 6, { animate: true, duration: 1.0 });
            });
        }

        // Fit map bounds
        const allBounds = L.latLngBounds(cleanedWp);
        allBounds.extend([realCurrentLat, realCurrentLon]);
        shopeeMap.fitBounds(allBounds, { padding: [50, 50] });
    });

    /**
     * Jump smoothly to a specific stage/step
     */
    function jumpToStep(stepNumber) {
        if (!shopeeDenseSteps || shopeeDenseSteps.length === 0) return;
        let targetIdx = 0;
        let bubbleText = 'Memproses pesanan';

        if (stepNumber === 0) {
            targetIdx = 0;
            bubbleText = 'Pelabuhan Asal';
        } else if (stepNumber === 1) {
            targetIdx = Math.round(shopeeDenseSteps.length * 0.5);
            bubbleText = 'Sedang Dikirim (Lautan)';
        } else if (stepNumber === 2) {
            targetIdx = shopeeDenseSteps.length - 1;
            bubbleText = 'Pelabuhan Tujuan';
        }

        shopeeStepIdx = targetIdx;
        const pt = shopeeDenseSteps[targetIdx];
        if (pt && shopeeShipMarker) {
            shopeeShipMarker.setLatLng([pt.lat, pt.lng]);
            const hullElem = document.getElementById('shopee-ship-hull-rotator');
            if (hullElem) hullElem.style.transform = `rotate(${Math.round(pt.bearing)}deg)`;
            const bubbleElem = document.getElementById('shopee-bubble-label');
            if (bubbleElem) bubbleElem.innerText = bubbleText;

            const trail = shopeeDenseSteps.slice(0, targetIdx + 1).map(p => [p.lat, p.lng]);
            shopeeTraveledLine.setLatLngs(trail);

            shopeeMap.flyTo([pt.lat, pt.lng], 6, { animate: true, duration: 1.0 });
        }
    }

    function jumpToCurrentRealPosition() {
        if (!shopeeDenseSteps || shopeeDenseSteps.length === 0) return;
        let closestIdx = 0;
        let minDist = 999999;
        for (let i = 0; i < shopeeDenseSteps.length; i++) {
            const d = Math.abs(shopeeDenseSteps[i].lat - realCurrentLat) + Math.abs(shopeeDenseSteps[i].lng - realCurrentLon);
            if (d < minDist) {
                minDist = d;
                closestIdx = i;
            }
        }
        shopeeStepIdx = closestIdx;
        const pt = shopeeDenseSteps[closestIdx];
        if (pt && shopeeShipMarker) {
            shopeeShipMarker.setLatLng([pt.lat, pt.lng]);
            const bubbleElem = document.getElementById('shopee-bubble-label');
            if (bubbleElem) bubbleElem.innerText = (realCurrentLocationName && realCurrentLocationName.length > 20) ? realCurrentLocationName.substring(0, 18) + '...' : realCurrentLocationName;
            shopeeMap.flyTo([realCurrentLat, realCurrentLon], 6, { animate: true, duration: 1.0 });
        }
    }

    function jumpToWaypoint(lat, lng, name) {
        if (!shopeeDenseSteps || shopeeDenseSteps.length === 0) return;
        let closestIdx = 0;
        let minDist = 999999;
        for (let i = 0; i < shopeeDenseSteps.length; i++) {
            const d = Math.abs(shopeeDenseSteps[i].lat - lat) + Math.abs(shopeeDenseSteps[i].lng - lng);
            if (d < minDist) {
                minDist = d;
                closestIdx = i;
            }
        }
        shopeeStepIdx = closestIdx;
        const pt = shopeeDenseSteps[closestIdx];
        if (pt && shopeeShipMarker) {
            shopeeShipMarker.setLatLng([pt.lat, pt.lng]);
            const bubbleElem = document.getElementById('shopee-bubble-label');
            if (bubbleElem) bubbleElem.innerText = (name && name.length > 20) ? name.substring(0, 18) + '...' : name;
            shopeeMap.flyTo([lat, lng], 6, { animate: true, duration: 1.0 });
        }
    }
</script>
@endsection
@endif
