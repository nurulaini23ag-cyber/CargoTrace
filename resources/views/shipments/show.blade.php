@extends('layouts.app')

@section('title', 'Shipment #' . $shipment->shipment_number)

@section('content')
<div class="space-y-6">

    <!-- Top Breadcrumb & Status Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('shipments.index') }}" class="hover:underline">Shipments</a>
                <span>&rsaquo;</span>
                <span class="font-semibold text-slate-800">#{{ $shipment->shipment_number }}</span>
            </div>
            <div class="flex items-center space-x-3">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                    {{ $shipment->shipment_number }}
                </h1>
                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider
                    @if($shipment->current_status === 'DELIVERED') bg-emerald-100 text-emerald-800 border border-emerald-300
                    @elseif($shipment->current_status === 'IN_TRANSIT') bg-blue-100 text-blue-800 border border-blue-300
                    @elseif($shipment->current_status === 'PICKED_UP') bg-indigo-100 text-indigo-800 border border-indigo-300
                    @elseif($shipment->current_status === 'BOOKED') bg-purple-100 text-purple-800 border border-purple-300
                    @elseif($shipment->current_status === 'CANCELLED') bg-rose-100 text-rose-800 border border-rose-300
                    @else bg-slate-100 text-slate-800 border border-slate-300 @endif">
                    {{ $shipment->current_status }}
                </span>
            </div>
        </div>

        <!-- Action Buttons (Admin & Operator) -->
        <div class="flex flex-wrap items-center gap-2">
            @if(Auth::user()->isAdmin() || Auth::user()->isOperator())
                <a href="{{ route('shipments.edit', $shipment->id) }}" class="px-3.5 py-1.5 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-300 rounded-lg shadow-sm transition">
                    Edit Details
                </a>

                <!-- Quick Transition Dropdown / Form -->
                @if(!$shipment->isTerminal())
                <div class="relative inline-block text-left" id="transition-dropdown-container">
                    @php
                        $nextStatusMap = [
                            'CREATED' => ['BOOKED', 'CANCELLED'],
                            'BOOKED' => ['PICKED_UP', 'CANCELLED'],
                            'PICKED_UP' => ['IN_TRANSIT', 'CANCELLED'],
                            'IN_TRANSIT' => ['DELIVERED'],
                        ];
                        $nextOptions = $nextStatusMap[$shipment->current_status] ?? [];
                    @endphp

                    @foreach($nextOptions as $opt)
                    <form action="{{ route('shipments.transition', $shipment->id) }}" method="POST" class="inline" onsubmit="return confirm('Transition status to {{ $opt }}?')">
                        @csrf
                        <input type="hidden" name="target_status" value="{{ $opt }}">
                        <button type="submit" class="px-3 py-1.5 text-xs font-semibold rounded-lg shadow-sm transition
                            @if($opt === 'DELIVERED') bg-emerald-600 hover:bg-emerald-500 text-white
                            @elseif($opt === 'CANCELLED') bg-rose-600 hover:bg-rose-500 text-white
                            @else bg-sky-600 hover:bg-sky-500 text-white @endif">
                            &rarr; Advance to {{ $opt }}
                        </button>
                    </form>
                    @endforeach
                </div>
                @endif
            @endif
        </div>
    </div>

    <!-- Status Lifecycle State Machine Progress Bar -->
    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
        <div class="flex items-center justify-between text-xs mb-3">
            <span class="font-bold uppercase tracking-wider text-slate-500 flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-[#38251D]"></span>
                Maritime Lifecycle State Machine Flow
            </span>
            <span class="text-[11px] text-slate-400 font-medium">Linear Deterministic Progression</span>
        </div>
        @php
            $statuses = ['CREATED', 'BOOKED', 'PICKED_UP', 'IN_TRANSIT', 'DELIVERED'];
            $currentIndex = array_search($shipment->current_status, $statuses);
            if ($shipment->current_status === 'CANCELLED') $currentIndex = -1;
        @endphp

        <div class="grid grid-cols-5 gap-2 text-center text-xs">
            @foreach($statuses as $idx => $st)
                @php
                    $isPassed = $currentIndex !== false && $currentIndex >= $idx;
                    $isCurrent = $shipment->current_status === $st;
                @endphp
                <div class="flex flex-col items-center">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs mb-1.5 transition
                        @if($isCurrent) bg-[#38251D] text-white ring-4 ring-[#E8D7B9] shadow
                        @elseif($isPassed) bg-emerald-600 text-white
                        @else bg-slate-100 text-slate-400 border border-slate-200 @endif">
                        @if($isPassed && !$isCurrent)
                            &check;
                        @else
                            {{ $idx + 1 }}
                        @endif
                    </div>
                    <span class="font-bold {{ $isCurrent ? 'text-[#38251D]' : ($isPassed ? 'text-slate-800' : 'text-slate-400') }}">
                        {{ $st }}
                    </span>
                    <span class="text-[10px] text-slate-400 mt-0.5 hidden sm:inline">
                        @if($st === 'CREATED') Booking Placed
                        @elseif($st === 'BOOKED') Slot Allocated
                        @elseif($st === 'PICKED_UP') CFS Gate-In
                        @elseif($st === 'IN_TRANSIT') Ocean Voyage
                        @elseif($st === 'DELIVERED') Consignee Release
                        @endif
                    </span>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Delay Detection Banner (Slide 08) -->
    @if($shipment->is_delayed)
    <div class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded-r-xl shadow-sm">
        <div class="flex items-start justify-between">
            <div class="flex items-start space-x-3">
                <svg class="w-6 h-6 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <div class="flex items-center space-x-2">
                        <span class="font-bold text-amber-900 text-sm">Schedule Delay Detected</span>
                        <span class="px-2 py-0.5 rounded text-xs font-extrabold uppercase
                            @if($shipment->delay_severity === 'HIGH') bg-red-600 text-white
                            @elseif($shipment->delay_severity === 'MEDIUM') bg-amber-600 text-white
                            @else bg-yellow-500 text-slate-900 @endif">
                            Severity: {{ $shipment->delay_severity }} (+{{ $shipment->delay_hours }} Hours)
                        </span>
                    </div>
                    <p class="text-xs text-amber-800 mt-1 font-medium">
                        {{ $shipment->delay_reason }}
                    </p>
                    <div class="mt-2 text-[11px] text-amber-700/80">
                        Scheduled Arrival: <strong>{{ $shipment->expected_arrival->format('Y-m-d H:i') }}</strong> | Current ETA: <strong>{{ $shipment->estimated_arrival?->format('Y-m-d H:i') }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Main Grid: Left Details & Map | Right Timeline & Live Weather -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Left Column: Specs & Interactive Leaflet Map (7 cols) -->
        <div class="lg:col-span-7 space-y-6">

            <!-- Route & Consignment Info Card -->
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                <h2 class="text-base font-bold text-slate-900 mb-4 pb-2 border-b border-slate-100">Consignment Specifications</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-xs">
                    <div>
                        <span class="text-slate-400 block uppercase font-medium">Origin</span>
                        <span class="font-bold text-slate-800 text-sm">{{ $shipment->origin->name }}</span>
                        <span class="text-slate-500 block">{{ $shipment->origin->city }}, {{ $shipment->origin->country }} ({{ $shipment->origin->code }})</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block uppercase font-medium">Destination</span>
                        <span class="font-bold text-slate-800 text-sm">{{ $shipment->destination->name }}</span>
                        <span class="text-slate-500 block">{{ $shipment->destination->city }}, {{ $shipment->destination->country }} ({{ $shipment->destination->code }})</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block uppercase font-medium">Carrier</span>
                        <span class="font-bold text-slate-800 text-sm">{{ $shipment->carrier->name }}</span>
                        <span class="text-slate-500 block">Code: {{ $shipment->carrier->code }} ({{ $shipment->carrier->mode }})</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block uppercase font-medium">Customer / Consignee</span>
                        <span class="font-bold text-slate-800">{{ $shipment->customer->name }}</span>
                        <span class="text-slate-500 block">{{ $shipment->customer->company_name ?? 'Individual' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block uppercase font-medium">Cargo & Weight</span>
                        <span class="font-bold text-slate-800">{{ $shipment->cargo_type }}</span>
                        <span class="text-slate-500 block">{{ number_format($shipment->weight, 2) }} kg &bull; {{ number_format($shipment->volume, 2) }} m³</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block uppercase font-medium">Departure & Arrival</span>
                        <span class="font-bold text-slate-800">Dep: {{ $shipment->expected_departure->format('M d, Y') }}</span>
                        <span class="text-slate-500 block">Arr: {{ $shipment->expected_arrival->format('M d, Y') }}</span>
                    </div>
                </div>

                @if($shipment->special_instructions)
                <div class="mt-4 p-2.5 bg-slate-50 rounded-lg text-xs text-slate-600 border border-slate-100">
                    <span class="font-semibold text-slate-700">Special Instructions:</span> {{ $shipment->special_instructions }}
                </div>
                @endif
            </div>

            <!-- Interactive Live Cargo Tracking Map & Current Position (Slide 09) -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-base font-bold text-slate-900">Geographic Maritime Tracking Map</h2>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-ping"></span> Live Position Active
                            </span>
                        </div>
                        <p class="text-xs text-slate-500">Real-time sea freight route, waypoints, and current cargo position</p>
                    </div>
                    <span class="text-[11px] font-semibold text-slate-600 bg-white border border-slate-200 px-2.5 py-1 rounded shadow-xs">
                        Leaflet + OSM Marine
                    </span>
                </div>

                <!-- Prominent Current Position HUD Card ("Di Mana Barang Itu Sudah Sampai") -->
                <div class="p-4 bg-gradient-to-r from-[#38251D] via-[#4E342E] to-[#38251D] text-white">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                        <div>
                            <div class="text-[11px] uppercase tracking-wider text-[#C49A55] font-bold flex items-center gap-1.5">
                                <span>📍</span> Posisi Kargo Saat Ini &bull; Current Cargo Position
                            </div>
                            <div class="text-base font-extrabold text-white mt-0.5 flex items-center gap-2">
                                <span>{{ $currentPos['location_name'] }}</span>
                                @if($currentPos['is_arrived'])
                                    <span class="px-2 py-0.5 bg-emerald-500 text-white text-[10px] rounded font-bold">ARRIVED</span>
                                @else
                                    <span class="px-2 py-0.5 bg-[#C49A55] text-slate-900 text-[10px] rounded font-bold">EN ROUTE</span>
                                @endif
                            </div>
                            <div class="text-xs text-[#E8D7B9] mt-1 flex flex-wrap items-center gap-3">
                                <span><strong>Koordinat:</strong> {{ number_format($currentPos['latitude'], 4) }}°, {{ number_format($currentPos['longitude'], 4) }}°</span>
                                <span>&bull;</span>
                                <span><strong>Pembaruan Terakhir:</strong> {{ $currentPos['last_updated'] }}</span>
                                <span>&bull;</span>
                                <span><strong>Vessel:</strong> {{ $shipment->vessel_name ?? $shipment->vessel_carrier ?? 'MSC Amsterdam' }}</span>
                            </div>
                        </div>

                        <!-- Progress Bar & Nautical Weather Pill -->
                        <div class="bg-black/20 p-2.5 rounded-lg border border-white/10 min-w-[210px]">
                            <div class="flex justify-between text-[11px] font-bold mb-1">
                                <span class="text-[#E8D7B9]">Voyage Progress</span>
                                <span class="text-white">{{ $currentPos['progress_percent'] }}%</span>
                            </div>
                            <div class="w-full bg-white/20 h-2 rounded-full overflow-hidden">
                                <div class="bg-gradient-to-r from-[#C49A55] to-emerald-400 h-2 rounded-full transition-all duration-500" style="width: {{ $currentPos['progress_percent'] }}%"></div>
                            </div>
                            <div class="mt-2 text-[10px] text-white/80 flex items-center justify-between">
                                <span>{{ $shipment->origin->city }}</span>
                                <span class="text-[#C49A55] font-mono">⚓</span>
                                <span>{{ $shipment->destination->city }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Shopee-Style Live Vessel Sailing Controls Bar -->
                <div class="px-4 py-2.5 bg-slate-900 text-white flex flex-wrap items-center justify-between gap-2.5 text-xs border-b border-slate-800">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-[10px] font-black uppercase tracking-wider text-[#C49A55] flex items-center gap-1">
                            <span>⚡</span> LIVE SAILING SIMULATOR
                        </span>
                        <div class="flex items-center gap-1 bg-slate-800 rounded-lg p-0.5 border border-slate-700">
                            <button id="btn-play-pause" type="button" class="px-2.5 py-1 rounded bg-[#C49A55] text-slate-900 font-extrabold text-[11px] hover:bg-[#b38944] transition flex items-center gap-1 shadow-xs">
                                <span id="play-icon">⏸️</span> <span id="play-text">Jeda</span>
                            </button>
                            <button id="btn-restart" type="button" class="px-2 py-1 rounded text-slate-300 hover:text-white hover:bg-slate-700 text-[11px] font-bold transition flex items-center gap-1" title="Ulangi pelayaran dari pelabuhan asal">
                                <span>🔄</span> Ulangi
                            </button>
                            <button id="btn-jump-now" type="button" class="px-2 py-1 rounded text-slate-300 hover:text-white hover:bg-slate-700 text-[11px] font-bold transition flex items-center gap-1" title="Lompat ke posisi kargo saat ini">
                                <span>🎯</span> Posisi Terkini
                            </button>
                        </div>

                        <!-- Speed selector -->
                        <div class="flex items-center gap-1 bg-slate-800 rounded-lg p-0.5 border border-slate-700 text-[10px]">
                            <span class="text-slate-400 px-1 font-bold">Speed:</span>
                            <button type="button" onclick="setSailingSpeed(1)" class="speed-btn px-1.5 py-0.5 rounded font-bold transition bg-[#C49A55] text-slate-900" id="speed-1">1x</button>
                            <button type="button" onclick="setSailingSpeed(2)" class="speed-btn px-1.5 py-0.5 rounded font-bold transition text-slate-300 hover:text-white" id="speed-2">2x</button>
                            <button type="button" onclick="setSailingSpeed(4)" class="speed-btn px-1.5 py-0.5 rounded font-bold transition text-slate-300 hover:text-white" id="speed-4">4x</button>
                        </div>
                    </div>

                    <!-- World Map Theme Selector -->
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] text-slate-400 font-bold uppercase hidden sm:inline">Peta Dunia:</span>
                        <select id="world-map-style" onchange="changeMapTheme(this.value)" class="bg-slate-800 text-white border border-slate-700 rounded-lg px-2.5 py-1 text-[11px] font-semibold focus:outline-none focus:ring-1 focus:ring-[#C49A55] cursor-pointer">
                            <option value="voyager" selected>🗺️ Peta Dunia Voyager (Default)</option>
                            <option value="dark">🌙 Dark Maritime Chart</option>
                            <option value="osm">🧭 OpenStreetMap Classic</option>
                        </select>
                    </div>
                </div>

                <!-- Leaflet Map Canvas with Telemetry Overlay -->
                <div class="relative w-full">
                    <!-- Live Telemetry HUD Bar on Top of Map -->
                    <div class="absolute top-3 left-14 z-20 bg-slate-900/90 backdrop-blur-md text-white px-3 py-1.5 rounded-xl border border-[#C49A55]/40 shadow-lg text-[11px] flex items-center gap-3 pointer-events-none">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                            <span class="font-extrabold text-[#E8D7B9]">KAPAL BERLAYAR (LIVE):</span>
                            <span class="font-mono text-white font-bold" id="telemetry-coords">Lat: {{ number_format($currentPos['latitude'], 4) }}°, Lon: {{ number_format($currentPos['longitude'], 4) }}°</span>
                        </div>
                        <span class="text-slate-500">|</span>
                        <span class="text-[#C49A55] font-bold" id="telemetry-speed">Kecepatan: 19.4 Knots</span>
                        <span class="text-slate-500 hidden md:inline">|</span>
                        <span class="text-slate-300 hidden md:inline">Status: <strong id="telemetry-leg">High Seas Transit</strong></span>
                    </div>

                    <div id="shipment-map" class="h-[520px] w-full z-10"></div>
                </div>

                <div class="p-3 bg-slate-50 text-[11px] text-slate-600 border-t border-slate-100 flex flex-wrap items-center justify-between gap-2">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500 inline-block"></span> Origin Port</span>
                        <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-amber-500 inline-block"></span> <strong>Current Position (Di mana barang sampai)</strong></span>
                        <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-sky-500 inline-block"></span> Waypoint</span>
                        <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-rose-500 inline-block"></span> Destination Port</span>
                    </div>
                    <div>
                        &copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" class="underline text-slate-500">OpenStreetMap</a> contributors
                    </div>
                </div>
            </div>

            <!-- Items Manifest Table -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Cargo Manifest & Commodity Items</h2>
                        <p class="text-xs text-slate-500">Official ocean freight manifest, product varieties, grades, and quality specs</p>
                    </div>
                    <span class="text-xs font-bold text-slate-500 bg-slate-100 px-2.5 py-1 rounded-full">
                        {{ $shipment->items->count() }} {{ Str::plural('Lot', $shipment->items->count()) }}
                    </span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-slate-50 uppercase text-[10px] tracking-wider text-slate-500 border-b border-slate-100">
                            <tr>
                                <th class="px-4 py-3">Commodity & Variety</th>
                                <th class="px-4 py-3">Grade & HS Code</th>
                                <th class="px-4 py-3">Packaging & Bags</th>
                                <th class="px-4 py-3">Net Weight</th>
                                <th class="px-4 py-3">Moisture & Temp</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($shipment->items as $item)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-1.5 mb-0.5">
                                        @if($item->isCocoa() || str_contains(strtolower($item->product_name ?? ''), 'cocoa'))
                                            <span class="px-1.5 py-0.5 bg-[#795548]/10 text-[#795548] border border-[#795548]/20 rounded font-black text-[10px] uppercase">
                                                🍫 COCOA
                                            </span>
                                        @else
                                            <span class="px-1.5 py-0.5 bg-[#38251D]/10 text-[#38251D] border border-[#38251D]/20 rounded font-black text-[10px] uppercase">
                                                ☕ COFFEE
                                            </span>
                                        @endif
                                        <span class="font-bold text-slate-900 text-xs">{{ $item->product_name ?? $item->item_description }}</span>
                                    </div>
                                    <div class="text-[11px] text-slate-500">
                                        Variety: {{ $item->variety ?? 'Standard Regional Blend' }}
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-slate-800">{{ $item->grade ?? 'Export Standard' }}</div>
                                    <div class="text-[11px] text-slate-400 font-mono">HS: {{ $item->hs_code ?? 'N/A' }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-slate-800">{{ $item->package_count ?? $item->quantity }} pkgs</div>
                                    <div class="text-[11px] text-slate-400">{{ str_replace('_', ' ', $item->package_type ?? 'JUTE_BAG_60KG') }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-bold text-slate-900">{{ number_format($item->net_weight ?: $item->weight_kg, 2) }} kg</div>
                                    <div class="text-[11px] text-slate-400">{{ $item->volume_cbm ?? '--' }} m³</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-1 font-semibold text-slate-800">
                                        <span>💧 {{ $item->moisture_content ? $item->moisture_content . '%' : 'N/A' }}</span>
                                        @if($item->moisture_content && $item->moisture_content > 12.5)
                                            <span class="text-[9px] px-1 py-0.2 bg-amber-100 text-amber-800 rounded font-bold">High</span>
                                        @elseif($item->moisture_content)
                                            <span class="text-[9px] px-1 py-0.2 bg-emerald-100 text-emerald-800 rounded font-bold">Optimal</span>
                                        @endif
                                    </div>
                                    <div class="text-[11px] text-slate-400">
                                        Target: {{ $item->temperature_target ? $item->temperature_target . '°C' : 'Ambient' }}
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-4 py-4 text-center text-slate-400">No detailed commodity items recorded.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Right Column: Live Weather & Timeline & Exceptions (5 cols) -->
        <div class="lg:col-span-5 space-y-6">

            <!-- Real-Time External API Weather Context (Slide 09) -->
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 00-9.78 2.096A4.001 4.001 0 003 15z" />
                        </svg>
                        <h2 class="text-sm font-bold text-slate-900">Live Weather Context</h2>
                    </div>
                    <span class="text-[10px] font-semibold bg-sky-100 text-sky-800 px-2 py-0.5 rounded-full">Open-Meteo API</span>
                </div>
                <div class="grid grid-cols-3 gap-2 text-center text-xs">
                    <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-100">
                        <div class="text-slate-400 text-[10px] uppercase font-semibold">Temperature</div>
                        <div class="text-lg font-black text-slate-800 mt-0.5">{{ $weather['temperature'] ?? '--' }}°C</div>
                    </div>
                    <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-100">
                        <div class="text-slate-400 text-[10px] uppercase font-semibold">Wind Speed</div>
                        <div class="text-lg font-black text-slate-800 mt-0.5">{{ $weather['wind_speed'] ?? '--' }} km/h</div>
                    </div>
                    <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-100">
                        <div class="text-slate-400 text-[10px] uppercase font-semibold">Condition</div>
                        <div class="text-xs font-bold text-slate-800 mt-1 line-clamp-1">{{ $weather['condition'] ?? 'Clear' }}</div>
                    </div>
                </div>
                <div class="mt-2 text-[10px] text-slate-400 italic">
                    {{ $weather['disclaimer'] ?? 'Environmental conditions provided for operational situational awareness only.' }}
                </div>
            </div>

            <!-- Exception Incidents Section (Slide 08) -->
            <div id="exceptions" class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm space-y-4">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <div class="flex items-center space-x-2">
                        <h2 class="text-base font-bold text-slate-900">Incident & Exception Logs</h2>
                        @if($shipment->openExceptions->count() > 0)
                            <span class="px-2 py-0.2 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">
                                {{ $shipment->openExceptions->count() }} Open
                            </span>
                        @endif
                    </div>
                    @if(Auth::user()->isAdmin() || Auth::user()->isOperator())
                    <button type="button" onclick="document.getElementById('log-exception-modal').classList.toggle('hidden')"
                        class="px-2.5 py-1 text-xs font-semibold bg-rose-50 text-rose-700 hover:bg-rose-100 rounded border border-rose-200 transition">
                        + Log Incident
                    </button>
                    @endif
                </div>

                <!-- Exception List -->
                <div class="space-y-3">
                    @forelse($shipment->exceptions as $exc)
                    <div class="p-3 rounded-lg border {{ $exc->status === 'RESOLVED' ? 'bg-slate-50/60 border-slate-200' : 'bg-rose-50/40 border-rose-200' }} text-xs space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-slate-900">{{ $exc->exception_type }}</span>
                            <span class="px-1.5 py-0.2 rounded text-[10px] font-bold uppercase
                                @if($exc->status === 'RESOLVED') bg-emerald-100 text-emerald-800
                                @elseif($exc->status === 'INVESTIGATING') bg-amber-100 text-amber-800
                                @else bg-rose-100 text-rose-800 @endif">
                                {{ $exc->status }}
                            </span>
                        </div>
                        <p class="text-slate-600">{{ $exc->description }}</p>
                        <div class="flex items-center justify-between text-[11px] text-slate-400 pt-1 border-t border-slate-200/50">
                            <span>Detected: {{ $exc->detected_at->format('M d, H:i') }} (+{{ $exc->impact_hours }}h impact)</span>
                            <span>Logged by: {{ $exc->logger->name }}</span>
                        </div>

                        @if($exc->resolution_notes)
                            <div class="text-[11px] text-emerald-700 bg-emerald-50 p-1.5 rounded font-medium">
                                Resolved ({{ $exc->resolved_at?->format('M d, H:i') }}): {{ $exc->resolution_notes }}
                            </div>
                        @endif

                        @if(($exc->status !== 'RESOLVED') && (Auth::user()->isAdmin() || Auth::user()->isOperator()))
                        <div class="pt-2 flex items-center justify-end space-x-2">
                            <form action="{{ route('exceptions.update', $exc->id) }}" method="POST" class="inline">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="INVESTIGATING">
                                <button type="submit" class="px-2 py-0.5 text-[10px] font-semibold bg-amber-100 text-amber-800 rounded hover:bg-amber-200">
                                    Set Investigating
                                </button>
                            </form>
                            <button type="button" onclick="openResolveModal('{{ $exc->id }}', '{{ $exc->exception_type }}')"
                                class="px-2 py-0.5 text-[10px] font-semibold bg-emerald-600 text-white rounded hover:bg-emerald-500">
                                Resolve Exception
                            </button>
                        </div>
                        @endif
                    </div>
                    @empty
                    <div class="text-xs text-slate-400 text-center py-4">No active or resolved exceptions recorded.</div>
                    @endforelse
                </div>
            </div>

            <!-- Chronological Tracking Timeline (Slide 07) -->
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm space-y-4">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <div>
                        <div class="flex items-center space-x-2">
                            <h2 class="text-base font-bold text-slate-900">Tracking Timeline</h2>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-[#E8D7B9]/40 text-[#38251D] border border-[#E8D7B9]">
                                {{ $shipment->trackingEvents->count() }} Events
                            </span>
                        </div>
                        <p class="text-xs text-slate-400">Append-only chronological audit trail of sea freight custody</p>
                    </div>
                    @if(Auth::user()->isAdmin() || Auth::user()->isOperator())
                    <button type="button" onclick="document.getElementById('add-event-modal').classList.toggle('hidden')"
                        class="px-2.5 py-1 text-xs font-bold bg-[#38251D] text-white hover:bg-[#795548] rounded-lg shadow-sm transition">
                        + Add Waypoint Event
                    </button>
                    @endif
                </div>

                <!-- Timeline Feed -->
                <div class="space-y-4 relative pl-4 border-l-2 border-[#E8D7B9]">
                    @forelse($shipment->trackingEvents as $event)
                    <div class="relative group">
                        <!-- Bullet indicator with icon -->
                        @php
                            $bulletBg = 'bg-[#38251D]';
                            if ($event->is_exception_event || $event->event_type === 'EXCEPTION') {
                                $bulletBg = 'bg-rose-600 ring-2 ring-rose-200';
                            } elseif ($event->event_type === 'DELIVERED') {
                                $bulletBg = 'bg-emerald-600 ring-2 ring-emerald-200';
                            } elseif ($event->event_type === 'IN_TRANSIT' || $event->event_type === 'DEPARTED') {
                                $bulletBg = 'bg-sky-600 ring-2 ring-sky-200';
                            } elseif ($event->event_type === 'CUSTOMS_HOLD') {
                                $bulletBg = 'bg-amber-600 ring-2 ring-amber-200';
                            }
                        @endphp
                        <span class="absolute -left-[23px] top-1.5 w-3.5 h-3.5 rounded-full border-2 border-white {{ $bulletBg }}"></span>

                        <div class="flex items-center justify-between text-xs">
                            <div class="flex items-center space-x-2">
                                <span class="font-black text-slate-900">{{ $event->event_type }}</span>
                                @if($event->status)
                                    <span class="px-1.5 py-0.2 rounded text-[9px] font-bold uppercase
                                        @if($event->status === 'DELIVERED') bg-emerald-100 text-emerald-800
                                        @elseif($event->status === 'IN_TRANSIT') bg-sky-100 text-sky-800
                                        @elseif($event->status === 'CANCELLED') bg-rose-100 text-rose-800
                                        @else bg-slate-100 text-slate-700 @endif">
                                        {{ $event->status }}
                                    </span>
                                @endif
                            </div>
                            <span class="text-[11px] text-slate-400 font-mono">{{ $event->event_time->format('M d, Y · H:i') }}</span>
                        </div>

                        <div class="text-xs font-semibold text-slate-700 mt-1 flex items-center justify-between">
                            <div class="flex items-center space-x-1.5">
                                <svg class="w-3.5 h-3.5 text-[#795548]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span>{{ $event->location_name }}</span>
                            </div>
                            @if($event->latitude && $event->longitude)
                                <span class="text-[10px] font-mono text-slate-400">
                                    {{ number_format($event->latitude, 3) }}, {{ number_format($event->longitude, 3) }}
                                </span>
                            @endif
                        </div>

                        <p class="text-xs text-slate-600 mt-1 p-2 rounded-lg {{ $event->is_exception_event ? 'bg-rose-50 text-rose-900 border border-rose-200' : 'bg-slate-50 border border-slate-100' }}">
                            {{ $event->description }}
                        </p>
                        <div class="flex items-center justify-between text-[10px] text-slate-400 mt-1">
                            <span>Logged by: {{ $event->creator->name ?? 'System' }}</span>
                            <span>{{ $event->event_time->diffForHumans() }}</span>
                        </div>
                    </div>
                    @empty
                    <div class="text-xs text-slate-400 py-4">No tracking events recorded.</div>
                    @endforelse
                </div>
            </div>

        </div>

    </div>

</div>

<!-- Modal: Add Tracking Event (Admin/Operator) -->
<div id="add-event-modal" class="fixed inset-0 bg-slate-900/60 z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-xl max-w-lg w-full p-6 shadow-2xl border border-slate-200">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h3 class="text-base font-bold text-slate-900">Append Maritime Tracking Event</h3>
                <p class="text-xs text-slate-400">Immutable historical event log with automatic status progression</p>
            </div>
            <button type="button" onclick="document.getElementById('add-event-modal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
        </div>

        <!-- Quick Waypoint Presets -->
        <div class="my-3 p-2.5 bg-[#FAF7F0] border border-[#E8D7B9] rounded-lg">
            <span class="text-[11px] font-bold uppercase text-[#38251D] block mb-1">Quick Port / Maritime Waypoint:</span>
            <div class="flex flex-wrap gap-1.5">
                <button type="button" onclick="fillWaypoint('Belawan Port (IDBLW)', 3.7833, 98.6833)" class="text-[10px] bg-white border border-[#E8D7B9] hover:bg-amber-50 px-2 py-0.5 rounded font-semibold text-slate-700">Belawan (ID)</button>
                <button type="button" onclick="fillWaypoint('Strait of Malacca Transit', 2.5000, 101.5000)" class="text-[10px] bg-white border border-[#E8D7B9] hover:bg-amber-50 px-2 py-0.5 rounded font-semibold text-slate-700">Malacca Strait</button>
                <button type="button" onclick="fillWaypoint('Singapore Offshore Anchorage', 1.2902, 103.8519)" class="text-[10px] bg-white border border-[#E8D7B9] hover:bg-amber-50 px-2 py-0.5 rounded font-semibold text-slate-700">Singapore</button>
                <button type="button" onclick="fillWaypoint('Suez Canal Maritime Corridor', 29.9744, 32.5598)" class="text-[10px] bg-white border border-[#E8D7B9] hover:bg-amber-50 px-2 py-0.5 rounded font-semibold text-slate-700">Suez Canal</button>
                <button type="button" onclick="fillWaypoint('Port of Rotterdam (NLRTM)', 51.9540, 4.0530)" class="text-[10px] bg-white border border-[#E8D7B9] hover:bg-amber-50 px-2 py-0.5 rounded font-semibold text-slate-700">Rotterdam (NL)</button>
                <button type="button" onclick="fillWaypoint('Port of Hamburg (DEHAM)', 53.5350, 9.9800)" class="text-[10px] bg-white border border-[#E8D7B9] hover:bg-amber-50 px-2 py-0.5 rounded font-semibold text-slate-700">Hamburg (DE)</button>
            </div>
        </div>

        <form action="{{ route('shipments.tracking.store', $shipment->id) }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Event Type / Milestone *</label>
                <select name="event_type" id="event_type_select" required class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 text-slate-900 focus:border-amber-600" onchange="autoFillDescription(this.value)">
                    <option value="BOOKED">BOOKED — Ocean Vessel Booking Confirmed</option>
                    <option value="PICKED_UP">PICKED_UP — Container Gate-In at Depot / POL</option>
                    <option value="DEPARTED">DEPARTED — Vessel Departed Port of Loading</option>
                    <option value="IN_TRANSIT" selected>IN_TRANSIT — Ocean Voyage Transit Waypoint</option>
                    <option value="ARRIVED_AT_PORT">ARRIVED_AT_PORT — Vessel Berthed at Port of Discharge</option>
                    <option value="CUSTOMS_HOLD">CUSTOMS_HOLD — Phytosanitary / Quarantine Inspection</option>
                    <option value="CUSTOMS_CLEARED">CUSTOMS_CLEARED — Import Customs & Release Granted</option>
                    <option value="DELIVERED">DELIVERED — Cargo Consignee Release Completed</option>
                    <option value="EXCEPTION">EXCEPTION — Operational Anomaly / Alert Logged</option>
                    <option value="CANCELLED">CANCELLED — Booking Cancelled</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Waypoint / Location Name *</label>
                <input type="text" name="location_name" id="wp_location_name" required placeholder="e.g. Strait of Malacca Waypoint" class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 text-slate-900">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Latitude</label>
                    <input type="number" step="0.0000001" name="latitude" id="wp_latitude" placeholder="e.g. 1.290270" class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 text-slate-900 font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Longitude</label>
                    <input type="number" step="0.0000001" name="longitude" id="wp_longitude" placeholder="e.g. 103.851959" class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 text-slate-900 font-mono">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Event Timestamp (UTC) *</label>
                <input type="datetime-local" name="event_time" required value="{{ now()->format('Y-m-d\TH:i') }}" class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 text-slate-900 font-mono">
                <p class="text-[10px] text-slate-400 mt-1">Must be equal to or later than the most recent tracking event.</p>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Description & Quality Inspection Notes *</label>
                <textarea name="description" id="wp_description" required rows="3" placeholder="Enter waypoint inspection or custody update details..." class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 text-slate-900">Container in transit under optimal atmospheric conditions; relative humidity &lt; 65%.</textarea>
            </div>

            <div class="flex items-center justify-end space-x-2 pt-2">
                <button type="button" onclick="document.getElementById('add-event-modal').classList.add('hidden')" class="px-3 py-1.5 text-xs border rounded-lg text-slate-600 hover:bg-slate-50">Cancel</button>
                <button type="submit" class="px-4 py-1.5 text-xs font-bold bg-[#38251D] hover:bg-[#795548] text-white rounded-lg shadow-sm">Record Event</button>
            </div>
        </form>
    </div>
</div>

<script>
    function fillWaypoint(name, lat, lon) {
        document.getElementById('wp_location_name').value = name;
        document.getElementById('wp_latitude').value = lat;
        document.getElementById('wp_longitude').value = lon;
    }

    function autoFillDescription(type) {
        const desc = document.getElementById('wp_description');
        const map = {
            'BOOKED': 'Ocean vessel carrier slot confirmed. Booking documentation issued.',
            'PICKED_UP': 'Container sealed and received at terminal CFS. Gate-in inspection completed.',
            'DEPARTED': 'Vessel departed berth. Underway on international sea route.',
            'IN_TRANSIT': 'Vessel underway. Container temperature and humidity levels monitored.',
            'ARRIVED_AT_PORT': 'Vessel safely berthed at discharge terminal. Gantry discharge scheduled.',
            'CUSTOMS_HOLD': 'Phytosanitary inspection sampling hold by port agriculture agency.',
            'CUSTOMS_CLEARED': 'Import customs duties processed and phytosanitary clearance granted.',
            'DELIVERED': 'Container released to buyer consignee truck. Final seal intact and verified.',
            'EXCEPTION': 'Operational delay or route anomaly recorded by vessel operator.',
            'CANCELLED': 'Maritime shipment booking cancelled by mutual trade agreement.'
        };
        if (map[type]) {
            desc.value = map[type];
        }
    }
</script>

<!-- Modal: Log Exception (Admin/Operator) -->
<div id="log-exception-modal" class="fixed inset-0 bg-slate-900/60 z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-xl max-w-lg w-full p-6 shadow-2xl border border-slate-200">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="text-base font-bold text-rose-950">Log Shipment Incident / Exception</h3>
            <button type="button" onclick="document.getElementById('log-exception-modal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
        </div>
        <form action="{{ route('shipments.exceptions.store', $shipment->id) }}" method="POST" class="mt-4 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold uppercase text-slate-700 mb-1">Exception Type *</label>
                <select name="exception_type" required class="w-full text-xs rounded border border-slate-300 px-3 py-2">
                    <option value="CUSTOMS_HOLD">CUSTOMS_HOLD (Phytosanitary & Customs Inspection)</option>
                    <option value="WEATHER_DELAY">WEATHER_DELAY (Monsoon / Typhoon / Rough Seas)</option>
                    <option value="PORT_CONGESTION">PORT_CONGESTION (Berthing Queue / Crane Bottleneck)</option>
                    <option value="DAMAGED">DAMAGED (Cargo Packaging / Seal Disrupted)</option>
                    <option value="MISROUTED">MISROUTED (Carrier Transshipment Discrepancy)</option>
                    <option value="DOCUMENTATION_ISSUE">DOCUMENTATION_ISSUE (Missing Bill of Lading / COO)</option>
                    <option value="ACCIDENT">ACCIDENT (Collision / Maritime Equipment Disruption)</option>
                    <option value="TECHNICAL_FAILURE">TECHNICAL_FAILURE (Vessel Machinery / Reefer Failure)</option>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-700 mb-1">Severity *</label>
                    <select name="severity" required class="w-full text-xs rounded border border-slate-300 px-3 py-2">
                        <option value="LOW">LOW</option>
                        <option value="MEDIUM" selected>MEDIUM</option>
                        <option value="HIGH">HIGH</option>
                        <option value="CRITICAL">CRITICAL</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-700 mb-1">Delay Impact (Hours)</label>
                    <input type="number" name="impact_hours" min="0" value="12" placeholder="Hours added to ETA" class="w-full text-xs rounded border border-slate-300 px-3 py-2">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase text-slate-700 mb-1">Incident Description *</label>
                <textarea name="description" required rows="3" placeholder="Describe the operational bottleneck or disruption..." class="w-full text-xs rounded border border-slate-300 px-3 py-2"></textarea>
            </div>
            <div class="flex items-center justify-end space-x-2 pt-2">
                <button type="button" onclick="document.getElementById('log-exception-modal').classList.add('hidden')" class="px-3 py-1.5 text-xs border rounded text-slate-600">Cancel</button>
                <button type="submit" class="px-4 py-1.5 text-xs font-semibold bg-rose-600 text-white rounded hover:bg-rose-500">Record Exception</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Resolve Exception -->
<div id="resolve-modal" class="fixed inset-0 bg-slate-900/60 z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-xl max-w-md w-full p-6 shadow-2xl border border-slate-200">
        <h3 class="text-base font-bold text-slate-900 mb-2">Resolve Exception</h3>
        <form id="resolve-form" method="POST" action="" class="space-y-4">
            @csrf
            @method('PATCH')
            <input type="hidden" name="status" value="RESOLVED">
            <div>
                <label class="block text-xs font-semibold uppercase text-slate-700 mb-1">Resolution Action & Notes *</label>
                <textarea name="resolution_notes" required rows="3" placeholder="Details of clearance, repair, or resolution..." class="w-full text-xs rounded border border-slate-300 px-3 py-2"></textarea>
            </div>
            <div class="flex items-center justify-end space-x-2">
                <button type="button" onclick="document.getElementById('resolve-modal').classList.add('hidden')" class="px-3 py-1.5 text-xs border rounded text-slate-600">Cancel</button>
                <button type="submit" class="px-4 py-1.5 text-xs font-semibold bg-emerald-600 text-white rounded hover:bg-emerald-500">Confirm Resolution</button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    function openResolveModal(id, title) {
        const form = document.getElementById('resolve-form');
        form.action = '/exceptions/' + id;
        document.getElementById('resolve-modal').classList.remove('hidden');
    }

    let map;
    let currentTileLayer;
    let tileProviders = {};
    let animationInterval = null;
    let densePoints = [];
    let curStepIndex = 0;
    let sailingSpeed = 1;
    let isSailingPlaying = true;
    let movingVesselMarker = null;
    let traveledPolyline = null;
    let plannedPolyline = null;

    function setSailingSpeed(multiplier) {
        sailingSpeed = multiplier;
        document.querySelectorAll('.speed-btn').forEach(b => {
            b.classList.remove('bg-[#C49A55]', 'text-slate-900');
            b.classList.add('text-slate-300');
        });
        const activeBtn = document.getElementById('speed-' + multiplier);
        if (activeBtn) {
            activeBtn.classList.add('bg-[#C49A55]', 'text-slate-900');
            activeBtn.classList.remove('text-slate-300');
        }
    }

    function changeMapTheme(theme) {
        if (!map || !tileProviders[theme]) return;
        if (currentTileLayer) {
            map.removeLayer(currentTileLayer);
        }
        currentTileLayer = tileProviders[theme];
        currentTileLayer.addTo(map);
    }

    // High precision nautical bearing between two points
    function calculateBearing(lat1, lon1, lat2, lon2) {
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
        const vesselName = {!! json_encode($shipment->vessel_name ?? $shipment->vessel_carrier ?? 'MSC Amsterdam') !!};

        // Complete voyage milestones array
        const rawRoutePoints = [
            [originLat, originLon]
        ];

        // Add intermediate tracking waypoints
        @foreach($shipment->trackingEvents as $ev)
            @if($ev->latitude && $ev->longitude)
                rawRoutePoints.push([{{ $ev->latitude }}, {{ $ev->longitude }}]);
            @endif
        @endforeach

        // Ensure current real-world position is included in the nautical route
        rawRoutePoints.push([currentLat, currentLon]);
        rawRoutePoints.push([destLat, destLon]);

        // Remove immediate duplicates if any
        const cleanedWaypoints = [];
        for (let i = 0; i < rawRoutePoints.length; i++) {
            if (i === 0 || rawRoutePoints[i][0] !== rawRoutePoints[i-1][0] || rawRoutePoints[i][1] !== rawRoutePoints[i-1][1]) {
                cleanedWaypoints.push(rawRoutePoints[i]);
            }
        }

        // Initialize World Map Leaflet Instance
        map = L.map('shipment-map', {
            center: [(originLat + destLat) / 2, (originLon + destLon) / 2],
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

        // Default to Voyager World Map
        currentTileLayer = tileProviders.voyager;
        currentTileLayer.addTo(map);

        // 1. Origin Marker (Emerald Port Anchor)
        const originMarker = L.circleMarker([originLat, originLon], {
            radius: 8,
            fillColor: '#10b981',
            color: '#ffffff',
            weight: 2.5,
            opacity: 1,
            fillOpacity: 0.95
        }).addTo(map);
        originMarker.bindPopup(`<strong>PELABUHAN ASAL (ORIGIN):</strong><br>{{ $shipment->origin->name }}, {{ $shipment->origin->country }}<br>Departure: {{ $shipment->expected_departure->format('Y-m-d H:i') }}`);

        // 2. Destination Marker (Rose Terminal Flag)
        const destMarker = L.circleMarker([destLat, destLon], {
            radius: 8,
            fillColor: '#ef4444',
            color: '#ffffff',
            weight: 2.5,
            opacity: 1,
            fillOpacity: 0.95
        }).addTo(map);
        destMarker.bindPopup(`<strong>PELABUHAN TUJUAN (DESTINATION):</strong><br>{{ $shipment->destination->name }}, {{ $shipment->destination->country }}<br>ETA: {{ $shipment->expected_arrival->format('Y-m-d H:i') }}`);

        // 3. Historical Waypoint Checkpoints
        @foreach($shipment->trackingEvents as $ev)
            @if($ev->latitude && $ev->longitude)
                const wpMarker = L.circleMarker([{{ $ev->latitude }}, {{ $ev->longitude }}], {
                    radius: 6,
                    fillColor: '{{ $ev->is_exception_event ? "#dc2626" : "#0284c7" }}',
                    color: '#ffffff',
                    weight: 2,
                    opacity: 1,
                    fillOpacity: 0.9
                }).addTo(map);
                wpMarker.bindPopup(`<strong>CHECKPOINT:</strong> {{ $ev->location_name }}<br><small class="text-slate-500">{{ $ev->event_time->format('Y-m-d H:i') }}</small><br>{{ $ev->description }}`);
            @endif
        @endforeach

        // 4. Fixed Real-Time Anchor Beacon for Current Cargo Location ("Di mana barang sudah sampai")
        const currentBeaconHtml = `
            <div class="relative flex items-center justify-center pointer-events-auto" style="width: 36px; height: 36px; margin-left: -18px; margin-top: -18px;">
                <span class="animate-ping absolute inline-flex h-9 w-9 rounded-full bg-amber-400 opacity-75"></span>
                <span class="absolute inline-flex h-6 w-6 rounded-full bg-amber-500 opacity-50"></span>
                <div class="relative inline-flex rounded-full h-7 w-7 bg-amber-600 border-2 border-white shadow-xl items-center justify-center text-white text-xs font-black">
                    📍
                </div>
            </div>
        `;
        const currentBeaconMarker = L.marker([currentLat, currentLon], {
            icon: L.divIcon({
                html: currentBeaconHtml,
                className: 'real-position-anchor-beacon',
                iconSize: [36, 36],
                iconAnchor: [18, 18],
                popupAnchor: [0, -18]
            }),
            zIndexOffset: 700
        }).addTo(map);
        currentBeaconMarker.bindPopup(`
            <div class="p-1 font-sans text-xs min-w-[210px]">
                <div class="text-[10px] font-black uppercase tracking-wider text-amber-800 mb-0.5">📍 POSISI REAL-TIME SAAT INI</div>
                <div class="text-sm font-extrabold text-slate-900">${currentLocationName}</div>
                <div class="text-slate-600 text-[11px] mt-1 space-y-0.5 border-t border-slate-100 pt-1">
                    <div>Status: <span class="font-bold text-amber-700">{{ $shipment->current_status }}</span></div>
                    <div>Perjalanan Tuntas: <strong>${currentProgress}%</strong></div>
                    <div>Koordinat: ${currentLat.toFixed(4)}°, ${currentLon.toFixed(4)}°</div>
                    <div class="text-[10px] text-slate-400">Pembaruan: {{ $currentPos['last_updated'] }}</div>
                </div>
            </div>
        `);

        // 5. Build Densely Interpolated Route for Butter-Smooth Shopee-Style Sailing Animation
        densePoints = [];
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
                const bearing = calculateBearing(curLat, curLon, p2[0], p2[1]);

                densePoints.push({
                    lat: curLat,
                    lng: curLon,
                    bearing: bearing,
                    segment: i
                });
            }
        }
        if (cleanedWaypoints.length > 0) {
            const last = cleanedWaypoints[cleanedWaypoints.length - 1];
            densePoints.push({
                lat: last[0],
                lng: last[1],
                bearing: densePoints.length > 0 ? densePoints[densePoints.length - 1].bearing : 0,
                segment: cleanedWaypoints.length - 1
            });
        }

        // 6. Polylines: Planned route (dashed) and Traveled route (glowing solid gold)
        plannedPolyline = L.polyline(cleanedWaypoints, {
            color: '#38251D',
            weight: 3,
            opacity: 0.45,
            dashArray: '6, 8'
        }).addTo(map);

        traveledPolyline = L.polyline([], {
            color: '#C49A55',
            weight: 4,
            opacity: 0.95
        }).addTo(map);

        // 7. Custom Moving Vessel Marker (Shopee-Style Animated Cargo Ship with Water Ripples)
        const movingVesselHtml = `
            <div class="shopee-sailing-ship-container relative flex items-center justify-center cursor-pointer" style="width: 52px; height: 52px; margin-left: -26px; margin-top: -26px;">
                <!-- Expanding water wake ripple effects -->
                <div class="ship-wake-pulse absolute w-12 h-12 rounded-full bg-cyan-400/40 animate-ping"></div>
                <div class="ship-wake-glow absolute w-9 h-9 rounded-full bg-amber-400/30 animate-pulse"></div>

                <!-- Directional Container Ship Body (Rotates to heading) -->
                <div class="vessel-rotating-body relative w-10 h-10 bg-gradient-to-br from-[#38251D] via-[#4E342E] to-[#292421] border-2 border-[#C49A55] rounded-full shadow-2xl flex items-center justify-center text-white text-base font-bold transition-transform duration-75" id="sailing-ship-icon">
                    🚢
                </div>

                <!-- Floating Shopee-style live badge -->
                <div class="absolute -top-7 whitespace-nowrap bg-slate-900/95 text-[#E8D7B9] text-[9px] font-black px-2 py-0.5 rounded-full border border-[#C49A55]/50 shadow-lg flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span id="ship-badge-text">Berlayar 19.4 Knots</span>
                </div>
            </div>
        `;

        const movingVesselIcon = L.divIcon({
            html: movingVesselHtml,
            className: 'shopee-moving-vessel-marker',
            iconSize: [52, 52],
            iconAnchor: [26, 26],
            popupAnchor: [0, -26]
        });

        const startPt = densePoints[0] || { lat: originLat, lng: originLon };
        movingVesselMarker = L.marker([startPt.lat, startPt.lng], {
            icon: movingVesselIcon,
            zIndexOffset: 1200
        }).addTo(map);

        movingVesselMarker.bindPopup(`
            <div class="p-1 font-sans text-xs min-w-[210px]">
                <div class="text-[10px] font-black uppercase tracking-wider text-emerald-700 flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span> KAPAL SEDANG BERLAYAR
                </div>
                <div class="text-sm font-extrabold text-slate-900 mt-0.5">${vesselName}</div>
                <div class="text-[11px] text-slate-600 space-y-0.5 mt-1 border-t border-slate-100 pt-1">
                    <div>Rute: <strong>{{ $shipment->origin->city }} &rarr; {{ $shipment->destination->city }}</strong></div>
                    <div>Kecepatan: <strong>19.4 Knots (~36 km/h)</strong></div>
                    <div>Status: <span class="px-1.5 py-0.2 bg-amber-100 text-amber-900 font-bold rounded text-[10px]">{{ $shipment->current_status }}</span></div>
                </div>
            </div>
        `);

        // 8. Continuous Animation Loop (Shopee Smooth Movement)
        const telemetryCoords = document.getElementById('telemetry-coords');
        const telemetrySpeed = document.getElementById('telemetry-speed');
        const telemetryLeg = document.getElementById('telemetry-leg');
        const shipBadgeText = document.getElementById('ship-badge-text');

        function animateSailingFrame() {
            if (!isSailingPlaying) return;

            curStepIndex += sailingSpeed;
            if (curStepIndex >= densePoints.length) {
                curStepIndex = 0; // Smooth loop from origin
            }

            const cur = densePoints[curStepIndex];
            if (cur) {
                movingVesselMarker.setLatLng([cur.lat, cur.lng]);

                // Rotate ship icon smoothly to heading angle
                const shipIconElem = document.getElementById('sailing-ship-icon');
                if (shipIconElem) {
                    shipIconElem.style.transform = `rotate(${Math.round(cur.bearing)}deg)`;
                }

                // Update dynamic trail
                const trailCoords = densePoints.slice(0, curStepIndex + 1).map(p => [p.lat, p.lng]);
                traveledPolyline.setLatLngs(trailCoords);

                // Update telemetry overlay
                if (telemetryCoords) {
                    telemetryCoords.innerText = `Lat: ${cur.lat.toFixed(4)}°, Lon: ${cur.lng.toFixed(4)}°`;
                }
                const progressPct = Math.round((curStepIndex / (densePoints.length - 1)) * 100);
                if (shipBadgeText) {
                    shipBadgeText.innerText = `Berlayar (${progressPct}%)`;
                }
            }
        }

        // Run animation timer (approx 30fps for smooth gliding)
        animationInterval = setInterval(animateSailingFrame, 35);

        // 9. Interactive Control Buttons
        const playPauseBtn = document.getElementById('btn-play-pause');
        const playIcon = document.getElementById('play-icon');
        const playText = document.getElementById('play-text');
        if (playPauseBtn) {
            playPauseBtn.addEventListener('click', function () {
                isSailingPlaying = !isSailingPlaying;
                if (isSailingPlaying) {
                    playIcon.innerText = '⏸️';
                    playText.innerText = 'Jeda';
                    animationInterval = setInterval(animateSailingFrame, 35);
                } else {
                    playIcon.innerText = '▶️';
                    playText.innerText = 'Lanjut';
                    clearInterval(animationInterval);
                }
            });
        }

        const restartBtn = document.getElementById('btn-restart');
        if (restartBtn) {
            restartBtn.addEventListener('click', function () {
                curStepIndex = 0;
                traveledPolyline.setLatLngs([]);
                if (!isSailingPlaying) {
                    isSailingPlaying = true;
                    if (playIcon) playIcon.innerText = '⏸️';
                    if (playText) playText.innerText = 'Jeda';
                    animationInterval = setInterval(animateSailingFrame, 35);
                }
                map.panTo([originLat, originLon]);
            });
        }

        const jumpNowBtn = document.getElementById('btn-jump-now');
        if (jumpNowBtn) {
            jumpNowBtn.addEventListener('click', function () {
                // Find point closest to current real-world coordinates
                let closestIndex = 0;
                let minDist = 999999;
                for (let i = 0; i < densePoints.length; i++) {
                    const d = Math.abs(densePoints[i].lat - currentLat) + Math.abs(densePoints[i].lng - currentLon);
                    if (d < minDist) {
                        minDist = d;
                        closestIndex = i;
                    }
                }
                curStepIndex = closestIndex;
                map.flyTo([currentLat, currentLon], 6, { animate: true, duration: 1.2 });
                currentBeaconMarker.openPopup();
            });
        }

        // Fit bounds initially
        const allBounds = L.latLngBounds(cleanedWaypoints);
        allBounds.extend([currentLat, currentLon]);
        map.fitBounds(allBounds, { padding: [55, 55] });
    });
</script>
@endsection
