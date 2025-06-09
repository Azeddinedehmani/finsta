<?php

namespace App\Observers;

use App\Models\Product;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;

class ProductObserver
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updated(Product $product)
    {
        try {
            // Check if stock quantity was changed
            if ($product->wasChanged('stock_quantity')) {
                $this->handleStockChange($product);
            }

            // Check if expiry date was changed
            if ($product->wasChanged('expiry_date')) {
                $this->checkExpiryDate($product);
            }

            // Check if stock threshold was changed
            if ($product->wasChanged('stock_threshold')) {
                $this->checkStockLevel($product);
            }
        } catch (\Exception $e) {
            Log::error('Error in ProductObserver updated: ' . $e->getMessage());
        }
    }

    /**
     * Handle the Product "created" event.
     */
    public function created(Product $product)
    {
        try {
            // Check stock level and expiry for new products
            $this->checkStockLevel($product);
            $this->checkExpiryDate($product);
        } catch (\Exception $e) {
            Log::error('Error in ProductObserver created: ' . $e->getMessage());
        }
    }

    /**
     * Handle stock changes
     */
    private function handleStockChange(Product $product)
    {
        $oldStock = $product->getOriginal('stock_quantity');
        $newStock = $product->stock_quantity;
        
        Log::info('Product stock changed', [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'old_stock' => $oldStock,
            'new_stock' => $newStock,
            'threshold' => $product->stock_threshold
        ]);

        // If stock went from above threshold to at or below threshold
        if ($oldStock > $product->stock_threshold && $newStock <= $product->stock_threshold) {
            $this->createStockAlert($product);
        }

        // If stock went to zero
        if ($oldStock > 0 && $newStock <= 0) {
            $this->createOutOfStockAlert($product);
        }

        // If stock was restored above threshold, we might want to clear old notifications
        if ($oldStock <= $product->stock_threshold && $newStock > $product->stock_threshold) {
            $this->clearOldStockAlerts($product);
        }
    }

    /**
     * Check stock level and create notification if needed
     */
    private function checkStockLevel(Product $product)
    {
        if ($product->stock_quantity <= 0) {
            $this->createOutOfStockAlert($product);
        } elseif ($product->stock_quantity <= $product->stock_threshold) {
            $this->createStockAlert($product);
        }
    }

    /**
     * Check expiry date and create notification if needed
     */
    private function checkExpiryDate(Product $product)
    {
        if (!$product->expiry_date) {
            return;
        }

        $now = now();
        $expiryDate = $product->expiry_date;

        // If product is expired
        if ($expiryDate->isPast() && $product->stock_quantity > 0) {
            $this->createExpiredAlert($product);
        } 
        // If product expires within 30 days
        elseif ($expiryDate->diffInDays($now) <= 30 && $expiryDate->isFuture()) {
            $daysUntilExpiry = $now->diffInDays($expiryDate);
            $this->createExpiryAlert($product, $daysUntilExpiry);
        }
    }

    /**
     * Create stock alert notification
     */
    private function createStockAlert(Product $product)
    {
        // Check if notification already exists for this product (within last 6 hours)
        $existingNotification = Notification::where('type', 'stock_alert')
            ->where('data->product_id', $product->id)
            ->where('data->alert_type', 'low_stock')
            ->where('created_at', '>=', now()->subHours(6))
            ->first();

        if (!$existingNotification) {
            $users = \App\Models\User::where('role', 'responsable')->get();
            
            foreach ($users as $user) {
                Notification::createNotification(
                    $user->id,
                    'stock_alert',
                    'Alerte Stock Critique',
                    "Le produit {$product->name} a un stock critique ({$product->stock_quantity} unités restantes, seuil: {$product->stock_threshold})",
                    [
                        'product_id' => $product->id,
                        'current_stock' => $product->stock_quantity,
                        'threshold' => $product->stock_threshold,
                        'alert_type' => 'low_stock'
                    ],
                    'high',
                    route('inventory.show', $product->id)
                );
            }

            Log::info('Created stock alert notification', [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'stock' => $product->stock_quantity,
                'threshold' => $product->stock_threshold
            ]);
        }
    }

    /**
     * Create out of stock alert notification
     */
    private function createOutOfStockAlert(Product $product)
    {
        // Check if notification already exists for this product (within last 6 hours)
        $existingNotification = Notification::where('type', 'stock_alert')
            ->where('data->product_id', $product->id)
            ->where('data->alert_type', 'out_of_stock')
            ->where('created_at', '>=', now()->subHours(6))
            ->first();

        if (!$existingNotification) {
            $users = \App\Models\User::where('role', 'responsable')->get();
            
            foreach ($users as $user) {
                Notification::createNotification(
                    $user->id,
                    'stock_alert',
                    'Produit en Rupture de Stock',
                    "Le produit {$product->name} est en rupture de stock (0 unité disponible)",
                    [
                        'product_id' => $product->id,
                        'current_stock' => $product->stock_quantity,
                        'alert_type' => 'out_of_stock'
                    ],
                    'high',
                    route('inventory.show', $product->id)
                );
            }

            Log::info('Created out of stock alert notification', [
                'product_id' => $product->id,
                'product_name' => $product->name
            ]);
        }
    }

    /**
     * Create expiry alert notification
     */
    private function createExpiryAlert(Product $product, $daysUntilExpiry)
    {
        // Check if notification already exists for this product (within last 24 hours)
        $existingNotification = Notification::where('type', 'expiry_alert')
            ->where('data->product_id', $product->id)
            ->where('data->alert_type', 'expiring_soon')
            ->where('created_at', '>=', now()->subDay())
            ->first();

        if (!$existingNotification) {
            $users = \App\Models\User::all();
            $priority = $daysUntilExpiry <= 7 ? 'high' : ($daysUntilExpiry <= 15 ? 'medium' : 'normal');
            
            foreach ($users as $user) {
                Notification::createNotification(
                    $user->id,
                    'expiry_alert',
                    'Produit Bientôt Expiré',
                    "Le produit {$product->name} expire dans {$daysUntilExpiry} jour(s) (Date d'expiration: {$product->expiry_date->format('d/m/Y')})",
                    [
                        'product_id' => $product->id,
                        'expiry_date' => $product->expiry_date->toDateString(),
                        'days_until_expiry' => $daysUntilExpiry,
                        'alert_type' => 'expiring_soon'
                    ],
                    $priority,
                    route('inventory.show', $product->id)
                );
            }

            Log::info('Created expiry alert notification', [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'days_until_expiry' => $daysUntilExpiry
            ]);
        }
    }

    /**
     * Create expired product alert notification
     */
    private function createExpiredAlert(Product $product)
    {
        // Check if notification already exists for this product (within last week)
        $existingNotification = Notification::where('type', 'expiry_alert')
            ->where('data->product_id', $product->id)
            ->where('data->alert_type', 'expired')
            ->where('created_at', '>=', now()->subWeek())
            ->first();

        if (!$existingNotification) {
            $users = \App\Models\User::all();
            $daysExpired = $product->expiry_date->diffInDays(now());
            
            foreach ($users as $user) {
                Notification::createNotification(
                    $user->id,
                    'expiry_alert',
                    'Produit Expiré',
                    "Le produit {$product->name} a expiré il y a {$daysExpired} jour(s) (Date d'expiration: {$product->expiry_date->format('d/m/Y')}) et il reste {$product->stock_quantity} unité(s) en stock",
                    [
                        'product_id' => $product->id,
                        'expiry_date' => $product->expiry_date->toDateString(),
                        'days_expired' => $daysExpired,
                        'current_stock' => $product->stock_quantity,
                        'alert_type' => 'expired'
                    ],
                    'high',
                    route('inventory.show', $product->id)
                );
            }

            Log::info('Created expired product alert notification', [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'days_expired' => $daysExpired
            ]);
        }
    }

    /**
     * Clear old stock alerts when stock is restored
     */
    private function clearOldStockAlerts(Product $product)
    {
        // Mark old stock alerts as read when stock is restored above threshold
        Notification::where('type', 'stock_alert')
            ->where('data->product_id', $product->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        Log::info('Cleared old stock alerts for product', [
            'product_id' => $product->id,
            'product_name' => $product->name
        ]);
    }
}