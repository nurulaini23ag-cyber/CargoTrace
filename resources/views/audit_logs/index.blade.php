@extends('layouts.app')

@section('title', 'Security & System Audit Logs')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">System Audit Trail</h1>
            <p class="text-sm text-slate-500">Immutable ledger of administrative, operational, and lifecycle transactions</p>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
        <form action="{{ route('audit_logs.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
            <input type="text" name="action" value="{{ request('action') }}" placeholder="Filter action (e.g. STATUS_TRANSITION)" class="text-xs rounded border border-slate-300 px-3 py-1.5 w-64">
            <select name="entity_type" class="text-xs rounded border border-slate-300 px-3 py-1.5">
                <option value="">All Entity Types</option>
                <option value="Shipment" {{ request('entity_type') === 'Shipment' ? 'selected' : '' }}>Shipment</option>
                <option value="TrackingEvent" {{ request('entity_type') === 'TrackingEvent' ? 'selected' : '' }}>TrackingEvent</option>
                <option value="ShipmentException" {{ request('entity_type') === 'ShipmentException' ? 'selected' : '' }}>ShipmentException</option>
                <option value="User" {{ request('entity_type') === 'User' ? 'selected' : '' }}>User</option>
                <option value="Carrier" {{ request('entity_type') === 'Carrier' ? 'selected' : '' }}>Carrier</option>
                <option value="SystemSetting" {{ request('entity_type') === 'SystemSetting' ? 'selected' : '' }}>SystemSetting</option>
            </select>
            <button type="submit" class="px-3 py-1.5 text-xs font-semibold bg-slate-900 text-white rounded">Filter</button>
            <a href="{{ route('audit_logs.index') }}" class="px-3 py-1.5 text-xs border rounded text-slate-600 hover:bg-slate-50">Reset</a>
        </form>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500 border-b border-slate-200">
                <tr>
                    <th class="px-5 py-3">Timestamp</th>
                    <th class="px-5 py-3">Actor</th>
                    <th class="px-5 py-3">Action</th>
                    <th class="px-5 py-3">Entity</th>
                    <th class="px-5 py-3">Description</th>
                    <th class="px-5 py-3 text-right">IP Address</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-xs">
                @forelse($auditLogs as $log)
                <tr class="hover:bg-slate-50/60 transition">
                    <td class="px-5 py-3 font-mono text-slate-500 whitespace-nowrap">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                    <td class="px-5 py-3 font-semibold text-slate-800">{{ $log->user ? $log->user->name : 'System / Guest' }}</td>
                    <td class="px-5 py-3">
                        <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold uppercase bg-slate-100 text-slate-800">
                            {{ $log->action }}
                        </span>
                    </td>
                    <td class="px-5 py-3 font-mono text-slate-500">{{ $log->entity_type }} #{{ $log->entity_id }}</td>
                    <td class="px-5 py-3 text-slate-700 max-w-xs truncate" title="{{ $log->description }}">{{ $log->description }}</td>
                    <td class="px-5 py-3 text-right font-mono text-slate-400">{{ $log->ip_address }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-5 py-8 text-center text-slate-400">No audit records found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 border-t border-slate-200">
            {{ $auditLogs->links() }}
        </div>
    </div>
</div>
@endsection
