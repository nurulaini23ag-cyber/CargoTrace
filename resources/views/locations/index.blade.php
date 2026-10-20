@extends('layouts.app')

@section('title', 'Locations & Ports Directory')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Origin & Destination Ports</h1>
            <p class="text-sm text-slate-500">Manage geographic nodes, ocean terminals, and logistics hubs</p>
        </div>
        <button type="button" onclick="document.getElementById('add-location-modal').classList.toggle('hidden')"
            class="px-4 py-2 bg-sky-600 hover:bg-sky-500 text-white text-sm font-semibold rounded-lg shadow-sm transition">
            + Add Location
        </button>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500 border-b border-slate-200">
                <tr>
                    <th class="px-5 py-3.5">Code</th>
                    <th class="px-5 py-3.5">Location Name</th>
                    <th class="px-5 py-3.5">City & Country</th>
                    <th class="px-5 py-3.5">Type</th>
                    <th class="px-5 py-3.5">Coordinates (Lat, Lon)</th>
                    <th class="px-5 py-3.5 text-right">Traffic</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($locations as $loc)
                <tr class="hover:bg-slate-50/60 transition">
                    <td class="px-5 py-3.5 font-mono font-bold text-slate-900">{{ $loc->code }}</td>
                    <td class="px-5 py-3.5 font-semibold text-slate-800">{{ $loc->name }}</td>
                    <td class="px-5 py-3.5 text-xs text-slate-600">{{ $loc->city }}, {{ $loc->country }} ({{ $loc->country_code }})</td>
                    <td class="px-5 py-3.5">
                        <span class="px-2 py-0.5 rounded text-[11px] font-bold uppercase bg-slate-100 text-slate-700">
                            {{ $loc->type }}
                        </span>
                    </td>
                    <td class="px-5 py-3.5 font-mono text-xs text-slate-500">
                        {{ number_format($loc->latitude, 5) }}, {{ number_format($loc->longitude, 5) }}
                    </td>
                    <td class="px-5 py-3.5 text-right text-xs font-semibold text-slate-700">
                        {{ $loc->outbound_shipments_count + $loc->inbound_shipments_count }} shipments
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-5 py-8 text-center text-slate-400">No locations registered.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 border-t border-slate-200">
            {{ $locations->links() }}
        </div>
    </div>
</div>

<!-- Modal: Add Location -->
<div id="add-location-modal" class="fixed inset-0 bg-slate-900/60 z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-xl max-w-lg w-full p-6 shadow-2xl border border-slate-200">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="text-base font-bold text-slate-900">Add Location / Port</h3>
            <button type="button" onclick="document.getElementById('add-location-modal').classList.add('hidden')" class="text-slate-400 text-lg">&times;</button>
        </div>

        <!-- External API Geocode Search Helper -->
        <div class="mt-3 p-3 bg-sky-50/70 border border-sky-100 rounded-lg">
            <label class="block text-[11px] font-bold uppercase text-sky-800 mb-1">Nominatim Geocoding Auto-Fill</label>
            <div class="flex items-center space-x-2">
                <input type="text" id="geocode-query" placeholder="Type city or port (e.g. Hamburg Port, Germany)" class="w-full text-xs rounded border border-sky-300 px-2.5 py-1.5">
                <button type="button" onclick="searchGeocode()" class="px-3 py-1.5 text-xs font-semibold bg-sky-600 text-white rounded hover:bg-sky-500 whitespace-nowrap">
                    Search
                </button>
            </div>
            <div id="geocode-results" class="mt-2 text-xs space-y-1 hidden"></div>
        </div>

        <form action="{{ route('locations.store') }}" method="POST" class="mt-4 space-y-3">
            @csrf
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-700 mb-1">UN/LOCODE Code *</label>
                    <input type="text" name="code" id="loc-code" required placeholder="e.g. DEHAM" class="w-full text-xs rounded border border-slate-300 px-3 py-2">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-700 mb-1">Location Type *</label>
                    <select name="type" required class="w-full text-xs rounded border border-slate-300 px-3 py-2">
                        <option value="PORT">PORT (Sea Terminal)</option>
                        <option value="AIRPORT">AIRPORT</option>
                        <option value="WAREHOUSE">WAREHOUSE</option>
                        <option value="INLAND_HUB">INLAND_HUB</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase text-slate-700 mb-1">Full Name *</label>
                <input type="text" name="name" id="loc-name" required placeholder="e.g. Port of Hamburg" class="w-full text-xs rounded border border-slate-300 px-3 py-2">
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div class="col-span-2">
                    <label class="block text-xs font-semibold uppercase text-slate-700 mb-1">City *</label>
                    <input type="text" name="city" id="loc-city" required placeholder="Hamburg" class="w-full text-xs rounded border border-slate-300 px-3 py-2">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-700 mb-1">Country Code *</label>
                    <input type="text" name="country_code" id="loc-country-code" maxlength="2" required placeholder="DE" class="w-full text-xs rounded border border-slate-300 px-3 py-2">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase text-slate-700 mb-1">Country *</label>
                <input type="text" name="country" id="loc-country" required placeholder="Germany" class="w-full text-xs rounded border border-slate-300 px-3 py-2">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-700 mb-1">Latitude *</label>
                    <input type="number" step="0.0000001" name="latitude" id="loc-lat" required placeholder="53.5350" class="w-full text-xs rounded border border-slate-300 px-3 py-2">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-700 mb-1">Longitude *</label>
                    <input type="number" step="0.0000001" name="longitude" id="loc-lon" required placeholder="9.9800" class="w-full text-xs rounded border border-slate-300 px-3 py-2">
                </div>
            </div>

            <div class="flex items-center justify-end space-x-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('add-location-modal').classList.add('hidden')" class="px-3 py-1.5 text-xs border rounded text-slate-600">Cancel</button>
                <button type="submit" class="px-4 py-1.5 text-xs font-semibold bg-sky-600 text-white rounded hover:bg-sky-500">Save Location</button>
            </div>
        </form>
    </div>
</div>

<script>
    async function searchGeocode() {
        const query = document.getElementById('geocode-query').value;
        if (!query) return;

        const resultsContainer = document.getElementById('geocode-results');
        resultsContainer.innerHTML = '<span class="text-slate-400">Querying OpenStreetMap Nominatim...</span>';
        resultsContainer.classList.remove('hidden');

        try {
            const res = await fetch('/locations/geocode-lookup?q=' + encodeURIComponent(query));
            const json = await res.json();

            if (json.data && json.data.length > 0) {
                resultsContainer.innerHTML = '';
                json.data.forEach(item => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'block w-full text-left p-1.5 bg-white border border-slate-200 rounded text-slate-700 hover:bg-sky-50 text-[11px] truncate';
                    btn.innerText = `${item.display_name} (${item.latitude}, ${item.longitude})`;
                    btn.onclick = () => {
                        document.getElementById('loc-lat').value = item.latitude;
                        document.getElementById('loc-lon').value = item.longitude;
                        resultsContainer.classList.add('hidden');
                    };
                    resultsContainer.appendChild(btn);
                });
            } else {
                resultsContainer.innerHTML = '<span class="text-rose-500">No coordinates matched.</span>';
            }
        } catch (e) {
            resultsContainer.innerHTML = '<span class="text-rose-500">Lookup failed. Enter manually.</span>';
        }
    }
</script>
@endsection
