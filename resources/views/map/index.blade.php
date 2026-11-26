@extends('layouts.app')

@section('title', 'Peta Armada Kargo Global (Google Maps Style) — CargoTrace')

@section('content')
<div class="space-y-4 font-sans">

    <!-- Top Floating Header Bar -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 rounded-2xl p-5 md:p-6 text-white shadow-xl border border-slate-700/70 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex flex-wrap items-center gap-2 mb-1.5">
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-black bg-[#C49A55] text-slate-950 uppercase tracking-wider shadow-sm">
                    LEAFLET + OPENSTREETMAP (PETA ARUS MARITIM)
                </span>
                <span class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-400 bg-emerald-950/80 px-2.5 py-0.5 rounded-full border border-emerald-500/30">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                    Jalur Laut Akurat (Tanpa Memotong Daratan)
                </span>
            </div>
            <h1 class="text-2xl md:text-3xl font-black tracking-tight text-white">
                Peta Kargo Global & Tracking Armada Kapal Pelanggan
            </h1>
            <p class="text-xs md:text-sm text-slate-300 mt-1 max-w-3xl leading-relaxed">
                Visualisasi pelayaran kapal kargo kopi dan kakao di koridor laut internasional dengan pembedaan warna status: <strong class="text-emerald-400">🟢 Sudah Sampai</strong>, <strong class="text-sky-400">🔵 Sedang Berlayar</strong>, dan <strong class="text-rose-400">🔴 Ada Terhambat / Delay</strong>.
            </p>
        </div>

        <!-- 3-Tier Status Metric Badges -->
        <div class="flex flex-wrap items-center gap-2 shrink-0">
            <div class="px-3.5 py-2 bg-slate-800/80 border border-slate-700 rounded-xl text-center min-w-[85px]">
                <div class="text-[10px] uppercase font-bold text-slate-400">Semua Kapal</div>
                <div class="text-lg font-black text-white" id="stat-total">{{ $stats['total'] }}</div>
            </div>
            <div class="px-3.5 py-2 bg-emerald-950/60 border border-emerald-500/40 rounded-xl text-center min-w-[85px]">
                <div class="text-[10px] uppercase font-bold text-emerald-300">🟢 Sudah Sampai</div>
                <div class="text-lg font-black text-emerald-400" id="stat-delivered">{{ $stats['delivered'] }}</div>
            </div>
            <div class="px-3.5 py-2 bg-sky-950/60 border border-sky-500/40 rounded-xl text-center min-w-[85px]">
                <div class="text-[10px] uppercase font-bold text-sky-300">🔵 Lagi Transit</div>
                <div class="text-lg font-black text-sky-400" id="stat-sailing">{{ $stats['in_transit'] }}</div>
            </div>
            <div class="px-3.5 py-2 bg-rose-950/60 border border-rose-500/40 rounded-xl text-center min-w-[85px]">
                <div class="text-[10px] uppercase font-bold text-rose-300">🔴 Terhambat</div>
                <div class="text-lg font-black text-rose-400" id="stat-delayed">{{ $stats['delayed'] }}</div>
            </div>
        </div>
    </div>

    <!-- Quick Filter Bar & Route View Mode Toggles -->
    <div class="bg-white p-3.5 rounded-2xl border border-slate-200 shadow-sm flex flex-wrap items-center justify-between gap-3">
        
        <!-- Search Input -->
        <div class="relative flex-1 min-w-[260px] max-w-md">
            <input type="text" id="map-search-bar" placeholder="Cari nama kapal, no kargo, komoditas, pembeli, atau pelabuhan..."
                   class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl pl-9 pr-4 py-2.5 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-inner">
            <span class="absolute left-3 top-3 text-slate-400 text-xs">🔍</span>
        </div>

        <!-- 3-Tier Status Filter Chips -->
        <div class="flex flex-wrap items-center gap-1.5 text-xs">
            <button type="button" onclick="applyQuickFilter('ALL')" id="chip-ALL"
                    class="filter-chip px-3 py-1.5 rounded-full font-black border transition bg-slate-900 text-white border-slate-900 shadow-xs cursor-pointer">
                🌐 Semua Kapal ({{ $stats['total'] }})
            </button>
            <button type="button" onclick="applyQuickFilter('DELIVERED')" id="chip-DELIVERED"
                    class="filter-chip px-3 py-1.5 rounded-full font-bold border transition bg-emerald-50 text-emerald-800 border-emerald-300 hover:bg-emerald-100 cursor-pointer">
                🟢 Sudah Sampai ({{ $stats['delivered'] }})
            </button>
            <button type="button" onclick="applyQuickFilter('IN_TRANSIT')" id="chip-IN_TRANSIT"
                    class="filter-chip px-3 py-1.5 rounded-full font-bold border transition bg-sky-50 text-sky-800 border-sky-300 hover:bg-sky-100 cursor-pointer">
                🔵 Sedang Berlayar ({{ $stats['in_transit'] }})
            </button>
            <button type="button" onclick="applyQuickFilter('DELAYED')" id="chip-DELAYED"
                    class="filter-chip px-3 py-1.5 rounded-full font-bold border transition bg-rose-50 text-rose-800 border-rose-300 hover:bg-rose-100 cursor-pointer">
                🔴 Terhambat / Delay ({{ $stats['delayed'] }})
            </button>
            <button type="button" onclick="applyQuickFilter('WEST')" id="chip-WEST"
                    class="filter-chip px-3 py-1.5 rounded-full font-bold border transition bg-indigo-50 text-indigo-800 border-indigo-200 hover:bg-indigo-100 cursor-pointer">
                🇪🇺 Di Barat (Eropa)
            </button>
            <button type="button" onclick="applyQuickFilter('OFFLINE')" id="chip-OFFLINE"
                    class="filter-chip px-3 py-1.5 rounded-full font-bold border transition bg-amber-50 text-amber-800 border-amber-300 hover:bg-amber-100 cursor-pointer">
                ⚠️ Sinyal Mati (LKP)
            </button>
            <button type="button" onclick="applyQuickFilter('COFFEE')" id="chip-COFFEE"
                    class="filter-chip px-3 py-1.5 rounded-full font-bold border transition bg-slate-100 text-slate-700 border-slate-200 hover:bg-slate-200 cursor-pointer">
                ☕ Kopi
            </button>
            <button type="button" onclick="applyQuickFilter('COCOA')" id="chip-COCOA"
                    class="filter-chip px-3 py-1.5 rounded-full font-bold border transition bg-slate-100 text-slate-700 border-slate-200 hover:bg-slate-200 cursor-pointer">
                🍫 Kakao
            </button>
        </div>

        <!-- Route Mode Toggle: Single Active Focus vs All Lines -->
        <div class="flex items-center gap-1.5 bg-slate-100 p-1 rounded-xl border border-slate-200 text-xs">
            <button type="button" onclick="toggleRouteMode('FOCUS')" id="btn-mode-focus"
                    class="px-2.5 py-1 rounded-lg font-bold text-[11px] transition bg-white text-slate-900 shadow-xs cursor-pointer">
                🎯 Fokus Rute Terpilih (Bersih)
            </button>
            <button type="button" onclick="toggleRouteMode('ALL')" id="btn-mode-all"
                    class="px-2.5 py-1 rounded-lg font-bold text-[11px] transition text-slate-600 hover:text-slate-900 cursor-pointer">
                🌐 Tampilkan Semua Jalur
            </button>
        </div>
    </div>

    <!-- Main Map Workspace: Left Fleet Directory Drawer | Right Leaflet Canvas -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">

        <!-- Leaflet Map Canvas (8 cols) -->
        <div class="lg:col-span-8 bg-white rounded-2xl border border-slate-200 shadow-md overflow-hidden flex flex-col relative">

            <!-- Floating Top Layer Switcher -->
            <div class="absolute top-3 left-3 z-[1000] flex items-center gap-1.5 bg-white/95 backdrop-blur-md p-1 rounded-xl shadow-md border border-slate-200 text-xs font-semibold text-slate-700">
                <span class="text-[10px] font-bold px-2 text-slate-400 uppercase">PETA:</span>
                <button type="button" onclick="switchBaseMap('osm')" id="btn-layer-osm"
                        class="layer-toggle-btn px-2.5 py-1 rounded-lg text-xs font-bold transition bg-blue-600 text-white shadow-xs cursor-pointer">
                    🗺️ Google / OSM Standard
                </button>
                <button type="button" onclick="switchBaseMap('satellite')" id="btn-layer-satellite"
                        class="layer-toggle-btn px-2.5 py-1 rounded-lg text-xs font-bold transition bg-slate-100 text-slate-700 hover:bg-slate-200 cursor-pointer">
                    🛰️ Citra Satelit
                </button>
                <button type="button" onclick="switchBaseMap('voyager')" id="btn-layer-voyager"
                        class="layer-toggle-btn px-2.5 py-1 rounded-lg text-xs font-bold transition bg-slate-100 text-slate-700 hover:bg-slate-200 cursor-pointer">
                    🌊 Peta Maritim
                </button>
            </div>

            <!-- Floating Right Controls -->
            <div class="absolute top-3 right-3 z-[1000] flex flex-col gap-1.5">
                <button type="button" onclick="resetWorldView()" title="Lihat Seluruh Dunia"
                        class="w-9 h-9 bg-white hover:bg-slate-50 text-slate-700 rounded-xl shadow-md border border-slate-200 flex items-center justify-center font-bold text-sm transition cursor-pointer">
                    🌐
                </button>
                <button type="button" onclick="locateMyShips()" title="Pusatkan ke Kapal Saya"
                        class="w-9 h-9 bg-white hover:bg-slate-50 text-blue-600 rounded-xl shadow-md border border-slate-200 flex items-center justify-center font-bold text-sm transition cursor-pointer">
                    🎯
                </button>
            </div>

            <!-- Active Selected Vessel Notification Toast on Map -->
            <div id="active-vessel-banner" class="hidden absolute bottom-12 left-4 right-4 z-[1000] bg-slate-900/95 backdrop-blur-md text-white p-3 rounded-2xl shadow-2xl border border-slate-700 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3 truncate">
                    <div id="banner-status-icon" class="w-8 h-8 rounded-xl flex items-center justify-center text-lg font-bold shrink-0 bg-blue-600">
                        🚢
                    </div>
                    <div class="truncate">
                        <div class="flex items-center gap-2">
                            <span id="banner-vessel-name" class="font-black text-sm text-white">MV Nusantara Jaya</span>
                            <span id="banner-status-badge" class="px-2 py-0.5 rounded text-[10px] font-black uppercase bg-blue-500/30 text-blue-300 border border-blue-400/40">
                                SEDANG BERLAYAR
                            </span>
                        </div>
                        <div id="banner-route-text" class="text-xs text-slate-300 mt-0.5 truncate">
                            Pelabuhan Belawan (Medan) &rarr; Port of Rotterdam (Belanda) &bull; Koridor Laut Merah / Terusan Suez
                        </div>
                    </div>
                </div>
                <button type="button" onclick="clearActiveVesselFocus()" class="px-3 py-1 bg-white/10 hover:bg-white/20 text-white rounded-lg text-xs font-bold shrink-0 cursor-pointer border border-white/20">
                    ✕ Tutup Fokus
                </button>
            </div>

            <!-- The Leaflet Canvas with OpenStreetMap -->
            <div id="fleet-google-map" class="h-[680px] w-full z-10"></div>

            <!-- Map Footer Legend Bar -->
            <div class="p-3 bg-slate-50 border-t border-slate-200 text-xs text-slate-600 flex flex-wrap items-center justify-between gap-3 px-4">
                <div class="flex flex-wrap items-center gap-3 text-[11px]">
                    <span class="inline-flex items-center gap-1.5 font-bold text-emerald-700">
                        <span class="w-3.5 h-3.5 rounded-full bg-emerald-500 border-2 border-white shadow-xs"></span>
                        🟢 Sudah Sampai di Tujuan
                    </span>
                    <span class="inline-flex items-center gap-1.5 font-bold text-sky-700">
                        <span class="w-3.5 h-3.5 rounded-full bg-sky-500 border-2 border-white shadow-xs"></span>
                        🔵 Sedang Berlayar Normal
                    </span>
                    <span class="inline-flex items-center gap-1.5 font-bold text-rose-700">
                        <span class="w-3.5 h-3.5 rounded-full bg-rose-600 border-2 border-white shadow-xs"></span>
                        🔴 Ada Terhambat / Delay
                    </span>
                    <span class="inline-flex items-center gap-1.5 font-bold text-slate-600">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span> Pelabuhan Asal (A)
                    </span>
                    <span class="inline-flex items-center gap-1.5 font-bold text-slate-600">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-600"></span> Pelabuhan Tujuan (B)
                    </span>
                </div>
                <div class="text-[10px] text-slate-400 font-mono">
                    OpenStreetMap Contributors &bull; Leaflet Maritime Layer
                </div>
            </div>
        </div>

        <!-- Right Side Drawer: Vessel Cards Directory (4 cols) -->
        <div class="lg:col-span-4 space-y-3">
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-md">
                <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100">
                    <div>
                        <h2 class="text-sm font-black text-slate-900 flex items-center gap-1.5">
                            <span>🚢</span> Kapal Barang Dagangan Saya
                        </h2>
                        <p class="text-[11px] text-slate-500">Klik kartu untuk mengisolasi rute kapal di peta</p>
                    </div>
                    <span id="vessel-count-badge" class="text-xs font-black px-2.5 py-1 bg-slate-900 text-white rounded-full">
                        {{ count($mapShipments) }} Kapal
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
                                    @else text-sky-700 @endif">
                                    {{ $s['number'] }} &bull; {{ $s['carrier'] }}
                                </div>
                            </div>

                            <!-- 3-Tier Status Pill -->
                            <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-full shrink-0 border
                                @if($s['status_type'] === 'DELIVERED') bg-emerald-100 text-emerald-800 border-emerald-300
                                @elseif($s['status_type'] === 'DELAYED') bg-rose-100 text-rose-800 border-rose-300
                                @else bg-sky-100 text-sky-800 border-sky-300 @endif">
                                @if($s['status_type'] === 'DELIVERED') 🟢 SUDAH SAMPAI
                                @elseif($s['status_type'] === 'DELAYED') 🔴 TERHAMBAT
                                @else 🔵 BERLAYAR @endif
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
                                <strong>Pelanggan:</strong> {{ $s['customer']['name'] }} ({{ $s['customer']['company'] }})
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
                            <span class="text-xs">⚠️</span>
                            <span class="truncate">{{ $s['signal_label'] ?? 'Sinyal Offline (Last Known Position)' }}</span>
                        </div>
                        @endif

                    </div>
                    @empty
                    <div class="p-8 text-center text-slate-400 text-xs">
                        Tidak ada kapal kargo ditemukan.
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
    const shipmentPolylines = {};
    const shipmentOriginMarkers = {};
    const shipmentDestMarkers = {};
    let globalBounds;
    let currentFocusShipmentId = null;
    let routeDisplayMode = 'FOCUS'; // 'FOCUS' (clean) or 'ALL' (all lines)

    /**
     * Switch Base Tile Layer (Google/OSM, Satelit, Maritim)
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
        if (fleetMap && globalBounds && !globalBounds.isEmpty()) {
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
        if (!myShipBounds.isEmpty()) {
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
            document.getElementById('banner-route-text').innerText = `${s.origin.name} (${s.origin.city}) → ${s.destination.name} (${s.destination.city}) • ${s.cargo_type} (${s.weight_formatted})`;
            
            const badge = document.getElementById('banner-status-badge');
            const icon = document.getElementById('banner-status-icon');
            
            if (s.status_type === 'DELIVERED') {
                badge.innerText = '🟢 SUDAH SAMPAI DI TUJUAN';
                badge.className = 'px-2.5 py-0.5 rounded text-[10px] font-black uppercase bg-emerald-500/30 text-emerald-300 border border-emerald-400/40';
                icon.className = 'w-8 h-8 rounded-xl flex items-center justify-center text-lg font-bold shrink-0 bg-emerald-600';
                icon.innerText = '✅';
            } else if (s.status_type === 'DELAYED') {
                badge.innerText = '🔴 TERHAMBAT / PERINGATAN LOGISTIK';
                badge.className = 'px-2.5 py-0.5 rounded text-[10px] font-black uppercase bg-rose-500/30 text-rose-300 border border-rose-400/40';
                icon.className = 'w-8 h-8 rounded-xl flex items-center justify-center text-lg font-bold shrink-0 bg-rose-600';
                icon.innerText = '⚠️';
            } else {
                badge.innerText = '🔵 SEDANG BERLAYAR DI JALUR LAUT';
                badge.className = 'px-2.5 py-0.5 rounded text-[10px] font-black uppercase bg-sky-500/30 text-sky-300 border border-sky-400/40';
                icon.className = 'w-8 h-8 rounded-xl flex items-center justify-center text-lg font-bold shrink-0 bg-sky-600';
                icon.innerText = '🚢';
            }

            banner.classList.remove('hidden');
        }

        // Update polylines visibility (clean focused line)
        updateRoutePolylinesVisibility();

        // Fit map bounds to encompass the full voyage of the selected shipment
        if (s.sea_route && s.sea_route.length >= 2) {
            const voyageBounds = L.latLngBounds();
            s.sea_route.forEach(pt => voyageBounds.extend(pt));
            fleetMap.fitBounds(voyageBounds, { padding: [60, 60], maxZoom: 6 });
        } else if (vesselMarkers[id]) {
            fleetMap.flyTo(vesselMarkers[id].getLatLng(), 6);
        }

        // Open popup
        setTimeout(() => {
            if (vesselMarkers[id]) vesselMarkers[id].openPopup();
        }, 800);
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
            const poly = shipmentPolylines[s.id];
            if (!poly) return;

            const isThisFocused = (currentFocusShipmentId === s.id);

            if (currentFocusShipmentId !== null) {
                // In FOCUS mode: prominently highlight selected route, hide/dim others
                if (isThisFocused) {
                    poly.setStyle({
                        opacity: 0.95,
                        weight: 5,
                        color: s.status_color,
                        dashArray: s.status_type === 'DELIVERED' ? null : '10, 8'
                    });
                    poly.bringToFront();
                    if (!fleetMap.hasLayer(poly)) poly.addTo(fleetMap);
                } else {
                    if (routeDisplayMode === 'FOCUS') {
                        if (fleetMap.hasLayer(poly)) fleetMap.removeLayer(poly);
                    } else {
                        poly.setStyle({
                            opacity: 0.15,
                            weight: 2,
                            color: '#94a3b8',
                            dashArray: '4, 6'
                        });
                        if (!fleetMap.hasLayer(poly)) poly.addTo(fleetMap);
                    }
                }
            } else {
                // In overview mode
                if (routeDisplayMode === 'ALL') {
                    poly.setStyle({
                        opacity: 0.45,
                        weight: 3,
                        color: s.status_color,
                        dashArray: s.status_type === 'DELIVERED' ? null : '6, 6'
                    });
                    if (!fleetMap.hasLayer(poly)) poly.addTo(fleetMap);
                } else {
                    // Soft clean preview
                    poly.setStyle({
                        opacity: 0.35,
                        weight: 2.5,
                        color: s.status_color,
                        dashArray: s.status_type === 'DELIVERED' ? null : '6, 6'
                    });
                    if (!fleetMap.hasLayer(poly)) poly.addTo(fleetMap);
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
                if (shipmentPolylines[id]) fleetMap.removeLayer(shipmentPolylines[id]);
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

        L.control.zoom({ position: 'bottomright' }).addTo(fleetMap);

        // 2. Base Tile Layers
        baseTileLayers = {
            osm: L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            }),
            satellite: L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                maxZoom: 18,
                attribution: '&copy; Esri World Imagery'
            }),
            voyager: L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                maxZoom: 19,
                subdomains: 'abcd',
                attribution: '&copy; CARTO'
            })
        };

        currentBaseLayer = baseTileLayers.osm;
        currentBaseLayer.addTo(fleetMap);

        globalBounds = L.latLngBounds();

        // 3. Render Ships & True Maritime Corridors with Color Coding
        fleetShipments.forEach((s) => {
            const pos = s.current_position;
            const origin = s.origin;
            const dest = s.destination;

            // Draw Real Maritime Sea Polyline
            if (s.sea_route && s.sea_route.length >= 2) {
                const poly = L.polyline(s.sea_route, {
                    color: s.status_color,
                    weight: 3,
                    opacity: 0.35,
                    dashArray: s.status_type === 'DELIVERED' ? null : '6, 6'
                }).addTo(fleetMap);

                poly.on('click', () => {
                    selectShipmentRoute(s.id);
                });

                shipmentPolylines[s.id] = poly;
            }

            // Origin Port Marker (A)
            if (origin.lat && origin.lng) {
                const originPinHtml = `
                    <div class="relative flex items-center justify-center cursor-pointer" style="width: 24px; height: 30px; margin-left: -12px; margin-top: -30px;">
                        <svg width="24" height="30" viewBox="0 0 24 30" fill="none">
                            <path d="M12 0C5.37 0 0 5.37 0 12C0 21 12 30 12 30C12 30 24 21 24 12C24 5.37 18.63 0 12 0Z" fill="#10b981"/>
                            <circle cx="12" cy="11" r="5" fill="#ffffff"/>
                            <text x="12" y="14" font-size="8" font-weight="900" text-anchor="middle" fill="#065f46">A</text>
                        </svg>
                    </div>
                `;
                const origMarker = L.marker([origin.lat, origin.lng], {
                    icon: L.divIcon({ html: originPinHtml, className: 'gmap-origin-pin', iconSize: [24, 30] }),
                    zIndexOffset: 300
                }).addTo(fleetMap).bindPopup(`
                    <div class="p-2 font-sans text-xs">
                        <div class="font-black text-emerald-800 text-[10px] uppercase">PELABUHAN ASAL (ORIGIN PORT)</div>
                        <div class="text-sm font-black text-slate-900 mt-0.5">${origin.name}</div>
                        <div class="text-slate-500 text-[11px]">${origin.city}, ${origin.country}</div>
                        <div class="text-[10px] text-slate-400 mt-1">Muatan Kapal: <strong>${s.vessel_name}</strong></div>
                    </div>
                `);
                shipmentOriginMarkers[s.id] = origMarker;
                globalBounds.extend([origin.lat, origin.lng]);
            }

            // Destination Port Marker (B)
            if (dest.lat && dest.lng) {
                const destPinHtml = `
                    <div class="relative flex items-center justify-center cursor-pointer" style="width: 24px; height: 30px; margin-left: -12px; margin-top: -30px;">
                        <svg width="24" height="30" viewBox="0 0 24 30" fill="none">
                            <path d="M12 0C5.37 0 0 5.37 0 12C0 21 12 30 12 30C12 30 24 21 24 12C24 5.37 18.63 0 12 0Z" fill="#ef4444"/>
                            <circle cx="12" cy="11" r="5" fill="#ffffff"/>
                            <text x="12" y="14" font-size="8" font-weight="900" text-anchor="middle" fill="#991b1b">B</text>
                        </svg>
                    </div>
                `;
                const destMarker = L.marker([dest.lat, dest.lng], {
                    icon: L.divIcon({ html: destPinHtml, className: 'gmap-dest-pin', iconSize: [24, 30] }),
                    zIndexOffset: 300
                }).addTo(fleetMap).bindPopup(`
                    <div class="p-2 font-sans text-xs">
                        <div class="font-black text-rose-800 text-[10px] uppercase">PELABUHAN TUJUAN (DESTINATION PORT)</div>
                        <div class="text-sm font-black text-slate-900 mt-0.5">${dest.name}</div>
                        <div class="text-slate-500 text-[11px]">${dest.city}, ${dest.country}</div>
                        <div class="text-[10px] text-slate-400 mt-1">Pembeli: <strong>${s.customer.name}</strong></div>
                    </div>
                `);
                shipmentDestMarkers[s.id] = destMarker;
                globalBounds.extend([dest.lat, dest.lng]);
            }

            // Vessel Marker with 3-Tier Status Color Coding
            if (pos.latitude && pos.longitude) {
                const isDelivered = (s.status_type === 'DELIVERED');
                const isDelayed = (s.status_type === 'DELAYED');
                const isCoffee = (s.commodity === 'COFFEE');

                // Color choices:
                // Hijau Emerald (#10b981) for DELIVERED
                // Merah Peringatan (#e11d48) for DELAYED
                // Biru Samudra (#0284c7) for IN_TRANSIT
                const hullColor = isDelivered ? '#10b981' : (isDelayed ? '#e11d48' : '#0284c7');
                const badgeColorClass = isDelivered ? 'bg-emerald-600 text-white' : (isDelayed ? 'bg-rose-600 text-white' : 'bg-blue-600 text-white');
                const statusDot = isDelivered ? '🟢' : (isDelayed ? '🔴' : '🔵');

                const vesselMarkerHtml = `
                    <div class="relative flex flex-col items-center pointer-events-auto cursor-pointer" style="width: 120px; margin-left: -60px; margin-top: -38px;">
                        
                        <!-- Floating Vessel Name & Status Badge -->
                        <div class="${badgeColorClass} font-black text-[10px] px-2.5 py-0.5 rounded-full shadow-lg whitespace-nowrap mb-1 flex items-center gap-1 border border-white/40">
                            <span>${statusDot}</span>
                            <span>${s.vessel_name}</span>
                        </div>

                        <!-- 3D Directional Container Ship SVG -->
                        <div class="relative w-12 h-6 flex items-center justify-center transform transition-transform duration-150" style="transform: rotate(${Math.round(pos.bearing || 90)}deg);">
                            ${isDelayed ? `
                                <div class="absolute w-9 h-9 rounded-full bg-rose-500/40 border-2 border-rose-500 animate-ping"></div>
                            ` : (!isDelivered ? `
                                <div class="absolute w-8 h-8 rounded-full bg-blue-400/30 animate-pulse"></div>
                            ` : '')}

                            <svg width="48" height="24" viewBox="0 0 60 28" fill="none">
                                <path d="M4 16L10 25H48L56 16H4Z" fill="${hullColor}" stroke="#0f172a" stroke-width="1.3"/>
                                <path d="M4 16L12 11H50L56 16H4Z" fill="#334155"/>
                                <rect x="40" y="4" width="9" height="8" rx="1" fill="#f8fafc" stroke="#cbd5e1" stroke-width="0.8"/>
                                <rect x="42" y="6" width="5" height="2.5" rx="0.5" fill="#38bdf8"/>
                                <rect x="12" y="7" width="8" height="5" rx="0.5" fill="#C49A55"/>
                                <rect x="21" y="7" width="8" height="5" rx="0.5" fill="${isCoffee ? '#78350f' : '#ea580c'}"/>
                                <rect x="30" y="7" width="8" height="5" rx="0.5" fill="${hullColor}"/>
                            </svg>
                        </div>
                    </div>
                `;

                const vMarker = L.marker([pos.latitude, pos.longitude], {
                    icon: L.divIcon({
                        html: vesselMarkerHtml,
                        className: 'gmap-vessel-marker',
                        iconSize: [120, 50],
                        iconAnchor: [60, 38],
                        popupAnchor: [0, -38]
                    }),
                    zIndexOffset: isDelayed ? 950 : (isDelivered ? 850 : 900)
                }).addTo(fleetMap);

                // Rich InfoWindow Popup
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
                            <div><strong>Muatan:</strong> ${s.cargo_type} &bull; ${s.weight_formatted}</div>
                            <div><strong>Pelanggan (Buyer):</strong> ${s.customer.name} (${s.customer.company})</div>
                            <div><strong>Rute Laut:</strong> ${origin.city} &rarr; ${dest.city}</div>
                            <div><strong>Koridor:</strong> ${s.sector_label}</div>
                        </div>

                        <div class="mt-2 p-2 bg-white rounded-lg border border-slate-200 text-[11px] space-y-1">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-slate-800">📍 Posisi Terkini:</span>
                                <span class="font-mono text-[10px] font-black">${s.progress_percent}% Tuntas</span>
                            </div>
                            <div class="text-xs font-bold text-slate-900">${pos.location_name}</div>
                            <div class="text-[10px] text-slate-400 font-mono">Koordinat: ${parseFloat(pos.latitude).toFixed(4)}°, ${parseFloat(pos.longitude).toFixed(4)}°</div>
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
                                Buka Rincian &rarr;
                            </a>
                        </div>
                    </div>
                `;

                vMarker.bindPopup(infoWindowHtml);
                vMarker.on('click', () => {
                    selectShipmentRoute(s.id);
                });

                vesselMarkers[s.id] = vMarker;
                globalBounds.extend([pos.latitude, pos.longitude]);
            }
        });

        // Fit map bounds
        if (!globalBounds.isEmpty()) {
            fleetMap.fitBounds(globalBounds, { padding: [50, 50], maxZoom: 5 });
        }

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
                    } else {
                        card.style.display = 'none';
                        if (vesselMarkers[id]) fleetMap.removeLayer(vesselMarkers[id]);
                        if (shipmentOriginMarkers[id]) fleetMap.removeLayer(shipmentOriginMarkers[id]);
                        if (shipmentDestMarkers[id]) fleetMap.removeLayer(shipmentDestMarkers[id]);
                        if (shipmentPolylines[id]) fleetMap.removeLayer(shipmentPolylines[id]);
                    }
                });

                const badge = document.getElementById('vessel-count-badge');
                if (badge) badge.innerText = `${matchCount} Kapal`;
            });
        }
    });
</script>
@endsection
