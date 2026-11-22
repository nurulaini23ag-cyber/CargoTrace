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

            <!-- Interactive Leaflet.js Map (Slide 09) -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Geographic Tracking Map</h2>
                        <p class="text-xs text-slate-500">Origin, checkpoints, active vessel/vehicle coordinates, and destination</p>
                    </div>
                    <span class="text-[11px] font-semibold text-slate-500 bg-slate-100 px-2 py-0.5 rounded">
                        Leaflet + OSM
                    </span>
                </div>
                <div id="shipment-map" class="h-96 w-full z-10"></div>
                <div class="p-3 bg-slate-50 text-[11px] text-slate-500 border-t border-slate-100 flex items-center justify-between">
                    <div>
                        Green: Origin &bull; Red: Destination &bull; Blue: Historical Checkpoint &bull; Gold: Current Location
                    </div>
                    <div>
                        &copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" class="underline">OpenStreetMap</a>
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
                    <option value="CUSTOMS_HOLD">CUSTOMS_HOLD (Customs Clearance Inspection)</option>
                    <option value="WEATHER_DELAY">WEATHER_DELAY (Typhoon / High Seas / Storm)</option>
                    <option value="PORT_CONGESTION">PORT_CONGESTION (Berthing Queue Delay)</option>
                    <option value="DAMAGED">DAMAGED (Cargo Packaging / Seal Disrupted)</option>
                    <option value="MISROUTED">MISROUTED (Carrier Routing Discrepancy)</option>
                    <option value="DOCUMENTATION_ISSUE">DOCUMENTATION_ISSUE (Missing Bill of Lading)</option>
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

    document.addEventListener('DOMContentLoaded', function () {
        // Prepare Leaflet map
        const originLat = {{ $shipment->origin->latitude }};
        const originLon = {{ $shipment->origin->longitude }};
        const destLat = {{ $shipment->destination->latitude }};
        const destLon = {{ $shipment->destination->longitude }};

        // Coordinates array for polyline
        const routeCoords = [
            [originLat, originLon]
        ];

        // Center map midway
        const centerLat = (originLat + destLat) / 2;
        const centerLon = (originLon + destLon) / 2;

        const map = L.map('shipment-map').setView([centerLat, centerLon], 3);

        // OpenStreetMap Tile Layer with mandatory attribution
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        // Origin marker (Green)
        const originMarker = L.circleMarker([originLat, originLon], {
            radius: 8,
            fillColor: '#10b981',
            color: '#ffffff',
            weight: 2,
            opacity: 1,
            fillOpacity: 0.9
        }).addTo(map);
        originMarker.bindPopup("<strong>ORIGIN:</strong> {{ $shipment->origin->name }}<br>Departure: {{ $shipment->expected_departure->format('Y-m-d H:i') }}");

        // Destination marker (Red)
        const destMarker = L.circleMarker([destLat, destLon], {
            radius: 8,
            fillColor: '#ef4444',
            color: '#ffffff',
            weight: 2,
            opacity: 1,
            fillOpacity: 0.9
        }).addTo(map);
        destMarker.bindPopup("<strong>DESTINATION:</strong> {{ $shipment->destination->name }}<br>Arrival: {{ $shipment->expected_arrival->format('Y-m-d H:i') }}");

        // Waypoints from tracking events
        @foreach($shipment->trackingEvents as $ev)
            @if($ev->latitude && $ev->longitude)
                routeCoords.push([{{ $ev->latitude }}, {{ $ev->longitude }}]);
                const wpMarker = L.circleMarker([{{ $ev->latitude }}, {{ $ev->longitude }}], {
                    radius: 6,
                    fillColor: '{{ $ev->is_exception_event ? "#dc2626" : "#0284c7" }}',
                    color: '#ffffff',
                    weight: 2,
                    opacity: 1,
                    fillOpacity: 0.8
                }).addTo(map);
                wpMarker.bindPopup("<strong>{{ $ev->event_type }}</strong><br>{{ $ev->location_name }}<br><small>{{ $ev->event_time->format('Y-m-d H:i') }}</small><br>{{ $ev->description }}");
            @endif
        @endforeach

        routeCoords.push([destLat, destLon]);

        // Draw connecting polyline
        const polyline = L.polyline(routeCoords, {
            color: '#0284c7',
            weight: 3,
            opacity: 0.7,
            dashArray: '6, 6'
        }).addTo(map);

        map.fitBounds(polyline.getBounds(), { padding: [40, 40] });
    });
</script>
@endsection
