<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Client;
use App\Models\Product;
use App\Models\ActivityLog;

class SaleController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the sales.
     */
    public function index(Request $request)
    {
        $query = Sale::with(['client', 'user', 'saleItems.product']);

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('sale_number', 'like', "%{$search}%")
                  ->orWhere('prescription_number', 'like', "%{$search}%")
                  ->orWhereHas('client', function($clientQuery) use ($search) {
                      $clientQuery->where('first_name', 'like', "%{$search}%")
                                 ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by payment status
        if ($request->has('payment_status') && $request->payment_status !== '') {
            $query->where('payment_status', $request->payment_status);
        }

        // Filter by date range
        if ($request->has('date_from') && !empty($request->date_from)) {
            $query->whereDate('sale_date', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && !empty($request->date_to)) {
            $query->whereDate('sale_date', '<=', $request->date_to);
        }

        // Filter by prescription
        if ($request->has('has_prescription') && $request->has_prescription !== '') {
            $query->where('has_prescription', $request->has_prescription === 'yes');
        }

        $sales = $query->latest('sale_date')->paginate(15);
        
        // Calculate summary statistics
        $allSales = Sale::all();
        $totalSales = $allSales->sum('total_amount');
        $salesCount = $allSales->count();
        $averageSale = $salesCount > 0 ? $totalSales / $salesCount : 0;
        
        return view('sales.index', compact('sales', 'totalSales', 'salesCount', 'averageSale'));
    }

    /**
     * Show the form for creating a new sale.
     */
    public function create(Request $request)
    {
        $clients = Client::active()->orderBy('first_name')->get();
        $products = Product::where('stock_quantity', '>', 0)->orderBy('name')->get();
        
        // Pre-select client if passed in URL
        $selectedClientId = $request->get('client_id');
        
        return view('sales.create', compact('clients', 'products', 'selectedClientId'));
    }

    /**
     * Store a newly created sale in storage.
     */
    public function store(Request $request)
    {
        try {
            Log::info('Sale creation attempt', [
                'user_id' => auth()->id(),
                'request_data' => $request->all()
            ]);

            $validator = Validator::make($request->all(), [
                'client_id' => 'nullable|exists:clients,id',
                'payment_method' => 'required|in:cash,card,insurance,other',
                'has_prescription' => 'boolean',
                'prescription_number' => 'nullable|string|max:255',
                'discount_amount' => 'nullable|numeric|min:0',
                'notes' => 'nullable|string',
                'products' => 'required|array|min:1',
                'products.*.id' => 'required|exists:products,id',
                'products.*.quantity' => 'required|integer|min:1',
            ], [
                'products.required' => 'Veuillez ajouter au moins un produit à la vente.',
                'products.*.id.required' => 'ID produit manquant.',
                'products.*.id.exists' => 'Un des produits sélectionnés n\'existe pas.',
                'products.*.quantity.required' => 'Quantité manquante pour un produit.',
                'products.*.quantity.min' => 'La quantité doit être d\'au moins 1.',
            ]);

            if ($validator->fails()) {
                Log::warning('Sale creation validation failed', [
                    'errors' => $validator->errors()->toArray(),
                    'request_data' => $request->all()
                ]);
                
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // Validate stock availability and prepare product data
            $productData = [];
            foreach ($request->products as $item) {
                $product = Product::find($item['id']);
                if (!$product) {
                    Log::error('Product not found', ['product_id' => $item['id']]);
                    return redirect()->back()
                        ->withErrors(['products' => "Produit avec l'ID {$item['id']} introuvable."])
                        ->withInput();
                }
                
                $quantity = (int) $item['quantity'];
                if ($product->stock_quantity < $quantity) {
                    Log::warning('Insufficient stock', [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'available_stock' => $product->stock_quantity,
                        'requested_quantity' => $quantity
                    ]);
                    return redirect()->back()
                        ->withErrors(['products' => "Stock insuffisant pour {$product->name}. Stock disponible: {$product->stock_quantity}, demandé: {$quantity}"])
                        ->withInput();
                }
                
                $productData[] = [
                    'product' => $product,
                    'quantity' => $quantity
                ];
            }

            DB::beginTransaction();
            
            try {
                // Create sale
                $sale = new Sale();
                $sale->client_id = $request->client_id;
                $sale->user_id = auth()->id();
                $sale->payment_method = $request->payment_method;
                $sale->payment_status = 'paid';
                $sale->has_prescription = $request->has('has_prescription');
                $sale->prescription_number = $request->prescription_number;
                $sale->discount_amount = $request->discount_amount ?? 0;
                $sale->notes = $request->notes;
                $sale->sale_date = now();
                
                // Calculate totals before saving
                $subtotal = 0;
                foreach ($productData as $item) {
                    $subtotal += $item['product']->selling_price * $item['quantity'];
                }
                
                $sale->subtotal = $subtotal;
                $sale->tax_amount = $subtotal * 0.20; // 20% tax
                $sale->total_amount = $subtotal + $sale->tax_amount - $sale->discount_amount;
                
                $sale->save();

                Log::info('Sale created successfully', [
                    'sale_id' => $sale->id,
                    'sale_number' => $sale->sale_number,
                    'total_amount' => $sale->total_amount
                ]);

                // Create sale items and update stock
                foreach ($productData as $item) {
                    $saleItem = new SaleItem();
                    $saleItem->sale_id = $sale->id;
                    $saleItem->product_id = $item['product']->id;
                    $saleItem->quantity = $item['quantity'];
                    $saleItem->unit_price = $item['product']->selling_price;
                    $saleItem->total_price = $item['product']->selling_price * $item['quantity'];
                    $saleItem->save();

                    // Update product stock
                    $oldStock = $item['product']->stock_quantity;
                    $item['product']->decrement('stock_quantity', $item['quantity']);
                    $newStock = $item['product']->fresh()->stock_quantity;
                    
                    Log::info('Product stock updated', [
                        'product_id' => $item['product']->id,
                        'product_name' => $item['product']->name,
                        'quantity_sold' => $item['quantity'],
                        'old_stock' => $oldStock,
                        'new_stock' => $newStock
                    ]);

                    // Log stock change - only if ActivityLog class exists and has the method
                    try {
                        if (class_exists('App\Models\ActivityLog') && method_exists(ActivityLog::class, 'logStockChange')) {
                            ActivityLog::logStockChange(
                                $item['product'], 
                                $oldStock, 
                                $newStock, 
                                "Vente #{$sale->sale_number}"
                            );
                        }
                    } catch (\Exception $e) {
                        Log::warning('Could not log stock change', ['error' => $e->getMessage()]);
                    }
                }

                // Log sale creation - only if ActivityLog class exists and has the method
                try {
                    if (class_exists('App\Models\ActivityLog') && method_exists(ActivityLog::class, 'logTransaction')) {
                        $clientName = $sale->client ? $sale->client->full_name : 'Client anonyme';
                        ActivityLog::logTransaction('sale', $sale, 'create', [
                            'client_name' => $clientName,
                            'total_amount' => $sale->total_amount,
                            'payment_method' => $sale->payment_method,
                            'products_count' => count($productData)
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::warning('Could not log sale transaction', ['error' => $e->getMessage()]);
                }

                DB::commit();

                return redirect()->route('sales.show', $sale->id)
                    ->with('success', 'Vente enregistrée avec succès!');
                    
            } catch (\Exception $e) {
                DB::rollback();
                
                Log::error('Sale creation database error', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'request_data' => $request->all()
                ]);
                
                return redirect()->back()
                    ->withErrors(['error' => 'Erreur lors de l\'enregistrement de la vente: ' . $e->getMessage()])
                    ->withInput();
            }
                
        } catch (\Exception $e) {
            Log::error('Sale creation general error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            
            return redirect()->back()
                ->withErrors(['error' => 'Erreur inattendue: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified sale.
     */
    public function show($id)
    {
        $sale = Sale::with(['client', 'user', 'saleItems.product'])->findOrFail($id);
        
        return view('sales.show', compact('sale'));
    }

    /**
     * Show the form for editing the specified sale.
     */
    public function edit($id)
    {
        $sale = Sale::with(['saleItems.product'])->findOrFail($id);
        $clients = Client::active()->orderBy('first_name')->get();
        
        return view('sales.edit', compact('sale', 'clients'));
    }

    /**
     * Update the specified sale in storage.
     */
    public function update(Request $request, $id)
    {
        $sale = Sale::findOrFail($id);
        $oldValues = $sale->toArray();

        $validator = Validator::make($request->all(), [
            'payment_status' => 'required|in:paid,pending,failed',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $sale->payment_status = $request->payment_status;
        $sale->notes = $request->notes;
        $sale->save();

        // Log the update - only if ActivityLog exists
        try {
            if (class_exists('App\Models\ActivityLog') && method_exists(ActivityLog::class, 'logTransaction')) {
                ActivityLog::logTransaction('sale', $sale, 'update', [
                    'changes' => [
                        'payment_status' => ['old' => $oldValues['payment_status'], 'new' => $sale->payment_status],
                        'notes' => ['old' => $oldValues['notes'], 'new' => $sale->notes]
                    ]
                ]);
            }
        } catch (\Exception $e) {
            Log::warning('Could not log sale update', ['error' => $e->getMessage()]);
        }

        return redirect()->route('sales.show', $sale->id)
            ->with('success', 'Vente mise à jour avec succès!');
    }

    /**
     * Remove the specified sale from storage.
     */
    public function destroy($id)
    {
        $sale = Sale::with(['saleItems.product'])->findOrFail($id);
        
        // Vérifier si la vente peut être supprimée
        if ($sale->sale_date < now()->subDays(7)) {
            return redirect()->route('sales.index')
                ->withErrors(['error' => 'Impossible de supprimer une vente de plus de 7 jours.']);
        }

        DB::beginTransaction();
        
        try {
            $saleData = $sale->toArray();
            $restoredProducts = [];
            
            // Restaurer le stock des produits
            foreach ($sale->saleItems as $item) {
                $oldStock = $item->product->stock_quantity;
                $item->product->increment('stock_quantity', $item->quantity);
                $newStock = $item->product->fresh()->stock_quantity;
                
                $restoredProducts[] = [
                    'name' => $item->product->name,
                    'quantity' => $item->quantity
                ];
                
                Log::info('Stock restored for product', [
                    'product_id' => $item->product->id,
                    'product_name' => $item->product->name,
                    'quantity_restored' => $item->quantity,
                    'new_stock' => $newStock
                ]);
            }
            
            // Supprimer les items de vente
            $sale->saleItems()->delete();
            
            // Supprimer la vente
            $sale->delete();
            
            DB::commit();
            
            Log::info('Sale deleted successfully', [
                'sale_id' => $id,
                'sale_number' => $saleData['sale_number'],
                'deleted_by' => auth()->id(),
                'restored_products_count' => count($restoredProducts)
            ]);

            return redirect()->route('sales.index')
                ->with('success', 'Vente supprimée avec succès! Le stock a été restauré.');
                
        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Sale deletion failed', [
                'sale_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('sales.index')
                ->withErrors(['error' => 'Erreur lors de la suppression de la vente. Veuillez réessayer.']);
        }
    }

    /**
     * Get product details for AJAX requests.
     */
    public function getProduct($id)
    {
        $product = Product::find($id);
        
        if (!$product) {
            return response()->json(['error' => 'Produit non trouvé'], 404);
        }

        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'price' => $product->selling_price,
            'stock' => $product->stock_quantity,
            'prescription_required' => $product->prescription_required,
        ]);
    }

    /**
     * Print sale receipt.
     */
    public function print($id)
    {
        $sale = Sale::with(['client', 'user', 'saleItems.product'])->findOrFail($id);
        
        return view('sales.print', compact('sale'));
    }
}