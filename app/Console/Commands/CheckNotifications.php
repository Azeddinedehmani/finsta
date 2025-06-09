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
    protected $signature = 'notifications:check {--force : Force check even if recently run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for low stock, out of stock, expiring and expired products and send notifications';

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
        $this->newLine();
        
        $startTime = now();

        try {
            // Check for low stock products
            $this->info('📦 Vérification des stocks faibles...');
            $this->notificationService->checkLowStock();
            $this->line('   ✅ Vérification des stocks faibles terminée');

            // Check for out of stock products
            $this->info('🚫 Vérification des ruptures de stock...');
            $this->notificationService->checkOutOfStock();
            $this->line('   ✅ Vérification des ruptures de stock terminée');
            
            // Check for expiring products (30 days)
            $this->info('⏰ Vérification des produits qui expirent dans 30 jours...');
            $this->notificationService->checkExpiringProducts(30);
            $this->line('   ✅ Vérification 30 jours terminée');

            // Check for expiring products (7 days) - higher priority
            $this->info('⚠️  Vérification des produits qui expirent dans 7 jours...');
            $this->notificationService->checkExpiringProducts(7);
            $this->line('   ✅ Vérification 7 jours terminée');
            
            // Check for expired products
            $this->info('❌ Vérification des produits expirés...');
            $this->notificationService->checkExpiredProducts();
            $this->line('   ✅ Vérification des produits expirés terminée');
            
            // Clean up old notifications
            $this->info('🧹 Nettoyage des anciennes notifications...');
            $cleanedCount = $this->notificationService->cleanupOldNotifications();
            $this->line("   ✅ {$cleanedCount} anciennes notifications supprimées");
            
            $endTime = now();
            $duration = $endTime->diffInSeconds($startTime);
            
            $this->newLine();
            $this->info("✅ Vérification des notifications terminée en {$duration} secondes!");
            
            // Display summary if verbose
            if ($this->option('verbose')) {
                $this->displaySummary();
            }
            
        } catch (\Exception $e) {
            $this->error('❌ Erreur lors de la vérification des notifications: ' . $e->getMessage());
            $this->error('Trace: ' . $e->getTraceAsString());
            return 1;
        }
        
        return 0;
    }

    /**
     * Display summary of notifications
     */
    private function displaySummary()
    {
        $this->newLine();
        $this->info('📊 Résumé des notifications:');
        
        try {
            $notifications = \App\Models\Notification::where('created_at', '>=', now()->subDay())
                ->selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->get();

            if ($notifications->count() > 0) {
                $this->table(
                    ['Type', 'Nombre (dernières 24h)'],
                    $notifications->map(function ($notification) {
                        return [
                            $this->getTypeLabel($notification->type),
                            $notification->count
                        ];
                    })->toArray()
                );
            } else {
                $this->line('   Aucune notification créée dans les dernières 24 heures');
            }

            // Show current alerts summary
            $lowStock = \App\Models\Product::whereColumn('stock_quantity', '<=', 'stock_threshold')->count();
            $outOfStock = \App\Models\Product::where('stock_quantity', '<=', 0)->count();
            $expiring = \App\Models\Product::where('expiry_date', '<=', now()->addDays(30))
                ->where('expiry_date', '>', now())
                ->count();
            $expired = \App\Models\Product::where('expiry_date', '<', now())
                ->where('stock_quantity', '>', 0)
                ->count();

            $this->newLine();
            $this->info('🎯 État actuel des alertes:');
            $this->table(
                ['Type d\'alerte', 'Nombre de produits'],
                [
                    ['Stock faible', $lowStock],
                    ['Rupture de stock', $outOfStock],
                    ['Expire bientôt (30j)', $expiring],
                    ['Expiré', $expired],
                ]
            );

        } catch (\Exception $e) {
            $this->error('Erreur lors de l\'affichage du résumé: ' . $e->getMessage());
        }
    }

    /**
     * Get type label in French
     */
    private function getTypeLabel($type)
    {
        return match($type) {
            'stock_alert' => 'Alerte Stock',
            'expiry_alert' => 'Alerte Expiration',
            'sale_created' => 'Vente Créée',
            'prescription_ready' => 'Ordonnance Prête',
            'purchase_received' => 'Livraison Reçue',
            'system_alert' => 'Alerte Système',
            default => ucfirst($type)
        };
    }
}