@extends('layouts.app')

@section('title', 'Katalog Komoditas Ekspor (Kopi & Kakao) — CargoTrace')

@section('content')
<div class="space-y-6">

    <!-- Top Hero Banner -->
    <div class="bg-gradient-to-r from-slate-900 via-amber-950/40 to-slate-900 rounded-2xl p-6 md:p-8 text-white shadow-xl border border-amber-900/40 relative overflow-hidden">
        <div class="relative z-10 max-w-3xl">
            <div class="flex items-center gap-2 mb-2">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-black bg-[#C49A55] text-slate-950 uppercase tracking-wider">
                    MARKETPLACE KOMODITAS GLOBAL
                </span>
                <span class="text-xs font-semibold text-amber-300">
                    Sertifikasi Ekspor Grade-A & Verified Exporters
                </span>
            </div>
            <h1 class="text-3xl md:text-4xl font-black tracking-tight text-white">
                Katalog Kopi & Kakao Internasional
            </h1>
            <p class="text-sm md:text-base text-slate-300 mt-2 leading-relaxed">
                Beli batch komoditas kopi dan kakao pilihan langsung dari koperasi perkebunan terverifikasi dengan kontrak perdagangan internasional CIF, pembayaran multi-channel escrow aman, dan pelacakan rute kargo laut real-time.
            </p>
        </div>
        <div class="absolute right-4 bottom-2 opacity-15 text-8xl pointer-events-none select-none hidden lg:block">
            ☕🍫
        </div>
    </div>

    <!-- Filter & Search Controls -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm space-y-3">
        <form action="{{ route('catalog.index') }}" method="GET" class="flex flex-col md:flex-row md:items-center justify-between gap-3">
            
            <!-- Search bar -->
            <div class="relative flex-1 max-w-md">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama komoditas, grade, atau deskripsi..."
                       class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl pl-9 pr-4 py-2.5 text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500 shadow-inner">
                <span class="absolute left-3 top-3 text-slate-400 text-xs">🔍</span>
            </div>

            <!-- Filter Controls -->
            <div class="flex flex-wrap items-center gap-2 text-xs">
                <!-- Category Filter -->
                <select name="category" onchange="this.form.submit()" class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-700 font-semibold focus:ring-2 focus:ring-amber-500">
                    <option value="">Semua Kategori (Kopi & Kakao)</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->slug }}" {{ request('category') === $cat->slug ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>

                <!-- Country Filter -->
                <select name="country_id" onchange="this.form.submit()" class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-700 font-semibold focus:ring-2 focus:ring-amber-500">
                    <option value="">Semua Negara Asal</option>
                    @foreach($countries as $c)
                        <option value="{{ $c->id }}" {{ request('country_id') == $c->id ? 'selected' : '' }}>
                            {{ $c->name }}
                        </option>
                    @endforeach
                </select>

                <!-- Sort Filter -->
                <select name="sort" onchange="this.form.submit()" class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-700 font-semibold focus:ring-2 focus:ring-amber-500">
                    <option value="featured" {{ request('sort') === 'featured' ? 'selected' : '' }}>Unggulan (Featured)</option>
                    <option value="price_asc" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>Harga: Terendah &rarr; Tertinggi</option>
                    <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>Harga: Tertinggi &rarr; Terendah</option>
                    <option value="name" {{ request('sort') === 'name' ? 'selected' : '' }}>Nama Alfabet (A-Z)</option>
                </select>

                <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl shadow-xs transition">
                    Terapkan
                </button>

                @if(request()->anyFilled(['q', 'category', 'country_id', 'sort']))
                    <a href="{{ route('catalog.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>

        <!-- Quick Category Chips -->
        <div class="flex items-center gap-2 pt-2 border-t border-slate-100 text-xs">
            <span class="text-slate-400 font-bold text-[11px] uppercase">Kategori Cepat:</span>
            <a href="{{ route('catalog.index') }}" class="px-3 py-1 rounded-full font-bold transition {{ !request('category') ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Semua
            </a>
            <a href="{{ route('catalog.index', ['category' => 'cocoa']) }}" class="px-3 py-1 rounded-full font-bold transition flex items-center gap-1 {{ request('category') === 'cocoa' ? 'bg-amber-800 text-white' : 'bg-amber-50 text-amber-800 border border-amber-200 hover:bg-amber-100' }}">
                <span>🍫</span> Biji Kakao (Cocoa)
            </a>
            <a href="{{ route('catalog.index', ['category' => 'coffee']) }}" class="px-3 py-1 rounded-full font-bold transition flex items-center gap-1 {{ request('category') === 'coffee' ? 'bg-amber-950 text-white' : 'bg-orange-50 text-orange-800 border border-orange-200 hover:bg-orange-100' }}">
                <span>☕</span> Biji Kopi (Coffee)
            </a>
        </div>
    </div>

    <!-- Product Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($products as $product)
            @php
                $isCocoa = str_contains(strtolower($product->category->name ?? ''), 'cocoa') || str_contains(strtolower($product->category->name ?? ''), 'kakao');
                $specs = is_array($product->specifications) ? $product->specifications : json_decode($product->specifications ?? '[]', true);
            @endphp
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between overflow-hidden group">
                
                <!-- Card Header with Badges -->
                <div class="p-5 pb-0">
                    <div class="flex items-start justify-between gap-2 mb-2">
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-black uppercase tracking-wider
                            {{ $isCocoa ? 'bg-amber-100 text-amber-900 border border-amber-200' : 'bg-orange-100 text-orange-900 border border-orange-200' }}">
                            {{ $isCocoa ? '🍫 Kakao' : '☕ Kopi' }} &bull; {{ $product->originCountry?->name ?? 'Global' }}
                        </span>
                        <span class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase bg-slate-900 text-amber-300">
                            Grade {{ $product->grade }}
                        </span>
                    </div>

                    <h2 class="text-lg font-black text-slate-900 group-hover:text-amber-700 transition leading-snug">
                        {{ $product->name }}
                    </h2>

                    <p class="text-xs text-slate-500 mt-1 line-clamp-2 leading-relaxed">
                        {{ $product->description }}
                    </p>
                </div>

                <!-- Quality Specifications Summary -->
                <div class="p-5 pt-3 space-y-3">
                    <div class="grid grid-cols-3 gap-2 p-2.5 bg-slate-50 rounded-xl text-center border border-slate-100 text-[11px]">
                        <div>
                            <span class="text-[10px] uppercase font-bold text-slate-400 block">Kadar Air</span>
                            <span class="font-black text-slate-800">{{ $specs['moisture'] ?? '11.5%' }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] uppercase font-bold text-slate-400 block">Kecacatan</span>
                            <span class="font-black text-slate-800">{{ $specs['defect_rate'] ?? '2.8%' }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] uppercase font-bold text-slate-400 block">Screen/Size</span>
                            <span class="font-black text-slate-800">{{ $specs['screen_size'] ?? '18/19' }}</span>
                        </div>
                    </div>

                    <!-- Certifications Tags -->
                    @if(!empty($specs['certifications']) && is_array($specs['certifications']))
                    <div class="flex flex-wrap gap-1">
                        @foreach($specs['certifications'] as $cert)
                            <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                🌿 {{ $cert }}
                            </span>
                        @endforeach
                    </div>
                    @endif

                    <!-- Price & Stock Row -->
                    <div class="pt-3 border-t border-slate-100 flex items-baseline justify-between">
                        <div>
                            <span class="text-[10px] uppercase font-bold text-slate-400 block">Harga FOB / Metrik Ton</span>
                            <div class="text-xl font-black text-slate-900">
                                ${{ number_format($product->price, 2) }}
                                <span class="text-xs font-normal text-slate-500">/ MT</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] font-semibold text-emerald-600 block">Tersedia Siap Muat</span>
                            <span class="text-xs font-bold text-slate-700 font-mono">{{ number_format($product->stock_availability, 0) }} MT</span>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="grid grid-cols-2 gap-2 pt-2">
                        <a href="{{ route('catalog.show', $product->slug ?? $product->id) }}"
                           class="py-2.5 px-3 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs rounded-xl text-center transition">
                            Lihat Rincian
                        </a>
                        <a href="{{ route('orders.create', ['product_id' => $product->id]) }}"
                           class="py-2.5 px-3 bg-amber-600 hover:bg-amber-700 text-white font-black text-xs rounded-xl text-center shadow-md transition flex items-center justify-center gap-1">
                            <span>🛒</span> Beli Batch Ini
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white p-12 text-center rounded-2xl border border-slate-200 shadow-sm">
                <span class="text-5xl block mb-3">📦</span>
                <h3 class="text-lg font-bold text-slate-800">Tidak ada produk komoditas yang cocok</h3>
                <p class="text-xs text-slate-500 mt-1">Coba gunakan filter lain atau reset pencarian Anda.</p>
                <a href="{{ route('catalog.index') }}" class="mt-4 inline-block px-4 py-2 bg-slate-900 text-white rounded-xl text-xs font-bold">
                    Lihat Semua Komoditas
                </a>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $products->links() }}
    </div>

</div>
@endsection
