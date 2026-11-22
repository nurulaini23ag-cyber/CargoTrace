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

                <!-- Interactive Leaflet Map Canvas -->
                <div id="public-tracking-map" class="h-96 w-full z-10"></div>

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
    document.addEventListener('DOMContentLoaded', function () {
        const originLat = {{ $shipment->origin->latitude }};
        const originLon = {{ $shipment->origin->longitude }};
        const destLat = {{ $shipment->destination->latitude }};
        const destLon = {{ $shipment->destination->longitude }};

        const currentLat = {{ $currentPos['latitude'] }};
        const currentLon = {{ $currentPos['longitude'] }};
        const currentLocationName = {!! json_encode($currentPos['location_name']) !!};
        const currentProgress = {{ $currentPos['progress_percent'] }};

        const routeCoords = [
            [originLat, originLon]
        ];

        const map = L.map('public-tracking-map', {
            center: [currentLat, currentLon],
            zoom: 4
        });

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        // Origin marker
        L.circleMarker([originLat, originLon], {
            radius: 8,
            fillColor: '#10b981',
            color: '#ffffff',
            weight: 2,
            opacity: 1,
            fillOpacity: 0.9
        }).addTo(map).bindPopup("<strong>ASAL:</strong> {{ $shipment->origin->name }}");

        // Destination marker
        L.circleMarker([destLat, destLon], {
            radius: 8,
            fillColor: '#ef4444',
            color: '#ffffff',
            weight: 2,
            opacity: 1,
            fillOpacity: 0.9
        }).addTo(map).bindPopup("<strong>TUJUAN:</strong> {{ $shipment->destination->name }}");

        // Waypoints
        @foreach($shipment->trackingEvents as $ev)
            @if($ev->latitude && $ev->longitude)
                routeCoords.push([{{ $ev->latitude }}, {{ $ev->longitude }}]);
                L.circleMarker([{{ $ev->latitude }}, {{ $ev->longitude }}], {
                    radius: 5,
                    fillColor: '#0284c7',
                    color: '#ffffff',
                    weight: 2,
                    opacity: 1,
                    fillOpacity: 0.8
                }).addTo(map).bindPopup("<strong>{{ $ev->location_name }}</strong><br><small>{{ $ev->event_time->format('Y-m-d H:i') }}</small>");
            @endif
        @endforeach

        routeCoords.push([destLat, destLon]);

        // Draw connecting polyline
        const polyline = L.polyline(routeCoords, {
            color: '#38251D',
            weight: 3.5,
            opacity: 0.8
        }).addTo(map);

        // Pulsing Vessel Marker ("Di Mana Barang Itu Sudah Sampai")
        const vesselHtml = `
            <div class="relative flex items-center justify-center cursor-pointer" style="width: 44px; height: 44px; margin-left: -22px; margin-top: -22px;">
                <span class="animate-ping absolute inline-flex h-10 w-10 rounded-full bg-amber-400 opacity-75"></span>
                <span class="absolute inline-flex h-7 w-7 rounded-full bg-amber-500 opacity-40"></span>
                <div class="relative inline-flex rounded-full h-8 w-8 bg-[#38251D] border-2 border-[#C49A55] shadow-xl items-center justify-center text-white text-sm font-bold">
                    🚢
                </div>
            </div>
        `;

        const vesselIcon = L.divIcon({
            html: vesselHtml,
            className: 'live-cargo-vessel-marker',
            iconSize: [44, 44],
            iconAnchor: [22, 22],
            popupAnchor: [0, -22]
        });

        const vesselMarker = L.marker([currentLat, currentLon], { icon: vesselIcon, zIndexOffset: 1000 }).addTo(map);

        vesselMarker.bindPopup(`
            <div class="p-1 font-sans text-xs min-w-[200px]">
                <div class="text-[10px] font-bold text-amber-800 uppercase tracking-wide">📍 POSISI BARANG SAAT INI</div>
                <div class="text-sm font-black text-slate-900 mb-1">${currentLocationName}</div>
                <div class="text-slate-600 text-[11px] space-y-0.5">
                    <div>Perjalanan Tuntas: <strong>${currentProgress}%</strong></div>
                    <div>Koordinat: ${currentLat.toFixed(4)}°, ${currentLon.toFixed(4)}°</div>
                    <div>Status: {{ $shipment->current_status }}</div>
                </div>
            </div>
        `).openPopup();

        const allBounds = L.latLngBounds(routeCoords);
        allBounds.extend([currentLat, currentLon]);
        map.fitBounds(allBounds, { padding: [50, 50] });
    });
</script>
@endsection
@endif
