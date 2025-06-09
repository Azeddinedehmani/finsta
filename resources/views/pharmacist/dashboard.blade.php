<?php
// resources/views/pharmacist/dashboard.blade.php - Updated with real data
?>
@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="alert bg-accent text-white">
            <h4 class="alert-heading"><i class="fas fa-user-circle me-2"></i>Bienvenue, {{ Auth::user()->name }}!</h4>
            <p>Vous êtes connecté en tant que Pharmacien. Vous avez accès aux fonctionnalités de vente et de gestion des clients.</p>
        </div>
    </div>
</div>

@php
    use App\Models\Sale;
    use App\Models\Client;
    use App\Models\Product;
    use App\Models\Prescription;
    
    // Real data calculations
    $today = now();
    $salesToday = Sale::whereDate('sale_date', $today)->sum('total_amount') ?? 0;
    $salesCountToday = Sale::whereDate('sale_date', $today)->count();
    $clientsToday = Sale::whereDate('sale_date', $today)->distinct('client_id')->count('client_id');
    $prescriptionsToday = Prescription::whereDate('created_at', $today)->count();
    
    // Low stock alerts
    $lowStockProducts = Product::whereColumn('stock_quantity', '<=', 'stock_threshold')->count();
    $outOfStockProducts = Product::where('stock_quantity', '<=', 0)->count();
    $expiringProducts = Product::where('expiry_date', '<=', now()->addDays(30))
                              ->where('expiry_date', '>', now())
                              ->count();
    
    // Recent sales for table
    $recentSales = Sale::with(['client', 'user', 'saleItems.product'])
                      ->latest('sale_date')
                      ->take(5)
                      ->get();
    
    // Low stock products for alerts
    $lowStockProductsList = Product::with(['category', 'supplier'])
                                  ->whereColumn('stock_quantity', '<=', 'stock_threshold')
                                  ->orderBy('stock_quantity', 'asc')
                                  ->take(5)
                                  ->get();
    
    // Pending prescriptions
    $pendingPrescriptions = Prescription::where('status', 'pending')->count();
    $expiringPrescriptions = Prescription::where('expiry_date', '<=', now()->addDays(7))
                                        ->where('expiry_date', '>', now())
                                        ->count();
@endphp

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="card-title">Ventes du jour</h6>
                    <h3 class="mb-0">{{ number_format($salesToday, 2) }} €</h3>
                    <small class="opacity-75">{{ $salesCountToday }} vente(s)</small>
                </div>
                <i class="fas fa-shopping-cart fa-2x"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="card-title">Clients aujourd'hui</h6>
                    <h3 class="mb-0">{{ $clientsToday }}</h3>
                    <small class="opacity-75">Clients servis</small>
                </div>
                <i class="fas fa-users fa-2x"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="card-title">Ordonnances</h6>
                    <h3 class="mb-0">{{ $pendingPrescriptions }}</h3>
                    <small class="opacity-75">En attente</small>
                </div>
                <i class="fas fa-file-prescription fa-2x"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="card-title">Alertes Stock</h6>
                    <h3 class="mb-0">{{ $lowStockProducts }}</h3>
                    <small class="opacity-75">{{ $outOfStockProducts }} rupture(s)</small>
                </div>
                <i class="fas fa-exclamation-triangle fa-2x"></i>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Accès rapide</h5>
            </div>
            <div class="card-body p-3">
                <div class="row">
                    <div class="col-6 mb-3">
                        <a href="{{ route('sales.create') }}" class="btn btn-primary w-100 h-100 py-4 d-flex flex-column align-items-center justify-content-center">
                            <i class="fas fa-cash-register fa-2x mb-2"></i>
                            <span>Nouvelle vente</span>
                        </a>
                    </div>
                    <div class="col-6 mb-3">
                        <a href="{{ route('prescriptions.create') }}" class="btn btn-secondary w-100 h-100 py-4 d-flex flex-column align-items-center justify-content-center">
                            <i class="fas fa-file-prescription fa-2x mb-2"></i>
                            <span>Nouvelle ordonnance</span>
                        </a>
                    </div>
                    <div class="col-6 mb-3">
                        <a href="{{ route('clients.create') }}" class="btn btn-info text-white w-100 h-100 py-4 d-flex flex-column align-items-center justify-content-center">
                            <i class="fas fa-user-plus fa-2x mb-2"></i>
                            <span>Nouveau client</span>
                        </a>
                    </div>
                    <div class="col-6 mb-3">
                        <a href="{{ route('inventory.index') }}" class="btn btn-warning w-100 h-100 py-4 d-flex flex-column align-items-center justify-content-center">
                            <i class="fas fa-search fa-2x mb-2"></i>
                            <span>Recherche produit</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Alertes Stock</h5>
                <span class="badge bg-danger">{{ $lowStockProducts }}</span>
            </div>
            <div class="card-body p-0">
                @if($lowStockProductsList->count() > 0)
                    <ul class="list-group list-group-flush">
                        @foreach($lowStockProductsList as $product)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">{{ $product->name }}</h6>
                                <small class="{{ $product->stock_quantity <= 0 ? 'text-danger' : 'text-warning' }}">
                                    Stock: {{ $product->stock_quantity }} 
                                    {{ $product->stock_quantity <= 1 ? 'unité' : 'unités' }}
                                    @if($product->category)
                                        <span class="text-muted">- {{ $product->category->name }}</span>
                                    @endif
                                </small>
                            </div>
                            <span class="badge {{ $product->stock_quantity <= 0 ? 'bg-danger' : 'bg-warning text-dark' }} rounded-pill">
                                {{ $product->stock_quantity <= 0 ? 'Rupture' : 'Critique' }}
                            </span>
                        </li>
                        @endforeach
                    </ul>
                @else
                    <div class="p-4 text-center text-muted">
                        <i class="fas fa-check-circle fa-2x mb-2"></i>
                        <p class="mb-0">Aucune alerte de stock pour le moment</p>
                    </div>
                @endif
            </div>
            <div class="card-footer text-center">
                <a href="{{ route('inventory.index', ['stock_status' => 'low']) }}" class="btn btn-sm btn-primary">
                    Voir toutes les alertes
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Ventes récentes</h5>
                <span class="badge bg-primary">{{ $salesCountToday }} aujourd'hui</span>
            </div>
            <div class="card-body p-0">
                @if($recentSales->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>N° Vente</th>
                                    <th>Client</th>
                                    <th>Produits</th>
                                    <th>Ordonnance</th>
                                    <th>Montant</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentSales as $sale)
                                <tr>
                                    <td>
                                        <span class="fw-bold text-primary">{{ $sale->sale_number }}</span>
                                    </td>
                                    <td>
                                        @if($sale->client)
                                            {{ $sale->client->full_name }}
                                        @else
                                            <span class="text-muted">Client anonyme</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            {{ $sale->saleItems->count() }} produit(s)
                                        </span>
                                        @if($sale->saleItems->first())
                                            <br><small class="text-muted">{{ $sale->saleItems->first()->product->name }}{{ $sale->saleItems->count() > 1 ? '...' : '' }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($sale->has_prescription)
                                            <span class="badge bg-success">Oui</span>
                                            @if($sale->prescription_number)
                                                <br><small class="text-muted">{{ $sale->prescription_number }}</small>
                                            @endif
                                        @else
                                            <span class="badge bg-secondary">Non</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="fw-bold">{{ number_format($sale->total_amount, 2) }} €</span>
                                        <br><small class="text-muted">{{ ucfirst($sale->payment_method_label) }}</small>
                                    </td>
                                    <td>
                                        {{ $sale->sale_date->format('d/m/Y H:i') }}
                                        <br><small class="text-muted">{{ $sale->sale_date->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        <a href="{{ route('sales.show', $sale->id) }}" class="btn btn-sm btn-info text-white" title="Voir">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('sales.print', $sale->id) }}" class="btn btn-sm btn-primary" title="Imprimer">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-4 text-center text-muted">
                        <i class="fas fa-shopping-cart fa-2x mb-2"></i>
                        <p class="mb-0">Aucune vente récente</p>
                        <a href="{{ route('sales.create') }}" class="btn btn-primary btn-sm mt-2">
                            <i class="fas fa-plus me-1"></i>Créer une vente
                        </a>
                    </div>
                @endif
            </div>
            <div class="card-footer text-center">
                <a href="{{ route('sales.index') }}" class="btn btn-sm btn-primary">Voir toutes les ventes</a>
            </div>
        </div>
    </div>
</div>

@if($expiringProducts > 0 || $expiringPrescriptions > 0)
<div class="row mt-4">
    <div class="col-md-12">
        <div class="alert alert-warning">
            <h5 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Alertes d'expiration</h5>
            <div class="row">
                @if($expiringProducts > 0)
                <div class="col-md-6">
                    <p class="mb-2">
                        <strong>{{ $expiringProducts }}</strong> produit(s) expire(nt) dans les 30 prochains jours.
                    </p>
                    <a href="{{ route('inventory.index') }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-pills me-1"></i>Voir les produits
                    </a>
                </div>
                @endif
                @if($expiringPrescriptions > 0)
                <div class="col-md-6">
                    <p class="mb-2">
                        <strong>{{ $expiringPrescriptions }}</strong> ordonnance(s) expire(nt) dans les 7 prochains jours.
                    </p>
                    <a href="{{ route('prescriptions.index') }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-file-prescription me-1"></i>Voir les ordonnances
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endif
@endsection