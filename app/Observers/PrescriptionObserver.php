<?php

namespace App\Observers;

use App\Models\Prescription;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;

class PrescriptionObserver
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the Prescription "created" event.
     */
    public function created(Prescription $prescription)
    {
        try {
            Log::info('Prescription created, sending notifications', [
                'prescription_id' => $prescription->id,
                'prescription_number' => $prescription->prescription_number
            ]);

            $this->notifyNewPrescription($prescription);
        } catch (\Exception $e) {
            Log::error('Error in PrescriptionObserver created: ' . $e->getMessage());
        }
    }

    /**
     * Handle the Prescription "updated" event.
     */
    public function updated(Prescription $prescription)
    {
        try {
            // If status changed to completed, notify
            if ($prescription->wasChanged('status') && $prescription->status === 'completed') {
                $this->notifyPrescriptionCompleted($prescription);
            }

            // If status changed to partially_delivered, notify
            if ($prescription->wasChanged('status') && $prescription->status === 'partially_delivered') {
                $this->notifyPrescriptionPartiallyDelivered($prescription);
            }

            // If prescription is about to expire (within 7 days), notify
            if ($prescription->expiry_date && $prescription->expiry_date->diffInDays(now()) <= 7 && $prescription->status === 'pending') {
                $this->notifyPrescriptionExpiringSoon($prescription);
            }
        } catch (\Exception $e) {
            Log::error('Error in PrescriptionObserver updated: ' . $e->getMessage());
        }
    }

    /**
     * Create notification for new prescription
     */
    private function notifyNewPrescription(Prescription $prescription)
    {
        // Check if notification already exists for this prescription
        $existingNotification = Notification::where('type', 'prescription_ready')
            ->where('data->prescription_id', $prescription->id)
            ->where('data->action', 'created')
            ->first();

        if (!$existingNotification) {
            $users = \App\Models\User::where('role', 'pharmacien')->get();
            
            foreach ($users as $user) {
                Notification::createNotification(
                    $user->id,
                    'prescription_ready',
                    'Nouvelle Ordonnance',
                    "Nouvelle ordonnance #{$prescription->prescription_number} pour {$prescription->client->full_name} - Médecin: {$prescription->doctor_name}",
                    [
                        'prescription_id' => $prescription->id,
                        'action' => 'created',
                        'client_name' => $prescription->client->full_name,
                        'doctor_name' => $prescription->doctor_name
                    ],
                    'medium',
                    route('prescriptions.show', $prescription->id)
                );
            }

            // Also notify admins
            $admins = \App\Models\User::where('role', 'responsable')->get();
            foreach ($admins as $admin) {
                Notification::createNotification(
                    $admin->id,
                    'prescription_ready',
                    'Nouvelle Ordonnance',
                    "Nouvelle ordonnance #{$prescription->prescription_number} créée pour {$prescription->client->full_name}",
                    [
                        'prescription_id' => $prescription->id,
                        'action' => 'created',
                        'client_name' => $prescription->client->full_name,
                        'doctor_name' => $prescription->doctor_name
                    ],
                    'low',
                    route('prescriptions.show', $prescription->id)
                );
            }

            Log::info('Created new prescription notifications', [
                'prescription_id' => $prescription->id,
                'prescription_number' => $prescription->prescription_number
            ]);
        }
    }

    /**
     * Create notification for completed prescription
     */
    private function notifyPrescriptionCompleted(Prescription $prescription)
    {
        // Check if notification already exists
        $existingNotification = Notification::where('type', 'prescription_ready')
            ->where('data->prescription_id', $prescription->id)
            ->where('data->action', 'completed')
            ->first();

        if (!$existingNotification) {
            $admins = \App\Models\User::where('role', 'responsable')->get();
            
            foreach ($admins as $admin) {
                Notification::createNotification(
                    $admin->id,
                    'prescription_ready',
                    'Ordonnance Complétée',
                    "L'ordonnance #{$prescription->prescription_number} pour {$prescription->client->full_name} a été entièrement délivrée",
                    [
                        'prescription_id' => $prescription->id,
                        'action' => 'completed',
                        'client_name' => $prescription->client->full_name,
                        'delivered_by' => $prescription->deliveredBy ? $prescription->deliveredBy->name : 'Inconnu'
                    ],
                    'low',
                    route('prescriptions.show', $prescription->id)
                );
            }

            Log::info('Created prescription completed notification', [
                'prescription_id' => $prescription->id,
                'prescription_number' => $prescription->prescription_number
            ]);
        }
    }

    /**
     * Create notification for partially delivered prescription
     */
    private function notifyPrescriptionPartiallyDelivered(Prescription $prescription)
    {
        // Only notify pharmacists about partial delivery
        $users = \App\Models\User::where('role', 'pharmacien')->get();
        
        foreach ($users as $user) {
            Notification::createNotification(
                $user->id,
                'prescription_ready',
                'Ordonnance Partiellement Délivrée',
                "L'ordonnance #{$prescription->prescription_number} pour {$prescription->client->full_name} a été partiellement délivrée ({$prescription->delivery_progress}%)",
                [
                    'prescription_id' => $prescription->id,
                    'action' => 'partially_delivered',
                    'client_name' => $prescription->client->full_name,
                    'progress' => $prescription->delivery_progress
                ],
                'medium',
                route('prescriptions.show', $prescription->id)
            );
        }

        Log::info('Created prescription partially delivered notification', [
            'prescription_id' => $prescription->id,
            'prescription_number' => $prescription->prescription_number,
            'progress' => $prescription->delivery_progress
        ]);
    }

    /**
     * Create notification for prescription expiring soon
     */
    private function notifyPrescriptionExpiringSoon(Prescription $prescription)
    {
        // Check if notification already exists
        $existingNotification = Notification::where('type', 'expiry_alert')
            ->where('data->prescription_id', $prescription->id)
            ->where('data->alert_type', 'prescription_expiring')
            ->where('created_at', '>=', now()->subDays(3))
            ->first();

        if (!$existingNotification) {
            $users = \App\Models\User::all();
            $daysUntilExpiry = now()->diffInDays($prescription->expiry_date);
            
            foreach ($users as $user) {
                Notification::createNotification(
                    $user->id,
                    'expiry_alert',
                    'Ordonnance Bientôt Expirée',
                    "L'ordonnance #{$prescription->prescription_number} pour {$prescription->client->full_name} expire dans {$daysUntilExpiry} jour(s)",
                    [
                        'prescription_id' => $prescription->id,
                        'alert_type' => 'prescription_expiring',
                        'client_name' => $prescription->client->full_name,
                        'days_until_expiry' => $daysUntilExpiry,
                        'expiry_date' => $prescription->expiry_date->toDateString()
                    ],
                    'high',
                    route('prescriptions.show', $prescription->id)
                );
            }

            Log::info('Created prescription expiring soon notification', [
                'prescription_id' => $prescription->id,
                'prescription_number' => $prescription->prescription_number,
                'days_until_expiry' => $daysUntilExpiry
            ]);
        }
    }
}