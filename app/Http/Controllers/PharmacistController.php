<?php
// app/Http/Controllers/PharmacistController.php - Complete implementation

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Client;
use App\Models\Product;
use App\Models\Prescription;
use App\Models\ActivityLog;

class PharmacistController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware(\Illuminate\Auth\Middleware\Authenticate::class);
        $this->middleware(\App\Http\Middleware\PharmacistMiddleware::class);
    }

    /**
     * Show the pharmacist dashboard with real data.
     */
    public function index()
    {
        // Log dashboard access
        try {
            ActivityLog::logActivity(
                'view',
                'Accès au tableau de bord pharmacien',
                null,
                null,
                ['dashboard_type' => 'pharmacist', 'access_time' => now()]
            );
        } catch (\Exception $e) {
            // Silent fail for logging
        }

        // Calculate real-time statistics
        $stats = [
            // Today's sales
            'sales_today' => Sale::whereDate('sale_date', today())->sum('total_amount') ?? 0,
            'sales_count_today' => Sale::whereDate('sale_date', today())->count(),
            'sales_average_today' => 0,
            
            // Client statistics
            'clients_today' => Sale::whereDate('sale_date', today())->distinct('client_id')->count('client_id'),
            'total_active_clients' => Client::where('active', true)->count(),
            'new_clients_this_week' => Client::where('created_at', '>=', now()->startOfWeek())->count(),
            
            // Prescription statistics
            'prescriptions_pending' => Prescription::where('status', 'pending')->count(),
            'prescriptions_today' => Prescription::whereDate('created_at', today())->count(),
            'prescriptions_expiring_soon' => Prescription::where('expiry_date', '<=', now()->addDays(7))
                                                        ->where('expiry_date', '>', now())
                                                        ->count(),
            
            // Inventory alerts
            'low_stock_products' => Product::whereColumn('stock_quantity', '<=', 'stock_threshold')->count(),
            'out_of_stock_products' => Product::where('stock_quantity', '<=', 0)->count(),
            'expiring_products' => Product::where('expiry_date', '<=', now()->addDays(30))
                                         ->where('expiry_date', '>', now())
                                         ->count(),
            
            // Weekly statistics
            'sales_this_week' => Sale::whereBetween('sale_date', [now()->startOfWeek(), now()->endOfWeek()])->sum('total_amount') ?? 0,
            'clients_this_week' => Sale::whereBetween('sale_date', [now()->startOfWeek(), now()->endOfWeek()])
                                      ->distinct('client_id')->count('client_id'),
            
            // Monthly statistics
            'sales_this_month' => Sale::whereMonth('sale_date', now()->month)->sum('total_amount') ?? 0,
            'prescriptions_this_month' => Prescription::whereMonth('created_at', now()->month)->count(),
        ];

        // Calculate average sale for today
        if ($stats['sales_count_today'] > 0) {
            $stats['sales_average_today'] = $stats['sales_today'] / $stats['sales_count_today'];
        }

        // Get recent sales (last 10)
        $recentSales = Sale::with(['client', 'user', 'saleItems.product'])
                          ->latest('sale_date')
                          ->take(10)
                          ->get();

        // Get low stock products (top 10 most critical)
        $lowStockProducts = Product::with(['category', 'supplier'])
                                  ->whereColumn('stock_quantity', '<=', 'stock_threshold')
                                  ->orderBy('stock_quantity', 'asc')
                                  ->take(10)
                                  ->get();

        // Get recent prescriptions
        $recentPrescriptions = Prescription::with(['client', 'createdBy'])
                                         ->latest('created_at')
                                         ->take(5)
                                         ->get();

        // Get products expiring soon
        $expiringProducts = Product::with(['category'])
                                  ->where('expiry_date', '<=', now()->addDays(30))
                                  ->where('expiry_date', '>', now())
                                  ->orderBy('expiry_date', 'asc')
                                  ->take(5)
                                  ->get();

        // Get top selling products (this month)
        $topProducts = Sale::with(['saleItems.product'])
                          ->whereMonth('sale_date', now()->month)
                          ->get()
                          ->flatMap(function ($sale) {
                              return $sale->saleItems;
                          })
                          ->groupBy('product_id')
                          ->map(function ($items) {
                              $product = $items->first()->product;
                              $totalQuantity = $items->sum('quantity');
                              $totalRevenue = $items->sum('total_price');
                              
                              return [
                                  'product' => $product,
                                  'quantity_sold' => $totalQuantity,
                                  'total_revenue' => $totalRevenue,
                              ];
                          })
                          ->sortByDesc('quantity_sold')
                          ->take(5);

        // Recent activity summary
        $recentActivity = [
            'last_sale' => Sale::latest('sale_date')->first(),
            'last_prescription' => Prescription::latest('created_at')->first(),
            'last_client' => Client::latest('created_at')->first(),
        ];

        // Quick access data
        $quickAccess = [
            'prescription_required_products' => Product::where('prescription_required', true)->count(),
            'over_counter_products' => Product::where('prescription_required', false)->count(),
            'active_clients_with_allergies' => Client::where('active', true)
                                                    ->whereNotNull('allergies')
                                                    ->where('allergies', '!=', '')
                                                    ->count(),
        ];

        // Performance metrics for pharmacist
        $performance = [
            'sales_growth_week' => $this->calculateGrowthRate('week', 'sales'),
            'client_growth_week' => $this->calculateGrowthRate('week', 'clients'),
            'prescriptions_processed_today' => Prescription::whereDate('updated_at', today())
                                                          ->where('status', '!=', 'pending')
                                                          ->count(),
        ];

        return view('pharmacist.dashboard', compact(
            'stats',
            'recentSales',
            'lowStockProducts',
            'recentPrescriptions',
            'expiringProducts',
            'topProducts',
            'recentActivity',
            'quickAccess',
            'performance'
        ));
    }

    /**
     * Calculate growth rate for metrics
     */
    private function calculateGrowthRate($period, $metric)
    {
        try {
            $current = 0;
            $previous = 0;

            if ($period === 'week') {
                $currentWeekStart = now()->startOfWeek();
                $currentWeekEnd = now()->endOfWeek();
                $previousWeekStart = now()->subWeek()->startOfWeek();
                $previousWeekEnd = now()->subWeek()->endOfWeek();

                if ($metric === 'sales') {
                    $current = Sale::whereBetween('sale_date', [$currentWeekStart, $currentWeekEnd])->sum('total_amount');
                    $previous = Sale::whereBetween('sale_date', [$previousWeekStart, $previousWeekEnd])->sum('total_amount');
                } elseif ($metric === 'clients') {
                    $current = Sale::whereBetween('sale_date', [$currentWeekStart, $currentWeekEnd])
                                  ->distinct('client_id')->count('client_id');
                    $previous = Sale::whereBetween('sale_date', [$previousWeekStart, $previousWeekEnd])
                                   ->distinct('client_id')->count('client_id');
                }
            }

            if ($previous > 0) {
                return round((($current - $previous) / $previous) * 100, 1);
            }

            return $current > 0 ? 100 : 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get dashboard data via AJAX
     */
    public function getDashboardData(Request $request)
    {
        $type = $request->get('type', 'overview');

        switch ($type) {
            case 'sales':
                return response()->json([
                    'today' => Sale::whereDate('sale_date', today())->sum('total_amount'),
                    'count' => Sale::whereDate('sale_date', today())->count(),
                    'week' => Sale::whereBetween('sale_date', [now()->startOfWeek(), now()->endOfWeek()])->sum('total_amount'),
                    'month' => Sale::whereMonth('sale_date', now()->month)->sum('total_amount'),
                ]);

            case 'alerts':
                return response()->json([
                    'low_stock' => Product::whereColumn('stock_quantity', '<=', 'stock_threshold')->count(),
                    'out_of_stock' => Product::where('stock_quantity', '<=', 0)->count(),
                    'expiring' => Product::where('expiry_date', '<=', now()->addDays(30))
                                        ->where('expiry_date', '>', now())->count(),
                    'prescriptions_pending' => Prescription::where('status', 'pending')->count(),
                ]);

            case 'recent_sales':
                $sales = Sale::with(['client', 'saleItems.product'])
                            ->latest('sale_date')
                            ->take(5)
                            ->get()
                            ->map(function ($sale) {
                                return [
                                    'id' => $sale->id,
                                    'sale_number' => $sale->sale_number,
                                    'client_name' => $sale->client ? $sale->client->full_name : 'Client anonyme',
                                    'total_amount' => $sale->total_amount,
                                    'items_count' => $sale->saleItems->count(),
                                    'created_at' => $sale->sale_date->diffForHumans(),
                                ];
                            });
                return response()->json($sales);

            default:
                return response()->json([
                    'sales_today' => Sale::whereDate('sale_date', today())->sum('total_amount'),
                    'clients_today' => Sale::whereDate('sale_date', today())->distinct('client_id')->count('client_id'),
                    'prescriptions_pending' => Prescription::where('status', 'pending')->count(),
                    'alerts_count' => Product::whereColumn('stock_quantity', '<=', 'stock_threshold')->count(),
                ]);
        }
    }

    /**
     * Get quick stats for header or widgets
     */
    public function getQuickStats()
    {
        return response()->json([
            'sales_today' => [
                'amount' => Sale::whereDate('sale_date', today())->sum('total_amount'),
                'count' => Sale::whereDate('sale_date', today())->count(),
            ],
            'alerts' => [
                'stock' => Product::whereColumn('stock_quantity', '<=', 'stock_threshold')->count(),
                'prescriptions' => Prescription::where('status', 'pending')->count(),
                'expiring' => Product::where('expiry_date', '<=', now()->addDays(30))
                                    ->where('expiry_date', '>', now())->count(),
            ],
            'activity' => [
                'last_sale' => Sale::latest('sale_date')->first()?->sale_date?->diffForHumans(),
                'last_prescription' => Prescription::latest('created_at')->first()?->created_at?->diffForHumans(),
            ]
        ]);
    }

    /**
     * Search functionality for pharmacist (products, clients, prescriptions)
     */
    public function search(Request $request)
    {
        $query = $request->get('q');
        $type = $request->get('type', 'all');

        if (empty($query)) {
            return response()->json(['results' => []]);
        }

        $results = [];

        if ($type === 'all' || $type === 'products') {
            $products = Product::where('name', 'like', "%{$query}%")
                              ->orWhere('barcode', 'like', "%{$query}%")
                              ->with('category')
                              ->take(5)
                              ->get();

            foreach ($products as $product) {
                $results[] = [
                    'type' => 'product',
                    'id' => $product->id,
                    'title' => $product->name,
                    'subtitle' => $product->category->name ?? 'Sans catégorie',
                    'url' => route('inventory.show', $product->id),
                    'stock' => $product->stock_quantity,
                    'price' => $product->selling_price,
                ];
            }
        }

        if ($type === 'all' || $type === 'clients') {
            $clients = Client::where('first_name', 'like', "%{$query}%")
                            ->orWhere('last_name', 'like', "%{$query}%")
                            ->orWhere('email', 'like', "%{$query}%")
                            ->where('active', true)
                            ->take(5)
                            ->get();

            foreach ($clients as $client) {
                $results[] = [
                    'type' => 'client',
                    'id' => $client->id,
                    'title' => $client->full_name,
                    'subtitle' => $client->email ?: $client->phone,
                    'url' => route('clients.show', $client->id),
                ];
            }
        }

        if ($type === 'all' || $type === 'prescriptions') {
            $prescriptions = Prescription::where('prescription_number', 'like', "%{$query}%")
                                        ->orWhereHas('client', function($q) use ($query) {
                                            $q->where('first_name', 'like', "%{$query}%")
                                              ->orWhere('last_name', 'like', "%{$query}%");
                                        })
                                        ->with('client')
                                        ->take(5)
                                        ->get();

            foreach ($prescriptions as $prescription) {
                $results[] = [
                    'type' => 'prescription',
                    'id' => $prescription->id,
                    'title' => $prescription->prescription_number,
                    'subtitle' => $prescription->client->full_name ?? 'Client inconnu',
                    'url' => route('prescriptions.show', $prescription->id),
                    'status' => $prescription->status,
                ];
            }
        }

        return response()->json(['results' => $results]);
    }
}