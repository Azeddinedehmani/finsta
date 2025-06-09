<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NotificationService;

class SetupRealtimeNotifications extends Command
{
    protected $signature = 'notifications:setup-realtime {--test : Create test notifications}';
    protected $description = 'Setup real-time notification system';

    public function handle()
    {
        $this->info('🔧 Configuration du système de notifications en temps réel...');
        $this->newLine();

        try {
            // Vérifier que les observers sont bien enregistrés
            $this->info('1. Vérification des observers...');
            $this->checkObservers();
            $this->newLine();

            // Vérifier la table notifications
            $this->info('2. Vérification de la base de données...');
            $this->checkDatabase();
            $this->newLine();

            // Vérifier le service de notifications
            $this->info('3. Test du service de notifications...');
            $this->testNotificationService();
            $this->newLine();

            // Exécuter une vérification complète
            $this->info('4. Exécution d\'une vérification complète...');
            $this->runFullCheck();
            $this->newLine();

            // Créer des notifications de test si demandé
            if ($this->option('test')) {
                $this->info('5. Création de notifications de test...');
                $this->createTestNotifications();
                $this->newLine();
            }

            $this->info('🎉 Configuration du système de notifications terminée !');
            $this->newLine();
            $this->displayInstructions();

        } catch (\Exception $e) {
            $this->error('❌ Erreur lors de la configuration : ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    private function checkObservers()
    {
        $observers = [
            'App\Models\Product' => 'App\Observers\ProductObserver',
            'App\Models\Sale' => 'App\Observers\SaleObserver',
            'App\Models\Prescription' => 'App\Observers\PrescriptionObserver',
            'App\Models\Purchase' => 'App\Observers\PurchaseObserver',
        ];

        foreach ($observers as $model => $observer) {
            if (class_exists($model) && class_exists($observer)) {
                $this->line("   ✅ Observer {$observer} configuré pour {$model}");
            } else {
                $this->error("   ❌ Observer {$observer} manquant pour {$model}");
            }
        }
    }

    private function checkDatabase()
    {
        try {
            $tableExists = \Schema::hasTable('notifications');
            if ($tableExists) {
                $this->line('   ✅ Table notifications existe');
                
                $count = \App\Models\Notification::count();
                $this->line("   ✅ {$count} notification(s) en base");
            } else {
                $this->error('   ❌ Table notifications manquante');
                $this->line('   💡 Exécuter: php artisan migrate');
            }
        } catch (\Exception $e) {
            $this->error('   ❌ Erreur base de données: ' . $e->getMessage());
        }
    }

    private function testNotificationService()
    {
        try {
            $service = app(NotificationService::class);
            $this->line('   ✅ Service NotificationService instantié');
            
            // Test d'une méthode du service
            $count = $service->getUnreadCount(1);
            $this->line("   ✅ Test getUnreadCount réussi: {$count}");
            
        } catch (\Exception $e) {
            $this->error('   ❌ Erreur service: ' . $e->getMessage());
        }
    }

    private function runFullCheck()
    {
        try {
            $service = app(NotificationService::class);
            
            $this->line('   🔍 Vérification des stocks faibles...');
            $service->checkLowStock();
            
            $this->line('   🔍 Vérification des ruptures de stock...');
            $service->checkOutOfStock();
            
            $this->line('   🔍 Vérification des produits expirant...');
            $service->checkExpiringProducts(30);
            
            $this->line('   🔍 Vérification des produits expirés...');
            $service->checkExpiredProducts();
            
            $this->line('   ✅ Vérification complète terminée');
            
        } catch (\Exception $e) {
            $this->error('   ❌ Erreur lors de la vérification: ' . $e->getMessage());
        }
    }

    private function createTestNotifications()
    {
        try {
            $user = \App\Models\User::first();
            if (!$user) {
                $this->error('   ❌ Aucun utilisateur trouvé');
                return;
            }

            \App\Models\Notification::createNotification(
                $user->id,
                'system_alert',
                'Système de Notifications Activé',
                'Votre système de notifications en temps réel est maintenant actif et fonctionnel!',
                ['test' => true, 'setup_time' => now()],
                'normal'
            );
            
            $this->line('   ✅ Notification de test créée');
            
        } catch (\Exception $e) {
            $this->error('   ❌ Erreur création test: ' . $e->getMessage());
        }
    }

    private function displayInstructions()
    {
        $this->info('📋 Instructions importantes:');
        $this->line('');
        $this->line('1. Les notifications seront créées automatiquement quand:');
        $this->line('   - Le stock d\'un produit devient faible ou nul');
        $this->line('   - Un produit arrive à expiration');
        $this->line('   - Une vente importante est effectuée');
        $this->line('   - Une ordonnance est créée ou délivrée');
        $this->line('   - Une commande est reçue ou en retard');
        $this->line('');
        $this->line('2. Pour vérifier manuellement les notifications:');
        $this->line('   php artisan notifications:check');
        $this->line('');
        $this->line('3. Pour diagnostiquer le système:');
        $this->line('   php artisan notifications:diagnose');
        $this->line('');
        $this->line('4. Pour créer des notifications de test:');
        $this->line('   php artisan notifications:create-test');
        $this->line('');
        $this->line('5. Le scheduler automatique vérifie les notifications:');
        $this->line('   - Toutes les 30 minutes (7h-20h)');
        $this->line('   - Toutes les 2 heures (20h-7h)');
        $this->line('   - Vérification complète à 8h chaque jour');
        $this->line('');
        $this->info('🎯 Le système est maintenant opérationnel!');
    }
}