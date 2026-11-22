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
            <div class="p-3 bg-slate-900 text-white flex items-center justify-between text-xs px-4">
                <div class="flex items-center gap-3">
                    <span class="flex items-center gap-1.5 font-bold text-[#E8D7B9]">
                        <span>🌍</span> GLOBAL MARITIME AIS TRACKING
                    </span>
                    <span class="text-slate-400">|</span>
                    <span class="text-slate-300">Menampilkan {{ count($mapShipments) }} Posisi Kapal Real-Time</span>
                </div>
                <div class="flex items-center gap-3 text-[11px]">
                    <span class="inline-flex items-center gap-1 text-emerald-400">
                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span> Origin
                    </span>
                    <span class="inline-flex items-center gap-1 text-amber-400">
                        <span class="w-2 h-2 rounded-full bg-amber-400 animate-ping"></span> Posisi Kargo (Di Mana Barang Sampai)
                    </span>
                    <span class="inline-flex items-center gap-1 text-rose-400">
                        <span class="w-2 h-2 rounded-full bg-rose-400"></span> Destination
                    </span>
                </div>
            </div>

            <!-- The Leaflet Map Canvas -->
            <div id="fleet-map" class="h-[600px] w-full z-10"></div>

            <div class="p-2.5 bg-slate-50 border-t border-slate-200 text-[11px] text-slate-500 flex items-center justify-between px-4">
                <div>
                    Peta interaktif menggunakan OpenStreetMap &bull; Klik ikon kapal untuk detail kargo, voyage, cuaca, dan ETA
                </div>
                <div class="font-mono text-[10px]">
                    Lat/Lng WGS84 Maritime Coordinates
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
    // Shipments data from backend
    const shipmentsData = {!! json_encode($mapShipments) !!};
    let map;
    const markers = {};
    const routePolylines = {};

    document.addEventListener('DOMContentLoaded', function () {
        // Initialize Leaflet Map centered on global maritime lanes (Equatorial / Indian Ocean view)
        map = L.map('fleet-map', {
            center: [10.0, 50.0],
            zoom: 3,
            minZoom: 2,
            maxZoom: 17
        });

        // OpenStreetMap Standard Tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        const bounds = L.latLngBounds();

        // Loop and render each active cargo shipment
        shipmentsData.forEach(s => {
            const pos = s.current_position;
            const origin = s.origin;
            const dest = s.destination;

            // 1. Draw Origin Port (Emerald Circle Marker)
            if (origin.lat && origin.lng) {
                const originMarker = L.circleMarker([origin.lat, origin.lng], {
                    radius: 6,
                    fillColor: '#10b981',
                    color: '#ffffff',
                    weight: 2,
                    opacity: 1,
                    fillOpacity: 0.9
                }).addTo(map);
                originMarker.bindPopup(`<strong>PELABUHAN ASAL (ORIGIN):</strong><br>${origin.name}, ${origin.country}<br><small>Cargo: ${s.number}</small>`);
                bounds.extend([origin.lat, origin.lng]);
            }

            // 2. Draw Destination Port (Rose Circle Marker)
            if (dest.lat && dest.lng) {
                const destMarker = L.circleMarker([dest.lat, dest.lng], {
                    radius: 6,
                    fillColor: '#ef4444',
                    color: '#ffffff',
                    weight: 2,
                    opacity: 1,
                    fillOpacity: 0.9
                }).addTo(map);
                destMarker.bindPopup(`<strong>PELABUHAN TUJUAN (DESTINATION):</strong><br>${dest.name}, ${dest.country}<br><small>Cargo: ${s.number}</small>`);
                bounds.extend([dest.lat, dest.lng]);
            }

            // 3. Draw Maritime Route Polyline
            const routePoints = [];
            if (origin.lat && origin.lng) routePoints.push([origin.lat, origin.lng]);
            if (s.waypoints && s.waypoints.length > 0) {
                s.waypoints.forEach(wp => {
                    routePoints.push([wp.lat, wp.lng]);
                });
            }
            if (pos.latitude && pos.longitude) {
                routePoints.push([pos.latitude, pos.longitude]);
            }
            if (dest.lat && dest.lng) routePoints.push([dest.lat, dest.lng]);

            if (routePoints.length >= 2) {
                const poly = L.polyline(routePoints, {
                    color: s.commodity === 'COCOA' ? '#795548' : '#38251D',
                    weight: 2.5,
                    opacity: 0.65,
                    dashArray: '5, 8'
                }).addTo(map);
                routePolylines[s.id] = poly;
            }

            // 4. Draw Current Vessel Location Beacon ("Di Mana Barang Itu Sudah Sampai")
            if (pos.latitude && pos.longitude) {
                const isCoffee = s.commodity === 'COFFEE';
                const vesselEmoji = isCoffee ? '☕' : '🍫';
                const pulseBg = isCoffee ? 'bg-amber-400' : 'bg-orange-500';
                const iconBg = isCoffee ? 'bg-[#38251D]' : 'bg-[#795548]';
                const borderColor = isCoffee ? 'border-[#C49A55]' : 'border-amber-300';

                const vesselIconHtml = `
                    <div class="relative flex items-center justify-center cursor-pointer" style="width: 40px; height: 40px; margin-left: -20px; margin-top: -20px;">
                        <span class="animate-ping absolute inline-flex h-9 w-9 rounded-full ${pulseBg} opacity-75"></span>
                        <div class="relative inline-flex rounded-full h-8 w-8 ${iconBg} border-2 ${borderColor} shadow-xl items-center justify-center text-white text-xs font-bold">
                            ${vesselEmoji}
                        </div>
                    </div>
                `;

                const vesselIcon = L.divIcon({
                    html: vesselIconHtml,
                    className: 'fleet-vessel-marker',
                    iconSize: [40, 40],
                    iconAnchor: [20, 20],
                    popupAnchor: [0, -20]
                });

                const vMarker = L.marker([pos.latitude, pos.longitude], {
                    icon: vesselIcon,
                    zIndexOffset: 800
                }).addTo(map);

                const popupHtml = `
                    <div class="p-2 font-sans text-xs min-w-[240px]">
                        <div class="flex items-center justify-between gap-1 pb-1 mb-1.5 border-b border-slate-200">
                            <span class="font-bold text-[10px] uppercase tracking-wider text-amber-800">
                                📍 POSISI KARGO SAAT INI
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

                vMarker.bindPopup(popupHtml);
                markers[s.id] = vMarker;
                bounds.extend([pos.latitude, pos.longitude]);
            }
        });

        // Fit map bounds to view all active vessels
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
        const marker = markers[id];
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
