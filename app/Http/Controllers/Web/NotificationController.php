<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Services\LiveNewsAndMarketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function __construct(
        protected LiveNewsAndMarketService $liveNewsService
    ) {}

    /**
     * Display Notification Center with Live FX & World Port Weather News.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $notifications = $user->notifications()->paginate(15);

        $forceRefresh = $request->boolean('refresh');
        $liveIntelligence = $this->liveNewsService->getAggregatedLiveNewsFeed($forceRefresh);

        $currencyRates = $liveIntelligence['fx']['rates'] ?? [];
        $weatherPorts = $liveIntelligence['weather_ports'] ?? [];
        $newsItems = $liveIntelligence['news_items'] ?? [];
        $generatedAt = $liveIntelligence['generated_at'];
        $fxSource = $liveIntelligence['fx']['source'] ?? 'ExchangeRate-API Global';

        $unreadSystemCount = $user->notifications()->where('is_read', false)->count();

        return view('notifications.index', compact(
            'notifications',
            'currencyRates',
            'weatherPorts',
            'newsItems',
            'generatedAt',
            'fxSource',
            'unreadSystemCount'
        ));
    }

    /**
     * JSON Endpoint for Real-Time Live Feed Refresh via Fetch API.
     */
    public function liveFeeds(Request $request): JsonResponse
    {
        $forceRefresh = $request->boolean('refresh', true);
        $liveIntelligence = $this->liveNewsService->getAggregatedLiveNewsFeed($forceRefresh);

        return response()->json([
            'status' => 'success',
            'message' => 'Berita kurs valuta asing dan cuaca maritim global berhasil diperbarui secara real-time.',
            'data' => $liveIntelligence,
        ]);
    }

    public function markAsRead(int $id): RedirectResponse
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return back()->with('info', 'Notifikasi berhasil ditandai telah dibaca.');
    }

    public function markAllAsRead(): RedirectResponse
    {
        Auth::user()->notifications()->where('is_read', false)->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return back()->with('success', 'Semua notifikasi sistem berhasil ditandai telah dibaca.');
    }
}
