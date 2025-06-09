<?php

namespace App\Observers;

use App\Models\Sale;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;

class SaleObserver
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the Sale "created" event.
     */
    public function created(Sale $sale)
    {
        try {
            Log::info('Sale created, checking for notifications', [
                'sale_id' => $sale->id,
                'sale_number' => $sale->sale_number,
                'total_amount' => $sale->total_amount
            ]);

            // Create notification for significant sales (>= 50€)
            if ($sale->total_amount >= 50) {
                $this->createSaleNotification($sale);
            }

            // Check stock levels for all products in this sale
            $this->checkStockAfterSale($sale);

        } catch (\Exception $e) {
            Log::error('Error in SaleObserver created: ' . $e->getMessage());
        }
    }

    /**
     * Handle the Sale "updated" event.
     */
    public function updated(Sale $sale)
    {
        try {
            // If payment status changed to paid and amount is significant, notify
            if ($sale->wasChanged('payment_status') && $sale->payment_status === 'paid' && $sale->total_amount >= 50) {
                $this->createSaleNotification($sale);
            }
        } catch (\Exception $e) {
            Log::error('Error in SaleObserver updated: ' . $e->getMessage());
        }
    }

    /**
     * Create sale notification
     */
    private function createSaleNotification(Sale $sale)
    {
        // Check if notification already exists for this sale
        $existingNotification = Notification::where('type', 'sale_created')
            ->where('data->sale_id', $sale->id)
            ->first();

        if (!$existingNotification) {
            $admins = \App\Models\User::where('role', 'responsable')->get();
            $clientName = $sale->client ? $sale->client->full_name : 'Client anonyme';
            
            foreach ($admins as $admin) {
                Notification::createNotification(
                    $admin->id,
                    'sale_created',
                    'Nouvelle Vente Importante',
                    "Vente #{$sale->sale_number} de {$sale->total_amount}€ effectuée par {$sale->user->name} pour {$clientName}",
                    [
                        'sale_id' => $sale->id,
                        'amount' => $sale->total_amount,
                        'seller' => $sale->user->name,
                        'client' => $clientName
                    ],
                    'low',
                    route('sales.show', $sale->id)
                );
            }

            Log::info('Created sale notification', [
                'sale_id' => $sale->id,
                'amount' => $sale->total_amount,
                'seller' => $sale->user->name
            ]);
        }
    }

    /**
     * Check stock levels after sale and create notifications if needed
     */
    private function checkStockAfterSale(Sale $sale)
    {
        foreach ($sale->saleItems as $item) {
            $product = $item->product->fresh(); // Get fresh data from database
            
            Log::info('Checking stock after sale', [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'current_stock' => $product->stock_quantity,
                'threshold' => $product->stock_threshold,
                'quantity_sold' => $item->quantity
            ]);

            // Create out of stock alert
            if ($product->stock_quantity <= 0) {
                $this->createOutOfStockAlert($product);
            } 
            // Create low stock alert
            elseif ($product->stock_quantity <= $product->stock_threshold) {
                $this->createStockAlert($product);
            }
        }
    }

    /**
     * Create out of stock alert notification
     */
    private function createOutOfStockAlert($product)
    {
        // Check if notification already exists for this product (within last 2 hours)
        $existingNotification = Notification::where('type', 'stock_alert')
            ->where('data->product_id', $product->id)
            ->where('data->alert_type', 'out_of_stock')
            ->where('created_at', '>=', now()->subHours(2))
            ->first();

        if (!$existingNotification) {
            $users = \App\Models\User::where('role', 'responsable')->get();
            
            foreach ($users as $user) {
                Notification::createNotification(
                    $user->id,
                    'stock_alert',
                    'Rupture de Stock Après Vente',
                    "Le produit {$product->name} est maintenant en rupture de stock suite à une vente",
                    [
                        'product_id' => $product->id,
                        'current_stock' => $product->stock_quantity,
                        'alert_type' => 'out_of_stock',
                        'caused_by' => 'sale'
                    ],
                    'high',
                    route('inventory.show', $product->id)
                );
            }

            Log::info('Created out of stock alert after sale', [
                'product_id' => $product->id,
                'product_name' => $product->name
            ]);
        }
    }

    /**
     * Create stock alert notification
     */
    private function createStockAlert($product)
    {
        // Check if notification already exists for this product (within last 2 hours)
        $existingNotification = Notification::where('type', 'stock_alert')
            ->where('data->product_id', $product->id)
            ->where('data->alert_type', 'low_stock')
            ->where('created_at', '>=', now()->subHours(2))
            ->first();

        if (!$existingNotification) {
            $users = \App\Models\User::where('role', 'responsable')->get();
            
            foreach ($users as $user) {
                Notification::createNotification(
                    $user->id,
                    'stock_alert',
                    'Stock Critique Après Vente',
                    "Le produit {$product->name} a maintenant un stock critique ({$product->stock_quantity} unités restantes, seuil: {$product->stock_threshold}) suite à une vente",
                    [
                        'product_id' => $product->id,
                        'current_stock' => $product->stock_quantity,
                        'threshold' => $product->stock_threshold,
                        'alert_type' => 'low_stock',
                        'caused_by' => 'sale'
                    ],
                    'high',
                    route('inventory.show', $product->id)
                );
            }

            Log::info('Created stock alert after sale', [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'stock' => $product->stock_quantity,
                'threshold' => $product->stock_threshold
            ]);
        }
    }
}