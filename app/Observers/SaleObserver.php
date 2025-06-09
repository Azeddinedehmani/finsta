/ File: app/Observers/SaleObserver.php
// Create this new file for automatic sale notifications

namespace App\Observers;

use App\Models\Sale;
use App\Models\Notification;

class SaleObserver
{
    /**
     * Handle the Sale "created" event.
     */
    public function created(Sale $sale)
    {
        // Create notification for significant sales (>= 50€)
        if ($sale->total_amount >= 50) {
            Notification::createSaleNotification($sale);
        }

        // Check stock levels for all products in this sale
        foreach ($sale->saleItems as $item) {
            if ($item->product->isLowStock()) {
                Notification::createStockAlert($item->product);
            }
        }
    }
}
