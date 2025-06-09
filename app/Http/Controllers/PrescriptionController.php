<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Client;
use App\Models\Product;
use App\Models\ActivityLog;

class PrescriptionController extends Controller
{
    public function __construct() 
    { 
        $this->middleware('auth'); 
    }

    public function index(Request $request)
    {
        $query = Prescription::with(['client', 'createdBy', 'prescriptionItems.product']);

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('prescription_number', 'like', "%{$search}%")
                  ->orWhere('doctor_name', 'like', "%{$search}%")
                  ->orWhereHas('client', function($clientQuery) use ($search) {
                      $clientQuery->where('first_name', 'like', "%{$search}%")
                                 ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        if ($request->has('date_from') && !empty($request->date_from)) {
            $query->whereDate('prescription_date', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && !empty($request->date_to)) {
            $query->whereDate('prescription_date', '<=', $request->date_to);
        }

        if ($request->has('expiry_filter') && $request->expiry_filter !== '') {
            if ($request->expiry_filter === 'expired') {
                $query->expired();
            } elseif ($request->expiry_filter === 'expiring_soon') {
                $query->active()->where('expiry_date', '<=', now()->addDays(7));
            }
        }

        $prescriptions = $query->latest('prescription_date')->paginate(15);
        
        $totalPrescriptions = $query->count();
        $pendingCount = Prescription::pending()->count();
        $expiredCount = Prescription::expired()->count();
        $expiringCount = Prescription::active()->where('expiry_date', '<=', now()->addDays(7))->count();
        
        return view('prescriptions.index', compact(
            'prescriptions', 'totalPrescriptions', 'pendingCount', 'expiredCount', 'expiringCount'
        ));
    }

    public function create()
    {
        $clients = Client::active()->orderBy('first_name')->get();
        $products = Product::orderBy('name')->get();
        return view('prescriptions.create', compact('clients', 'products'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id',
            'doctor_name' => 'required|string|max:255',
            'doctor_phone' => 'nullable|string|max:20',
            'doctor_speciality' => 'nullable|string|max:255',
            'prescription_date' => 'required|date',
            'expiry_date' => 'required|date|after:prescription_date',
            'medical_notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity_prescribed' => 'required|integer|min:1',
            'items.*.dosage_instructions' => 'required|string|max:255',
            'items.*.duration_days' => 'nullable|integer|min:1',
            'items.*.instructions' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        
        try {
            $prescription = new Prescription();
            $prescription->fill($request->except('items'));
            $prescription->created_by = auth()->id();
            $prescription->save();

            foreach ($request->items as $itemData) {
                PrescriptionItem::create([
                    'prescription_id' => $prescription->id,
                    'product_id' => $itemData['product_id'],
                    'quantity_prescribed' => $itemData['quantity_prescribed'],
                    'dosage_instructions' => $itemData['dosage_instructions'],
                    'duration_days' => $itemData['duration_days'] ?? null,
                    'instructions' => $itemData['instructions'] ?? null,
                    'is_substitutable' => isset($itemData['is_substitutable']),
                ]);
            }

            // Log activity
            ActivityLog::logActivity(
                'create',
                "Ordonnance créée: {$prescription->prescription_number} pour {$prescription->client->full_name}",
                $prescription,
                null,
                $prescription->toArray()
            );

            DB::commit();
            return redirect()->route('prescriptions.show', $prescription->id)
                ->with('success', 'Ordonnance créée avec succès!');
                
        } catch (\Exception $e) {
            DB::rollback();
            
            ActivityLog::logActivity(
                'error',
                "Erreur lors de la création de l'ordonnance: " . $e->getMessage(),
                null,
                null,
                ['error_details' => $e->getMessage(), 'request_data' => $request->except('items')]
            );
            
            return redirect()->back()
                ->withErrors(['error' => 'Erreur lors de la création de l\'ordonnance: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function show($id)
    {
        $prescription = Prescription::with(['client', 'createdBy', 'deliveredBy', 'prescriptionItems.product'])->findOrFail($id);
        return view('prescriptions.show', compact('prescription'));
    }

    public function edit($id)
    {
        // Load prescription with prescription items and their products (including soft-deleted ones)
        $prescription = Prescription::with([
            'prescriptionItems' => function($query) {
                $query->with(['product' => function($productQuery) {
                    $productQuery->withTrashed(); // Include soft-deleted products
                }]);
            },
            'client'
        ])->findOrFail($id);
        
        if (in_array($prescription->status, ['completed', 'expired'])) {
            return redirect()->route('prescriptions.show', $prescription->id)
                ->withErrors(['error' => 'Cette ordonnance ne peut plus être modifiée.']);
        }
        
        $clients = Client::active()->orderBy('first_name')->get();
        $products = Product::orderBy('name')->get();
        
        // Check for missing products and log warning
        $missingProducts = $prescription->prescriptionItems->where('product', null);
        if ($missingProducts->count() > 0) {
            \Log::warning('Prescription ' . $prescription->prescription_number . ' has missing products', [
                'prescription_id' => $prescription->id,
                'missing_product_ids' => $missingProducts->pluck('product_id')->toArray()
            ]);
        }
        
        return view('prescriptions.edit', compact('prescription', 'clients', 'products'));
    }

    public function update(Request $request, $id)
    {
        $prescription = Prescription::findOrFail($id);
        $oldValues = $prescription->toArray();
        
        if (in_array($prescription->status, ['completed', 'expired'])) {
            return redirect()->route('prescriptions.show', $prescription->id)
                ->withErrors(['error' => 'Cette ordonnance ne peut plus être modifiée.']);
        }

        $validator = Validator::make($request->all(), [
            'doctor_name' => 'required|string|max:255',
            'doctor_phone' => 'nullable|string|max:20',
            'doctor_speciality' => 'nullable|string|max:255',
            'prescription_date' => 'required|date',
            'expiry_date' => 'required|date|after:prescription_date',
            'medical_notes' => 'nullable|string',
            'pharmacist_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $prescription->update($request->only([
            'doctor_name', 'doctor_phone', 'doctor_speciality',
            'prescription_date', 'expiry_date', 'medical_notes', 'pharmacist_notes'
        ]));

        // Log the update
        ActivityLog::logActivity(
            'update',
            "Ordonnance modifiée: {$prescription->prescription_number}",
            $prescription,
            $oldValues,
            $prescription->toArray()
        );

        return redirect()->route('prescriptions.show', $prescription->id)
            ->with('success', 'Ordonnance mise à jour avec succès!');
    }

    /**
     * Remove the specified prescription from storage - IMPROVED WITH PROPER PERMISSION CHECKS
     */
    public function destroy($id)
    {
        try {
            $prescription = Prescription::with(['client', 'prescriptionItems.product'])->findOrFail($id);
            $prescriptionData = $prescription->toArray();
            $prescriptionNumber = $prescription->prescription_number;
            $clientName = $prescription->client ? $prescription->client->full_name : 'Client supprimé';
            
            // IMPROVED PERMISSION CHECK - Use User model method
            if (!auth()->user()->canDeletePrescription($prescription)) {
                $reason = auth()->user()->getDeletionRestrictionReason($prescription);
                
                ActivityLog::logActivity(
                    'unauthorized_access',
                    "Tentative de suppression non autorisée de l'ordonnance {$prescriptionNumber} par " . auth()->user()->name . ": {$reason}",
                    $prescription,
                    null,
                    [
                        'attempted_by' => auth()->user()->name, 
                        'user_role' => auth()->user()->role,
                        'prescription_status' => $prescription->status,
                        'is_expired' => $prescription->isExpired(),
                        'restriction_reason' => $reason
                    ]
                );
                
                return redirect()->route('prescriptions.index')
                    ->withErrors(['error' => $reason ?: 'Vous n\'avez pas l\'autorisation de supprimer cette ordonnance.']);
            }

            // Count associated data
            $itemsCount = $prescription->prescriptionItems()->count();

            DB::beginTransaction();
            
            try {
                $deletionSummary = [
                    'prescription_number' => $prescriptionNumber,
                    'prescription_data' => $prescriptionData,
                    'client_name' => $clientName,
                    'items_deleted' => 0,
                    'stock_restored' => [],
                    'deleted_by' => auth()->user()->name,
                    'user_role' => auth()->user()->role,
                    'deletion_date' => now()->toDateTimeString()
                ];

                // 1. HANDLE PRESCRIPTION ITEMS - RESTORE STOCK IF DELIVERED
                if ($itemsCount > 0) {
                    $prescriptionItems = $prescription->prescriptionItems()->with('product')->get();
                    
                    foreach ($prescriptionItems as $item) {
                        // Restore stock for delivered quantities (if product still exists)
                        if ($item->quantity_delivered > 0 && $item->product) {
                            $oldStock = $item->product->stock_quantity;
                            $item->product->increment('stock_quantity', $item->quantity_delivered);
                            
                            $deletionSummary['stock_restored'][] = [
                                'product_name' => $item->product->name,
                                'quantity_restored' => $item->quantity_delivered,
                                'old_stock' => $oldStock,
                                'new_stock' => $item->product->fresh()->stock_quantity
                            ];
                            
                            // Log stock restoration
                            ActivityLog::logStockChange(
                                $item->product,
                                $oldStock,
                                $item->product->fresh()->stock_quantity,
                                "Restauration suite à suppression ordonnance {$prescriptionNumber}"
                            );
                        }
                        
                        // Delete the prescription item
                        $item->delete();
                        $deletionSummary['items_deleted']++;
                    }
                }

                // 2. DELETE THE PRESCRIPTION
                $prescription->delete();

                // Log the complete deletion
                ActivityLog::logActivity(
                    'delete',
                    "Ordonnance supprimée: {$prescriptionNumber} | Client: {$clientName} | Items supprimés: {$deletionSummary['items_deleted']} | Stock restauré pour " . count($deletionSummary['stock_restored']) . " produit(s) | Supprimée par: " . auth()->user()->name . " (" . auth()->user()->role . ")",
                    null,
                    $prescriptionData,
                    $deletionSummary
                );
                
                DB::commit();
                
                // Success message
                $message = "Ordonnance {$prescriptionNumber} supprimée avec succès!";
                if (count($deletionSummary['stock_restored']) > 0) {
                    $message .= " Le stock a été restauré pour " . count($deletionSummary['stock_restored']) . " produit(s).";
                }
                
                return redirect()->route('prescriptions.index')
                    ->with('success', $message);
                    
            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }

        } catch (\Exception $e) {
            ActivityLog::logActivity(
                'error',
                "Erreur lors de la suppression de l'ordonnance: " . $e->getMessage(),
                isset($prescription) ? $prescription : null,
                null,
                [
                    'prescription_id' => $id, 
                    'error_details' => $e->getMessage(),
                    'attempted_by' => auth()->user()->name,
                    'user_role' => auth()->user()->role,
                    'error_trace' => $e->getTraceAsString()
                ]
            );
            
            return redirect()->route('prescriptions.index')
                ->withErrors(['error' => 'Erreur lors de la suppression de l\'ordonnance: ' . $e->getMessage()]);
        }
    }

    /**
     * Check prescription dependencies before deletion (AJAX) - IMPROVED VERSION
     */
    public function checkDependencies($id)
    {
        try {
            $prescription = Prescription::with(['prescriptionItems.product'])->findOrFail($id);
            
            // Check user permissions first
            $canDelete = auth()->user()->canDeletePrescription($prescription);
            $restrictionReason = auth()->user()->getDeletionRestrictionReason($prescription);
            
            $dependencies = [
                'items' => $prescription->prescriptionItems()->count(),
                'delivered_items' => $prescription->prescriptionItems()->where('quantity_delivered', '>', 0)->count(),
                'can_delete' => $canDelete,
                'warnings' => [],
                'action_type' => $canDelete ? 'safe_delete' : 'restricted',
                'stock_impact' => [],
                'restriction_reason' => $restrictionReason
            ];
            
            // Add permission-based warnings
            if (!$canDelete) {
                $dependencies['warnings'][] = "🚫 " . $restrictionReason;
            } else {
                // Add info warnings for successful deletion
                if ($dependencies['items'] > 0) {
                    $dependencies['warnings'][] = "ℹ️ Cette ordonnance contient {$dependencies['items']} médicament(s) qui seront supprimés.";
                }
                
                if ($dependencies['delivered_items'] > 0) {
                    $dependencies['warnings'][] = "⚠️ {$dependencies['delivered_items']} médicament(s) ont été délivrés. Le stock sera restauré automatiquement.";
                    
                    // Calculate stock impact
                    $deliveredItems = $prescription->prescriptionItems()->where('quantity_delivered', '>', 0)->with('product')->get();
                    foreach ($deliveredItems as $item) {
                        if ($item->product) {
                            $dependencies['stock_impact'][] = [
                                'product_name' => $item->product->name,
                                'quantity_to_restore' => $item->quantity_delivered,
                                'current_stock' => $item->product->stock_quantity,
                                'new_stock' => $item->product->stock_quantity + $item->quantity_delivered
                            ];
                        }
                    }
                }
            }
            
            return response()->json($dependencies);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Erreur lors de la vérification des dépendances: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deliver($id)
    {
        $prescription = Prescription::with([
            'client', 
            'prescriptionItems' => function($query) {
                $query->with(['product' => function($productQuery) {
                    $productQuery->withTrashed(); // Include soft-deleted products for delivery view
                }]);
            }
        ])->findOrFail($id);
        
        if ($prescription->status === 'completed') {
            return redirect()->route('prescriptions.show', $prescription->id)
                ->withErrors(['error' => 'Cette ordonnance a déjà été complètement délivrée.']);
        }
        
        if ($prescription->isExpired()) {
            return redirect()->route('prescriptions.show', $prescription->id)
                ->withErrors(['error' => 'Cette ordonnance a expiré et ne peut plus être délivrée.']);
        }
        
        return view('prescriptions.deliver', compact('prescription'));
    }

    public function processDelivery(Request $request, $id)
    {
        $prescription = Prescription::with(['prescriptionItems'])->findOrFail($id);
        
        if ($prescription->isExpired()) {
            return redirect()->route('prescriptions.show', $prescription->id)
                ->withErrors(['error' => 'Cette ordonnance a expiré.']);
        }

        $validator = Validator::make($request->all(), [
            'items' => 'required|array',
            'items.*.item_id' => 'required|exists:prescription_items,id',
            'items.*.quantity_to_deliver' => 'required|integer|min:0',
            'pharmacist_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        
        try {
            $deliveryData = [];
            
            foreach ($request->items as $itemData) {
                $prescriptionItem = PrescriptionItem::with('product')->find($itemData['item_id']);
                $quantityToDeliver = (int) $itemData['quantity_to_deliver'];
                
                // Skip items with missing products
                if (!$prescriptionItem->product) {
                    continue;
                }
                
                $maxQuantity = $prescriptionItem->quantity_prescribed - $prescriptionItem->quantity_delivered;
                if ($quantityToDeliver > $maxQuantity) {
                    throw new \Exception("Quantité trop élevée pour {$prescriptionItem->product->name}. Maximum: {$maxQuantity}");
                }
                
                if ($quantityToDeliver > $prescriptionItem->product->stock_quantity) {
                    throw new \Exception("Stock insuffisant pour {$prescriptionItem->product->name}. Stock disponible: {$prescriptionItem->product->stock_quantity}");
                }
                
                $prescriptionItem->quantity_delivered += $quantityToDeliver;
                $prescriptionItem->save();
                
                if ($quantityToDeliver > 0) {
                    $oldStock = $prescriptionItem->product->stock_quantity;
                    $prescriptionItem->product->decrement('stock_quantity', $quantityToDeliver);
                    
                    $deliveryData[] = [
                        'product_name' => $prescriptionItem->product->name,
                        'quantity_delivered' => $quantityToDeliver,
                        'old_stock' => $oldStock,
                        'new_stock' => $prescriptionItem->product->fresh()->stock_quantity
                    ];
                    
                    // Log stock change
                    ActivityLog::logStockChange(
                        $prescriptionItem->product,
                        $oldStock,
                        $prescriptionItem->product->fresh()->stock_quantity,
                        "Délivrance ordonnance {$prescription->prescription_number}"
                    );
                }
            }
            
            if ($request->pharmacist_notes) {
                $prescription->pharmacist_notes = $request->pharmacist_notes;
            }
            
            $prescription->updateStatus();
            
            // Log delivery
            ActivityLog::logActivity(
                'delivery',
                "Délivrance ordonnance: {$prescription->prescription_number} | " . count($deliveryData) . " produit(s) délivré(s)",
                $prescription,
                null,
                [
                    'delivered_by' => auth()->user()->name,
                    'delivery_data' => $deliveryData,
                    'new_status' => $prescription->status,
                    'pharmacist_notes' => $request->pharmacist_notes
                ]
            );
            
            DB::commit();

            return redirect()->route('prescriptions.show', $prescription->id)
                ->with('success', 'Délivrance enregistrée avec succès!');
                
        } catch (\Exception $e) {
            DB::rollback();
            
            ActivityLog::logActivity(
                'error',
                "Erreur lors de la délivrance de l'ordonnance {$prescription->prescription_number}: " . $e->getMessage(),
                $prescription,
                null,
                ['error_details' => $e->getMessage(), 'request_data' => $request->all()]
            );
            
            return redirect()->back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function print($id)
    {
        $prescription = Prescription::with([
            'client', 
            'createdBy', 
            'prescriptionItems' => function($query) {
                $query->with(['product' => function($productQuery) {
                    $productQuery->withTrashed(); // Include soft-deleted products for printing
                }]);
            }
        ])->findOrFail($id);
        
        return view('prescriptions.print', compact('prescription'));
    }

    /**
     * Export prescriptions to CSV
     */
    public function export(Request $request)
    {
        $query = Prescription::with(['client', 'createdBy']);

        // Apply filters
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        if ($request->has('date_from') && !empty($request->date_from)) {
            $query->whereDate('prescription_date', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && !empty($request->date_to)) {
            $query->whereDate('prescription_date', '<=', $request->date_to);
        }

        $prescriptions = $query->get();

        // Log export activity
        ActivityLog::logActivity(
            'export',
            'Export de la liste des ordonnances (' . $prescriptions->count() . ' ordonnances)',
            null,
            null,
            [
                'export_count' => $prescriptions->count(), 
                'exported_by' => auth()->user()->name,
                'filters_applied' => $request->only(['status', 'date_from', 'date_to'])
            ]
        );

        $filename = 'prescriptions_export_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($prescriptions) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fwrite($file, "\xEF\xBB\xBF");
            
            // CSV headers
            fputcsv($file, [
                'N° Ordonnance',
                'Client',
                'Médecin',
                'Spécialité',
                'Date prescription',
                'Date expiration',
                'Statut',
                'Créée par',
                'Date création',
                'Notes médicales',
                'Notes pharmacien'
            ], ';');

            foreach ($prescriptions as $prescription) {
                fputcsv($file, [
                    $prescription->prescription_number,
                    $prescription->client ? $prescription->client->full_name : 'Client supprimé',
                    $prescription->doctor_name,
                    $prescription->doctor_speciality ?: 'Non renseigné',
                    $prescription->prescription_date->format('d/m/Y'),
                    $prescription->expiry_date->format('d/m/Y'),
                    $prescription->status_label,
                    $prescription->createdBy ? $prescription->createdBy->name : 'Utilisateur supprimé',
                    $prescription->created_at->format('d/m/Y H:i'),
                    $prescription->medical_notes ?: 'Aucune',
                    $prescription->pharmacist_notes ?: 'Aucune'
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Duplicate a prescription (create a new one based on existing)
     */
    public function duplicate($id)
    {
        try {
            $originalPrescription = Prescription::with(['client', 'prescriptionItems.product'])->findOrFail($id);
            
            DB::beginTransaction();
            
            // Create new prescription
            $newPrescription = new Prescription();
            $newPrescription->client_id = $originalPrescription->client_id;
            $newPrescription->doctor_name = $originalPrescription->doctor_name;
            $newPrescription->doctor_phone = $originalPrescription->doctor_phone;
            $newPrescription->doctor_speciality = $originalPrescription->doctor_speciality;
            $newPrescription->prescription_date = now()->format('Y-m-d');
            $newPrescription->expiry_date = now()->addDays(30)->format('Y-m-d'); // Default 30 days validity
            $newPrescription->medical_notes = $originalPrescription->medical_notes;
            $newPrescription->created_by = auth()->id();
            $newPrescription->status = 'pending';
            $newPrescription->save();

            // Copy prescription items (only for products that still exist)
            foreach ($originalPrescription->prescriptionItems as $item) {
                if ($item->product) { // Only copy if product still exists
                    PrescriptionItem::create([
                        'prescription_id' => $newPrescription->id,
                        'product_id' => $item->product_id,
                        'quantity_prescribed' => $item->quantity_prescribed,
                        'dosage_instructions' => $item->dosage_instructions,
                        'duration_days' => $item->duration_days,
                        'instructions' => $item->instructions,
                        'is_substitutable' => $item->is_substitutable,
                    ]);
                }
            }

            // Log duplication
            ActivityLog::logActivity(
                'duplicate',
                "Ordonnance dupliquée: {$originalPrescription->prescription_number} → {$newPrescription->prescription_number}",
                $newPrescription,
                null,
                [
                    'original_prescription_id' => $originalPrescription->id,
                    'original_prescription_number' => $originalPrescription->prescription_number,
                    'duplicated_by' => auth()->user()->name
                ]
            );
            
            DB::commit();
            
            return redirect()->route('prescriptions.edit', $newPrescription->id)
                ->with('success', "Ordonnance dupliquée avec succès! Nouveau numéro: {$newPrescription->prescription_number}");
                
        } catch (\Exception $e) {
            DB::rollback();
            
            ActivityLog::logActivity(
                'error',
                "Erreur lors de la duplication de l'ordonnance: " . $e->getMessage(),
                null,
                null,
                [
                    'original_prescription_id' => $id,
                    'error_details' => $e->getMessage(),
                    'attempted_by' => auth()->user()->name
                ]
            );
            
            return redirect()->route('prescriptions.show', $id)
                ->withErrors(['error' => 'Erreur lors de la duplication de l\'ordonnance: ' . $e->getMessage()]);
        }
    }

    /**
     * Mark prescription as expired (manual expiry)
     */
    public function markAsExpired($id)
    {
        try {
            $prescription = Prescription::findOrFail($id);
            $oldStatus = $prescription->status;
            
            if ($prescription->isExpired()) {
                return redirect()->route('prescriptions.show', $prescription->id)
                    ->withErrors(['error' => 'Cette ordonnance est déjà expirée.']);
            }
            
            if ($prescription->status === 'completed') {
                return redirect()->route('prescriptions.show', $prescription->id)
                    ->withErrors(['error' => 'Impossible d\'expirer une ordonnance déjà complètement délivrée.']);
            }
            
            $prescription->status = 'expired';
            $prescription->save();
            
            // Log manual expiry
            ActivityLog::logActivity(
                'manual_expiry',
                "Ordonnance marquée comme expirée manuellement: {$prescription->prescription_number}",
                $prescription,
                ['status' => $oldStatus],
                [
                    'status' => 'expired',
                    'expired_by' => auth()->user()->name,
                    'expired_at' => now()
                ]
            );
            
            return redirect()->route('prescriptions.show', $prescription->id)
                ->with('success', 'Ordonnance marquée comme expirée.');
                
        } catch (\Exception $e) {
            ActivityLog::logActivity(
                'error',
                "Erreur lors de l'expiration manuelle de l'ordonnance: " . $e->getMessage(),
                null,
                null,
                [
                    'prescription_id' => $id,
                    'error_details' => $e->getMessage(),
                    'attempted_by' => auth()->user()->name
                ]
            );
            
            return redirect()->route('prescriptions.show', $id)
                ->withErrors(['error' => 'Erreur lors de l\'expiration de l\'ordonnance.']);
        }
    }

    /**
     * Get prescription statistics for dashboard/reports
     */
    public function getStatistics(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());
        
        $statistics = [
            'total_prescriptions' => Prescription::whereBetween('created_at', [$startDate, $endDate])->count(),
            'pending_prescriptions' => Prescription::pending()->whereBetween('created_at', [$startDate, $endDate])->count(),
            'completed_prescriptions' => Prescription::where('status', 'completed')->whereBetween('created_at', [$startDate, $endDate])->count(),
            'expired_prescriptions' => Prescription::expired()->whereBetween('created_at', [$startDate, $endDate])->count(),
            'expiring_soon' => Prescription::active()->where('expiry_date', '<=', now()->addDays(7))->count(),
            'prescriptions_by_doctor' => Prescription::whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('doctor_name')
                ->selectRaw('doctor_name, count(*) as count')
                ->orderBy('count', 'desc')
                ->take(10)
                ->get(),
            'prescriptions_by_status' => Prescription::whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('status')
                ->selectRaw('status, count(*) as count')
                ->get(),
        ];
        
        return response()->json($statistics);
    }
}