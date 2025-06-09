<?php
// File: app/Traits/LogsActivity.php
// Create this new trait file to centralize activity logging

namespace App\Traits;

use App\Models\ActivityLog;

trait LogsActivity
{
    /**
     * Log a search activity
     */
    protected function logSearch($resource, $filters = [])
    {
        ActivityLog::logActivity(
            'search',
            "Recherche dans {$resource}",
            null,
            null,
            ['filters' => $filters, 'resource' => $resource]
        );
    }

    /**
     * Log a view/consultation activity
     */
    protected function logView($resource, $model = null, $description = null)
    {
        $description = $description ?: "Consultation de {$resource}";
        
        ActivityLog::logActivity(
            'view',
            $description,
            $model,
            null,
            ['resource' => $resource]
        );
    }

    /**
     * Log a create activity
     */
    protected function logCreate($resource, $model, $description = null)
    {
        $description = $description ?: "Création de {$resource}";
        
        ActivityLog::logActivity(
            'create',
            $description,
            $model,
            null,
            $model->toArray()
        );
    }

    /**
     * Log an update activity
     */
    protected function logUpdate($resource, $model, $oldValues, $description = null)
    {
        $description = $description ?: "Modification de {$resource}";
        
        ActivityLog::logActivity(
            'update',
            $description,
            $model,
            $oldValues,
            $model->toArray()
        );
    }

    /**
     * Log a delete activity
     */
    protected function logDelete($resource, $modelData, $description = null)
    {
        $description = $description ?: "Suppression de {$resource}";
        
        ActivityLog::logActivity(
            'delete',
            $description,
            null,
            $modelData,
            null
        );
    }

    /**
     * Log a print activity
     */
    protected function logPrint($resource, $model, $description = null)
    {
        $description = $description ?: "Impression de {$resource}";
        
        ActivityLog::logActivity(
            'print',
            $description,
            $model
        );
    }

    /**
     * Log an export activity
     */
    protected function logExport($resource, $filters = [], $description = null)
    {
        $description = $description ?: "Export de {$resource}";
        
        ActivityLog::logActivity(
            'export',
            $description,
            null,
            null,
            ['filters' => $filters, 'resource' => $resource]
        );
    }

    /**
     * Log a stock-related activity
     */
    protected function logStockActivity($action, $product, $oldStock, $newStock, $quantity, $reason = null)
    {
        $description = match($action) {
            'sale' => "Stock diminué pour vente: {$product->name}",
            'purchase' => "Stock augmenté par réception: {$product->name}",
            'adjustment' => "Ajustement de stock: {$product->name}",
            'prescription' => "Stock diminué pour ordonnance: {$product->name}",
            default => "Modification de stock: {$product->name}"
        };
        
        ActivityLog::logActivity(
            'stock_update',
            $description,
            $product,
            ['stock_quantity' => $oldStock],
            [
                'stock_quantity' => $newStock,
                'quantity_changed' => $quantity,
                'action_type' => $action,
                'reason' => $reason
            ]
        );
    }

    /**
     * Log a validation error
     */
    protected function logValidationError($resource, $errors, $inputData = [])
    {
        ActivityLog::logActivity(
            'validation_error',
            "Erreur de validation lors de l'opération sur {$resource}",
            null,
            null,
            [
                'errors' => $errors,
                'input_data' => $inputData,
                'resource' => $resource
            ]
        );
    }

    /**
     * Log an error
     */
    protected function logError($action, $resource, $errorMessage, $additionalData = [])
    {
        ActivityLog::logActivity(
            'error',
            "Erreur lors de {$action} sur {$resource}: {$errorMessage}",
            null,
            null,
            array_merge(['error_message' => $errorMessage], $additionalData)
        );
    }

    /**
     * Log an unauthorized access attempt
     */
    protected function logUnauthorizedAccess($resource, $attemptedAction)
    {
        ActivityLog::logActivity(
            'unauthorized_access',
            "Tentative d'accès non autorisé à {$resource} pour l'action: {$attemptedAction}",
            null,
            null,
            [
                'resource' => $resource,
                'attempted_action' => $attemptedAction,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]
        );
    }

    /**
     * Log a business logic action (like prescription delivery, sale completion, etc.)
     */
    protected function logBusinessAction($action, $description, $model = null, $additionalData = [])
    {
        ActivityLog::logActivity(
            $action,
            $description,
            $model,
            null,
            $additionalData
        );
    }
}

// File: app/Http/Controllers/EnhancedSaleController.php
// Example of how to use the trait in your controllers

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Client;
use App\Models\Product;
use App\Traits\LogsActivity;

class EnhancedSaleController extends Controller
{
    use LogsActivity;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = Sale::with(['client', 'user', 'saleItems.product']);

        // Apply filters
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

        if ($request->has('payment_status') && $request->payment_status !== '') {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->has('date_from') && !empty($request->date_from)) {
            $query->whereDate('sale_date', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && !empty($request->date_to)) {
            $query->whereDate('sale_date', '<=', $request->date_to);
        }

        if ($request->has('has_prescription') && $request->has_prescription !== '') {
            $query->where('has_prescription', $request->has_prescription === 'yes');
        }

        $sales = $query->latest('sale_date')->paginate(15);
        
        // Log the search/filter activity
        if ($request->hasAny(['search', 'payment_status', 'date_from', 'date_to', 'has_prescription'])) {
            $this->logSearch('ventes', $request->only(['search', 'payment_status', 'date_from', 'date_to', 'has_prescription']));
        } else {
            $this->logView('liste des ventes');
        }
        
        $totalSales = Sale::sum('total_amount');
        $salesCount = Sale::count();
        $averageSale = $salesCount > 0 ? $totalSales / $salesCount : 0;
        
        return view('sales.index', compact('sales', 'totalSales', 'salesCount', 'averageSale'));
    }

    public function create(Request $request)
    {
        $clients = Client::active()->orderBy('first_name')->get();
        $products = Product::where('stock_quantity', '>', 0)->orderBy('name')->get();
        $selectedClientId = $request->get('client_id');
        
        // Log access to sale creation form
        $this->logView('formulaire de nouvelle vente', null, 'Accès au formulaire de création de vente');
        
        return view('sales.create', compact('clients', 'products', 'selectedClientId'));
    }

    public function store(Request $request)
    {
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
        ]);

        if ($validator->fails()) {
            $this->logValidationError('vente', $validator->errors()->toArray(), $request->all());
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Validate stock availability
        $productData = [];
        foreach ($request->products as $item) {
            $product = Product::find($item['id']);
            if (!$product) {
                $this->logError('création', 'vente', "Produit inexistant (ID: {$item['id']})", $request->all());
                return redirect()->back()
                    ->withErrors(['products' => "Produit avec l'ID {$item['id']} introuvable."])
                    ->withInput();
            }
            
            $quantity = (int) $item['quantity'];
            if ($product->stock_quantity < $quantity) {
                $this->logError('création', 'vente', "Stock insuffisant pour {$product->name}", [
                    'product_id' => $product->id,
                    'available_stock' => $product->stock_quantity,
                    'requested_quantity' => $quantity
                ]);
                return redirect()->back()
                    ->withErrors(['products' => "Stock insuffisant pour {$product->name}."])
                    ->withInput();
            }
            
            $productData[] = ['product' => $product, 'quantity' => $quantity];
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
            
            // Calculate totals
            $subtotal = 0;
            foreach ($productData as $item) {
                $subtotal += $item['product']->selling_price * $item['quantity'];
            }
            
            $sale->subtotal = $subtotal;
            $sale->tax_amount = $subtotal * 0.20;
            $sale->total_amount = $subtotal + $sale->tax_amount - $sale->discount_amount;
            $sale->save();

            // Log sale creation with detailed info
            $clientName = $sale->client ? $sale->client->full_name : 'Client anonyme';
            $this->logCreate(
                'vente', 
                $sale, 
                "Vente créée: {$sale->sale_number} pour {$clientName} - Montant: {$sale->total_amount}€"
            );

            // Create sale items and update stock
            foreach ($productData as $item) {
                $saleItem = new SaleItem();
                $saleItem->sale_id = $sale->id;
                $saleItem->product_id = $item['product']->id;
                $saleItem->quantity = $item['quantity'];
                $saleItem->unit_price = $item['product']->selling_price;
                $saleItem->total_price = $item['product']->selling_price * $item['quantity'];
                $saleItem->save();

                // Update stock and log it
                $oldStock = $item['product']->stock_quantity;
                $item['product']->decrement('stock_quantity', $item['quantity']);
                $newStock = $item['product']->fresh()->stock_quantity;
                
                $this->logStockActivity(
                    'sale', 
                    $item['product'], 
                    $oldStock, 
                    $newStock, 
                    $item['quantity'], 
                    "Vente #{$sale->sale_number}"
                );
            }

            DB::commit();
            return redirect()->route('sales.show', $sale->id)
                ->with('success', 'Vente enregistrée avec succès!');
                
        } catch (\Exception $e) {
            DB::rollback();
            $this->logError('création', 'vente', $e->getMessage(), [
                'request_data' => $request->all(),
                'error_trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()
                ->withErrors(['error' => 'Erreur lors de l\'enregistrement de la vente.'])
                ->withInput();
        }
    }

    public function show($id)
    {
        $sale = Sale::with(['client', 'user', 'saleItems.product'])->findOrFail($id);
        
        $this->logView('vente', $sale, "Consultation de la vente: {$sale->sale_number}");
        
        return view('sales.show', compact('sale'));
    }

    public function edit($id)
    {
        $sale = Sale::with(['saleItems.product'])->findOrFail($id);
        $clients = Client::active()->orderBy('first_name')->get();
        
        $this->logView('formulaire de modification de vente', $sale, "Accès au formulaire de modification de la vente: {$sale->sale_number}");
        
        return view('sales.edit', compact('sale', 'clients'));
    }

    public function update(Request $request, $id)
    {
        $sale = Sale::findOrFail($id);
        $oldValues = $sale->toArray();

        $validator = Validator::make($request->all(), [
            'payment_status' => 'required|in:paid,pending,failed',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            $this->logValidationError('modification de vente', $validator->errors()->toArray());
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $sale->payment_status = $request->payment_status;
        $sale->notes = $request->notes;
        $sale->save();

        $this->logUpdate('vente', $sale, $oldValues, "Vente modifiée: {$sale->sale_number}");

        return redirect()->route('sales.show', $sale->id)
            ->with('success', 'Vente mise à jour avec succès!');
    }

    public function destroy($id)
    {
        $sale = Sale::with(['saleItems.product'])->findOrFail($id);
        
        if ($sale->sale_date < now()->subDays(7)) {
            $this->logUnauthorizedAccess('vente', 'suppression d\'une vente de plus de 7 jours');
            return redirect()->route('sales.index')
                ->withErrors(['error' => 'Impossible de supprimer une vente de plus de 7 jours.']);
        }

        DB::beginTransaction();
        
        try {
            $saleData = $sale->toArray();
            $restoredStock = [];
            
            // Restore stock
            foreach ($sale->saleItems as $item) {
                $oldStock = $item->product->stock_quantity;
                $item->product->increment('stock_quantity', $item->quantity);
                $newStock = $item->product->fresh()->stock_quantity;
                
                $this->logStockActivity(
                    'adjustment', 
                    $item->product, 
                    $oldStock, 
                    $newStock, 
                    $item->quantity, 
                    "Suppression de vente #{$sale->sale_number}"
                );
                
                $restoredStock[] = [
                    'product_name' => $item->product->name,
                    'quantity_restored' => $item->quantity
                ];
            }
            
            $sale->saleItems()->delete();
            $sale->delete();
            
            $this->logDelete('vente', $saleData, "Vente supprimée: {$saleData['sale_number']} - Stock restauré pour " . count($restoredStock) . " produit(s)");
            
            DB::commit();
            return redirect()->route('sales.index')
                ->with('success', 'Vente supprimée avec succès! Le stock a été restauré.');
                
        } catch (\Exception $e) {
            DB::rollback();
            $this->logError('suppression', 'vente', $e->getMessage(), ['sale_id' => $id]);
            return redirect()->route('sales.index')
                ->withErrors(['error' => 'Erreur lors de la suppression de la vente.']);
        }
    }

    public function print($id)
    {
        $sale = Sale::with(['client', 'user', 'saleItems.product'])->findOrFail($id);
        
        $this->logPrint('facture de vente', $sale, "Impression de la facture: {$sale->sale_number}");
        
        return view('sales.print', compact('sale'));
    }

    public function getProduct($id)
    {
        $product = Product::find($id);
        
        if (!$product) {
            $this->logError('consultation', 'produit', "Produit inexistant (ID: {$id})");
            return response()->json(['error' => 'Produit non trouvé'], 404);
        }

        $this->logView('détails produit', $product, "Consultation des détails du produit: {$product->name} (pour vente)");

        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'price' => $product->selling_price,
            'stock' => $product->stock_quantity,
            'prescription_required' => $product->prescription_required,
        ]);
    }
}

// File: app/Http/Controllers/EnhancedProductController.php
// Enhanced product controller with comprehensive logging

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use App\Traits\LogsActivity;

class EnhancedProductController extends Controller
{
    use LogsActivity;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = Product::with(['category', 'supplier']);

        // Apply filters and log them
        $appliedFilters = [];
        
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $appliedFilters['search'] = $search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->has('category') && $request->category != '') {
            $appliedFilters['category'] = $request->category;
            $query->where('category_id', $request->category);
        }

        if ($request->has('supplier') && $request->supplier != '') {
            $appliedFilters['supplier'] = $request->supplier;
            $query->where('supplier_id', $request->supplier);
        }

        if ($request->has('stock_status') && !empty($request->stock_status)) {
            $appliedFilters['stock_status'] = $request->stock_status;
            if ($request->stock_status == 'low') {
                $query->whereColumn('stock_quantity', '<=', 'stock_threshold');
            } elseif ($request->stock_status == 'out') {
                $query->where('stock_quantity', '<=', 0);
            }
        }

        $products = $query->paginate(10);
        $categories = Category::all();
        $suppliers = Supplier::all();
        
        // Log the activity
        if (!empty($appliedFilters)) {
            $this->logSearch('inventaire', $appliedFilters);
        } else {
            $this->logView('inventaire');
        }
        
        return view('inventory.index', compact('products', 'categories', 'suppliers'));
    }

    public function create(Request $request)
    {
        $categories = Category::all();
        $suppliers = Supplier::where('active', true)->get();
        $selectedSupplierId = $request->get('supplier_id');
        
        $this->logView('formulaire de nouveau produit', null, 'Accès au formulaire de création de produit');
        
        return view('inventory.create', compact('categories', 'suppliers', 'selectedSupplierId'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'stock_threshold' => 'required|integer|min:0',
            'barcode' => 'nullable|string|unique:products',
            'description' => 'nullable|string',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'expiry_date' => 'nullable|date|after:today',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            $this->logValidationError('création de produit', $validator->errors()->toArray(), $request->all());
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $product = new Product();
            $product->fill($request->except('image', 'prescription_required'));
            $product->prescription_required = $request->has('prescription_required');

            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('products', 'public');
                $product->image_path = $imagePath;
            }

            $product->save();

            $this->logCreate('produit', $product, "Produit ajouté à l'inventaire: {$product->name}");

            return redirect()->route('inventory.index')
                ->with('success', 'Produit ajouté avec succès!');
                
        } catch (\Exception $e) {
            $this->logError('création', 'produit', $e->getMessage(), $request->all());
            return redirect()->back()
                ->withErrors(['error' => 'Erreur lors de la création du produit.'])
                ->withInput();
        }
    }

    public function show($id)
    {
        $product = Product::with(['category', 'supplier'])->findOrFail($id);
        
        $this->logView('produit', $product, "Consultation du produit: {$product->name}");
        
        return view('inventory.show', compact('product'));
    }

    public function edit($id)
    {
        $product = Product::findOrFail($id);
        $categories = Category::all();
        $suppliers = Supplier::all();
        
        $this->logView('formulaire de modification de produit', $product, "Accès au formulaire de modification du produit: {$product->name}");
        
        return view('inventory.edit', compact('product', 'categories', 'suppliers'));
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $oldValues = $product->toArray();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'stock_threshold' => 'required|integer|min:0',
            'barcode' => 'nullable|string|unique:products,barcode,'.$id,
            'description' => 'nullable|string',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'expiry_date' => 'nullable|date',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            $this->logValidationError('modification de produit', $validator->errors()->toArray());
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            // Check if stock quantity changed for logging
            $stockChanged = $oldValues['stock_quantity'] != $request->stock_quantity;
            
            $product->fill($request->except('image', 'prescription_required'));
            $product->prescription_required = $request->has('prescription_required');

            if ($request->hasFile('image')) {
                if ($product->image_path) {
                    Storage::disk('public')->delete($product->image_path);
                }
                $imagePath = $request->file('image')->store('products', 'public');
                $product->image_path = $imagePath;
            }

            $product->save();

            $this->logUpdate('produit', $product, $oldValues, "Produit modifié: {$product->name}");

            // Log stock change separately if it occurred
            if ($stockChanged) {
                $this->logStockActivity(
                    'adjustment',
                    $product,
                    $oldValues['stock_quantity'],
                    $product->stock_quantity,
                    $product->stock_quantity - $oldValues['stock_quantity'],
                    'Ajustement manuel via modification de produit'
                );
            }

            return redirect()->route('inventory.index')
                ->with('success', 'Produit mis à jour avec succès!');
                
        } catch (\Exception $e) {
            $this->logError('modification', 'produit', $e->getMessage(), ['product_id' => $id]);
            return redirect()->back()
                ->withErrors(['error' => 'Erreur lors de la modification du produit.'])
                ->withInput();
        }
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $productData = $product->toArray();
        
        try {
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }
            
            $product->delete();

            $this->logDelete('produit', $productData, "Produit supprimé de l'inventaire: {$productData['name']}");

            return redirect()->route('inventory.index')
                ->with('success', 'Produit supprimé avec succès!');
                
        } catch (\Exception $e) {
            $this->logError('suppression', 'produit', $e->getMessage(), ['product_id' => $id]);
            return redirect()->route('inventory.index')
                ->withErrors(['error' => 'Erreur lors de la suppression du produit.']);
        }
    }
}