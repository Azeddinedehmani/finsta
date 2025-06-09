<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\ActivityLog;

class PurchaseController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin'); // Seuls les admins peuvent gérer les achats
    }

    /**
     * Display a listing of the purchases.
     */
    public function index(Request $request)
    {
        $query = Purchase::with(['supplier', 'user', 'purchaseItems.product']);

        // Search functionality
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->where('purchase_number', 'like', "%{$search}%")
                  ->orWhereHas('supplier', function($supplierQuery) use ($search) {
                      $supplierQuery->where('name', 'like', "%{$search}%")
                                   ->orWhere('contact_person', 'like', "%{$search}%");
                  })
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        // Filter by status - Fixed the logic
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by supplier
        if ($request->filled('supplier')) {
            $query->where('supplier_id', $request->supplier);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('order_date', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('order_date', '<=', $request->date_to);
        }

        $purchases = $query->latest('order_date')->paginate(15);
        
        // Calculate summary statistics
        $totalPurchases = Purchase::sum('total_amount');
        $pendingCount = Purchase::where('status', 'pending')->count();
        $overdueCount = Purchase::where('status', 'pending')
                              ->where('expected_date', '<', now())
                              ->count();
        $receivedCount = Purchase::where('status', 'received')->count();
        
        $suppliers = Supplier::where('active', true)->orderBy('name')->get();
        
        return view('purchases.index', compact(
            'purchases', 'totalPurchases', 'pendingCount', 'overdueCount', 'receivedCount', 'suppliers'
        ));
    }

    /**
     * Show the form for creating a new purchase.
     */
    public function create(Request $request)
    {
        $suppliers = Supplier::where('active', true)->orderBy('name')->get();
        $products = Product::orderBy('name')->get();
        $selectedSupplierId = $request->get('supplier_id');
        
        return view('purchases.create', compact('suppliers', 'products', 'selectedSupplierId'));
    }

    /**
     * Store a newly created purchase in storage.
     */
    public function store(Request $request)
    {
        Log::info('Purchase creation attempt', [
            'user_id' => auth()->id(),
            'request_data' => $request->all()
        ]);

        // Validation corrigée et complète
        $validator = Validator::make($request->all(), [
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'expected_date' => 'nullable|date|after_or_equal:order_date',
            'notes' => 'nullable|string|max:1000',
            'products' => 'required|array|min:1',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.price' => 'required|numeric|min:0',
        ], [
            'supplier_id.required' => 'Veuillez sélectionner un fournisseur.',
            'supplier_id.exists' => 'Le fournisseur sélectionné n\'existe pas.',
            'order_date.required' => 'La date de commande est requise.',
            'order_date.date' => 'La date de commande doit être une date valide.',
            'expected_date.date' => 'La date prévue doit être une date valide.',
            'expected_date.after_or_equal' => 'La date prévue doit être égale ou postérieure à la date de commande.',
            'notes.max' => 'Les notes ne peuvent pas dépasser 1000 caractères.',
            'products.required' => 'Veuillez ajouter au moins un produit à la commande.',
            'products.array' => 'Les données des produits sont invalides.',
            'products.min' => 'Veuillez ajouter au moins un produit à la commande.',
            'products.*.id.required' => 'ID produit manquant.',
            'products.*.id.exists' => 'Un des produits sélectionnés n\'existe pas.',
            'products.*.quantity.required' => 'Quantité manquante pour un produit.',
            'products.*.quantity.integer' => 'La quantité doit être un nombre entier.',
            'products.*.quantity.min' => 'La quantité doit être d\'au moins 1.',
            'products.*.price.required' => 'Prix manquant pour un produit.',
            'products.*.price.numeric' => 'Le prix doit être un nombre.',
            'products.*.price.min' => 'Le prix doit être positif.',
        ]);

        if ($validator->fails()) {
            Log::warning('Purchase creation validation failed', [
                'errors' => $validator->errors()->toArray(),
                'request_data' => $request->all()
            ]);
            
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Validation supplémentaire des produits
        $productData = [];
        $errors = [];

        try {
            foreach ($request->products as $index => $item) {
                $product = Product::find($item['id']);
                if (!$product) {
                    $errors[] = "Le produit avec l'ID {$item['id']} n'existe pas.";
                    continue;
                }
                
                $quantity = (int) $item['quantity'];
                $price = (float) $item['price'];
                
                if ($quantity <= 0) {
                    $errors[] = "La quantité pour {$product->name} doit être supérieure à 0.";
                    continue;
                }
                
                if ($price < 0) {
                    $errors[] = "Le prix pour {$product->name} ne peut pas être négatif.";
                    continue;
                }
                
                $productData[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'price' => $price
                ];
            }
        } catch (\Exception $e) {
            Log::error('Error processing products in purchase', [
                'error' => $e->getMessage(),
                'products' => $request->products
            ]);
            $errors[] = "Erreur lors du traitement des produits.";
        }

        if (!empty($errors)) {
            return redirect()->back()
                ->withErrors(['products' => implode(' ', $errors)])
                ->withInput();
        }

        if (empty($productData)) {
            return redirect()->back()
                ->withErrors(['products' => 'Aucun produit valide trouvé dans la commande.'])
                ->withInput();
        }

        DB::beginTransaction();
        
        try {
            // Vérifier que le fournisseur existe et est actif
            $supplier = Supplier::where('id', $request->supplier_id)
                               ->where('active', true)
                               ->first();
            
            if (!$supplier) {
                throw new \Exception('Le fournisseur sélectionné n\'est pas disponible.');
            }

            // Create purchase
            $purchase = new Purchase();
            $purchase->supplier_id = $request->supplier_id;
            $purchase->user_id = auth()->id();
            $purchase->order_date = $request->order_date;
            $purchase->expected_date = $request->expected_date;
            $purchase->notes = $request->notes;
            $purchase->status = 'pending';
            
            // Calculate totals
            $subtotal = 0;
            foreach ($productData as $item) {
                $subtotal += $item['quantity'] * $item['price'];
            }
            
            $purchase->subtotal = $subtotal;
            $purchase->tax_amount = $subtotal * 0.20; // 20% tax
            $purchase->total_amount = $subtotal + $purchase->tax_amount;
            
            $purchase->save();

            Log::info('Purchase created successfully', [
                'purchase_id' => $purchase->id,
                'purchase_number' => $purchase->purchase_number,
                'total_amount' => $purchase->total_amount
            ]);

            // Create purchase items
            foreach ($productData as $item) {
                $purchaseItem = new PurchaseItem();
                $purchaseItem->purchase_id = $purchase->id;
                $purchaseItem->product_id = $item['product']->id;
                $purchaseItem->quantity_ordered = $item['quantity'];
                $purchaseItem->quantity_received = 0;
                $purchaseItem->unit_price = $item['price'];
                $purchaseItem->total_price = $item['quantity'] * $item['price'];
                $purchaseItem->save();
                
                Log::info('Purchase item created', [
                    'purchase_item_id' => $purchaseItem->id,
                    'product_id' => $item['product']->id,
                    'product_name' => $item['product']->name,
                    'quantity' => $item['quantity'],
                    'price' => $item['price']
                ]);
            }

            // Log purchase creation
            try {
                ActivityLog::logActivity(
                    'create',
                    "Commande d'achat créée: {$purchase->purchase_number} - Fournisseur: {$supplier->name} - Montant: {$purchase->total_amount}€",
                    $purchase,
                    null,
                    [
                        'supplier_name' => $supplier->name,
                        'total_amount' => $purchase->total_amount,
                        'products_count' => count($productData),
                        'order_date' => $purchase->order_date,
                        'expected_date' => $purchase->expected_date
                    ]
                );
            } catch (\Exception $e) {
                Log::warning('Failed to log purchase creation activity', [
                    'error' => $e->getMessage(),
                    'purchase_id' => $purchase->id
                ]);
            }

            DB::commit();

            return redirect()->route('purchases.show', $purchase->id)
                ->with('success', 'Commande d\'achat créée avec succès!');
                
        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Purchase creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['products']),
                'products_count' => count($productData ?? [])
            ]);
            
            // Log error activity
            try {
                ActivityLog::logActivity(
                    'error',
                    "Erreur lors de la création d'une commande d'achat: " . $e->getMessage(),
                    null,
                    null,
                    [
                        'error_details' => $e->getMessage(),
                        'supplier_id' => $request->supplier_id,
                        'products_count' => count($productData ?? [])
                    ]
                );
            } catch (\Exception $logError) {
                Log::warning('Failed to log error activity', [
                    'log_error' => $logError->getMessage()
                ]);
            }
            
            $errorMessage = 'Erreur lors de la création de la commande. Veuillez réessayer.';
            
            if (str_contains($e->getMessage(), 'fournisseur')) {
                $errorMessage = 'Problème avec le fournisseur sélectionné.';
            } elseif (str_contains($e->getMessage(), 'produit')) {
                $errorMessage = 'Problème avec un ou plusieurs produits de la commande.';
            } elseif (str_contains($e->getMessage(), 'database') || str_contains($e->getMessage(), 'SQL')) {
                $errorMessage = 'Erreur de base de données. Veuillez réessayer.';
            }
            
            return redirect()->back()
                ->withErrors(['error' => $errorMessage])
                ->withInput();
        }
    }

    /**
     * Display the specified purchase.
     */
    public function show($id)
    {
        $purchase = Purchase::with(['supplier', 'user', 'receivedBy', 'purchaseItems.product'])->findOrFail($id);
        
        return view('purchases.show', compact('purchase'));
    }

    /**
     * Show the form for editing the specified purchase.
     */
    public function edit($id)
    {
        $purchase = Purchase::with(['purchaseItems.product'])->findOrFail($id);
        
        if ($purchase->status === 'received') {
            return redirect()->route('purchases.show', $purchase->id)
                ->withErrors(['error' => 'Cette commande a été reçue et ne peut plus être modifiée.']);
        }
        
        $suppliers = Supplier::where('active', true)->orderBy('name')->get();
        
        return view('purchases.edit', compact('purchase', 'suppliers'));
    }

    /**
     * Update the specified purchase in storage.
     */
    public function update(Request $request, $id)
    {
        $purchase = Purchase::findOrFail($id);
        $oldValues = $purchase->toArray();
        
        if ($purchase->status === 'received') {
            return redirect()->route('purchases.show', $purchase->id)
                ->withErrors(['error' => 'Cette commande a été reçue et ne peut plus être modifiée.']);
        }

        $validator = Validator::make($request->all(), [
            'expected_date' => 'nullable|date|after_or_equal:order_date',
            'notes' => 'nullable|string|max:1000',
            'status' => 'required|in:pending,cancelled',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $purchase->update($request->only(['expected_date', 'notes', 'status']));

            // Log purchase update
            ActivityLog::logActivity(
                'update',
                "Commande d'achat modifiée: {$purchase->purchase_number}",
                $purchase,
                $oldValues,
                $purchase->toArray()
            );

            return redirect()->route('purchases.show', $purchase->id)
                ->with('success', 'Commande mise à jour avec succès!');
                
        } catch (\Exception $e) {
            Log::error('Purchase update failed', [
                'purchase_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()
                ->withErrors(['error' => 'Erreur lors de la mise à jour de la commande.'])
                ->withInput();
        }
    }

    /**
     * Remove the specified purchase from storage.
     */
    public function destroy($id)
    {
        try {
            $purchase = Purchase::with(['purchaseItems'])->findOrFail($id);
            
            // Check if purchase can be deleted
            if ($purchase->status === 'received') {
                return redirect()->route('purchases.index')
                    ->withErrors(['error' => 'Impossible de supprimer une commande reçue.']);
            }
            
            if ($purchase->status === 'partially_received') {
                return redirect()->route('purchases.index')
                    ->withErrors(['error' => 'Impossible de supprimer une commande partiellement reçue.']);
            }
            
            DB::beginTransaction();
            
            // Store purchase info for logging before deletion
            $purchaseInfo = [
                'purchase_number' => $purchase->purchase_number,
                'supplier_name' => $purchase->supplier->name,
                'total_amount' => $purchase->total_amount,
                'status' => $purchase->status
            ];
            
            // Delete purchase items first
            $purchase->purchaseItems()->delete();
            
            // Delete the purchase
            $purchase->delete();
            
            // Log deletion
            ActivityLog::logActivity(
                'delete',
                "Commande d'achat supprimée: {$purchaseInfo['purchase_number']} - Fournisseur: {$purchaseInfo['supplier_name']} - Montant: {$purchaseInfo['total_amount']}€",
                null,
                $purchaseInfo,
                null
            );
            
            DB::commit();
            
            return redirect()->route('purchases.index')
                ->with('success', 'Commande supprimée avec succès.');
                
        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Purchase deletion failed', [
                'purchase_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('purchases.index')
                ->withErrors(['error' => 'Erreur lors de la suppression de la commande.']);
        }
    }

    /**
     * Show the form for receiving a purchase.
     */
    public function receive($id)
    {
        $purchase = Purchase::with(['supplier', 'purchaseItems.product'])->findOrFail($id);
        
        if ($purchase->status === 'received') {
            return redirect()->route('purchases.show', $purchase->id)
                ->withErrors(['error' => 'Cette commande a déjà été complètement reçue.']);
        }
        
        if ($purchase->status === 'cancelled') {
            return redirect()->route('purchases.show', $purchase->id)
                ->withErrors(['error' => 'Cette commande a été annulée.']);
        }
        
        return view('purchases.receive', compact('purchase'));
    }

    /**
     * Process the reception of a purchase.
     */
    public function processReception(Request $request, $id)
    {
        $purchase = Purchase::with(['purchaseItems.product'])->findOrFail($id);
        
        if ($purchase->status === 'received') {
            return redirect()->route('purchases.show', $purchase->id)
                ->withErrors(['error' => 'Cette commande a déjà été reçue.']);
        }

        $validator = Validator::make($request->all(), [
            'items' => 'required|array',
            'items.*.item_id' => 'required|exists:purchase_items,id',
            'items.*.quantity_received' => 'required|integer|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        
        try {
            $deliveredItems = [];
            $stockChanges = [];
            
            foreach ($request->items as $itemData) {
                $purchaseItem = PurchaseItem::find($itemData['item_id']);
                if (!$purchaseItem || $purchaseItem->purchase_id != $purchase->id) {
                    throw new \Exception("Item de commande invalide.");
                }
                
                $quantityToReceive = (int) $itemData['quantity_received'];
                $maxQuantity = $purchaseItem->quantity_ordered - $purchaseItem->quantity_received;
                
                if ($quantityToReceive > $maxQuantity) {
                    throw new \Exception("Quantité trop élevée pour {$purchaseItem->product->name}. Maximum: {$maxQuantity}");
                }
                
                if ($quantityToReceive > 0) {
                    $oldStock = $purchaseItem->product->stock_quantity;
                    $purchaseItem->quantity_received += $quantityToReceive;
                    $purchaseItem->save();
                    
                    $purchaseItem->product->increment('stock_quantity', $quantityToReceive);
                    $newStock = $purchaseItem->product->fresh()->stock_quantity;
                    
                    // Log stock change
                    ActivityLog::logActivity(
                        'stock_update',
                        "Stock augmenté pour {$purchaseItem->product->name}: {$oldStock} → {$newStock} (+{$quantityToReceive}) - Réception commande #{$purchase->purchase_number}",
                        $purchaseItem->product,
                        ['stock_quantity' => $oldStock],
                        ['stock_quantity' => $newStock, 'change' => $quantityToReceive, 'reason' => "Réception commande #{$purchase->purchase_number}"]
                    );
                    
                    $deliveredItems[] = [
                        'product_name' => $purchaseItem->product->name,
                        'quantity' => $quantityToReceive
                    ];
                    
                    $stockChanges[] = [
                        'product_id' => $purchaseItem->product->id,
                        'old_stock' => $oldStock,
                        'new_stock' => $newStock
                    ];
                }
            }
            
            if ($request->notes) {
                $purchase->notes = $request->notes;
            }
            
            // Update purchase status
            $purchase->updateStatus();
            
            if ($purchase->status === 'received') {
                $purchase->received_date = now();
                $purchase->received_by = auth()->id();
                $purchase->save();
            }
            
            // Log reception
            ActivityLog::logActivity(
                'receive',
                "Réception de la commande: {$purchase->purchase_number} - " . count($deliveredItems) . " produit(s) reçu(s)",
                $purchase,
                null,
                [
                    'received_items' => $deliveredItems,
                    'stock_changes' => $stockChanges,
                    'notes' => $request->notes,
                    'received_by' => auth()->user()->name
                ]
            );
            
            DB::commit();

            return redirect()->route('purchases.show', $purchase->id)
                ->with('success', 'Réception enregistrée avec succès!');
                
        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Purchase reception failed', [
                'purchase_id' => $id,
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);
            
            ActivityLog::logActivity(
                'error',
                "Erreur lors de la réception de la commande #{$purchase->purchase_number}: " . $e->getMessage(),
                $purchase
            );
            
            return redirect()->back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Cancel a purchase.
     */
    public function cancel($id)
    {
        try {
            $purchase = Purchase::findOrFail($id);
            
            if ($purchase->status === 'received') {
                return redirect()->route('purchases.show', $purchase->id)
                    ->withErrors(['error' => 'Impossible d\'annuler une commande déjà reçue.']);
            }
            
            $oldStatus = $purchase->status;
            $purchase->status = 'cancelled';
            $purchase->save();
            
            // Log cancellation
            ActivityLog::logActivity(
                'cancel',
                "Commande d'achat annulée: {$purchase->purchase_number}",
                $purchase,
                ['status' => $oldStatus],
                ['status' => 'cancelled']
            );
            
            return redirect()->route('purchases.show', $purchase->id)
                ->with('success', 'Commande annulée avec succès.');
                
        } catch (\Exception $e) {
            Log::error('Purchase cancellation failed', [
                'purchase_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()
                ->withErrors(['error' => 'Erreur lors de l\'annulation de la commande.']);
        }
    }

    /**
     * Print purchase order.
     */
    public function print($id)
    {
        $purchase = Purchase::with(['supplier', 'user', 'purchaseItems.product'])->findOrFail($id);
        
        // Log print action
        ActivityLog::logActivity(
            'print',
            "Impression du bon de commande: {$purchase->purchase_number}",
            $purchase
        );
        
        return view('purchases.print', compact('purchase'));
    }
}