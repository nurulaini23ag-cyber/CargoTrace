@extends('layouts.app')

@section('title', 'Create Sea Freight Shipment - Cocoa & Coffee Logistics')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-amber-900/60 uppercase tracking-wider mb-1">
                <span>Shipment Management</span>
                <span>&rsaquo;</span>
                <span class="text-amber-900">Maritime Booking</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <svg class="w-7 h-7 text-[#795548]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                Create Cocoa & Coffee Shipment
            </h1>
            <p class="text-sm text-slate-500">Register ocean freight booking, container manifest, commodity specs, and target schedule</p>
        </div>
        <a href="{{ route('shipments.index') }}" class="inline-flex items-center text-xs font-bold text-slate-600 hover:text-slate-900 px-3 py-2 border border-slate-300 rounded-lg hover:bg-slate-50 transition">
            &larr; Back to Directory
        </a>
    </div>

    <!-- Quick Preset Banner -->
    <div class="bg-gradient-to-r from-[#FAF7F0] via-[#E8D7B9]/30 to-[#FAF7F0] p-4 rounded-xl border border-[#E8D7B9] shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
            <div>
                <span class="text-xs font-black uppercase tracking-wider text-[#38251D] flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-[#C49A55]"></span>
                    One-Click Commodity Trade Presets
                </span>
                <p class="text-xs text-slate-600 mt-0.5">Quickly pre-fill realistic export parameters for high-volume coffee or cocoa trade routes</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" onclick="applyPreset('coffee_gayo')" class="px-2.5 py-1.5 bg-white hover:bg-amber-50 text-[#38251D] border border-amber-300 rounded-lg text-xs font-bold shadow-sm transition">
                    ☕ Aceh Gayo Arabica (Belawan &rarr; Rotterdam)
                </button>
                <button type="button" onclick="applyPreset('cocoa_ghana')" class="px-2.5 py-1.5 bg-white hover:bg-amber-50 text-[#795548] border border-amber-300 rounded-lg text-xs font-bold shadow-sm transition">
                    🍫 Ghana Forastero (Tema &rarr; Hamburg)
                </button>
                <button type="button" onclick="applyPreset('coffee_robusta')" class="px-2.5 py-1.5 bg-white hover:bg-amber-50 text-[#38251D] border border-amber-300 rounded-lg text-xs font-bold shadow-sm transition">
                    ☕ Lampung Robusta (Tanjung Priok &rarr; Genoa)
                </button>
            </div>
        </div>
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

    <form action="{{ route('shipments.store') }}" method="POST" id="shipment-form" class="space-y-6">
        @csrf

        <!-- 1. Parties & Ocean Freight Routing -->
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h2 class="text-sm font-bold uppercase tracking-wider text-slate-900 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-[#38251D] text-white flex items-center justify-center text-xs">1</span>
                    Trade Parties & Maritime Route
                </h2>
                <span class="text-xs text-slate-400">Strict Sea Freight Rules</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Customer / Consignee -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Customer / Consignee *</label>
                    <select name="customer_id" id="customer_id" required class="w-full text-sm rounded-lg border border-slate-300 px-3 py-2 text-slate-900 focus:border-amber-600 focus:ring-1 focus:ring-amber-600">
                        <option value="">Select Buyer / Consignee...</option>
                        @foreach($customers as $cust)
                            <option value="{{ $cust->id }}" {{ old('customer_id') == $cust->id ? 'selected' : '' }}>
                                {{ $cust->name }} ({{ $cust->company_name ?? 'Trading House' }}) - {{ $cust->email }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Carrier -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Ocean Carrier / Shipping Line *</label>
                    <select name="carrier_id" id="carrier_id" required class="w-full text-sm rounded-lg border border-slate-300 px-3 py-2 text-slate-900 focus:border-amber-600 focus:ring-1 focus:ring-amber-600">
                        <option value="">Select Ocean Carrier...</option>
                        @foreach($carriers as $car)
                            <option value="{{ $car->id }}" {{ old('carrier_id') == $car->id ? 'selected' : '' }}>
                                {{ $car->name }} ({{ $car->code }}) - Vessel Maritime Line
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Origin Port -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Port of Origin (POL) *</label>
                    <select name="origin_id" id="origin_id" required class="w-full text-sm rounded-lg border border-slate-300 px-3 py-2 text-slate-900 focus:border-amber-600 focus:ring-1 focus:ring-amber-600">
                        <option value="">Select Origin Port...</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" {{ old('origin_id') == $loc->id ? 'selected' : '' }}>
                                {{ $loc->name }} ({{ $loc->code }}) - {{ $loc->city }}, {{ $loc->country }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Destination Port -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Port of Destination (POD) *</label>
                    <select name="destination_id" id="destination_id" required class="w-full text-sm rounded-lg border border-slate-300 px-3 py-2 text-slate-900 focus:border-amber-600 focus:ring-1 focus:ring-amber-600">
                        <option value="">Select Destination Port...</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" {{ old('destination_id') == $loc->id ? 'selected' : '' }}>
                                {{ $loc->name }} ({{ $loc->code }}) - {{ $loc->city }}, {{ $loc->country }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-slate-400 mt-1">Destination port must be different from origin port.</p>
                </div>
            </div>
        </div>

        <!-- 2. Cargo Commodity Overview -->
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h2 class="text-sm font-bold uppercase tracking-wider text-slate-900 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-[#795548] text-white flex items-center justify-center text-xs">2</span>
                    Commodity & Container Specification
                </h2>
                <span class="text-xs text-slate-400">Coffee / Cocoa Sea Freight</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Commodity Cargo Title *</label>
                    <input type="text" name="cargo_type" id="cargo_type" required value="{{ old('cargo_type', 'Green Arabica Coffee Beans - Grade 1') }}"
                        placeholder="e.g. Green Arabica Coffee Beans or Fermented Cocoa Beans"
                        class="w-full text-sm rounded-lg border border-slate-300 px-3 py-2 text-slate-900 focus:border-amber-600 focus:ring-1 focus:ring-amber-600">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Total Net Weight (kg) *</label>
                    <input type="number" step="0.01" min="0.01" name="weight" id="total_weight" required value="{{ old('weight', '19200') }}"
                        class="w-full text-sm rounded-lg border border-slate-300 px-3 py-2 text-slate-900 focus:border-amber-600 focus:ring-1 focus:ring-amber-600">
                    <p class="text-[11px] text-slate-400 mt-1">Standard 20ft container: ~19,200 kg (320 bags @ 60kg)</p>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Total Volume (m³) *</label>
                    <input type="number" step="0.01" min="0.01" name="volume" id="total_volume" required value="{{ old('volume', '33.2') }}"
                        class="w-full text-sm rounded-lg border border-slate-300 px-3 py-2 text-slate-900 focus:border-amber-600 focus:ring-1 focus:ring-amber-600">
                    <p class="text-[11px] text-slate-400 mt-1">Standard 20ft dry box: ~33.2 m³</p>
                </div>
            </div>
        </div>

        <!-- 3. Maritime Voyage Schedule & Handling -->
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h2 class="text-sm font-bold uppercase tracking-wider text-slate-900 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-[#C49A55] text-white flex items-center justify-center text-xs">3</span>
                    Voyage Schedule & Handling Protocol
                </h2>
                <span class="text-xs text-slate-400">Deterministic ETA Monitoring</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Expected Departure (ETD) *</label>
                    <input type="datetime-local" name="expected_departure" id="expected_departure" required
                        value="{{ old('expected_departure', now()->addDay()->format('Y-m-d\TH:i')) }}"
                        class="w-full text-sm rounded-lg border border-slate-300 px-3 py-2 text-slate-900 focus:border-amber-600 focus:ring-1 focus:ring-amber-600">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Expected Arrival (ETA) *</label>
                    <input type="datetime-local" name="expected_arrival" id="expected_arrival" required
                        value="{{ old('expected_arrival', now()->addDays(24)->format('Y-m-d\TH:i')) }}"
                        class="w-full text-sm rounded-lg border border-slate-300 px-3 py-2 text-slate-900 focus:border-amber-600 focus:ring-1 focus:ring-amber-600">
                    <p class="text-[11px] text-slate-400 mt-1">Must be strictly after expected departure date.</p>
                </div>
            </div>

            <div class="mt-2">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Cargo Handling & Quality Preservation Instructions</label>
                <textarea name="special_instructions" id="special_instructions" rows="2"
                    placeholder="e.g. Stowed in ventilated food-grade dry container, relative humidity &lt; 65%, use GrainPro moisture barrier bags, keep away from engine heat."
                    class="w-full text-sm rounded-lg border border-slate-300 px-3 py-2 text-slate-900 focus:border-amber-600 focus:ring-1 focus:ring-amber-600">{{ old('special_instructions', 'Ventilated dry container, GrainPro Hermetic Bags, maintain relative humidity below 65%.') }}</textarea>
            </div>
        </div>

        <!-- 4. Detailed Cargo Manifest Items (Dynamic 1-to-many) -->
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
                <!-- Item row template rendered below -->
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
                        <!-- Category -->
                        <div class="sm:col-span-3">
                            <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Category *</label>
                            <select name="items[0][product_category]" class="item-category w-full text-xs rounded-lg border border-slate-300 px-2.5 py-1.5 bg-white text-slate-900" onchange="updateCategoryFields(this)">
                                <option value="COFFEE" selected>☕ COFFEE (Kopi)</option>
                                <option value="COCOA">🍫 COCOA (Kakao)</option>
                            </select>
                        </div>

                        <!-- Product Name -->
                        <div class="sm:col-span-5">
                            <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Product Name *</label>
                            <input type="text" name="items[0][product_name]" required value="Green Arabica Coffee Beans"
                                class="item-name w-full text-xs rounded-lg border border-slate-300 px-2.5 py-1.5 bg-white text-slate-900">
                        </div>

                        <!-- Variety -->
                        <div class="sm:col-span-4">
                            <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Variety / Cultivar</label>
                            <input type="text" name="items[0][variety]" value="Typica / Gayo Highland"
                                placeholder="e.g. Typica, Forastero, Criollo"
                                class="item-variety w-full text-xs rounded-lg border border-slate-300 px-2.5 py-1.5 bg-white text-slate-900">
                        </div>

                        <!-- Grade -->
                        <div class="sm:col-span-3">
                            <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Quality Grade</label>
                            <input type="text" name="items[0][grade]" value="Specialty Grade 1 (SCA 86.5)"
                                placeholder="e.g. Grade 1, GFG, SCA 85+"
                                class="item-grade w-full text-xs rounded-lg border border-slate-300 px-2.5 py-1.5 bg-white text-slate-900">
                        </div>

                        <!-- Packaging -->
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

                        <!-- Package Count -->
                        <div class="sm:col-span-2">
                            <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Bags/Packages</label>
                            <input type="number" min="1" name="items[0][package_count]" value="320"
                                class="item-count w-full text-xs rounded-lg border border-slate-300 px-2.5 py-1.5 bg-white text-slate-900"
                                oninput="recalcRow(this)">
                        </div>

                        <!-- Net Weight (kg) -->
                        <div class="sm:col-span-2">
                            <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Weight (kg)</label>
                            <input type="number" step="0.01" min="0" name="items[0][net_weight]" value="19200"
                                class="item-weight w-full text-xs rounded-lg border border-slate-300 px-2.5 py-1.5 bg-white text-slate-900"
                                oninput="recalcTotals()">
                        </div>

                        <!-- Moisture Content (%) -->
                        <div class="sm:col-span-2">
                            <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Moisture (%)</label>
                            <input type="number" step="0.1" min="0" max="30" name="items[0][moisture_content]" value="11.5"
                                placeholder="e.g. 11.5%"
                                class="item-moisture w-full text-xs rounded-lg border border-slate-300 px-2.5 py-1.5 bg-white text-slate-900">
                        </div>

                        <!-- HS Code & Reefer Temp -->
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
            </div>
        </div>

        <!-- Form Submission Footer -->
        <div class="flex items-center justify-between pt-4 bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <div class="text-xs text-slate-500">
                Initial Status will be automatically recorded as <span class="font-bold text-slate-800">CREATED</span> with audit tracking.
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('shipments.index') }}" class="px-4 py-2 border border-slate-300 rounded-lg text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2 bg-[#38251D] hover:bg-[#795548] text-white rounded-lg text-xs font-black uppercase tracking-wider shadow-sm transition">
                    Register Maritime Shipment
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    let itemCount = 1;

    function applyPreset(presetKey) {
        const presets = {
            coffee_gayo: {
                cargo_type: 'Green Arabica Coffee Beans - Specialty Grade 1',
                weight: 19200,
                volume: 33.2,
                instructions: 'Ventilated dry container, GrainPro Hermetic Bags, maintain relative humidity < 65%, temperature target 18°C.',
                category: 'COFFEE',
                product_name: 'Sumatra Gayo Highland Arabica',
                variety: 'Typica & Tim Tim',
                grade: 'Specialty Grade 1 (SCA 86.5)',
                packaging: 'JUTE_BAG_60KG',
                package_count: 320,
                moisture: 11.5,
                temp: 18.0,
                hscode: '0901.11.10'
            },
            cocoa_ghana: {
                cargo_type: 'Fermented Cocoa Beans - Good Fermented Grade',
                weight: 20000,
                volume: 34.5,
                instructions: 'Stowed in clean dry container with kraft paper lining, moisture traps inserted, temperature 16°C-20°C.',
                category: 'COCOA',
                product_name: 'Raw Fermented Cocoa Beans',
                variety: 'Forastero Ghana',
                grade: 'Good Fermented Grade (GFG)',
                packaging: 'JUTE_BAG_60KG',
                package_count: 333,
                moisture: 7.2,
                temp: 18.0,
                hscode: '1801.00.00'
            },
            coffee_robusta: {
                cargo_type: 'Fine Robusta Green Coffee - Grade 1 Export',
                weight: 19800,
                volume: 33.5,
                instructions: 'Food grade standard dry box, moisture desiccant bags installed, keep away from vessel engine bulkhead.',
                category: 'COFFEE',
                product_name: 'Lampung Fine Robusta Green Beans',
                variety: 'Robusta Java/Lampung',
                grade: 'Export Grade 1 Defect < 11',
                packaging: 'JUTE_BAG_60KG',
                package_count: 330,
                moisture: 12.0,
                temp: 20.0,
                hscode: '0901.11.20'
            }
        };

        const data = presets[presetKey];
        if (!data) return;

        document.getElementById('cargo_type').value = data.cargo_type;
        document.getElementById('total_weight').value = data.weight;
        document.getElementById('total_volume').value = data.volume;
        document.getElementById('special_instructions').value = data.instructions;

        // Apply to first item
        const firstRow = document.querySelector('.cargo-item-row');
        if (firstRow) {
            firstRow.querySelector('.item-category').value = data.category;
            firstRow.querySelector('.item-name').value = data.product_name;
            firstRow.querySelector('.item-variety').value = data.variety;
            firstRow.querySelector('.item-grade').value = data.grade;
            firstRow.querySelector('.item-packaging').value = data.packaging;
            firstRow.querySelector('.item-count').value = data.package_count;
            firstRow.querySelector('.item-weight').value = data.weight;
            firstRow.querySelector('.item-moisture').value = data.moisture;
            firstRow.querySelector('.item-temp').value = data.temp;
            firstRow.querySelector('.item-hscode').value = data.hscode;
        }
    }

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
                            placeholder="e.g. Typica, Forastero, Criollo"
                            class="item-variety w-full text-xs rounded-lg border border-slate-300 px-2.5 py-1.5 bg-white text-slate-900">
                    </div>

                    <div class="sm:col-span-3">
                        <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Quality Grade</label>
                        <input type="text" name="items[${idx}][grade]" value="Good Fermented Grade"
                            placeholder="e.g. Grade 1, GFG, SCA 85+"
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
</script>
@endsection
