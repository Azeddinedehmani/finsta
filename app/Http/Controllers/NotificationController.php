<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Notification;

class NotificationController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of notifications for the authenticated user.
     */
    public function index(Request $request)
    {
        $query = auth()->user()->notifications()->latest();

        // Filter by type
        if ($request->has('type') && $request->type !== '') {
            $query->where('type', $request->type);
        }

        // Filter by read status
        if ($request->has('status')) {
            if ($request->status === 'unread') {
                $query->unread();
            } elseif ($request->status === 'read') {
                $query->read();
            }
        }

        // Filter by priority
        if ($request->has('priority') && $request->priority !== '') {
            $query->where('priority', $request->priority);
        }

        $notifications = $query->active()->paginate(20);
        
        // Get counts for badges
        $unreadCount = auth()->user()->notifications()->unread()->active()->count();
        $totalCount = auth()->user()->notifications()->active()->count();

        return view('notifications.index', compact('notifications', 'unreadCount', 'totalCount'));
    }

    /**
     * Get unread notifications count for AJAX requests.
     */
    public function getUnreadCount()
    {
        $count = auth()->user()->notifications()->unread()->active()->count();
        
        return response()->json(['count' => $count]);
    }

    /**
     * Get recent notifications for dropdown.
     */
    public function getRecent()
    {
        $notifications = auth()->user()->notifications()
            ->active()
            ->latest()
            ->take(5)
            ->get();

        return response()->json([
            'notifications' => $notifications->map(function($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'type' => $notification->type,
                    'type_label' => $notification->type_label,
                    'type_icon' => $notification->type_icon,
                    'priority' => $notification->priority,
                    'priority_badge' => $notification->priority_badge,
                    'is_read' => $notification->isRead(),
                    'action_url' => $notification->action_url,
                    'created_at' => $notification->created_at->diffForHumans(),
                ];
            }),
            'unread_count' => auth()->user()->notifications()->unread()->active()->count()
        ]);
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead($id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        // If there's an action URL, redirect to it
        if ($notification->action_url) {
            return redirect($notification->action_url);
        }

        return redirect()->back()->with('success', 'Notification marquée comme lue.');
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        auth()->user()->notifications()
            ->unread()
            ->active()
            ->update(['read_at' => now()]);

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Toutes les notifications ont été marquées comme lues.');
    }

    /**
     * Delete a notification.
     */
    public function destroy($id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->delete();

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Notification supprimée.');
    }

    /**
     * Delete all read notifications.
     */
    public function deleteAllRead()
    {
        $deletedCount = auth()->user()->notifications()
            ->read()
            ->delete();

        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'deleted_count' => $deletedCount]);
        }

        return redirect()->back()->with('success', "{$deletedCount} notifications supprimées.");
    }

    /**
     * Create a test notification (for development/testing).
     */
    public function createTest()
    {
        if (!app()->environment('local')) {
            abort(403, 'Cette fonctionnalité est uniquement disponible en développement.');
        }

        $types = ['stock_alert', 'expiry_alert', 'sale_created', 'prescription_ready', 'purchase_received', 'system_alert'];
        $priorities = ['low', 'normal', 'medium', 'high'];
        
        $type = $types[array_rand($types)];
        $priority = $priorities[array_rand($priorities)];

        Notification::createNotification(
            auth()->id(),
            $type,
            'Notification de test',
            'Ceci est une notification de test générée automatiquement.',
            ['test' => true],
            $priority
        );

        return redirect()->back()->with('success', 'Notification de test créée.');
    }

    /**
     * Show notification settings.
     */
    public function settings()
    {
        return view('notifications.settings');
    }

    /**
     * Update notification settings.
     */
    public function updateSettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email_notifications' => 'boolean',
            'browser_notifications' => 'boolean',
            'stock_alerts' => 'boolean',
            'expiry_alerts' => 'boolean',
            'sale_notifications' => 'boolean',
            'prescription_notifications' => 'boolean',
            'purchase_notifications' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Update user preferences (you might want to create a user_settings table)
        $user = auth()->user();
        
        // For now, store in user's permissions field as an example
        $settings = $user->permissions ?? [];
        $settings['notifications'] = [
            'email_notifications' => $request->has('email_notifications'),
            'browser_notifications' => $request->has('browser_notifications'),
            'stock_alerts' => $request->has('stock_alerts'),
            'expiry_alerts' => $request->has('expiry_alerts'),
            'sale_notifications' => $request->has('sale_notifications'),
            'prescription_notifications' => $request->has('prescription_notifications'),
            'purchase_notifications' => $request->has('purchase_notifications'),
        ];
        
        $user->permissions = $settings;
        $user->save();

        return redirect()->back()->with('success', 'Paramètres de notification mis à jour.');
    }
}