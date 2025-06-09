<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Client;
use App\Models\Sale;
use Illuminate\Support\Facades\Schema; // Add this import

use App\Models\Prescription;
use App\Models\ActivityLog;

class ClientController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the clients.
     */
    public function index(Request $request)
    {
        $query = Client::query();

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('insurance_number', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('active', $request->status === 'active');
        }

        $clients = $query->latest()->paginate(15);
        
        return view('clients.index', compact('clients'));
    }

    /**
     * Show the form for creating a new client.
     */
    public function create()
    {
        return view('clients.create');
    }

    /**
     * Store a newly created client in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:clients,email',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date|before:today',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:10',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'allergies' => 'nullable|string',
            'medical_notes' => 'nullable|string',
            'insurance_number' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $client = new Client();
            $client->fill($request->all());
            $client->active = $request->has('active');
            $client->save();

            // Log client creation
            ActivityLog::logActivity(
                'create',
                "Client créé: {$client->full_name}",
                $client,
                null,
                $client->toArray()
            );

            return redirect()->route('clients.index')
                ->with('success', 'Client ajouté avec succès!');

        } catch (\Exception $e) {
            ActivityLog::logActivity(
                'error',
                "Erreur lors de la création du client: " . $e->getMessage(),
                null,
                null,
                ['error_details' => $e->getMessage(), 'request_data' => $request->all()]
            );
            
            return redirect()->back()
                ->withErrors(['error' => 'Erreur lors de la création du client.'])
                ->withInput();
        }
    }

    /**
     * Display the specified client.
     */
    public function show($id)
    {
        $client = Client::with(['sales.saleItems.product'])->findOrFail($id);
        $recentSales = $client->sales()->with(['saleItems.product'])
                                   ->latest()
                                   ->take(10)
                                   ->get();
        
        // Statistiques du client
        $clientStats = [
            'total_sales' => $client->sales()->count(),
            'total_spent' => $client->sales()->sum('total_amount'),
            'last_visit' => $client->sales()->latest()->first()?->sale_date,
            'prescriptions_count' => Prescription::where('client_id', $id)->count(),
        ];
        
        return view('clients.show', compact('client', 'recentSales', 'clientStats'));
    }

    /**
     * Show the form for editing the specified client.
     */
    public function edit($id)
    {
        $client = Client::findOrFail($id);
        return view('clients.edit', compact('client'));
    }

    /**
     * Update the specified client in storage.
     */
    public function update(Request $request, $id)
    {
        $client = Client::findOrFail($id);
        $oldValues = $client->toArray();

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:clients,email,'.$id,
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date|before:today',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:10',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'allergies' => 'nullable|string',
            'medical_notes' => 'nullable|string',
            'insurance_number' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $client->fill($request->all());
            $client->active = $request->has('active');
            $client->save();

            // Log client update
            ActivityLog::logActivity(
                'update',
                "Client modifié: {$client->full_name}",
                $client,
                $oldValues,
                $client->toArray()
            );

            return redirect()->route('clients.index')
                ->with('success', 'Client mis à jour avec succès!');

        } catch (\Exception $e) {
            ActivityLog::logActivity(
                'error',
                "Erreur lors de la modification du client {$client->full_name}: " . $e->getMessage(),
                $client
            );
            
            return redirect()->back()
                ->withErrors(['error' => 'Erreur lors de la modification du client.'])
                ->withInput();
        }
    }

    /**
 * Remove the specified client from storage - VERSION CORRIGÉE
 */
public function destroy($id)
{
    try {
        $client = Client::findOrFail($id);
        $clientData = $client->toArray();
        $clientName = $client->full_name;
        
        // Count associated data
        $salesCount = $client->sales()->count();
        $prescriptionsCount = Prescription::where('client_id', $id)->count();
        
        // Protection: only admins can delete clients with sales or prescriptions
        if (($salesCount > 0 || $prescriptionsCount > 0) && !auth()->user()->isAdmin()) {
            ActivityLog::logActivity(
                'unauthorized_access',
                "Tentative de suppression de client avec données associées par un non-administrateur: {$clientName}",
                $client,
                null,
                ['attempted_by' => auth()->user()->name, 'user_role' => auth()->user()->role]
            );
            
            return redirect()->route('clients.index')
                ->withErrors(['error' => 'Seuls les administrateurs peuvent supprimer des clients ayant des données associées.']);
        }

        DB::beginTransaction();
        
        try {
            $deletionSummary = [
                'client_name' => $clientName,
                'client_data' => $clientData,
                'sales_updated' => 0,
                'prescriptions_updated' => 0,
                'deleted_by' => auth()->user()->name,
                'deletion_date' => now()->toDateTimeString()
            ];

            // 1. GÉRER LES VENTES - Vérifier d'abord si les colonnes existent
            if ($salesCount > 0) {
                // Vérifier si les colonnes de suivi client existent dans la table sales
                $hasTrackingColumns = Schema::hasColumn('sales', 'client_name_at_deletion') && 
                                    Schema::hasColumn('sales', 'deleted_client_data');
                
                if ($hasTrackingColumns) {
                    // Utiliser la nouvelle méthode avec colonnes de suivi
                    $sales = $client->sales()->get();
                    
                    foreach ($sales as $sale) {
                        $sale->update([
                            'client_id' => null,
                            'client_name_at_deletion' => $clientName,
                            'deleted_client_data' => [
                                'name' => $clientName,
                                'email' => $client->email,
                                'phone' => $client->phone,
                                'insurance_number' => $client->insurance_number,
                                'deleted_at' => now()->toDateTimeString(),
                                'deleted_by' => auth()->user()->name
                            ]
                        ]);
                    }
                    $deletionSummary['sales_updated'] = $salesCount;
                } else {
                    // Méthode de fallback - simplement mettre client_id à null
                    $client->sales()->update(['client_id' => null]);
                    $deletionSummary['sales_updated'] = $salesCount;
                    
                    // Avertir l'utilisateur que le suivi des clients supprimés n'est pas disponible
                    session()->flash('warning', 'Les ventes ont été préservées mais sans informations détaillées du client supprimé. Veuillez exécuter les migrations pour activer le suivi complet.');
                }
            }

            // 2. GÉRER LES ORDONNANCES - Supprimer les ordonnances et leurs items
            if ($prescriptionsCount > 0) {
                $prescriptions = Prescription::where('client_id', $id)->with('prescriptionItems')->get();
                
                foreach ($prescriptions as $prescription) {
                    // Supprimer les items d'ordonnance d'abord
                    $prescription->prescriptionItems()->delete();
                }
                
                // Supprimer les ordonnances
                Prescription::where('client_id', $id)->delete();
                $deletionSummary['prescriptions_updated'] = $prescriptionsCount;
            }

            // 3. SUPPRIMER LE CLIENT
            $client->delete();

            // Log the complete deletion
            ActivityLog::logActivity(
                'delete',
                "Client supprimé: {$clientName} | Ventes préservées: {$deletionSummary['sales_updated']} | Ordonnances supprimées: {$deletionSummary['prescriptions_updated']}",
                null,
                $clientData,
                $deletionSummary
            );
            
            DB::commit();
            
            // Success message
            $message = "Client supprimé avec succès!";
            if ($deletionSummary['sales_updated'] > 0) {
                $message .= " {$deletionSummary['sales_updated']} vente(s) ont été préservées.";
            }
            if ($deletionSummary['prescriptions_updated'] > 0) {
                $message .= " {$deletionSummary['prescriptions_updated']} ordonnance(s) supprimée(s).";
            }
            
            return redirect()->route('clients.index')
                ->with('success', $message);
                
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }

    } catch (\Exception $e) {
        ActivityLog::logActivity(
            'error',
            "Erreur lors de la suppression du client: " . $e->getMessage(),
            isset($client) ? $client : null,
            null,
            [
                'client_id' => $id, 
                'error_details' => $e->getMessage(),
                'attempted_by' => auth()->user()->name,
                'error_trace' => $e->getTraceAsString()
            ]
        );
        
        return redirect()->route('clients.index')
            ->withErrors(['error' => 'Erreur lors de la suppression du client: ' . $e->getMessage()]);
    }
}

    /**
     * Deactivate a client instead of deleting (RECOMMENDED ALTERNATIVE)
     */
    public function deactivate($id)
    {
        try {
            $client = Client::findOrFail($id);
            $oldStatus = $client->active;
            
            if (!$client->active) {
                return redirect()->route('clients.index')
                    ->withErrors(['error' => 'Ce client est déjà désactivé.']);
            }
            
            $client->update(['active' => false]);
            
            // Log deactivation
            ActivityLog::logActivity(
                'update',
                "Client désactivé: {$client->full_name}",
                $client,
                ['active' => $oldStatus],
                ['active' => false, 'deactivated_by' => auth()->user()->name, 'deactivated_at' => now()]
            );
            
            return redirect()->route('clients.index')
                ->with('success', 'Client désactivé avec succès! Ses données restent préservées.');
                
        } catch (\Exception $e) {
            ActivityLog::logActivity(
                'error',
                "Erreur lors de la désactivation du client: " . $e->getMessage(),
                null,
                null,
                ['client_id' => $id, 'error_details' => $e->getMessage()]
            );
            
            return redirect()->route('clients.index')
                ->withErrors(['error' => 'Erreur lors de la désactivation du client.']);
        }
    }

    /**
     * Reactivate a client
     */
    public function reactivate($id)
    {
        try {
            $client = Client::findOrFail($id);
            $oldStatus = $client->active;
            
            if ($client->active) {
                return redirect()->route('clients.index')
                    ->withErrors(['error' => 'Ce client est déjà actif.']);
            }
            
            $client->update(['active' => true]);
            
            // Log reactivation
            ActivityLog::logActivity(
                'update',
                "Client réactivé: {$client->full_name}",
                $client,
                ['active' => $oldStatus],
                ['active' => true, 'reactivated_by' => auth()->user()->name, 'reactivated_at' => now()]
            );
            
            return redirect()->route('clients.index')
                ->with('success', 'Client réactivé avec succès!');
                
        } catch (\Exception $e) {
            ActivityLog::logActivity(
                'error',
                "Erreur lors de la réactivation du client: " . $e->getMessage(),
                null,
                null,
                ['client_id' => $id, 'error_details' => $e->getMessage()]
            );
            
            return redirect()->route('clients.index')
                ->withErrors(['error' => 'Erreur lors de la réactivation du client.']);
        }
    }

    /**
     * Check client dependencies before deletion (AJAX)
     */
    public function checkDependencies($id)
    {
        try {
            $client = Client::findOrFail($id);
            
            $dependencies = [
                'sales' => $client->sales()->count(),
                'prescriptions' => Prescription::where('client_id', $id)->count(),
                'can_delete' => true,
                'warnings' => [],
                'action_type' => 'safe_delete'
            ];
            
            // Add warnings
            if ($dependencies['sales'] > 0) {
                $dependencies['warnings'][] = "⚠️ Ce client a {$dependencies['sales']} vente(s). Les ventes seront préservées mais le lien avec le client sera supprimé.";
            }
            if ($dependencies['prescriptions'] > 0) {
                $dependencies['warnings'][] = "⚠️ Ce client a {$dependencies['prescriptions']} ordonnance(s). Elles seront définitivement supprimées.";
            }
            
            return response()->json($dependencies);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la vérification des dépendances',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export clients
     */
    public function export(Request $request)
    {
        $query = Client::withCount(['sales', 'prescriptions' => function($query) {
            // Count prescriptions
        }]);

        // Apply filters
        if ($request->has('status') && $request->status !== '') {
            $query->where('active', $request->status === 'active');
        }

        $clients = $query->get();

        // Log export activity
        ActivityLog::logActivity(
            'export',
            'Export de la liste des clients (' . $clients->count() . ' clients)',
            null,
            null,
            ['export_count' => $clients->count(), 'exported_by' => auth()->user()->name]
        );

        $filename = 'clients_export_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($clients) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fwrite($file, "\xEF\xBB\xBF");
            
            // CSV headers
            fputcsv($file, [
                'ID',
                'Prénom',
                'Nom',
                'Email',
                'Téléphone',
                'Statut',
                'Nb Ventes',
                'Nb Ordonnances',
                'Date création',
                'Dernière modification'
            ], ';');

            foreach ($clients as $client) {
                fputcsv($file, [
                    $client->id,
                    $client->first_name,
                    $client->last_name,
                    $client->email ?: 'Non renseigné',
                    $client->phone ?: 'Non renseigné',
                    $client->active ? 'Actif' : 'Inactif',
                    $client->sales_count,
                    $client->prescriptions_count ?? 0,
                    $client->created_at->format('d/m/Y H:i'),
                    $client->updated_at->format('d/m/Y H:i')
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}