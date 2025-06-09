// File: app/Console/Commands/FixNotificationSystem.php
// Command to fix common notification issues

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Notification;
use App\Models\User;

class FixNotificationSystem extends Command
{
    protected $signature = 'notifications:fix {--force : Force fix without confirmation}';
    protected $description = 'Fix common notification system issues';

    public function handle()
    {
        $this->info('🔧 Fixing Notification System Issues...');
        $this->newLine();

        if (!$this->option('force')) {
            if (!$this->confirm('This will attempt to fix notification system issues. Continue?')) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        // Fix 1: Ensure all users have user relationships
        $this->info('1. Checking user relationships...');
        $this->fixUserRelationships();
        $this->newLine();

        // Fix 2: Clean up malformed notifications
        $this->info('2. Cleaning up malformed notifications...');
        $this->cleanupMalformedNotifications();
        $this->newLine();

        // Fix 3: Fix expired notifications
        $this->info('3. Fixing expired notifications...');
        $this->fixExpiredNotifications();
        $this->newLine();

        // Fix 4: Add missing indexes
        $this->info('4. Checking database indexes...');
        $this->checkDatabaseIndexes();
        $this->newLine();

        $this->info('✅ Notification system fixes completed!');
        return 0;
    }

    private function fixUserRelationships()
    {
        try {
            // Remove notifications for non-existent users
            $orphanedNotifications = Notification::whereNotExists(function($query) {
                $query->select(\DB::raw(1))
                      ->from('users')
                      ->whereRaw('users.id = notifications.user_id');
            })->count();

            if ($orphanedNotifications > 0) {
                Notification::whereNotExists(function($query) {
                    $query->select(\DB::raw(1))
                          ->from('users')
                          ->whereRaw('users.id = notifications.user_id');
                })->delete();
                
                $this->line("   ✅ Removed {$orphanedNotifications} orphaned notifications");
            } else {
                $this->line('   ✅ No orphaned notifications found');
            }
        } catch (\Exception $e) {
            $this->error('   ❌ Error fixing user relationships: ' . $e->getMessage());
        }
    }

    private function cleanupMalformedNotifications()
    {
        try {
            // Fix notifications with invalid JSON data
            $invalidDataCount = 0;
            Notification::whereNotNull('data')->chunk(100, function($notifications) use (&$invalidDataCount) {
                foreach ($notifications as $notification) {
                    if (is_string($notification->data)) {
                        $decoded = json_decode($notification->data, true);
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            $notification->update(['data' => null]);
                            $invalidDataCount++;
                        }
                    }
                }
            });

            if ($invalidDataCount > 0) {
                $this->line("   ✅ Fixed {$invalidDataCount} notifications with invalid JSON data");
            } else {
                $this->line('   ✅ No malformed JSON data found');
            }

            // Remove notifications with empty required fields
            $emptyFieldsCount = Notification::where(function($query) {
                $query->whereNull('title')
                      ->orWhereNull('message')
                      ->orWhereNull('type')
                      ->orWhere('title', '')
                      ->orWhere('message', '')
                      ->orWhere('type', '');
            })->count();

            if ($emptyFieldsCount > 0) {
                Notification::where(function($query) {
                    $query->whereNull('title')
                          ->orWhereNull('message')
                          ->orWhereNull('type')
                          ->orWhere('title', '')
                          ->orWhere('message', '')
                          ->orWhere('type', '');
                })->delete();
                
                $this->line("   ✅ Removed {$emptyFieldsCount} notifications with empty required fields");
            } else {
                $this->line('   ✅ No notifications with empty fields found');
            }
        } catch (\Exception $e) {
            $this->error('   ❌ Error cleaning up malformed notifications: ' . $e->getMessage());
        }
    }

    private function fixExpiredNotifications()
    {
        try {
            // Clean up very old read notifications (older than 60 days)
            $oldReadCount = Notification::whereNotNull('read_at')
                                      ->where('read_at', '<', now()->subDays(60))
                                      ->count();

            if ($oldReadCount > 0) {
                Notification::whereNotNull('read_at')
                           ->where('read_at', '<', now()->subDays(60))
                           ->delete();
                           
                $this->line("   ✅ Cleaned up {$oldReadCount} old read notifications");
            } else {
                $this->line('   ✅ No old read notifications to clean up');
            }

            // Mark expired notifications
            $expiredCount = Notification::where('expires_at', '<', now())
                                      ->whereNull('read_at')
                                      ->count();

            if ($expiredCount > 0) {
                Notification::where('expires_at', '<', now())
                           ->whereNull('read_at')
                           ->update(['read_at' => now()]);
                           
                $this->line("   ✅ Marked {$expiredCount} expired notifications as read");
            } else {
                $this->line('   ✅ No expired notifications to mark as read');
            }
        } catch (\Exception $e) {
            $this->error('   ❌ Error fixing expired notifications: ' . $e->getMessage());
        }
    }

    private function checkDatabaseIndexes()
    {
        try {
            // Check if indexes exist (simplified check)
            $indexes = \DB::select('SHOW INDEX FROM notifications');
            $indexNames = collect($indexes)->pluck('Key_name')->unique();
            
            $expectedIndexes = [
                'notifications_user_id_read_at_index',
                'notifications_type_created_at_index',
                'notifications_expires_at_index'
            ];

            foreach ($expectedIndexes as $expectedIndex) {
                if ($indexNames->contains($expectedIndex)) {
                    $this->line("   ✅ Index '{$expectedIndex}' exists");
                } else {
                    $this->warn("   ⚠️  Index '{$expectedIndex}' missing (this is OK if database structure is correct)");
                }
            }
        } catch (\Exception $e) {
            $this->warn('   ⚠️  Could not check indexes: ' . $e->getMessage());
        }
    }
     }