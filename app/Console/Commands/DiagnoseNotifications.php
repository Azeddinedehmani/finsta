<?php
// File: app/Console/Commands/DiagnoseNotifications.php
// Create this new command to diagnose notification issues

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Notification;
use App\Models\User;
use App\Models\Product;
use App\Services\NotificationService;

class DiagnoseNotifications extends Command
{
    protected $signature = 'notifications:diagnose';
    protected $description = 'Diagnose notification system issues';

    public function handle()
    {
        $this->info('🔍 Diagnosing Notification System...');
        $this->newLine();

        // Test 1: Check database structure
        $this->info('1. Checking database structure...');
        $this->checkDatabaseStructure();
        $this->newLine();

        // Test 2: Check existing notifications
        $this->info('2. Checking existing notifications...');
        $this->checkExistingNotifications();
        $this->newLine();

        // Test 3: Test notification creation
        $this->info('3. Testing notification creation...');
        $this->testNotificationCreation();
        $this->newLine();

        // Test 4: Test NotificationService
        $this->info('4. Testing NotificationService...');
        $this->testNotificationService();
        $this->newLine();

        // Test 5: Check routes
        $this->info('5. Checking notification routes...');
        $this->checkRoutes();
        $this->newLine();

        $this->info('✅ Notification diagnosis completed!');
        return 0;
    }

    private function checkDatabaseStructure()
    {
        try {
            $tableExists = \Schema::hasTable('notifications');
            if ($tableExists) {
                $this->line('   ✅ Table "notifications" exists');
                
                $columns = \Schema::getColumnListing('notifications');
                $requiredColumns = ['id', 'user_id', 'type', 'title', 'message', 'data', 'read_at', 'action_url', 'priority', 'expires_at', 'created_at', 'updated_at'];
                
                foreach ($requiredColumns as $column) {
                    if (in_array($column, $columns)) {
                        $this->line("   ✅ Column '{$column}' exists");
                    } else {
                        $this->error("   ❌ Column '{$column}' missing");
                    }
                }
            } else {
                $this->error('   ❌ Table "notifications" does not exist!');
                $this->line('   💡 Run: php artisan migrate');
            }
        } catch (\Exception $e) {
            $this->error('   ❌ Database error: ' . $e->getMessage());
        }
    }

    private function checkExistingNotifications()
    {
        try {
            $totalNotifications = Notification::count();
            $this->line("   Total notifications: {$totalNotifications}");
            
            $unreadNotifications = Notification::whereNull('read_at')->count();
            $this->line("   Unread notifications: {$unreadNotifications}");
            
            $activeNotifications = Notification::where(function($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })->count();
            $this->line("   Active notifications: {$activeNotifications}");
            
            if ($totalNotifications > 0) {
                $this->line('   ✅ Notifications found in database');
                $byType = Notification::selectRaw('type, COUNT(*) as count')->groupBy('type')->get();
                foreach ($byType as $type) {
                    $this->line("     - {$type->type}: {$type->count}");
                }
            } else {
                $this->warn('   ⚠️  No notifications found in database');
            }
        } catch (\Exception $e) {
            $this->error('   ❌ Error checking notifications: ' . $e->getMessage());
        }
    }

    private function testNotificationCreation()
    {
        try {
            $user = User::first();
            if (!$user) {
                $this->error('   ❌ No users found! Cannot test notification creation.');
                return;
            }

            $notification = Notification::create([
                'user_id' => $user->id,
                'type' => 'system_alert',
                'title' => 'Test Notification',
                'message' => 'This is a test notification created by the diagnostic command.',
                'data' => ['test' => true, 'created_by' => 'diagnose_command'],
                'priority' => 'normal',
                'action_url' => null,
                'expires_at' => now()->addHours(1),
            ]);

            $this->line("   ✅ Successfully created test notification ID: {$notification->id}");
            
            // Test notification methods
            $this->line("   Testing notification methods:");
            $this->line("     - isRead(): " . ($notification->isRead() ? 'true' : 'false'));
            $this->line("     - isExpired(): " . ($notification->isExpired() ? 'true' : 'false'));
            $this->line("     - type_icon: {$notification->type_icon}");
            $this->line("     - type_label: {$notification->type_label}");
            $this->line("     - priority_badge: {$notification->priority_badge}");
            
        } catch (\Exception $e) {
            $this->error('   ❌ Failed to create test notification: ' . $e->getMessage());
        }
    }

    private function testNotificationService()
    {
        try {
            $service = app(NotificationService::class);
            $this->line('   ✅ NotificationService instantiated successfully');
            
            // Test method existence
            $methods = ['getUnreadCount', 'getRecentNotifications', 'cleanupOldNotifications'];
            foreach ($methods as $method) {
                if (method_exists($service, $method)) {
                    $this->line("   ✅ Method '{$method}' exists");
                } else {
                    $this->error("   ❌ Method '{$method}' missing");
                }
            }
            
            // Test actual service calls
            $user = User::first();
            if ($user) {
                $unreadCount = $service->getUnreadCount($user->id);
                $this->line("   Unread count for user {$user->id}: {$unreadCount}");
                
                $recent = $service->getRecentNotifications($user->id, 5);
                $this->line("   Recent notifications count: {$recent->count()}");
            }
            
        } catch (\Exception $e) {
            $this->error('   ❌ NotificationService error: ' . $e->getMessage());
        }
    }

    private function checkRoutes()
    {
        try {
            $routes = [
                'notifications.index',
                'notifications.mark-read',
                'notifications.mark-all-read',
                'notifications.destroy',
                'notifications.recent',
                'notifications.count',
                'notifications.settings',
            ];

            foreach ($routes as $routeName) {
                if (\Route::has($routeName)) {
                    $this->line("   ✅ Route '{$routeName}' exists");
                } else {
                    $this->error("   ❌ Route '{$routeName}' missing");
                }
            }
        } catch (\Exception $e) {
            $this->error('   ❌ Error checking routes: ' . $e->getMessage());
        }
    }
}