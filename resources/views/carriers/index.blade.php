@extends('layouts.app')

@section('title', 'Logistics Carriers')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Logistics Carriers</h1>
            <p class="text-sm text-slate-500">Manage shipping lines, freight airlines, and intermodal transport operators</p>
        </div>
        <button type="button" onclick="document.getElementById('add-carrier-modal').classList.toggle('hidden')"
            class="px-4 py-2 bg-sky-600 hover:bg-sky-500 text-white text-sm font-semibold rounded-lg shadow-sm transition">
            + Add Carrier
        </button>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500 border-b border-slate-200">
                <tr>
                    <th class="px-5 py-3.5">Carrier Code</th>
                    <th class="px-5 py-3.5">Name</th>
                    <th class="px-5 py-3.5">Mode</th>
                    <th class="px-5 py-3.5">Contact Email</th>
                    <th class="px-5 py-3.5">Shipments</th>
                    <th class="px-5 py-3.5">Status</th>
                    <th class="px-5 py-3.5 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($carriers as $carrier)
                <tr class="hover:bg-slate-50/60 transition">
                    <td class="px-5 py-3.5 font-mono font-bold text-slate-900">{{ $carrier->code }}</td>
                    <td class="px-5 py-3.5 font-semibold text-slate-800">{{ $carrier->name }}</td>
                    <td class="px-5 py-3.5">
                        <span class="px-2 py-0.5 rounded text-[11px] font-bold uppercase bg-slate-100 text-slate-700">
                            {{ $carrier->mode }}
                        </span>
                    </td>
                    <td class="px-5 py-3.5 text-xs text-slate-500">{{ $carrier->contact_email ?? 'N/A' }}</td>
                    <td class="px-5 py-3.5 text-xs font-semibold">{{ $carrier->shipments_count }} shipments</td>
                    <td class="px-5 py-3.5">
                        <span class="px-2 py-0.5 rounded text-[11px] font-bold uppercase {{ $carrier->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500' }}">
                            {{ $carrier->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="px-5 py-3.5 text-right">
                        <form action="{{ route('carriers.toggle', $carrier->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-xs text-sky-600 hover:underline">
                                {{ $carrier->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-5 py-8 text-center text-slate-400">No carriers registered.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 border-t border-slate-200">
            {{ $carriers->links() }}
        </div>
    </div>
</div>

<!-- Modal: Add Carrier -->
<div id="add-carrier-modal" class="fixed inset-0 bg-slate-900/60 z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-xl max-w-md w-full p-6 shadow-2xl border border-slate-200">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="text-base font-bold text-slate-900">Add Carrier</h3>
            <button type="button" onclick="document.getElementById('add-carrier-modal').classList.add('hidden')" class="text-slate-400 text-lg">&times;</button>
        </div>
        <form action="{{ route('carriers.store') }}" method="POST" class="mt-4 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold uppercase text-slate-700 mb-1">Carrier Code (e.g. MAEU) *</label>
                <input type="text" name="code" required class="w-full text-xs rounded border border-slate-300 px-3 py-2">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase text-slate-700 mb-1">Company Name *</label>
                <input type="text" name="name" required class="w-full text-xs rounded border border-slate-300 px-3 py-2">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase text-slate-700 mb-1">Primary Transport Mode *</label>
                <select name="mode" required class="w-full text-xs rounded border border-slate-300 px-3 py-2">
                    <option value="SEA">SEA (Ocean Freight)</option>
                    <option value="AIR">AIR (Air Cargo)</option>
                    <option value="ROAD">ROAD (Trucking)</option>
                    <option value="RAIL">RAIL (Rail Freight)</option>
                    <option value="MULTIMODAL">MULTIMODAL</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase text-slate-700 mb-1">Contact Email</label>
                <input type="email" name="contact_email" class="w-full text-xs rounded border border-slate-300 px-3 py-2">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase text-slate-700 mb-1">Website URL</label>
                <input type="url" name="website" placeholder="https://" class="w-full text-xs rounded border border-slate-300 px-3 py-2">
            </div>
            <div class="flex items-center justify-end space-x-2 pt-2">
                <button type="button" onclick="document.getElementById('add-carrier-modal').classList.add('hidden')" class="px-3 py-1.5 text-xs border rounded text-slate-600">Cancel</button>
                <button type="submit" class="px-4 py-1.5 text-xs font-semibold bg-sky-600 text-white rounded hover:bg-sky-500">Save Carrier</button>
            </div>
        </form>
    </div>
</div>
@endsection
