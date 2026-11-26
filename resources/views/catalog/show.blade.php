@extends('layouts.app')

@section('title', $product->name . ' — CargoTrace Marketplace')

@section('content')
<div class="space-y-6">

    <!-- Breadcrumb & Back -->
    <div class="flex items-center justify-between">
        <nav class="flex items-center space-x-2 text-xs font-semibold text-slate-500">
            <a href="{{ route('catalog.index') }}" class="hover:text-slate-800 transition">Katalog</a>
            <span>&rsaquo;</span>
            <span class="text-slate-800">{{ $product->name }}</span>
        </nav>
        <a href="{{ route('catalog.index') }}" class="text-xs font-bold text-amber-700 hover:text-amber-800 transition flex items-center gap-1">
            &larr; Kembali ke Katalog
        </a>
    </div>

    @php
        $isCocoa = str_contains(strtolower($product->category->name ?? ''), 'cocoa') || str_contains(strtolower($product->category->name ?? ''), 'kakao');
        $specs = is_array($product->specifications) ? $product->specifications : json_decode($product->specifications ?? '[]', true);
    @endphp

    <!-- Main Product Card & Purchasing Section -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Left Column: Specs & Laboratory Analysis (7 cols) -->
        <div class="lg:col-span-7 space-y-6">
            
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider
                        {{ $isCocoa ? 'bg-amber-100 text-amber-900 border border-amber-300' : 'bg-orange-100 text-orange-900 border border-orange-300' }}">
                        {{ $isCocoa ? '🍫 Biji Kakao Ekspor' : '☕ Biji Kopi Spesialti' }}
                    </span>
                    <span class="px-2.5 py-1 rounded-full text-xs font-black bg-slate-900 text-amber-300">
                        Grade {{ $product->grade }}
                    </span>
                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-300">
                        🌍 Asal: {{ $product->originCountry?->name ?? 'Global' }}
                    </span>
                </div>

                <h1 class="text-2xl md:text-3xl font-black text-slate-900 tracking-tight">
                    {{ $product->name }}
                </h1>

                <p class="text-sm text-slate-600 leading-relaxed">
                    {{ $product->description }}
                </p>

                <!-- Technical Quality Specification Table -->
                <div class="pt-4 border-t border-slate-100">
                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-400 mb-3">
                        Parameter Kualitas Standar Internasional (SGS / ICO / ICCO)
                    </h3>
                    
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                            <span class="text-[10px] uppercase font-bold text-slate-400 block">Kadar Air (Moisture)</span>
                            <span class="text-base font-black text-slate-800">{{ $specs['moisture'] ?? '11.5%' }}</span>
                            <span class="text-[10px] text-emerald-600 font-semibold block">Max standar 12.5%</span>
                        </div>
                        <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                            <span class="text-[10px] uppercase font-bold text-slate-400 block">Tingkat Cacat (Defects)</span>
                            <span class="text-base font-black text-slate-800">{{ $specs['defect_rate'] ?? '2.5%' }}</span>
                            <span class="text-[10px] text-emerald-600 font-semibold block">Primary grade 1</span>
                        </div>
                        <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                            <span class="text-[10px] uppercase font-bold text-slate-400 block">Screen Size / Bean Count</span>
                            <span class="text-base font-black text-slate-800">{{ $specs['screen_size'] ?? '18/19' }}</span>
                            <span class="text-[10px] text-slate-500 font-semibold block">Seragam & Terkalibrasi</span>
                        </div>
                        <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                            <span class="text-[10px] uppercase font-bold text-slate-400 block">Tahun Panen (Crop Year)</span>
                            <span class="text-base font-black text-slate-800">{{ $specs['crop_year'] ?? '2026 Fresh Crop' }}</span>
                            <span class="text-[10px] text-emerald-600 font-semibold block">Hasil Panen Terbaru</span>
                        </div>
                        <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                            <span class="text-[10px] uppercase font-bold text-slate-400 block">Kemasan Ekspor</span>
                            <span class="text-base font-black text-slate-800">{{ $specs['packaging'] ?? 'Jute Bag 60kg + GrainPro' }}</span>
                            <span class="text-[10px] text-slate-500 font-semibold block">Aman & Hermetik</span>
                        </div>
                        <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                            <span class="text-[10px] uppercase font-bold text-slate-400 block">Pelabuhan Muat Asal</span>
                            <span class="text-base font-black text-slate-800">{{ $specs['origin_port'] ?? 'Belawan / Tanjung Priok' }}</span>
                            <span class="text-[10px] text-slate-500 font-semibold block">Fasilitas Kontainer Penuh</span>
                        </div>
                    </div>
                </div>

                <!-- Certifications list -->
                @if(!empty($specs['certifications']) && is_array($specs['certifications']))
                <div class="pt-4 border-t border-slate-100">
                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-400 mb-2">
                        Sertifikasi Keberlanjutan & Kepatuhan Perdagangan
                    </h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($specs['certifications'] as $cert)
                            <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 text-xs font-bold">
                                <span>🌿</span>
                                <span>{{ $cert }} Certified</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <!-- Guarantee & Escrow Safety Notice -->
            <div class="bg-gradient-to-r from-blue-900 to-slate-900 rounded-2xl p-5 text-white shadow-md border border-blue-700/50 flex items-start gap-4">
                <div class="text-3xl shrink-0 p-2 bg-blue-800/60 rounded-xl">🛡️</div>
                <div class="space-y-1">
                    <h4 class="font-black text-sm text-white">Jaminan Keamanan Escrow & Kargo Internasional</h4>
                    <p class="text-xs text-blue-200 leading-relaxed">
                        Dana pembayaran ditahan di rekening penampung (Escrow Account) bank berlisensi sampai dokumen Bill of Lading (B/L) dan sertifikat fitosanitasi diterbitkan secara sah oleh otoritas pelabuhan. Kargo Anda otomatis diasuransikan polis Marine Cargo Institute Clauses A.
                    </p>
                </div>
            </div>
        </div>

        <!-- Right Column: Purchasing CTA & Live Estimator (5 cols) -->
        <div class="lg:col-span-5 space-y-4">
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-md sticky top-20 space-y-5">
                <div>
                    <span class="text-xs uppercase font-bold text-slate-400 block">Harga Satuan FOB</span>
                    <div class="text-3xl font-black text-slate-900 mt-0.5">
                        ${{ number_format($product->price, 2) }}
                        <span class="text-sm font-normal text-slate-500">/ Metrik Ton</span>
                    </div>
                    <span class="text-xs text-emerald-600 font-semibold block mt-1">
                        ✓ Stok Terverifikasi: {{ number_format($product->stock_availability, 0) }} MT
                    </span>
                </div>

                <!-- Instant Estimator Calculator -->
                <div class="p-4 bg-slate-50 rounded-xl border border-slate-200/80 space-y-3">
                    <label class="text-xs font-bold text-slate-700 block">
                        Simulasi Kuantitas Pembelian (Metrik Ton):
                    </label>
                    <div class="flex items-center gap-2">
                        <input type="number" id="calc-qty" min="1" max="1000" value="20"
                               class="w-full text-sm font-black bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-900 focus:ring-2 focus:ring-amber-500 text-center"
                               oninput="recalculateEstimator()">
                        <span class="text-xs font-bold text-slate-500 shrink-0">MT (Ton)</span>
                    </div>
                    <div class="text-[11px] text-slate-500">
                        1 Kontainer 20ft FCL &asymp; 18 - 20 Metrik Ton
                    </div>

                    <div class="pt-3 border-t border-slate-200 space-y-1.5 text-xs text-slate-600">
                        <div class="flex justify-between">
                            <span>Subtotal Komoditas:</span>
                            <span class="font-bold text-slate-800" id="calc-subtotal">$0</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Estimasi Freight Laut (1 FCL):</span>
                            <span class="font-bold text-slate-800" id="calc-freight">$1,850.00</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Asuransi Kargo (0.35%):</span>
                            <span class="font-bold text-slate-800" id="calc-insurance">$0</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Dokumen & Fitosanitasi:</span>
                            <span class="font-bold text-slate-800">$250.00</span>
                        </div>
                        <div class="pt-2 border-t border-slate-200 flex justify-between text-sm font-black text-slate-900">
                            <span>Estimasi Total CIF:</span>
                            <span class="text-amber-700 font-mono" id="calc-total">$0</span>
                        </div>
                    </div>
                </div>

                <!-- Checkout CTA Button -->
                <div class="space-y-2">
                    <a id="btn-proceed-order"
                       href="{{ route('orders.create', ['product_id' => $product->id, 'quantity' => 20]) }}"
                       class="w-full py-3.5 px-4 bg-amber-600 hover:bg-amber-700 text-white font-black text-sm rounded-xl text-center shadow-lg transition flex items-center justify-center gap-2">
                        <span>🛒</span>
                        <span>Beli Sekarang & Buka Payment Gateway</span>
                    </a>
                    <p class="text-[11px] text-slate-400 text-center">
                        Simulasi pembayaran lengkap: Virtual Account, Kartu Kredit (3DS), L/C SWIFT, dan QRIS.
                    </p>
                </div>
            </div>
        </div>

    </div>

</div>

<script>
    const pricePerMt = {{ (float)$product->price }};

    function recalculateEstimator() {
        const qtyInput = document.getElementById('calc-qty');
        let qty = parseFloat(qtyInput.value) || 1;
        if (qty < 1) qty = 1;

        const subtotal = qty * pricePerMt;
        const containers = Math.ceil(qty / 20.0);
        const freight = containers * 1850.00;
        const insurance = (subtotal + freight) * 0.0035;
        const docs = 250.00;
        const total = subtotal + freight + insurance + docs;

        document.getElementById('calc-subtotal').innerText = '$' + subtotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('calc-freight').innerText = '$' + freight.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ` (${containers} Kontainer)`;
        document.getElementById('calc-insurance').innerText = '$' + insurance.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('calc-total').innerText = '$' + total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        const btn = document.getElementById('btn-proceed-order');
        btn.href = `{{ route('orders.create') }}?product_id={{ $product->id }}&quantity=${qty}`;
    }

    document.addEventListener('DOMContentLoaded', recalculateEstimator);
</script>
@endsection
