@extends('layouts.app')

@section('title', 'Business Rules & Group Scenario Config')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Business Rules & Scenario Configuration</h1>
        <p class="text-sm text-slate-500">Configure delay detection thresholds and UAS student group scenario parameters</p>
    </div>

    <!-- Group Scenario Presets Card (Slide 20 & 23) -->
    <div class="bg-gradient-to-r from-sky-900 to-indigo-900 text-white p-6 rounded-xl shadow-md space-y-4">
        <div>
            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-sky-500/30 text-sky-200 border border-sky-400/40">
                UAS Group Scenario Selector (Slide 23)
            </span>
            <h2 class="text-lg font-bold mt-1 text-white">Active Scenario: Group {{ $settings['group_scenario'] ?? 'A' }}</h2>
            <p class="text-xs text-sky-200/90">
                Click any preset below to instantly reconfigure system business rules, delay thresholds, and case studies to match the specific UAS defense scenario.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 pt-2">
            <!-- Group A Preset -->
            <div class="bg-white/10 hover:bg-white/15 p-4 rounded-lg border {{ ($settings['group_scenario'] ?? 'A') === 'A' ? 'border-sky-300 ring-2 ring-sky-300' : 'border-white/20' }} transition flex flex-col justify-between">
                <div>
                    <div class="font-bold text-sm text-white">Group A Scenario</div>
                    <div class="text-xs text-sky-200 mt-1">Route: Southeast Asia &rarr; Europe</div>
                    <div class="text-xs text-sky-300 mt-0.5">Delay Threshold: 12 Hours</div>
                    <div class="text-[11px] text-sky-100/70 mt-1">Primary Case: Customs Hold Inspection</div>
                </div>
                <form action="{{ route('settings.preset', 'A') }}" method="POST" class="mt-3">
                    @csrf
                    <button type="submit" class="w-full py-1.5 px-3 rounded bg-white text-slate-900 text-xs font-bold hover:bg-sky-50 transition shadow-sm">
                        Activate Group A
                    </button>
                </form>
            </div>

            <!-- Group B Preset -->
            <div class="bg-white/10 hover:bg-white/15 p-4 rounded-lg border {{ ($settings['group_scenario'] ?? '') === 'B' ? 'border-sky-300 ring-2 ring-sky-300' : 'border-white/20' }} transition flex flex-col justify-between">
                <div>
                    <div class="font-bold text-sm text-white">Group B Scenario</div>
                    <div class="text-xs text-sky-200 mt-1">Route: Asia &rarr; North America</div>
                    <div class="text-xs text-sky-300 mt-0.5">Delay Threshold: 18 Hours</div>
                    <div class="text-[11px] text-sky-100/70 mt-1">Primary Case: Weather Delay & Typhoon</div>
                </div>
                <form action="{{ route('settings.preset', 'B') }}" method="POST" class="mt-3">
                    @csrf
                    <button type="submit" class="w-full py-1.5 px-3 rounded bg-white text-slate-900 text-xs font-bold hover:bg-sky-50 transition shadow-sm">
                        Activate Group B
                    </button>
                </form>
            </div>

            <!-- Group C Preset -->
            <div class="bg-white/10 hover:bg-white/15 p-4 rounded-lg border {{ ($settings['group_scenario'] ?? '') === 'C' ? 'border-sky-300 ring-2 ring-sky-300' : 'border-white/20' }} transition flex flex-col justify-between">
                <div>
                    <div class="font-bold text-sm text-white">Group C Scenario</div>
                    <div class="text-xs text-sky-200 mt-1">Route: Southeast Asia &rarr; Australia</div>
                    <div class="text-xs text-sky-300 mt-0.5">Delay Threshold: 24 Hours</div>
                    <div class="text-[11px] text-sky-100/70 mt-1">Primary Case: Documentation Discrepancy</div>
                </div>
                <form action="{{ route('settings.preset', 'C') }}" method="POST" class="mt-3">
                    @csrf
                    <button type="submit" class="w-full py-1.5 px-3 rounded bg-white text-slate-900 text-xs font-bold hover:bg-sky-50 transition shadow-sm">
                        Activate Group C
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Manual Delay Rule Config Form (Slide 08) -->
    <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
        <h2 class="text-base font-bold text-slate-900 mb-2">Deterministic Delay Rule Matrix</h2>
        <p class="text-xs text-slate-500 mb-4">
            Under Slide 08 rules: When Estimated Arrival (ETA) exceeds Expected Arrival, delay hours are calculated and classified into Low, Medium, and High categories based on these thresholds.
        </p>

        <form action="{{ route('settings.update') }}" method="POST" class="space-y-4">
            @csrf

            <input type="hidden" name="group_scenario" value="{{ $settings['group_scenario'] ?? 'A' }}">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-700 mb-1">Low Severity Delay Max Hours (LOW)</label>
                    <input type="number" step="0.5" min="1" name="delay_threshold_low" value="{{ old('delay_threshold_low', $settings['delay_threshold_low'] ?? '6') }}" required
                        class="w-full text-sm rounded-lg border border-slate-300 px-3 py-2 text-slate-900">
                    <span class="text-[11px] text-slate-400">Delays between 0h and this threshold are tagged LOW.</span>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-700 mb-1">Medium Severity Delay Max Hours (MEDIUM)</label>
                    <input type="number" step="0.5" min="2" name="delay_threshold_medium" value="{{ old('delay_threshold_medium', $settings['delay_threshold_medium'] ?? '24') }}" required
                        class="w-full text-sm rounded-lg border border-slate-300 px-3 py-2 text-slate-900">
                    <span class="text-[11px] text-slate-400">Delays between Low and this threshold are tagged MEDIUM. Delays above this are HIGH.</span>
                </div>
            </div>

            <div class="pt-2 flex justify-end">
                <button type="submit" class="px-5 py-2 bg-sky-600 hover:bg-sky-500 text-white rounded-lg text-sm font-semibold shadow-sm transition">
                    Save Business Rules
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
