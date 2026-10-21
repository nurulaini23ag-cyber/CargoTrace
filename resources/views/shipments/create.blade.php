@extends('layouts.app')

@section('title', 'Create Shipment')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Create New Shipment</h1>
            <p class="text-sm text-slate-500">Register cargo booking, route parameters, and schedule</p>
        </div>
        <a href="{{ route('shipments.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">
            &larr; Back to Shipments
        </a>
    </div>

    <form action="{{ route('shipments.store') }}" method="POST" class="bg-white p-6 sm:p-8 rounded-xl border border-slate-200 shadow-sm space-y-6">
        @csrf

        <!-- Core Route & Parties -->
        <div>
            <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-2 mb-4">1. Parties & Logistics Route</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Customer -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Customer / Consignee *</label>
                    <select name="customer_id" required class="w-full text-sm rounded-lg border border-slate-300 px-3 py-2 text-slate-900 focus:border-sky-500">
                        <option value="">Select Customer...</option>
                        @foreach($customers as $cust)
                            <option value="{{ $cust->id }}" {{ old('customer_id') == $cust->id ? 'selected' : '' }}>
                                {{ $cust->name }} ({{ $cust->company_name ?? 'Individual' }}) - {{ $cust->email }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Carrier -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Logistics Carrier *</label>
                    <select name="carrier_id" required class="w-full text-sm rounded-lg border border-slate-300 px-3 py-2 text-slate-900 focus:border-sky-500">
                        <option value="">Select Carrier...</option>
                        @foreach($carriers as $car)
                            <option value="{{ $car->id }}" {{ old('carrier_id') == $car->id ? 'selected' : '' }}>
                                {{ $car->name }} ({{ $car->code }}) - Mode: {{ $car->mode }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Origin -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Origin Port / Hub *</label>
                    <select name="origin_id" id="origin_id" required class="w-full text-sm rounded-lg border border-slate-300 px-3 py-2 text-slate-900 focus:border-sky-500">
                        <option value="">Select Origin...</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" {{ old('origin_id') == $loc->id ? 'selected' : '' }}>
                                {{ $loc->name }} ({{ $loc->code }}) - {{ $loc->country }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Destination -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Destination Port / Hub *</label>
                    <select name="destination_id" id="destination_id" required class="w-full text-sm rounded-lg border border-slate-300 px-3 py-2 text-slate-900 focus:border-sky-500">
                        <option value="">Select Destination...</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" {{ old('destination_id') == $loc->id ? 'selected' : '' }}>
                                {{ $loc->name }} ({{ $loc->code }}) - {{ $loc->country }}
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
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Cargo Commodity Type *</label>
                    <input type="text" name="cargo_type" required value="{{ old('cargo_type', 'Electronics & Components') }}"
                        placeholder="e.g. Perishables, Garments, Electronics"
                        class="w-full text-sm rounded-lg border border-slate-300 px-3 py-2 text-slate-900">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Total Weight (kg) *</label>
                    <input type="number" step="0.01" min="0.01" name="weight" required value="{{ old('weight', '12500') }}"
                        class="w-full text-sm rounded-lg border border-slate-300 px-3 py-2 text-slate-900">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Total Volume (m³) *</label>
                    <input type="number" step="0.01" min="0.01" name="volume" required value="{{ old('volume', '32.5') }}"
                        class="w-full text-sm rounded-lg border border-slate-300 px-3 py-2 text-slate-900">
                </div>
            </div>
        </div>

        <!-- Schedule & Milestone Dates -->
        <div>
            <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-2 mb-4">3. Schedule & ETA</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Expected Departure *</label>
                    <input type="datetime-local" name="expected_departure" required
                        value="{{ old('expected_departure', now()->addDay()->format('Y-m-d\TH:i')) }}"
                        class="w-full text-sm rounded-lg border border-slate-300 px-3 py-2 text-slate-900">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Expected Arrival *</label>
                    <input type="datetime-local" name="expected_arrival" required
                        value="{{ old('expected_arrival', now()->addDays(12)->format('Y-m-d\TH:i')) }}"
                        class="w-full text-sm rounded-lg border border-slate-300 px-3 py-2 text-slate-900">
                </div>
            </div>

            <div class="mt-4">
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Special Handling Instructions (Optional)</label>
                <textarea name="special_instructions" rows="2" placeholder="e.g. Temperature-controlled at 18°C, Keep upright"
                    class="w-full text-sm rounded-lg border border-slate-300 px-3 py-2 text-slate-900">{{ old('special_instructions') }}</textarea>
            </div>
        </div>

        <!-- Items Breakdown -->
        <div>
            <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-2 mb-3">4. Shipment Items Breakdown</h2>
            <div class="space-y-3" id="items-container">
                <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-lg grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
                    <div class="sm:col-span-5">
                        <label class="block text-[11px] font-semibold text-slate-500 uppercase">Item Description</label>
                        <input type="text" name="items[0][item_description]" required value="Semiconductor Microcontroller Modules"
                            class="w-full text-xs rounded border border-slate-300 px-2.5 py-1.5 mt-0.5">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-[11px] font-semibold text-slate-500 uppercase">Quantity</label>
                        <input type="number" min="1" name="items[0][quantity]" required value="500"
                            class="w-full text-xs rounded border border-slate-300 px-2.5 py-1.5 mt-0.5">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-[11px] font-semibold text-slate-500 uppercase">Packaging</label>
                        <select name="items[0][package_type]" class="w-full text-xs rounded border border-slate-300 px-2 py-1.5 mt-0.5">
                            <option value="BOX">BOX</option>
                            <option value="PALLET" selected>PALLET</option>
                            <option value="CONTAINER">CONTAINER</option>
                            <option value="CRATE">CRATE</option>
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-[11px] font-semibold text-slate-500 uppercase">Weight (kg)</label>
                        <input type="number" step="0.01" name="items[0][weight_kg]" value="12500"
                            class="w-full text-xs rounded border border-slate-300 px-2.5 py-1.5 mt-0.5">
                    </div>
                    <div class="sm:col-span-1 text-center pt-4">
                        <span class="text-xs text-slate-400">#1</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-200">
            <a href="{{ route('shipments.index') }}" class="px-4 py-2 border border-slate-300 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50 transition">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2 bg-sky-600 hover:bg-sky-500 text-white rounded-lg text-sm font-semibold shadow-sm transition">
                Save & Initialize Shipment
            </button>
        </div>
    </form>
</div>
@endsection
