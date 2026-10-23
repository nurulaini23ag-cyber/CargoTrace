@extends('layouts.app')

@section('title', 'Shipments Directory')

@section('content')
<div class="space-y-6">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Shipment Management</h1>
            <p class="text-sm text-slate-500">Search, filter, and monitor global cargo movement lifecycle</p>
        </div>
        @if(Auth::user()->isAdmin() || Auth::user()->isOperator())
        <div>
            <a href="{{ route('shipments.create') }}" class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-500 text-white text-sm font-semibold rounded-lg shadow-sm transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                New Shipment
            </a>
        </div>
        @endif
    </div>

    <!-- Multi-Attribute Search & Filter Panel (Server-Side) -->
    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
        <form action="{{ route('shipments.index') }}" method="GET" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search Text Input -->
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Search Reference / Cargo</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Search by shipment #, cargo type, or customer..."
                        class="w-full text-sm rounded-lg border border-slate-300 px-3 py-2 text-slate-900 focus:border-sky-500 focus:ring-1 focus:ring-sky-500">
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Status</label>
                    <select name="status" class="w-full text-sm rounded-lg border border-slate-300 px-3 py-2 text-slate-900 focus:border-sky-500 focus:ring-1 focus:ring-sky-500">
                        <option value="">All Statuses</option>
                        <option value="CREATED" {{ request('status') === 'CREATED' ? 'selected' : '' }}>CREATED</option>
                        <option value="BOOKED" {{ request('status') === 'BOOKED' ? 'selected' : '' }}>BOOKED</option>
                        <option value="PICKED_UP" {{ request('status') === 'PICKED_UP' ? 'selected' : '' }}>PICKED_UP</option>
                        <option value="IN_TRANSIT" {{ request('status') === 'IN_TRANSIT' ? 'selected' : '' }}>IN_TRANSIT</option>
                        <option value="DELIVERED" {{ request('status') === 'DELIVERED' ? 'selected' : '' }}>DELIVERED</option>
                        <option value="CANCELLED" {{ request('status') === 'CANCELLED' ? 'selected' : '' }}>CANCELLED</option>
                    </select>
                </div>

                <!-- Carrier Filter -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Carrier</label>
                    <select name="carrier_id" class="w-full text-sm rounded-lg border border-slate-300 px-3 py-2 text-slate-900 focus:border-sky-500 focus:ring-1 focus:ring-sky-500">
                        <option value="">All Carriers</option>
                        @foreach($carriers as $carrier)
                            <option value="{{ $carrier->id }}" {{ request('carrier_id') == $carrier->id ? 'selected' : '' }}>
                                {{ $carrier->name }} ({{ $carrier->code }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Secondary Filters Row -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 pt-2 border-t border-slate-100">
                <!-- Origin -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Origin Port</label>
                    <select name="origin_id" class="w-full text-xs rounded-lg border border-slate-300 px-2.5 py-1.5 text-slate-900 focus:border-sky-500">
                        <option value="">All Origins</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" {{ request('origin_id') == $loc->id ? 'selected' : '' }}>{{ $loc->city }} ({{ $loc->code }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Destination -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Destination Port</label>
                    <select name="destination_id" class="w-full text-xs rounded-lg border border-slate-300 px-2.5 py-1.5 text-slate-900 focus:border-sky-500">
                        <option value="">All Destinations</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" {{ request('destination_id') == $loc->id ? 'selected' : '' }}>{{ $loc->city }} ({{ $loc->code }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Delay Status -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Delay Condition</label>
                    <select name="is_delayed" class="w-full text-xs rounded-lg border border-slate-300 px-2.5 py-1.5 text-slate-900 focus:border-sky-500">
                        <option value="">All Schedule States</option>
                        <option value="yes" {{ request('is_delayed') === 'yes' ? 'selected' : '' }}>Delayed Only</option>
                        <option value="no" {{ request('is_delayed') === 'no' ? 'selected' : '' }}>On-Time Only</option>
                    </select>
                </div>

                <!-- Date Range (Arrival) -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Arrival From</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                        class="w-full text-xs rounded-lg border border-slate-300 px-2 py-1.5 text-slate-900">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Arrival To</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                        class="w-full text-xs rounded-lg border border-slate-300 px-2 py-1.5 text-slate-900">
                </div>
            </div>

            <!-- Action buttons -->
            <div class="flex items-center justify-between pt-2">
                <div class="flex items-center space-x-2 text-xs text-slate-500">
                    <span>Sort by:</span>
                    <select name="sort_by" class="border border-slate-200 rounded px-2 py-1 text-xs">
                        <option value="created_at" {{ request('sort_by') === 'created_at' ? 'selected' : '' }}>Created Date</option>
                        <option value="expected_arrival" {{ request('sort_by') === 'expected_arrival' ? 'selected' : '' }}>Expected Arrival</option>
                        <option value="shipment_number" {{ request('sort_by') === 'shipment_number' ? 'selected' : '' }}>Shipment Number</option>
                        <option value="weight" {{ request('sort_by') === 'weight' ? 'selected' : '' }}>Weight</option>
                    </select>
                    <select name="sort_order" class="border border-slate-200 rounded px-2 py-1 text-xs">
                        <option value="desc" {{ request('sort_order') === 'desc' ? 'selected' : '' }}>Desc</option>
                        <option value="asc" {{ request('sort_order') === 'asc' ? 'selected' : '' }}>Asc</option>
                    </select>
                </div>
                <div class="flex items-center space-x-2">
                    <a href="{{ route('shipments.index') }}" class="px-3 py-1.5 text-xs text-slate-600 hover:text-slate-900 border border-slate-300 rounded-lg hover:bg-slate-50 transition">
                        Reset Filters
                    </a>
                    <button type="submit" class="px-4 py-1.5 text-xs font-semibold bg-slate-900 hover:bg-slate-800 text-white rounded-lg shadow-sm transition">
                        Apply Filters
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Shipments Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3.5">Shipment Number</th>
                        <th class="px-5 py-3.5">Customer / Cargo</th>
                        <th class="px-5 py-3.5">Carrier & Route</th>
                        <th class="px-5 py-3.5">Status & Delay</th>
                        <th class="px-5 py-3.5">Expected Arrival</th>
                        <th class="px-5 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($shipments as $shipment)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="px-5 py-4">
                            <a href="{{ route('shipments.show', $shipment->id) }}" class="font-bold text-sky-600 hover:underline text-sm">
                                {{ $shipment->shipment_number }}
                            </a>
                            <div class="text-[11px] text-slate-400 mt-0.5">
                                {{ $shipment->weight }} kg &bull; {{ $shipment->volume }} m³
                            </div>
                        </td>

                        <td class="px-5 py-4">
                            <div class="font-semibold text-slate-900 text-xs">{{ $shipment->customer->name }}</div>
                            <div class="text-xs text-slate-500">{{ $shipment->customer->company_name ?? 'Individual' }}</div>
                            <div class="text-[11px] text-slate-400 mt-0.5">{{ $shipment->cargo_type }}</div>
                        </td>

                        <td class="px-5 py-4 text-xs">
                            <div class="font-medium text-slate-800">
                                {{ $shipment->origin->code }} ({{ $shipment->origin->city }})
                                &rarr;
                                {{ $shipment->destination->code }} ({{ $shipment->destination->city }})
                            </div>
                            <div class="text-slate-500 mt-0.5 flex items-center space-x-1">
                                <span class="px-1.5 py-0.2 bg-slate-100 text-slate-600 rounded text-[10px] font-semibold">{{ $shipment->carrier->mode }}</span>
                                <span>{{ $shipment->carrier->name }}</span>
                            </div>
                        </td>

                        <td class="px-5 py-4">
                            <div>
                                <span class="inline-block px-2.5 py-1 rounded text-xs font-bold uppercase
                                    @if($shipment->current_status === 'DELIVERED') bg-emerald-100 text-emerald-800 border border-emerald-200
                                    @elseif($shipment->current_status === 'IN_TRANSIT') bg-blue-100 text-blue-800 border border-blue-200
                                    @elseif($shipment->current_status === 'PICKED_UP') bg-indigo-100 text-indigo-800 border border-indigo-200
                                    @elseif($shipment->current_status === 'BOOKED') bg-purple-100 text-purple-800 border border-purple-200
                                    @elseif($shipment->current_status === 'CANCELLED') bg-rose-100 text-rose-800 border border-rose-200
                                    @else bg-slate-100 text-slate-800 border border-slate-200 @endif">
                                    {{ $shipment->current_status }}
                                </span>
                            </div>
                            @if($shipment->is_delayed)
                                <div class="mt-1">
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold uppercase bg-amber-100 text-amber-900 border border-amber-300">
                                        Delayed +{{ $shipment->delay_hours }}h ({{ $shipment->delay_severity }})
                                    </span>
                                </div>
                            @endif
                        </td>

                        <td class="px-5 py-4 text-xs text-slate-600">
                            <div class="font-medium text-slate-800">{{ $shipment->expected_arrival->format('Y-m-d H:i') }}</div>
                            @if($shipment->estimated_arrival && $shipment->estimated_arrival->notEqualTo($shipment->expected_arrival))
                                <div class="text-[11px] text-amber-600 font-semibold mt-0.5">
                                    ETA: {{ $shipment->estimated_arrival->format('Y-m-d H:i') }}
                                </div>
                            @endif
                        </td>

                        <td class="px-5 py-4 text-right space-x-2 whitespace-nowrap">
                            <a href="{{ route('shipments.show', $shipment->id) }}" class="inline-block px-2.5 py-1 text-xs font-semibold text-sky-700 bg-sky-50 hover:bg-sky-100 rounded transition">
                                View
                            </a>

                            @if(Auth::user()->isAdmin() || Auth::user()->isOperator())
                                <a href="{{ route('shipments.edit', $shipment->id) }}" class="inline-block px-2.5 py-1 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded transition">
                                    Edit
                                </a>
                            @endif

                            @if(Auth::user()->isAdmin())
                                <form action="{{ route('shipments.destroy', $shipment->id) }}" method="POST" class="inline" onsubmit="return confirm('Soft delete shipment #{{ $shipment->shipment_number }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2 py-1 text-xs font-semibold text-rose-600 hover:text-rose-800 hover:bg-rose-50 rounded transition">
                                        Delete
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center text-slate-400">
                            <svg class="mx-auto h-10 w-10 text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                            <div class="text-sm font-medium text-slate-600">No shipments match your criteria.</div>
                            <div class="text-xs text-slate-400 mt-1">Try adjusting your filters or search keywords.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Server-side Pagination Links -->
        <div class="p-4 border-t border-slate-200">
            {{ $shipments->links() }}
        </div>
    </div>

</div>
@endsection
