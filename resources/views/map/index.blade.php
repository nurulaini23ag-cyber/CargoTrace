@extends('layouts.app')

@section('title', 'Global Live Cargo Fleet Map — CargoTrace')

@section('content')
<div class="space-y-4">
    <!-- Header & Statistics Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#38251D] text-[#E8D7B9] border border-[#C49A55]/30">
                    OCEAN FREIGHT RADAR
                </span>
                <span class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Live Satellite & AIS Tracking
                </span>
            </div>
            <h1 class="text-2xl font-extrabold text-[#38251D] tracking-tight mt-1">
                Peta Kargo Global & Posisi Barang Saat Ini
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Worldwide Coffee & Cocoa Sea Freight Visibility &bull; Real-time maritime passage monitoring
            </p>
        </div>

        <!-- Quick Stats Pills -->
        <div class="flex flex-wrap items-center gap-2">
            <div class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-center min-w-[90px]">
                <div class="text-[10px] uppercase font-bold text-slate-500">Total Kargo</div>
                <div class="text-lg font-black text-slate-900">{{ $stats['total'] }}</div>
            </div>
            <div class="px-3 py-2 bg-amber-50 border border-amber-200 rounded-xl text-center min-w-[90px]">
                <div class="text-[10px] uppercase font-bold text-amber-700">En Route</div>
                <div class="text-lg font-black text-amber-900">{{ $stats['in_transit'] }}</div>
            </div>
            <div class="px-3 py-2 bg-[#38251D]/10 border border-[#38251D]/20 rounded-xl text-center min-w-[90px]">
                <div class="text-[10px] uppercase font-bold text-[#38251D]">☕ Coffee</div>
                <div class="text-lg font-black text-[#38251D]">{{ $stats['coffee'] }}</div>
            </div>
            <div class="px-3 py-2 bg-[#795548]/10 border border-[#795548]/20 rounded-xl text-center min-w-[90px]">
                <div class="text-[10px] uppercase font-bold text-[#795548]">🍫 Cocoa</div>
                <div class="text-lg font-black text-[#795548]">{{ $stats['cocoa'] }}</div>
            </div>
            @if($stats['delayed'] > 0)
            <div class="px-3 py-2 bg-rose-50 border border-rose-200 rounded-xl text-center min-w-[90px]">
                <div class="text-[10px] uppercase font-bold text-rose-700">⚠️ Delayed</div>
                <div class="text-lg font-black text-rose-800">{{ $stats['delayed'] }}</div>
            </div>
            @endif
        </div>
    </div>

    <!-- Filter Toolbar -->
    <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-sm flex flex-wrap items-center justify-between gap-3">
        <form method="GET" action="{{ route('map.index') }}" class="flex flex-wrap items-center gap-2.5 w-full md:w-auto">
            <!-- Commodity Filter -->
            <select name="commodity" onchange="this.form.submit()" class="text-xs bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 font-semibold text-slate-700 focus:ring-2 focus:ring-[#38251D] focus:outline-none">
                <option value="">Semua Komoditas (Coffee & Cocoa)</option>
                <option value="COFFEE" {{ request('commodity') === 'COFFEE' ? 'selected' : '' }}>☕ Coffee Only (Arabica / Robusta)</option>
                <option value="COCOA" {{ request('commodity') === 'COCOA' ? 'selected' : '' }}>🍫 Cocoa Only (Beans / Butter)</option>
            </select>

            <!-- Status Filter -->
            <select name="status" onchange="this.form.submit()" class="text-xs bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 font-semibold text-slate-700 focus:ring-2 focus:ring-[#38251D] focus:outline-none">
                <option value="">Semua Status Pengiriman</option>
                <option value="IN_TRANSIT" {{ request('status') === 'IN_TRANSIT' ? 'selected' : '' }}>🚢 In Transit (Sedang Berlayar)</option>
                <option value="PICKED_UP" {{ request('status') === 'PICKED_UP' ? 'selected' : '' }}>📦 Picked Up / Port Staged</option>
                <option value="BOOKED" {{ request('status') === 'BOOKED' ? 'selected' : '' }}>📋 Booked</option>
                <option value="DELIVERED" {{ request('status') === 'DELIVERED' ? 'selected' : '' }}>✅ Delivered (Tiba di Tujuan)</option>
            </select>

            <!-- Carrier Filter -->
            <select name="carrier_id" onchange="this.form.submit()" class="text-xs bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 font-semibold text-slate-700 focus:ring-2 focus:ring-[#38251D] focus:outline-none">
                <option value="">Semua Maritime Carrier</option>
                @foreach($carriers as $car)
                    <option value="{{ $car->id }}" {{ request('carrier_id') == $car->id ? 'selected' : '' }}>
                        {{ $car->name }} ({{ $car->code }})
                    </option>
                @endforeach
            </select>

            @if(request()->anyFilled(['commodity', 'status', 'carrier_id']))
                <a href="{{ route('map.index') }}" class="text-xs text-rose-600 hover:text-rose-800 font-bold px-2 py-1">
                    ✕ Reset Filter
                </a>
            @endif
        </form>

        <div class="text-xs text-slate-500 font-medium hidden lg:block">
            Klik kapal pada peta atau daftar di samping untuk melihat di mana kargo saat ini berada
        </div>
    </div>

    <!-- Main Map Workspace: Left Map Canvas | Right Live Shipment Directory -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
        <!-- Leaflet Map Container (8 cols) -->
        <div class="lg:col-span-8 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col">
            <!-- Shopee-Style Fleet Sailing Controls & World Map Theme -->
            <div class="p-3 bg-slate-900 text-white flex flex-wrap items-center justify-between gap-3 text-xs px-4 border-b border-slate-800">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="flex items-center gap-1.5 font-bold text-[#E8D7B9] text-xs">
                        <span>🌍</span> GLOBAL MARITIME AIS TRACKING
                    </span>
                    <div class="flex items-center gap-1 bg-slate-800 rounded-lg p-0.5 border border-slate-700">
                        <button id="fleet-btn-play-pause" type="button" class="px-2.5 py-1 rounded bg-[#C49A55] text-slate-900 font-extrabold text-[11px] hover:bg-[#b38944] transition flex items-center gap-1">
                            <span id="fleet-play-icon">⏸️</span> <span id="fleet-play-text">Jeda Semua Kapal</span>
                        </button>
                        <button id="fleet-btn-restart" type="button" class="px-2 py-1 rounded text-slate-300 hover:text-white hover:bg-slate-700 text-[11px] font-bold transition flex items-center gap-1">
                            <span>🔄</span> Ulangi Rute
                        </button>
                    </div>

                    <div class="flex items-center gap-1 bg-slate-800 rounded-lg p-0.5 border border-slate-700 text-[10px]">
                        <span class="text-slate-400 px-1 font-bold">Speed:</span>
                        <button type="button" onclick="setFleetSpeed(1)" class="fleet-speed-btn px-1.5 py-0.5 rounded font-bold transition bg-[#C49A55] text-slate-900" id="fleet-spd-1">1x</button>
                        <button type="button" onclick="setFleetSpeed(2)" class="fleet-speed-btn px-1.5 py-0.5 rounded font-bold transition text-slate-300 hover:text-white" id="fleet-spd-2">2x</button>
                        <button type="button" onclick="setFleetSpeed(4)" class="fleet-speed-btn px-1.5 py-0.5 rounded font-bold transition text-slate-300 hover:text-white" id="fleet-spd-4">4x</button>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-1.5">
                        <span class="text-[10px] text-slate-400 font-bold uppercase hidden sm:inline">Peta Dunia:</span>
                        <select id="fleet-world-theme" onchange="changeFleetMapTheme(this.value)" class="bg-slate-800 text-white border border-slate-700 rounded-lg px-2.5 py-1 text-[11px] font-semibold focus:outline-none focus:ring-1 focus:ring-[#C49A55] cursor-pointer">
                            <option value="voyager" selected>🗺️ Peta Dunia Voyager (Default)</option>
                            <option value="dark">🌙 Dark Maritime Chart</option>
                            <option value="osm">🧭 OpenStreetMap Classic</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- The Leaflet Map Canvas with World View -->
            <div id="fleet-map" class="h-[620px] w-full z-10 relative"></div>

            <div class="p-2.5 bg-slate-50 border-t border-slate-200 text-[11px] text-slate-500 flex items-center justify-between px-4">
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center gap-1 text-emerald-600"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> Pelabuhan Asal</span>
                    <span class="inline-flex items-center gap-1 text-amber-700"><span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span> Posisi Kargo (Di Mana Barang Sampai)</span>
                    <span class="inline-flex items-center gap-1 text-rose-600"><span class="w-2 h-2 rounded-full bg-rose-500"></span> Pelabuhan Tujuan</span>
                </div>
                <div class="font-mono text-[10px]">
                    Nautical AIS Engine &bull; WGS84 World Coordinates
                </div>
            </div>
        </div>

        <!-- Right Side: Active Fleet & Cargo Position Directory (4 cols) -->
        <div class="lg:col-span-4 space-y-3">
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-sm font-bold text-[#38251D] flex items-center gap-2">
                        <span>📋</span> Daftar Posisi Kargo Aktif
                    </h2>
                    <span class="text-xs font-bold px-2 py-0.5 bg-slate-100 text-slate-600 rounded-full">
                        {{ count($mapShipments) }} Kapal
                    </span>
                </div>

                <!-- Live Search Box within List -->
                <div class="relative mb-3">
                    <input type="text" id="shipment-search-input" placeholder="Cari nomor kargo, kapal, atau kota..."
                           class="w-full text-xs bg-slate-50 border border-slate-200 rounded-lg pl-8 pr-3 py-2 text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#38251D]">
                    <span class="absolute left-2.5 top-2.5 text-slate-400 text-xs">🔍</span>
                </div>

                <!-- Scrollable Shipment Cards List -->
                <div class="space-y-2.5 max-h-[500px] overflow-y-auto pr-1" id="shipment-list-container">
                    @forelse($mapShipments as $s)
                    <div class="shipment-card p-3 rounded-xl border border-slate-200 hover:border-[#C49A55] bg-slate-50 hover:bg-amber-50/40 transition cursor-pointer"
                         onclick="focusShipment({{ $s['id'] }})"
                         data-search="{{ strtolower($s['number'] . ' ' . $s['cargo_type'] . ' ' . $s['vessel_name'] . ' ' . $s['current_position']['location_name'] . ' ' . $s['origin']['city'] . ' ' . $s['destination']['city']) }}">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <div class="flex items-center gap-1.5">
                                    @if($s['commodity'] === 'COCOA')
                                        <span class="px-1.5 py-0.2 bg-[#795548]/10 text-[#795548] text-[9px] font-black uppercase rounded border border-[#795548]/20">🍫 COCOA</span>
                                    @else
                                        <span class="px-1.5 py-0.2 bg-[#38251D]/10 text-[#38251D] text-[9px] font-black uppercase rounded border border-[#38251D]/20">☕ COFFEE</span>
                                    @endif
                                    <span class="text-xs font-bold text-slate-900">{{ $s['number'] }}</span>
                                </div>
                                <div class="text-[11px] font-semibold text-slate-700 mt-1 line-clamp-1">
                                    {{ $s['cargo_type'] }}
                                </div>
                            </div>
                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded
                                @if($s['status'] === 'IN_TRANSIT') bg-sky-100 text-sky-800
                                @elseif($s['status'] === 'DELIVERED') bg-emerald-100 text-emerald-800
                                @elseif($s['status'] === 'PICKED_UP') bg-amber-100 text-amber-800
                                @else bg-slate-200 text-slate-700 @endif">
                                {{ $s['status'] }}
                            </span>
                        </div>

                        <!-- Current Cargo Position Badge ("Di mana barang sudah sampai") -->
                        <div class="mt-2.5 p-2 bg-white rounded-lg border border-slate-200 text-[11px]">
                            <div class="text-[10px] font-bold text-[#C49A55] uppercase tracking-wide flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-ping"></span>
                                Posisi Saat Ini:
                            </div>
                            <div class="font-extrabold text-[#38251D] text-xs mt-0.5">
                                {{ $s['current_position']['location_name'] }}
                            </div>
                            <div class="text-[10px] text-slate-500 mt-0.5 flex items-center justify-between">
                                <span>Lat: {{ number_format($s['current_position']['latitude'], 3) }}°, Lon: {{ number_format($s['current_position']['longitude'], 3) }}°</span>
                                <span class="font-bold text-slate-700">{{ $s['current_position']['progress_percent'] }}% Tuntas</span>
                            </div>
                        </div>

                        <div class="mt-2 flex items-center justify-between text-[10px] text-slate-500 pt-1 border-t border-slate-200">
                            <div>🚢 {{ $s['vessel_name'] }}</div>
                            <a href="{{ $s['detail_url'] }}" class="text-[#38251D] hover:text-[#C49A55] font-bold underline" onclick="event.stopPropagation()">
                                Rincian &rarr;
                            </a>
                        </div>
                    </div>
                    @empty
                    <div class="p-6 text-center text-slate-500 text-xs">
                        Tidak ada pengiriman kargo yang sesuai dengan filter pencarian.
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<script>
    const shipmentsData = {!! json_encode($mapShipments) !!};
    let map;
    let currentTileLayer;
    let tileProviders = {};
    const staticMarkers = {};
    const activeVessels = [];
    let fleetAnimationInterval = null;
    let isFleetPlaying = true;
    let fleetSpeed = 1;

    function setFleetSpeed(multiplier) {
        fleetSpeed = multiplier;
        document.querySelectorAll('.fleet-speed-btn').forEach(b => {
            b.classList.remove('bg-[#C49A55]', 'text-slate-900');
            b.classList.add('text-slate-300');
        });
        const activeBtn = document.getElementById('fleet-spd-' + multiplier);
        if (activeBtn) {
            activeBtn.classList.add('bg-[#C49A55]', 'text-slate-900');
            activeBtn.classList.remove('text-slate-300');
        }
    }

    function changeFleetMapTheme(theme) {
        if (!map || !tileProviders[theme]) return;
        if (currentTileLayer) {
            map.removeLayer(currentTileLayer);
        }
        currentTileLayer = tileProviders[theme];
        currentTileLayer.addTo(map);
    }

    function fleetCalculateBearing(lat1, lon1, lat2, lon2) {
        const toRad = Math.PI / 180;
        const toDeg = 180 / Math.PI;
        const dLon = (lon2 - lon1) * toRad;
        const y = Math.sin(dLon) * Math.cos(lat2 * toRad);
        const x = Math.cos(lat1 * toRad) * Math.sin(lat2 * toRad) -
                  Math.sin(lat1 * toRad) * Math.cos(lat2 * toRad) * Math.cos(dLon);
        return (Math.atan2(y, x) * toDeg + 360) % 360;
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Initialize World Map Leaflet Instance
        map = L.map('fleet-map', {
            center: [15.0, 50.0],
            zoom: 3,
            minZoom: 2,
            maxZoom: 18,
            worldCopyJump: true
        });

        // World Map Basemap Tile Providers
        tileProviders = {
            voyager: L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                maxZoom: 19,
                subdomains: 'abcd',
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>'
            }),
            dark: L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                maxZoom: 19,
                subdomains: 'abcd',
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>'
            }),
            osm: L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 18,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            })
        };

        currentTileLayer = tileProviders.voyager;
        currentTileLayer.addTo(map);

        const bounds = L.latLngBounds();

        // Loop through all active shipments and setup sailing animations
        shipmentsData.forEach((s, idx) => {
            const pos = s.current_position;
            const origin = s.origin;
            const dest = s.destination;

            // 1. Origin Marker
            if (origin.lat && origin.lng) {
                L.circleMarker([origin.lat, origin.lng], {
                    radius: 6,
                    fillColor: '#10b981',
                    color: '#ffffff',
                    weight: 2,
                    opacity: 1,
                    fillOpacity: 0.95
                }).addTo(map).bindPopup(`<strong>PELABUHAN ASAL:</strong><br>${origin.name}, ${origin.country}<br><small>Cargo: ${s.number}</small>`);
                bounds.extend([origin.lat, origin.lng]);
            }

            // 2. Destination Marker
            if (dest.lat && dest.lng) {
                L.circleMarker([dest.lat, dest.lng], {
                    radius: 6,
                    fillColor: '#ef4444',
                    color: '#ffffff',
                    weight: 2,
                    opacity: 1,
                    fillOpacity: 0.95
                }).addTo(map).bindPopup(`<strong>PELABUHAN TUJUAN:</strong><br>${dest.name}, ${dest.country}<br><small>Cargo: ${s.number}</small>`);
                bounds.extend([dest.lat, dest.lng]);
            }

            // 3. Assemble cleaned route points
            const rawPoints = [];
            if (origin.lat && origin.lng) rawPoints.push([origin.lat, origin.lng]);
            if (s.waypoints && s.waypoints.length > 0) {
                s.waypoints.forEach(wp => rawPoints.push([wp.lat, wp.lng]));
            }
            if (pos.latitude && pos.longitude) rawPoints.push([pos.latitude, pos.longitude]);
            if (dest.lat && dest.lng) rawPoints.push([dest.lat, dest.lng]);

            const cleanedRoute = [];
            for (let i = 0; i < rawPoints.length; i++) {
                if (i === 0 || rawPoints[i][0] !== rawPoints[i-1][0] || rawPoints[i][1] !== rawPoints[i-1][1]) {
                    cleanedRoute.push(rawPoints[i]);
                }
            }

            // Draw planned polyline
            if (cleanedRoute.length >= 2) {
                L.polyline(cleanedRoute, {
                    color: s.commodity === 'COCOA' ? '#795548' : '#38251D',
                    weight: 2.5,
                    opacity: 0.45,
                    dashArray: '5, 8'
                }).addTo(map);
            }

            // 4. Fixed Real-World Position Beacon ("Di mana barang sudah sampai")
            if (pos.latitude && pos.longitude) {
                const beaconHtml = `
                    <div class="relative flex items-center justify-center pointer-events-auto" style="width: 28px; height: 28px; margin-left: -14px; margin-top: -14px;">
                        <span class="animate-ping absolute inline-flex h-7 w-7 rounded-full bg-amber-400 opacity-75"></span>
                        <div class="relative inline-flex rounded-full h-5 w-5 bg-amber-600 border border-white shadow-md items-center justify-center text-white text-[9px] font-black">
                            📍
                        </div>
                    </div>
                `;
                const beaconMarker = L.marker([pos.latitude, pos.longitude], {
                    icon: L.divIcon({
                        html: beaconHtml,
                        className: 'fleet-beacon-marker',
                        iconSize: [28, 28],
                        iconAnchor: [14, 14],
                        popupAnchor: [0, -14]
                    }),
                    zIndexOffset: 600
                }).addTo(map);

                const isCoffee = s.commodity === 'COFFEE';
                const popupHtml = `
                    <div class="p-2 font-sans text-xs min-w-[240px]">
                        <div class="flex items-center justify-between gap-1 pb-1 mb-1.5 border-b border-slate-200">
                            <span class="font-bold text-[10px] uppercase tracking-wider text-amber-800">
                                📍 POSISI REAL-TIME SAAT INI
                            </span>
                            <span class="px-1.5 py-0.5 rounded text-[9px] font-black ${isCoffee ? 'bg-amber-100 text-amber-900' : 'bg-orange-100 text-orange-900'}">
                                ${s.commodity}
                            </span>
                        </div>
                        <div class="text-sm font-black text-slate-900 leading-snug">
                            ${pos.location_name}
                        </div>
                        <div class="text-[11px] text-slate-600 mt-0.5">
                            <strong>Muatan:</strong> ${s.cargo_type} (${s.number})
                        </div>
                        <div class="mt-2 space-y-1 bg-slate-50 p-2 rounded-lg text-slate-600 border border-slate-100 text-[10px]">
                            <div><strong>Kapal Laut:</strong> ${s.vessel_name} (${s.voyage_number})</div>
                            <div><strong>Rute:</strong> ${origin.city} &rarr; ${dest.city}</div>
                            <div><strong>Koordinat:</strong> ${pos.latitude.toFixed(4)}°, ${pos.longitude.toFixed(4)}°</div>
                            <div><strong>Perjalanan Tuntas:</strong> ${pos.progress_percent}%</div>
                            <div><strong>Pembaruan Terakhir:</strong> ${pos.last_updated}</div>
                        </div>
                        <div class="mt-2.5 pt-1 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-[10px] font-bold text-slate-500">Status: ${s.status}</span>
                            <a href="${s.detail_url}" class="px-2.5 py-1 bg-[#38251D] text-[#E8D7B9] hover:bg-[#292421] text-[10px] font-bold rounded shadow-xs">
                                Buka Detail &rarr;
                            </a>
                        </div>
                    </div>
                `;
                beaconMarker.bindPopup(popupHtml);
                staticMarkers[s.id] = beaconMarker;
                bounds.extend([pos.latitude, pos.longitude]);
            }

            // 5. Generate Dense Steps for Moving Shopee Ship
            if (cleanedRoute.length >= 2) {
                const denseSteps = [];
                for (let i = 0; i < cleanedRoute.length - 1; i++) {
                    const p1 = cleanedRoute[i];
                    const p2 = cleanedRoute[i + 1];

                    const dLat = (p2[0] - p1[0]) * 111;
                    const dLon = (p2[1] - p1[1]) * 111 * Math.cos((p1[0] + p2[0]) * Math.PI / 360);
                    const distKm = Math.sqrt(dLat * dLat + dLon * dLon);
                    const steps = Math.max(60, Math.min(250, Math.round(distKm * 0.08)));

                    for (let st = 0; st < steps; st++) {
                        const t = st / steps;
                        const curLat = p1[0] + (p2[0] - p1[0]) * t;
                        const curLon = p1[1] + (p2[1] - p1[1]) * t;
                        const bearing = fleetCalculateBearing(curLat, curLon, p2[0], p2[1]);

                        denseSteps.push({
                            lat: curLat,
                            lng: curLon,
                            bearing: bearing
                        });
                    }
                }

                // Traveled Polyline for this vessel
                const traveledLine = L.polyline([], {
                    color: s.commodity === 'COCOA' ? '#C49A55' : '#10b981',
                    weight: 3.5,
                    opacity: 0.9
                }).addTo(map);

                // Initial start point staggered by shipment index
                const startStep = (idx * 25) % Math.max(1, denseSteps.length);
                const initPt = denseSteps[startStep] || denseSteps[0];

                const isCoffee = s.commodity === 'COFFEE';
                const vesselEmoji = isCoffee ? '☕' : '🍫';
                const pulseBg = isCoffee ? 'bg-amber-400' : 'bg-orange-500';
                const iconBg = isCoffee ? 'bg-[#38251D]' : 'bg-[#795548]';
                const borderColor = isCoffee ? 'border-[#C49A55]' : 'border-amber-300';

                const sailingHtml = `
                    <div class="relative flex flex-col items-center cursor-pointer pointer-events-auto" style="width: 80px; margin-left: -40px; margin-top: -30px;">
                        <!-- Mini Callout Badge -->
                        <div class="bg-white/95 text-slate-900 font-extrabold text-[9px] px-1.5 py-0.5 rounded shadow border border-slate-200 whitespace-nowrap mb-0.5 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full ${isCoffee ? 'bg-amber-500' : 'bg-orange-500'} animate-pulse"></span>
                            <span>${s.number}</span>
                        </div>
                        <!-- Container Ship SVG Hull -->
                        <div class="relative w-10 h-5 flex items-center justify-center transform transition-transform duration-75 fleet-ship-icon-${s.id}">
                            <svg width="38" height="18" viewBox="0 0 60 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M4 16L10 25H48L56 16H4Z" fill="${isCoffee ? '#38251D' : '#795548'}" stroke="#0f172a" stroke-width="1.2"/>
                                <path d="M4 16L12 11H50L56 16H4Z" fill="#334155"/>
                                <rect x="40" y="4" width="9" height="8" rx="1" fill="#f8fafc" stroke="#cbd5e1" stroke-width="0.8"/>
                                <rect x="42" y="6" width="5" height="2.5" rx="0.5" fill="#38bdf8"/>
                                <rect x="12" y="7" width="8" height="5" rx="0.5" fill="#C49A55"/>
                                <rect x="21" y="7" width="8" height="5" rx="0.5" fill="${isCoffee ? '#78350f' : '#ea580c'}"/>
                                <rect x="30" y="7" width="8" height="5" rx="0.5" fill="#0284c7"/>
                            </svg>
                        </div>
                    </div>
                `;

                const vMarker = L.marker([initPt.lat, initPt.lng], {
                    icon: L.divIcon({
                        html: sailingHtml,
                        className: 'fleet-sailing-marker',
                        iconSize: [80, 40],
                        iconAnchor: [40, 30],
                        popupAnchor: [0, -30]
                    }),
                    zIndexOffset: 1000
                }).addTo(map);

                vMarker.bindPopup(`
                    <div class="p-2 font-sans text-xs min-w-[220px]">
                        <div class="flex items-center justify-between text-[10px] font-bold pb-1 border-b border-slate-100 text-amber-800">
                            <span>🚢 KAPAL BERLAYAR (LIVE)</span>
                            <span class="px-1.5 py-0.2 rounded font-black ${isCoffee ? 'bg-amber-100 text-amber-900' : 'bg-orange-100 text-orange-900'}">${s.commodity}</span>
                        </div>
                        <div class="text-sm font-extrabold text-slate-900 mt-1">${s.vessel_name}</div>
                        <div class="text-slate-600 text-[11px] mt-0.5">${s.cargo_type} &bull; ${s.number}</div>
                        <div class="mt-2 text-[10px] text-slate-500 space-y-0.5 bg-slate-50 p-2 rounded border border-slate-100">
                            <div>Rute: ${origin.city} &rarr; ${dest.city}</div>
                            <div>Kecepatan: 19.2 Knots</div>
                            <div>Status: ${s.status}</div>
                        </div>
                        <div class="mt-2 text-right">
                            <a href="${s.detail_url}" class="text-[10px] font-bold text-[#38251D] hover:text-[#C49A55] underline">Lihat Detail Pengiriman &rarr;</a>
                        </div>
                    </div>
                `);

                activeVessels.push({
                    id: s.id,
                    marker: vMarker,
                    steps: denseSteps,
                    currentStep: startStep,
                    traveledLine: traveledLine
                });
            }
        });

        // 6. Global Multi-Vessel Sailing Animation Loop
        function animateFleetFrame() {
            if (!isFleetPlaying) return;

            activeVessels.forEach(v => {
                v.currentStep += fleetSpeed;
                if (v.currentStep >= v.steps.length) {
                    v.currentStep = 0;
                }

                const pt = v.steps[v.currentStep];
                if (pt) {
                    v.marker.setLatLng([pt.lat, pt.lng]);

                    const iconElement = document.querySelector(`.fleet-ship-icon-${v.id}`);
                    if (iconElement) {
                        iconElement.style.transform = `rotate(${Math.round(pt.bearing)}deg)`;
                    }

                    const trail = v.steps.slice(0, v.currentStep + 1).map(p => [p.lat, p.lng]);
                    v.traveledLine.setLatLngs(trail);
                }
            });
        }

        fleetAnimationInterval = setInterval(animateFleetFrame, 40);

        // Fleet Play/Pause Handlers
        const playPauseBtn = document.getElementById('fleet-btn-play-pause');
        const playIcon = document.getElementById('fleet-play-icon');
        const playText = document.getElementById('fleet-play-text');
        if (playPauseBtn) {
            playPauseBtn.addEventListener('click', function () {
                isFleetPlaying = !isFleetPlaying;
                if (isFleetPlaying) {
                    playIcon.innerText = '⏸️';
                    playText.innerText = 'Jeda Semua Kapal';
                    fleetAnimationInterval = setInterval(animateFleetFrame, 40);
                } else {
                    playIcon.innerText = '▶️';
                    playText.innerText = 'Lanjut Semua Kapal';
                    clearInterval(fleetAnimationInterval);
                }
            });
        }

        const restartBtn = document.getElementById('fleet-btn-restart');
        if (restartBtn) {
            restartBtn.addEventListener('click', function () {
                activeVessels.forEach(v => {
                    v.currentStep = 0;
                    v.traveledLine.setLatLngs([]);
                });
                if (!isFleetPlaying) {
                    isFleetPlaying = true;
                    if (playIcon) playIcon.innerText = '⏸️';
                    if (playText) playText.innerText = 'Jeda Semua Kapal';
                    fleetAnimationInterval = setInterval(animateFleetFrame, 40);
                }
            });
        }

        // Fit map bounds
        if (!bounds.isEmpty()) {
            map.fitBounds(bounds, { padding: [50, 50], maxZoom: 8 });
        }

        // Live Search Filter for Side Directory
        const searchInput = document.getElementById('shipment-search-input');
        if (searchInput) {
            searchInput.addEventListener('input', function (e) {
                const term = e.target.value.toLowerCase().trim();
                const cards = document.querySelectorAll('.shipment-card');
                cards.forEach(card => {
                    const searchData = card.getAttribute('data-search') || '';
                    if (searchData.includes(term)) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        }
    });

    /**
     * Focus on specific shipment when clicked in the directory
     */
    function focusShipment(id) {
        const marker = staticMarkers[id];
        if (marker && map) {
            const latLng = marker.getLatLng();
            map.flyTo(latLng, 7, {
                animate: true,
                duration: 1.2
            });
            setTimeout(() => {
                marker.openPopup();
            }, 1250);
        }
    }
</script>
@endsection
