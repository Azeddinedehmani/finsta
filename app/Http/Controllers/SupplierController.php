<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Supplier;

class SupplierController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
        // Plus besoin de middleware admin ici car déjà appliqué dans les routes
    }

    /**
     * Display a listing of the suppliers.
     */
    public function index(Request $request)
    {
        $query = Supplier::query();

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('active', $request->status === 'active');
        }

        $suppliers = $query->withCount('products')->latest()->paginate(15);
        
        return view('suppliers.index', compact('suppliers'));
    }

    /**
     * Show the form for creating a new supplier.
     */
    public function create()
    {
        return view('suppliers.create');
    }

    /**
     * Store a newly created supplier in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:suppliers,name',
            'contact_person' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'email' => 'nullable|email|unique:suppliers,email',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $supplier = new Supplier();
        $supplier->fill($request->all());
        $supplier->active = $request->has('active');
        $supplier->save();

        return redirect()->route('suppliers.index')
            ->with('success', 'Fournisseur ajouté avec succès!');
    }

    /**
     * Display the specified supplier.
     */
    public function show($id)
    {
        $supplier = Supplier::with(['products' => function($query) {
            $query->latest()->take(10);
        }])->findOrFail($id);
        
        $recentProducts = $supplier->products;
        $totalProducts = $supplier->products()->count();
        $lowStockProducts = $supplier->products()
            ->whereColumn('stock_quantity', '<=', 'stock_threshold')
            ->count();
        
        return view('suppliers.show', compact('supplier', 'recentProducts', 'totalProducts', 'lowStockProducts'));
    }

    /**
     * Show the form for editing the specified supplier.
     */
    public function edit($id)
    {
        $supplier = Supplier::findOrFail($id);
        return view('suppliers.edit', compact('supplier'));
    }

    /**
     * Update the specified supplier in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:suppliers,name,'.$id,
            'contact_person' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'email' => 'nullable|email|unique:suppliers,email,'.$id,
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $supplier = Supplier::findOrFail($id);
        $supplier->fill($request->all());
        $supplier->active = $request->has('active');
        $supplier->save();

        return redirect()->route('suppliers.show', $supplier->id)
            ->with('success', 'Fournisseur mis à jour avec succès!');
    }

    /**
     * Remove the specified supplier from storage.
     */
    public function destroy($id)
    {
        $supplier = Supplier::findOrFail($id);
        
        // Check if supplier has products
        if ($supplier->products()->count() > 0) {
            return redirect()->route('suppliers.index')
                ->withErrors(['error' => 'Impossible de supprimer ce fournisseur car il a des produits associés.']);
        }
        
        $supplierName = $supplier->name;
        $supplier->delete();

        return redirect()->route('suppliers.index')
            ->with('success', "Fournisseur '{$supplierName}' supprimé avec succès!");
    }
}