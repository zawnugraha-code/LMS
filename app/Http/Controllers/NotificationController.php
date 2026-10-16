<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    /**
     * Get recent notifications and unread count JSON.
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['unread' => 0, 'items' => []]);
        }

        return response()->json([
            'unread' => $this->notificationService->getUnreadCount($user),
            'items' => $this->notificationService->getRecentNotifications($user, 10),
        ]);
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead(string $id, Request $request): JsonResponse|RedirectResponse
    {
        $user = Auth::user();
        if ($user) {
            $this->notificationService->markAsRead($id, $user);
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back();
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(Request $request): JsonResponse|RedirectResponse
    {
        $user = Auth::user();
        if ($user) {
            $this->notificationService->markAllAsRead($user);
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Semua notifikasi telah ditandai sebagai dibaca.');
    }

    /**
     * Display all notifications page.
     */
    public function all(Request $request): View
    {
        $user = Auth::user();
        $notifications = $this->notificationService->getRecentNotifications($user, 50);

        return view('pages.app.notifications', [
            'user' => $user,
            'campus' => $user?->campus,
            'notifications' => $notifications,
        ]);
    }
}
