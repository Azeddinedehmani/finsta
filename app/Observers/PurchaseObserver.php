<?php

namespace App\Observers;

use App\Models\Purchase;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;

class PurchaseObserver
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the Purchase "created" event.
     */
    public function created(Purchase $purchase)
    {
        try {
            Log::info('Purchase created, sending notifications', [
                'purchase_id' => $purchase->id,
                'purchase_number' => $purchase->purchase_number,
                'supplier' => $purchase->supplier->name
            ]);

            $this->notifyNewPurchaseOrder($purchase);
        } catch (\Exception $e) {
            Log::error('Error in PurchaseObserver created: ' . $e->getMessage());
        }
    }

    /**
     * Handle the Purchase "updated" event.
     */
    public function updated(Purchase $purchase)
    {
        try {
            // If status changed to received, notify
            if ($purchase->wasChanged('status') && $purchase->status === 'received') {
                $this->notifyPurchaseReceived($purchase);
            }

            // If status changed to partially_received, notify
            if ($purchase->wasChanged('status') && $purchase->status === 'partially_received') {
                $this->notifyPurchasePartiallyReceived($purchase);
            }

            // If status changed to cancelled, notify
            if ($purchase->wasChanged('status') && $purchase->status === 'cancelled') {
                $this->notifyPurchaseCancelled($purchase);
            }

            // Check if purchase is overdue
            if ($purchase->status === 'pending' && $purchase->expected_date && $purchase->expected_date->isPast()) {
                $this->notifyPurchaseOverdue($purchase);
            }
        } catch (\Exception $e) {
            Log::error('Error in PurchaseObserver updated: ' . $e->getMessage());
        }
    }

    /**
     * Create notification for new purchase order
     */
    private function notifyNewPurchaseOrder(Purchase $purchase)
    {
        // Check if notification already exists for this purchase
        $existingNotification = Notification::where('type', 'purchase_received')
            ->where('data->purchase_id', $purchase->id)
            ->where('data->action', 'created')
            ->first();

        if (!$existingNotification) {
            $admins = \App\Models\User::where('role', 'responsable')->get();
            
            foreach ($admins as $admin) {
                Notification::createNotification(
                    $admin->id,
                    'purchase_received',
                    'Nouvelle Commande d\'Achat',
                    "Commande #{$purchase->purchase_number} créée pour {$purchase->supplier->name} - Montant: {$purchase->total_amount}€",
                    [
                        'purchase_id' => $purchase->id,
                        'action' => 'created',
                        'supplier_name' => $purchase->supplier->name,
                        'total_amount' => $purchase->total_amount,
                        'expected_date' => $purchase->expected_date ? $purchase->expected_date->toDateString() : null
                    ],
                    'low',
                    route('purchases.show', $purchase->id)
                );
            }

            Log::info('Created new purchase order notification', [
                'purchase_id' => $purchase->id,
                'purchase_number' => $purchase->purchase_number,
                'supplier' => $purchase->supplier->name
            ]);
        }
    }

    /**
     * Create notification for received purchase
     */
    private function notifyPurchaseReceived(Purchase $purchase)
    {
        // Check if notification already exists
        $existingNotification = Notification::where('type', 'purchase_received')
            ->where('data->purchase_id', $purchase->id)
            ->where('data->action', 'received')
            ->first();

        if (!$existingNotification) {
            $admins = \App\Models\User::where('role', 'responsable')->get();
            
            foreach ($admins as $admin) {
                Notification::createNotification(
                    $admin->id,
                    'purchase_received',
                    'Livraison Complète Reçue',
                    "Commande #{$purchase->purchase_number} de {$purchase->supplier->name} entièrement reçue",
                    [
                        'purchase_id' => $purchase->id,
                        'action' => 'received',
                        'supplier_name' => $purchase->supplier->name,
                        'total_amount' => $purchase->total_amount,
                        'received_by' => $purchase->receivedBy ? $purchase->receivedBy->name : 'Inconnu'
                    ],
                    'low',
                    route('purchases.show', $purchase->id)
                );
            }

            // Also notify all users about stock replenishment
            $allUsers = \App\Models\User::all();
            foreach ($allUsers as $user) {
                Notification::createNotification(
                    $user->id,
                    'system_alert',
                    'Stock Réapprovisionné',
                    "Réception de la commande #{$purchase->purchase_number} - Stock mis à jour",
                    [
                        'purchase_id' => $purchase->id,
                        'action' => 'stock_replenished',
                        'supplier_name' => $purchase->supplier->name
                    ],
                    'normal',
                    route('purchases.show', $purchase->id)
                );
            }

            Log::info('Created purchase received notification', [
                'purchase_id' => $purchase->id,
                'purchase_number' => $purchase->purchase_number,
                'supplier' => $purchase->supplier->name
            ]);
        }
    }

    /**
     * Create notification for partially received purchase
     */
    private function notifyPurchasePartiallyReceived(Purchase $purchase)
    {
        $admins = \App\Models\User::where('role', 'responsable')->get();
        
        foreach ($admins as $admin) {
            Notification::createNotification(
                $admin->id,
                'purchase_received',
                'Livraison Partielle Reçue',
                "Commande #{$purchase->purchase_number} de {$purchase->supplier->name} partiellement reçue ({$purchase->progress_percentage}%)",
                [
                    'purchase_id' => $purchase->id,
                    'action' => 'partially_received',
                    'supplier_name' => $purchase->supplier->name,
                    'progress' => $purchase->progress_percentage,
                    'received_items' => $purchase->received_items,
                    'total_items' => $purchase->total_items
                ],
                'medium',
                route('purchases.show', $purchase->id)
            );
        }

        Log::info('Created purchase partially received notification', [
            'purchase_id' => $purchase->id,
            'purchase_number' => $purchase->purchase_number,
            'progress' => $purchase->progress_percentage
        ]);
    }

    /**
     * Create notification for cancelled purchase
     */
    private function notifyPurchaseCancelled(Purchase $purchase)
    {
        $admins = \App\Models\User::where('role', 'responsable')->get();
        
        foreach ($admins as $admin) {
            Notification::createNotification(
                $admin->id,
                'system_alert',
                'Commande Annulée',
                "Commande #{$purchase->purchase_number} de {$purchase->supplier->name} a été annulée",
                [
                    'purchase_id' => $purchase->id,
                    'action' => 'cancelled',
                    'supplier_name' => $purchase->supplier->name,
                    'total_amount' => $purchase->total_amount
                ],
                'medium',
                route('purchases.show', $purchase->id)
            );
        }

        Log::info('Created purchase cancelled notification', [
            'purchase_id' => $purchase->id,
            'purchase_number' => $purchase->purchase_number
        ]);
    }

    /**
     * Create notification for overdue purchase
     */
    private function notifyPurchaseOverdue(Purchase $purchase)
    {
        // Check if notification already exists for this overdue purchase (within last 24 hours)
        $existingNotification = Notification::where('type', 'system_alert')
            ->where('data->purchase_id', $purchase->id)
            ->where('data->alert_type', 'overdue')
            ->where('created_at', '>=', now()->subDay())
            ->first();

        if (!$existingNotification) {
            $admins = \App\Models\User::where('role', 'responsable')->get();
            $daysOverdue = $purchase->expected_date->diffInDays(now());
            
            foreach ($admins as $admin) {
                Notification::createNotification(
                    $admin->id,
                    'system_alert',
                    'Commande en Retard',
                    "Commande #{$purchase->purchase_number} de {$purchase->supplier->name} est en retard de {$daysOverdue} jour(s)",
                    [
                        'purchase_id' => $purchase->id,
                        'alert_type' => 'overdue',
                        'supplier_name' => $purchase->supplier->name,
                        'days_overdue' => $daysOverdue,
                        'expected_date' => $purchase->expected_date->toDateString()
                    ],
                    'high',
                    route('purchases.show', $purchase->id)
                );
            }

            Log::info('Created purchase overdue notification', [
                'purchase_id' => $purchase->id,
                'purchase_number' => $purchase->purchase_number,
                'days_overdue' => $daysOverdue
            ]);
        }
    }
}