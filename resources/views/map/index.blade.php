@extends('layouts.app')

@section('title', 'Peta Armada Kargo Global (Google Maps Style) — CargoTrace')

@section('content')
<div class="space-y-4 font-sans">

    <!-- Top Floating Header Bar (Google Maps Style) -->
    <div class="bg-white rounded-2xl p-4 md:p-5 border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-extrabold bg-[#38251D] text-[#E8D7B9] border border-[#C49A55]/40 uppercase tracking-wider">
                    LEAFLET + OPENSTREETMAP (PPT SLIDE 09)
                </span>
                <span class="inline-flex items-center gap-1.5 text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    AIS Real-Time & Last Known Position (LKP)
                </span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">
                Peta Kargo Global & Posisi Barang Dagangan Saya
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Pantau seluruh kapal pengangkut biji kopi & kakao yang Anda jual ke pelanggan dunia secara real-time &mdash; baik yang sudah sampai, sedang transit di barat, maupun yang transpondernya mati.
            </p>
        </div>

        <!-- Quick Summary Metrics -->
        <div class="flex flex-wrap items-center gap-2">
            <div class="px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-center min-w-[80px]">
                <div class="text-[10px] uppercase font-bold text-slate-500">Semua Kapal</div>
                <div class="text-base font-black text-slate-900">{{ $stats['total'] }}</div>
            </div>
            <div class="px-3 py-1.5 bg-blue-50 border border-blue-200 rounded-xl text-center min-w-[80px]">
                <div class="text-[10px] uppercase font-bold text-blue-700">Lagi Transit</div>
                <div class="text-base font-black text-blue-900">{{ $stats['in_transit'] }}</div>
            </div>
            <div class="px-3 py-1.5 bg-indigo-50 border border-indigo-200 rounded-xl text-center min-w-[80px]">
                <div class="text-[10px] uppercase font-bold text-indigo-700">Di Barat (Eropa)</div>
                <div class="text-base font-black text-indigo-900">{{ $stats['west_sector'] }}</div>
            </div>
            <div class="px-3 py-1.5 bg-emerald-50 border border-emerald-200 rounded-xl text-center min-w-[80px]">
                <div class="text-[10px] uppercase font-bold text-emerald-700">Sudah Sampai</div>
                <div class="text-base font-black text-emerald-900">{{ $stats['delivered'] }}</div>
            </div>
            <div class="px-3 py-1.5 bg-amber-50 border border-amber-200 rounded-xl text-center min-w-[80px]">
                <div class="text-[10px] uppercase font-bold text-amber-700">Sinyal Mati (LKP)</div>
                <div class="text-base font-black text-amber-900">{{ $stats['signal_offline'] }}</div>
            </div>
        </div>
    </div>

    <!-- Google Maps Style Quick Filter Bar & Search Chips -->
    <div class="bg-white p-3 rounded-2xl border border-slate-200 shadow-sm flex flex-wrap items-center justify-between gap-3">
        <!-- Floating Search Input -->
        <div class="relative flex-1 min-w-[260px] max-w-md">
            <input type="text" id="map-search-bar" placeholder="Cari nama kapal, no kargo, komoditas, pembeli, atau pelabuhan..."
                   class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl pl-9 pr-4 py-2.5 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-inner">
            <span class="absolute left-3 top-3 text-slate-400 text-xs">🔍</span>
        </div>

        <!-- Google Maps Filter Chips -->
        <div class="flex flex-wrap items-center gap-1.5 text-xs">
            <button type="button" onclick="applyQuickFilter('ALL')" id="chip-ALL"
                    class="filter-chip px-3 py-1.5 rounded-full font-bold border transition bg-blue-600 text-white border-blue-600 shadow-xs">
                🌐 Semua Kapal ({{ $stats['total'] }})
            </button>
            <button type="button" onclick="applyQuickFilter('IN_TRANSIT')" id="chip-IN_TRANSIT"
                    class="filter-chip px-3 py-1.5 rounded-full font-bold border transition bg-slate-100 text-slate-700 border-slate-200 hover:bg-slate-200">
                🚢 Lagi Transit ({{ $stats['in_transit'] }})
            </button>
            <button type="button" onclick="applyQuickFilter('WEST')" id="chip-WEST"
                    class="filter-chip px-3 py-1.5 rounded-full font-bold border transition bg-slate-100 text-slate-700 border-slate-200 hover:bg-slate-200">
                🧭 Di Barat / Eropa ({{ $stats['west_sector'] }})
            </button>
            <button type="button" onclick="applyQuickFilter('DELIVERED')" id="chip-DELIVERED"
                    class="filter-chip px-3 py-1.5 rounded-full font-bold border transition bg-slate-100 text-slate-700 border-slate-200 hover:bg-slate-200">
                ✅ Sudah Sampai ({{ $stats['delivered'] }})
            </button>
            <button type="button" onclick="applyQuickFilter('SIGNAL_OFFLINE')" id="chip-SIGNAL_OFFLINE"
                    class="filter-chip px-3 py-1.5 rounded-full font-bold border transition bg-slate-100 text-slate-700 border-slate-200 hover:bg-slate-200">
                ⚠️ Sinyal Mati / LKP ({{ $stats['signal_offline'] }})
            </button>
            <button type="button" onclick="applyQuickFilter('COFFEE')" id="chip-COFFEE"
                    class="filter-chip px-3 py-1.5 rounded-full font-bold border transition bg-slate-100 text-slate-700 border-slate-200 hover:bg-slate-200">
                ☕ Kopi ({{ $stats['coffee'] }})
            </button>
            <button type="button" onclick="applyQuickFilter('COCOA')" id="chip-COCOA"
                    class="filter-chip px-3 py-1.5 rounded-full font-bold border transition bg-slate-100 text-slate-700 border-slate-200 hover:bg-slate-200">
                🍫 Kakao ({{ $stats['cocoa'] }})
            </button>
        </div>
    </div>

    <!-- Main Map Workspace: Left Fleet Sidebar | Right Google Maps-style Leaflet Canvas -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">

        <!-- Google Maps Styled Leaflet Map Container (8 cols) -->
        <div class="lg:col-span-8 bg-white rounded-2xl border border-slate-200 shadow-md overflow-hidden flex flex-col relative">

            <!-- Floating Top Layer Switcher (Google Maps Style) -->
            <div class="absolute top-3 left-3 z-[1000] flex items-center gap-1.5 bg-white/95 backdrop-blur-md p-1 rounded-xl shadow-md border border-slate-200 text-xs font-semibold text-slate-700">
                <span class="text-[11px] font-bold px-2 text-slate-400 uppercase">Layer:</span>
                <button type="button" onclick="switchBaseMap('osm')" id="btn-layer-osm"
                        class="layer-toggle-btn px-2.5 py-1 rounded-lg text-xs font-bold transition bg-blue-600 text-white shadow-xs">
                    🗺️ Google / OSM Standard
                </button>
                <button type="button" onclick="switchBaseMap('satellite')" id="btn-layer-satellite"
                        class="layer-toggle-btn px-2.5 py-1 rounded-lg text-xs font-bold transition bg-slate-100 text-slate-700 hover:bg-slate-200">
                    🛰️ Citra Satelit
                </button>
                <button type="button" onclick="switchBaseMap('voyager')" id="btn-layer-voyager"
                        class="layer-toggle-btn px-2.5 py-1 rounded-lg text-xs font-bold transition bg-slate-100 text-slate-700 hover:bg-slate-200">
                    🌊 Peta Maritim
                </button>
            </div>

            <!-- Floating Right Controls (Google Maps Style Zoom & Target) -->
            <div class="absolute top-3 right-3 z-[1000] flex flex-col gap-1.5">
                <button type="button" onclick="resetWorldView()" title="Lihat Seluruh Dunia"
                        class="w-9 h-9 bg-white hover:bg-slate-50 text-slate-700 rounded-xl shadow-md border border-slate-200 flex items-center justify-center font-bold text-sm transition">
                    🌐
                </button>
                <button type="button" onclick="locateMyShips()" title="Pusatkan ke Kapal Saya"
                        class="w-9 h-9 bg-white hover:bg-slate-50 text-blue-600 rounded-xl shadow-md border border-slate-200 flex items-center justify-center font-bold text-sm transition">
                    🎯
                </button>
            </div>

            <!-- The Leaflet Canvas with OpenStreetMap (Google Maps Style) -->
            <div id="fleet-google-map" class="h-[650px] w-full z-10"></div>

            <!-- Map Footer Legend & Status Bar -->
            <div class="p-3 bg-slate-50 border-t border-slate-200 text-xs text-slate-600 flex flex-wrap items-center justify-between gap-3 px-4">
                <div class="flex flex-wrap items-center gap-3 text-[11px]">
                    <span class="inline-flex items-center gap-1.5 font-bold text-emerald-700">
                        <span class="w-3 h-3 rounded-full bg-emerald-500 border border-white shadow-xs"></span>
                        Pelabuhan Asal
                    </span>
                    <span class="inline-flex items-center gap-1.5 font-bold text-blue-700">
                        <span class="w-3 h-3 rounded-full bg-blue-600 border border-white shadow-xs"></span>
                        Kapal Sedang Transit
                    </span>
                    <span class="inline-flex items-center gap-1.5 font-bold text-emerald-800">
                        <span class="w-3 h-3 rounded-full bg-emerald-600 border border-white shadow-xs"></span>
                        Sudah Sampai di Tujuan
                    </span>
                    <span class="inline-flex items-center gap-1.5 font-bold text-amber-700">
                        <span class="w-3 h-3 rounded-full bg-amber-500 border-2 border-dashed border-amber-800 shadow-xs"></span>
                        Sinyal Mati / LKP (Terlacak)
                    </span>
                    <span class="inline-flex items-center gap-1.5 font-bold text-rose-700">
                        <span class="w-3 h-3 rounded-full bg-rose-600 border border-white shadow-xs"></span>
                        Pelabuhan Tujuan
                    </span>
                </div>
                <div class="text-[10px] text-slate-400 font-mono">
                    OpenStreetMap Contributors &bull; Leaflet.js Standard
                </div>
            </div>
        </div>

        <!-- Google Maps Styled Side Drawer: "Kapal-Kapal Barang Dagangan Saya" (4 cols) -->
        <div class="lg:col-span-4 space-y-3">
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-md">
                <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100">
                    <div>
                        <h2 class="text-sm font-black text-slate-900 flex items-center gap-1.5">
                            <span>🚢</span> Kapal Barang Dagangan Saya
                        </h2>
                        <p class="text-[11px] text-slate-500">Kargo kakao & kopi yang dikirim ke pelanggan</p>
                    </div>
                    <span id="vessel-count-badge" class="text-xs font-black px-2.5 py-1 bg-blue-50 text-blue-700 rounded-full border border-blue-200">
                        {{ count($mapShipments) }} Kapal
                    </span>
                </div>

                <!-- Scrollable Vessel Cards Directory -->
                <div class="space-y-3 max-h-[560px] overflow-y-auto pr-1" id="vessel-card-list">
                    @forelse($mapShipments as $s)
                    <div class="vessel-card bg-slate-50 hover:bg-blue-50/50 p-3.5 rounded-xl border border-slate-200 hover:border-blue-400 transition cursor-pointer shadow-xs"
                         onclick="flyToVessel({{ $s['id'] }})"
                         data-id="{{ $s['id'] }}"
                         data-status="{{ $s['status'] }}"
                         data-west="{{ $s['is_west_sector'] ? 'true' : 'false' }}"
                         data-signal="{{ $s['is_signal_offline'] ? 'OFFLINE' : 'ACTIVE' }}"
                         data-commodity="{{ $s['commodity'] }}"
                         data-search="{{ strtolower($s['number'] . ' ' . $s['vessel_name'] . ' ' . $s['cargo_type'] . ' ' . $s['customer']['name'] . ' ' . $s['customer']['company'] . ' ' . $s['origin']['city'] . ' ' . $s['destination']['city'] . ' ' . $s['current_position']['location_name']) }}">

                        <!-- Card Header: Vessel Name & Navigation Badge -->
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <div class="flex items-center gap-1.5">
                                    <span class="text-sm font-extrabold text-slate-900">{{ $s['vessel_name'] }}</span>
                                    <span class="text-[10px] text-slate-400 font-mono">({{ $s['voyage_number'] }})</span>
                                </div>
                                <div class="text-[11px] font-mono text-blue-700 font-bold mt-0.5">
                                    {{ $s['number'] }} &bull; {{ $s['carrier'] }}
                                </div>
                            </div>

                            <!-- Navigation Status Badge -->
                            <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-md shadow-2xs
                                @if($s['status'] === 'DELIVERED') bg-emerald-100 text-emerald-800 border border-emerald-300
                                @elseif($s['status'] === 'IN_TRANSIT') bg-blue-100 text-blue-800 border border-blue-300
                                @else bg-slate-200 text-slate-700 border border-slate-300 @endif">
                                {{ $s['navigation_status'] }}
                            </span>
                        </div>

                        <!-- Commodity & Consignee Buyer Info -->
                        <div class="mt-2.5 p-2 bg-white rounded-lg border border-slate-200 text-[11px] space-y-1">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-slate-800 flex items-center gap-1">
                                    @if($s['commodity'] === 'COCOA') 🍫 @else ☕ @endif
                                    {{ $s['cargo_type'] }}
                                </span>
                                <span class="text-[10px] text-slate-500 font-bold">{{ $s['weight_formatted'] }}</span>
                            </div>
                            <div class="text-[10px] text-slate-600 border-t border-slate-100 pt-1 flex items-center justify-between">
                                <span><strong>Pelanggan:</strong> {{ $s['customer']['name'] }}</span>
                                <span class="text-slate-400">{{ $s['destination']['city'] }}</span>
                            </div>
                        </div>

                        <!-- Route: Origin -> Destination -->
                        <div class="mt-2 text-[11px] text-slate-600 flex items-center gap-1.5">
                            <span class="text-emerald-600 font-bold">🟢 {{ $s['origin']['city'] }}</span>
                            <span class="text-slate-400">&rarr;</span>
                            <span class="text-rose-600 font-bold">🔴 {{ $s['destination']['city'] }}</span>
                            @if($s['is_west_sector'])
                                <span class="ml-auto px-1.5 py-0.2 bg-indigo-50 text-indigo-700 border border-indigo-200 rounded text-[9px] font-bold">
                                    Jalur Barat
                                </span>
                            @endif
                        </div>

                        <!-- Live Position & Signal Telemetry Status -->
                        <div class="mt-2 p-2 rounded-lg text-[11px]
                            @if($s['is_signal_offline']) bg-amber-50 border border-amber-200 text-amber-900
                            @elseif($s['status'] === 'DELIVERED') bg-emerald-50 border border-emerald-200 text-emerald-900
                            @else bg-slate-100 border border-slate-200 text-slate-800 @endif">

                            <div class="flex items-center justify-between text-[10px] font-extrabold pb-0.5">
                                <span>{{ $s['signal_label'] }}</span>
                                <span class="font-mono">{{ $s['current_position']['progress_percent'] }}%</span>
                            </div>

                            <div class="text-xs font-bold text-slate-900 mt-0.5">
                                📍 {{ $s['current_position']['location_name'] }}
                            </div>

                            <div class="text-[10px] text-slate-500 mt-1 flex items-center justify-between">
                                <span>Lat: {{ number_format($s['current_position']['latitude'], 3) }}°, Lon: {{ number_format($s['current_position']['longitude'], 3) }}°</span>
                                <a href="{{ $s['detail_url'] }}" class="text-blue-600 hover:text-blue-800 font-bold underline" onclick="event.stopPropagation()">
                                    Rincian &rarr;
                                </a>
                            </div>

                            @if($s['is_signal_offline'])
                            <div class="mt-1 text-[9px] text-amber-800 leading-tight border-t border-amber-200/60 pt-1">
                                Transponder kapal offline di laut lepas. Menampilkan <strong>Last Known Position (LKP)</strong> yang terekam secara real-time.
                            </div>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="p-8 text-center text-slate-500 text-xs">
                        Tidak ada kapal atau pengiriman kargo yang ditemukan.
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
    const fleetShipments = {!! json_encode($mapShipments) !!};
    let fleetMap;
    let currentBaseLayer;
    let baseTileLayers = {};
    const vesselMarkers = {};
    const routePolylines = [];
    let globalBounds;

    /**
     * Bearing calculator for ship heading
     */
    function calculateBearing(lat1, lon1, lat2, lon2) {
        const toRad = Math.PI / 180;
        const toDeg = 180 / Math.PI;
        const dLon = (lon2 - lon1) * toRad;
        const y = Math.sin(dLon) * Math.cos(lat2 * toRad);
        const x = Math.cos(lat1 * toRad) * Math.sin(lat2 * toRad) -
                  Math.sin(lat1 * toRad) * Math.cos(lat2 * toRad) * Math.cos(dLon);
        return (Math.atan2(y, x) * toDeg + 360) % 360;
    }

    /**
     * Switch Google Maps / Leaflet Tile Layer
     */
    function switchBaseMap(layerKey) {
        if (!fleetMap || !baseTileLayers[layerKey]) return;
        if (currentBaseLayer) {
            fleetMap.removeLayer(currentBaseLayer);
        }
        currentBaseLayer = baseTileLayers[layerKey];
        currentBaseLayer.addTo(fleetMap);

        document.querySelectorAll('.layer-toggle-btn').forEach(b => {
            b.classList.remove('bg-blue-600', 'text-white', 'shadow-xs');
            b.classList.add('bg-slate-100', 'text-slate-700');
        });
        const activeBtn = document.getElementById('btn-layer-' + layerKey);
        if (activeBtn) {
            activeBtn.classList.add('bg-blue-600', 'text-white', 'shadow-xs');
            activeBtn.classList.remove('bg-slate-100', 'text-slate-700');
        }
    }

    /**
     * Reset View to Worldwide Fleet
     */
    function resetWorldView() {
        if (fleetMap && globalBounds && !globalBounds.isEmpty()) {
            fleetMap.fitBounds(globalBounds, { padding: [50, 50], maxZoom: 6 });
        } else if (fleetMap) {
            fleetMap.setView([15.0, 45.0], 3);
        }
    }

    /**
     * Locate My Ships
     */
    function locateMyShips() {
        if (!fleetMap) return;
        const myShipBounds = L.latLngBounds();
        fleetShipments.forEach(s => {
            if (s.current_position && s.current_position.latitude && s.current_position.longitude) {
                myShipBounds.extend([s.current_position.latitude, s.current_position.longitude]);
            }
        });
        if (!myShipBounds.isEmpty()) {
            fleetMap.fitBounds(myShipBounds, { padding: [50, 50], maxZoom: 6 });
        }
    }

    /**
     * Fly smoothly to a specific vessel and open InfoWindow
     */
    function flyToVessel(id) {
        const marker = vesselMarkers[id];
        if (marker && fleetMap) {
            const latLng = marker.getLatLng();
            fleetMap.flyTo(latLng, 7, { animate: true, duration: 1.2 });
            setTimeout(() => {
                marker.openPopup();
            }, 1250);

            // Highlight side card
            document.querySelectorAll('.vessel-card').forEach(c => c.classList.remove('ring-2', 'ring-blue-500'));
            const activeCard = document.querySelector(`.vessel-card[data-id="${id}"]`);
            if (activeCard) {
                activeCard.classList.add('ring-2', 'ring-blue-500');
                activeCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }
    }

    /**
     * Quick Filter Chips
     */
    function applyQuickFilter(type) {
        document.querySelectorAll('.filter-chip').forEach(b => {
            b.classList.remove('bg-blue-600', 'text-white', 'border-blue-600', 'shadow-xs');
            b.classList.add('bg-slate-100', 'text-slate-700', 'border-slate-200');
        });
        const activeChip = document.getElementById('chip-' + type);
        if (activeChip) {
            activeChip.classList.add('bg-blue-600', 'text-white', 'border-blue-600', 'shadow-xs');
            activeChip.classList.remove('bg-slate-100', 'text-slate-700', 'border-slate-200');
        }

        const cards = document.querySelectorAll('.vessel-card');
        let visibleCount = 0;

        cards.forEach(card => {
            const status = card.getAttribute('data-status');
            const isWest = card.getAttribute('data-west') === 'true';
            const signal = card.getAttribute('data-signal');
            const commodity = card.getAttribute('data-commodity');
            const id = parseInt(card.getAttribute('data-id'));

            let show = false;
            if (type === 'ALL') show = true;
            else if (type === 'IN_TRANSIT' && status === 'IN_TRANSIT') show = true;
            else if (type === 'DELIVERED' && status === 'DELIVERED') show = true;
            else if (type === 'WEST' && isWest) show = true;
            else if (type === 'SIGNAL_OFFLINE' && signal === 'OFFLINE') show = true;
            else if (type === 'COFFEE' && commodity === 'COFFEE') show = true;
            else if (type === 'COCOA' && commodity === 'COCOA') show = true;

            if (show) {
                card.style.display = 'block';
                visibleCount++;
                if (vesselMarkers[id]) vesselMarkers[id].addTo(fleetMap);
            } else {
                card.style.display = 'none';
                if (vesselMarkers[id]) fleetMap.removeLayer(vesselMarkers[id]);
            }
        });

        const badge = document.getElementById('vessel-count-badge');
        if (badge) badge.innerText = `${visibleCount} Kapal`;
    }

    document.addEventListener('DOMContentLoaded', function () {

        // 1. Initialize Map Instance
        fleetMap = L.map('fleet-google-map', {
            center: [15.0, 45.0],
            zoom: 3,
            minZoom: 2,
            maxZoom: 18,
            zoomControl: false,
            worldCopyJump: true
        });

        // Sleek Google Maps Style Zoom Controls at bottom-right
        L.control.zoom({ position: 'bottomright' }).addTo(fleetMap);

        // 2. Base Tile Layers (Standard OpenStreetMap as default per PPT Slide 09!)
        baseTileLayers = {
            osm: L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }),
            satellite: L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                maxZoom: 18,
                attribution: '&copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community'
            }),
            voyager: L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                maxZoom: 19,
                subdomains: 'abcd',
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>'
            })
        };

        // Set OpenStreetMap standard as default
        currentBaseLayer = baseTileLayers.osm;
        currentBaseLayer.addTo(fleetMap);

        globalBounds = L.latLngBounds();

        // 3. Render Ships, Routes, and Google Maps Style Pins
        fleetShipments.forEach((s) => {
            const pos = s.current_position;
            const origin = s.origin;
            const dest = s.destination;

            // Google Maps Style Origin Pin (Green with white center)
            if (origin.lat && origin.lng) {
                const originPinHtml = `
                    <div class="relative flex items-center justify-center cursor-pointer" style="width: 26px; height: 32px; margin-left: -13px; margin-top: -32px;">
                        <svg width="26" height="32" viewBox="0 0 24 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 0C5.373 0 0 5.373 0 12C0 21 12 32 12 32C12 32 24 21 24 12C24 5.373 18.627 0 12 0Z" fill="#10b981"/>
                            <circle cx="12" cy="11" r="5" fill="#ffffff"/>
                            <text x="12" y="14" font-size="8" font-weight="900" text-anchor="middle" fill="#065f46">A</text>
                        </svg>
                    </div>
                `;
                L.marker([origin.lat, origin.lng], {
                    icon: L.divIcon({ html: originPinHtml, className: 'gmap-origin-pin', iconSize: [26, 32] }),
                    zIndexOffset: 300
                }).addTo(fleetMap).bindPopup(`
                    <div class="p-2 font-sans text-xs">
                        <div class="font-extrabold text-emerald-800 text-[10px] uppercase">PELABUHAN ASAL (ORIGIN PORT)</div>
                        <div class="text-sm font-extrabold text-slate-900 mt-0.5">${origin.name}</div>
                        <div class="text-slate-500 text-[11px]">${origin.city}, ${origin.country}</div>
                        <div class="text-[10px] text-slate-400 mt-1">Kargo: <strong>${s.number}</strong> &bull; ${s.cargo_type}</div>
                    </div>
                `);
                globalBounds.extend([origin.lat, origin.lng]);
            }

            // Google Maps Style Destination Pin (Red with checkered flag / B)
            if (dest.lat && dest.lng) {
                const destPinHtml = `
                    <div class="relative flex items-center justify-center cursor-pointer" style="width: 26px; height: 32px; margin-left: -13px; margin-top: -32px;">
                        <svg width="26" height="32" viewBox="0 0 24 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 0C5.373 0 0 5.373 0 12C0 21 12 32 12 32C12 32 24 21 24 12C24 5.373 18.627 0 12 0Z" fill="#ef4444"/>
                            <circle cx="12" cy="11" r="5" fill="#ffffff"/>
                            <text x="12" y="14" font-size="8" font-weight="900" text-anchor="middle" fill="#991b1b">B</text>
                        </svg>
                    </div>
                `;
                L.marker([dest.lat, dest.lng], {
                    icon: L.divIcon({ html: destPinHtml, className: 'gmap-dest-pin', iconSize: [26, 32] }),
                    zIndexOffset: 300
                }).addTo(fleetMap).bindPopup(`
                    <div class="p-2 font-sans text-xs">
                        <div class="font-extrabold text-rose-800 text-[10px] uppercase">PELABUHAN TUJUAN (DESTINATION PORT)</div>
                        <div class="text-sm font-extrabold text-slate-900 mt-0.5">${dest.name}</div>
                        <div class="text-slate-500 text-[11px]">${dest.city}, ${dest.country}</div>
                        <div class="text-[10px] text-slate-400 mt-1">Pembeli: <strong>${s.customer.name}</strong></div>
                    </div>
                `);
                globalBounds.extend([dest.lat, dest.lng]);
            }

            // Build Route Coordinates
            const routePts = [];
            if (origin.lat && origin.lng) routePts.push([origin.lat, origin.lng]);
            if (s.waypoints && s.waypoints.length > 0) {
                s.waypoints.forEach(wp => routePts.push([wp.lat, wp.lng]));
            }
            if (pos.latitude && pos.longitude) routePts.push([pos.latitude, pos.longitude]);
            if (dest.lat && dest.lng) routePts.push([dest.lat, dest.lng]);

            const cleanPts = [];
            for (let i = 0; i < routePts.length; i++) {
                if (i === 0 || routePts[i][0] !== routePts[i-1][0] || routePts[i][1] !== routePts[i-1][1]) {
                    cleanPts.push(routePts[i]);
                }
            }

            // Draw Planned Maritime Polyline (Google Maps Style Blue Ocean Route)
            if (cleanPts.length >= 2) {
                const poly = L.polyline(cleanPts, {
                    color: s.status === 'DELIVERED' ? '#10b981' : (s.commodity === 'COCOA' ? '#92400e' : '#0284c7'),
                    weight: 3.5,
                    opacity: 0.65,
                    dashArray: s.status === 'DELIVERED' ? null : '6, 6'
                }).addTo(fleetMap);
                routePolylines.push(poly);
            }

            // Real-Time Vessel Marker on Current Position
            if (pos.latitude && pos.longitude) {
                const isCoffee = s.commodity === 'COFFEE';
                const isOffline = s.is_signal_offline;
                const isDelivered = s.status === 'DELIVERED';

                // Determine bearing towards next waypoint or destination
                let bearing = 90;
                if (cleanPts.length >= 2) {
                    const nextPt = cleanPts[cleanPts.length - 1];
                    bearing = calculateBearing(pos.latitude, pos.longitude, nextPt[0], nextPt[1]);
                }

                // Vessel Marker HTML with Google Maps Clean Style
                const vesselMarkerHtml = `
                    <div class="relative flex flex-col items-center pointer-events-auto cursor-pointer" style="width: 100px; margin-left: -50px; margin-top: -38px;">

                        <!-- Floating Name & Status Badge -->
                        <div class="bg-white/95 text-slate-900 font-extrabold text-[10px] px-2 py-0.5 rounded-full shadow-md border border-slate-200 whitespace-nowrap mb-1 flex items-center gap-1.5">
                            ${isOffline ? `
                                <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                                <span class="text-amber-800">${s.vessel_name} (LKP)</span>
                            ` : (isDelivered ? `
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                <span class="text-emerald-800">${s.vessel_name} (Sampai)</span>
                            ` : `
                                <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
                                <span class="text-blue-900">${s.vessel_name}</span>
                            `)}
                        </div>

                        <!-- Directional Container Ship Body (Rotated to bearing) -->
                        <div class="relative w-12 h-6 flex items-center justify-center transform transition-transform duration-100" style="transform: rotate(${Math.round(bearing)}deg);">
                            <!-- Radar Glow / Signal Ripple -->
                            ${isOffline ? `
                                <div class="absolute w-9 h-9 rounded-full bg-amber-400/40 border-2 border-dashed border-amber-600 animate-pulse"></div>
                            ` : (!isDelivered ? `
                                <div class="absolute w-8 h-8 rounded-full bg-blue-400/30 animate-ping"></div>
                            ` : '')}

                            <!-- 3D Container Ship SVG -->
                            <svg width="46" height="22" viewBox="0 0 60 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M4 16L10 25H48L56 16H4Z" fill="${isOffline ? '#78350f' : (isDelivered ? '#065f46' : '#1e3a8a')}" stroke="#0f172a" stroke-width="1.2"/>
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

                const vMarker = L.marker([pos.latitude, pos.longitude], {
                    icon: L.divIcon({
                        html: vesselMarkerHtml,
                        className: 'gmap-vessel-marker',
                        iconSize: [100, 50],
                        iconAnchor: [50, 38],
                        popupAnchor: [0, -38]
                    }),
                    zIndexOffset: isOffline ? 800 : 900
                }).addTo(fleetMap);

                // Google Maps Style Rich InfoWindow Popup
                const infoWindowHtml = `
                    <div class="p-1 font-sans text-xs min-w-[270px]">
                        <!-- InfoWindow Header -->
                        <div class="flex items-center justify-between pb-1.5 mb-2 border-b border-slate-100">
                            <div>
                                <div class="text-[10px] font-extrabold uppercase tracking-wider ${isOffline ? 'text-amber-800' : 'text-blue-800'}">
                                    ${s.signal_label}
                                </div>
                                <div class="text-base font-black text-slate-900 mt-0.5">${s.vessel_name}</div>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase ${s.status === 'DELIVERED' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800'}">
                                ${s.navigation_status}
                            </span>
                        </div>

                        <!-- Commodity & Buyer Specs -->
                        <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100 space-y-1 text-[11px] text-slate-700">
                            <div><strong>Muatan:</strong> ${s.cargo_type} &bull; ${s.weight_formatted}</div>
                            <div><strong>Pembeli (Pelanggan):</strong> ${s.customer.name} (${s.customer.company})</div>
                            <div><strong>Rute Pelayaran:</strong> ${origin.city} &rarr; ${dest.city}</div>
                            <div><strong>Koridor:</strong> ${s.sector_label}</div>
                        </div>

                        <!-- Real-Time Position & Telemetry -->
                        <div class="mt-2 space-y-1 text-[11px] text-slate-600 bg-white p-2 rounded-lg border border-slate-200">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-slate-800">📍 Lokasi Kargo:</span>
                                <span class="font-mono text-[10px]">${pos.progress_percent}% Tuntas</span>
                            </div>
                            <div class="font-extrabold text-blue-900 text-xs">${pos.location_name}</div>
                            <div class="text-[10px] text-slate-500">Koordinat: ${pos.latitude.toFixed(4)}°, ${pos.longitude.toFixed(4)}°</div>
                            <div class="text-[10px] text-slate-400">Pembaruan: ${pos.last_updated}</div>
                        </div>

                        <!-- Offline / Lost Signal Telemetry Warning if any -->
                        ${isOffline ? `
                        <div class="mt-2 p-2 bg-amber-50 rounded-lg border border-amber-200 text-[10px] text-amber-900">
                            <strong>⚠️ Status Sinyal AIS Mati:</strong> Transponder satelit offline di laut lepas. Menampilkan <em>Last Known Position (LKP)</em> koordinat terakhir yang terekam secara real-time.
                        </div>
                        ` : ''}

                        <!-- Action Footer Button -->
                        <div class="mt-3 pt-2 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-[10px] text-slate-400 font-mono">${s.number}</span>
                            <a href="${s.detail_url}" class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs rounded-lg shadow-xs transition">
                                Buka Rincian Kargo &rarr;
                            </a>
                        </div>
                    </div>
                `;

                vMarker.bindPopup(infoWindowHtml);
                vesselMarkers[s.id] = vMarker;
                globalBounds.extend([pos.latitude, pos.longitude]);
            }
        });

        // Fit map bounds to encompass all fleet vessels & routes
        if (!globalBounds.isEmpty()) {
            fleetMap.fitBounds(globalBounds, { padding: [50, 50], maxZoom: 6 });
        }

        // Floating Search Filter handler
        const searchInput = document.getElementById('map-search-bar');
        if (searchInput) {
            searchInput.addEventListener('input', function (e) {
                const term = e.target.value.toLowerCase().trim();
                const cards = document.querySelectorAll('.vessel-card');
                let matchCount = 0;

                cards.forEach(card => {
                    const text = card.getAttribute('data-search') || '';
                    const id = parseInt(card.getAttribute('data-id'));
                    if (text.includes(term)) {
                        card.style.display = 'block';
                        matchCount++;
                        if (vesselMarkers[id]) vesselMarkers[id].addTo(fleetMap);
                    } else {
                        card.style.display = 'none';
                        if (vesselMarkers[id]) fleetMap.removeLayer(vesselMarkers[id]);
                    }
                });

                const badge = document.getElementById('vessel-count-badge');
                if (badge) badge.innerText = `${matchCount} Kapal`;
            });
        }
    });
</script>
@endsection