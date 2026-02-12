<?php

// 📁 app/Http/Controllers/NotificationsController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationsController extends Controller
{
    /**
     * Get notifications data for the bell dropdown (polled every 30s)
     */
    public function getNotificationsData(Request $request)
    {
        try {
            $user = Auth::user();

            // ✅ Guard: check if user model uses Notifiable trait
            if (!method_exists($user, 'unreadNotifications')) {
                return response()->json([
                    'count'         => 0,
                    'notifications' => [],
                    'error'         => 'Notifiable trait not found on User model.',
                ]);
            }

            $unread      = $user->unreadNotifications()->latest()->take(10)->get();
            $unreadCount = $user->unreadNotifications()->count();

            $notifications = $unread->map(function ($noti) {
                return [
                    'id'        => $noti->id,
                    'icon'      => $noti->data['icon']    ?? 'fas fa-bell text-info',
                    'message'   => $noti->data['message'] ?? 'New notification',
                    'time'      => $noti->created_at->format('M d, Y h:i A'),
                    'time_ago'  => $noti->created_at->diffForHumans(),
                    'url'       => $noti->data['url']     ?? '#',
                    'type'      => $noti->data['type']    ?? 'general',
                    'action'    => $noti->data['action']  ?? '',
                    'is_unread' => true,
                ];
            });

            return response()->json([
                'count'         => $unreadCount,
                'notifications' => $notifications,
            ]);
        } catch (\Exception $e) {
            \Log::error('Notification fetch error: ' . $e->getMessage());
            return response()->json([
                'count'         => 0,
                'notifications' => [],
            ]);
        }
    }

    /**
     * Mark a single notification as read
     */
    public function markAsRead(Request $request, $id)
    {
        try {
            $notification = Auth::user()
                ->notifications()
                ->where('id', $id)
                ->first();

            if ($notification) {
                $notification->markAsRead();
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Log::error('Mark as read error: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Mark ALL notifications as read
     */
    public function markAllRead(Request $request)
    {
        try {
            Auth::user()->unreadNotifications->markAsRead();

            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read',
            ]);
        } catch (\Exception $e) {
            \Log::error('Mark all read error: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get unread count only (lightweight poll)
     */
    public function getCount(Request $request)
    {
        try {
            $count = Auth::user()->unreadNotifications()->count();
            return response()->json(['count' => $count]);
        } catch (\Exception $e) {
            return response()->json(['count' => 0]);
        }
    }
}
