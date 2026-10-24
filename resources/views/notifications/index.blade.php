@extends('layouts.app')

@section('title', 'In-App Notifications')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">System & Tracking Notifications</h1>
            <p class="text-sm text-slate-500">Alerts regarding status updates, schedule delay warnings, and incident reports</p>
        </div>
        <form action="{{ route('notifications.readAll') }}" method="POST">
            @csrf
            <button type="submit" class="px-3 py-1.5 text-xs font-semibold text-sky-700 bg-sky-50 hover:bg-sky-100 border border-sky-200 rounded-lg transition">
                Mark All as Read
            </button>
        </form>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm divide-y divide-slate-100 overflow-hidden">
        @forelse($notifications as $notif)
        <div class="p-4 sm:p-5 flex items-start justify-between gap-4 {{ $notif->is_read ? 'bg-white' : 'bg-sky-50/40' }} transition">
            <div class="flex items-start space-x-3">
                <span class="w-2.5 h-2.5 rounded-full mt-1.5 flex-shrink-0 {{ $notif->is_read ? 'bg-slate-300' : 'bg-sky-600 ring-2 ring-sky-200' }}"></span>
                <div>
                    <div class="flex items-center space-x-2">
                        <span class="font-bold text-slate-900 text-sm">{{ $notif->title }}</span>
                        <span class="px-2 py-0.2 rounded text-[10px] font-bold uppercase
                            @if($notif->type === 'DELAY_ALERT') bg-amber-100 text-amber-800
                            @elseif($notif->type === 'EXCEPTION_ALERT') bg-rose-100 text-rose-800
                            @else bg-blue-100 text-blue-800 @endif">
                            {{ $notif->type }}
                        </span>
                        <span class="text-xs text-slate-400">{{ $notif->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="text-xs text-slate-600 mt-1">{{ $notif->message }}</p>

                    @if($notif->shipment_id)
                    <div class="mt-2">
                        <a href="{{ route('shipments.show', $notif->shipment_id) }}" class="text-xs font-semibold text-sky-600 hover:underline">
                            Inspect Shipment &rarr;
                        </a>
                    </div>
                    @endif
                </div>
            </div>

            @if(!$notif->is_read)
            <form action="{{ route('notifications.read', $notif->id) }}" method="POST">
                @csrf
                <button type="submit" class="text-xs text-slate-400 hover:text-slate-600 whitespace-nowrap">
                    Mark Read
                </button>
            </form>
            @endif
        </div>
        @empty
        <div class="p-12 text-center text-slate-400 text-sm">
            You have no notifications at this time.
        </div>
        @endforelse
    </div>

    <div class="p-2">
        {{ $notifications->links() }}
    </div>
</div>
@endsection
