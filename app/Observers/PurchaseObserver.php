// File: app/Observers/PurchaseObserver.php
// Create this new file for automatic purchase notifications

namespace App\Observers;

use App\Models\Purchase;
use App\Models\Notification;

class PurchaseObserver
{
    /**
     * Handle the Purchase "updated" event.
     */
    public function updated(Purchase $purchase)
    {
        // If status changed to received, notify
        if ($purchase->wasChanged('status') && $purchase->status === 'received') {
            Notification::createPurchaseReceivedNotification($purchase);
        }

        // If expected date passed and still pending, create overdue notification
        if ($purchase->status === 'pending' && $purchase->expected_date < now()) {
            $this->createOverdueNotification($purchase);
        }
    }

    private function createOverdueNotification(Purchase $purchase)
    {
        // Check if overdue notification already exists
        $existingNotification = Notification::where('type', 'system_alert')
            ->where('data->purchase_id', $purchase->id)
            ->where('data->alert_type', 'overdue')
            ->where('created_at', '>=', now()->subDay())
            ->first();

        if (!$existingNotification) {
            $admins = \App\Models\User::where('role', 'responsable')->get();
            
            foreach ($admins as $admin) {
                Notification::createNotification(
                    $admin->id,
                    'system_alert',
                    'Commande en retard',
                    "La commande #{$purchase->purchase_number} de {$purchase->supplier->name} est en retard",
                    ['purchase_id' => $purchase->id, 'alert_type' => 'overdue'],
                    'high',
                    route('purchases.show', $purchase->id)
                );
            }
        }
    }
}
