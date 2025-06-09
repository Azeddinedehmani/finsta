<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Notification;
use App\Models\User;
use App\Models\Product;
use Carbon\Carbon;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $products = Product::take(5)->get();
        
        if ($users->isEmpty()) {
            $this->command->warn('Aucun utilisateur trouvé. Veuillez d\'abord exécuter le UserSeeder.');
            return;
        }

        $notifications = [
            [
                'type' => 'stock_alert',
                'title' => 'Stock critique - Paracétamol',
                'message' => 'Le produit Paracétamol 500mg a un stock critique (5 unités restantes)',
                'priority' => 'high',
                'data' => ['product_id' => $products->first()?->id, 'current_stock' => 5],
                'action_url' => $products->first() ? route('inventory.show', $products->first()->id) : null,
            ],
            [
                'type' => 'expiry_alert',
                'title' => 'Produit bientôt expiré',
                'message' => 'Le produit Ibuprofène expire dans 15 jours',
                'priority' => 'medium',
                'data' => ['product_id' => $products->get(1)?->id, 'days_until_expiry' => 15],
                'action_url' => $products->get(1) ? route('inventory.show', $products->get(1)->id) : null,
            ],
            [
                'type' => 'sale_created',
                'title' => 'Nouvelle vente importante',
                'message' => 'Vente #VTE-20250602-001 de 125.50€ effectuée par Jean Dupont',
                'priority' => 'low',
                'data' => ['sale_id' => 1, 'amount' => 125.50],
                'action_url' => '#',
            ],
            [
                'type' => 'prescription_ready',
                'title' => 'Ordonnance prête',
                'message' => 'L\'ordonnance #ORD-20250602-003 pour Marie Leclerc est prête à être délivrée',
                'priority' => 'medium',
                'data' => ['prescription_id' => 1],
                'action_url' => '#',
            ],
            [
                'type' => 'system_alert',
                'title' => 'Maintenance programmée',
                'message' => 'Une maintenance système est programmée dimanche prochain de 2h à 4h du matin',
                'priority' => 'normal',
                'data' => ['maintenance_date' => '2025-06-08 02:00:00'],
                'action_url' => null,
                'expires_at' => Carbon::now()->addDays(7),
            ],
            [
                'type' => 'purchase_received',
                'title' => 'Livraison reçue',
                'message' => 'Commande #ACH-20250601-002 de Pharma Distrib reçue et stockée',
                'priority' => 'low',
                'data' => ['purchase_id' => 1],
                'action_url' => '#',
            ],
        ];

        foreach ($users as $user) {
            foreach ($notifications as $index => $notificationData) {
                // Create different notifications for different users
                if ($user->isAdmin() || in_array($notificationData['type'], ['stock_alert', 'expiry_alert', 'prescription_ready'])) {
                    Notification::create([
                        'user_id' => $user->id,
                        'type' => $notificationData['type'],
                        'title' => $notificationData['title'],
                        'message' => $notificationData['message'],
                        'priority' => $notificationData['priority'],
                        'data' => $notificationData['data'],
                        'action_url' => $notificationData['action_url'],
                        'expires_at' => $notificationData['expires_at'] ?? null,
                        'read_at' => $index % 3 === 0 ? Carbon::now()->subHours(rand(1, 24)) : null, // Mark some as read
                        'created_at' => Carbon::now()->subMinutes(rand(10, 1440)), // Random time in last 24h
                    ]);
                }
            }
            
            // Create some older notifications
            if ($user->isAdmin()) {
                for ($i = 0; $i < 3; $i++) {
                    Notification::create([
                        'user_id' => $user->id,
                        'type' => 'system_alert',
                        'title' => 'Notification ancienne',
                        'message' => 'Ceci est une ancienne notification de test',
                        'priority' => 'low',
                        'data' => ['test' => true],
                        'read_at' => Carbon::now()->subDays(rand(5, 30)),
                        'created_at' => Carbon::now()->subDays(rand(7, 40)),
                    ]);
                }
            }
        }

        $this->command->info('NotificationSeeder: Notifications de test créées avec succès.');
    }
}