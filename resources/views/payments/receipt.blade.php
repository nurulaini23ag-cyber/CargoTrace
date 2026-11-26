@extends('layouts.app')

@section('title', 'Kuitansi & Commercial Invoice #' . $payment->payment_number . ' — CargoTrace')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">

    <!-- Top Success Alert Banner -->
    <div class="bg-gradient-to-r from-emerald-900 via-slate-900 to-slate-900 rounded-2xl p-6 text-white shadow-xl border border-emerald-600/50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-500 text-slate-950 flex items-center justify-center text-2xl font-black shrink-0 shadow-lg">
                ✓
            </div>
            <div>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-emerald-400 text-slate-950 uppercase tracking-wider">
                    PEMBAYARAN TERVERIFIKASI & SETTLED
                </span>
                <h1 class="text-2xl font-black text-white mt-1">
                    Transaksi Pembelian & Pengiriman Berhasil
                </h1>
                <p class="text-xs text-emerald-200 mt-0.5">
                    Dana escrow telah dikonfirmasi dan armada kargo laut telah otomatis dialokasikan.
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 shrink-0">
            @if($shipment)
                <a href="{{ route('map.index', ['q' => $shipment->shipment_number]) }}"
                   class="px-4 py-2.5 bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs rounded-xl shadow-lg transition flex items-center gap-1.5">
                    <span>🗺️</span>
                    <span>Lacak Kargo di Peta</span>
                </a>
            @endif
            <button type="button" onclick="window.print()"
                    class="px-3.5 py-2.5 bg-white/10 hover:bg-white/20 text-white font-bold text-xs rounded-xl border border-white/20 transition flex items-center gap-1.5 cursor-pointer">
                <span>🖨️</span>
                <span>Cetak Invoice</span>
            </button>
        </div>
    </div>

    <!-- Official Printable Commercial Invoice & Receipt Document -->
    <div class="bg-white rounded-2xl border border-slate-300 p-8 shadow-md space-y-6 print:shadow-none print:border-none print:p-0" id="printable-invoice">
        
        <!-- Document Header -->
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 pb-6 border-b-2 border-slate-900">
            <div>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-slate-900 text-white flex items-center justify-center font-black text-sm">
                        CT
                    </div>
                    <span class="text-xl font-black text-slate-900 tracking-tight">CargoTrace Global Logistics</span>
                </div>
                <div class="text-[11px] text-slate-500 mt-1 leading-relaxed">
                    Kawasan Niaga Maritim Internasional No. 88 &bull; Jakarta Utara, Indonesia<br>
                    Website: cargotrace.id &bull; SWIFT: CTGLIDJA &bull; NPWP: 01.882.901.2-045.000
                </div>
            </div>

            <div class="text-right">
                <span class="text-xs uppercase font-black text-slate-400 block tracking-widest">COMMERCIAL INVOICE & RECEIPT</span>
                <div class="text-xl font-mono font-black text-slate-900 mt-0.5">
                    {{ $payment->payment_number }}
                </div>
                <div class="text-[11px] text-slate-500 mt-0.5 font-mono">
                    Tanggal Pembayaran: <strong>{{ $payment->paid_at ? $payment->paid_at->format('d M Y H:i') : now()->format('d M Y H:i') }}</strong>
                </div>
                <span class="inline-block mt-1 px-2.5 py-0.5 rounded text-[10px] font-black uppercase bg-emerald-100 text-emerald-800 border border-emerald-300">
                    PAID IN FULL (ESCROW SETTLED)
                </span>
            </div>
        </div>

        <!-- Parties Involved: Exporter & Importer (Consignee) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-xs text-slate-700">
            <div class="p-4 bg-slate-50 rounded-xl border border-slate-200/80 space-y-1">
                <span class="text-[10px] uppercase font-bold text-slate-400 block">Eksportir & Penjual (Seller / Exporter):</span>
                <div class="font-black text-slate-900 text-sm">
                    {{ $transaction->exporter?->company_name ?? 'PT Nusantara Agro Exporters' }}
                </div>
                <div class="text-slate-500">{{ $transaction->exporter?->address ?? 'Kawasan Industri Medan II, Belawan, Indonesia' }}</div>
                <div class="text-slate-500">Kontak: {{ $transaction->exporter?->contact_person ?? 'Budi Santoso' }} &bull; {{ $transaction->exporter?->phone ?? '+62-61-882910' }}</div>
            </div>

            <div class="p-4 bg-slate-50 rounded-xl border border-slate-200/80 space-y-1">
                <span class="text-[10px] uppercase font-bold text-slate-400 block">Pembeli & Penerima Kargo (Buyer / Consignee):</span>
                <div class="font-black text-slate-900 text-sm">
                    {{ $orderMeta['consignee_company'] ?? ($transaction->importer?->company_name ?? Auth::user()->name) }}
                </div>
                <div class="text-slate-500">{{ $orderMeta['consignee_address'] ?? 'Waalhaven Oostzijde 44, Rotterdam, Netherlands' }}</div>
                <div class="text-slate-500">Kontak: {{ $payment->user?->name }} &bull; {{ $orderMeta['contact_phone'] ?? '+31-10-892100' }}</div>
            </div>
        </div>

        <!-- Shipping & Maritime Routing Details -->
        <div class="p-4 bg-amber-50/50 rounded-xl border border-amber-200/80 grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
            <div>
                <span class="text-[10px] uppercase font-bold text-slate-400 block">Pelabuhan Muat Asal</span>
                <span class="font-bold text-slate-900">{{ $transaction->departure_port }}</span>
            </div>
            <div>
                <span class="text-[10px] uppercase font-bold text-slate-400 block">Pelabuhan Tujuan</span>
                <span class="font-bold text-slate-900">{{ $transaction->destination_port }}</span>
            </div>
            <div>
                <span class="text-[10px] uppercase font-bold text-slate-400 block">Syarat Perdagangan</span>
                <span class="font-bold text-slate-900">CIF (Cost, Insurance & Freight)</span>
            </div>
            <div>
                <span class="text-[10px] uppercase font-bold text-slate-400 block">Metode Pembayaran</span>
                <span class="font-bold text-slate-900">{{ $payment->payment_channel }}</span>
            </div>
        </div>

        <!-- Itemized Cost Breakdown Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border border-slate-200 rounded-xl overflow-hidden">
                <thead class="bg-slate-100 text-slate-700 font-bold uppercase text-[10px] border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Deskripsi Item / Komoditas</th>
                        <th class="py-3 px-3 text-center">Kuantitas</th>
                        <th class="py-3 px-4 text-right">Harga Satuan (USD)</th>
                        <th class="py-3 px-4 text-right">Total Nilai (USD)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-800">
                    <tr>
                        <td class="py-3.5 px-4">
                            <div class="font-black text-slate-900">{{ $transaction->product?->name ?? 'Komoditas Ekspor' }}</div>
                            <div class="text-[11px] text-slate-500">Grade {{ $transaction->product?->grade ?? 'A' }} &bull; Kadar air max 12.5% &bull; Kemasan Jute Bags hermetik</div>
                        </td>
                        <td class="py-3.5 px-3 text-center font-mono font-bold">{{ $transaction->quantity }} MT</td>
                        <td class="py-3.5 px-4 text-right font-mono">${{ number_format($transaction->unit_price, 2) }}</td>
                        <td class="py-3.5 px-4 text-right font-mono font-bold">${{ number_format($orderMeta['commodity_subtotal'] ?? ($transaction->quantity * $transaction->unit_price), 2) }}</td>
                    </tr>
                    <tr class="bg-slate-50/50">
                        <td class="py-3 px-4">
                            <div class="font-bold text-slate-800">Ocean Container Freight Charge</div>
                            <div class="text-[10px] text-slate-500">Peti kemas 20ft FCL via {{ $shipment?->carrier?->name ?? 'Armada Laut' }}</div>
                        </td>
                        <td class="py-3 px-3 text-center font-mono text-slate-500">{{ $orderMeta['containers'] ?? ceil($transaction->quantity / 20) }} FCL</td>
                        <td class="py-3 px-4 text-right font-mono text-slate-500">$1,850.00</td>
                        <td class="py-3 px-4 text-right font-mono font-semibold">${{ number_format($orderMeta['ocean_freight'] ?? (ceil($transaction->quantity / 20) * 1850), 2) }}</td>
                    </tr>
                    <tr class="bg-slate-50/50">
                        <td class="py-3 px-4">
                            <div class="font-bold text-slate-800">Marine Cargo Insurance Policy</div>
                            <div class="text-[10px] text-slate-500">Institute Cargo Clauses (A) all-risk protection (0.35%)</div>
                        </td>
                        <td class="py-3 px-3 text-center font-mono text-slate-500">1 Polis</td>
                        <td class="py-3 px-4 text-right font-mono text-slate-500">0.35%</td>
                        <td class="py-3 px-4 text-right font-mono font-semibold">${{ number_format($orderMeta['marine_insurance'] ?? ($payment->amount * 0.0035), 2) }}</td>
                    </tr>
                    <tr class="bg-slate-50/50">
                        <td class="py-3 px-4">
                            <div class="font-bold text-slate-800">Port Export Handling & Phytosanitary Inspection</div>
                            <div class="text-[10px] text-slate-500">Pemeriksaan karantina tumbuhan & izin fumigasi ekspor</div>
                        </td>
                        <td class="py-3 px-3 text-center font-mono text-slate-500">1 Dokumen</td>
                        <td class="py-3 px-4 text-right font-mono text-slate-500">$250.00</td>
                        <td class="py-3 px-4 text-right font-mono font-semibold">$250.00</td>
                    </tr>
                </tbody>
                <tfoot class="bg-slate-50 text-slate-900 border-t-2 border-slate-200">
                    <tr>
                        <td colspan="3" class="py-3.5 px-4 text-right font-black uppercase text-xs">
                            Total Tagihan CIF Lunas:
                        </td>
                        <td class="py-3.5 px-4 text-right font-mono font-black text-base text-amber-700">
                            ${{ number_format($payment->amount, 2) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Verification Hash & Auto-Shipment Notification Card -->
        @if($shipment)
        <div class="p-5 bg-gradient-to-r from-slate-900 to-sky-950 rounded-2xl text-white shadow-md border border-sky-800/60 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="space-y-1">
                <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase bg-sky-500/30 text-sky-300 border border-sky-400/30">
                    ARMADA KARGO TERDAFTAR
                </span>
                <div class="text-base font-black text-white">
                    {{ $shipment->vessel_carrier ?? 'MV CargoTrace Oceanic' }} &bull; {{ $shipment->shipment_number }}
                </div>
                <div class="text-xs text-slate-300 font-mono">
                    Nomor Resi / Pelacakan: <strong>{{ $shipment->tracking_number }}</strong> &bull; Status: <span class="text-emerald-400 font-bold">CREATED (PROSES ASAL)</span>
                </div>
            </div>

            <a href="{{ route('map.index', ['q' => $shipment->shipment_number]) }}"
               class="px-5 py-3 bg-blue-600 hover:bg-blue-500 text-white font-black text-xs rounded-xl shadow-lg transition flex items-center justify-center gap-2 shrink-0">
                <span>🌍</span>
                <span>Buka Posisi Kargo di Peta</span>
            </a>
        </div>
        @endif

        <!-- Document Footer & Legal Attribution -->
        <div class="pt-6 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4 text-[10px] text-slate-400">
            <div>
                Hash Otorisasi Gateway: <span class="font-mono text-slate-600">{{ $payment->transaction_reference }}</span><br>
                Dokumen ini sah dan diterbitkan secara elektronik oleh platform CargoTrace.
            </div>

            <div class="text-right">
                <span class="font-bold text-slate-700">Sertifikasi & Kepatuhan:</span><br>
                ICC UCP 600 &bull; Incoterms 2020 &bull; ISO 27001 Security Standard
            </div>
        </div>

    </div>

    <!-- Navigation Bottom CTA -->
    <div class="flex items-center justify-between pt-2">
        <a href="{{ route('orders.index') }}" class="text-xs font-bold text-slate-600 hover:text-slate-900 transition flex items-center gap-1">
            &larr; Lihat Semua Pesanan Saya
        </a>
        <a href="{{ route('catalog.index') }}" class="text-xs font-bold text-amber-700 hover:text-amber-800 transition">
            Belanja Komoditas Lain &rarr;
        </a>
    </div>

</div>
@endsection
