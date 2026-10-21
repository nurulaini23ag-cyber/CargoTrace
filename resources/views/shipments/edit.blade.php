@extends('layouts.app')

@section('title', 'Edit Shipment #' . $shipment->shipment_number)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Edit Shipment #{{ $shipment->shipment_number }}</h1>
            <p class="text-sm text-slate-500">Update parameters, route checkpoints, or schedule</p>
        </div>
        <a href="{{ route('shipments.show', $shipment->id) }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">
            &larr; View Details
        </a>
    </div>

    <form action="{{ route('shipments.update', $shipment->id) }}" method="POST" class="bg-white p-6 sm:p-8 rounded-xl border border-slate-200 shadow-sm space-y-6">
        @csrf
        @method('PUT')

        <!-- Core Route & Parties -->
        <div>
            <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-2 mb-4">1. Parties & Logistics Route</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Customer (Read-only for integrity) -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Customer / Consignee</label>
                    <input type="text" disabled value="{{ $shipment->customer->name }} ({{ $shipment->customer->company_name ?? 'Individual' }})"
                        class="w-full text-sm rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-slate-600">
                </div>

                <!-- Carrier -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Carrier</label>
                    <select name="carrier_id" class="w-full text-sm rounded-lg border border-slate-300 px-3 py-2 text-slate-900">
                        @foreach($carriers as $car)
                            <option value="{{ $car->id }}" {{ old('carrier_id', $shipment->carrier_id) == $car->id ? 'selected' : '' }}>
                                {{ $car->name }} ({{ $car->code }}) - {{ $car->mode }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Origin -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Origin Port</label>
                    <select name="origin_id" class="w-full text-sm rounded-lg border border-slate-300 px-3 py-2 text-slate-900">
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" {{ old('origin_id', $shipment->origin_id) == $loc->id ? 'selected' : '' }}>
                                {{ $loc->name }} ({{ $loc->code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Destination -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Destination Port</label>
                    <select name="destination_id" class="w-full text-sm rounded-lg border border-slate-300 px-3 py-2 text-slate-900">
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" {{ old('destination_id', $shipment->destination_id) == $loc->id ? 'selected' : '' }}>
                                {{ $loc->name }} ({{ $loc->code }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Cargo Specifications -->
        <div>
            <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-2 mb-4">2. Cargo Specifications</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Cargo Type</label>
                    <input type="text" name="cargo_type" value="{{ old('cargo_type', $shipment->cargo_type) }}"
                        class="w-full text-sm rounded-lg border border-slate-300 px-3 py-2 text-slate-900">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Weight (kg)</label>
                    <input type="number" step="0.01" name="weight" value="{{ old('weight', $shipment->weight) }}"
                        class="w-full text-sm rounded-lg border border-slate-300 px-3 py-2 text-slate-900">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Volume (m³)</label>
                    <input type="number" step="0.01" name="volume" value="{{ old('volume', $shipment->volume) }}"
                        class="w-full text-sm rounded-lg border border-slate-300 px-3 py-2 text-slate-900">
                </div>
            </div>
        </div>

        <!-- Schedule & Milestone Dates -->
        <div>
            <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-2 mb-4">3. Schedule & Estimated Arrival</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Expected Departure</label>
                    <input type="datetime-local" name="expected_departure"
                        value="{{ old('expected_departure', $shipment->expected_departure->format('Y-m-d\TH:i')) }}"
                        class="w-full text-sm rounded-lg border border-slate-300 px-3 py-2 text-slate-900">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Expected Arrival</label>
                    <input type="datetime-local" name="expected_arrival"
                        value="{{ old('expected_arrival', $shipment->expected_arrival->format('Y-m-d\TH:i')) }}"
                        class="w-full text-sm rounded-lg border border-slate-300 px-3 py-2 text-slate-900">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Estimated Arrival (ETA)</label>
                    <input type="datetime-local" name="estimated_arrival"
                        value="{{ old('estimated_arrival', $shipment->estimated_arrival ? $shipment->estimated_arrival->format('Y-m-d\TH:i') : '') }}"
                        class="w-full text-sm rounded-lg border border-slate-300 px-3 py-2 text-slate-900">
                </div>
            </div>

            <div class="mt-4">
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Special Instructions</label>
                <textarea name="special_instructions" rows="2"
                    class="w-full text-sm rounded-lg border border-slate-300 px-3 py-2 text-slate-900">{{ old('special_instructions', $shipment->special_instructions) }}</textarea>
            </div>
        </div>

        <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-200">
            <a href="{{ route('shipments.show', $shipment->id) }}" class="px-4 py-2 border border-slate-300 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50 transition">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2 bg-sky-600 hover:bg-sky-500 text-white rounded-lg text-sm font-semibold shadow-sm transition">
                Update Shipment
            </button>
        </div>
    </form>
</div>
@endsection
