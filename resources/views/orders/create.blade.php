@extends('layouts.app')

@section('title', 'Checkout Pembelian Komoditas — CargoTrace')

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">

    <!-- Breadcrumb -->
    <div class="flex items-center justify-between">
        <nav class="flex items-center space-x-2 text-xs font-semibold text-slate-500">
            <a href="{{ route('catalog.index') }}" class="hover:text-slate-800 transition">Katalog</a>
            <span>&rsaquo;</span>
            <a href="{{ route('catalog.show', $product->slug ?? $product->id) }}" class="hover:text-slate-800 transition">{{ $product->name }}</a>
            <span>&rsaquo;</span>
            <span class="text-slate-800 font-bold">Checkout & Kontrak Dagang</span>
        </nav>
    </div>

    <!-- Main Header -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 rounded-2xl p-6 text-white shadow-xl border border-slate-700/80 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-black bg-[#C49A55] text-slate-950 uppercase tracking-wider">
                KONTRAK PERDAGANGAN CIF INTERNASIONAL
            </span>
            <h1 class="text-2xl md:text-3xl font-black text-white mt-1">
                Formulir Pemesanan & Alokasi Armada Kargo
            </h1>
            <p class="text-xs text-slate-300 mt-0.5">
                Pilih pelabuhan tujuan, armada pelayaran, dan simulasi metode pembayaran gateway untuk memproses kargo Anda.
            </p>
        </div>
        <div class="px-4 py-2 bg-slate-800/90 border border-slate-600 rounded-xl text-right shrink-0">
            <span class="text-[10px] uppercase font-bold text-slate-400 block">Harga Komoditas</span>
            <span class="text-xl font-black text-amber-400">${{ number_format($product->price, 2) }}</span>
            <span class="text-[10px] text-slate-400">/ MT</span>
        </div>
    </div>

    <form action="{{ route('orders.store') }}" method="POST" id="order-form">
        @csrf
        <input type="hidden" name="product_id" value="{{ $product->id }}">

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- Left Column: Order Parameters & Consignee Info (7 cols) -->
            <div class="lg:col-span-7 space-y-5">

                <!-- 1. Commodity & Volume Specification -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-4">
                    <h2 class="text-sm font-black text-slate-900 flex items-center gap-2 pb-2 border-b border-slate-100">
                        <span class="w-6 h-6 rounded-lg bg-amber-100 text-amber-800 flex items-center justify-center text-xs font-black">1</span>
                        Spesifikasi & Volume Pembelian
                    </h2>

                    <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200/60 flex items-center justify-between">
                        <div>
                            <span class="text-xs font-bold text-slate-900">{{ $product->name }}</span>
                            <div class="text-[11px] text-slate-500">Grade {{ $product->grade }} &bull; Asal: {{ $product->originCountry?->name ?? 'Global' }}</div>
                        </div>
                        <span class="px-2.5 py-1 rounded-md text-xs font-black bg-amber-100 text-amber-900 font-mono">
                            ${{ number_format($product->price, 2) }} / MT
                        </span>
                    </div>

                    <div>
                        <label for="quantity_mt" class="block text-xs font-bold text-slate-700 mb-1">
                            Kuantitas Pemesanan (Metrik Ton):
                        </label>
                        <div class="relative">
                            <input type="number" name="quantity_mt" id="quantity_mt"
                                   min="1" max="500" step="1"
                                   value="{{ old('quantity_mt', request('quantity', 20)) }}"
                                   class="w-full text-base font-black bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-slate-900 focus:ring-2 focus:ring-amber-500 pr-16"
                                   oninput="calculateTotal()" required>
                            <span class="absolute right-4 top-3 text-xs font-bold text-slate-500">MT</span>
                        </div>
                        <div class="flex items-center justify-between mt-1.5 text-[11px] text-slate-500">
                            <span>Kapasitas FCL: 20 MT per kontainer 20ft</span>
                            <span id="container-calc-badge" class="font-bold text-amber-700">1 Kontainer 20ft FCL</span>
                        </div>
                    </div>
                </div>

                <!-- 2. Ocean Route & Carrier Selection -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-4">
                    <h2 class="text-sm font-black text-slate-900 flex items-center gap-2 pb-2 border-b border-slate-100">
                        <span class="w-6 h-6 rounded-lg bg-sky-100 text-sky-800 flex items-center justify-center text-xs font-black">2</span>
                        Rute Maritim & Operator Pelayaran
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label for="origin_id" class="block text-xs font-bold text-slate-700 mb-1">
                                Pelabuhan Muat Asal (Origin Port):
                            </label>
                            <select name="origin_id" id="origin_id" class="w-full text-xs font-semibold bg-slate-50 border border-slate-300 rounded-xl px-3 py-2.5 text-slate-900 focus:ring-2 focus:ring-sky-500" required>
                                @foreach($originLocations as $loc)
                                    <option value="{{ $loc->id }}" {{ $loop->first ? 'selected' : '' }}>
                                        🟢 {{ $loc->name }} ({{ $loc->city }}, {{ $loc->country }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="destination_id" class="block text-xs font-bold text-slate-700 mb-1">
                                Pelabuhan Bongkar Tujuan (Destination):
                            </label>
                            <select name="destination_id" id="destination_id" class="w-full text-xs font-semibold bg-slate-50 border border-slate-300 rounded-xl px-3 py-2.5 text-slate-900 focus:ring-2 focus:ring-sky-500" required>
                                @foreach($destinationLocations as $loc)
                                    <option value="{{ $loc->id }}" {{ $loc->city === 'Rotterdam' ? 'selected' : ($loop->first ? 'selected' : '') }}>
                                        🔴 {{ $loc->name }} ({{ $loc->city }}, {{ $loc->country }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="carrier_id" class="block text-xs font-bold text-slate-700 mb-1">
                            Armada Pelayaran (Ocean Carrier):
                        </label>
                        <select name="carrier_id" id="carrier_id" class="w-full text-xs font-semibold bg-slate-50 border border-slate-300 rounded-xl px-3 py-2.5 text-slate-900 focus:ring-2 focus:ring-sky-500" required>
                            @foreach($carriers as $carrier)
                                <option value="{{ $carrier->id }}">
                                    🚢 {{ $carrier->name }} ({{ $carrier->code }}) &bull; Koridor Samudra Internasional
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- 3. Consignee & Buyer Shipping Data -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-4">
                    <h2 class="text-sm font-black text-slate-900 flex items-center gap-2 pb-2 border-b border-slate-100">
                        <span class="w-6 h-6 rounded-lg bg-emerald-100 text-emerald-800 flex items-center justify-center text-xs font-black">3</span>
                        Data Pembeli & Alamat Gudang Penerima (Consignee)
                    </h2>

                    <div class="space-y-3">
                        <div>
                            <label for="consignee_company" class="block text-xs font-bold text-slate-700 mb-1">
                                Nama Perusahaan / Pembeli (Company Name):
                            </label>
                            <input type="text" name="consignee_company" id="consignee_company"
                                   value="{{ old('consignee_company', $buyer->company_name ?? Auth::user()->name . ' International Trade BV') }}"
                                   class="w-full text-xs bg-slate-50 border border-slate-300 rounded-xl px-3 py-2.5 text-slate-900 focus:ring-2 focus:ring-emerald-500" required>
                        </div>

                        <div>
                            <label for="consignee_address" class="block text-xs font-bold text-slate-700 mb-1">
                                Alamat Gudang Penerima & Kode Pos:
                            </label>
                            <textarea name="consignee_address" id="consignee_address" rows="2"
                                      class="w-full text-xs bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-slate-900 focus:ring-2 focus:ring-emerald-500" required>{{ old('consignee_address', $buyer->address ?? 'Waalhaven Oostzijde 44, 3087 BM Rotterdam, Netherlands') }}</textarea>
                        </div>

                        <div>
                            <label for="contact_phone" class="block text-xs font-bold text-slate-700 mb-1">
                                Nomor Kontak Telepon Logistik:
                            </label>
                            <input type="text" name="contact_phone" id="contact_phone"
                                   value="{{ old('contact_phone', $buyer->phone ?? '+31-10-892100') }}"
                                   class="w-full text-xs bg-slate-50 border border-slate-300 rounded-xl px-3 py-2.5 text-slate-900 focus:ring-2 focus:ring-emerald-500" required>
                        </div>
                    </div>
                </div>

                <!-- 4. Payment Gateway Channel Pre-selection -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-4">
                    <h2 class="text-sm font-black text-slate-900 flex items-center gap-2 pb-2 border-b border-slate-100">
                        <span class="w-6 h-6 rounded-lg bg-indigo-100 text-indigo-800 flex items-center justify-center text-xs font-black">4</span>
                        Pilih Kanal Pembayaran Payment Gateway
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                        <label class="payment-option p-3.5 rounded-xl border-2 border-amber-600 bg-amber-50/50 cursor-pointer flex items-start gap-3 transition">
                            <input type="radio" name="payment_method" value="VIRTUAL_ACCOUNT" checked class="mt-1" onchange="toggleChannel(this.value)">
                            <div>
                                <span class="font-black text-slate-900 block">🏦 Virtual Account (VA)</span>
                                <span class="text-[11px] text-slate-500 block mt-0.5">Bank Mandiri, BCA, BNI, BRI. Nomor VA instan dengan konfirmasi otomatis.</span>
                            </div>
                        </label>

                        <label class="payment-option p-3.5 rounded-xl border border-slate-200 hover:border-slate-400 bg-white cursor-pointer flex items-start gap-3 transition">
                            <input type="radio" name="payment_method" value="CREDIT_CARD" class="mt-1" onchange="toggleChannel(this.value)">
                            <div>
                                <span class="font-black text-slate-900 block">💳 Kartu Kredit / Debit (3DS)</span>
                                <span class="text-[11px] text-slate-500 block mt-0.5">Visa, Mastercard dengan verifikasi 3D Secure OTP perbankan.</span>
                            </div>
                        </label>

                        <label class="payment-option p-3.5 rounded-xl border border-slate-200 hover:border-slate-400 bg-white cursor-pointer flex items-start gap-3 transition">
                            <input type="radio" name="payment_method" value="LETTER_OF_CREDIT" class="mt-1" onchange="toggleChannel(this.value)">
                            <div>
                                <span class="font-black text-slate-900 block">📜 Letter of Credit (L/C)</span>
                                <span class="text-[11px] text-slate-500 block mt-0.5">SWIFT MT700 Irrevocable Documentary Credit untuk transaksi ekspor besar.</span>
                            </div>
                        </label>

                        <label class="payment-option p-3.5 rounded-xl border border-slate-200 hover:border-slate-400 bg-white cursor-pointer flex items-start gap-3 transition">
                            <input type="radio" name="payment_method" value="QRIS" class="mt-1" onchange="toggleChannel(this.value)">
                            <div>
                                <span class="font-black text-slate-900 block">📱 QRIS Instant Payment</span>
                                <span class="text-[11px] text-slate-500 block mt-0.5">Scan kode QRIS instan melalui mobile banking atau e-wallet.</span>
                            </div>
                        </label>
                    </div>

                    <!-- Bank channel options for VA -->
                    <div id="va-bank-select" class="pt-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Bank Virtual Account:</label>
                        <select name="bank_channel" class="w-full text-xs font-semibold bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-slate-900">
                            <option value="Bank Mandiri">Bank Mandiri (Prefix: 88200)</option>
                            <option value="Bank BCA">Bank Central Asia - BCA (Prefix: 80777)</option>
                            <option value="Bank BNI">Bank Negara Indonesia - BNI (Prefix: 98801)</option>
                            <option value="Bank BRI">Bank Rakyat Indonesia - BRI (Prefix: 88888)</option>
                        </select>
                    </div>
                </div>

            </div>

            <!-- Right Column: Live CIF Cost Breakdown & Submit (5 cols) -->
            <div class="lg:col-span-5 space-y-4">
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-md sticky top-20 space-y-5">
                    <h3 class="text-sm font-black text-slate-900 pb-2 border-b border-slate-100 flex items-center justify-between">
                        <span>📋 Rincian Kontrak CIF</span>
                        <span class="text-xs px-2 py-0.5 rounded bg-amber-100 text-amber-900 font-bold">Incoterm CIF</span>
                    </h3>

                    <div class="space-y-2 text-xs">
                        <div class="flex justify-between text-slate-600">
                            <span>Harga Komoditas FOB:</span>
                            <span class="font-mono font-bold text-slate-800" id="subtotal-val">$0.00</span>
                        </div>
                        <div class="flex justify-between text-slate-600">
                            <span>Freight Peti Kemas Laut:</span>
                            <span class="font-mono font-bold text-slate-800" id="freight-val">$1,850.00</span>
                        </div>
                        <div class="flex justify-between text-slate-600">
                            <span>Asuransi Marine Cargo (0.35%):</span>
                            <span class="font-mono font-bold text-slate-800" id="insurance-val">$0.00</span>
                        </div>
                        <div class="flex justify-between text-slate-600">
                            <span>Biaya Pelabuhan & Fitosanitasi:</span>
                            <span class="font-mono font-bold text-slate-800">$250.00</span>
                        </div>

                        <div class="pt-3 border-t border-slate-200 flex items-baseline justify-between">
                            <div>
                                <span class="text-sm font-black text-slate-900 block">Total Tagihan CIF:</span>
                                <span class="text-[10px] text-slate-400 font-normal">Termasuk biaya pengapalan & asuransi</span>
                            </div>
                            <div class="text-right">
                                <span class="text-2xl font-black text-amber-700 font-mono" id="total-val">$0.00</span>
                                <span class="text-[10px] text-slate-500 block font-mono" id="idr-approx">&asymp; Rp 0</span>
                            </div>
                        </div>
                    </div>

                    <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-200 text-xs text-emerald-800 space-y-1">
                        <div class="font-bold flex items-center gap-1">
                            <span>✓</span> Otomatis Terbit Pengiriman & Pelacakan
                        </div>
                        <p class="text-[11px] text-emerald-700 leading-tight">
                            Setelah pembayaran selesai disimulasikan, sistem CargoTrace akan langsung menerbitkan nomor pelacakan unik dan plotting kapal di peta global.
                        </p>
                    </div>

                    <button type="submit"
                            class="w-full py-3.5 px-4 bg-amber-600 hover:bg-amber-700 text-white font-black text-sm rounded-xl shadow-lg transition flex items-center justify-center gap-2 cursor-pointer">
                        <span>🔒</span>
                        <span>Konfirmasi Pesanan & Buka Pembayaran</span>
                    </button>

                    <div class="text-center text-[10px] text-slate-400">
                        Enkripsi TLS 256-bit &bull; Simulasi Keamanan Standar PCI-DSS Level 1
                    </div>
                </div>
            </div>

        </div>
    </form>

</div>

<script>
    const pricePerMt = {{ (float)$product->price }};

    function calculateTotal() {
        const qtyInput = document.getElementById('quantity_mt');
        let qty = parseFloat(qtyInput.value) || 1;
        if (qty < 1) qty = 1;

        const subtotal = qty * pricePerMt;
        const containers = Math.ceil(qty / 20.0);
        const freight = containers * 1850.00;
        const insurance = (subtotal + freight) * 0.0035;
        const docs = 250.00;
        const total = subtotal + freight + insurance + docs;
        const idrTotal = total * 16250; // Approx exchange rate

        document.getElementById('subtotal-val').innerText = '$' + subtotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('freight-val').innerText = '$' + freight.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ` (${containers} FCL)`;
        document.getElementById('insurance-val').innerText = '$' + insurance.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('total-val').innerText = '$' + total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('idr-approx').innerText = '≈ Rp ' + Math.round(idrTotal).toLocaleString('id-ID');
        document.getElementById('container-calc-badge').innerText = `${containers} Kontainer 20ft FCL (${qty} MT)`;
    }

    function toggleChannel(method) {
        document.querySelectorAll('.payment-option').forEach(el => {
            el.classList.remove('border-amber-600', 'bg-amber-50/50');
            el.classList.add('border-slate-200', 'bg-white');
        });
        const activeRadio = document.querySelector(`input[name="payment_method"][value="${method}"]`);
        if (activeRadio) {
            const container = activeRadio.closest('.payment-option');
            container.classList.add('border-amber-600', 'bg-amber-50/50');
            container.classList.remove('border-slate-200', 'bg-white');
        }

        const vaBox = document.getElementById('va-bank-select');
        if (method === 'VIRTUAL_ACCOUNT') {
            vaBox.style.display = 'block';
        } else {
            vaBox.style.display = 'none';
        }
    }

    document.addEventListener('DOMContentLoaded', calculateTotal);
</script>
@endsection
