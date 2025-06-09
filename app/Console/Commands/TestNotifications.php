<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\User;
use App\Models\Notification;
use App\Services\NotificationService;

class TestNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:test {--type=all : Type of notification to test (stock|expiry|all)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the notification system by creating sample scenarios';

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
        $this->info('🧪 Test du système de notifications...');
        $this->newLine();

        $type = $this->option('type');

        if ($type === 'all' || $type === 'stock') {
            $this->testStockNotifications();
        }

        if ($type === 'all' || $type === 'expiry') {
            $this->testExpiryNotifications();
        }

        if ($type === 'all') {
            $this->testSystemNotifications();
        }

        $this->newLine();
        $this->info('✅ Test du système de notifications terminé!');
        
        $this->displayNotificationSummary();

        return 0;
    }

    /**
     * Test stock notifications
     */
    private function testStockNotifications()
    {
        $this->info('📦 Test des notifications de stock...');

        // Find products to test with
        $lowStockProducts = Product::whereColumn('stock_quantity', '<=', 'stock_threshold')->take(3)->get();
        $outOfStockProducts = Product::where('stock_quantity', '<=', 0)->take(2)->get();

        if ($lowStockProducts->count() > 0) {
            $this->line('   ✅ Produits avec stock faible trouvés: ' . $lowStockProducts->count());
            foreach ($lowStockProducts as $product) {
                $this->line("     - {$product->name}: {$product->stock_quantity}/{$product->stock_threshold}");
            }
        } else {
            $this->warn('   ⚠️  Aucun produit avec stock faible trouvé');
            
            // Create a test scenario
            $product = Product::first();
            if ($product) {
                $originalStock = $product->stock_quantity;
                $originalThreshold = $product->stock_threshold;
                
                // Temporarily set low stock
                $product->stock_quantity = 1;
                $product->stock_threshold = 5;
                $product->save();
                
                $this->line("   📝 Scénario de test créé pour: {$product->name}");
                
                // Test notification
                $this->notificationService->checkLowStock();
                
                // Restore original values
                $product->stock_quantity = $originalStock;
                $product->stock_threshold = $originalThreshold;
                $product->save();
                
                $this->line("   🔄 Valeurs originales restaurées");
            }
        }

        if ($outOfStockProducts->count() > 0) {
            $this->line('   ✅ Produits en rupture trouvés: ' . $outOfStockProducts->count());
            foreach ($outOfStockProducts as $product) {
                $this->line("     - {$product->name}: {$product->stock_quantity} unités");
            }
        } else {
            $this->warn('   ⚠️  Aucun produit en rupture trouvé');
        }

        // Test the notification service methods
        $this->notificationService->checkLowStock();
        $this->notificationService->checkOutOfStock();
        
        $this->line('   ✅ Vérifications de stock exécutées');
    }

    /**
     * Test expiry notifications
     */
    private function testExpiryNotifications()
    {
        $this->info('⏰ Test des notifications d\'expiration...');

        // Find products expiring soon
        $expiringSoon = Product::where('expiry_date', '<=', now()->addDays(30))
                              ->where('expiry_date', '>', now())
                              ->take(3)
                              ->get();

        // Find expired products
        $expired = Product::where('expiry_date', '<', now())
                         ->where('stock_quantity', '>', 0)
                         ->take(2)
                         ->get();

        if ($expiringSoon->count() > 0) {
            $this->line('   ✅ Produits expirant bientôt trouvés: ' . $expiringSoon->count());
            foreach ($expiringSoon as $product) {
                $days = now()->diffInDays($product->expiry_date);
                $this->line("     - {$product->name}: expire dans {$days} jours");
            }
        } else {
            $this->warn('   ⚠️  Aucun produit expirant bientôt trouvé');
            
            // Create a test scenario
            $product = Product::whereNotNull('expiry_date')->first();
            if (!$product) {
                $product = Product::first();
            }
            
            if ($product) {
                $originalExpiry = $product->expiry_date;
                
                // Set expiry to 10 days from now
                $product->expiry_date = now()->addDays(10);
                $product->save();
                
                $this->line("   📝 Scénario de test créé pour: {$product->name} (expire dans 10 jours)");
                
                // Test notification
                $this->notificationService->checkExpiringProducts(30);
                
                // Restore original value
                $product->expiry_date = $originalExpiry;
                $product->save();
                
                $this->line("   🔄 Date d'expiration originale restaurée");
            }
        }

        if ($expired->count() > 0) {
            $this->line('   ✅ Produits expirés trouvés: ' . $expired->count());
            foreach ($expired as $product) {
                $days = $product->expiry_date->diffInDays(now());
                $this->line("     - {$product->name}: expiré depuis {$days} jours");
            }
        } else {
            $this->warn('   ⚠️  Aucun produit expiré avec stock trouvé');
        }

        // Test the notification service methods
        $this->notificationService->checkExpiringProducts(30);
        $this->notificationService->checkExpiringProducts(7);
        $this->notificationService->checkExpiredProducts();
        
        $this->line('   ✅ Vérifications d\'expiration exécutées');
    }

    /**
     * Test system notifications
     */
    private function testSystemNotifications()
    {
        $this->info('🔔 Test des notifications système...');

        // Create a test system notification
        $this->notificationService->sendSystemAlert(
            'Test Système',
            'Ceci est une notification de test du système. Elle sera supprimée automatiquement.',
            'normal'
        );

        $this->line('   ✅ Notification système de test créée');

        // Test notification for admins only
        $admins = User::where('role', 'responsable')->pluck('id')->toArray();
        if (!empty($admins)) {
            $this->notificationService->sendSystemAlert(
                'Test Admin',
                'Notification de test réservée aux administrateurs.',
                'low',
                $admins
            );
            $this->line('   ✅ Notification admin de test créée');
        }

        // Create a custom notification
        $user = User::first();
        if ($user) {
            $this->notificationService->createCustomNotification(
                $user->id,
                'system_alert',
                'Test Personnalisé',
                'Notification personnalisée créée pour le test du système.',
                ['test' => true, 'created_by_test' => true],
                'normal',
                null,
                now()->addHour() // Expire in 1 hour
            );
            $this->line("   ✅ Notification personnalisée créée pour {$user->name}");
        }
    }

    /**
     * Display notification summary
     */
    private function displayNotificationSummary()
    {
        $this->info('📊 Résumé des notifications créées lors du test:');
        
        $testNotifications = Notification::where('created_at', '>=', now()->subMinutes(5))
            ->selectRaw('type, priority, COUNT(*) as count')
            ->groupBy('type', 'priority')
            ->get();

        if ($testNotifications->count() > 0) {
            $this->table(
                ['Type', 'Priorité', 'Nombre'],
                $testNotifications->map(function ($notification) {
                    return [
                        $this->getTypeLabel($notification->type),
                        $this->getPriorityLabel($notification->priority),
                        $notification->count
                    ];
                })->toArray()
            );

            $totalNotifications = $testNotifications->sum('count');
            $this->info("Total: {$totalNotifications} notification(s) créée(s)");
        } else {
            $this->warn('Aucune nouvelle notification créée pendant le test');
        }

        // Show current system status
        $this->newLine();
        $this->info('📈 État actuel du système:');
        
        $systemStatus = [
            ['Produits stock faible', Product::whereColumn('stock_quantity', '<=', 'stock_threshold')->count()],
            ['Produits rupture', Product::where('stock_quantity', '<=', 0)->count()],
            ['Produits expirant (30j)', Product::where('expiry_date', '<=', now()->addDays(30))->where('expiry_date', '>', now())->count()],
            ['Produits expirés', Product::where('expiry_date', '<', now())->where('stock_quantity', '>', 0)->count()],
            ['Notifications non lues', Notification::whereNull('read_at')->count()],
            ['Total utilisateurs', User::count()],
        ];

        $this->table(['Métrique', 'Valeur'], $systemStatus);
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
            'prescription_ready' => 'Ordonnance',
            'purchase_received' => 'Livraison',
            'system_alert' => 'Système',
            default => ucfirst($type)
        };
    }

    /**
     * Get priority label in French
     */
    private function getPriorityLabel($priority)
    {
        return match($priority) {
            'high' => 'Élevée',
            'medium' => 'Moyenne',
            'low' => 'Faible',
            default => 'Normale'
        };
    }
}