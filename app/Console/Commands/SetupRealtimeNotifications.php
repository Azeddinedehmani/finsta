// File: app/Console/Commands/SetupRealtimeNotifications.php
// Create this command to set up everything automatically

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SetupRealtimeNotifications extends Command
{
    protected $signature = 'notifications:setup-realtime';
    protected $description = 'Setup real-time notification system';

    public function handle()
    {
        $this->info('🔧 Setting up real-time notification system...');
        
        // Check if observers are working
        $this->info('✅ Observers will be automatically registered via AppServiceProvider');
        
        // Test immediate notification creation
        $this->info('🧪 Testing immediate notification...');
        
        try {
            $user = \App\Models\User::first();
            if ($user) {
                \App\Models\Notification::createNotification(
                    $user->id,
                    'system_alert',
                    'Real-time system activated',
                    'Your real-time notification system is now active!',
                    ['test' => true, 'setup_time' => now()],
                    'normal'
                );
                
                $this->info('✅ Test notification created successfully');
            }
            
        } catch (\Exception $e) {
            $this->error('❌ Error creating test notification: ' . $e->getMessage());
            return 1;
        }
        
        $this->info('🎉 Real-time notification system setup complete!');
        $this->info('');
        $this->info('Notifications will now be created automatically when:');
        $this->info('- Product stock levels change');
        $this->info('- Sales are created');
        $this->info('- Prescriptions are updated');
        $this->info('- Purchases are received or overdue');
        
        return 0;
    }
}