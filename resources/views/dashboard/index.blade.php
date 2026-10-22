@extends('layouts.app')

@section('title', 'Operations Dashboard')

@section('content')
<div class="space-y-6">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Shipment Visibility Dashboard</h1>
            <p class="text-sm text-slate-500">
                Real-time operational metrics, delay detection, and active cargo exceptions
            </p>
        </div>
        @if(Auth::user()->isAdmin() || Auth::user()->isOperator())
        <div class="flex items-center space-x-3">
            <a href="{{ route('shipments.create') }}" class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-500 text-white text-sm font-semibold rounded-lg shadow-sm transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Create Shipment
            </a>
        </div>
        @endif
    </div>

    <!-- Live KPI Stat Cards (Real Database Driven) -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <!-- Total Shipments -->
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Shipments</div>
            <div class="mt-2 text-3xl font-extrabold text-slate-900">{{ $totalShipments }}</div>
            <div class="mt-1 text-xs text-slate-500">Managed in portfolio</div>
        </div>

        <!-- In Transit -->
        <div class="bg-white p-5 rounded-xl border border-blue-100 shadow-sm bg-gradient-to-br from-white to-blue-50/40">
            <div class="text-xs font-semibold uppercase tracking-wider text-blue-600">In Transit</div>
            <div class="mt-2 text-3xl font-extrabold text-blue-700">{{ $inTransitCount }}</div>
            <div class="mt-1 text-xs text-blue-600/80">Active cargo en-route</div>
        </div>

        <!-- Delivered -->
        <div class="bg-white p-5 rounded-xl border border-emerald-100 shadow-sm bg-gradient-to-br from-white to-emerald-50/40">
            <div class="text-xs font-semibold uppercase tracking-wider text-emerald-600">Delivered</div>
            <div class="mt-2 text-3xl font-extrabold text-emerald-700">{{ $deliveredCount }}</div>
            <div class="mt-1 text-xs text-emerald-600/80">Final terminal status</div>
        </div>

        <!-- Delayed -->
        <div class="bg-white p-5 rounded-xl border border-amber-100 shadow-sm bg-gradient-to-br from-white to-amber-50/40">
            <div class="text-xs font-semibold uppercase tracking-wider text-amber-600">Delayed</div>
            <div class="mt-2 text-3xl font-extrabold text-amber-700">{{ $delayedCount }}</div>
            <div class="mt-1 text-xs font-medium text-amber-600">Rate: {{ $delayRate }}%</div>
        </div>

        <!-- Open Exceptions -->
        <div class="bg-white p-5 rounded-xl border border-rose-100 shadow-sm bg-gradient-to-br from-white to-rose-50/40 col-span-2 md:col-span-1">
            <div class="text-xs font-semibold uppercase tracking-wider text-rose-600">Open Exceptions</div>
            <div class="mt-2 text-3xl font-extrabold text-rose-700">{{ $openExceptionsCount }}</div>
            <div class="mt-1 text-xs font-medium text-rose-600">Rate: {{ $exceptionRate }}%</div>
        </div>
    </div>

    <!-- Charts Section (Chart.js) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Status Distribution Chart -->
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Shipments by Lifecycle Status</h2>
                    <p class="text-xs text-slate-500">Distribution across active state machine phases</p>
                </div>
            </div>
            <div class="h-64 flex items-center justify-center">
                <canvas id="statusChart"></canvas>
            </div>
        </div>

        <!-- Shipments by Carrier Chart -->
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Shipments by Carrier</h2>
                    <p class="text-xs text-slate-500">Carrier allocation volume across logistics lines</p>
                </div>
            </div>
            <div class="h-64 flex items-center justify-center">
                <canvas id="carrierChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Open Exceptions Priority Action List -->
    @if($activeExceptions->count() > 0)
    <div class="bg-rose-50/70 border border-rose-200 rounded-xl p-5 shadow-sm">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center space-x-2">
                <span class="w-3 h-3 rounded-full bg-rose-500 animate-pulse"></span>
                <h2 class="text-base font-bold text-rose-950">Active Incident Priority Queue</h2>
            </div>
            <span class="text-xs font-semibold text-rose-700 bg-rose-100 px-2 py-0.5 rounded-full">
                {{ $activeExceptions->count() }} Urgent Item(s)
            </span>
        </div>
        <div class="divide-y divide-rose-200/60 overflow-hidden bg-white rounded-lg border border-rose-200">
            @foreach($activeExceptions as $exc)
            <div class="p-3.5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-sm hover:bg-rose-50/30 transition">
                <div>
                    <div class="flex items-center space-x-2">
                        <span class="font-bold text-slate-900">#{{ $exc->shipment->shipment_number }}</span>
                        <span class="px-2 py-0.5 rounded text-[11px] font-bold uppercase
                            @if($exc->severity === 'CRITICAL') bg-red-600 text-white
                            @elseif($exc->severity === 'HIGH') bg-orange-600 text-white
                            @else bg-amber-500 text-white @endif">
                            {{ $exc->exception_type }} ({{ $exc->severity }})
                        </span>
                        <span class="text-xs text-slate-400">Detected {{ $exc->detected_at->diffForHumans() }}</span>
                    </div>
                    <p class="text-xs text-slate-600 mt-1">{{ $exc->description }}</p>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="text-xs font-medium px-2 py-1 rounded bg-slate-100 text-slate-700 uppercase">
                        {{ $exc->status }}
                    </span>
                    <a href="{{ route('shipments.show', $exc->shipment_id) }}#exceptions" class="text-xs font-semibold text-sky-600 hover:text-sky-800 bg-sky-50 hover:bg-sky-100 px-2.5 py-1.5 rounded transition">
                        Inspect &rarr;
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Recent Activity & Recent Shipments Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Shipments Table (2 cols) -->
        <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Recent Shipments</h2>
                    <p class="text-xs text-slate-500">Latest shipments registered or in transit</p>
                </div>
                <a href="{{ route('shipments.index') }}" class="text-xs font-semibold text-sky-600 hover:text-sky-700">
                    View All &rarr;
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-400 border-b border-slate-100">
                        <tr>
                            <th class="px-5 py-3">Shipment No</th>
                            <th class="px-5 py-3">Route</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3">ETA</th>
                            <th class="px-5 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($recentShipments as $s)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-5 py-3 font-medium text-slate-900">
                                <a href="{{ route('shipments.show', $s->id) }}" class="text-sky-600 hover:underline">
                                    {{ $s->shipment_number }}
                                </a>
                                <div class="text-[11px] text-slate-400">{{ $s->cargo_type }}</div>
                            </td>
                            <td class="px-5 py-3 text-xs">
                                <div class="font-medium text-slate-800">{{ $s->origin->code }} &rarr; {{ $s->destination->code }}</div>
                                <div class="text-slate-400">{{ $s->carrier->name }}</div>
                            </td>
                            <td class="px-5 py-3">
                                <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold uppercase
                                    @if($s->current_status === 'DELIVERED') bg-emerald-100 text-emerald-800
                                    @elseif($s->current_status === 'IN_TRANSIT') bg-blue-100 text-blue-800
                                    @elseif($s->current_status === 'PICKED_UP') bg-indigo-100 text-indigo-800
                                    @elseif($s->current_status === 'BOOKED') bg-purple-100 text-purple-800
                                    @elseif($s->current_status === 'CANCELLED') bg-rose-100 text-rose-800
                                    @else bg-slate-100 text-slate-800 @endif">
                                    {{ $s->current_status }}
                                </span>
                                @if($s->is_delayed)
                                    <span class="ml-1 inline-block px-1.5 py-0.2 rounded text-[10px] font-bold uppercase bg-amber-100 text-amber-800">
                                        Delayed ({{ $s->delay_severity }})
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-xs text-slate-600">
                                {{ $s->estimated_arrival ? $s->estimated_arrival->format('M d, H:i') : $s->expected_arrival->format('M d, H:i') }}
                            </td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('shipments.show', $s->id) }}" class="text-xs font-semibold text-slate-600 hover:text-sky-600">
                                    Details
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-5 py-6 text-center text-xs text-slate-400">
                                No shipments found in database.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Tracking Activity Feed (1 col) -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
            <h2 class="text-base font-bold text-slate-900 mb-1">Live Tracking Feed</h2>
            <p class="text-xs text-slate-500 mb-4">Chronological waypoint and status events</p>

            <div class="space-y-4">
                @forelse($recentEvents as $event)
                <div class="relative pl-5 border-l-2 {{ $event->is_exception_event ? 'border-rose-400' : 'border-sky-400' }} pb-2">
                    <span class="absolute -left-[5px] top-1.5 w-2 h-2 rounded-full {{ $event->is_exception_event ? 'bg-rose-500 ring-2 ring-rose-200' : 'bg-sky-500 ring-2 ring-sky-200' }}"></span>
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-bold text-slate-800">{{ $event->event_type }}</span>
                        <span class="text-[11px] text-slate-400">{{ $event->event_time->diffForHumans() }}</span>
                    </div>
                    <div class="text-xs text-slate-600 font-medium mt-0.5">
                        <a href="{{ route('shipments.show', $event->shipment_id) }}" class="text-sky-600 hover:underline">
                            #{{ $event->shipment->shipment_number }}
                        </a> &bull; {{ $event->location_name }}
                    </div>
                    <p class="text-[11px] text-slate-500 mt-1 line-clamp-2">{{ $event->description }}</p>
                </div>
                @empty
                <div class="text-xs text-slate-400 text-center py-6">No recent events recorded.</div>
                @endforelse
            </div>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Status Chart (Doughnut)
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        const statusLabels = {!! json_encode(array_keys($statusChartData)) !!};
        const statusData = {!! json_encode(array_values($statusChartData)) !!};

        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: statusLabels,
                datasets: [{
                    data: statusData,
                    backgroundColor: [
                        '#94a3b8', // CREATED
                        '#c084fc', // BOOKED
                        '#818cf8', // PICKED_UP
                        '#38bdf8', // IN_TRANSIT
                        '#34d399', // DELIVERED
                        '#f87171'  // CANCELLED
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: { boxWidth: 12, font: { size: 11 } }
                    }
                }
            }
        });

        // Carrier Chart (Bar)
        const carrierCtx = document.getElementById('carrierChart').getContext('2d');
        const carrierLabels = {!! json_encode($carrierChartLabels) !!};
        const carrierData = {!! json_encode($carrierChartData) !!};

        new Chart(carrierCtx, {
            type: 'bar',
            data: {
                labels: carrierLabels.length > 0 ? carrierLabels : ['No Carriers Active'],
                datasets: [{
                    label: 'Active Shipments',
                    data: carrierData.length > 0 ? carrierData : [0],
                    backgroundColor: '#0284c7',
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, precision: 0 }
                    }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    });
</script>
@endsection
