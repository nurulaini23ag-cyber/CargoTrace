@extends('layouts.app')

@section('title', 'Direktori Titik Lokasi & Pelabuhan Maritim — CargoTrace')

@section('content')
<div class="space-y-6 font-sans">

    <!-- Top Header Banner -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 rounded-2xl p-6 md:p-8 text-white shadow-lg border border-slate-700/60 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-black bg-sky-500 text-slate-950 uppercase tracking-wider">
                    SISTEM LOKASI & GEODATABASE MARITIM
                </span>
                <span class="text-xs text-sky-300 font-semibold flex items-center gap-1">
                    <span>🗺️</span> Google Maps & Satelit Terintegrasi
                </span>
            </div>
            <h1 class="text-2xl md:text-3xl font-black tracking-tight text-white">
                Direktori Titik Lokasi & Pelabuhan Dunia
            </h1>
            <p class="text-xs md:text-sm text-slate-300 mt-1 max-w-2xl leading-relaxed">
                Kelola titik koordinat pelabuhan muat (origin), pelabuhan bongkar (destination), transit hub Selat Malaka, serta gudang kargo kopi & kakao internasional.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <button type="button" onclick="openAddLocationModal()"
                class="px-5 py-2.5 bg-sky-500 hover:bg-sky-400 text-slate-950 text-xs md:text-sm font-extrabold rounded-xl shadow-lg transition flex items-center gap-2 cursor-pointer border border-sky-300">
                <span class="text-base">📍</span>
                <span>+ Tambah Lokasi / Pelabuhan</span>
            </button>
        </div>
    </div>

    <!-- Quick Stats Bar -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
            <div class="text-[11px] font-bold text-slate-500 uppercase">Total Titik Terdaftar</div>
            <div class="text-2xl font-black text-slate-900 mt-0.5">{{ $locations->total() }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Node jaringan maritim global</div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
            <div class="text-[11px] font-bold text-slate-500 uppercase">Pelabuhan Laut (Port)</div>
            <div class="text-2xl font-black text-sky-600 mt-0.5">
                {{ $locations->where('type', 'PORT')->count() }}
            </div>
            <div class="text-[10px] text-slate-400 mt-0.5">Dermaga bongkar muat kapal</div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
            <div class="text-[11px] font-bold text-slate-500 uppercase">Gudang & Hub Kargo</div>
            <div class="text-2xl font-black text-amber-600 mt-0.5">
                {{ $locations->whereIn('type', ['WAREHOUSE', 'INLAND_HUB'])->count() }}
            </div>
            <div class="text-[10px] text-slate-400 mt-0.5">Penyimpanan kopi & kakao</div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
            <div class="text-[11px] font-bold text-slate-500 uppercase">Fitur Geocoding</div>
            <div class="text-2xl font-black text-emerald-600 mt-0.5">ONLINE</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Google Maps Geocoding & Satelit</div>
        </div>
    </div>

    <!-- Locations Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-sm font-black text-slate-900 uppercase tracking-wide">
                Daftar Titik Pelabuhan & Hub Logistik
            </h2>
            <span class="text-xs text-slate-500 font-mono">Halaman {{ $locations->currentPage() }} dari {{ $locations->lastPage() }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3.5">UN/LOCODE</th>
                        <th class="px-5 py-3.5">Nama Titik / Pelabuhan</th>
                        <th class="px-5 py-3.5">Kota & Negara</th>
                        <th class="px-5 py-3.5">Tipe Node</th>
                        <th class="px-5 py-3.5">Koordinat (Latitude, Longitude)</th>
                        <th class="px-5 py-3.5 text-right">Aktivitas Kargo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($locations as $loc)
                    <tr class="hover:bg-sky-50/40 transition">
                        <td class="px-5 py-3.5 font-mono font-bold text-slate-900">
                            <span class="px-2 py-0.5 rounded bg-slate-100 border border-slate-200 text-xs">
                                {{ $loc->code }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="font-extrabold text-slate-900">{{ $loc->name }}</div>
                            <div class="text-[11px] text-slate-400">Node ID: #{{ $loc->id }}</div>
                        </td>
                        <td class="px-5 py-3.5 text-xs text-slate-700">
                            <div class="font-bold">{{ $loc->city }}</div>
                            <div class="text-[11px] text-slate-500">{{ $loc->country }} ({{ $loc->country_code }})</div>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase
                                @if($loc->type === 'PORT') bg-sky-100 text-sky-800 border border-sky-300
                                @elseif($loc->type === 'WAREHOUSE') bg-amber-100 text-amber-800 border border-amber-300
                                @elseif($loc->type === 'AIRPORT') bg-purple-100 text-purple-800 border border-purple-300
                                @else bg-slate-100 text-slate-800 border border-slate-300 @endif">
                                {{ $loc->type }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 font-mono text-xs text-slate-600">
                            <span class="text-slate-800 font-semibold">{{ number_format($loc->latitude, 5) }}</span>, 
                            <span class="text-slate-800 font-semibold">{{ number_format($loc->longitude, 5) }}</span>
                        </td>
                        <td class="px-5 py-3.5 text-right text-xs font-bold text-slate-800">
                            <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 text-[11px]">
                                {{ $loc->outbound_shipments_count + $loc->inbound_shipments_count }} pengiriman
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center text-slate-400">
                            Belum ada lokasi yang terdaftar. Klik tombol "+ Tambah Lokasi / Pelabuhan" di atas untuk menambahkan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-200">
            {{ $locations->links() }}
        </div>
    </div>
</div>

<!-- ========================================================================================= -->
<!-- MODAL: TAMBAH LOKASI DENGAN PENCARIAN GOOGLE MAPS API & PETA INTERAKTIF PINPOINT         -->
<!-- ========================================================================================= -->
<div id="add-location-modal" class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs z-50 flex items-center justify-center p-3 sm:p-5 hidden overflow-y-auto">
    <div class="bg-white rounded-3xl max-w-4xl w-full p-6 sm:p-8 shadow-2xl border border-slate-200 my-8 max-h-[90vh] overflow-y-auto">
        
        <!-- Modal Header -->
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-sky-500/10 text-sky-600 flex items-center justify-center text-xl font-bold border border-sky-500/20">
                    📍
                </div>
                <div>
                    <h3 class="text-lg font-black text-slate-900 tracking-tight">
                        Tambah Titik Lokasi & Pelabuhan Maritim
                    </h3>
                    <p class="text-xs text-slate-500">
                        Cari nama tempat langsung menggunakan pencarian Google Maps atau klik pin di peta interaktif
                    </p>
                </div>
            </div>
            <button type="button" onclick="closeAddLocationModal()" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 hover:text-slate-900 text-lg font-black flex items-center justify-center transition">
                &times;
            </button>
        </div>

        <!-- Section 1: Google Maps Place Search Bar -->
        <div class="mt-4 p-4 bg-gradient-to-r from-sky-50 via-blue-50/50 to-indigo-50/30 border border-sky-200 rounded-2xl space-y-3">
            <div class="flex items-center justify-between">
                <label class="block text-xs font-black uppercase text-sky-900 tracking-wide flex items-center gap-1.5">
                    <span>🔍</span> Cari Nama Tempat / Pelabuhan (Google Maps Geocoding API)
                </label>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-sky-200 text-sky-800">
                    Real-Time Search
                </span>
            </div>

            <div class="relative">
                <div class="flex items-center gap-2">
                    <div class="relative w-full">
                        <input type="text" id="geocode-query"
                            placeholder="Ketik nama pelabuhan/kota, misal: Belawan Medan, Tanjung Priok, Santos, Rotterdam..."
                            class="w-full text-xs sm:text-sm rounded-xl border border-sky-300 px-3.5 py-2.5 pl-9 shadow-inner focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white"
                            onkeydown="if(event.key === 'Enter'){ event.preventDefault(); searchGeocode(); }">
                        <span class="absolute left-3 top-3 text-slate-400 text-sm">📍</span>
                    </div>

                    <button type="button" onclick="searchGeocode()" id="btn-search-place"
                        class="px-5 py-2.5 text-xs sm:text-sm font-black bg-sky-600 hover:bg-sky-500 text-white rounded-xl shadow-md transition flex items-center gap-1.5 shrink-0 cursor-pointer">
                        <span id="search-spinner" class="hidden animate-spin">⏳</span>
                        <span>Cari Lokasi</span>
                    </button>
                </div>

                <!-- Instant Search Suggestions Dropdown -->
                <div id="geocode-results" class="mt-2 text-xs space-y-1.5 hidden max-h-48 overflow-y-auto bg-white p-2 rounded-xl border border-sky-200 shadow-xl z-20"></div>
            </div>

            <!-- Quick Suggestion Pills -->
            <div class="flex flex-wrap items-center gap-1.5 text-[11px] pt-1">
                <span class="text-slate-500 font-bold text-[10px] uppercase">Contoh Cepat:</span>
                <button type="button" onclick="quickSearch('Belawan Medan')" class="px-2.5 py-0.5 rounded-lg bg-white border border-sky-200 text-sky-800 hover:bg-sky-100 font-bold transition">
                    📍 Belawan, Medan
                </button>
                <button type="button" onclick="quickSearch('Tanjung Priok Jakarta')" class="px-2.5 py-0.5 rounded-lg bg-white border border-sky-200 text-sky-800 hover:bg-sky-100 font-bold transition">
                    📍 Tanjung Priok, Jakarta
                </button>
                <button type="button" onclick="quickSearch('Port of Santos Brazil')" class="px-2.5 py-0.5 rounded-lg bg-white border border-sky-200 text-sky-800 hover:bg-sky-100 font-bold transition">
                    📍 Santos, Brasil
                </button>
                <button type="button" onclick="quickSearch('Port of Rotterdam')" class="px-2.5 py-0.5 rounded-lg bg-white border border-sky-200 text-sky-800 hover:bg-sky-100 font-bold transition">
                    📍 Rotterdam, Belanda
                </button>
            </div>
        </div>

        <!-- Section 2: Interactive Map with Pinpoint Picker -->
        <div class="mt-4 space-y-2">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-black uppercase text-slate-800">Peta Interaktif (Google Maps / Satelit)</span>
                    <span class="text-[10px] text-slate-500">Klik di peta atau geser pin untuk menyetel titik koordinat</span>
                </div>

                <!-- Layer Switcher Buttons -->
                <div class="flex items-center gap-1 text-[11px] bg-slate-100 p-1 rounded-lg border border-slate-200">
                    <button type="button" onclick="switchMapLayer('google_streets')" id="btn-layer-streets"
                            class="px-2 py-0.5 rounded text-[10px] font-bold bg-white text-slate-800 shadow-xs">
                        🗺️ Google Maps
                    </button>
                    <button type="button" onclick="switchMapLayer('google_satellite')" id="btn-layer-satellite"
                            class="px-2 py-0.5 rounded text-[10px] font-bold text-slate-600 hover:text-slate-900">
                        🛰️ Satelit
                    </button>
                    <button type="button" onclick="switchMapLayer('osm')" id="btn-layer-osm"
                            class="px-2 py-0.5 rounded text-[10px] font-bold text-slate-600 hover:text-slate-900">
                        🌐 OSM
                    </button>
                </div>
            </div>

            <div class="relative rounded-2xl overflow-hidden border border-slate-200 shadow-md">
                <div id="location-picker-map" style="height: 320px; width: 100%;" class="z-10"></div>
                
                <!-- Status Toast on Map -->
                <div id="map-selection-toast" class="absolute bottom-3 left-3 right-3 bg-slate-950/85 backdrop-blur-xs text-white p-2.5 rounded-xl text-xs flex items-center justify-between z-20 border border-slate-700/80">
                    <div class="flex items-center gap-2 truncate">
                        <span class="text-sky-400 font-bold text-sm">📍</span>
                        <span id="map-status-text" class="truncate font-semibold">Cari tempat di atas atau klik langsung di peta untuk memilih koordinat.</span>
                    </div>
                    <span id="map-coord-pill" class="text-[10px] font-mono px-2 py-0.5 rounded bg-sky-500/20 text-sky-300 border border-sky-500/30 shrink-0">
                        Belum Ada Titik
                    </span>
                </div>
            </div>
        </div>

        <!-- Section 3: Location Details Form (Auto-Populated) -->
        <form action="{{ route('locations.store') }}" method="POST" class="mt-5 space-y-4">
            @csrf
            
            <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-base">📋</span>
                    <span class="text-xs font-bold text-slate-700">Formulir Rincian Titik Lokasi (Otomatis Terisi dari Peta)</span>
                </div>
                <span class="text-[10px] font-black uppercase text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded">
                    Siap Disimpan
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Kode UN/LOCODE *</label>
                    <input type="text" name="code" id="loc-code" required placeholder="Contoh: IDBLW (Belawan) atau IDTPP"
                           class="w-full text-xs sm:text-sm font-mono font-bold rounded-xl border border-slate-300 px-3.5 py-2 uppercase focus:ring-2 focus:ring-sky-500">
                    <span class="text-[10px] text-slate-400">Kode pelabuhan standar internasional 5 huruf</span>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Tipe Titik Node *</label>
                    <select name="type" id="loc-type" required class="w-full text-xs sm:text-sm rounded-xl border border-slate-300 px-3.5 py-2 font-semibold">
                        <option value="PORT">PORT (Terminal Laut Ekspor/Impor)</option>
                        <option value="WAREHOUSE">WAREHOUSE (Gudang Pabean Kopi/Kakao)</option>
                        <option value="INLAND_HUB">INLAND_HUB (Pusat Konsolidasi Kargo Darat)</option>
                        <option value="AIRPORT">AIRPORT (Kargo Udara Cepat)</option>
                    </select>
                    <span class="text-[10px] text-slate-400">Kategori operasional kargo</span>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Nama Lengkap Lokasi / Pelabuhan *</label>
                <input type="text" name="name" id="loc-name" required placeholder="Contoh: Port of Belawan (Medan)"
                       class="w-full text-xs sm:text-sm font-bold rounded-xl border border-slate-300 px-3.5 py-2 focus:ring-2 focus:ring-sky-500">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Kota (City) *</label>
                    <input type="text" name="city" id="loc-city" required placeholder="Contoh: Medan"
                           class="w-full text-xs sm:text-sm rounded-xl border border-slate-300 px-3.5 py-2 focus:ring-2 focus:ring-sky-500">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Kode Negara (2 Huruf) *</label>
                    <input type="text" name="country_code" id="loc-country-code" maxlength="2" required placeholder="ID"
                           class="w-full text-xs sm:text-sm font-mono font-bold uppercase rounded-xl border border-slate-300 px-3.5 py-2 focus:ring-2 focus:ring-sky-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Negara (Country) *</label>
                <input type="text" name="country" id="loc-country" required placeholder="Contoh: Indonesia"
                       class="w-full text-xs sm:text-sm rounded-xl border border-slate-300 px-3.5 py-2 focus:ring-2 focus:ring-sky-500">
            </div>

            <!-- Latitude & Longitude Inputs -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-3 bg-slate-50 border border-slate-200 rounded-xl">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Latitude (Lintang) *</label>
                    <input type="number" step="0.0000001" name="latitude" id="loc-lat" required placeholder="3.7833000"
                           class="w-full text-xs sm:text-sm font-mono font-bold rounded-lg border border-slate-300 px-3 py-1.5 bg-white">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Longitude (Bujur) *</label>
                    <input type="number" step="0.0000001" name="longitude" id="loc-lon" required placeholder="98.6833000"
                           class="w-full text-xs sm:text-sm font-mono font-bold rounded-lg border border-slate-300 px-3 py-1.5 bg-white">
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeAddLocationModal()"
                    class="px-4 py-2 text-xs font-bold border border-slate-200 rounded-xl text-slate-600 hover:bg-slate-100 transition cursor-pointer">
                    Batal
                </button>
                <button type="submit"
                    class="px-6 py-2.5 text-xs sm:text-sm font-black bg-sky-600 hover:bg-sky-500 text-white rounded-xl shadow-lg transition flex items-center gap-2 cursor-pointer">
                    <span>💾</span>
                    <span>Simpan Titik Lokasi Baru</span>
                </button>
            </div>
        </form>

    </div>
</div>

@endsection

@section('scripts')
<script>
    let locationMap = null;
    let selectedMarker = null;
    let currentTileLayer = null;

    // Available tile layer URLs (Google Maps, Google Satellite, OSM)
    const TILE_LAYERS = {
        google_streets: {
            url: 'https://mt1.google.com/vt/lyrs=m&x={x}&y={y}&z={z}',
            attribution: '&copy; Google Maps'
        },
        google_satellite: {
            url: 'https://mt1.google.com/vt/lyrs=y&x={x}&y={y}&z={z}',
            attribution: '&copy; Google Satellite Imagery'
        },
        osm: {
            url: 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
            attribution: '&copy; OpenStreetMap contributors'
        }
    };

    /**
     * Open Modal and Initialize Leaflet Map
     */
    function openAddLocationModal() {
        const modal = document.getElementById('add-location-modal');
        modal.classList.remove('hidden');

        setTimeout(() => {
            initLocationMap();
        }, 150);
    }

    function closeAddLocationModal() {
        document.getElementById('add-location-modal').classList.add('hidden');
    }

    /**
     * Initialize Leaflet Map with Google Maps Tile Layer
     */
    function initLocationMap() {
        if (!locationMap) {
            // Default center at Indonesia / Southeast Asia maritime corridor
            locationMap = L.map('location-picker-map').setView([3.7833, 98.6833], 6);

            // Add Google Streets layer by default
            currentTileLayer = L.tileLayer(TILE_LAYERS.google_streets.url, {
                maxZoom: 20,
                attribution: TILE_LAYERS.google_streets.attribution
            }).addTo(locationMap);

            // Click on map to place or move marker
            locationMap.on('click', function(e) {
                setMarkerPosition(e.latlng.lat, e.latlng.lng, true);
            });
        }

        locationMap.invalidateSize();
    }

    /**
     * Switch Map Layer (Google Maps, Satelit, OpenStreetMap)
     */
    function switchMapLayer(layerKey) {
        if (!locationMap || !TILE_LAYERS[layerKey]) return;

        if (currentTileLayer) {
            locationMap.removeLayer(currentTileLayer);
        }

        currentTileLayer = L.tileLayer(TILE_LAYERS[layerKey].url, {
            maxZoom: 20,
            attribution: TILE_LAYERS[layerKey].attribution
        }).addTo(locationMap);

        // Update button active styles
        const btns = {
            'btn-layer-streets': layerKey === 'google_streets',
            'btn-layer-satellite': layerKey === 'google_satellite',
            'btn-layer-osm': layerKey === 'osm'
        };

        for (const [btnId, isActive] of Object.entries(btns)) {
            const btn = document.getElementById(btnId);
            if (btn) {
                if (isActive) {
                    btn.className = 'px-2 py-0.5 rounded text-[10px] font-bold bg-white text-slate-900 shadow-xs';
                } else {
                    btn.className = 'px-2 py-0.5 rounded text-[10px] font-bold text-slate-600 hover:text-slate-900';
                }
            }
        }
    }

    /**
     * Set Marker Position & Sync with Coordinates
     */
    function setMarkerPosition(lat, lng, doReverseGeocode = false, placeData = null) {
        if (!locationMap) return;

        const fixedLat = parseFloat(lat).toFixed(6);
        const fixedLng = parseFloat(lng).toFixed(6);

        // Update form coordinate inputs
        document.getElementById('loc-lat').value = fixedLat;
        document.getElementById('loc-lon').value = fixedLng;

        // Update map toast info
        document.getElementById('map-coord-pill').innerText = `${fixedLat}, ${fixedLng}`;

        if (!selectedMarker) {
            // Create draggable red marker
            selectedMarker = L.marker([lat, lng], { draggable: true }).addTo(locationMap);

            selectedMarker.on('dragend', function(e) {
                const pos = e.target.getLatLng();
                setMarkerPosition(pos.lat, pos.lng, true);
            });
        } else {
            selectedMarker.setLatLng([lat, lng]);
        }

        if (placeData) {
            document.getElementById('map-status-text').innerText = `✓ Terpilih: ${placeData.name || placeData.city} (${fixedLat}, ${fixedLng})`;
            selectedMarker.bindPopup(`
                <div class="font-sans text-xs">
                    <strong class="text-sky-700 text-sm">${placeData.name}</strong><br>
                    <span>${placeData.city}, ${placeData.country}</span><br>
                    <span class="font-mono text-slate-500">${fixedLat}, ${fixedLng}</span>
                </div>
            `).openPopup();
        } else if (doReverseGeocode) {
            document.getElementById('map-status-text').innerText = `Mengidentifikasi alamat titik (${fixedLat}, ${fixedLng})...`;
            performReverseGeocode(lat, lng);
        }
    }

    /**
     * Reverse Geocode coordinates to address
     */
    async function performReverseGeocode(lat, lng) {
        try {
            const res = await fetch(`/locations/reverse-geocode?lat=${lat}&lng=${lng}`);
            const json = await res.json();

            if (json.data) {
                const d = json.data;
                document.getElementById('map-status-text').innerText = `✓ Titik: ${d.name} (${d.city}, ${d.country})`;
                
                // If form fields are empty or user is picking from scratch, fill them
                if (!document.getElementById('loc-name').value || document.getElementById('loc-name').value.startsWith('Port of') || document.getElementById('loc-name').value.startsWith('Titik')) {
                    document.getElementById('loc-name').value = d.name;
                }
                document.getElementById('loc-city').value = d.city;
                document.getElementById('loc-country').value = d.country;
                document.getElementById('loc-country-code').value = d.country_code;

                if (!document.getElementById('loc-code').value && d.suggested_code) {
                    document.getElementById('loc-code').value = d.suggested_code;
                }

                if (selectedMarker) {
                    selectedMarker.bindPopup(`
                        <div class="font-sans text-xs">
                            <strong class="text-sky-700 text-sm">${d.name}</strong><br>
                            <span>${d.city}, ${d.country}</span><br>
                            <span class="font-mono text-slate-500">${parseFloat(lat).toFixed(5)}, ${parseFloat(lng).toFixed(5)}</span>
                        </div>
                    `).openPopup();
                }
            }
        } catch (e) {
            console.warn('Reverse geocode failed:', e);
            document.getElementById('map-status-text').innerText = `Titik koordinat: ${parseFloat(lat).toFixed(5)}, ${parseFloat(lng).toFixed(5)}`;
        }
    }

    /**
     * Quick search button trigger
     */
    function quickSearch(placeName) {
        document.getElementById('geocode-query').value = placeName;
        searchGeocode();
    }

    /**
     * Search Geocode (Nominatim / Google Maps Places Geocoding)
     */
    async function searchGeocode() {
        const query = document.getElementById('geocode-query').value.trim();
        if (!query) return;

        const resultsContainer = document.getElementById('geocode-results');
        const spinner = document.getElementById('search-spinner');

        resultsContainer.innerHTML = '<div class="p-2 text-slate-500 flex items-center gap-2"><span class="animate-spin">⏳</span> Mencari lokasi pelabuhan / kota di Google Maps & Geodatabase...</div>';
        resultsContainer.classList.remove('hidden');
        if (spinner) spinner.classList.remove('hidden');

        try {
            const res = await fetch('/locations/geocode-lookup?q=' + encodeURIComponent(query));
            const json = await res.json();

            if (json.data && json.data.length > 0) {
                resultsContainer.innerHTML = '';
                
                json.data.forEach((item, index) => {
                    const row = document.createElement('div');
                    row.className = 'p-2.5 bg-slate-50 hover:bg-sky-50 border border-slate-200 hover:border-sky-300 rounded-xl cursor-pointer transition flex items-center justify-between gap-3';
                    
                    row.innerHTML = `
                        <div class="flex items-start gap-2.5 truncate">
                            <span class="text-lg">📍</span>
                            <div class="truncate">
                                <div class="font-black text-slate-900 text-xs truncate">${item.name || item.city}</div>
                                <div class="text-[11px] text-slate-500 truncate">${item.display_name}</div>
                                <div class="text-[10px] text-sky-700 font-semibold mt-0.5">
                                    ${item.city}, ${item.country} (${item.country_code}) &bull; UN/LOCODE: <strong>${item.suggested_code || ''}</strong>
                                </div>
                            </div>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="px-2 py-1 text-[10px] font-black bg-sky-600 text-white rounded-lg shadow-xs hover:bg-sky-500">
                                Pilih & Peta &rarr;
                            </span>
                            <div class="text-[9px] font-mono text-slate-400 mt-1">${parseFloat(item.latitude).toFixed(4)}, ${parseFloat(item.longitude).toFixed(4)}</div>
                        </div>
                    `;

                    row.onclick = () => {
                        applySelectedPlace(item);
                        resultsContainer.classList.add('hidden');
                    };

                    resultsContainer.appendChild(row);
                });

                // Auto-apply first result if only 1 match or if exact match
                if (json.data.length === 1) {
                    applySelectedPlace(json.data[0]);
                }
            } else {
                resultsContainer.innerHTML = `
                    <div class="p-3 text-slate-500 text-center">
                        <span>Tidak ditemukan hasil untuk "<strong>${query}</strong>".</span><br>
                        <span class="text-[11px] text-slate-400">Silakan periksa ejaan atau klik langsung di peta untuk memilih koordinat.</span>
                    </div>
                `;
            }
        } catch (e) {
            console.error('Lookup failed:', e);
            resultsContainer.innerHTML = '<div class="p-2 text-rose-500">Gagal mencari lokasi. Silakan klik langsung pada peta.</div>';
        } finally {
            if (spinner) spinner.classList.add('hidden');
        }
    }

    /**
     * Apply Selected Place to Form and Fly Map
     */
    function applySelectedPlace(place) {
        // Auto-fill form inputs
        document.getElementById('loc-name').value = place.name || `Port of ${place.city}`;
        document.getElementById('loc-city').value = place.city || '';
        document.getElementById('loc-country').value = place.country || '';
        document.getElementById('loc-country-code').value = place.country_code || 'ID';
        document.getElementById('loc-code').value = place.suggested_code || '';
        if (place.type) {
            document.getElementById('loc-type').value = place.type;
        }

        // Fly map smoothly to selected location
        if (locationMap) {
            locationMap.flyTo([place.latitude, place.longitude], 13, {
                duration: 1.2
            });
        }

        // Position marker and update coordinates
        setMarkerPosition(place.latitude, place.longitude, false, place);
    }
</script>
@endsection
