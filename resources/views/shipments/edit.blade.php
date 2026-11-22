@extends('layouts.app')

@section('title', 'Edit Shipment #' . $shipment->shipment_number . ' - Cocoa & Coffee Logistics')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-amber-900/60 uppercase tracking-wider mb-1">
                <a href="{{ route('shipments.index') }}" class="hover:underline">Shipments</a>
                <span>&rsaquo;</span>
                <a href="{{ route('shipments.show', $shipment->id) }}" class="hover:underline">#{{ $shipment->shipment_number }}</a>
                <span>&rsaquo;</span>
                <span class="text-amber-900">Edit Maritime Booking</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <svg class="w-7 h-7 text-[#795548]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit Shipment #{{ $shipment->shipment_number }}
            </h1>
            <p class="text-sm text-slate-500">Update voyage parameters, commodity specifications, and handling protocols</p>
        </div>
        <a href="{{ route('shipments.show', $shipment->id) }}" class="inline-flex items-center text-xs font-bold text-slate-600 hover:text-slate-900 px-3 py-2 border border-slate-300 rounded-lg hover:bg-slate-50 transition">
            &larr; View Details
        </a>
    </div>

    @if ($errors->any())
        <div class="p-4 bg-rose-50 border-l-4 border-rose-500 rounded-r-xl text-xs text-rose-800 space-y-1">
            <div class="font-bold">Please correct the following errors:</div>
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('shipments.update', $shipment->id) }}" method="POST" id="edit-shipment-form" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- 1. Parties & Ocean Freight Route -->
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h2 class="text-sm font-bold uppercase tracking-wider text-slate-900 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-[#38251D] text-white flex items-center justify-center text-xs">1</span>
                    Parties & Maritime Route
                </h2>
                <span class="text-xs text-slate-400">Fixed Consignee Reference</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Customer (Read-only for integrity) -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Customer / Consignee</label>
                    <input type="text" disabled value="{{ $shipment->customer->name }} ({{ $shipment->customer->company_name ?? 'Trading House' }}) - {{ $shipment->customer->email }}"
                        class="w-full text-sm rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-slate-600">
                </div>

                <!-- Carrier -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Ocean Carrier / Line *</label>
                    <select name="carrier_id" class="w-full text-sm rounded-lg border border-slate-300 px-3 py-2 text-slate-900 focus:border-amber-600 focus:ring-1 focus:ring-amber-600">
                        @foreach($carriers as $car)
                            <option value="{{ $car->id }}" {{ old('carrier_id', $shipment->carrier_id) == $car->id ? 'selected' : '' }}>
                                {{ $car->name }} ({{ $car->code }}) - {{ $car->mode }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Origin Port -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Port of Origin (POL) *</label>
                    <select name="origin_id" class="w-full text-sm rounded-lg border border-slate-300 px-3 py-2 text-slate-900 focus:border-amber-600 focus:ring-1 focus:ring-amber-600">
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" {{ old('origin_id', $shipment->origin_id) == $loc->id ? 'selected' : '' }}>
                                {{ $loc->name }} ({{ $loc->code }}) - {{ $loc->city }}, {{ $loc->country }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Destination Port -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Port of Destination (POD) *</label>
                    <select name="destination_id" class="w-full text-sm rounded-lg border border-slate-300 px-3 py-2 text-slate-900 focus:border-amber-600 focus:ring-1 focus:ring-amber-600">
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" {{ old('destination_id', $shipment->destination_id) == $loc->id ? 'selected' : '' }}>
                                {{ $loc->name }} ({{ $loc->code }}) - {{ $loc->city }}, {{ $loc->country }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- 2. Cargo Commodity Overview -->
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h2 class="text-sm font-bold uppercase tracking-wider text-slate-900 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-[#795548] text-white flex items-center justify-center text-xs">2</span>
                    Commodity & Cargo Specifications
                </h2>
                <span class="text-xs text-slate-400">Coffee / Cocoa Sea Freight</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Cargo Commodity Title *</label>
                    <input type="text" name="cargo_type" value="{{ old('cargo_type', $shipment->cargo_type) }}" required
                        class="w-full text-sm rounded-lg border border-slate-300 px-3 py-2 text-slate-900 focus:border-amber-600 focus:ring-1 focus:ring-amber-600">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Total Net Weight (kg) *</label>
                    <input type="number" step="0.01" min="0.01" name="weight" id="total_weight" value="{{ old('weight', $shipment->weight) }}" required
                        class="w-full text-sm rounded-lg border border-slate-300 px-3 py-2 text-slate-900 focus:border-amber-600 focus:ring-1 focus:ring-amber-600">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Total Volume (m³) *</label>
                    <input type="number" step="0.01" min="0.01" name="volume" value="{{ old('volume', $shipment->volume) }}" required
                        class="w-full text-sm rounded-lg border border-slate-300 px-3 py-2 text-slate-900 focus:border-amber-600 focus:ring-1 focus:ring-amber-600">
                </div>
            </div>
        </div>

        <!-- 3. Schedule & ETA -->
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h2 class="text-sm font-bold uppercase tracking-wider text-slate-900 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-[#C49A55] text-white flex items-center justify-center text-xs">3</span>
                    Voyage Schedule & ETA Estimation
                </h2>
                <span class="text-xs text-slate-400">Deterministic Delay Tracking</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Expected Departure (ETD) *</label>
                    <input type="datetime-local" name="expected_departure" required
                        value="{{ old('expected_departure', $shipment->expected_departure->format('Y-m-d\TH:i')) }}"
                        class="w-full text-sm rounded-lg border border-slate-300 px-3 py-2 text-slate-900 focus:border-amber-600 focus:ring-1 focus:ring-amber-600">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Expected Arrival (ETA Baseline) *</label>
                    <input type="datetime-local" name="expected_arrival" required
                        value="{{ old('expected_arrival', $shipment->expected_arrival->format('Y-m-d\TH:i')) }}"
                        class="w-full text-sm rounded-lg border border-slate-300 px-3 py-2 text-slate-900 focus:border-amber-600 focus:ring-1 focus:ring-amber-600">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Estimated Arrival (Dynamic ETA)</label>
                    <input type="datetime-local" name="estimated_arrival"
                        value="{{ old('estimated_arrival', $shipment->estimated_arrival ? $shipment->estimated_arrival->format('Y-m-d\TH:i') : '') }}"
                        class="w-full text-sm rounded-lg border border-slate-300 px-3 py-2 text-slate-900 focus:border-amber-600 focus:ring-1 focus:ring-amber-600">
                </div>
            </div>

            <div class="mt-2">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Special Handling Instructions</label>
                <textarea name="special_instructions" rows="2"
                    class="w-full text-sm rounded-lg border border-slate-300 px-3 py-2 text-slate-900 focus:border-amber-600 focus:ring-1 focus:ring-amber-600">{{ old('special_instructions', $shipment->special_instructions) }}</textarea>
            </div>
        </div>

        <!-- 4. Detailed Cargo Manifest Items Breakdown -->
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h2 class="text-sm font-bold uppercase tracking-wider text-slate-900 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-slate-800 text-white flex items-center justify-center text-xs">4</span>
                        Cargo Manifest Items Breakdown
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Commodity-specific details: variety, grade, moisture content, packaging, and net weights</p>
                </div>
                <button type="button" onclick="addCargoItem()" class="inline-flex items-center px-3 py-1.5 text-xs font-bold bg-[#38251D] hover:bg-[#795548] text-white rounded-lg shadow-sm transition">
                    + Add Cargo Item
                </button>
            </div>

            <div id="items-container" class="space-y-4">
                @php $itemIndex = 0; @endphp
                @forelse($shipment->items as $item)
                    <div class="cargo-item-row p-4 bg-[#FAF7F0] border border-[#E8D7B9] rounded-xl space-y-3 relative" data-index="{{ $itemIndex }}">
                        <div class="flex items-center justify-between border-b border-[#E8D7B9]/60 pb-2">
                            <span class="text-xs font-black uppercase tracking-wider text-[#38251D] flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-[#795548]"></span>
                                Cargo Manifest Item #{{ $itemIndex + 1 }}
                            </span>
                            <button type="button" onclick="removeCargoItem(this)" class="text-xs text-rose-600 hover:text-rose-800 font-bold remove-item-btn">
                                &times; Remove Item
                            </button>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                            <!-- Category -->
                            <div class="sm:col-span-3">
                                <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Category *</label>
                                <select name="items[{{ $itemIndex }}][product_category]" class="item-category w-full text-xs rounded-lg border border-slate-300 px-2.5 py-1.5 bg-white text-slate-900" onchange="updateCategoryFields(this)">
                                    <option value="COFFEE" {{ $item->product_category === 'COFFEE' || str_contains(strtolower($item->product_name), 'coffee') ? 'selected' : '' }}>☕ COFFEE (Kopi)</option>
                                    <option value="COCOA" {{ $item->product_category === 'COCOA' || str_contains(strtolower($item->product_name), 'cocoa') ? 'selected' : '' }}>🍫 COCOA (Kakao)</option>
                                </select>
                            </div>

                            <!-- Product Name -->
                            <div class="sm:col-span-5">
                                <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Product Name *</label>
                                <input type="text" name="items[{{ $itemIndex }}][product_name]" required value="{{ $item->product_name ?? $item->item_description }}"
                                    class="item-name w-full text-xs rounded-lg border border-slate-300 px-2.5 py-1.5 bg-white text-slate-900">
                            </div>

                            <!-- Variety -->
                            <div class="sm:col-span-4">
                                <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Variety / Cultivar</label>
                                <input type="text" name="items[{{ $itemIndex }}][variety]" value="{{ $item->variety }}"
                                    placeholder="e.g. Typica, Forastero, Criollo"
                                    class="item-variety w-full text-xs rounded-lg border border-slate-300 px-2.5 py-1.5 bg-white text-slate-900">
                            </div>

                            <!-- Grade -->
                            <div class="sm:col-span-3">
                                <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Quality Grade</label>
                                <input type="text" name="items[{{ $itemIndex }}][grade]" value="{{ $item->grade }}"
                                    placeholder="e.g. Grade 1, GFG, SCA 85+"
                                    class="item-grade w-full text-xs rounded-lg border border-slate-300 px-2.5 py-1.5 bg-white text-slate-900">
                            </div>

                            <!-- Packaging -->
                            <div class="sm:col-span-3">
                                <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Packaging Type</label>
                                <select name="items[{{ $itemIndex }}][package_type]" class="item-packaging w-full text-xs rounded-lg border border-slate-300 px-2 py-1.5 bg-white text-slate-900">
                                    <option value="JUTE_BAG_60KG" {{ $item->package_type === 'JUTE_BAG_60KG' ? 'selected' : '' }}>Jute Bag 60kg</option>
                                    <option value="GRAINPRO_BAG" {{ $item->package_type === 'GRAINPRO_BAG' ? 'selected' : '' }}>GrainPro Hermetic Bag</option>
                                    <option value="BULK_LINER" {{ $item->package_type === 'BULK_LINER' ? 'selected' : '' }}>Bulk Container Liner</option>
                                    <option value="BOX" {{ $item->package_type === 'BOX' ? 'selected' : '' }}>Export Master Box</option>
                                    <option value="VACUUM_PACK" {{ $item->package_type === 'VACUUM_PACK' ? 'selected' : '' }}>Vacuum Brick Pack</option>
                                </select>
                            </div>

                            <!-- Package Count -->
                            <div class="sm:col-span-2">
                                <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Bags/Packages</label>
                                <input type="number" min="1" name="items[{{ $itemIndex }}][package_count]" value="{{ $item->package_count ?? $item->quantity }}"
                                    class="item-count w-full text-xs rounded-lg border border-slate-300 px-2.5 py-1.5 bg-white text-slate-900"
                                    oninput="recalcRow(this)">
                            </div>

                            <!-- Net Weight (kg) -->
                            <div class="sm:col-span-2">
                                <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Weight (kg)</label>
                                <input type="number" step="0.01" min="0" name="items[{{ $itemIndex }}][net_weight]" value="{{ $item->net_weight ?? $item->weight_kg }}"
                                    class="item-weight w-full text-xs rounded-lg border border-slate-300 px-2.5 py-1.5 bg-white text-slate-900"
                                    oninput="recalcTotals()">
                            </div>

                            <!-- Moisture Content (%) -->
                            <div class="sm:col-span-2">
                                <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Moisture (%)</label>
                                <input type="number" step="0.1" min="0" max="30" name="items[{{ $itemIndex }}][moisture_content]" value="{{ $item->moisture_content }}"
                                    class="item-moisture w-full text-xs rounded-lg border border-slate-300 px-2.5 py-1.5 bg-white text-slate-900">
                            </div>

                            <!-- HS Code -->
                            <div class="sm:col-span-3">
                                <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">HS Code</label>
                                <input type="text" name="items[{{ $itemIndex }}][hs_code]" value="{{ $item->hs_code }}"
                                    class="item-hscode w-full text-xs rounded-lg border border-slate-300 px-2.5 py-1.5 bg-white text-slate-900">
                            </div>

                            <!-- Target Temp (°C) -->
                            <div class="sm:col-span-3">
                                <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Target Temp (°C)</label>
                                <input type="number" step="0.5" name="items[{{ $itemIndex }}][temperature_target]" value="{{ $item->temperature_target }}"
                                    class="item-temp w-full text-xs rounded-lg border border-slate-300 px-2.5 py-1.5 bg-white text-slate-900">
                            </div>

                            <div class="sm:col-span-6 flex items-center pt-5">
                                <label class="inline-flex items-center text-xs text-slate-600">
                                    <input type="checkbox" name="items[{{ $itemIndex }}][is_hazardous]" value="1" {{ $item->is_hazardous ? 'checked' : '' }} class="rounded border-slate-300 text-amber-600 mr-2">
                                    Hazardous Material (DG Sea Freight Cargo)
                                </label>
                            </div>
                        </div>
                    </div>
                    @php $itemIndex++; @endphp
                @empty
                    <div class="cargo-item-row p-4 bg-[#FAF7F0] border border-[#E8D7B9] rounded-xl space-y-3 relative" data-index="0">
                        <div class="flex items-center justify-between border-b border-[#E8D7B9]/60 pb-2">
                            <span class="text-xs font-black uppercase tracking-wider text-[#38251D] flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-[#795548]"></span>
                                Cargo Manifest Item #1
                            </span>
                            <button type="button" onclick="removeCargoItem(this)" class="text-xs text-rose-600 hover:text-rose-800 font-bold hidden remove-item-btn">
                                &times; Remove Item
                            </button>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                            <div class="sm:col-span-3">
                                <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Category *</label>
                                <select name="items[0][product_category]" class="item-category w-full text-xs rounded-lg border border-slate-300 px-2.5 py-1.5 bg-white text-slate-900">
                                    <option value="COFFEE" selected>☕ COFFEE (Kopi)</option>
                                    <option value="COCOA">🍫 COCOA (Kakao)</option>
                                </select>
                            </div>
                            <div class="sm:col-span-5">
                                <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Product Name *</label>
                                <input type="text" name="items[0][product_name]" required value="{{ $shipment->cargo_type }}"
                                    class="item-name w-full text-xs rounded-lg border border-slate-300 px-2.5 py-1.5 bg-white text-slate-900">
                            </div>
                            <div class="sm:col-span-4">
                                <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Variety / Cultivar</label>
                                <input type="text" name="items[0][variety]" value="Typica / Gayo Highland"
                                    class="item-variety w-full text-xs rounded-lg border border-slate-300 px-2.5 py-1.5 bg-white text-slate-900">
                            </div>
                            <div class="sm:col-span-3">
                                <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Quality Grade</label>
                                <input type="text" name="items[0][grade]" value="Specialty Grade 1"
                                    class="item-grade w-full text-xs rounded-lg border border-slate-300 px-2.5 py-1.5 bg-white text-slate-900">
                            </div>
                            <div class="sm:col-span-3">
                                <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Packaging Type</label>
                                <select name="items[0][package_type]" class="item-packaging w-full text-xs rounded-lg border border-slate-300 px-2 py-1.5 bg-white text-slate-900">
                                    <option value="JUTE_BAG_60KG" selected>Jute Bag 60kg</option>
                                    <option value="GRAINPRO_BAG">GrainPro Hermetic Bag</option>
                                    <option value="BULK_LINER">Bulk Container Liner</option>
                                    <option value="BOX">Export Master Box</option>
                                    <option value="VACUUM_PACK">Vacuum Brick Pack</option>
                                </select>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Bags/Packages</label>
                                <input type="number" min="1" name="items[0][package_count]" value="320"
                                    class="item-count w-full text-xs rounded-lg border border-slate-300 px-2.5 py-1.5 bg-white text-slate-900"
                                    oninput="recalcRow(this)">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Weight (kg)</label>
                                <input type="number" step="0.01" min="0" name="items[0][net_weight]" value="{{ $shipment->weight }}"
                                    class="item-weight w-full text-xs rounded-lg border border-slate-300 px-2.5 py-1.5 bg-white text-slate-900"
                                    oninput="recalcTotals()">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Moisture (%)</label>
                                <input type="number" step="0.1" min="0" max="30" name="items[0][moisture_content]" value="11.5"
                                    class="item-moisture w-full text-xs rounded-lg border border-slate-300 px-2.5 py-1.5 bg-white text-slate-900">
                            </div>
                            <div class="sm:col-span-3">
                                <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">HS Code</label>
                                <input type="text" name="items[0][hs_code]" value="0901.11.10"
                                    class="item-hscode w-full text-xs rounded-lg border border-slate-300 px-2.5 py-1.5 bg-white text-slate-900">
                            </div>
                            <div class="sm:col-span-3">
                                <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Target Temp (°C)</label>
                                <input type="number" step="0.5" name="items[0][temperature_target]" value="18.0"
                                    class="item-temp w-full text-xs rounded-lg border border-slate-300 px-2.5 py-1.5 bg-white text-slate-900">
                            </div>
                            <div class="sm:col-span-6 flex items-center pt-5">
                                <label class="inline-flex items-center text-xs text-slate-600">
                                    <input type="checkbox" name="items[0][is_hazardous]" value="1" class="rounded border-slate-300 text-amber-600 mr-2">
                                    Hazardous Material (DG Sea Freight Cargo)
                                </label>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Form Submission Footer -->
        <div class="flex items-center justify-between pt-4 bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <div class="text-xs text-slate-500">
                Updating shipment recalculates schedule deviations and logs an audit record.
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('shipments.show', $shipment->id) }}" class="px-4 py-2 border border-slate-300 rounded-lg text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2 bg-[#38251D] hover:bg-[#795548] text-white rounded-lg text-xs font-black uppercase tracking-wider shadow-sm transition">
                    Update Shipment Record
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    let itemCount = {{ max(count($shipment->items), 1) }};

    function addCargoItem() {
        const container = document.getElementById('items-container');
        const idx = itemCount++;

        const rowHtml = `
            <div class="cargo-item-row p-4 bg-[#FAF7F0] border border-[#E8D7B9] rounded-xl space-y-3 relative" data-index="${idx}">
                <div class="flex items-center justify-between border-b border-[#E8D7B9]/60 pb-2">
                    <span class="text-xs font-black uppercase tracking-wider text-[#38251D] flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-[#795548]"></span>
                        Cargo Manifest Item #${idx + 1}
                    </span>
                    <button type="button" onclick="removeCargoItem(this)" class="text-xs text-rose-600 hover:text-rose-800 font-bold remove-item-btn">
                        &times; Remove Item
                    </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                    <div class="sm:col-span-3">
                        <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Category *</label>
                        <select name="items[${idx}][product_category]" class="item-category w-full text-xs rounded-lg border border-slate-300 px-2.5 py-1.5 bg-white text-slate-900" onchange="updateCategoryFields(this)">
                            <option value="COFFEE">☕ COFFEE (Kopi)</option>
                            <option value="COCOA">🍫 COCOA (Kakao)</option>
                        </select>
                    </div>

                    <div class="sm:col-span-5">
                        <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Product Name *</label>
                        <input type="text" name="items[${idx}][product_name]" required value="Fermented Cocoa Beans"
                            class="item-name w-full text-xs rounded-lg border border-slate-300 px-2.5 py-1.5 bg-white text-slate-900">
                    </div>

                    <div class="sm:col-span-4">
                        <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Variety / Cultivar</label>
                        <input type="text" name="items[${idx}][variety]" value="Forastero"
                            class="item-variety w-full text-xs rounded-lg border border-slate-300 px-2.5 py-1.5 bg-white text-slate-900">
                    </div>

                    <div class="sm:col-span-3">
                        <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Quality Grade</label>
                        <input type="text" name="items[${idx}][grade]" value="Good Fermented Grade"
                            class="item-grade w-full text-xs rounded-lg border border-slate-300 px-2.5 py-1.5 bg-white text-slate-900">
                    </div>

                    <div class="sm:col-span-3">
                        <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Packaging Type</label>
                        <select name="items[${idx}][package_type]" class="item-packaging w-full text-xs rounded-lg border border-slate-300 px-2 py-1.5 bg-white text-slate-900">
                            <option value="JUTE_BAG_60KG" selected>Jute Bag 60kg</option>
                            <option value="GRAINPRO_BAG">GrainPro Hermetic Bag</option>
                            <option value="BULK_LINER">Bulk Container Liner</option>
                            <option value="BOX">Export Master Box</option>
                            <option value="VACUUM_PACK">Vacuum Brick Pack</option>
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Bags/Packages</label>
                        <input type="number" min="1" name="items[${idx}][package_count]" value="100"
                            class="item-count w-full text-xs rounded-lg border border-slate-300 px-2.5 py-1.5 bg-white text-slate-900"
                            oninput="recalcRow(this)">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Weight (kg)</label>
                        <input type="number" step="0.01" min="0" name="items[${idx}][net_weight]" value="6000"
                            class="item-weight w-full text-xs rounded-lg border border-slate-300 px-2.5 py-1.5 bg-white text-slate-900"
                            oninput="recalcTotals()">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Moisture (%)</label>
                        <input type="number" step="0.1" min="0" max="30" name="items[${idx}][moisture_content]" value="7.5"
                            class="item-moisture w-full text-xs rounded-lg border border-slate-300 px-2.5 py-1.5 bg-white text-slate-900">
                    </div>

                    <div class="sm:col-span-3">
                        <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">HS Code</label>
                        <input type="text" name="items[${idx}][hs_code]" value="1801.00.00"
                            class="item-hscode w-full text-xs rounded-lg border border-slate-300 px-2.5 py-1.5 bg-white text-slate-900">
                    </div>

                    <div class="sm:col-span-3">
                        <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Target Temp (°C)</label>
                        <input type="number" step="0.5" name="items[${idx}][temperature_target]" value="18.0"
                            class="item-temp w-full text-xs rounded-lg border border-slate-300 px-2.5 py-1.5 bg-white text-slate-900">
                    </div>

                    <div class="sm:col-span-6 flex items-center pt-5">
                        <label class="inline-flex items-center text-xs text-slate-600">
                            <input type="checkbox" name="items[${idx}][is_hazardous]" value="1" class="rounded border-slate-300 text-amber-600 mr-2">
                            Hazardous Material (DG Sea Freight Cargo)
                        </label>
                    </div>
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', rowHtml);
        checkRemoveButtons();
        recalcTotals();
    }

    function removeCargoItem(btn) {
        const row = btn.closest('.cargo-item-row');
        row.remove();
        checkRemoveButtons();
        recalcTotals();
    }

    function checkRemoveButtons() {
        const rows = document.querySelectorAll('.cargo-item-row');
        rows.forEach(r => {
            const btn = r.querySelector('.remove-item-btn');
            if (btn) {
                btn.style.display = rows.length > 1 ? 'inline-block' : 'none';
            }
        });
    }

    function recalcRow(input) {
        const row = input.closest('.cargo-item-row');
        const count = parseFloat(input.value) || 0;
        const pkg = row.querySelector('.item-packaging').value;
        const weightInput = row.querySelector('.item-weight');

        if (pkg === 'JUTE_BAG_60KG' || pkg === 'GRAINPRO_BAG') {
            weightInput.value = (count * 60).toFixed(0);
        }
        recalcTotals();
    }

    function recalcTotals() {
        let totalW = 0;
        document.querySelectorAll('.item-weight').forEach(input => {
            totalW += parseFloat(input.value) || 0;
        });
        if (totalW > 0) {
            document.getElementById('total_weight').value = totalW.toFixed(2);
        }
    }

    function updateCategoryFields(select) {
        const row = select.closest('.cargo-item-row');
        const cat = select.value;
        const nameInput = row.querySelector('.item-name');
        const hscodeInput = row.querySelector('.item-hscode');
        const moistureInput = row.querySelector('.item-moisture');

        if (cat === 'COCOA') {
            if (nameInput.value.toLowerCase().includes('coffee') || !nameInput.value) {
                nameInput.value = 'Fermented Cocoa Beans';
            }
            hscodeInput.value = '1801.00.00';
            moistureInput.value = '7.5';
        } else {
            if (nameInput.value.toLowerCase().includes('cocoa') || !nameInput.value) {
                nameInput.value = 'Green Arabica Coffee Beans';
            }
            hscodeInput.value = '0901.11.10';
            moistureInput.value = '11.5';
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        checkRemoveButtons();
    });
</script>
@endsection
