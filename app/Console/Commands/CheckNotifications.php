<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NotificationService;

class CheckNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for low stock and expiring products and send notifications';

    /**
     * The notification service instance.
     */
    protected $notificationService;

    /**
     * Create a new command instance.
     */
    public function __construct(NotificationService $notificationService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Vérification des notifications...');
        
        // Check for low stock products
        $this->info('📦 Vérification des stocks faibles...');
        $this->notificationService->checkLowStock();
        
        // Check for expiring products
        $this->info('⏰ Vérification des produits qui expirent...');
        $this->notificationService->checkExpiringProducts();
        
        // Clean up old notifications
        $this->info('🧹 Nettoyage des anciennes notifications...');
        $cleanedCount = $this->notificationService->cleanupOldNotifications();
        $this->info("   - {$cleanedCount} anciennes notifications supprimées");
        
        $this->info('✅ Vérification des notifications terminée!');
        
        return 0;
    }
}