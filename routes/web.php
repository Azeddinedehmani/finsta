<?php
// routes/web.php - Updated with prescription delete route

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PharmacistController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes - Updated with Role-based Access Control
|--------------------------------------------------------------------------
*/

// ROUTE RACINE - FIXED
Route::get('/', function () {
    if (auth()->check()) {
        $user = auth()->user();
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        } else {
            return redirect()->route('pharmacist.dashboard');
        }
    }
    return redirect()->route('login');
})->name('home');

// Authentication routes - NO MIDDLEWARE TO AVOID CONFLICTS
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Password reset routes - NO MIDDLEWARE
Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.forgot');
Route::post('/forgot-password', [AuthController::class, 'sendResetCode'])->name('password.send.code');
Route::get('/reset-password', [AuthController::class, 'showResetForm'])->name('password.reset.form');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.reset');

// Protected routes
Route::middleware('auth')->group(function () {
    
    // Dashboard routes
    Route::get('/admin/dashboard', [AdminController::class, 'index'])->name('admin.dashboard')->middleware('admin');
    Route::get('/pharmacist/dashboard', [PharmacistController::class, 'index'])->name('pharmacist.dashboard')->middleware('pharmacist');
    
    // Routes des rapports - ADMIN ONLY
    Route::prefix('rapports')->name('reports.')->middleware('admin')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/ventes', [ReportController::class, 'sales'])->name('sales');
        Route::get('/inventaire', [ReportController::class, 'inventory'])->name('inventory');
        Route::get('/clients', [ReportController::class, 'clients'])->name('clients');
        Route::get('/ordonnances', [ReportController::class, 'prescriptions'])->name('prescriptions');
        Route::get('/financier', [ReportController::class, 'financial'])->name('financial');
        Route::get('/utilisateurs', [ReportController::class, 'users'])->name('users');
        Route::get('/fournisseurs', [ReportController::class, 'suppliers'])->name('suppliers');
    });

    // Notification routes - AVAILABLE TO BOTH ROLES
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/recent', [NotificationController::class, 'getRecent'])->name('recent');
        Route::get('/count', [NotificationController::class, 'getUnreadCount'])->name('count');
        Route::get('/settings', [NotificationController::class, 'settings'])->name('settings');
        Route::post('/settings', [NotificationController::class, 'updateSettings'])->name('settings.update');
        
    Route::post('/{id}/mark-read', [NotificationController::class, 'markAsRead'])->name('mark-read');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        
        Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
        Route::delete('/read/all', [NotificationController::class, 'deleteAllRead'])->name('delete-read');
        
        // Test route (only in local environment)
        if (app()->environment('local')) {
            Route::post('/test', [NotificationController::class, 'createTest'])->name('test');
        }
    });
    
    // Inventory management routes - AVAILABLE TO BOTH ROLES
    Route::resource('inventory', ProductController::class)->names([
        'index' => 'inventory.index',
        'create' => 'inventory.create',
        'store' => 'inventory.store',
        'show' => 'inventory.show',
        'edit' => 'inventory.edit',
        'update' => 'inventory.update',
        'destroy' => 'inventory.destroy'
    ]);
    
    // Client management routes - AVAILABLE TO BOTH ROLES
    Route::resource('clients', ClientController::class);
    Route::post('clients/{id}/deactivate', [ClientController::class, 'deactivate'])->name('clients.deactivate');
    Route::post('clients/{id}/reactivate', [ClientController::class, 'reactivate'])->name('clients.reactivate');
    Route::get('clients/{id}/dependencies', [ClientController::class, 'checkDependencies'])->name('clients.dependencies');
    Route::get('clients/export', [ClientController::class, 'export'])->name('clients.export');
    Route::get('clients/orphaned-sales', [ClientController::class, 'orphanedSales'])->name('clients.orphaned-sales');
    
    // Sales management routes - AVAILABLE TO BOTH ROLES
    Route::resource('sales', SaleController::class);
    Route::get('sales/{id}/print', [SaleController::class, 'print'])->name('sales.print');
    Route::get('sales/product/{id}', [SaleController::class, 'getProduct'])->name('sales.get-product');
    
    // Prescription management routes - AVAILABLE TO BOTH ROLES WITH DELETE FUNCTIONALITY
    Route::resource('prescriptions', PrescriptionController::class);
    Route::get('prescriptions/{id}/deliver', [PrescriptionController::class, 'deliver'])->name('prescriptions.deliver');
    Route::post('prescriptions/{id}/deliver', [PrescriptionController::class, 'processDelivery'])->name('prescriptions.process-delivery');
    Route::get('prescriptions/{id}/print', [PrescriptionController::class, 'print'])->name('prescriptions.print');
    // NEW: Add dependency check route for prescriptions
    Route::get('prescriptions/{id}/dependencies', [PrescriptionController::class, 'checkDependencies'])->name('prescriptions.dependencies');
    
    // Admin-only routes - SUPPLIERS & PURCHASES
    Route::middleware('admin')->group(function () {
        // Supplier management routes - ADMIN ONLY
        Route::resource('suppliers', SupplierController::class)->names([
            'index' => 'suppliers.index',
            'create' => 'suppliers.create',
            'store' => 'suppliers.store',
            'show' => 'suppliers.show',
            'edit' => 'suppliers.edit',
            'update' => 'suppliers.update',
            'destroy' => 'suppliers.destroy'
        ]);
        
        // Purchase management routes - ADMIN ONLY
        Route::resource('purchases', PurchaseController::class)->names([
            'index' => 'purchases.index',
            'create' => 'purchases.create',
            'store' => 'purchases.store',
            'show' => 'purchases.show',
            'edit' => 'purchases.edit',
            'update' => 'purchases.update',
            'destroy' => 'purchases.destroy'
        ]);
        
        Route::get('purchases/{id}/print', [PurchaseController::class, 'print'])->name('purchases.print');
        Route::get('purchases/{id}/receive', [PurchaseController::class, 'receive'])->name('purchases.receive');
        Route::post('purchases/{id}/receive', [PurchaseController::class, 'processReception'])->name('purchases.process-reception');
        Route::patch('purchases/{id}/cancel', [PurchaseController::class, 'cancel'])->name('purchases.cancel');
    });
    
    // Admin panel routes - ADMIN ONLY
    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        Route::get('/administration', [AdminController::class, 'administration'])->name('administration');
        Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
        Route::post('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');
        
        // System management routes
        Route::get('/system-status', [AdminController::class, 'systemStatus'])->name('system-status');
        Route::get('/performance-metrics', [AdminController::class, 'performanceMetrics'])->name('performance-metrics');
        Route::post('/toggle-maintenance', [AdminController::class, 'toggleMaintenance'])->name('toggle-maintenance');
        Route::post('/clear-cache', [AdminController::class, 'clearCache'])->name('clear-cache');
        Route::post('/optimize-database', [AdminController::class, 'optimizeDatabase'])->name('optimize-database');
        Route::get('/health-check', [AdminController::class, 'healthCheck'])->name('health-check');
        
        // User management
        Route::resource('users', UserController::class)->names([
            'index' => 'users.index',
            'create' => 'users.create',
            'store' => 'users.store',
            'show' => 'users.show',
            'edit' => 'users.edit',
            'update' => 'users.update',
            'destroy' => 'users.destroy'
        ]);
        
        Route::get('users/export', [UserController::class, 'export'])->name('users.export');
        Route::patch('users/{id}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::patch('users/{id}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
        Route::get('users/{id}/activity', [UserController::class, 'activityLogs'])->name('users.activity-logs');
        
        // Activity logs
        Route::get('/activity-logs', [AdminController::class, 'activityLogs'])->name('activity-logs');
        Route::get('/activity-logs/export', [AdminController::class, 'exportActivityLogs'])->name('export-activity-logs');
        Route::post('/clear-old-logs', [AdminController::class, 'clearOldLogs'])->name('clear-old-logs');
    });
});

// Block access to report routes for pharmacists
Route::middleware(['auth', 'pharmacist'])->group(function () {
    // Redirect pharmacists trying to access reports to their dashboard
    Route::get('/rapports{any?}', function () {
        return redirect()->route('pharmacist.dashboard')->with('error', 'Accès non autorisé. Les rapports sont réservés aux administrateurs.');
    })->where('any', '.*');
    
    // Block access to supplier routes for pharmacists
    Route::get('/suppliers{any?}', function () {
        return redirect()->route('pharmacist.dashboard')->with('error', 'Accès non autorisé. La gestion des fournisseurs est réservée aux administrateurs.');
    })->where('any', '.*');
    
    // Block access to purchase routes for pharmacists
    Route::get('/purchases{any?}', function () {
        return redirect()->route('pharmacist.dashboard')->with('error', 'Accès non autorisé. La gestion des achats est réservée aux administrateurs.');
    })->where('any', '.*');
    
    // Block access to admin routes for pharmacists
    Route::get('/admin{any?}', function () {
        return redirect()->route('pharmacist.dashboard')->with('error', 'Accès non autorisé. Les fonctions d\'administration sont réservées aux responsables.');
    })->where('any', '.*');
});

// Fallback route for undefined routes
Route::fallback(function () {
    if (auth()->check()) {
        $user = auth()->user();
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        } else {
            return redirect()->route('pharmacist.dashboard');
        }
    }
    return redirect()->route('login');
});