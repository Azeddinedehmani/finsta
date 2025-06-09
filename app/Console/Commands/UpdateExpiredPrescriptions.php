<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Prescription;

class UpdateExpiredPrescriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prescriptions:update-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update expired prescriptions status';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating expired prescriptions...');
        
        $expiredCount = Prescription::where('expiry_date', '<', now())
            ->whereNotIn('status', ['expired'])
            ->update(['status' => 'expired']);
            
        $this->info("Updated {$expiredCount} expired prescriptions.");
        
        // Also update status for all prescriptions to ensure consistency
        $prescriptions = Prescription::with('prescriptionItems')->get();
        $updatedCount = 0;
        
        foreach ($prescriptions as $prescription) {
            $oldStatus = $prescription->status;
            $prescription->updateStatus();
            if ($oldStatus !== $prescription->status) {
                $updatedCount++;
            }
        }
        
        $this->info("Updated status for {$updatedCount} prescriptions based on delivery progress.");
        
        return 0;
    }
}