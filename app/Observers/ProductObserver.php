<?php
// File: app/Observers/ProductObserver.php
// Create this new file for automatic product notifications

namespace App\Observers;

use App\Models\Product;
use App\Models\Notification;
use App\Services\NotificationService;

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
        // Check if stock quantity was changed
        if ($product->wasChanged('stock_quantity')) {
            $this->checkStockLevel($product);
        }

        // Check if expiry date was changed
        if ($product->wasChanged('expiry_date')) {
            $this->checkExpiryDate($product);
        }
    }

    /**
     * Handle the Product "created" event.
     */
    public function created(Product $product)
    {
        // Check stock level for new products
        $this->checkStockLevel($product);
        $this->checkExpiryDate($product);
    }

    /**
     * Check stock level and create notification if needed
     */
    private function checkStockLevel(Product $product)
    {
        if ($product->isLowStock()) {
            // Check if notification already exists for this product (within last hour)
            $existingNotification = Notification::where('type', 'stock_alert')
                ->where('data->product_id', $product->id)
                ->where('created_at', '>=', now()->subHour())
                ->first();

            if (!$existingNotification) {
                Notification::createStockAlert($product);
            }
        }
    }

    /**
     * Check expiry date and create notification if needed
     */
    private function checkExpiryDate(Product $product)
    {
        if ($product->expiry_date && $product->isAboutToExpire(30)) {
            $daysUntilExpiry = now()->diffInDays($product->expiry_date);
            
            // Check if notification already exists for this product (within last day)
            $existingNotification = Notification::where('type', 'expiry_alert')
                ->where('data->product_id', $product->id)
                ->where('created_at', '>=', now()->subDay())
                ->first();

            if (!$existingNotification) {
                Notification::createExpiryAlert($product, $daysUntilExpiry);
            }
        }
    }
}
