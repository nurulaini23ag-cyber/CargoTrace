@extends('layouts.app')

@section('title', 'Daftar Pesanan & Kontrak Dagang — CargoTrace')

@section('content')
<div class="space-y-6">

    <!-- Top Header -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 rounded-2xl p-6 text-white shadow-xl border border-slate-700/80 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-black bg-[#C49A55] text-slate-950 uppercase tracking-wider">
                COMMODITY TRADE ORDERS
            </span>
            <h1 class="text-2xl md:text-3xl font-black text-white mt-1">
                Daftar Pesanan & Kontrak Dagang Komoditas
            </h1>
            <p class="text-xs text-slate-300 mt-0.5">
                Kelola status pembayaran, invoice komersial, dan pantau pengiriman armada kapal Anda.
            </p>
        </div>

        <a href="{{ route('catalog.index') }}"
           class="px-4 py-2.5 bg-amber-600 hover:bg-amber-500 text-white font-black text-xs rounded-xl shadow-lg transition flex items-center gap-1.5 shrink-0">
            <span>🛒</span>
            <span>Beli Komoditas Baru</span>
        </a>
    </div>

    <!-- Orders Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
            <h2 class="text-xs font-black uppercase text-slate-700 tracking-wider">Semua Kontrak & Transaksi Pembelian</h2>
            <span class="text-xs text-slate-500 font-bold">Total: {{ $transactions->total() }} Transaksi</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-800">
                <thead class="bg-slate-100 text-slate-600 font-bold uppercase text-[10px] border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">No. Transaksi</th>
                        <th class="py-3 px-4">Komoditas & Volume</th>
                        <th class="py-3 px-4">Rute Pelabuhan</th>
                        <th class="py-3 px-4 text-right">Total CIF</th>
                        <th class="py-3 px-3 text-center">Status Bayar</th>
                        <th class="py-3 px-4 text-center">Aksi & Pelacakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($transactions as $t)
                        @php
                            $payment = $t->payment;
                            $shipment = $t->shipment;
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3.5 px-4">
                                <div class="font-mono font-black text-slate-900">{{ $t->transaction_id }}</div>
                                <div class="text-[10px] text-slate-400 font-mono">{{ $t->created_at ? $t->created_at->format('d M Y') : '-' }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-black text-slate-900">{{ $t->product?->name ?? 'Komoditas' }}</div>
                                <div class="text-[11px] text-slate-500 font-mono">{{ $t->quantity }} MT &bull; Grade {{ $t->product?->grade ?? 'A' }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-slate-800">{{ $t->departure_port }}</div>
                                <div class="text-[10px] text-slate-500">&rarr; {{ $t->destination_port }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="font-mono font-black text-amber-700 text-sm">
                                    ${{ number_format($t->total_price, 2) }}
                                </div>
                                <div class="text-[10px] text-slate-400 font-mono">Incoterm {{ $t->incoterms }}</div>
                            </td>
                            <td class="py-3.5 px-3 text-center">
                                @if($payment && $payment->isSuccess())
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-emerald-100 text-emerald-800 border border-emerald-300">
                                        🟢 LUNAS
                                    </span>
                                @else
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-amber-100 text-amber-800 border border-amber-300">
                                        ⏳ MENUNGGU
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    @if($payment && !$payment->isSuccess())
                                        <a href="{{ route('payments.show', $payment->id) }}"
                                           class="px-3 py-1 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-lg shadow-xs transition">
                                            Bayar Sekarang &rarr;
                                        </a>
                                    @elseif($payment && $payment->isSuccess())
                                        <a href="{{ route('payments.receipt', $payment->id) }}"
                                           class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs rounded-lg transition" title="Lihat Invoice">
                                            📄 Invoice
                                        </a>
                                        @if($shipment)
                                            <a href="{{ route('map.index', ['q' => $shipment->shipment_number]) }}"
                                               class="px-2.5 py-1 bg-sky-600 hover:bg-sky-500 text-white font-bold text-xs rounded-lg shadow-xs transition" title="Lacak Kapal di Peta">
                                                🗺️ Lacak
                                            </a>
                                        @endif
                                    @endif
                                    <a href="{{ route('orders.show', $t->id) }}"
                                       class="px-2 py-1 bg-slate-50 hover:bg-slate-100 text-slate-600 font-bold text-xs rounded-lg border border-slate-200 transition">
                                        Rincian
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <span class="text-4xl block mb-2">📋</span>
                                Belum ada pesanan transaksi dagang.
                                <div class="mt-2">
                                    <a href="{{ route('catalog.index') }}" class="text-amber-700 font-bold text-xs hover:underline">
                                        Jelajahi Katalog Komoditas &rarr;
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $transactions->links() }}
        </div>
    </div>

</div>
@endsection
