<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ActivityLog;
use App\Models\User;

class TestActivityLogging extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:activity-logging';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the activity logging system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Testing Activity Logging System...');
        $this->newLine();

        // Test 1: Check if activity logs exist
        $this->info('1. Checking for existing activity logs...');
        $totalLogs = ActivityLog::count();
        $this->line("   Total activity logs in database: {$totalLogs}");
        
        if ($totalLogs === 0) {
            $this->warn('   ⚠️  No activity logs found! The system might not be logging activities yet.');
        } else {
            $this->line('   ✅ Activity logs found');
        }
        $this->newLine();

        // Test 2: Check recent activities
        $this->info('2. Checking recent activities (last 24 hours)...');
        $recentLogs = ActivityLog::where('created_at', '>=', now()->subDay())->get();
        $this->line("   Recent activities: {$recentLogs->count()}");
        
        if ($recentLogs->count() > 0) {
            $this->line('   Recent activity breakdown:');
            $actionCounts = $recentLogs->groupBy('action')->map->count();
            foreach ($actionCounts as $action => $count) {
                $this->line("     - {$action}: {$count}");
            }
        } else {
            $this->warn('   ⚠️  No recent activities found');
        }
        $this->newLine();

        // Test 3: Check activities by user
        $this->info('3. Checking activities by user...');
        $users = User::withCount('activityLogs')->get();
        
        foreach ($users as $user) {
            $recentCount = $user->activityLogs()->where('created_at', '>=', now()->subDay())->count();
            $this->line("   {$user->name} ({$user->role}): {$user->activity_logs_count} total, {$recentCount} recent");
        }
        $this->newLine();

        // Test 4: Check for different action types
        $this->info('4. Checking action types distribution...');
        $actionTypes = ActivityLog::select('action')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('action')
            ->orderBy('count', 'desc')
            ->get();
            
        if ($actionTypes->count() > 0) {
            $this->line('   Action types found:');
            foreach ($actionTypes as $actionType) {
                $this->line("     - {$actionType->action}: {$actionType->count}");
            }
        } else {
            $this->warn('   ⚠️  No action types found');
        }
        $this->newLine();

        // Test 5: Check model types being tracked
        $this->info('5. Checking model types being tracked...');
        $modelTypes = ActivityLog::whereNotNull('model_type')
            ->select('model_type')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('model_type')
            ->orderBy('count', 'desc')
            ->get();
            
        if ($modelTypes->count() > 0) {
            $this->line('   Model types being tracked:');
            foreach ($modelTypes as $modelType) {
                $modelName = match($modelType->model_type) {
                    'App\Models\User' => 'Utilisateur',
                    'App\Models\Product' => 'Produit',
                    'App\Models\Sale' => 'Vente',
                    'App\Models\Client' => 'Client',
                    'App\Models\Prescription' => 'Ordonnance',
                    'App\Models\Purchase' => 'Achat',
                    'App\Models\Supplier' => 'Fournisseur',
                    default => class_basename($modelType->model_type)
                };
                $this->line("     - {$modelName}: {$modelType->count}");
            }
        } else {
            $this->warn('   ⚠️  No model types being tracked');
        }
        $this->newLine();

        // Test 6: Recent sample activities
        $this->info('6. Sample of recent activities...');
        $sampleActivities = ActivityLog::with('user')
            ->latest()
            ->take(5)
            ->get();
            
        if ($sampleActivities->count() > 0) {
            foreach ($sampleActivities as $activity) {
                $userName = $activity->user ? $activity->user->name : 'Système';
                $timeAgo = $activity->created_at->diffForHumans();
                $this->line("   [{$timeAgo}] {$userName}: {$activity->action} - {$activity->description}");
            }
        } else {
            $this->warn('   ⚠️  No sample activities found');
        }
        $this->newLine();

        // Test 7: Check middleware registration
        $this->info('7. Checking middleware configuration...');
        $middlewareAliases = config('app.aliases', []);
        $kernelMiddleware = app(\App\Http\Kernel::class);
        
        // Check if the middleware alias exists
        $middlewareRegistered = array_key_exists('log.activity', $kernelMiddleware->getMiddlewareAliases ?? []);
        
        if ($middlewareRegistered) {
            $this->line('   ✅ LogActivity middleware is registered');
        } else {
            $this->error('   ❌ LogActivity middleware is NOT registered');
            $this->line('   💡 Make sure to add \'log.activity\' => \App\Http\Middleware\LogActivity::class to Kernel.php');
        }
        $this->newLine();

        // Summary and recommendations
        $this->info('📊 Summary and Recommendations:');
        
        if ($totalLogs === 0) {
            $this->error('❌ No activity logging detected!');
            $this->line('Recommendations:');
            $this->line('1. Ensure LogActivity middleware is applied to protected routes');
            $this->line('2. Test by performing some actions in the web interface');
            $this->line('3. Check application logs for any errors');
        } elseif ($recentLogs->count() === 0) {
            $this->warn('⚠️  Activity logging exists but no recent activity detected');
            $this->line('Recommendations:');
            $this->line('1. Perform some actions in the web interface to test logging');
            $this->line('2. Check if middleware is applied to all necessary routes');
        } else {
            $this->line('✅ Activity logging appears to be working correctly!');
            $this->line("📈 {$recentLogs->count()} activities logged in the last 24 hours");
            
            // Check if all expected action types are present
            $expectedActions = ['list', 'view', 'create', 'update', 'delete', 'login', 'logout'];
            $foundActions = $actionTypes->pluck('action')->toArray();
            $missingActions = array_diff($expectedActions, $foundActions);
            
            if (count($missingActions) > 0) {
                $this->warn('Some action types are missing: ' . implode(', ', $missingActions));
                $this->line('This might be normal if these actions haven\'t been performed yet.');
            }
        }
        
        $this->newLine();
        $this->info('✅ Activity logging test completed!');
        
        return 0;
    }
}