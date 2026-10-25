@extends('layouts.app')

@section('title', 'User Accounts & Roles')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">User Accounts & RBAC Roles</h1>
            <p class="text-sm text-slate-500">Manage Administrator, Logistics Operator, and Customer accounts</p>
        </div>
        <button type="button" onclick="document.getElementById('add-user-modal').classList.toggle('hidden')"
            class="px-4 py-2 bg-sky-600 hover:bg-sky-500 text-white text-sm font-semibold rounded-lg shadow-sm transition">
            + Add User Account
        </button>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500 border-b border-slate-200">
                <tr>
                    <th class="px-5 py-3.5">Name</th>
                    <th class="px-5 py-3.5">Email</th>
                    <th class="px-5 py-3.5">Company</th>
                    <th class="px-5 py-3.5">Role</th>
                    <th class="px-5 py-3.5">Associated Shipments</th>
                    <th class="px-5 py-3.5 text-right">Change Role</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($users as $user)
                <tr class="hover:bg-slate-50/60 transition">
                    <td class="px-5 py-3.5 font-bold text-slate-900">{{ $user->name }}</td>
                    <td class="px-5 py-3.5 text-xs text-slate-600">{{ $user->email }}</td>
                    <td class="px-5 py-3.5 text-xs text-slate-500">{{ $user->company_name ?? '—' }}</td>
                    <td class="px-5 py-3.5">
                        <span class="px-2 py-0.5 rounded text-[11px] font-bold uppercase
                            @if($user->isAdmin()) bg-purple-100 text-purple-800
                            @elseif($user->isOperator()) bg-emerald-100 text-emerald-800
                            @else bg-sky-100 text-sky-800 @endif">
                            {{ $user->role }}
                        </span>
                    </td>
                    <td class="px-5 py-3.5 text-xs font-semibold">{{ $user->shipments_count }} shipments</td>
                    <td class="px-5 py-3.5 text-right">
                        <form action="{{ route('users.role', $user->id) }}" method="POST" class="inline-flex items-center space-x-1">
                            @csrf
                            @method('PATCH')
                            <select name="role" onchange="this.form.submit()" class="text-xs rounded border border-slate-200 px-2 py-1 text-slate-700 bg-white">
                                <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="operator" {{ $user->role === 'operator' ? 'selected' : '' }}>Operator</option>
                                <option value="customer" {{ $user->role === 'customer' ? 'selected' : '' }}>Customer</option>
                            </select>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-5 py-8 text-center text-slate-400">No users found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 border-t border-slate-200">
            {{ $users->links() }}
        </div>
    </div>
</div>

<!-- Modal: Add User -->
<div id="add-user-modal" class="fixed inset-0 bg-slate-900/60 z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-xl max-w-md w-full p-6 shadow-2xl border border-slate-200">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="text-base font-bold text-slate-900">Create User Account</h3>
            <button type="button" onclick="document.getElementById('add-user-modal').classList.add('hidden')" class="text-slate-400 text-lg">&times;</button>
        </div>
        <form action="{{ route('users.store') }}" method="POST" class="mt-4 space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-semibold uppercase text-slate-700 mb-1">Full Name *</label>
                <input type="text" name="name" required class="w-full text-xs rounded border border-slate-300 px-3 py-2">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase text-slate-700 mb-1">Email Address *</label>
                <input type="email" name="email" required class="w-full text-xs rounded border border-slate-300 px-3 py-2">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase text-slate-700 mb-1">Role Permission *</label>
                <select name="role" required class="w-full text-xs rounded border border-slate-300 px-3 py-2">
                    <option value="customer">Customer (View own shipments, timeline, map)</option>
                    <option value="operator">Logistics Operator (CRUD shipments, tracking, exceptions)</option>
                    <option value="admin">Administrator (Full system, users, carriers, settings, audit)</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase text-slate-700 mb-1">Company Name</label>
                <input type="text" name="company_name" class="w-full text-xs rounded border border-slate-300 px-3 py-2">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase text-slate-700 mb-1">Password *</label>
                <input type="password" name="password" required minlength="8" class="w-full text-xs rounded border border-slate-300 px-3 py-2">
            </div>
            <div class="flex items-center justify-end space-x-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('add-user-modal').classList.add('hidden')" class="px-3 py-1.5 text-xs border rounded text-slate-600">Cancel</button>
                <button type="submit" class="px-4 py-1.5 text-xs font-semibold bg-sky-600 text-white rounded hover:bg-sky-500">Create Account</button>
            </div>
        </form>
    </div>
</div>
@endsection
