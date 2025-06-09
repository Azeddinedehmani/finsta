<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Prescription;
use App\Models\Purchase;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Check for low stock products and create notifications
     */
    public function checkLowStock()
    {
        try {
            $lowStockProducts = Product::whereColumn('stock_quantity', '<=', 'stock_threshold')->get();
            
            Log::info('Checking low stock products', ['count' => $lowStockProducts->count()]);
            
            foreach ($lowStockProducts as $product) {
                // Check if notification already exists for this product (within last 24 hours)
                $existingNotification = Notification::where('type', 'stock_alert')
                    ->where('data->product_id', $product->id)
                    ->where('created_at', '>=', now()->subDay())
                    ->first();
                    
                if (!$existingNotification) {
                    $this->createStockAlert($product);
                    Log::info('Created stock alert notification', ['product_id' => $product->id, 'product_name' => $product->name]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error checking low stock: ' . $e->getMessage());
        }
    }

    /**
     * Check for out of stock products and create notifications
     */
    public function checkOutOfStock()
    {
        try {
            $outOfStockProducts = Product::where('stock_quantity', '<=', 0)->get();
            
            Log::info('Checking out of stock products', ['count' => $outOfStockProducts->count()]);
            
            foreach ($outOfStockProducts as $product) {
                // Check if notification already exists for this product (within last 24 hours)
                $existingNotification = Notification::where('type', 'stock_alert')
                    ->where('data->product_id', $product->id)
                    ->where('data->alert_type', 'out_of_stock')
                    ->where('created_at', '>=', now()->subDay())
                    ->first();
                    
                if (!$existingNotification) {
                    $this->createOutOfStockAlert($product);
                    Log::info('Created out of stock alert notification', ['product_id' => $product->id, 'product_name' => $product->name]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error checking out of stock: ' . $e->getMessage());
        }
    }

    /**
     * Check for expiring products and create notifications
     */
    public function checkExpiringProducts($daysAhead = 30)
    {
        try {
            $expiringProducts = Product::where('expiry_date', '<=', now()->addDays($daysAhead))
                ->where('expiry_date', '>', now())
                ->get();
                
            Log::info('Checking expiring products', ['count' => $expiringProducts->count(), 'days_ahead' => $daysAhead]);
            
            foreach ($expiringProducts as $product) {
                $daysUntilExpiry = now()->diffInDays($product->expiry_date);
                
                // Check if notification already exists for this product (within last week)
                $existingNotification = Notification::where('type', 'expiry_alert')
                    ->where('data->product_id', $product->id)
                    ->where('created_at', '>=', now()->subWeek())
                    ->first();
                    
                if (!$existingNotification) {
                    $this->createExpiryAlert($product, $daysUntilExpiry);
                    Log::info('Created expiry alert notification', [
                        'product_id' => $product->id, 
                        'product_name' => $product->name,
                        'days_until_expiry' => $daysUntilExpiry
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error checking expiring products: ' . $e->getMessage());
        }
    }

    /**
     * Check for expired products and create notifications
     */
    public function checkExpiredProducts()
    {
        try {
            $expiredProducts = Product::where('expiry_date', '<', now())
                ->where('stock_quantity', '>', 0) // Only notify if we still have stock of expired products
                ->get();
                
            Log::info('Checking expired products', ['count' => $expiredProducts->count()]);
            
            foreach ($expiredProducts as $product) {
                // Check if notification already exists for this product (within last week)
                $existingNotification = Notification::where('type', 'expiry_alert')
                    ->where('data->product_id', $product->id)
                    ->where('data->alert_type', 'expired')
                    ->where('created_at', '>=', now()->subWeek())
                    ->first();
                    
                if (!$existingNotification) {
                    $this->createExpiredAlert($product);
                    Log::info('Created expired product alert notification', ['product_id' => $product->id, 'product_name' => $product->name]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error checking expired products: ' . $e->getMessage());
        }
    }

    /**
     * Create stock alert notification
     */
    private function createStockAlert($product)
    {
        $users = User::where('role', 'responsable')->get(); // Only notify admins
        
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
    }

    /**
     * Create out of stock alert notification
     */
    private function createOutOfStockAlert($product)
    {
        $users = User::where('role', 'responsable')->get(); // Only notify admins
        
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
    }

    /**
     * Create expiry alert notification
     */
    private function createExpiryAlert($product, $daysUntilExpiry)
    {
        $users = User::all(); // Notify all users
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
    }

    /**
     * Create expired product alert notification
     */
    private function createExpiredAlert($product)
    {
        $users = User::all(); // Notify all users
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
    }

    /**
     * Send notification when a sale is created
     */
    public function notifySaleCreated(Sale $sale)
    {
        // Only notify for sales above a certain amount
        if ($sale->total_amount >= 50) {
            $admins = User::where('role', 'responsable')->get();
            
            foreach ($admins as $admin) {
                Notification::createNotification(
                    $admin->id,
                    'sale_created',
                    'Nouvelle Vente Importante',
                    "Vente #{$sale->sale_number} de {$sale->total_amount}€ effectuée par {$sale->user->name}",
                    [
                        'sale_id' => $sale->id,
                        'amount' => $sale->total_amount,
                        'seller' => $sale->user->name
                    ],
                    'low',
                    route('sales.show', $sale->id)
                );
            }
        }
    }

    /**
     * Send notification when a prescription is ready
     */
    public function notifyPrescriptionReady(Prescription $prescription)
    {
        $users = User::all();
        
        foreach ($users as $user) {
            Notification::createNotification(
                $user->id,
                'prescription_ready',
                'Ordonnance Prête',
                "L'ordonnance #{$prescription->prescription_number} pour {$prescription->client->full_name} est prête à être délivrée",
                ['prescription_id' => $prescription->id],
                'medium',
                route('prescriptions.show', $prescription->id)
            );
        }
    }

    /**
     * Send notification when a purchase is received
     */
    public function notifyPurchaseReceived(Purchase $purchase)
    {
        $admins = User::where('role', 'responsable')->get();
        
        foreach ($admins as $admin) {
            Notification::createNotification(
                $admin->id,
                'purchase_received',
                'Livraison Reçue',
                "Commande #{$purchase->purchase_number} de {$purchase->supplier->name} reçue avec succès",
                ['purchase_id' => $purchase->id],
                'low',
                route('purchases.show', $purchase->id)
            );
        }
    }

    /**
     * Check stock after sale and create notifications if needed
     */
    public function checkStockAfterSale(Sale $sale)
    {
        foreach ($sale->saleItems as $item) {
            $product = $item->product->fresh(); // Get fresh data
            
            if ($product->stock_quantity <= 0) {
                $this->createOutOfStockAlert($product);
            } elseif ($product->stock_quantity <= $product->stock_threshold) {
                $this->createStockAlert($product);
            }
        }
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
                ['created_by_system' => true],
                $priority
            );
        }
    }

    /**
     * Clean up old notifications
     */
    public function cleanupOldNotifications($days = 30)
    {
        return Notification::where('read_at', '<', now()->subDays($days))->delete();
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
     * Run all notification checks
     */
    public function runAllChecks()
    {
        Log::info('Running all notification checks');
        
        try {
            $this->checkLowStock();
            $this->checkOutOfStock();
            $this->checkExpiringProducts(30); // Check 30 days ahead
            $this->checkExpiringProducts(7);  // Check 7 days ahead with higher priority
            $this->checkExpiredProducts();
            
            Log::info('All notification checks completed successfully');
        } catch (\Exception $e) {
            Log::error('Error running notification checks: ' . $e->getMessage());
        }
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
}