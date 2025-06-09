<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Prescription;
use App\Models\Purchase;

class NotificationService
{
    /**
     * Check for low stock products and create notifications
     */
    public function checkLowStock()
    {
        $lowStockProducts = Product::whereColumn('stock_quantity', '<=', 'stock_threshold')->get();
        
        foreach ($lowStockProducts as $product) {
            // Check if notification already exists for this product (within last 24 hours)
            $existingNotification = Notification::where('type', 'stock_alert')
                ->where('data->product_id', $product->id)
                ->where('created_at', '>=', now()->subDay())
                ->first();
                
            if (!$existingNotification) {
                Notification::createStockAlert($product);
            }
        }
    }

    /**
     * Check for expiring products and create notifications
     */
    public function checkExpiringProducts($daysAhead = 30)
    {
        $expiringProducts = Product::where('expiry_date', '<=', now()->addDays($daysAhead))
            ->where('expiry_date', '>', now())
            ->get();
            
        foreach ($expiringProducts as $product) {
            $daysUntilExpiry = now()->diffInDays($product->expiry_date);
            
            // Check if notification already exists for this product (within last week)
            $existingNotification = Notification::where('type', 'expiry_alert')
                ->where('data->product_id', $product->id)
                ->where('created_at', '>=', now()->subWeek())
                ->first();
                
            if (!$existingNotification) {
                Notification::createExpiryAlert($product, $daysUntilExpiry);
            }
        }
    }

    /**
     * Send notification when a sale is created
     */
    public function notifySaleCreated(Sale $sale)
    {
        // Only notify for sales above a certain amount
        if ($sale->total_amount >= 50) {
            Notification::createSaleNotification($sale);
        }
    }

    /**
     * Send notification when a prescription is ready
     */
    public function notifyPrescriptionReady(Prescription $prescription)
    {
        Notification::createPrescriptionNotification($prescription);
    }

    /**
     * Send notification when a purchase is received
     */
    public function notifyPurchaseReceived(Purchase $purchase)
    {
        Notification::createPurchaseReceivedNotification($purchase);
    }

    /**
     * Send system alert notification
     */
    public function sendSystemAlert($title, $message, $priority = 'medium', $userIds = null)
    {
        $users = $userIds ? User::whereIn('id', $userIds)->get() : User::all();
        
        foreach ($users as $user) {
            Notification::createNotification(
                $user->id,
                'system_alert',
                $title,
                $message,
                null,
                $priority
            );
        }
    }

    /**
     * Send user activity notification
     */
    public function notifyUserActivity($userId, $activity, $details = null)
    {
        $admins = User::where('role', 'responsable')->get();
        
        foreach ($admins as $admin) {
            Notification::createNotification(
                $admin->id,
                'user_activity',
                'Activité Utilisateur',
                $activity,
                $details,
                'low'
            );
        }
    }

    /**
     * Clean up old notifications
     */
    public function cleanupOldNotifications($days = 30)
    {
        return Notification::cleanOldNotifications($days);
    }

    /**
     * Get unread notification count for user
     */
    public function getUnreadCount($userId)
    {
        return Notification::where('user_id', $userId)
            ->unread()
            ->active()
            ->count();
    }

    /**
     * Get recent notifications for user
     */
    public function getRecentNotifications($userId, $limit = 10)
    {
        return Notification::where('user_id', $userId)
            ->active()
            ->latest()
            ->take($limit)
            ->get();
    }

    /**
     * Mark multiple notifications as read
     */
    public function markMultipleAsRead($notificationIds, $userId)
    {
        return Notification::whereIn('id', $notificationIds)
            ->where('user_id', $userId)
            ->unread()
            ->update(['read_at' => now()]);
    }

    /**
     * Create custom notification
     */
    public function createCustomNotification($userId, $type, $title, $message, $data = null, $priority = 'normal', $actionUrl = null, $expiresAt = null)
    {
        return Notification::createNotification(
            $userId,
            $type,
            $title,
            $message,
            $data,
            $priority,
            $actionUrl,
            $expiresAt
        );
    }

    /**
     * Broadcast notification to all users of a specific role
     */
    public function broadcastToRole($role, $type, $title, $message, $data = null, $priority = 'normal')
    {
        $users = User::where('role', $role)->get();
        
        foreach ($users as $user) {
            $this->createCustomNotification(
                $user->id,
                $type,
                $title,
                $message,
                $data,
                $priority
            );
        }
    }

    /**
     * Get notification statistics
     */
    public function getStatistics($userId)
    {
        $user = User::find($userId);
        
        return [
            'total' => $user->notifications()->count(),
            'unread' => $user->notifications()->unread()->count(),
            'today' => $user->notifications()->whereDate('created_at', today())->count(),
            'this_week' => $user->notifications()->where('created_at', '>=', now()->startOfWeek())->count(),
            'by_type' => $user->notifications()
                ->selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray(),
            'by_priority' => $user->notifications()
                ->selectRaw('priority, COUNT(*) as count')
                ->groupBy('priority')
                ->pluck('count', 'priority')
                ->toArray(),
        ];
    }
}