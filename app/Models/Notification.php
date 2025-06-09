<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'read_at',
        'action_url',
        'priority',
        'expires_at'
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the notification
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead()
    {
        $this->update(['read_at' => now()]);
    }

    /**
     * Check if notification is read
     */
    public function isRead()
    {
        return !is_null($this->read_at);
    }

    /**
     * Check if notification is expired
     */
    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Scope for unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope for read notifications
     */
    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    /**
     * Scope for non-expired notifications
     */
    public function scopeActive($query)
    {
        return $query->where(function($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Scope for specific type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get priority badge class
     */
    public function getPriorityBadgeAttribute()
    {
        return match($this->priority) {
            'high' => 'bg-danger',
            'medium' => 'bg-warning text-dark',
            'low' => 'bg-info',
            default => 'bg-secondary'
        };
    }

    /**
     * Get priority label
     */
    public function getPriorityLabelAttribute()
    {
        return match($this->priority) {
            'high' => 'Élevée',
            'medium' => 'Moyenne',
            'low' => 'Faible',
            default => 'Normale'
        };
    }

    /**
     * Get type icon
     */
    public function getTypeIconAttribute()
    {
        return match($this->type) {
            'stock_alert' => 'fas fa-box',
            'expiry_alert' => 'fas fa-clock',
            'sale_created' => 'fas fa-shopping-cart',
            'prescription_ready' => 'fas fa-file-prescription',
            'purchase_received' => 'fas fa-truck',
            'system_alert' => 'fas fa-exclamation-triangle',
            'user_activity' => 'fas fa-user',
            default => 'fas fa-bell'
        };
    }

    /**
     * Get type label
     */
    public function getTypeLabelAttribute()
    {
        return match($this->type) {
            'stock_alert' => 'Alerte Stock',
            'expiry_alert' => 'Expiration',
            'sale_created' => 'Vente',
            'prescription_ready' => 'Ordonnance',
            'purchase_received' => 'Livraison',
            'system_alert' => 'Système',
            'user_activity' => 'Activité',
            default => 'Notification'
        };
    }

    /**
     * Create a notification
     */
    public static function createNotification($userId, $type, $title, $message, $data = null, $priority = 'normal', $actionUrl = null, $expiresAt = null)
    {
        return static::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'priority' => $priority,
            'action_url' => $actionUrl,
            'expires_at' => $expiresAt,
        ]);
    }

    /**
     * Create stock alert notification
     */
    public static function createStockAlert($product)
    {
        $admins = User::where('role', 'responsable')->get();
        
        foreach ($admins as $admin) {
            static::createNotification(
                $admin->id,
                'stock_alert',
                'Alerte Stock Faible',
                "Le produit {$product->name} a un stock faible ({$product->stock_quantity} unités restantes)",
                ['product_id' => $product->id, 'current_stock' => $product->stock_quantity],
                'high',
                route('inventory.show', $product->id)
            );
        }
    }

    /**
     * Create expiry alert notification
     */
    public static function createExpiryAlert($product, $daysUntilExpiry)
    {
        $users = User::all();
        
        foreach ($users as $user) {
            static::createNotification(
                $user->id,
                'expiry_alert',
                'Produit Bientôt Expiré',
                "Le produit {$product->name} expire dans {$daysUntilExpiry} jours",
                ['product_id' => $product->id, 'expiry_date' => $product->expiry_date, 'days_until_expiry' => $daysUntilExpiry],
                'medium',
                route('inventory.show', $product->id)
            );
        }
    }

    /**
     * Create sale notification
     */
    public static function createSaleNotification($sale)
    {
        $admins = User::where('role', 'responsable')->get();
        
        foreach ($admins as $admin) {
            static::createNotification(
                $admin->id,
                'sale_created',
                'Nouvelle Vente',
                "Vente #{$sale->sale_number} de {$sale->total_amount}€ effectuée par {$sale->user->name}",
                ['sale_id' => $sale->id, 'amount' => $sale->total_amount],
                'low',
                route('sales.show', $sale->id)
            );
        }
    }

    /**
     * Create prescription notification
     */
    public static function createPrescriptionNotification($prescription)
    {
        $users = User::all();
        
        foreach ($users as $user) {
            static::createNotification(
                $user->id,
                'prescription_ready',
                'Ordonnance Prête',
                "L'ordonnance #{$prescription->prescription_number} pour {$prescription->client->full_name} est prête",
                ['prescription_id' => $prescription->id],
                'medium',
                route('prescriptions.show', $prescription->id)
            );
        }
    }

    /**
     * Create purchase received notification
     */
    public static function createPurchaseReceivedNotification($purchase)
    {
        $admins = User::where('role', 'responsable')->get();
        
        foreach ($admins as $admin) {
            static::createNotification(
                $admin->id,
                'purchase_received',
                'Livraison Reçue',
                "Commande #{$purchase->purchase_number} de {$purchase->supplier->name} reçue",
                ['purchase_id' => $purchase->id],
                'low',
                route('purchases.show', $purchase->id)
            );
        }
    }

    /**
     * Clean up old read notifications
     */
    public static function cleanOldNotifications($days = 30)
    {
        return static::whereNotNull('read_at')
                    ->where('read_at', '<', now()->subDays($days))
                    ->delete();
    }
}