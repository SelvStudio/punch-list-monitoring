<?php

namespace App\Http\Controllers;

use App\Models\InternalNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        $notifications = InternalNotification::where('user_id', $user->id)
            ->with('punchList')
            ->latest()
            ->paginate(15);

        $unreadCount = InternalNotification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    public function markAsRead(InternalNotification $notification): RedirectResponse
    {
        $user = Auth::user();

        if ($notification->user_id !== $user->id) {
            abort(403);
        }

        $notification->markAsRead();

        if ($notification->punch_list_id && $notification->punchList) {
            return redirect()->route('punch-lists.show', $notification->punch_list_id);
        }

        return back()->with('success', 'Notifikasi telah ditandai dibaca.');
    }

    public function markAllAsRead(): RedirectResponse
    {
        $user = Auth::user();

        InternalNotification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return back()->with('success', 'Seluruh notifikasi telah ditandai dibaca.');
    }
}
