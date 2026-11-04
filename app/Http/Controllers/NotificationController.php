<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function __construct(
        protected NotificationService $service,
    ) {}

    public function index(Request $request): View
    {
        $notifications = \App\Models\AppNotification::where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function markRead(int $id): RedirectResponse
    {
        $this->service->markRead($id, auth()->id());
        return back();
    }

    public function markAllRead(Request $request): RedirectResponse|JsonResponse
    {
        $this->service->markAllRead(auth()->id());

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Semua notifikasi ditandai sudah dibaca.']);
        }

        return back()->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }

    public function destroy(int $id): RedirectResponse
    {
        \App\Models\AppNotification::where('id', $id)
            ->where('user_id', auth()->id())
            ->delete();
        return back();
    }

    /**
     * API endpoint — returns unread count + last 10 for dropdown.
     */
    public function dropdown(): JsonResponse
    {
        $userId        = auth()->id();
        $unreadCount   = $this->service->unreadCount($userId);
        $recent        = $this->service->recent($userId, 10);

        return response()->json([
            'unread_count' => $unreadCount,
            'notifications' => $recent->map(fn ($n) => [
                'id'         => $n->id,
                'icon'       => $n->icon,
                'title'      => $n->title,
                'message'    => $n->message,
                'is_read'    => $n->isRead(),
                'color'      => $n->color_class,
                'url'        => $n->data['url'] ?? null,
                'created_at' => $n->created_at->diffForHumans(),
            ]),
        ]);
    }
}
