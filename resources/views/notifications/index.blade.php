@extends('layouts.app')

@section('title', 'Pusat Notifikasi & Berita Intelijen Real-Time — CargoTrace')

@section('content')
<div class="max-w-6xl mx-auto space-y-6 font-sans">

    <!-- Top Header & Live Status Radar Banner -->
    <div class="bg-gradient-to-r from-[#38251D] via-[#4E342E] to-[#292421] rounded-2xl p-6 md:p-8 text-white shadow-lg border border-[#C49A55]/30">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-extrabold bg-[#C49A55] text-slate-900 uppercase tracking-wider">
                        LIVE GLOBAL INTELLIGENCE RADAR
                    </span>
                    <span class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-400 bg-emerald-950/80 px-2.5 py-0.5 rounded-full border border-emerald-500/30">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                        Real-Time Connected
                    </span>
                </div>
                <h1 class="text-2xl md:text-3xl font-black tracking-tight text-white">
                    Pusat Notifikasi & Berita Terkini Dunia
                </h1>
                <p class="text-xs md:text-sm text-[#E8D7B9] mt-1.5 max-w-2xl leading-relaxed">
                    Pantau pergerakan kurs valuta asing (SGD, IDR, BRL, EUR) serta berita cuaca maritim real-time dari pelabuhan-pelabuhan kopi & kakao di seluruh dunia.
                </p>
                <div class="mt-2 text-[11px] text-[#E8D7B9]/70 flex items-center gap-2">
                    <span>📡 Sumber Kurs: <strong>{{ $fxSource }}</strong></span>
                    <span>&bull;</span>
                    <span>🌊 Sumber Cuaca: <strong>Open-Meteo Global Marine Satellite</strong></span>
                    <span>&bull;</span>
                    <span id="label-last-updated">Pembaruan: {{ $generatedAt }}</span>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-wrap items-center gap-2 shrink-0">
                <button type="button" onclick="refreshLiveFeeds()" id="btn-refresh-feeds"
                        class="px-4 py-2 bg-[#C49A55] hover:bg-[#b38944] text-[#292421] font-black rounded-xl text-xs transition shadow-md flex items-center gap-2 cursor-pointer">
                    <span id="refresh-icon" class="text-sm">🔄</span>
                    <span id="refresh-text">Perbarui Real-Time</span>
                </button>

                @if($unreadSystemCount > 0)
                <form action="{{ route('notifications.readAll') }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-white/10 hover:bg-white/20 text-white font-bold rounded-xl text-xs transition border border-white/20 flex items-center gap-1.5">
                        <span>✓</span>
                        <span>Tandai Semua Dibaca</span>
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>

    <!-- Live Toast Feedback Notification -->
    <div id="live-toast" class="hidden transition-all duration-300 p-3 bg-emerald-600 text-white text-xs font-bold rounded-xl shadow-lg flex items-center justify-between">
        <div class="flex items-center gap-2">
            <span>✅</span>
            <span id="toast-message">Data kurs dan cuaca berhasil diperbarui secara real-time.</span>
        </div>
        <button type="button" onclick="document.getElementById('live-toast').classList.add('hidden')" class="text-white hover:text-slate-200 text-sm font-bold">&times;</button>
    </div>

    <!-- Real-Time Currency Rates Ticker Bar (SGD, IDR, BRL, EUR, USD, GHS, VND) -->
    <div class="bg-white rounded-2xl p-4 md:p-5 border border-slate-200 shadow-sm space-y-3">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-lg">💱</span>
                <div>
                    <h2 class="text-sm font-black text-slate-900 uppercase tracking-wide">
                        Kurs Valuta Asing Real-Time (Foreign Exchange Ticker)
                    </h2>
                    <p class="text-[11px] text-slate-500">Mata uang utama perdagangan internasional kopi, kakao, & biaya freight laut</p>
                </div>
            </div>
            <span class="text-[10px] font-extrabold uppercase px-2 py-0.5 rounded bg-blue-50 text-blue-700 border border-blue-200">
                Base USD ($)
            </span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3" id="currency-ticker-container">
            @foreach($currencyRates as $code => $rate)
            <div class="p-3 rounded-xl border border-slate-200 bg-slate-50 hover:bg-blue-50/40 transition">
                <div class="flex items-center justify-between">
                    <span class="text-base">{{ $rate['flag'] }}</span>
                    <span class="text-[10px] font-mono font-bold px-1.5 py-0.2 rounded
                        @if($rate['trend_direction'] === 'up') bg-emerald-100 text-emerald-800
                        @elseif($rate['trend_direction'] === 'down') bg-rose-100 text-rose-800
                        @else bg-slate-200 text-slate-700 @endif">
                        {{ $rate['trend'] }}
                    </span>
                </div>
                <div class="text-[11px] text-slate-500 font-bold mt-1">{{ $rate['name'] }}</div>
                <div class="text-sm font-black text-slate-900 tracking-tight mt-0.5">{{ $rate['formatted'] }}</div>
                <div class="text-[9px] text-slate-400 font-mono mt-0.5">1 USD = {{ $rate['code'] }}</div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Filter Navigation Tabs -->
    <div class="flex flex-wrap items-center gap-2 border-b border-slate-200 pb-2">
        <button type="button" onclick="switchTab('ALL')" id="tab-btn-ALL"
                class="tab-btn px-4 py-2 rounded-xl text-xs font-black transition bg-blue-600 text-white shadow-sm flex items-center gap-1.5">
            <span>🌐</span> Semua Berita & Notifikasi (<span id="count-all">{{ count($newsItems) + $notifications->total() }}</span>)
        </button>
        <button type="button" onclick="switchTab('CURRENCY')" id="tab-btn-CURRENCY"
                class="tab-btn px-4 py-2 rounded-xl text-xs font-bold transition bg-white text-slate-700 border border-slate-200 hover:bg-slate-50 flex items-center gap-1.5">
            <span>💱</span> Berita Kurs & Valuta
        </button>
        <button type="button" onclick="switchTab('WEATHER')" id="tab-btn-WEATHER"
                class="tab-btn px-4 py-2 rounded-xl text-xs font-bold transition bg-white text-slate-700 border border-slate-200 hover:bg-slate-50 flex items-center gap-1.5">
            <span>🌊</span> Cuaca Pelabuhan Dunia
        </button>
        <button type="button" onclick="switchTab('SYSTEM')" id="tab-btn-SYSTEM"
                class="tab-btn px-4 py-2 rounded-xl text-xs font-bold transition bg-white text-slate-700 border border-slate-200 hover:bg-slate-50 flex items-center gap-1.5">
            <span>📦</span> Notifikasi Operasional Kargo ({{ $notifications->total() }})
            @if($unreadSystemCount > 0)
                <span class="w-2 h-2 rounded-full bg-rose-500"></span>
            @endif
        </button>
    </div>

    <!-- Main Content Area: Live Feed Items -->
    <div class="space-y-4" id="news-feed-container">

        <!-- 1. Real-Time Currency & Market Intelligence News -->
        @foreach($newsItems as $news)
        <div class="news-feed-card p-4 sm:p-5 rounded-2xl border border-slate-200 bg-white hover:border-blue-400 transition shadow-sm"
             data-category="{{ $news['category'] }}">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-start gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-xl shrink-0 shadow-inner">
                        {{ $news['icon'] }}
                    </div>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase
                                @if($news['badge_color'] === 'emerald') bg-emerald-100 text-emerald-800 border border-emerald-200
                                @elseif($news['badge_color'] === 'rose') bg-rose-100 text-rose-800 border border-rose-200
                                @elseif($news['badge_color'] === 'amber') bg-amber-100 text-amber-800 border border-amber-200
                                @else bg-blue-100 text-blue-800 border border-blue-200 @endif">
                                {{ $news['badge'] }}
                            </span>
                            <span class="text-xs text-slate-400 font-semibold">&bull; {{ $news['source'] }}</span>
                            <span class="text-xs text-slate-400 font-mono">({{ $news['timestamp'] }})</span>
                        </div>

                        <h3 class="text-sm sm:text-base font-extrabold text-slate-900 mt-1">
                            {{ $news['title'] }}
                        </h3>

                        <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                            {{ $news['summary'] }}
                        </p>

                        <div class="mt-2.5 flex items-center gap-3 text-[11px]">
                            <span class="font-bold text-slate-500">Kategori: <strong class="text-slate-800">{{ $news['category_label'] }}</strong></span>
                            @if(!empty($news['action_label']))
                            <span>&bull;</span>
                            <span class="text-blue-600 font-bold hover:underline cursor-pointer">
                                {{ $news['action_label'] }} &rarr;
                            </span>
                            @endif
                        </div>
                    </div>
                </div>

                <span class="px-2 py-0.5 rounded text-[9px] font-black uppercase shrink-0
                    @if($news['impact_level'] === 'CRITICAL') bg-rose-600 text-white
                    @elseif($news['impact_level'] === 'HIGH') bg-amber-500 text-slate-900
                    @else bg-slate-200 text-slate-700 @endif">
                    {{ $news['impact_level'] }}
                </span>
            </div>
        </div>
        @endforeach

        <!-- 2. System Operational Cargo Notifications (Database) -->
        @foreach($notifications as $notif)
        <div class="news-feed-card p-4 sm:p-5 rounded-2xl border border-slate-200 {{ $notif->is_read ? 'bg-white' : 'bg-blue-50/40 border-blue-300' }} transition shadow-sm"
             data-category="SYSTEM">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-start gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-xl shrink-0 shadow-inner">
                        @if($notif->type === 'DELAY_ALERT') ⏱️
                        @elseif($notif->type === 'EXCEPTION_ALERT') ⚠️
                        @else 📦 @endif
                    </div>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase
                                @if($notif->type === 'DELAY_ALERT') bg-amber-100 text-amber-800 border border-amber-300
                                @elseif($notif->type === 'EXCEPTION_ALERT') bg-rose-100 text-rose-800 border border-rose-300
                                @else bg-blue-100 text-blue-800 border border-blue-300 @endif">
                                {{ $notif->type }}
                            </span>
                            <span class="text-xs text-slate-400">{{ $notif->created_at->diffForHumans() }}</span>
                        </div>

                        <h3 class="text-sm sm:text-base font-extrabold text-slate-900 mt-1">
                            {{ $notif->title }}
                        </h3>

                        <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                            {{ $notif->message }}
                        </p>

                        @if($notif->shipment_id)
                        <div class="mt-2.5">
                            <a href="{{ route('shipments.show', $notif->shipment_id) }}" class="text-xs font-bold text-blue-600 hover:text-blue-800 hover:underline flex items-center gap-1">
                                <span>Periksa Status & Peta Kargo</span>
                                <span>&rarr;</span>
                            </a>
                        </div>
                        @endif
                    </div>
                </div>

                @if(!$notif->is_read)
                <form action="{{ route('notifications.read', $notif->id) }}" method="POST" class="shrink-0">
                    @csrf
                    <button type="submit" class="px-2.5 py-1 text-xs text-slate-500 hover:text-slate-800 bg-slate-100 hover:bg-slate-200 rounded-lg font-bold transition">
                        Tandai Dibaca
                    </button>
                </form>
                @endif
            </div>
        </div>
        @endforeach

        @if(count($newsItems) === 0 && $notifications->isEmpty())
        <div class="p-12 text-center text-slate-400 text-sm bg-white rounded-2xl border border-slate-200">
            Tidak ada berita atau notifikasi saat ini.
        </div>
        @endif

    </div>

    <!-- Paged Navigation for System Notifications -->
    @if($notifications->hasPages())
    <div class="p-2">
        {{ $notifications->links() }}
    </div>
    @endif

</div>
@endsection

@section('scripts')
<script>
    /**
     * Switch category filter tabs
     */
    function switchTab(cat) {
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('bg-blue-600', 'text-white', 'shadow-sm');
            btn.classList.add('bg-white', 'text-slate-700', 'border', 'border-slate-200');
        });

        const activeBtn = document.getElementById('tab-btn-' + cat);
        if (activeBtn) {
            activeBtn.classList.add('bg-blue-600', 'text-white', 'shadow-sm');
            activeBtn.classList.remove('bg-white', 'text-slate-700', 'border-slate-200');
        }

        const cards = document.querySelectorAll('.news-feed-card');
        cards.forEach(card => {
            const cardCat = card.getAttribute('data-category');
            if (cat === 'ALL' || cardCat === cat) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }

    /**
     * Refresh live market & weather intelligence feeds via AJAX Fetch API
     */
    async function refreshLiveFeeds() {
        const btn = document.getElementById('btn-refresh-feeds');
        const icon = document.getElementById('refresh-icon');
        const text = document.getElementById('refresh-text');
        const toast = document.getElementById('live-toast');
        const toastMsg = document.getElementById('toast-message');

        try {
            if (icon) icon.classList.add('animate-spin');
            if (text) text.innerText = 'Mengambil Data Real-Time...';
            btn.disabled = true;

            const response = await fetch("{{ route('notifications.liveFeeds') }}?refresh=1", {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.ok) {
                const res = await response.json();
                if (res.status === 'success' && res.data) {
                    const data = res.data;

                    // Update timestamp
                    const updatedElem = document.getElementById('label-last-updated');
                    if (updatedElem) {
                        updatedElem.innerText = `Pembaruan: ${data.generated_at}`;
                    }

                    // Show success toast
                    if (toast && toastMsg) {
                        toastMsg.innerText = 'Berhasil memperbarui kurs SGD, IDR, BRL, EUR & cuaca pelabuhan dunia secara real-time!';
                        toast.classList.remove('hidden');
                        setTimeout(() => toast.classList.add('hidden'), 5000);
                    }

                    // Reload page smoothly to reflect fresh cards
                    setTimeout(() => {
                        window.location.reload();
                    }, 800);
                }
            } else {
                alert('Gagal mengambil data real-time terbaru. Silakan coba kembali.');
            }
        } catch (err) {
            console.error('Error fetching real-time feeds:', err);
        } finally {
            if (icon) icon.classList.remove('animate-spin');
            if (text) text.innerText = 'Perbarui Real-Time';
            btn.disabled = false;
        }
    }
</script>
@endsection
