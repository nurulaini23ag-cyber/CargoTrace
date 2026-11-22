@extends('layouts.app')

@section('title', 'Public Cargo Tracking & Live Position — CargoTrace')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <!-- Hero Search Header -->
    <div class="bg-gradient-to-r from-[#38251D] via-[#4E342E] to-[#292421] rounded-2xl p-6 md:p-8 text-white shadow-lg border border-[#C49A55]/30">
        <div class="max-w-2xl">
            <div class="flex items-center gap-2 mb-2">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#C49A55] text-slate-900">
                    PUBLIC TRACKING PORTAL
                </span>
                <span class="text-xs text-[#E8D7B9] font-medium">
                    Global Coffee & Cocoa Freight Visibility
                </span>
            </div>
            <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-white">
                Lacak Posisi Kargo Kapal Laut Anda
            </h1>
            <p class="text-xs md:text-sm text-[#E8D7B9] mt-1.5 leading-relaxed">
                Pantau perjalanan kontainer kakao dan kopi secara transparan dari pelabuhan asal hingga pelabuhan tujuan dengan live AIS waypoint tracking.
            </p>

            <!-- Search Form -->
            <form action="{{ route('tracking.public') }}" method="GET" class="mt-5 flex flex-col sm:flex-row gap-2">
                <div class="relative flex-1">
                    <input type="text" name="q" value="{{ $trackingNumber }}"
                           placeholder="Masukkan nomor kargo (cth: CTC-2026-0001)..."
                           class="w-full px-4 py-3 pl-10 rounded-xl bg-white text-slate-900 placeholder-slate-400 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-[#C49A55] shadow-inner" required>
                    <span class="absolute left-3.5 top-3.5 text-slate-400 text-sm">🔍</span>
                </div>
                <button type="submit" class="px-6 py-3 bg-[#C49A55] hover:bg-[#b38944] text-[#292421] font-extrabold rounded-xl text-sm transition shadow-md flex items-center justify-center gap-2">
                    <span>Lacak Kargo</span>
                    <span>&rarr;</span>
                </button>
            </form>

            <!-- Quick Demo Tracking Number Pills -->
            <div class="mt-4 flex flex-wrap items-center gap-2 text-xs">
                <span class="text-[#E8D7B9]/80 font-medium">Contoh Demo:</span>
                <a href="{{ route('tracking.public', ['q' => 'CTC-2026-0001']) }}" class="px-2 py-0.5 rounded-full bg-white/10 hover:bg-white/20 text-[#E8D7B9] border border-white/20 transition">
                    ☕ CTC-2026-0001 (Arabica to Rotterdam)
                </a>
                <a href="{{ route('tracking.public', ['q' => 'CTC-2026-0002']) }}" class="px-2 py-0.5 rounded-full bg-white/10 hover:bg-white/20 text-[#E8D7B9] border border-white/20 transition">
                    🍫 CTC-2026-0002 (Cocoa to Hamburg)
                </a>
                <a href="{{ route('tracking.public', ['q' => 'CTC-2026-0005']) }}" class="px-2 py-0.5 rounded-full bg-white/10 hover:bg-white/20 text-[#E8D7B9] border border-white/20 transition">
                    ✅ CTC-2026-0005 (Delivered Genoa)
                </a>
            </div>
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
                Nomor pengiriman <span class="font-mono font-bold text-rose-600">{{ $trackingNumber }}</span> tidak terdaftar dalam sistem logistik maritim CargoTrace. Silakan periksa kembali nomor B/L atau tracking number Anda.
            </p>
            <div class="mt-4">
                <a href="{{ route('tracking.public') }}" class="text-xs font-bold text-[#38251D] hover:underline">
                    &larr; Coba Pencarian Lain
                </a>
            </div>
        </div>
    @elseif($shipment && $currentPos)
        <!-- Shipment Found: Live Tracking Dashboard -->
        <div class="space-y-6">
            <!-- Prominent Position HUD: "Di Mana Barang Itu Sudah Sampai" -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-4 bg-[#38251D] text-white flex flex-col md:flex-row md:items-center justify-between gap-3">
                    <div>
                        <div class="text-[11px] font-bold uppercase tracking-wider text-[#C49A55] flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-amber-400 animate-ping"></span>
                            POSISI KARGO SAAT INI &bull; REAL-TIME CARGO POSITION
                        </div>
                        <div class="text-xl font-black text-white mt-0.5">
                            {{ $currentPos['location_name'] }}
                        </div>
                        <div class="text-xs text-[#E8D7B9] mt-0.5 flex flex-wrap items-center gap-2">
                            <span>Koordinat: <strong>{{ number_format($currentPos['latitude'], 4) }}°, {{ number_format($currentPos['longitude'], 4) }}°</strong></span>
                            <span>&bull;</span>
                            <span>Kapal: <strong>{{ $shipment->vessel_name ?? $shipment->vessel_carrier ?? 'MV Cargo Liner' }}</strong></span>
                            <span>&bull;</span>
                            <span>Kontainer: <strong>{{ $shipment->container_number ?? 'CTRU-99201' }}</strong></span>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="text-right">
                            <div class="text-[10px] text-white/70">Kemajuan Perjalanan</div>
                            <div class="text-lg font-black text-[#C49A55]">{{ $currentPos['progress_percent'] }}%</div>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-extrabold uppercase tracking-wider
                            @if($shipment->current_status === 'DELIVERED') bg-emerald-500 text-white
                            @elseif($shipment->current_status === 'IN_TRANSIT') bg-amber-400 text-slate-900
                            @else bg-white/20 text-white @endif">
                            {{ $shipment->current_status }}
                        </span>
                    </div>
                </div>

                <!-- Environmental Live Weather Bar -->
                @if($weather)
                <div class="p-3 bg-amber-50/50 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3 text-xs text-slate-700 px-5">
                    <div class="flex items-center gap-2">
                        <span class="text-base">🌊</span>
                        <span><strong>Kondisi Cuaca Maritim:</strong> {{ $weather['condition'] ?? 'Cerah Berawan' }} ({{ $weather['temperature'] ?? 28 }}°C)</span>
                        <span class="text-slate-400">&bull;</span>
                        <span>Angin: {{ $weather['wind_speed'] ?? 12 }} km/h</span>
                        <span class="text-slate-400">&bull;</span>
                        <span>Kelembapan: {{ $weather['humidity'] ?? 75 }}%</span>
                    </div>
                    <span class="text-[10px] text-slate-400">Open-Meteo Marine Data</span>
                </div>
                @endif

                <!-- Shopee-Style Live Vessel Sailing Controls Bar -->
                <div class="px-5 py-2.5 bg-slate-900 text-white flex flex-wrap items-center justify-between gap-2 text-xs border-b border-slate-800">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-[10px] font-black uppercase tracking-wider text-[#C49A55] flex items-center gap-1">
                            <span>⚡</span> SIMULASI PELAYARAN KAPAL
                        </span>
                        <div class="flex items-center gap-1 bg-slate-800 rounded-lg p-0.5 border border-slate-700">
                            <button id="public-btn-play-pause" type="button" class="px-2.5 py-1 rounded bg-[#C49A55] text-slate-900 font-extrabold text-[11px] hover:bg-[#b38944] transition flex items-center gap-1 shadow-xs">
                                <span id="public-play-icon">⏸️</span> <span id="public-play-text">Jeda</span>
                            </button>
                            <button id="public-btn-restart" type="button" class="px-2 py-1 rounded text-slate-300 hover:text-white hover:bg-slate-700 text-[11px] font-bold transition flex items-center gap-1">
                                <span>🔄</span> Ulangi
                            </button>
                            <button id="public-btn-jump-now" type="button" class="px-2 py-1 rounded text-slate-300 hover:text-white hover:bg-slate-700 text-[11px] font-bold transition flex items-center gap-1">
                                <span>🎯</span> Posisi Terkini
                            </button>
                        </div>

                        <!-- Speed selector -->
                        <div class="flex items-center gap-1 bg-slate-800 rounded-lg p-0.5 border border-slate-700 text-[10px]">
                            <span class="text-slate-400 px-1 font-bold">Speed:</span>
                            <button type="button" onclick="setPublicSailingSpeed(1)" class="pub-speed-btn px-1.5 py-0.5 rounded font-bold transition bg-[#C49A55] text-slate-900" id="pub-speed-1">1x</button>
                            <button type="button" onclick="setPublicSailingSpeed(2)" class="pub-speed-btn px-1.5 py-0.5 rounded font-bold transition text-slate-300 hover:text-white" id="pub-speed-2">2x</button>
                            <button type="button" onclick="setPublicSailingSpeed(4)" class="pub-speed-btn px-1.5 py-0.5 rounded font-bold transition text-slate-300 hover:text-white" id="pub-speed-4">4x</button>
                        </div>
                    </div>

                    <!-- World Map Theme Selector -->
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] text-slate-400 font-bold uppercase hidden sm:inline">Peta Dunia:</span>
                        <select id="public-map-style" onchange="changePublicMapTheme(this.value)" class="bg-slate-800 text-white border border-slate-700 rounded-lg px-2.5 py-1 text-[11px] font-semibold focus:outline-none focus:ring-1 focus:ring-[#C49A55] cursor-pointer">
                            <option value="voyager" selected>🗺️ Peta Dunia Voyager (Default)</option>
                            <option value="dark">🌙 Dark Maritime Chart</option>
                            <option value="osm">🧭 OpenStreetMap Classic</option>
                        </select>
                    </div>
                </div>

                <!-- Interactive Leaflet Map Canvas with Telemetry Overlay -->
                <div class="relative w-full">
                    <!-- Live Telemetry HUD Bar on Top of Map -->
                    <div class="absolute top-3 left-14 z-20 bg-slate-900/90 backdrop-blur-md text-white px-3 py-1.5 rounded-xl border border-[#C49A55]/40 shadow-lg text-[11px] flex items-center gap-3 pointer-events-none">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                            <span class="font-extrabold text-[#E8D7B9]">KAPAL BERLAYAR (LIVE):</span>
                            <span class="font-mono text-white font-bold" id="pub-telemetry-coords">Lat: {{ number_format($currentPos['latitude'], 4) }}°, Lon: {{ number_format($currentPos['longitude'], 4) }}°</span>
                        </div>
                        <span class="text-slate-500">|</span>
                        <span class="text-[#C49A55] font-bold">19.4 Knots</span>
                    </div>

                    <div id="public-tracking-map" class="h-[500px] w-full z-10"></div>
                </div>

                <div class="p-3 bg-slate-50 text-[11px] text-slate-600 border-t border-slate-100 flex flex-wrap items-center justify-between px-5">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Pelabuhan Asal: {{ $shipment->origin->city }}</span>
                        <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-ping"></span> <strong>Posisi Barang: {{ $currentPos['location_name'] }}</strong></span>
                        <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span> Pelabuhan Tujuan: {{ $shipment->destination->city }}</span>
                    </div>
                    <div>
                        &copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" class="underline text-slate-500">OpenStreetMap</a>
                    </div>
                </div>
            </div>

            <!-- Voyage Details & Waypoint History Grid -->
            <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                <!-- Consignment Specs (5 cols) -->
                <div class="md:col-span-5 space-y-4">
                    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-3">
                        <h3 class="text-sm font-bold text-slate-900 pb-2 border-b border-slate-100 flex items-center justify-between">
                            <span>Spesifikasi Kargo & Kontainer</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-[#38251D] text-[#E8D7B9]">
                                {{ $shipment->cargo_type }}
                            </span>
                        </h3>
                        <div class="grid grid-cols-2 gap-3 text-xs text-slate-600">
                            <div>
                                <div class="text-[10px] text-slate-400 font-bold uppercase">Nomor Pengiriman</div>
                                <div class="font-mono font-bold text-slate-900">{{ $shipment->shipment_number }}</div>
                            </div>
                            <div>
                                <div class="text-[10px] text-slate-400 font-bold uppercase">Carrier</div>
                                <div class="font-bold text-slate-900">{{ $shipment->carrier->name }}</div>
                            </div>
                            <div>
                                <div class="text-[10px] text-slate-400 font-bold uppercase">Estimasi Tiba (ETA)</div>
                                <div class="font-bold text-slate-900">{{ $shipment->estimated_arrival ? $shipment->estimated_arrival->format('d M Y, H:i') : $shipment->expected_arrival->format('d M Y, H:i') }}</div>
                            </div>
                            <div>
                                <div class="text-[10px] text-slate-400 font-bold uppercase">Berat Bersih</div>
                                <div class="font-bold text-slate-900">{{ number_format($shipment->weight, 0) }} kg</div>
                            </div>
                        </div>

                        <!-- Commodity Items Table -->
                        @if($shipment->items && $shipment->items->count() > 0)
                        <div class="pt-2 border-t border-slate-100">
                            <div class="text-[10px] font-bold uppercase text-slate-400 mb-1.5">Rincian Komoditas:</div>
                            <div class="space-y-1.5">
                                @foreach($shipment->items as $item)
                                <div class="p-2 bg-slate-50 rounded-lg text-xs flex items-center justify-between">
                                    <div>
                                        <div class="font-bold text-slate-800">{{ $item->item_name }}</div>
                                        <div class="text-[10px] text-slate-500">{{ $item->variety }} &bull; {{ $item->grade }}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-bold text-slate-900">{{ $item->package_count }} {{ $item->package_type ?? 'Bags' }}</div>
                                        <div class="text-[10px] text-slate-500">{{ number_format($item->net_weight, 0) }} kg</div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Timeline Waypoint History (7 cols) -->
                <div class="md:col-span-7 bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                    <h3 class="text-sm font-bold text-slate-900 pb-2 border-b border-slate-100 mb-4 flex items-center justify-between">
                        <span>Riwayat Checkpoint & Waypoint Maritim</span>
                        <span class="text-xs font-semibold text-slate-500">{{ $shipment->trackingEvents->count() }} Peristiwa</span>
                    </h3>

                    <div class="space-y-4 relative before:absolute before:inset-0 before:left-3.5 before:w-0.5 before:bg-slate-200">
                        @forelse($shipment->trackingEvents as $ev)
                        <div class="relative flex items-start gap-3 pl-1">
                            <div class="w-6 h-6 rounded-full bg-[#38251D] border-2 border-white shadow-sm flex items-center justify-center text-white text-[10px] z-10 shrink-0">
                                ⚓
                            </div>
                            <div class="bg-slate-50 p-3 rounded-xl border border-slate-200 flex-1">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-xs text-slate-900">{{ $ev->location_name }}</span>
                                    <span class="text-[10px] text-slate-500">{{ $ev->event_time->format('d M Y, H:i') }}</span>
                                </div>
                                <div class="text-xs text-slate-600 mt-1">
                                    {{ $ev->description }}
                                </div>
                                <div class="mt-1 text-[10px] text-slate-400 font-mono">
                                    Stage: {{ $ev->status ?? $ev->event_type }}
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-6 text-slate-500 text-xs">
                            Belum ada waypoint perantara yang dicatat.
                        </div>
                        @endforelse
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
    let pubMap;
    let pubCurrentTileLayer;
    let pubTileProviders = {};
    let pubAnimationInterval = null;
    let pubDensePoints = [];
    let pubCurStepIndex = 0;
    let pubSailingSpeed = 1;
    let pubIsSailingPlaying = true;
    let pubMovingVesselMarker = null;
    let pubTraveledPolyline = null;
    let pubPlannedPolyline = null;

    function setPublicSailingSpeed(multiplier) {
        pubSailingSpeed = multiplier;
        document.querySelectorAll('.pub-speed-btn').forEach(b => {
            b.classList.remove('bg-[#C49A55]', 'text-slate-900');
            b.classList.add('text-slate-300');
        });
        const activeBtn = document.getElementById('pub-speed-' + multiplier);
        if (activeBtn) {
            activeBtn.classList.add('bg-[#C49A55]', 'text-slate-900');
            activeBtn.classList.remove('text-slate-300');
        }
    }

    function changePublicMapTheme(theme) {
        if (!pubMap || !pubTileProviders[theme]) return;
        if (pubCurrentTileLayer) {
            pubMap.removeLayer(pubCurrentTileLayer);
        }
        pubCurrentTileLayer = pubTileProviders[theme];
        pubCurrentTileLayer.addTo(pubMap);
    }

    function pubCalculateBearing(lat1, lon1, lat2, lon2) {
        const toRad = Math.PI / 180;
        const toDeg = 180 / Math.PI;
        const dLon = (lon2 - lon1) * toRad;
        const y = Math.sin(dLon) * Math.cos(lat2 * toRad);
        const x = Math.cos(lat1 * toRad) * Math.sin(lat2 * toRad) -
                  Math.sin(lat1 * toRad) * Math.cos(lat2 * toRad) * Math.cos(dLon);
        return (Math.atan2(y, x) * toDeg + 360) % 360;
    }

    document.addEventListener('DOMContentLoaded', function () {
        const originLat = {{ $shipment->origin->latitude }};
        const originLon = {{ $shipment->origin->longitude }};
        const destLat = {{ $shipment->destination->latitude }};
        const destLon = {{ $shipment->destination->longitude }};

        const currentLat = {{ $currentPos['latitude'] }};
        const currentLon = {{ $currentPos['longitude'] }};
        const currentLocationName = {!! json_encode($currentPos['location_name']) !!};
        const currentProgress = {{ $currentPos['progress_percent'] }};
        const vesselName = {!! json_encode($shipment->vessel_name ?? $shipment->vessel_carrier ?? 'MV Cargo Liner') !!};

        const rawWaypoints = [
            [originLat, originLon]
        ];

        @foreach($shipment->trackingEvents as $ev)
            @if($ev->latitude && $ev->longitude)
                rawWaypoints.push([{{ $ev->latitude }}, {{ $ev->longitude }}]);
            @endif
        @endforeach

        rawWaypoints.push([currentLat, currentLon]);
        rawWaypoints.push([destLat, destLon]);

        const cleanedWaypoints = [];
        for (let i = 0; i < rawWaypoints.length; i++) {
            if (i === 0 || rawWaypoints[i][0] !== rawWaypoints[i-1][0] || rawWaypoints[i][1] !== rawWaypoints[i-1][1]) {
                cleanedWaypoints.push(rawWaypoints[i]);
            }
        }

        pubMap = L.map('public-tracking-map', {
            center: [currentLat, currentLon],
            zoom: 4,
            minZoom: 2,
            maxZoom: 18,
            worldCopyJump: true
        });

        pubTileProviders = {
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

        pubCurrentTileLayer = pubTileProviders.voyager;
        pubCurrentTileLayer.addTo(pubMap);

        // Origin marker
        L.circleMarker([originLat, originLon], {
            radius: 8,
            fillColor: '#10b981',
            color: '#ffffff',
            weight: 2.5,
            opacity: 1,
            fillOpacity: 0.95
        }).addTo(pubMap).bindPopup("<strong>PELABUHAN ASAL:</strong> {{ $shipment->origin->name }}, {{ $shipment->origin->country }}");

        // Destination marker
        L.circleMarker([destLat, destLon], {
            radius: 8,
            fillColor: '#ef4444',
            color: '#ffffff',
            weight: 2.5,
            opacity: 1,
            fillOpacity: 0.95
        }).addTo(pubMap).bindPopup("<strong>PELABUHAN TUJUAN:</strong> {{ $shipment->destination->name }}, {{ $shipment->destination->country }}");

        // Intermediate checkpoints
        @foreach($shipment->trackingEvents as $ev)
            @if($ev->latitude && $ev->longitude)
                L.circleMarker([{{ $ev->latitude }}, {{ $ev->longitude }}], {
                    radius: 5,
                    fillColor: '#0284c7',
                    color: '#ffffff',
                    weight: 2,
                    opacity: 1,
                    fillOpacity: 0.85
                }).addTo(pubMap).bindPopup("<strong>CHECKPOINT:</strong> {{ $ev->location_name }}<br><small>{{ $ev->event_time->format('Y-m-d H:i') }}</small>");
            @endif
        @endforeach

        // Current Anchor Beacon
        const beaconHtml = `
            <div class="relative flex items-center justify-center pointer-events-auto" style="width: 36px; height: 36px; margin-left: -18px; margin-top: -18px;">
                <span class="animate-ping absolute inline-flex h-9 w-9 rounded-full bg-amber-400 opacity-75"></span>
                <div class="relative inline-flex rounded-full h-7 w-7 bg-amber-600 border-2 border-white shadow-xl items-center justify-center text-white text-xs font-black">
                    📍
                </div>
            </div>
        `;
        const pubBeacon = L.marker([currentLat, currentLon], {
            icon: L.divIcon({
                html: beaconHtml,
                className: 'pub-beacon-marker',
                iconSize: [36, 36],
                iconAnchor: [18, 18],
                popupAnchor: [0, -18]
            }),
            zIndexOffset: 700
        }).addTo(pubMap);
        pubBeacon.bindPopup(`
            <div class="p-1 font-sans text-xs min-w-[200px]">
                <div class="text-[10px] font-black uppercase tracking-wider text-amber-800">📍 POSISI REAL-TIME SAAT INI</div>
                <div class="text-sm font-extrabold text-slate-900">${currentLocationName}</div>
                <div class="text-slate-600 text-[11px] mt-1 space-y-0.5">
                    <div>Perjalanan Tuntas: <strong>${currentProgress}%</strong></div>
                    <div>Koordinat: ${currentLat.toFixed(4)}°, ${currentLon.toFixed(4)}°</div>
                    <div>Status: <strong>{{ $shipment->current_status }}</strong></div>
                </div>
            </div>
        `);

        // Build Dense Interpolated Steps for Smooth Shopee-Style Gliding
        pubDensePoints = [];
        for (let i = 0; i < cleanedWaypoints.length - 1; i++) {
            const p1 = cleanedWaypoints[i];
            const p2 = cleanedWaypoints[i + 1];

            const dLat = (p2[0] - p1[0]) * 111;
            const dLon = (p2[1] - p1[1]) * 111 * Math.cos((p1[0] + p2[0]) * Math.PI / 360);
            const distKm = Math.sqrt(dLat * dLat + dLon * dLon);
            const steps = Math.max(80, Math.min(300, Math.round(distKm * 0.08)));

            for (let s = 0; s < steps; s++) {
                const t = s / steps;
                const curLat = p1[0] + (p2[0] - p1[0]) * t;
                const curLon = p1[1] + (p2[1] - p1[1]) * t;
                const bearing = pubCalculateBearing(curLat, curLon, p2[0], p2[1]);

                pubDensePoints.push({
                    lat: curLat,
                    lng: curLon,
                    bearing: bearing
                });
            }
        }
        if (cleanedWaypoints.length > 0) {
            const last = cleanedWaypoints[cleanedWaypoints.length - 1];
            pubDensePoints.push({
                lat: last[0],
                lng: last[1],
                bearing: pubDensePoints.length > 0 ? pubDensePoints[pubDensePoints.length - 1].bearing : 0
            });
        }

        pubPlannedPolyline = L.polyline(cleanedWaypoints, {
            color: '#38251D',
            weight: 3,
            opacity: 0.45,
            dashArray: '6, 8'
        }).addTo(pubMap);

        pubTraveledPolyline = L.polyline([], {
            color: '#C49A55',
            weight: 4,
            opacity: 0.95
        }).addTo(pubMap);

        // Moving Ship (Shopee Style)
        const pubMovingVesselHtml = `
            <div class="relative flex items-center justify-center cursor-pointer" style="width: 52px; height: 52px; margin-left: -26px; margin-top: -26px;">
                <div class="absolute w-12 h-12 rounded-full bg-cyan-400/40 animate-ping"></div>
                <div class="relative w-10 h-10 bg-gradient-to-br from-[#38251D] via-[#4E342E] to-[#292421] border-2 border-[#C49A55] rounded-full shadow-2xl flex items-center justify-center text-white text-base font-bold transition-transform duration-75" id="pub-sailing-ship-icon">
                    🚢
                </div>
                <div class="absolute -top-7 whitespace-nowrap bg-slate-900/95 text-[#E8D7B9] text-[9px] font-black px-2 py-0.5 rounded-full border border-[#C49A55]/50 shadow-lg flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span id="pub-ship-badge-text">Berlayar...</span>
                </div>
            </div>
        `;

        const pubStartPt = pubDensePoints[0] || { lat: originLat, lng: originLon };
        pubMovingVesselMarker = L.marker([pubStartPt.lat, pubStartPt.lng], {
            icon: L.divIcon({
                html: pubMovingVesselHtml,
                className: 'pub-sailing-marker',
                iconSize: [52, 52],
                iconAnchor: [26, 26],
                popupAnchor: [0, -26]
            }),
            zIndexOffset: 1200
        }).addTo(pubMap);

        const pubTelemetryCoords = document.getElementById('pub-telemetry-coords');
        const pubShipBadgeText = document.getElementById('pub-ship-badge-text');

        function animatePublicSailingFrame() {
            if (!pubIsSailingPlaying) return;

            pubCurStepIndex += pubSailingSpeed;
            if (pubCurStepIndex >= pubDensePoints.length) {
                pubCurStepIndex = 0;
            }

            const cur = pubDensePoints[pubCurStepIndex];
            if (cur) {
                pubMovingVesselMarker.setLatLng([cur.lat, cur.lng]);

                const iconElem = document.getElementById('pub-sailing-ship-icon');
                if (iconElem) {
                    iconElem.style.transform = `rotate(${Math.round(cur.bearing)}deg)`;
                }

                const trail = pubDensePoints.slice(0, pubCurStepIndex + 1).map(p => [p.lat, p.lng]);
                pubTraveledPolyline.setLatLngs(trail);

                if (pubTelemetryCoords) {
                    pubTelemetryCoords.innerText = `Lat: ${cur.lat.toFixed(4)}°, Lon: ${cur.lng.toFixed(4)}°`;
                }
                const pct = Math.round((pubCurStepIndex / (pubDensePoints.length - 1)) * 100);
                if (pubShipBadgeText) {
                    pubShipBadgeText.innerText = `Berlayar (${pct}%)`;
                }
            }
        }

        pubAnimationInterval = setInterval(animatePublicSailingFrame, 35);

        // Control handlers
        const playPauseBtn = document.getElementById('public-btn-play-pause');
        const playIcon = document.getElementById('public-play-icon');
        const playText = document.getElementById('public-play-text');
        if (playPauseBtn) {
            playPauseBtn.addEventListener('click', function () {
                pubIsSailingPlaying = !pubIsSailingPlaying;
                if (pubIsSailingPlaying) {
                    playIcon.innerText = '⏸️';
                    playText.innerText = 'Jeda';
                    pubAnimationInterval = setInterval(animatePublicSailingFrame, 35);
                } else {
                    playIcon.innerText = '▶️';
                    playText.innerText = 'Lanjut';
                    clearInterval(pubAnimationInterval);
                }
            });
        }

        const restartBtn = document.getElementById('public-btn-restart');
        if (restartBtn) {
            restartBtn.addEventListener('click', function () {
                pubCurStepIndex = 0;
                pubTraveledPolyline.setLatLngs([]);
                if (!pubIsSailingPlaying) {
                    pubIsSailingPlaying = true;
                    if (playIcon) playIcon.innerText = '⏸️';
                    if (playText) playText.innerText = 'Jeda';
                    pubAnimationInterval = setInterval(animatePublicSailingFrame, 35);
                }
                pubMap.panTo([originLat, originLon]);
            });
        }

        const jumpNowBtn = document.getElementById('public-btn-jump-now');
        if (jumpNowBtn) {
            jumpNowBtn.addEventListener('click', function () {
                let closestIndex = 0;
                let minDist = 999999;
                for (let i = 0; i < pubDensePoints.length; i++) {
                    const d = Math.abs(pubDensePoints[i].lat - currentLat) + Math.abs(pubDensePoints[i].lng - currentLon);
                    if (d < minDist) {
                        minDist = d;
                        closestIndex = i;
                    }
                }
                pubCurStepIndex = closestIndex;
                pubMap.flyTo([currentLat, currentLon], 6, { animate: true, duration: 1.2 });
                pubBeacon.openPopup();
            });
        }

        const allBounds = L.latLngBounds(cleanedWaypoints);
        allBounds.extend([currentLat, currentLon]);
        pubMap.fitBounds(allBounds, { padding: [55, 55] });
    });
</script>
@endsection
@endif
