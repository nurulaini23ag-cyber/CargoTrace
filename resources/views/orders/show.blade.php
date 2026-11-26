@extends('layouts.app')

@section('title', 'Rincian Pesanan ' . $transaction->transaction_id . ' — CargoTrace')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">

    <!-- Breadcrumb -->
    <div class="flex items-center justify-between">
        <nav class="flex items-center space-x-2 text-xs font-semibold text-slate-500">
            <a href="{{ route('orders.index') }}" class="hover:text-slate-800 transition">Pesanan</a>
            <span>&rsaquo;</span>
            <span class="text-slate-800 font-bold">{{ $transaction->transaction_id }}</span>
        </nav>
        <a href="{{ route('orders.index') }}" class="text-xs font-bold text-slate-600 hover:text-slate-900 transition">
            &larr; Kembali ke Daftar Pesanan
        </a>
    </div>

    <!-- Header Card -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 rounded-2xl p-6 text-white shadow-xl border border-slate-700 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="font-mono text-xs text-amber-400 font-bold">{{ $transaction->transaction_id }}</span>
                <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase
                    {{ $transaction->payment?->isSuccess() ? 'bg-emerald-500 text-slate-950' : 'bg-amber-500 text-slate-950' }}">
                    {{ $transaction->payment?->isSuccess() ? 'LUNAS (CONFIRMED)' : 'AWAITING PAYMENT' }}
                </span>
            </div>
            <h1 class="text-2xl font-black text-white">
                {{ $transaction->product?->name ?? 'Komoditas Pertanian' }}
            </h1>
            <p class="text-xs text-slate-300 mt-0.5">
                Volume: {{ $transaction->quantity }} MT &bull; Rute: {{ $transaction->departure_port }} &rarr; {{ $transaction->destination_port }}
            </p>
        </div>

        <div class="text-right">
            <span class="text-[10px] uppercase font-bold text-slate-400 block">Total Tagihan CIF</span>
            <span class="text-2xl font-black text-amber-400 font-mono">${{ number_format($transaction->total_price, 2) }}</span>
        </div>
    </div>

    <!-- Details Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Left: Contract & Logistics Data -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
            <h2 class="text-sm font-black text-slate-900 pb-2 border-b border-slate-100 flex items-center justify-between">
                <span>📦 Data Kontrak Dagang</span>
                <span class="text-[10px] px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-mono">Incoterm CIF</span>
            </h2>

            <div class="space-y-2.5 text-xs text-slate-700">
                <div class="flex justify-between">
                    <span class="text-slate-400">Komoditas:</span>
                    <span class="font-bold text-slate-900">{{ $transaction->product?->name }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Kategori:</span>
                    <span class="font-semibold text-slate-800">{{ $transaction->product?->category?->name }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Grade Kualitas:</span>
                    <span class="font-bold text-amber-700">Grade {{ $transaction->product?->grade }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Kuantitas Ekspor:</span>
                    <span class="font-mono font-bold text-slate-900">{{ $transaction->quantity }} MT</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Pelabuhan Muat:</span>
                    <span class="font-semibold text-slate-800">{{ $transaction->departure_port }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Pelabuhan Tujuan:</span>
                    <span class="font-semibold text-slate-800">{{ $transaction->destination_port }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Estimasi Pelayaran:</span>
                    <span class="font-semibold text-slate-800">14 - 16 Hari Berlayar</span>
                </div>
            </div>
        </div>

        <!-- Right: Payment & Consignee Data -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
            <h2 class="text-sm font-black text-slate-900 pb-2 border-b border-slate-100 flex items-center justify-between">
                <span>💳 Status Pembayaran & Penerima</span>
                <span class="text-[10px] px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold">Escrow Verified</span>
            </h2>

            <div class="space-y-2.5 text-xs text-slate-700">
                <div class="flex justify-between">
                    <span class="text-slate-400">No. Pembayaran:</span>
                    <span class="font-mono font-bold text-slate-900">{{ $transaction->payment?->payment_number ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Kanal Pembayaran:</span>
                    <span class="font-semibold text-slate-800">{{ $transaction->payment?->payment_channel ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Status Pembayaran:</span>
                    <span class="font-bold {{ $transaction->payment?->isSuccess() ? 'text-emerald-600' : 'text-amber-600' }}">
                        {{ $transaction->payment?->payment_status ?? 'PENDING' }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Perusahaan Penerima:</span>
                    <span class="font-semibold text-slate-800">{{ $orderMeta['consignee_company'] ?? ($transaction->importer?->company_name ?? '-') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Alamat Gudang:</span>
                    <span class="font-medium text-slate-600 text-right max-w-[200px]">{{ $orderMeta['consignee_address'] ?? '-' }}</span>
                </div>
            </div>

            <!-- Action buttons -->
            <div class="pt-3 border-t border-slate-100 space-y-2">
                @if($transaction->payment && !$transaction->payment->isSuccess())
                    <a href="{{ route('payments.show', $transaction->payment->id) }}"
                       class="w-full py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl shadow-md text-center block transition">
                        Buka Payment Gateway Sekarang &rarr;
                    </a>
                @elseif($transaction->payment && $transaction->payment->isSuccess())
                    <a href="{{ route('payments.receipt', $transaction->payment->id) }}"
                       class="w-full py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs rounded-xl text-center block transition">
                        📄 Unduh Commercial Invoice & Kuitansi
                    </a>
                    @if($transaction->shipment)
                        <a href="{{ route('map.index', ['q' => $transaction->shipment->shipment_number]) }}"
                           class="w-full py-2.5 bg-sky-600 hover:bg-sky-500 text-white font-black text-xs rounded-xl shadow-md text-center block transition">
                            🗺️ Lacak Posisi Kapal di Peta Global
                        </a>
                    @endif
                @endif
            </div>
        </div>

    </div>

</div>
@endsection
