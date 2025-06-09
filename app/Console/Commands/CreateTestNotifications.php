// File: app/Console/Commands/CreateTestNotifications.php
// Command to create test notifications

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Notification;
use App\Models\User;
use App\Models\Product;
use App\Services\NotificationService;

class CreateTestNotifications extends Command
{
    protected $signature = 'notifications:create-test {--user-id= : Specific user ID} {--count=5 : Number of notifications to create}';
    protected $description = 'Create test notifications for debugging';

    public function handle()
    {
        $userId = $this->option('user-id');
        $count = (int) $this->option('count');
        
        if ($userId) {
            $user = User::find($userId);
            if (!$user) {
                $this->error("User with ID {$userId} not found!");
                return 1;
            }
            $users = collect([$user]);
        } else {
            $users = User::take(3)->get();
        }

        if ($users->isEmpty()) {
            $this->error('No users found!');
            return 1;
        }

        $this->info("Creating {$count} test notifications for {$users->count()} user(s)...");

        $notificationTypes = [
            [
                'type' => 'stock_alert',
                'title' => 'Stock critique détecté',
                'message' => 'Le produit Paracétamol a un stock critique (3 unités restantes)',
                'priority' => 'high',
                'data' => ['product_id' => 1, 'current_stock' => 3]
            ],
            [
                'type' => 'expiry_alert',
                'title' => 'Produit bientôt expiré',
                'message' => 'Le produit Ibuprofène expire dans 10 jours',
                'priority' => 'medium',
                'data' => ['product_id' => 2, 'days_until_expiry' => 10]
            ],
            [
                'type' => 'sale_created',
                'title' => 'Nouvelle vente importante',
                'message' => 'Vente de 89.50€ effectuée par Jean Dupont',
                'priority' => 'low',
                'data' => ['sale_id' => 1, 'amount' => 89.50]
            ],
            [
                'type' => 'prescription_ready',
                'title' => 'Ordonnance prête',
                'message' => 'L\'ordonnance pour Marie Leclerc est prête à être délivrée',
                'priority' => 'medium',
                'data' => ['prescription_id' => 1]
            ],
            [
                'type' => 'system_alert',
                'title' => 'Test système',
                'message' => 'Notification de test du système de notifications',
                'priority' => 'normal',
                'data' => ['test' => true]
            ]
        ];

        $created = 0;
        foreach ($users as $user) {
            for ($i = 0; $i < $count; $i++) {
                $notifData = $notificationTypes[$i % count($notificationTypes)];
                
                try {
                    Notification::create([
                        'user_id' => $user->id,
                        'type' => $notifData['type'],
                        'title' => $notifData['title'],
                        'message' => $notifData['message'],
                        'priority' => $notifData['priority'],
                        'data' => $notifData['data'],
                        'read_at' => $i % 3 === 0 ? now()->subMinutes(rand(5, 120)) : null, // Some read
                        'created_at' => now()->subMinutes(rand(1, 1440)), // Random time
                    ]);
                    $created++;
                } catch (\Exception $e) {
                    $this->error("Failed to create notification: " . $e->getMessage());
                }
            }
        }

        $this->info("✅ Successfully created {$created} test notifications!");
        return 0;
    }
}