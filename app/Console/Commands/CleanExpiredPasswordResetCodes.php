<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PasswordResetCode;

class CleanExpiredPasswordResetCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'password-reset:clean-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean expired password reset codes from the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Cleaning expired password reset codes...');
        
        $deletedCount = PasswordResetCode::where('expires_at', '<', now())
            ->orWhere('used', true)
            ->count();
            
        PasswordResetCode::cleanExpired();
        
        $this->info("Cleaned {$deletedCount} expired/used password reset codes.");
        
        return 0;
    }
}