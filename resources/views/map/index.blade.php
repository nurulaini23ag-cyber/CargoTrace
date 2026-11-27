@extends('layouts.app')

@section('title', 'Global Cargo Fleet Map & Maritime Tracking — CargoTrace')

@section('content')
<div class="space-y-4 font-sans">

    <!-- Top Floating Header Bar -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 rounded-2xl p-5 md:p-6 text-white shadow-xl border border-slate-700/70 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex flex-wrap items-center gap-2 mb-1.5">
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-black bg-[#C49A55] text-slate-950 uppercase tracking-wider shadow-sm">
                    LEAFLET + OPENSTREETMAP (MARITIME FLOW CHART)
                </span>
                <span class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-400 bg-emerald-950/80 px-2.5 py-0.5 rounded-full border border-emerald-500/30">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                    Accurate Sea Lanes (No Overland Crossing)
                </span>
            </div>
            <h1 class="text-2xl md:text-3xl font-black tracking-tight text-white flex items-center gap-2">
                <span>Global Cargo Map & Customer Fleet Tracking</span>
            </h1>
            <p class="text-xs md:text-sm text-slate-300 mt-1 max-w-3xl leading-relaxed">
                Real-time tracking of coffee and cocoa cargo vessels across international maritime corridors with status color coding: <strong class="text-emerald-400">🟢 Delivered</strong>, <strong class="text-sky-400">🔵 In Transit</strong>, and <strong class="text-rose-400">🔴 Delayed / Alert</strong>.
            </p>
        </div>

        <!-- 4-Tier Status Metric Badges -->
        <div class="flex flex-wrap items-center gap-2 shrink-0">
            <div class="px-3.5 py-2 bg-slate-800/80 border border-slate-700 rounded-xl text-center min-w-[85px]">
                <div class="text-[10px] uppercase font-bold text-slate-400">All Vessels</div>
                <div class="text-lg font-black text-white" id="stat-total">{{ $stats['total'] }}</div>
            </div>
            <div class="px-3.5 py-2 bg-emerald-950/60 border border-emerald-500/40 rounded-xl text-center min-w-[85px]">
                <div class="text-[10px] uppercase font-bold text-emerald-300">🟢 Delivered</div>
                <div class="text-lg font-black text-emerald-400" id="stat-delivered">{{ $stats['delivered'] }}</div>
            </div>
            <div class="px-3.5 py-2 bg-sky-950/60 border border-sky-500/40 rounded-xl text-center min-w-[85px]">
                <div class="text-[10px] uppercase font-bold text-sky-300">🔵 In Transit</div>
                <div class="text-lg font-black text-sky-400" id="stat-sailing">{{ $stats['in_transit'] }}</div>
            </div>
            <div class="px-3.5 py-2 bg-rose-950/60 border border-rose-500/40 rounded-xl text-center min-w-[85px]">
                <div class="text-[10px] uppercase font-bold text-rose-300">🔴 Delayed</div>
                <div class="text-lg font-black text-rose-400" id="stat-delayed">{{ $stats['delayed'] }}</div>
            </div>
            @if(($stats['staged'] ?? 0) > 0)
            <div class="px-3.5 py-2 bg-amber-950/60 border border-amber-500/40 rounded-xl text-center min-w-[85px]">
                <div class="text-[10px] uppercase font-bold text-amber-300">📦 At Origin</div>
                <div class="text-lg font-black text-amber-400" id="stat-staged">{{ $stats['staged'] }}</div>
            </div>
            @endif
        </div>
    </div>

    <!-- Quick Filter Bar & Route View Mode Toggles -->
    <div class="bg-white p-3.5 rounded-2xl border border-slate-200 shadow-sm flex flex-wrap items-center justify-between gap-3">
        
        <!-- Search Input -->
        <div class="relative flex-1 min-w-[260px] max-w-md">
            <input type="text" id="map-search-bar" placeholder="Search vessel name, shipment #, commodity, buyer, or port..."
                   class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl pl-9 pr-4 py-2.5 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-inner">
            <span class="absolute left-3 top-3 text-slate-400 text-xs">🔍</span>
        </div>

        <!-- 4-Tier Status Filter Chips -->
        <div class="flex flex-wrap items-center gap-1.5 text-xs">
            <button type="button" onclick="applyQuickFilter('ALL')" id="chip-ALL"
                    class="filter-chip px-3 py-1.5 rounded-full font-black border transition bg-slate-900 text-white border-slate-900 shadow-xs cursor-pointer">
                🌐 All Vessels ({{ $stats['total'] }})
            </button>
            <button type="button" onclick="applyQuickFilter('DELIVERED')" id="chip-DELIVERED"
                    class="filter-chip px-3 py-1.5 rounded-full font-bold border transition bg-emerald-50 text-emerald-800 border-emerald-300 hover:bg-emerald-100 cursor-pointer">
                🟢 Delivered ({{ $stats['delivered'] }})
            </button>
            <button type="button" onclick="applyQuickFilter('IN_TRANSIT')" id="chip-IN_TRANSIT"
                    class="filter-chip px-3 py-1.5 rounded-full font-bold border transition bg-sky-50 text-sky-800 border-sky-300 hover:bg-sky-100 cursor-pointer">
                🔵 In Transit ({{ $stats['in_transit'] }})
            </button>
            <button type="button" onclick="applyQuickFilter('DELAYED')" id="chip-DELAYED"
                    class="filter-chip px-3 py-1.5 rounded-full font-bold border transition bg-rose-50 text-rose-800 border-rose-300 hover:bg-rose-100 cursor-pointer">
                🔴 Delayed ({{ $stats['delayed'] }})
            </button>
            @if(($stats['staged'] ?? 0) > 0)
            <button type="button" onclick="applyQuickFilter('STAGED')" id="chip-STAGED"
                    class="filter-chip px-3 py-1.5 rounded-full font-bold border transition bg-amber-50 text-amber-800 border-amber-300 hover:bg-amber-100 cursor-pointer">
                📦 At Origin Port ({{ $stats['staged'] }})
            </button>
            @endif
            <button type="button" onclick="applyQuickFilter('WEST')" id="chip-WEST"
                    class="filter-chip px-3 py-1.5 rounded-full font-bold border transition bg-indigo-50 text-indigo-800 border-indigo-200 hover:bg-indigo-100 cursor-pointer">
                🇪🇺 West Sector (Europe)
            </button>
            <button type="button" onclick="applyQuickFilter('OFFLINE')" id="chip-OFFLINE"
                    class="filter-chip px-3 py-1.5 rounded-full font-bold border transition bg-amber-50 text-amber-800 border-amber-300 hover:bg-amber-100 cursor-pointer">
                ⚠️ Offline Signal (LKP)
            </button>
            <button type="button" onclick="applyQuickFilter('COFFEE')" id="chip-COFFEE"
                    class="filter-chip px-3 py-1.5 rounded-full font-bold border transition bg-slate-100 text-slate-700 border-slate-200 hover:bg-slate-200 cursor-pointer">
                ☕ Coffee
            </button>
            <button type="button" onclick="applyQuickFilter('COCOA')" id="chip-COCOA"
                    class="filter-chip px-3 py-1.5 rounded-full font-bold border transition bg-slate-100 text-slate-700 border-slate-200 hover:bg-slate-200 cursor-pointer">
                🍫 Cocoa
            </button>
        </div>

        <!-- Route Mode Toggle: Single Focus vs All Lines -->
        <div class="flex items-center gap-1.5 bg-slate-100 p-1 rounded-xl border border-slate-200 text-xs">
            <button type="button" onclick="toggleRouteMode('FOCUS')" id="btn-mode-focus"
                    class="px-2.5 py-1 rounded-lg font-bold text-[11px] transition bg-white text-slate-900 shadow-xs cursor-pointer">
                🎯 Focus 1 Route (Clean View)
            </button>
            <button type="button" onclick="toggleRouteMode('ALL')" id="btn-mode-all"
                    class="px-2.5 py-1 rounded-lg font-bold text-[11px] transition text-slate-600 hover:text-slate-900 cursor-pointer">
                🌐 Show All Sea Lanes
            </button>
        </div>
    </div>

    <!-- Main Map Workspace: Left Fleet Directory Drawer | Right Leaflet Canvas -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">

        <!-- Leaflet Map Canvas (8 cols) -->
        <div class="lg:col-span-8 bg-white rounded-2xl border border-slate-200 shadow-md overflow-hidden flex flex-col relative">

            <!-- Floating Top Layer Switcher -->
            <div class="absolute top-3 left-3 z-[1000] flex items-center gap-1.5 bg-white/95 backdrop-blur-md p-1 rounded-xl shadow-md border border-slate-200 text-xs font-semibold text-slate-700">
                <span class="text-[10px] font-bold px-2 text-slate-400 uppercase">MAP:</span>
                <button type="button" onclick="switchBaseMap('voyager')" id="btn-layer-voyager"
                        class="layer-toggle-btn px-2.5 py-1 rounded-lg text-xs font-bold transition bg-blue-600 text-white shadow-xs cursor-pointer">
                    🌊 Nautical Sea Chart (English)
                </button>
                <button type="button" onclick="switchBaseMap('osm')" id="btn-layer-osm"
                        class="layer-toggle-btn px-2.5 py-1 rounded-lg text-xs font-bold transition bg-slate-100 text-slate-700 hover:bg-slate-200 cursor-pointer">
                    🗺️ Standard Map
                </button>
                <button type="button" onclick="switchBaseMap('satellite')" id="btn-layer-satellite"
                        class="layer-toggle-btn px-2.5 py-1 rounded-lg text-xs font-bold transition bg-slate-100 text-slate-700 hover:bg-slate-200 cursor-pointer">
                    🛰️ Satellite Imagery
                </button>
            </div>

            <!-- Floating Right Controls -->
            <div class="absolute top-3 right-3 z-[1000] flex flex-col gap-1.5">
                <button type="button" onclick="resetWorldView()" title="Reset Global View"
                        class="w-9 h-9 bg-white hover:bg-slate-50 text-slate-700 rounded-xl shadow-md border border-slate-200 flex items-center justify-center font-bold text-sm transition cursor-pointer">
                    🌐
                </button>
                <button type="button" onclick="locateMyShips()" title="Fit to My Fleet"
                        class="w-9 h-9 bg-white hover:bg-slate-50 text-blue-600 rounded-xl shadow-md border border-slate-200 flex items-center justify-center font-bold text-sm transition cursor-pointer">
                    🎯
                </button>
            </div>

            <!-- Active Selected Vessel Banner (Floating at Top of Map Canvas) -->
            <div id="active-vessel-banner" class="hidden absolute top-14 left-3 right-3 z-[1000] bg-slate-900/95 backdrop-blur-md text-white p-2.5 rounded-xl shadow-xl border border-slate-700 flex items-center justify-between gap-3 transition-all duration-300">
                <div class="flex items-center gap-2.5 truncate">
                    <div id="banner-status-icon" class="w-7 h-7 rounded-lg flex items-center justify-center text-sm font-bold shrink-0 bg-blue-600">
                        🚢
                    </div>
                    <div class="truncate">
                        <div class="flex items-center gap-2">
                            <span id="banner-vessel-name" class="font-black text-xs text-white">MV Nusantara Jaya</span>
                            <span id="banner-status-badge" class="px-2 py-0.5 rounded text-[9px] font-black uppercase bg-blue-500/30 text-blue-300 border border-blue-400/40">
                                IN TRANSIT
                            </span>
                        </div>
                        <div id="banner-route-text" class="text-[11px] text-slate-300 mt-0.5 truncate font-medium">
                            Origin Port &rarr; Destination Port &bull; International Sea Lane
                        </div>
                    </div>
                </div>
                <button type="button" onclick="clearActiveVesselFocus()" class="px-2.5 py-1 bg-white/10 hover:bg-white/20 text-white rounded-lg text-[11px] font-bold shrink-0 cursor-pointer border border-white/20 transition">
                    ✕ Close Focus
                </button>
            </div>

            <!-- The Leaflet Canvas with OpenStreetMap -->
            <div id="fleet-google-map" class="h-[680px] w-full z-10"></div>

            <!-- Map Footer Legend Bar -->
            <div class="p-3 bg-slate-50 border-t border-slate-200 text-xs text-slate-600 flex flex-wrap items-center justify-between gap-3 px-4">
                <div class="flex flex-wrap items-center gap-3 text-[11px]">
                    <span class="inline-flex items-center gap-1.5 font-bold text-emerald-700">
                        <span class="w-3.5 h-3.5 rounded-full bg-emerald-500 border-2 border-white shadow-xs"></span>
                        🟢 Delivered at Destination
                    </span>
                    <span class="inline-flex items-center gap-1.5 font-bold text-sky-700">
                        <span class="w-3.5 h-3.5 rounded-full bg-sky-500 border-2 border-white shadow-xs"></span>
                        🔵 In Transit (On Schedule)
                    </span>
                    <span class="inline-flex items-center gap-1.5 font-bold text-rose-700">
                        <span class="w-3.5 h-3.5 rounded-full bg-rose-600 border-2 border-white shadow-xs"></span>
                        🔴 Delayed / Exception Alert
                    </span>
                    <span class="inline-flex items-center gap-1.5 font-bold text-slate-600">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span> Origin Port (A)
                    </span>
                    <span class="inline-flex items-center gap-1.5 font-bold text-slate-600">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-600"></span> Destination Port (B)
                    </span>
                </div>
                <div class="text-[10px] text-slate-400 font-mono">
                    OpenStreetMap Contributors &bull; CARTO Voyager &bull; Leaflet Maritime Layer
                </div>
            </div>
        </div>

        <!-- Right Side Drawer: Vessel Cards Directory (4 cols) -->
        <div class="lg:col-span-4 space-y-3">
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-md">
                <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100">
                    <div>
                        <h2 class="text-sm font-black text-slate-900 flex items-center gap-1.5">
                            <span>🚢</span> My Cargo Fleet
                        </h2>
                        <p class="text-[11px] text-slate-500">Click card to focus vessel route on map</p>
                    </div>
                    <span id="vessel-count-badge" class="text-xs font-black px-2.5 py-1 bg-slate-900 text-white rounded-full">
                        {{ count($mapShipments) }} Vessels
                    </span>
                </div>

                <!-- Scrollable Vessel Cards Directory -->
                <div class="space-y-3 max-h-[580px] overflow-y-auto pr-1" id="vessel-card-list">
                    @forelse($mapShipments as $s)
                    <div class="vessel-card p-3.5 rounded-xl border transition cursor-pointer shadow-xs relative overflow-hidden
                         @if($s['status_type'] === 'DELIVERED')
                             bg-emerald-50/40 border-emerald-200 hover:border-emerald-500
                         @elseif($s['status_type'] === 'DELAYED')
                             bg-rose-50/40 border-rose-200 hover:border-rose-500
                         @elseif($s['status_type'] === 'STAGED')
                             bg-amber-50/40 border-amber-200 hover:border-amber-500
                         @else
                             bg-sky-50/30 border-sky-200 hover:border-sky-500
                         @endif"
                         onclick="selectShipmentRoute({{ $s['id'] }})"
                         data-id="{{ $s['id'] }}"
                         data-status="{{ $s['status_type'] }}"
                         data-commodity="{{ $s['commodity'] }}"
                         data-west="{{ $s['is_west_sector'] ? '1' : '0' }}"
                         data-offline="{{ !empty($s['is_signal_offline']) ? '1' : '0' }}"
                         data-search="{{ strtolower($s['number'] . ' ' . $s['vessel_name'] . ' ' . $s['cargo_type'] . ' ' . $s['customer']['name'] . ' ' . $s['customer']['company'] . ' ' . $s['origin']['city'] . ' ' . $s['destination']['city']) }}">

                        <!-- Top Row: Vessel Name, Status Pill & Color Indicator -->
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <div class="flex items-center gap-1.5">
                                    <span class="text-sm font-black text-slate-900">{{ $s['vessel_name'] }}</span>
                                    <span class="text-[10px] text-slate-400 font-mono">({{ $s['voyage_number'] }})</span>
                                </div>
                                <div class="text-[11px] font-mono font-bold mt-0.5
                                    @if($s['status_type'] === 'DELIVERED') text-emerald-700
                                    @elseif($s['status_type'] === 'DELAYED') text-rose-700
                                    @elseif($s['status_type'] === 'STAGED') text-amber-700
                                    @else text-sky-700 @endif">
                                    {{ $s['number'] }} &bull; {{ $s['carrier'] }}
                                </div>
                            </div>

                            <!-- 4-Tier Status Pill -->
                            <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-full shrink-0 border
                                @if($s['status_type'] === 'DELIVERED') bg-emerald-100 text-emerald-800 border-emerald-300
                                @elseif($s['status_type'] === 'DELAYED') bg-rose-100 text-rose-800 border-rose-300
                                @elseif($s['status_type'] === 'STAGED') bg-amber-100 text-amber-800 border-amber-300
                                @else bg-sky-100 text-sky-800 border-sky-300 @endif">
                                @if($s['status_type'] === 'DELIVERED') 🟢 DELIVERED
                                @elseif($s['status_type'] === 'DELAYED') 🔴 DELAYED
                                @elseif($s['status_type'] === 'STAGED') 📦 AT ORIGIN
                                @else 🔵 IN TRANSIT @endif
                            </span>
                        </div>

                        <!-- Commodity & Customer (Consignee) Details -->
                        <div class="mt-2.5 text-xs text-slate-700 space-y-1">
                            <div class="flex items-center gap-1.5 font-bold">
                                <span>{{ $s['commodity'] === 'COFFEE' ? '☕' : '🍫' }}</span>
                                <span class="truncate">{{ $s['cargo_type'] }}</span>
                                <span class="text-slate-400 font-normal">({{ $s['weight_formatted'] }})</span>
                            </div>
                            <div class="text-[11px] text-slate-600 truncate">
                                <strong>Consignee:</strong> {{ $s['customer']['name'] }} ({{ $s['customer']['company'] }})
                            </div>
                            <div class="text-[11px] text-slate-500 flex items-center gap-1">
                                <span>🚢</span>
                                <span class="font-bold text-slate-700">{{ $s['origin']['city'] }}</span>
                                <span>&rarr;</span>
                                <span class="font-bold text-slate-700">{{ $s['destination']['city'] }}</span>
                            </div>
                        </div>

                        <!-- Progress Bar & Location -->
                        <div class="mt-3 pt-2.5 border-t border-slate-200/60 flex items-center justify-between text-[11px]">
                            <div class="flex items-center gap-1 truncate text-slate-500">
                                <span>📍</span>
                                <span class="truncate font-semibold">{{ $s['current_position']['location_name'] }}</span>
                            </div>
                            <span class="font-mono font-black text-slate-700 shrink-0">
                                {{ $s['progress_percent'] }}%
                            </span>
                        </div>

                        @if(!empty($s['is_signal_offline']))
                        <div class="mt-2 px-2 py-1 bg-amber-50 rounded-lg border border-amber-300 text-amber-900 text-[10px] font-bold flex items-center gap-1.5 shadow-xs">
                            <span class="truncate">{{ $s['signal_label'] ?? '⚠️ Offline Signal (Last Known Position)' }}</span>
                        </div>
                        @endif

                    </div>
                    @empty
                    <div class="p-8 text-center text-slate-400 text-xs">
                        No cargo vessels found.
                    </div>
                    @endforelse
                </div>

            </div>
        </div>

    </div>

</div>
@endsection

@section('scripts')
<script>
    const fleetShipments = {!! json_encode($mapShipments) !!};
    let fleetMap;
    let currentBaseLayer;
    let baseTileLayers = {};
    const vesselMarkers = {};
    const shipmentPolylines = {};
    const shipmentTraveledPolylines = {};
    const shipmentRemainingPolylines = {};
    const shipmentOriginMarkers = {};
    const shipmentDestMarkers = {};
    let globalBounds;
    let currentFocusShipmentId = null;
    let routeDisplayMode = 'FOCUS'; // Default: 'FOCUS' (clean single-route focus, no visual clutter)

    /**
     * Helper to get compass direction in English from bearing degree
     */
    function getCompassDirection(bearing) {
        if (bearing === null || bearing === undefined) return 'North';
        const b = (parseFloat(bearing) % 360 + 360) % 360;
        if (b >= 337.5 || b < 22.5) return 'N (North)';
        if (b < 67.5) return 'NE (Northeast)';
        if (b < 112.5) return 'E (East)';
        if (b < 157.5) return 'SE (Southeast)';
        if (b < 202.5) return 'S (South)';
        if (b < 247.5) return 'SW (Southwest)';
        if (b < 292.5) return 'W (West)';
        return 'NW (Northwest)';
    }

    /**
     * Pass clean coordinate paths directly (all CargoTrace maritime routes stay strictly
     * between -75°W and +115°E via Suez/Gibraltar, zero crossing of 180° antimeridian).
     */
    function sanitizePath(coords) {
        if (!coords || !Array.isArray(coords) || coords.length < 2) return [];
        return coords.filter(p => Array.isArray(p) && p.length >= 2 && !isNaN(p[0]) && !isNaN(p[1]));
    }

    /**
     * Switch Base Tile Layer (CARTO Voyager English, OSM Standard, Satellite)
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
     * Toggle Route Display Mode: 'FOCUS' (only selected ship) vs 'ALL' (all lines)
     */
    function toggleRouteMode(mode) {
        routeDisplayMode = mode;
        const btnFocus = document.getElementById('btn-mode-focus');
        const btnAll = document.getElementById('btn-mode-all');

        if (mode === 'FOCUS') {
            btnFocus.className = 'px-2.5 py-1 rounded-lg font-bold text-[11px] transition bg-white text-slate-900 shadow-xs cursor-pointer';
            btnAll.className = 'px-2.5 py-1 rounded-lg font-bold text-[11px] transition text-slate-600 hover:text-slate-900 cursor-pointer';
        } else {
            btnAll.className = 'px-2.5 py-1 rounded-lg font-bold text-[11px] transition bg-white text-slate-900 shadow-xs cursor-pointer';
            btnFocus.className = 'px-2.5 py-1 rounded-lg font-bold text-[11px] transition text-slate-600 hover:text-slate-900 cursor-pointer';
        }

        updateRoutePolylinesVisibility();
    }

    /**
     * Reset View to Worldwide Fleet
     */
    function resetWorldView() {
        clearActiveVesselFocus();
        if (fleetMap && globalBounds && globalBounds.isValid()) {
            fleetMap.fitBounds(globalBounds, { padding: [50, 50], maxZoom: 5 });
        } else if (fleetMap) {
            fleetMap.setView([15.0, 45.0], 3);
        }
    }

    /**
     * Locate all ships
     */
    function locateMyShips() {
        if (!fleetMap) return;
        const myShipBounds = L.latLngBounds();
        fleetShipments.forEach(s => {
            if (s.current_position && s.current_position.latitude && s.current_position.longitude) {
                myShipBounds.extend([s.current_position.latitude, s.current_position.longitude]);
            }
        });
        if (myShipBounds.isValid()) {
            fleetMap.fitBounds(myShipBounds, { padding: [50, 50], maxZoom: 6 });
        }
    }

    /**
     * Select a shipment, isolate its route, and fly map smoothly to it
     */
    function selectShipmentRoute(id) {
        currentFocusShipmentId = id;
        const s = fleetShipments.find(item => item.id === id);
        if (!s) return;

        // Highlight vessel card in left drawer
        document.querySelectorAll('.vessel-card').forEach(c => {
            c.classList.remove('ring-4', 'ring-blue-500', 'shadow-xl');
        });
        const activeCard = document.querySelector(`.vessel-card[data-id="${id}"]`);
        if (activeCard) {
            activeCard.classList.add('ring-4', 'ring-blue-500', 'shadow-xl');
            activeCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        // Update top active vessel banner
        const banner = document.getElementById('active-vessel-banner');
        if (banner) {
            document.getElementById('banner-vessel-name').innerText = s.vessel_name;
            const headingText = `${Math.round(s.current_position.bearing || 0)}° (${getCompassDirection(s.current_position.bearing)})`;
            document.getElementById('banner-route-text').innerText = `${s.origin.name} (${s.origin.city}) → ${s.destination.name} (${s.destination.city}) • Heading: ${headingText} • ${s.cargo_type} (${s.weight_formatted})`;
            
            const badge = document.getElementById('banner-status-badge');
            const icon = document.getElementById('banner-status-icon');
            
            if (s.status_type === 'DELIVERED') {
                badge.innerText = '🟢 DELIVERED AT DESTINATION';
                badge.className = 'px-2.5 py-0.5 rounded text-[10px] font-black uppercase bg-emerald-500/30 text-emerald-300 border border-emerald-400/40';
                icon.className = 'w-8 h-8 rounded-xl flex items-center justify-center text-lg font-bold shrink-0 bg-emerald-600';
                icon.innerText = '✅';
            } else if (s.status_type === 'DELAYED') {
                badge.innerText = '🔴 DELAYED / LOGISTICS ALERT';
                badge.className = 'px-2.5 py-0.5 rounded text-[10px] font-black uppercase bg-rose-500/30 text-rose-300 border border-rose-400/40';
                icon.className = 'w-8 h-8 rounded-xl flex items-center justify-center text-lg font-bold shrink-0 bg-rose-600';
                icon.innerText = '⚠️';
            } else if (s.status_type === 'STAGED') {
                badge.innerText = '📦 AT ORIGIN PORT (STAGED)';
                badge.className = 'px-2.5 py-0.5 rounded text-[10px] font-black uppercase bg-amber-500/30 text-amber-300 border border-amber-400/40';
                icon.className = 'w-8 h-8 rounded-xl flex items-center justify-center text-lg font-bold shrink-0 bg-amber-600';
                icon.innerText = '📦';
            } else {
                badge.innerText = '🔵 IN TRANSIT (SAILING ON MARITIME ROUTE)';
                badge.className = 'px-2.5 py-0.5 rounded text-[10px] font-black uppercase bg-sky-500/30 text-sky-300 border border-sky-400/40';
                icon.className = 'w-8 h-8 rounded-xl flex items-center justify-center text-lg font-bold shrink-0 bg-sky-600';
                icon.innerText = '🚢';
            }

            banner.classList.remove('hidden');
        }

        // Update polylines visibility (clean focused line)
        updateRoutePolylinesVisibility();

        // Fit map bounds to frame this specific shipment's route from Origin [A] -> Ship -> Destination [B]
        const origMarker = shipmentOriginMarkers[id];
        const destMarker = shipmentDestMarkers[id];
        const shipPos = s.current_position;

        if (origMarker && destMarker && shipPos && shipPos.latitude && shipPos.longitude) {
            const voyageBounds = L.latLngBounds([
                origMarker.getLatLng(),
                destMarker.getLatLng(),
                [shipPos.latitude, shipPos.longitude]
            ]);
            fleetMap.fitBounds(voyageBounds, { padding: [60, 60], maxZoom: 6 });
        } else if (shipPos && shipPos.latitude && shipPos.longitude) {
            fleetMap.flyTo([shipPos.latitude, shipPos.longitude], 5);
        } else if (vesselMarkers[id]) {
            fleetMap.flyTo(vesselMarkers[id].getLatLng(), 5);
        }
    }

    function clearActiveVesselFocus() {
        currentFocusShipmentId = null;
        document.querySelectorAll('.vessel-card').forEach(c => {
            c.classList.remove('ring-4', 'ring-blue-500', 'shadow-xl');
        });
        const banner = document.getElementById('active-vessel-banner');
        if (banner) banner.classList.add('hidden');
        updateRoutePolylinesVisibility();
    }

    /**
     * Update route polylines rendering based on active focus and display mode
     */
    function updateRoutePolylinesVisibility() {
        fleetShipments.forEach(s => {
            const travPoly = shipmentTraveledPolylines[s.id];
            const remPoly = shipmentRemainingPolylines[s.id];
            const origMarker = shipmentOriginMarkers[s.id];
            const destMarker = shipmentDestMarkers[s.id];
            const vMarker = vesselMarkers[s.id];

            const isThisFocused = (currentFocusShipmentId === s.id);

            if (currentFocusShipmentId !== null) {
                // When a specific vessel is focused/selected
                if (isThisFocused) {
                    if (travPoly) {
                        travPoly.setStyle({
                            opacity: 1.0,
                            weight: 6,
                            color: s.status_color
                        });
                        travPoly.bringToFront();
                        if (!fleetMap.hasLayer(travPoly)) travPoly.addTo(fleetMap);
                    }
                    if (remPoly) {
                        remPoly.setStyle({
                            opacity: 0.9,
                            weight: 4.5,
                            color: s.status_color,
                            dashArray: s.status_type === 'DELIVERED' ? null : '8, 8'
                        });
                        remPoly.bringToFront();
                        if (!fleetMap.hasLayer(remPoly)) remPoly.addTo(fleetMap);
                    }
                    if (origMarker) {
                        origMarker.setZIndexOffset(3500);
                        if (!fleetMap.hasLayer(origMarker)) origMarker.addTo(fleetMap);
                    }
                    if (destMarker) {
                        destMarker.setZIndexOffset(3500);
                        if (!fleetMap.hasLayer(destMarker)) destMarker.addTo(fleetMap);
                    }
                    if (vMarker) {
                        vMarker.setZIndexOffset(2500);
                        vMarker.setOpacity(1.0);
                    }
                } else {
                    if (routeDisplayMode === 'FOCUS') {
                        // In FOCUS mode: hide non-selected lines and non-selected origin/destination pins
                        if (travPoly && fleetMap.hasLayer(travPoly)) fleetMap.removeLayer(travPoly);
                        if (remPoly && fleetMap.hasLayer(remPoly)) fleetMap.removeLayer(remPoly);
                        if (origMarker && fleetMap.hasLayer(origMarker)) fleetMap.removeLayer(origMarker);
                        if (destMarker && fleetMap.hasLayer(destMarker)) fleetMap.removeLayer(destMarker);
                        if (vMarker) {
                            vMarker.setZIndexOffset(100);
                            vMarker.setOpacity(0.35); // Dim non-selected ships so focused route dominates
                        }
                    } else {
                        // In ALL mode: keep other vessels visible as subtle background routes
                        if (travPoly) {
                            travPoly.setStyle({
                                opacity: 0.35,
                                weight: 2.5,
                                color: s.status_color
                            });
                            if (!fleetMap.hasLayer(travPoly)) travPoly.addTo(fleetMap);
                        }
                        if (remPoly) {
                            remPoly.setStyle({
                                opacity: 0.25,
                                weight: 2,
                                color: s.status_color,
                                dashArray: '5, 7'
                            });
                            if (!fleetMap.hasLayer(remPoly)) remPoly.addTo(fleetMap);
                        }
                        if (origMarker && !fleetMap.hasLayer(origMarker)) origMarker.addTo(fleetMap);
                        if (destMarker && !fleetMap.hasLayer(destMarker)) destMarker.addTo(fleetMap);
                        if (vMarker) {
                            vMarker.setZIndexOffset(200);
                            vMarker.setOpacity(1.0);
                        }
                    }
                }
            } else {
                // Overview mode (no single focus): show all real maritime routes
                if (travPoly) {
                    travPoly.setStyle({
                        opacity: 0.8,
                        weight: 3.5,
                        color: s.status_color
                    });
                    if (!fleetMap.hasLayer(travPoly)) travPoly.addTo(fleetMap);
                }
                if (remPoly) {
                    remPoly.setStyle({
                        opacity: 0.6,
                        weight: 2.8,
                        color: s.status_color,
                        dashArray: s.status_type === 'DELIVERED' ? null : '6, 8'
                    });
                    if (!fleetMap.hasLayer(remPoly)) remPoly.addTo(fleetMap);
                }
                if (origMarker && !fleetMap.hasLayer(origMarker)) origMarker.addTo(fleetMap);
                if (destMarker && !fleetMap.hasLayer(destMarker)) destMarker.addTo(fleetMap);
                if (vMarker) {
                    vMarker.setZIndexOffset(300);
                    vMarker.setOpacity(1.0);
                }
            }
        });
    }

    /**
     * Quick Filter Chips (All, Delivered, In Transit, Delayed, Coffee, Cocoa)
     */
    function applyQuickFilter(type) {
        document.querySelectorAll('.filter-chip').forEach(b => {
            b.classList.remove('bg-slate-900', 'text-white', 'shadow-xs');
            b.classList.add('bg-slate-100', 'text-slate-700', 'border-slate-200');
        });
        const activeChip = document.getElementById('chip-' + type);
        if (activeChip) {
            activeChip.classList.add('bg-slate-900', 'text-white', 'shadow-xs');
            activeChip.classList.remove('bg-slate-100', 'text-slate-700', 'border-slate-200');
        }

        const cards = document.querySelectorAll('.vessel-card');
        let visibleCount = 0;

        cards.forEach(card => {
            const status = card.getAttribute('data-status');
            const commodity = card.getAttribute('data-commodity');
            const id = parseInt(card.getAttribute('data-id'));

            let show = false;
            if (type === 'ALL') show = true;
            else if (type === 'DELIVERED' && status === 'DELIVERED') show = true;
            else if (type === 'IN_TRANSIT' && status === 'IN_TRANSIT') show = true;
            else if (type === 'DELAYED' && status === 'DELAYED') show = true;
            else if (type === 'STAGED' && status === 'STAGED') show = true;
            else if (type === 'WEST' && card.getAttribute('data-west') === '1') show = true;
            else if (type === 'OFFLINE' && card.getAttribute('data-offline') === '1') show = true;
            else if (type === 'COFFEE' && commodity === 'COFFEE') show = true;
            else if (type === 'COCOA' && commodity === 'COCOA') show = true;

            if (show) {
                card.style.display = 'block';
                visibleCount++;
                if (vesselMarkers[id]) vesselMarkers[id].addTo(fleetMap);
                if (shipmentOriginMarkers[id]) shipmentOriginMarkers[id].addTo(fleetMap);
                if (shipmentDestMarkers[id]) shipmentDestMarkers[id].addTo(fleetMap);
            } else {
                card.style.display = 'none';
                if (vesselMarkers[id]) fleetMap.removeLayer(vesselMarkers[id]);
                if (shipmentOriginMarkers[id]) fleetMap.removeLayer(shipmentOriginMarkers[id]);
                if (shipmentDestMarkers[id]) fleetMap.removeLayer(shipmentDestMarkers[id]);
                if (shipmentTraveledPolylines[id]) fleetMap.removeLayer(shipmentTraveledPolylines[id]);
                if (shipmentRemainingPolylines[id]) fleetMap.removeLayer(shipmentRemainingPolylines[id]);
            }
        });

        updateRoutePolylinesVisibility();

        const badge = document.getElementById('vessel-count-badge');
        if (badge) badge.innerText = `${visibleCount} Vessels`;
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

        L.control.zoom({ position: 'bottomright' }).addTo(fleetMap);

        // 2. Base Tile Layers (CARTO Voyager default with English labels)
        baseTileLayers = {
            voyager: L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                maxZoom: 19,
                subdomains: 'abcd',
                attribution: '&copy; <a href="https://carto.com/">CARTO</a> &bull; OpenStreetMap contributors'
            }),
            osm: L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            }),
            satellite: L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                maxZoom: 18,
                attribution: '&copy; Esri World Imagery'
            })
        };

        // Default to CARTO Voyager: high-resolution English cartography designed for global navigation
        currentBaseLayer = baseTileLayers.voyager;
        currentBaseLayer.addTo(fleetMap);

        globalBounds = L.latLngBounds();

        // 3. Render Ships & True Maritime Corridors with Color Coding
        fleetShipments.forEach((s) => {
            const pos = s.current_position;
            const origin = s.origin;
            const dest = s.destination;

            // Draw Real-time Traveled Path (Solid line: Origin -> Current Ship Position)
            if (s.traveled_path && s.traveled_path.length >= 2) {
                const travPoly = L.polyline(sanitizePath(s.traveled_path), {
                    color: s.status_color,
                    weight: 3.5,
                    opacity: 0.8
                });

                travPoly.on('click', () => {
                    selectShipmentRoute(s.id);
                });

                shipmentTraveledPolylines[s.id] = travPoly;
            }

            // Draw Real-time Remaining Path (Dashed line: Current Ship Position -> Destination)
            if (s.remaining_path && s.remaining_path.length >= 2) {
                const remPoly = L.polyline(sanitizePath(s.remaining_path), {
                    color: s.status_color,
                    weight: 2.8,
                    opacity: 0.6,
                    dashArray: s.status_type === 'DELIVERED' ? null : '6, 8'
                });

                remPoly.on('click', () => {
                    selectShipmentRoute(s.id);
                });

                shipmentRemainingPolylines[s.id] = remPoly;
            }

            // Keep reference for sea_route
            if (s.sea_route && s.sea_route.length >= 2) {
                shipmentPolylines[s.id] = shipmentTraveledPolylines[s.id] || shipmentRemainingPolylines[s.id];
            }

            // Origin Port Marker (A) with high-visibility badge in English
            if (origin.lat && origin.lng) {
                const originPinHtml = `
                    <div class="relative flex flex-col items-center cursor-pointer pointer-events-auto" style="width: 135px; height: 50px;">
                        <div class="px-2 py-0.5 rounded-full bg-emerald-700 text-white font-black text-[9px] shadow-md border border-white whitespace-nowrap mb-0.5">
                            🟢 [A] ORIGIN: ${origin.city}
                        </div>
                        <svg width="20" height="25" viewBox="0 0 24 30" fill="none">
                            <path d="M12 0C5.37 0 0 5.37 0 12C0 21 12 30 12 30C12 30 24 21 24 12C24 5.37 18.63 0 12 0Z" fill="#10b981"/>
                            <circle cx="12" cy="11" r="5" fill="#ffffff"/>
                            <text x="12" y="14" font-size="8" font-weight="900" text-anchor="middle" fill="#065f46">A</text>
                        </svg>
                    </div>
                `;
                const origMarker = L.marker([origin.lat, origin.lng], {
                    icon: L.divIcon({
                        html: originPinHtml,
                        className: 'gmap-origin-pin',
                        iconSize: [135, 50],
                        iconAnchor: [67, 48],
                        popupAnchor: [0, -48]
                    }),
                    zIndexOffset: 300
                }).addTo(fleetMap).bindPopup(`
                    <div class="p-2 font-sans text-xs">
                        <div class="font-black text-emerald-800 text-[10px] uppercase">ORIGIN PORT [A]</div>
                        <div class="text-sm font-black text-slate-900 mt-0.5">${origin.name}</div>
                        <div class="text-slate-500 text-[11px]">${origin.city}, ${origin.country}</div>
                        <div class="text-[10px] text-slate-400 mt-1">Allocated Vessel: <strong>${s.vessel_name}</strong></div>
                    </div>
                `);
                shipmentOriginMarkers[s.id] = origMarker;
                globalBounds.extend([origin.lat, origin.lng]);
            }

            // Destination Port Marker (B) with high-visibility badge in English
            if (dest.lat && dest.lng) {
                const destPinHtml = `
                    <div class="relative flex flex-col items-center cursor-pointer pointer-events-auto" style="width: 135px; height: 50px;">
                        <div class="px-2 py-0.5 rounded-full bg-rose-700 text-white font-black text-[9px] shadow-md border border-white whitespace-nowrap mb-0.5">
                            🔴 [B] DESTINATION: ${dest.city}
                        </div>
                        <svg width="20" height="25" viewBox="0 0 24 30" fill="none">
                            <path d="M12 0C5.37 0 0 5.37 0 12C0 21 12 30 12 30C12 30 24 21 24 12C24 5.37 18.63 0 12 0Z" fill="#ef4444"/>
                            <circle cx="12" cy="11" r="5" fill="#ffffff"/>
                            <text x="12" y="14" font-size="8" font-weight="900" text-anchor="middle" fill="#991b1b">B</text>
                        </svg>
                    </div>
                `;
                const destMarker = L.marker([dest.lat, dest.lng], {
                    icon: L.divIcon({
                        html: destPinHtml,
                        className: 'gmap-dest-pin',
                        iconSize: [135, 50],
                        iconAnchor: [67, 48],
                        popupAnchor: [0, -48]
                    }),
                    zIndexOffset: 300
                }).addTo(fleetMap).bindPopup(`
                    <div class="p-2 font-sans text-xs">
                        <div class="font-black text-rose-800 text-[10px] uppercase">DESTINATION PORT [B]</div>
                        <div class="text-sm font-black text-slate-900 mt-0.5">${dest.name}</div>
                        <div class="text-slate-500 text-[11px]">${dest.city}, ${dest.country}</div>
                        <div class="text-[10px] text-slate-400 mt-1">Consignee (Buyer): <strong>${s.customer.name}</strong></div>
                    </div>
                `);
                shipmentDestMarkers[s.id] = destMarker;
                globalBounds.extend([dest.lat, dest.lng]);
            }

            // Vessel Marker with Authentic Top-Down Nautical Navigation Vessel SVG
            if (pos.latitude && pos.longitude) {
                const isDelivered = (s.status_type === 'DELIVERED');
                const isDelayed = (s.status_type === 'DELAYED');
                const isStaged = (s.status_type === 'STAGED');
                const isCoffee = (s.commodity === 'COFFEE');

                // Color Coding & Status Badges
                let hullColor = '#0284c7';
                let badgeColorClass = 'bg-sky-600 text-white';
                let statusDot = '🔵';

                if (isDelivered) {
                    hullColor = '#10b981';
                    badgeColorClass = 'bg-emerald-600 text-white';
                    statusDot = '🟢';
                } else if (isDelayed) {
                    hullColor = '#e11d48';
                    badgeColorClass = 'bg-rose-600 text-white';
                    statusDot = '🔴';
                } else if (isStaged) {
                    hullColor = '#d97706';
                    badgeColorClass = 'bg-amber-600 text-white';
                    statusDot = '📦';
                }

                const compassDir = getCompassDirection(pos.bearing);
                const compassShort = compassDir.split(' ')[0];
                const bearingDeg = Math.round(pos.bearing || 0);

                const vesselMarkerHtml = `
                    <div class="relative flex flex-col items-center pointer-events-auto cursor-pointer group" style="width: 140px; height: 80px;">
                        
                        <!-- Floating Vessel Name & Direction Badge -->
                        <div class="${badgeColorClass} font-black text-[9px] px-2 py-0.5 rounded-full shadow-md whitespace-nowrap mb-1 flex items-center gap-1 border border-white/70 max-w-[135px] truncate transition-transform group-hover:scale-105">
                            <span class="shrink-0">${statusDot}</span>
                            <span class="truncate">${s.vessel_name}</span>
                            <span class="text-[8px] font-mono px-1 py-0.2 rounded bg-black/30 text-white font-bold shrink-0">${bearingDeg}° ${compassShort}</span>
                        </div>

                        <!-- Top-Down Nautical Navigation Vessel (Bow Points UP at 0deg) -->
                        <div class="relative w-9 h-14 flex items-center justify-center transform transition-transform duration-300" style="transform: rotate(${bearingDeg}deg); transform-origin: 50% 50%;">
                            ${isDelayed ? `
                                <div class="absolute -top-2 w-12 h-12 rounded-full bg-rose-500/35 border-2 border-rose-500 animate-ping"></div>
                            ` : (!isDelivered && !isStaged ? `
                                <div class="absolute -top-1 w-10 h-10 rounded-full bg-sky-400/25 animate-pulse"></div>
                            ` : '')}

                            <svg width="34" height="52" viewBox="0 0 34 52" fill="none" class="overflow-visible">
                                <!-- Forward Nautical Radar Heading Beam (Points Forward from Bow) -->
                                ${!isDelivered && !isStaged ? `
                                    <polygon points="17,-1 8,-16 26,-16" fill="${hullColor}" fill-opacity="0.3"/>
                                    <line x1="17" y1="-1" x2="17" y2="-20" stroke="${hullColor}" stroke-width="1.8" stroke-dasharray="2,2"/>
                                ` : ''}

                                <!-- Hull Shadow -->
                                <path d="M17 2 C24 11 30 22 30 45 C24 48 10 48 4 45 C4 22 10 11 17 2 Z" fill="#0f172a" fill-opacity="0.35"/>
                                
                                <!-- Main Ship Hull (Bow Pointed Up at Y=4) -->
                                <path d="M17 4 C23.5 12 29 22 29 44 C22.5 46.5 11.5 46.5 5 44 C5 22 10.5 12 17 4 Z" fill="${hullColor}" stroke="#0f172a" stroke-width="1.8"/>
                                
                                <!-- Cargo Hold Well -->
                                <path d="M17 9 C21 15 25 23 25 41 C21 42.5 13 42.5 9 41 C9 23 13 15 17 9 Z" fill="#1e293b"/>
                                
                                <!-- Cargo Container Stacks -->
                                <rect x="10.5" y="16" width="5.5" height="7.5" rx="1" fill="${isCoffee ? '#92400e' : '#ea580c'}" stroke="#0f172a" stroke-width="0.6"/>
                                <rect x="18" y="16" width="5.5" height="7.5" rx="1" fill="#f59e0b" stroke="#0f172a" stroke-width="0.6"/>
                                <rect x="10.5" y="26" width="5.5" height="7.5" rx="1" fill="${hullColor}" stroke="#0f172a" stroke-width="0.6"/>
                                <rect x="18" y="26" width="5.5" height="7.5" rx="1" fill="${isCoffee ? '#78350f' : '#c2410c'}" stroke="#0f172a" stroke-width="0.6"/>
                                
                                <!-- Wheelhouse Superstructure at Stern (Dark Naval Structure) -->
                                <rect x="10.5" y="36" width="13" height="4.5" rx="1" fill="#334155" stroke="#475569" stroke-width="0.8"/>
                                <rect x="12.5" y="37" width="9" height="1.6" rx="0.4" fill="#38bdf8"/>
                                
                                <!-- High-Visibility Directional Navigation Bow Prow Arrow (Points FORWARD at Bow) -->
                                <polygon points="17,-1 24,10 17,7 10,10" fill="#ffffff" stroke="#0f172a" stroke-width="1.6"/>
                                <polygon points="17,1 22,9 17,6.8 12,9" fill="${hullColor}"/>
                            </svg>
                        </div>
                    </div>
                `;

                const vMarker = L.marker([pos.latitude, pos.longitude], {
                    icon: L.divIcon({
                        html: vesselMarkerHtml,
                        className: 'gmap-vessel-marker',
                        iconSize: [140, 80],
                        iconAnchor: [70, 48],
                        popupAnchor: [0, -48]
                    }),
                    zIndexOffset: isDelayed ? 950 : (isDelivered ? 850 : (isStaged ? 800 : 900))
                }).addTo(fleetMap);

                vMarker.on('click', () => {
                    selectShipmentRoute(s.id);
                });

                // Rich InfoWindow Popup in English
                const infoWindowHtml = `
                    <div class="p-2 font-sans text-xs min-w-[280px]">
                        <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-100">
                            <div>
                                <div class="text-[10px] font-black uppercase tracking-wider" style="color: ${s.status_color}">
                                    ${s.status_label}
                                </div>
                                <div class="text-base font-black text-slate-900 mt-0.5">${s.vessel_name}</div>
                            </div>
                            <span class="px-2.5 py-0.5 rounded text-[10px] font-black uppercase ${s.status_badge_class}">
                                ${s.navigation_status}
                            </span>
                        </div>

                        <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-200/60 space-y-1.5 text-[11px] text-slate-700">
                            <div><strong>Cargo:</strong> ${s.cargo_type} &bull; ${s.weight_formatted}</div>
                            <div><strong>Consignee (Buyer):</strong> ${s.customer.name} (${s.customer.company})</div>
                            <div><strong>Sea Route:</strong> ${origin.city} &rarr; ${dest.city}</div>
                            <div><strong>Corridor:</strong> ${s.sector_label}</div>
                        </div>

                        <div class="mt-2 p-2 bg-white rounded-lg border border-slate-200 text-[11px] space-y-1">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-slate-800">📍 Real-Time Position:</span>
                                <span class="font-mono text-[10px] font-black">${s.progress_percent}% Completed</span>
                            </div>
                            <div class="text-xs font-bold text-slate-900">${pos.location_name}</div>
                            <div class="text-[10px] text-slate-500 font-mono">
                                Navigation Heading: <strong>${Math.round(pos.bearing || 0)}° (${compassDir})</strong> &bull; ${parseFloat(pos.latitude).toFixed(4)}°, ${parseFloat(pos.longitude).toFixed(4)}°
                            </div>
                            ${s.is_signal_offline ? `
                                <div class="mt-1 text-[10px] font-bold text-amber-700 bg-amber-50 p-1.5 rounded border border-amber-200 flex items-center gap-1">
                                    <span>⚠️</span>
                                    <span>${s.signal_label || 'Last Known Position'}</span>
                                </div>
                            ` : ''}
                        </div>

                        <div class="mt-2.5 pt-2 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-[10px] text-slate-400 font-mono">${s.number}</span>
                            <a href="${s.detail_url}" class="px-3 py-1 bg-slate-900 hover:bg-slate-800 text-white font-black text-xs rounded-lg shadow-xs transition">
                                View Shipment Details &rarr;
                            </a>
                        </div>
                    </div>
                `;

                vMarker.bindPopup(infoWindowHtml, { autoPan: false, maxWidth: 300 });
                vMarker.on('click', () => {
                    selectShipmentRoute(s.id);
                });

                vesselMarkers[s.id] = vMarker;
                globalBounds.extend([pos.latitude, pos.longitude]);
            }
        });

        // 4. Initial Route Lines Visibility: Auto-focus on first shipment by default so the map opens with a clean, focused single route
        if (fleetShipments && fleetShipments.length > 0) {
            selectShipmentRoute(fleetShipments[0].id);
        } else {
            updateRoutePolylinesVisibility();
            if (globalBounds && globalBounds.isValid()) {
                fleetMap.fitBounds(globalBounds, { padding: [50, 50], maxZoom: 5 });
            }
        }

        setTimeout(() => {
            if (fleetMap) {
                fleetMap.invalidateSize();
            }
        }, 300);

        // Search bar handler
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
                        if (shipmentOriginMarkers[id]) shipmentOriginMarkers[id].addTo(fleetMap);
                        if (shipmentDestMarkers[id]) shipmentDestMarkers[id].addTo(fleetMap);
                        if (shipmentTraveledPolylines[id]) shipmentTraveledPolylines[id].addTo(fleetMap);
                        if (shipmentRemainingPolylines[id]) shipmentRemainingPolylines[id].addTo(fleetMap);
                    } else {
                        card.style.display = 'none';
                        if (vesselMarkers[id]) fleetMap.removeLayer(vesselMarkers[id]);
                        if (shipmentOriginMarkers[id]) fleetMap.removeLayer(shipmentOriginMarkers[id]);
                        if (shipmentDestMarkers[id]) fleetMap.removeLayer(shipmentDestMarkers[id]);
                        if (shipmentTraveledPolylines[id]) fleetMap.removeLayer(shipmentTraveledPolylines[id]);
                        if (shipmentRemainingPolylines[id]) fleetMap.removeLayer(shipmentRemainingPolylines[id]);
                    }
                });

                updateRoutePolylinesVisibility();

                const badge = document.getElementById('vessel-count-badge');
                if (badge) badge.innerText = `${matchCount} Vessels`;
            });
        }
    });
</script>
@endsection
