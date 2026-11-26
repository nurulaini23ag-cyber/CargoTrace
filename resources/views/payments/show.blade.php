@extends('layouts.app')

@section('title', 'Simulasi Payment Gateway — CargoTrace')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">

    <!-- Top Secure Header -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 rounded-2xl p-6 text-white shadow-xl border border-slate-700 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1.5">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-emerald-500 text-slate-950 uppercase tracking-wider flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-slate-950 animate-ping"></span>
                    GATEWAY PEMBAYARAN AKTIF
                </span>
                <span class="text-xs font-mono text-slate-300">
                    Ref: {{ $payment->payment_number }}
                </span>
            </div>
            <h1 class="text-2xl md:text-3xl font-black text-white">
                Simulasi Payment Gateway Internasional
            </h1>
            <p class="text-xs text-slate-300 mt-1">
                Selesaikan pembayaran komoditas untuk otomatis menerbitkan kargo dan pelacakan rute laut.
            </p>
        </div>

        <div class="text-right p-3 bg-slate-800/80 rounded-xl border border-slate-700 shrink-0">
            <span class="text-[10px] uppercase font-bold text-slate-400 block">Total Tagihan CIF</span>
            <div class="text-2xl font-black text-amber-400 font-mono">
                ${{ number_format($payment->amount, 2) }}
            </div>
            <div class="text-[11px] text-slate-300 font-mono" id="idr-val">
                &asymp; Rp {{ number_format($payment->amount * 16250, 0, ',', '.') }}
            </div>
        </div>
    </div>

    <!-- Countdown & Order Summary Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-wrap items-center justify-between gap-4 text-xs">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center text-lg font-bold">
                ☕
            </div>
            <div>
                <span class="font-black text-slate-900 block text-sm">
                    {{ $transaction->product?->name ?? 'Komoditas Pertanian' }}
                </span>
                <span class="text-slate-500">
                    Volume: <strong>{{ $transaction->quantity }} MT</strong> &bull; Rute: {{ $transaction->departure_port }} &rarr; {{ $transaction->destination_port }}
                </span>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <span class="text-slate-500 font-medium">Batas Waktu Pembayaran:</span>
            <div class="px-3 py-1 bg-rose-50 text-rose-700 border border-rose-200 rounded-lg font-mono font-black text-xs flex items-center gap-1.5" id="countdown-timer">
                <span>⏱️</span>
                <span id="timer-display">23:59:45</span>
            </div>
        </div>
    </div>

    <!-- Main Payment Channels Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-md overflow-hidden">
        
        <!-- Tab Headers -->
        <div class="flex border-b border-slate-200 bg-slate-50 overflow-x-auto text-xs font-bold" id="payment-tabs">
            <button type="button" onclick="switchTab('va')" id="tab-btn-va"
                    class="py-3.5 px-5 border-b-2 border-amber-600 bg-white text-slate-900 font-black flex items-center gap-2 cursor-pointer transition shrink-0">
                <span>🏦</span> Virtual Account (Bank Transfer)
            </button>
            <button type="button" onclick="switchTab('cc')" id="tab-btn-cc"
                    class="py-3.5 px-5 border-b-2 border-transparent text-slate-500 hover:text-slate-800 flex items-center gap-2 cursor-pointer transition shrink-0">
                <span>💳</span> Kartu Kredit / Debit (3DS OTP)
            </button>
            <button type="button" onclick="switchTab('lc')" id="tab-btn-lc"
                    class="py-3.5 px-5 border-b-2 border-transparent text-slate-500 hover:text-slate-800 flex items-center gap-2 cursor-pointer transition shrink-0">
                <span>📜</span> Letter of Credit (SWIFT MT700)
            </button>
            <button type="button" onclick="switchTab('qris')" id="tab-btn-qris"
                    class="py-3.5 px-5 border-b-2 border-transparent text-slate-500 hover:text-slate-800 flex items-center gap-2 cursor-pointer transition shrink-0">
                <span>📱</span> QRIS Instant Payment
            </button>
        </div>

        <!-- Tab 1: Virtual Account -->
        <div id="tab-content-va" class="p-6 space-y-6">
            <div>
                <h3 class="text-sm font-black text-slate-900">Pembayaran Melalui Virtual Account Otomatis</h3>
                <p class="text-xs text-slate-500 mt-0.5">Pilih salah satu bank di bawah ini untuk menampilkan nomor Virtual Account resmi.</p>
            </div>

            <!-- Bank Selector Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                @foreach($bankVAs as $key => $bank)
                    <button type="button" onclick="selectBank('{{ $key }}', '{{ $bank['va'] }}', '{{ $bank['channel'] }}')"
                            id="bank-card-{{ $key }}"
                            class="bank-pill p-3 rounded-xl border text-left transition cursor-pointer {{ $loop->first ? 'border-amber-600 bg-amber-50/60 shadow-xs ring-2 ring-amber-500/20' : 'border-slate-200 bg-white hover:border-slate-300' }}">
                        <span class="text-xs font-black text-slate-900 block">{{ $bank['name'] }}</span>
                        <span class="text-[10px] text-slate-500 font-mono mt-0.5 block">Prefix: {{ substr($bank['va'], 0, 5) }}</span>
                    </button>
                @endforeach
            </div>

            <!-- Virtual Account Display Card -->
            <div class="p-5 bg-gradient-to-br from-slate-900 to-slate-800 rounded-2xl text-white shadow-md space-y-3">
                <div class="flex items-center justify-between text-xs text-slate-300">
                    <span id="active-bank-name">Bank Mandiri Escrow Virtual Account</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/30 text-emerald-300 border border-emerald-400/30">Otomatis Terverifikasi</span>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-2">
                    <div>
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Nomor Virtual Account (16 Digit)</span>
                        <div class="text-2xl sm:text-3xl font-black font-mono tracking-widest text-amber-300" id="va-number-display">
                            {{ $bankVAs['Mandiri']['va'] }}
                        </div>
                    </div>

                    <button type="button" onclick="copyVA()" class="px-4 py-2 bg-white/10 hover:bg-white/20 text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 border border-white/20 cursor-pointer">
                        <span id="copy-icon">📋</span>
                        <span id="copy-text">Salin Nomor VA</span>
                    </button>
                </div>

                <div class="pt-2 border-t border-slate-700/80 flex items-center justify-between text-[11px] text-slate-400">
                    <span>Nama Rekening: <strong>CARGOTRACE ESCROW COMMODITY</strong></span>
                    <span>Total: <strong>${{ number_format($payment->amount, 2) }}</strong></span>
                </div>
            </div>

            <!-- How to pay accordion -->
            <div class="border border-slate-200 rounded-xl divide-y divide-slate-100 text-xs">
                <details class="p-3.5 group cursor-pointer" open>
                    <summary class="font-bold text-slate-800 flex items-center justify-between">
                        <span>📱 Pembayaran via Mobile Banking (Livin' / BCA Mobile / BRImo)</span>
                        <span class="text-slate-400 text-xs group-open:rotate-180 transition">▼</span>
                    </summary>
                    <ol class="list-decimal list-inside mt-2.5 space-y-1 text-slate-600 pl-1 leading-relaxed">
                        <li>Buka aplikasi Mobile Banking pilihan Anda dan lakukan Login.</li>
                        <li>Pilih menu <strong>Bayar / Transfer &rarr; Virtual Account / Tagihan</strong>.</li>
                        <li>Masukkan nomor Virtual Account di atas dan klik <strong>Lanjut</strong>.</li>
                        <li>Periksa rincian pembayaran atas nama <strong>CargoTrace Escrow</strong> dan nominal sesuai tagihan.</li>
                        <li>Masukkan PIN transaksi Anda untuk mengonfirmasi.</li>
                    </ol>
                </details>
                <details class="p-3.5 group cursor-pointer">
                    <summary class="font-bold text-slate-800 flex items-center justify-between">
                        <span>🏧 Pembayaran via Mesin ATM</span>
                        <span class="text-slate-400 text-xs group-open:rotate-180 transition">▼</span>
                    </summary>
                    <ol class="list-decimal list-inside mt-2.5 space-y-1 text-slate-600 pl-1 leading-relaxed">
                        <li>Masukkan kartu ATM dan 6 digit PIN Anda.</li>
                        <li>Pilih menu <strong>Transaksi Lainnya &rarr; Pembayaran &rarr; Virtual Account</strong>.</li>
                        <li>Ketik nomor Virtual Account di atas lalu tekan <strong>Benar</strong>.</li>
                        <li>Pastikan data tagihan sesuai, lalu tekan <strong>Ya / Bayar</strong>.</li>
                    </ol>
                </details>
            </div>

            <!-- Simulated Instant Pay Action Form -->
            <form action="{{ route('payments.process', $payment->id) }}" method="POST" class="pt-2">
                @csrf
                <input type="hidden" name="payment_method" value="VIRTUAL_ACCOUNT">
                <input type="hidden" name="payment_channel" id="form-va-channel" value="Bank Mandiri Escrow Virtual Account">
                
                <button type="submit"
                        class="w-full py-4 px-6 bg-emerald-600 hover:bg-emerald-700 text-white font-black text-sm rounded-xl shadow-lg transition flex items-center justify-center gap-2 cursor-pointer">
                    <span>⚡</span>
                    <span>Simulasikan Pembayaran Virtual Account Sukses (Bayar Sekarang)</span>
                </button>
            </form>
        </div>

        <!-- Tab 2: Credit Card with 3D Secure OTP Modal -->
        <div id="tab-content-cc" class="p-6 space-y-6 hidden">
            <div>
                <h3 class="text-sm font-black text-slate-900">Pembayaran Kartu Kredit / Debit Internasional</h3>
                <p class="text-xs text-slate-500 mt-0.5">Dilengkapi dengan simulasi otentikasi keamanan 3D Secure (3DS OTP).</p>
            </div>

            <!-- Test Cards Helpers -->
            <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-xs flex flex-wrap items-center justify-between gap-2">
                <span class="font-bold text-amber-900">Kartu Percobaan (Sandbox):</span>
                <div class="flex gap-2">
                    <button type="button" onclick="fillCard('4000000000000001', 'John Doe', '12/28', '123', false)"
                            class="px-2.5 py-1 bg-white hover:bg-slate-50 text-slate-800 font-bold rounded-lg border border-slate-300 text-[11px] shadow-xs cursor-pointer">
                        Direct Success (4000...0001)
                    </button>
                    <button type="button" onclick="fillCard('4000000000000002', 'John Doe', '12/28', '123', true)"
                            class="px-2.5 py-1 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg text-[11px] shadow-xs cursor-pointer">
                        Trigger 3DS OTP (4000...0002)
                    </button>
                </div>
            </div>

            <!-- Interactive Visual Card -->
            <div class="w-full max-w-sm mx-auto h-48 bg-gradient-to-tr from-slate-900 via-slate-800 to-amber-900 rounded-2xl p-5 text-white shadow-xl border border-slate-700 flex flex-col justify-between relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-black tracking-widest uppercase text-amber-300">CARGOTRACE CORPORATE</span>
                    <span class="text-xl font-black italic" id="card-badge">VISA</span>
                </div>
                
                <div class="space-y-1">
                    <div class="w-8 h-6 bg-amber-200/40 rounded-sm border border-amber-200/50 mb-2"></div>
                    <div class="text-lg font-mono font-black tracking-widest text-slate-100" id="card-num-preview">
                        •••• •••• •••• 0001
                    </div>
                </div>

                <div class="flex items-center justify-between text-[10px] uppercase font-bold text-slate-300">
                    <div>
                        <span class="text-slate-400 block text-[8px]">Cardholder</span>
                        <span id="card-name-preview" class="text-white text-xs">JOHN DOE</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[8px]">Expires</span>
                        <span id="card-exp-preview" class="text-white text-xs">12/28</span>
                    </div>
                </div>
            </div>

            <!-- Card Form -->
            <form action="{{ route('payments.process', $payment->id) }}" method="POST" id="cc-form" class="space-y-4 max-w-md mx-auto">
                @csrf
                <input type="hidden" name="payment_method" value="CREDIT_CARD">
                <input type="hidden" name="requires_3ds" id="input-requires-3ds" value="0">

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nomor Kartu (16 Digit):</label>
                    <input type="text" name="card_number" id="card_number" value="4000 0000 0000 0001" maxlength="19"
                           class="w-full text-xs font-mono font-bold bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-slate-900 focus:ring-2 focus:ring-amber-500"
                           oninput="updateCardPreview()" required>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Pemegang Kartu:</label>
                    <input type="text" name="card_name" id="card_name" value="JOHN DOE"
                           class="w-full text-xs bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-slate-900 focus:ring-2 focus:ring-amber-500 uppercase"
                           oninput="updateCardPreview()" required>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Berlaku Hingga (MM/YY):</label>
                        <input type="text" name="card_expiry" id="card_expiry" value="12/28" maxlength="5"
                               class="w-full text-xs font-mono bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-slate-900 focus:ring-2 focus:ring-amber-500 text-center"
                               oninput="updateCardPreview()" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">CVV / CVC:</label>
                        <input type="password" name="card_cvv" id="card_cvv" value="123" maxlength="4"
                               class="w-full text-xs font-mono bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-slate-900 focus:ring-2 focus:ring-amber-500 text-center" required>
                    </div>
                </div>

                <button type="submit"
                        class="w-full py-3.5 px-4 bg-blue-600 hover:bg-blue-700 text-white font-black text-sm rounded-xl shadow-lg transition flex items-center justify-center gap-2 cursor-pointer">
                    <span>💳</span>
                    <span>Bayar Tagihan ${{ number_format($payment->amount, 2) }}</span>
                </button>
            </form>
        </div>

        <!-- Tab 3: Letter of Credit (SWIFT MT700) -->
        <div id="tab-content-lc" class="p-6 space-y-6 hidden">
            <div>
                <h3 class="text-sm font-black text-slate-900">Simulasi Letter of Credit (L/C) / SWIFT MT700 Escrow</h3>
                <p class="text-xs text-slate-500 mt-0.5">Instrumen jaminan perbankan internasional berstandar ICC UCP 600 untuk pengapalan komoditas.</p>
            </div>

            <!-- Swift MT700 Message Preview Box -->
            <div class="p-4 bg-slate-900 text-emerald-400 font-mono text-xs rounded-2xl shadow-inner border border-slate-800 space-y-2 overflow-x-auto">
                <div class="text-slate-400 font-bold border-b border-slate-800 pb-1.5 flex items-center justify-between">
                    <span>SWIFT HEADER: FIN MT700 (ISSUE OF A DOCUMENTARY CREDIT)</span>
                    <span class="text-amber-400">STATUS: IRREVOCABLE</span>
                </div>
                <div>:27: SEQUENCE OF TOTAL &bull; 1/1</div>
                <div>:40A: FORM OF DOC CREDIT &bull; <strong>IRREVOCABLE & CONFIRMED</strong></div>
                <div>:20: SENDER'S TRN &bull; LC-CT-{{ date('Y') }}-{{ $payment->id }}9921</div>
                <div>:31C: DATE OF ISSUE &bull; {{ date('ymd') }}</div>
                <div>:31D: DATE & PLACE OF EXPIRY &bull; {{ date('ymd', strtotime('+30 days')) }} SINGAPORE</div>
                <div>:50: APPLICANT &bull; {{ $transaction->importer?->company_name ?? Auth::user()->name }}</div>
                <div>:59: BENEFICIARY &bull; {{ $transaction->exporter?->company_name ?? 'COCOA & COFFEE PRODUCER CO-OP' }}</div>
                <div>:32B: CURRENCY & AMOUNT &bull; <strong>USD {{ number_format($payment->amount, 2) }}</strong></div>
                <div>:44E: PORT OF LOADING &bull; {{ $transaction->departure_port }}</div>
                <div>:44F: PORT OF DISCHARGE &bull; {{ $transaction->destination_port }}</div>
                <div>:45A: DESCRIPTION OF GOODS &bull; {{ $transaction->quantity }} MT {{ $transaction->product?->name }} (GRADE A)</div>
                <div>:46A: REQUIRED DOCUMENTS &bull; FULL SET 3/3 CLEAN ON BOARD OCEAN BILL OF LADING, COMMERCIAL INVOICE, PHYTOSANITARY CERTIFICATE, CERTIFICATE OF ORIGIN.</div>
            </div>

            <form action="{{ route('payments.process', $payment->id) }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="payment_method" value="LETTER_OF_CREDIT">
                
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Bank Pembuka / Issuing Bank:</label>
                    <select name="payment_channel" class="w-full text-xs font-semibold bg-slate-50 border border-slate-300 rounded-xl px-3 py-2.5 text-slate-900">
                        <option value="BNI Corporate Trade Finance (Irrevocable L/C)">Bank Negara Indonesia (BNI) Corporate Trade Finance & Escrow</option>
                        <option value="Deutsche Bank AG Amsterdam (Confirmed L/C)">Deutsche Bank AG Amsterdam (Confirmed Documentary Credit)</option>
                        <option value="Standard Chartered Singapore (Trade Escrow)">Standard Chartered Bank Singapore (Trade Facility Escrow)</option>
                        <option value="Rabobank International Agri Finance">Rabobank International Agricultural Commodity Trade Desk</option>
                    </select>
                </div>

                <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-xs text-amber-900">
                    <strong>Ketentuan Escrow Perdagangan:</strong> Dengan mengonfirmasi, dana komoditas senilai <strong>${{ number_format($payment->amount, 2) }}</strong> akan di-lock dalam escrow bank dan kargo akan langsung dirilis untuk embarkasi kapal.
                </div>

                <button type="submit"
                        class="w-full py-4 px-6 bg-slate-900 hover:bg-slate-800 text-white font-black text-sm rounded-xl shadow-lg transition flex items-center justify-center gap-2 cursor-pointer">
                    <span>📜</span>
                    <span>Konfirmasi & Buka Escrow Letter of Credit (L/C Berhasil)</span>
                </button>
            </form>
        </div>

        <!-- Tab 4: QRIS Instant -->
        <div id="tab-content-qris" class="p-6 space-y-6 text-center hidden">
            <div>
                <h3 class="text-sm font-black text-slate-900">Pembayaran Instan QRIS Standar Nasional</h3>
                <p class="text-xs text-slate-500 mt-0.5">Pindai kode QRIS dinamis menggunakan aplikasi mobile banking atau e-wallet apa saja.</p>
            </div>

            <!-- Authentic QRIS Card Frame -->
            <div class="max-w-xs mx-auto p-6 bg-white rounded-2xl border-2 border-slate-900 shadow-xl space-y-4">
                <div class="flex items-center justify-between pb-2 border-b border-slate-200">
                    <span class="font-black text-xs text-rose-600 tracking-wider">QRIS</span>
                    <span class="text-[9px] font-bold text-slate-400">PEMBAYARAN DIGITAL</span>
                </div>

                <div class="text-center space-y-0.5">
                    <div class="font-black text-xs text-slate-900">CARGOTRACE ESCROW COMMODITY</div>
                    <div class="text-[10px] text-slate-500 font-mono">NMID: ID102026889210</div>
                </div>

                <!-- Dynamic SVG QR Code Simulation -->
                <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 flex items-center justify-center">
                    <svg class="w-48 h-48" viewBox="0 0 200 200" fill="none">
                        <!-- Corners -->
                        <rect x="10" y="10" width="50" height="50" stroke="#0f172a" stroke-width="8" fill="none"/>
                        <rect x="22" y="22" width="26" height="26" fill="#0f172a"/>
                        <rect x="140" y="10" width="50" height="50" stroke="#0f172a" stroke-width="8" fill="none"/>
                        <rect x="152" y="22" width="26" height="26" fill="#0f172a"/>
                        <rect x="10" y="140" width="50" height="50" stroke="#0f172a" stroke-width="8" fill="none"/>
                        <rect x="22" y="152" width="26" height="26" fill="#0f172a"/>
                        <!-- QR Pixels Pattern -->
                        <rect x="75" y="15" width="10" height="20" fill="#0f172a"/>
                        <rect x="95" y="15" width="25" height="10" fill="#0f172a"/>
                        <rect x="75" y="45" width="15" height="15" fill="#0f172a"/>
                        <rect x="105" y="40" width="15" height="25" fill="#0f172a"/>
                        <rect x="15" y="75" width="30" height="10" fill="#0f172a"/>
                        <rect x="15" y="95" width="15" height="25" fill="#0f172a"/>
                        <rect x="40" y="110" width="20" height="10" fill="#0f172a"/>
                        <rect x="75" y="75" width="50" height="50" rx="6" fill="#0284c7"/>
                        <!-- Center Logo CT -->
                        <text x="100" y="108" font-size="20" font-weight="900" text-anchor="middle" fill="#ffffff">CT</text>
                        <!-- Bottom Pixels -->
                        <rect x="140" y="75" width="20" height="15" fill="#0f172a"/>
                        <rect x="170" y="80" width="15" height="30" fill="#0f172a"/>
                        <rect x="145" y="120" width="40" height="15" fill="#0f172a"/>
                        <rect x="75" y="145" width="20" height="30" fill="#0f172a"/>
                        <rect x="105" y="140" width="25" height="15" fill="#0f172a"/>
                        <rect x="110" y="165" width="30" height="20" fill="#0f172a"/>
                        <rect x="155" y="150" width="25" height="30" fill="#0f172a"/>
                    </svg>
                </div>

                <div class="text-sm font-black font-mono text-slate-900">
                    Rp {{ number_format($payment->amount * 16250, 0, ',', '.') }}
                </div>
                <div class="text-[10px] text-slate-400">
                    Mendukung BCA, Mandiri, GoPay, OVO, ShopeePay, DANA
                </div>
            </div>

            <form action="{{ route('payments.process', $payment->id) }}" method="POST">
                @csrf
                <input type="hidden" name="payment_method" value="QRIS">
                <input type="hidden" name="payment_channel" value="QRIS Instant Trade Escrow">
                
                <button type="submit"
                        class="max-w-xs mx-auto w-full py-3.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs rounded-xl shadow-lg transition flex items-center justify-center gap-2 cursor-pointer">
                    <span>📱</span>
                    <span>Simulasikan Scan & Bayar QRIS Sukses</span>
                </button>
            </form>
        </div>

    </div>

</div>

<!-- 3D Secure (3DS) OTP Verification Modal -->
@if(session('show_3ds_modal'))
<div class="fixed inset-0 z-50 bg-slate-900/80 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full border border-slate-200 overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        
        <!-- Bank 3DS Header -->
        <div class="bg-slate-900 p-4 text-white flex items-center justify-between border-b border-slate-700">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded bg-blue-600 flex items-center justify-center font-black text-xs">
                    3D
                </div>
                <div>
                    <span class="text-xs font-black block leading-tight">VISA SECURE / MASTERCARD ID CHECK</span>
                    <span class="text-[10px] text-slate-400">Bank Mandiri / BCA Otentikasi Kartu</span>
                </div>
            </div>
            <span class="text-[10px] text-emerald-400 font-mono font-bold bg-emerald-950 px-2 py-0.5 rounded border border-emerald-500/30">
                ENCRYPTED
            </span>
        </div>

        <!-- 3DS Form Content -->
        <form action="{{ route('payments.process', $payment->id) }}" method="POST" class="p-6 space-y-4">
            @csrf
            <input type="hidden" name="payment_method" value="CREDIT_CARD">
            <input type="hidden" name="is_otp_step" value="1">
            <input type="hidden" name="card_number" value="{{ session('temp_card_number') }}">
            <input type="hidden" name="card_name" value="{{ session('temp_card_name') }}">
            <input type="hidden" name="card_expiry" value="{{ session('temp_card_expiry') }}">

            <div class="text-center space-y-1">
                <h4 class="font-black text-base text-slate-900">Verifikasi Transaksi Perbankan</h4>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Kode OTP SMS 6 digit telah dikirimkan ke nomor ponsel terdaftar <strong>+62 812-••••-9901</strong> untuk otorisasi tagihan senilai <strong>${{ number_format($payment->amount, 2) }}</strong>.
                </p>
            </div>

            <div class="p-3 bg-blue-50 rounded-xl border border-blue-200 text-center text-xs text-blue-900 font-semibold">
                💡 Kode OTP Simulasi Uji Coba: <strong class="font-mono text-sm font-black text-blue-700">123456</strong>
            </div>

            <div>
                <label for="otp_code" class="block text-xs font-bold text-slate-700 mb-1 text-center">
                    Masukkan 6 Digit Kode OTP:
                </label>
                <input type="text" name="otp_code" id="otp_code" value="123456" maxlength="6"
                       class="w-48 mx-auto block text-2xl font-mono font-black text-center tracking-widest bg-slate-50 border-2 border-blue-500 rounded-xl py-2 text-slate-900 focus:ring-4 focus:ring-blue-500/20"
                       required autofocus>
                @error('otp_code')
                    <span class="text-xs text-rose-600 block text-center mt-1 font-bold">{{ $message }}</span>
                @enderror
            </div>

            <div class="pt-2 space-y-2">
                <button type="submit"
                        class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-black text-xs rounded-xl shadow-md transition flex items-center justify-center gap-1.5 cursor-pointer">
                    <span>✓</span>
                    <span>Verifikasi OTP & Selesaikan Pembayaran</span>
                </button>
                <a href="{{ route('payments.show', $payment->id) }}"
                   class="block w-full py-2 text-center text-xs text-slate-400 hover:text-slate-600 font-semibold">
                    Batalkan Transaksi
                </a>
            </div>
        </form>

    </div>
</div>
@endif

<script>
    function switchTab(tabKey) {
        ['va', 'cc', 'lc', 'qris'].forEach(k => {
            const btn = document.getElementById('tab-btn-' + k);
            const content = document.getElementById('tab-content-' + k);
            if (k === tabKey) {
                btn.className = 'py-3.5 px-5 border-b-2 border-amber-600 bg-white text-slate-900 font-black flex items-center gap-2 cursor-pointer transition shrink-0';
                content.classList.remove('hidden');
            } else {
                btn.className = 'py-3.5 px-5 border-b-2 border-transparent text-slate-500 hover:text-slate-800 flex items-center gap-2 cursor-pointer transition shrink-0';
                content.classList.add('hidden');
            }
        });
    }

    function selectBank(bankKey, vaNumber, channelName) {
        document.querySelectorAll('.bank-pill').forEach(el => {
            el.classList.remove('border-amber-600', 'bg-amber-50/60', 'shadow-xs', 'ring-2', 'ring-amber-500/20');
            el.classList.add('border-slate-200', 'bg-white');
        });
        const activeCard = document.getElementById('bank-card-' + bankKey);
        if (activeCard) {
            activeCard.classList.add('border-amber-600', 'bg-amber-50/60', 'shadow-xs', 'ring-2', 'ring-amber-500/20');
            activeCard.classList.remove('border-slate-200', 'bg-white');
        }

        document.getElementById('va-number-display').innerText = vaNumber;
        document.getElementById('active-bank-name').innerText = channelName;
        document.getElementById('form-va-channel').value = channelName;
    }

    function copyVA() {
        const vaText = document.getElementById('va-number-display').innerText.trim();
        navigator.clipboard.writeText(vaText).then(() => {
            document.getElementById('copy-icon').innerText = '✓';
            document.getElementById('copy-text').innerText = 'Tersalin!';
            setTimeout(() => {
                document.getElementById('copy-icon').innerText = '📋';
                document.getElementById('copy-text').innerText = 'Salin Nomor VA';
            }, 2500);
        });
    }

    function fillCard(number, name, expiry, cvv, trigger3ds) {
        document.getElementById('card_number').value = number;
        document.getElementById('card_name').value = name;
        document.getElementById('card_expiry').value = expiry;
        document.getElementById('card_cvv').value = cvv;
        document.getElementById('input-requires-3ds').value = trigger3ds ? '1' : '0';
        updateCardPreview();
    }

    function updateCardPreview() {
        const num = document.getElementById('card_number').value || '•••• •••• •••• 0001';
        const name = document.getElementById('card_name').value || 'JOHN DOE';
        const exp = document.getElementById('card_expiry').value || '12/28';

        document.getElementById('card-num-preview').innerText = num;
        document.getElementById('card-name-preview').innerText = name.toUpperCase();
        document.getElementById('card-exp-preview').innerText = exp;

        const clean = num.replace(/\s+/g, '');
        const badge = document.getElementById('card-badge');
        if (clean.startsWith('5')) {
            badge.innerText = 'MASTERCARD';
        } else {
            badge.innerText = 'VISA';
        }
    }

    // Countdown Timer Simulator (24 Hours)
    let secondsLeft = 23 * 3600 + 59 * 60 + 45;
    setInterval(() => {
        if (secondsLeft > 0) {
            secondsLeft--;
            const h = Math.floor(secondsLeft / 3600).toString().padStart(2, '0');
            const m = Math.floor((secondsLeft % 3600) / 60).toString().padStart(2, '0');
            const s = (secondsLeft % 60).toString().padStart(2, '0');
            const timerEl = document.getElementById('timer-display');
            if (timerEl) timerEl.innerText = `${h}:${m}:${s}`;
        }
    }, 1000);
</script>
@endsection
