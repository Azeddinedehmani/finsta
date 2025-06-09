<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Sale;
use App\Models\Product;
use App\Models\Client;
use App\Models\Prescription;
use App\Models\Purchase;
use App\Models\User;
use App\Models\ActivityLog;
use App\Models\Supplier;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display the main reports dashboard
     */
    public function index()
    {
        $user = auth()->user();
        
        // Données pour les graphiques de base
        $salesStats = $this->getSalesStats();
        $inventoryStats = $this->getInventoryStats();
        $clientStats = $this->getClientStats();
        
        if ($user->isAdmin()) {
            $purchaseStats = $this->getPurchaseStats();
            $userStats = $this->getUserStats();
            return view('rapports.index', compact('salesStats', 'inventoryStats', 'clientStats', 'purchaseStats', 'userStats'));
        }
        
        return view('rapports.index', compact('salesStats', 'inventoryStats', 'clientStats'));
    }

    /**
     * Sales report
     */
    public function sales(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));
        $groupBy = $request->get('group_by', 'day');

        // Ventes par période
        $salesByPeriod = $this->getSalesByPeriod($dateFrom, $dateTo, $groupBy);
        
        // Top produits vendus
        $topProducts = $this->getTopProducts($dateFrom, $dateTo, 10);
        
        // Ventes par utilisateur
        $salesByUser = $this->getSalesByUser($dateFrom, $dateTo);
        
        // Ventes par méthode de paiement
        $salesByPaymentMethod = $this->getSalesByPaymentMethod($dateFrom, $dateTo);
        
        // Statistiques générales
        $totalSales = Sale::whereBetween('sale_date', [$dateFrom, $dateTo])->sum('total_amount');
        $totalTransactions = Sale::whereBetween('sale_date', [$dateFrom, $dateTo])->count();
        $averageTransaction = $totalTransactions > 0 ? $totalSales / $totalTransactions : 0;
        
        return view('rapports.sales', compact(
            'salesByPeriod', 'topProducts', 'salesByUser', 'salesByPaymentMethod',
            'totalSales', 'totalTransactions', 'averageTransaction',
            'dateFrom', 'dateTo', 'groupBy'
        ));
    }

    /**
     * Inventory report
     */
    public function inventory()
    {
        // Produits avec stock faible
        $lowStockProducts = Product::whereColumn('stock_quantity', '<=', 'stock_threshold')
            ->with('category', 'supplier')
            ->orderBy('stock_quantity')
            ->get();

        // Produits en rupture
        $outOfStockProducts = Product::where('stock_quantity', '<=', 0)
            ->with('category', 'supplier')
            ->get();

        // Produits qui expirent bientôt
        $expiringProducts = Product::where('expiry_date', '<=', now()->addDays(30))
            ->where('expiry_date', '>', now())
            ->with('category', 'supplier')
            ->orderBy('expiry_date')
            ->get();

        // Top catégories par valeur de stock
        $categoriesValue = DB::table('products')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select('categories.name as category_name', 
                     DB::raw('SUM(products.stock_quantity * products.purchase_price) as total_value'),
                     DB::raw('SUM(products.stock_quantity) as total_quantity'))
            ->whereNull('products.deleted_at')
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('total_value', 'desc')
            ->get();

        // Statistiques générales
        $totalProducts = Product::count();
        $totalStockValue = Product::sum(DB::raw('stock_quantity * purchase_price'));
        $averageStockLevel = Product::avg('stock_quantity');

        return view('rapports.inventory', compact(
            'lowStockProducts', 'outOfStockProducts', 'expiringProducts', 'categoriesValue',
            'totalProducts', 'totalStockValue', 'averageStockLevel'
        ));
    }

    /**
     * Client report
     */
    public function clients(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));

        // Top clients par montant dépensé
        $topClients = Client::withSum(['sales as total_spent' => function($query) use ($dateFrom, $dateTo) {
            $query->whereBetween('sale_date', [$dateFrom, $dateTo]);
        }], 'total_amount')
        ->withCount(['sales as total_purchases' => function($query) use ($dateFrom, $dateTo) {
            $query->whereBetween('sale_date', [$dateFrom, $dateTo]);
        }])
        ->having('total_spent', '>', 0)
        ->orderBy('total_spent', 'desc')
        ->take(20)
        ->get();

        // Nouveaux clients par mois
        $newClientsByMonth = Client::select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
            DB::raw('COUNT(*) as count')
        )
        ->where('created_at', '>=', now()->subMonths(12))
        ->groupBy('month')
        ->orderBy('month')
        ->get();

        // Clients avec allergies
        $clientsWithAllergies = Client::whereNotNull('allergies')
            ->where('allergies', '!=', '')
            ->count();

        // Statistiques générales
        $totalClients = Client::count();
        $activeClients = Client::where('active', true)->count();
        $clientsWithPurchases = Client::has('sales')->count();

        return view('rapports.clients', compact(
            'topClients', 'newClientsByMonth', 'clientsWithAllergies',
            'totalClients', 'activeClients', 'clientsWithPurchases',
            'dateFrom', 'dateTo'
        ));
    }

    /**
     * Financial report
     */
    public function financial(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));

        // Revenus et dépenses
        $revenue = Sale::whereBetween('sale_date', [$dateFrom, $dateTo])
            ->where('payment_status', 'paid')
            ->sum('total_amount');

        $expenses = Purchase::whereBetween('order_date', [$dateFrom, $dateTo])
            ->where('status', 'received')
            ->sum('total_amount');

        $profit = $revenue - $expenses;

        // Revenus par mois (12 derniers mois)
        $revenueByMonth = DB::table('sales')
            ->select(
                DB::raw('DATE_FORMAT(sale_date, "%Y-%m") as month'),
                DB::raw('SUM(total_amount) as revenue'),
                DB::raw('COUNT(*) as transactions')
            )
            ->where('sale_date', '>=', now()->subMonths(12))
            ->where('payment_status', 'paid')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Dépenses par mois
        $expensesByMonth = DB::table('purchases')
            ->select(
                DB::raw('DATE_FORMAT(order_date, "%Y-%m") as month'),
                DB::raw('SUM(total_amount) as expenses')
            )
            ->where('order_date', '>=', now()->subMonths(12))
            ->where('status', 'received')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Marges par produit
        $productMargins = DB::table('sale_items')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->select(
                'products.name',
                DB::raw('SUM(sale_items.quantity) as total_sold'),
                DB::raw('SUM(sale_items.total_price) as total_revenue'),
                DB::raw('SUM(sale_items.quantity * products.purchase_price) as total_cost'),
                DB::raw('SUM(sale_items.total_price - (sale_items.quantity * products.purchase_price)) as total_margin')
            )
            ->whereBetween('sales.sale_date', [$dateFrom, $dateTo])
            ->where('sales.payment_status', 'paid')
            ->groupBy('products.id', 'products.name')
            ->having('total_sold', '>', 0)
            ->orderBy('total_margin', 'desc')
            ->take(20)
            ->get();

        return view('rapports.financial', compact(
            'revenue', 'expenses', 'profit', 'revenueByMonth', 'expensesByMonth', 'productMargins',
            'dateFrom', 'dateTo'
        ));
    }

    /**
     * Prescriptions report
     */
    public function prescriptions(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));

        // Ordonnances par statut
        $prescriptionsByStatus = Prescription::select('status', DB::raw('COUNT(*) as count'))
            ->whereBetween('prescription_date', [$dateFrom, $dateTo])
            ->groupBy('status')
            ->get();

        // Ordonnances expirées
        $expiredPrescriptions = Prescription::where('expiry_date', '<', now())
            ->where('status', '!=', 'completed')
            ->with('client')
            ->orderBy('expiry_date', 'desc')
            ->take(20)
            ->get();

        // Top médicaments prescrits
        $topPrescribedMedications = DB::table('prescription_items')
            ->join('products', 'prescription_items.product_id', '=', 'products.id')
            ->join('prescriptions', 'prescription_items.prescription_id', '=', 'prescriptions.id')
            ->select(
                'products.name',
                DB::raw('SUM(prescription_items.quantity_prescribed) as total_prescribed'),
                DB::raw('SUM(prescription_items.quantity_delivered) as total_delivered'),
                DB::raw('COUNT(DISTINCT prescription_items.prescription_id) as prescription_count')
            )
            ->whereBetween('prescriptions.prescription_date', [$dateFrom, $dateTo])
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_prescribed', 'desc')
            ->take(15)
            ->get();

        // Statistiques générales
        $totalPrescriptions = Prescription::whereBetween('prescription_date', [$dateFrom, $dateTo])->count();
        $completedPrescriptions = Prescription::whereBetween('prescription_date', [$dateFrom, $dateTo])
            ->where('status', 'completed')->count();
        $completionRate = $totalPrescriptions > 0 ? ($completedPrescriptions / $totalPrescriptions) * 100 : 0;

        return view('rapports.prescriptions', compact(
            'prescriptionsByStatus', 'expiredPrescriptions', 'topPrescribedMedications',
            'totalPrescriptions', 'completedPrescriptions', 'completionRate',
            'dateFrom', 'dateTo'
        ));
    }

    /**
     * CORRIGÉ : Suppliers report - Version sans erreurs SQL
     */
    public function suppliers(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));

        // Statistiques générales des fournisseurs
        $totalSuppliers = Supplier::count();
        $activeSuppliers = Supplier::where('active', true)->count();
        $suppliersWithProducts = Supplier::has('products')->count();
        $suppliersWithoutProducts = Supplier::doesntHave('products')->count();

        // Top fournisseurs par nombre de produits - REQUÊTE CORRIGÉE
        $topSuppliersByProducts = Supplier::withCount('products')
            ->orderBy('products_count', 'desc')
            ->take(15)
            ->get();

        // Fournisseurs par valeur de stock - REQUÊTE CORRIGÉE
        $suppliersByStockValue = Supplier::select([
            'suppliers.id',
            'suppliers.name',
            'suppliers.contact_person',
            'suppliers.phone_number',
            'suppliers.email',
            'suppliers.active',
            'suppliers.created_at',
            'suppliers.updated_at'
        ])
        ->selectRaw('COALESCE(SUM(products.stock_quantity * products.purchase_price), 0) as total_stock_value')
        ->selectRaw('COALESCE(SUM(products.stock_quantity), 0) as total_stock_quantity')
        ->selectRaw('COUNT(products.id) as products_count')
        ->leftJoin('products', function($join) {
            $join->on('suppliers.id', '=', 'products.supplier_id')
                 ->whereNull('products.deleted_at');
        })
        ->groupBy([
            'suppliers.id', 'suppliers.name', 'suppliers.contact_person',
            'suppliers.phone_number', 'suppliers.email', 'suppliers.active',
            'suppliers.created_at', 'suppliers.updated_at'
        ])
        ->having('products_count', '>', 0)
        ->orderBy('total_stock_value', 'desc')
        ->take(15)
        ->get();

        // Commandes par fournisseur (période sélectionnée) - REQUÊTE CORRIGÉE
        $purchasesBySupplier = Purchase::select([
            'purchases.supplier_id',
            'suppliers.name',
            'suppliers.contact_person'
        ])
        ->selectRaw('COUNT(purchases.id) as orders_count')
        ->selectRaw('SUM(purchases.total_amount) as total_amount')
        ->selectRaw('AVG(purchases.total_amount) as average_amount')
        ->join('suppliers', 'purchases.supplier_id', '=', 'suppliers.id')
        ->whereBetween('purchases.order_date', [$dateFrom, $dateTo])
        ->groupBy([
            'purchases.supplier_id', 'suppliers.name', 'suppliers.contact_person'
        ])
        ->orderBy('total_amount', 'desc')
        ->get();

        // Fournisseurs avec produits en stock faible - REQUÊTE SIMPLIFIÉE
        $suppliersWithLowStock = Supplier::whereHas('products', function($query) {
            $query->whereColumn('stock_quantity', '<=', 'stock_threshold');
        })
        ->withCount(['products as low_stock_products_count' => function($query) {
            $query->whereColumn('stock_quantity', '<=', 'stock_threshold');
        }])
        ->orderBy('low_stock_products_count', 'desc')
        ->get();

        // Performance des fournisseurs (délais de livraison) - OPTIMISÉ
        $supplierPerformance = collect();
        
        $performanceData = Purchase::select([
            'purchases.supplier_id',
            'suppliers.name as supplier_name',
            'purchases.expected_date',
            'purchases.received_date'
        ])
        ->join('suppliers', 'purchases.supplier_id', '=', 'suppliers.id')
        ->whereNotNull('purchases.received_date')
        ->whereNotNull('purchases.expected_date')
        ->whereBetween('purchases.order_date', [$dateFrom, $dateTo])
        ->get()
        ->groupBy('supplier_id');

        foreach($performanceData as $supplierId => $purchases) {
            $totalDelays = 0;
            $onTimeDeliveries = 0;
            $lateDeliveries = 0;
            
            foreach($purchases as $purchase) {
                $expectedDate = Carbon::parse($purchase->expected_date);
                $receivedDate = Carbon::parse($purchase->received_date);
                
                if ($receivedDate <= $expectedDate) {
                    $onTimeDeliveries++;
                } else {
                    $lateDeliveries++;
                    $totalDelays += $receivedDate->diffInDays($expectedDate);
                }
            }
            
            $supplierPerformance->push((object)[
                'supplier_name' => $purchases->first()->supplier_name,
                'supplier_id' => $supplierId,
                'total_orders' => $purchases->count(),
                'on_time_deliveries' => $onTimeDeliveries,
                'late_deliveries' => $lateDeliveries,
                'on_time_percentage' => $purchases->count() > 0 ? ($onTimeDeliveries / $purchases->count()) * 100 : 0,
                'average_delay' => $lateDeliveries > 0 ? round($totalDelays / $lateDeliveries, 1) : 0
            ]);
        }
        
        $supplierPerformance = $supplierPerformance->sortByDesc('on_time_percentage');

        // Commandes en cours et en retard - SIMPLIFIÉ
        $pendingPurchases = Purchase::with('supplier')
            ->where('status', 'pending')
            ->orderBy('order_date')
            ->get()
            ->groupBy('supplier.name');

        $overduePurchases = Purchase::with('supplier')
            ->where('status', 'pending')
            ->where('expected_date', '<', now())
            ->orderBy('expected_date')
            ->get();

        return view('rapports.suppliers', compact(
            'totalSuppliers', 'activeSuppliers', 'suppliersWithProducts', 'suppliersWithoutProducts',
            'topSuppliersByProducts', 'suppliersByStockValue', 'purchasesBySupplier', 
            'suppliersWithLowStock', 'supplierPerformance', 'pendingPurchases', 'overduePurchases',
            'dateFrom', 'dateTo'
        ));
    }

    /**
     * CORRIGÉ : Users report (Admin only) - Version sans erreurs SQL
     */
    public function users(Request $request)
    {
        // Vérifier que l'utilisateur est admin
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Accès refusé.');
        }

        $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));

        // Statistiques générales des utilisateurs
        $totalUsers = User::count();
        $activeUsers = User::where('is_active', true)->count();
        $adminUsers = User::where('role', 'responsable')->count();
        $pharmacistUsers = User::where('role', 'pharmacien')->count();
        $usersNeedingPasswordChange = User::where('force_password_change', true)->count();

        // Activité des utilisateurs par mois (12 derniers mois) - REQUÊTE CORRIGÉE
        $userActivityByMonth = ActivityLog::select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
            DB::raw('COUNT(*) as activity_count'),
            DB::raw('COUNT(DISTINCT user_id) as active_users')
        )
        ->where('created_at', '>=', now()->subMonths(12))
        ->groupBy('month')
        ->orderBy('month')
        ->get();

        // Top utilisateurs par activité (période sélectionnée) - REQUÊTE CORRIGÉE
        $topUsersByActivity = User::select([
            'users.id',
            'users.name',
            'users.email',
            'users.role',
            'users.is_active',
            'users.last_login_at'
        ])
        ->selectRaw('COALESCE(COUNT(activity_logs.id), 0) as activity_count')
        ->selectRaw('COALESCE(COUNT(DISTINCT DATE(activity_logs.created_at)), 0) as active_days')
        ->leftJoin('activity_logs', function($join) use ($dateFrom, $dateTo) {
            $join->on('users.id', '=', 'activity_logs.user_id')
                 ->whereBetween('activity_logs.created_at', [$dateFrom, $dateTo]);
        })
        ->groupBy([
            'users.id', 'users.name', 'users.email', 'users.role',
            'users.is_active', 'users.last_login_at'
        ])
        ->orderBy('activity_count', 'desc')
        ->take(15)
        ->get();

        // Performance des ventes par utilisateur - REQUÊTE CORRIGÉE
        $salesPerformance = User::select([
            'users.id',
            'users.name',
            'users.email',
            'users.role'
        ])
        ->selectRaw('COALESCE(COUNT(sales.id), 0) as sales_count')
        ->selectRaw('COALESCE(SUM(sales.total_amount), 0) as total_sales_amount')
        ->selectRaw('COALESCE(AVG(sales.total_amount), 0) as average_sale_amount')
        ->leftJoin('sales', function($join) use ($dateFrom, $dateTo) {
            $join->on('users.id', '=', 'sales.user_id')
                 ->whereBetween('sales.sale_date', [$dateFrom, $dateTo]);
        })
        ->groupBy(['users.id', 'users.name', 'users.email', 'users.role'])
        ->having('sales_count', '>', 0)
        ->orderBy('total_sales_amount', 'desc')
        ->get();

        // Actions les plus fréquentes - REQUÊTE SIMPLE
        $topActions = ActivityLog::select('action')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw('COUNT(*) as count')
            ->groupBy('action')
            ->orderBy('count', 'desc')
            ->take(10)
            ->get();

        // Connexions par jour (7 derniers jours) - REQUÊTE SIMPLE
        $loginsByDay = ActivityLog::where('action', 'login')
            ->where('created_at', '>=', now()->subDays(7))
            ->select(DB::raw('DATE(created_at) as date'))
            ->selectRaw('COUNT(DISTINCT user_id) as unique_logins')
            ->selectRaw('COUNT(*) as total_logins')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Utilisateurs inactifs - REQUÊTE SIMPLE
        $inactiveUsers = User::where('is_active', true)
            ->where(function($query) {
                $query->where('last_login_at', '<', now()->subDays(30))
                      ->orWhereNull('last_login_at');
            })
            ->orderBy('last_login_at', 'asc')
            ->get();

        // Répartition des activités par type - REQUÊTE SIMPLE
        $activityByType = ActivityLog::select('action')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw('COUNT(*) as count')
            ->groupBy('action')
            ->orderBy('count', 'desc')
            ->get();

        // Connexions récentes - REQUÊTE SIMPLE
        $recentLogins = ActivityLog::with('user')
            ->where('action', 'login')
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        return view('rapports.users', compact(
            'totalUsers', 'activeUsers', 'adminUsers', 'pharmacistUsers', 'usersNeedingPasswordChange',
            'userActivityByMonth', 'topUsersByActivity', 'salesPerformance', 'topActions',
            'loginsByDay', 'inactiveUsers', 'activityByType', 'recentLogins', 'dateFrom', 'dateTo'
        ));
    }

    // Méthodes privées pour les statistiques

    private function getSalesStats()
    {
        return [
            'today' => Sale::whereDate('sale_date', today())->sum('total_amount'),
            'week' => Sale::whereBetween('sale_date', [now()->startOfWeek(), now()->endOfWeek()])->sum('total_amount'),
            'month' => Sale::whereMonth('sale_date', now()->month)->sum('total_amount'),
            'year' => Sale::whereYear('sale_date', now()->year)->sum('total_amount'),
        ];
    }

    private function getInventoryStats()
    {
        return [
            'total_products' => Product::count(),
            'low_stock' => Product::whereColumn('stock_quantity', '<=', 'stock_threshold')->count(),
            'out_of_stock' => Product::where('stock_quantity', '<=', 0)->count(),
            'expiring_soon' => Product::where('expiry_date', '<=', now()->addDays(30))->where('expiry_date', '>', now())->count(),
        ];
    }

    private function getClientStats()
    {
        return [
            'total_clients' => Client::count(),
            'active_clients' => Client::where('active', true)->count(),
            'new_this_month' => Client::whereMonth('created_at', now()->month)->count(),
            'with_allergies' => Client::whereNotNull('allergies')->where('allergies', '!=', '')->count(),
        ];
    }

    private function getPurchaseStats()
    {
        return [
            'pending_purchases' => Purchase::where('status', 'pending')->count(),
            'total_this_month' => Purchase::whereMonth('order_date', now()->month)->sum('total_amount'),
            'overdue_purchases' => Purchase::where('status', 'pending')->where('expected_date', '<', now())->count(),
        ];
    }

    private function getUserStats()
    {
        return [
            'total_users' => User::count(),
            'admins' => User::where('role', 'responsable')->count(),
            'pharmacists' => User::where('role', 'pharmacien')->count(),
        ];
    }

    private function getSalesByPeriod($dateFrom, $dateTo, $groupBy)
    {
        $format = match($groupBy) {
            'hour' => '%Y-%m-%d %H:00:00',
            'day' => '%Y-%m-%d',
            'week' => '%Y-%u',
            'month' => '%Y-%m',
            'year' => '%Y',
            default => '%Y-%m-%d'
        };

        return Sale::select(
            DB::raw("DATE_FORMAT(sale_date, '{$format}') as period"),
            DB::raw('SUM(total_amount) as total'),
            DB::raw('COUNT(*) as count')
        )
        ->whereBetween('sale_date', [$dateFrom, $dateTo])
        ->groupBy('period')
        ->orderBy('period')
        ->get();
    }

    private function getTopProducts($dateFrom, $dateTo, $limit)
    {
        return DB::table('sale_items')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->select(
                'products.name',
                DB::raw('SUM(sale_items.quantity) as total_quantity'),
                DB::raw('SUM(sale_items.total_price) as total_revenue')
            )
            ->whereBetween('sales.sale_date', [$dateFrom, $dateTo])
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_revenue', 'desc')
            ->take($limit)
            ->get();
    }

    private function getSalesByUser($dateFrom, $dateTo)
    {
        return Sale::select(
            'users.name',
            DB::raw('SUM(sales.total_amount) as total_sales'),
            DB::raw('COUNT(*) as total_transactions')
        )
        ->join('users', 'sales.user_id', '=', 'users.id')
        ->whereBetween('sale_date', [$dateFrom, $dateTo])
        ->groupBy('users.id', 'users.name')
        ->orderBy('total_sales', 'desc')
        ->get();
    }

    private function getSalesByPaymentMethod($dateFrom, $dateTo)
    {
        return Sale::select(
            'payment_method',
            DB::raw('SUM(total_amount) as total'),
            DB::raw('COUNT(*) as count')
        )
        ->whereBetween('sale_date', [$dateFrom, $dateTo])
        ->groupBy('payment_method')
        ->orderBy('total', 'desc')
        ->get();
    }
}