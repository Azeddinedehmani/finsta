
namespace App\Observers;

use App\Models\Prescription;
use App\Models\Notification;

class PrescriptionObserver
{
    /**
     * Handle the Prescription "updated" event.
     */
    public function updated(Prescription $prescription)
    {
        // If status changed to completed, notify
        if ($prescription->wasChanged('status') && $prescription->status === 'completed') {
            Notification::createPrescriptionNotification($prescription);
        }
    }

    /**
     * Handle the Prescription "created" event.
     */
    public function created(Prescription $prescription)
    {
        // Notify about new prescription
        $this->notifyNewPrescription($prescription);
    }

    private function notifyNewPrescription(Prescription $prescription)
    {
        // Create notification for new prescription
        $users = \App\Models\User::where('role', 'pharmacien')->get();
        
        foreach ($users as $user) {
            Notification::createNotification(
                $user->id,
                'prescription_ready',
                'Nouvelle ordonnance',
                "Nouvelle ordonnance #{$prescription->prescription_number} pour {$prescription->client->full_name}",
                ['prescription_id' => $prescription->id],
                'medium',
                route('prescriptions.show', $prescription->id)
            );
        }
    }
}