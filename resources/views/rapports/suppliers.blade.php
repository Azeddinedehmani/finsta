@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <h2><i class="fas fa-truck me-2"></i>Rapport des fournisseurs</h2>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('reports.index') }}" class="btn btn-secondary me-2">
            <i class="fas fa-arrow-left me-1"></i> Retour aux rapports
        </a>
        <button type="button" class="btn btn-primary" onclick="window.print()">
            <i class="fas fa-print me-1"></i> Imprimer
        </button>
    </div>
</div>

<!-- Filtres de période -->
<div class="card mb-4">
    <div class="card-header bg-light">
        <h5 class="card-title mb-0">Période d'analyse</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('reports.suppliers') }}" method="GET" class="row g-3">
            <div class="col-md-4">
                <label for="date_from" class="form-label">Date de début</label>
                <input type="date" class="form-control" id="date_from" name="date_from" value="{{ $dateFrom }}">
            </div>
            <div class="col-md-4">
                <label for="date_to" class="form-label">Date de fin</label>
                <input type="date" class="form-control" id="date_to" name="date_to" value="{{ $dateTo }}">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-1"></i> Analyser
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Statistiques générales -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Total fournisseurs</h6>
                        <h4 class="mb-0">{{ $totalSuppliers }}</h4>
                    </div>
                    <i class="fas fa-truck fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Fournisseurs actifs</h6>
                        <h4 class="mb-0">{{ $activeSuppliers }}</h4>
                    </div>
                    <i class="fas fa-check-circle fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Avec produits</h6>
                        <h4 class="mb-0">{{ $suppliersWithProducts }}</h4>
                    </div>
                    <i class="fas fa-boxes fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Sans produits</h6>
                        <h4 class="mb-0">{{ $suppliersWithoutProducts }}</h4>
                    </div>
                    <i class="fas fa-exclamation-triangle fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- CORRIGÉ : Top fournisseurs par valeur de stock -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-bar me-2"></i>Top fournisseurs par valeur de stock
                </h5>
            </div>
            <div class="card-body p-0">
                @if($suppliersByStockValue->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Rang</th>
                                    <th>Fournisseur</th>
                                    <th>Contact</th>
                                    <th class="text-center">Nb produits</th>
                                    <th class="text-center">Stock total</th>
                                    <th class="text-end">Valeur stock</th>
                                    <th class="text-center">Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($suppliersByStockValue as $index => $supplier)
                                    <tr>
                                        <td>
                                            <span class="badge {{ $index < 3 ? 'bg-warning' : 'bg-secondary' }}">
                                                #{{ $index + 1 }}
                                            </span>
                                        </td>
                                        <td>
                                            <div>
                                                <strong>{{ $supplier->name }}</strong>
                                                @if($supplier->phone_number)
                                                    <br><small class="text-muted">{{ $supplier->phone_number }}</small>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                {{ $supplier->contact_person ?? 'N/A' }}
                                                @if($supplier->email)
                                                    <br><small class="text-muted">{{ $supplier->email }}</small>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="text-center">{{ $supplier->products_count }}</td>
                                        <td class="text-center">{{ number_format($supplier->total_stock_quantity) }}</td>
                                        <td class="text-end">
                                            <strong>{{ number_format($supplier->total_stock_value, 2) }} €</strong>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge {{ $supplier->active ? 'bg-success' : 'bg-danger' }}">
                                                {{ $supplier->active ? 'Actif' : 'Inactif' }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="4">Total</th>
                                    <th class="text-center">{{ number_format($suppliersByStockValue->sum('total_stock_quantity')) }}</th>
                                    <th class="text-end">{{ number_format($suppliersByStockValue->sum('total_stock_value'), 2) }} €</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-truck fa-3x mb-3"></i>
                        <p class="mb-0">Aucun fournisseur avec du stock</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- NOUVEAU : Commandes par fournisseur -->
        @if($purchasesBySupplier->count() > 0)
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-shopping-cart me-2"></i>
                    Commandes par fournisseur (période sélectionnée)
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Fournisseur</th>
                                <th>Contact</th>
                                <th class="text-center">Nb commandes</th>
                                <th class="text-end">Total commandes</th>
                                <th class="text-end">Commande moyenne</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchasesBySupplier as $supplier)
                                <tr>
                                    <td><strong>{{ $supplier->name }}</strong></td>
                                    <td>{{ $supplier->contact_person ?? 'N/A' }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-primary">{{ $supplier->orders_count }}</span>
                                    </td>
                                    <td class="text-end">
                                        <strong>{{ number_format($supplier->total_amount, 2) }} €</strong>
                                    </td>
                                    <td class="text-end">
                                        {{ number_format($supplier->average_amount, 2) }} €
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        <!-- Commandes en retard -->
        @if($overduePurchases->count() > 0)
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Commandes en retard ({{ $overduePurchases->count() }})
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>N° Commande</th>
                                <th>Fournisseur</th>
                                <th>Date commande</th>
                                <th>Date prévue</th>
                                <th class="text-end">Montant</th>
                                <th class="text-center">Retard</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($overduePurchases as $purchase)
                                <tr>
                                    <td>
                                        @if(Auth::user()->isAdmin())
                                            <a href="{{ route('purchases.show', $purchase->id) }}" class="text-decoration-none">
                                                {{ $purchase->purchase_number }}
                                            </a>
                                        @else
                                            {{ $purchase->purchase_number }}
                                        @endif
                                    </td>
                                    <td>{{ $purchase->supplier->name }}</td>
                                    <td>{{ $purchase->order_date->format('d/m/Y') }}</td>
                                    <td>{{ $purchase->expected_date->format('d/m/Y') }}</td>
                                    <td class="text-end">{{ number_format($purchase->total_amount, 2) }} €</td>
                                    <td class="text-center">
                                        <span class="badge bg-danger">
                                            {{ $purchase->expected_date->diffInDays(now()) }} jour(s)
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="col-md-4">
        <!-- Top par nombre de produits -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">
                    <i class="fas fa-list me-2"></i>Top par nombre de produits
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Fournisseur</th>
                                <th class="text-center">Produits</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topSuppliersByProducts->take(10) as $supplier)
                                <tr>
                                    <td>
                                        <strong>{{ $supplier->name }}</strong>
                                        <br><small class="text-muted">{{ $supplier->contact_person ?? 'N/A' }}</small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary">{{ $supplier->products_count }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Performance des fournisseurs -->
        @if($supplierPerformance->count() > 0)
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-line me-2"></i>Performance livraisons
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Fournisseur</th>
                                <th class="text-center">À temps</th>
                                <th class="text-center">Retard moy.</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($supplierPerformance->take(8) as $performance)
                                <tr>
                                    <td>
                                        <strong>{{ $performance->supplier_name }}</strong>
                                        <br><small class="text-muted">{{ $performance->total_orders }} commande(s)</small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $performance->on_time_percentage >= 80 ? 'bg-success' : ($performance->on_time_percentage >= 60 ? 'bg-warning text-dark' : 'bg-danger') }}">
                                            {{ number_format($performance->on_time_percentage, 1) }}%
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if($performance->average_delay > 0)
                                            <span class="badge bg-warning text-dark">
                                                {{ $performance->average_delay }} j
                                            </span>
                                        @else
                                            <span class="badge bg-success">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        <!-- Fournisseurs avec stock faible -->
        @if($suppliersWithLowStock->count() > 0)
        <div class="card mb-4">
            <div class="card-header bg-warning text-dark">
                <h5 class="card-title mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>Stock faible
                </h5>
            </div>
            <div class="card-body">
                @foreach($suppliersWithLowStock->take(5) as $supplier)
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <strong>{{ $supplier->name }}</strong>
                            <br><small class="text-muted">{{ $supplier->contact_person ?? 'N/A' }}</small>
                        </div>
                        <span class="badge bg-warning text-dark">
                            {{ $supplier->low_stock_products_count }} produit(s)
                        </span>
                    </div>
                    @if(!$loop->last)<hr class="my-2">@endif
                @endforeach
            </div>
        </div>
        @endif

        <!-- Commandes en cours -->
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-clock me-2"></i>Commandes en cours
                </h5>
            </div>
            <div class="card-body">
                @if($pendingPurchases->count() > 0)
                    @foreach($pendingPurchases->take(5) as $supplierName => $purchases)
                        <div class="mb-3">
                            <h6 class="mb-2">{{ $supplierName }}</h6>
                            @foreach($purchases as $purchase)
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <small>{{ $purchase->purchase_number }}</small>
                                    <span class="badge bg-info">{{ number_format($purchase->total_amount, 0) }} €</span>
                                </div>
                            @endforeach
                        </div>
                        @if(!$loop->last)<hr>@endif
                    @endforeach
                @else
                    <div class="text-center text-muted">
                        <i class="fas fa-check-circle fa-2x mb-2"></i>
                        <p class="mb-0">Aucune commande en cours</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Actions rapides -->
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">
                    <i class="fas fa-bolt me-2"></i>Actions rapides
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('suppliers.index') }}" class="btn btn-primary">
                        <i class="fas fa-truck me-2"></i>
                        Voir tous les fournisseurs
                    </a>
                    
                    @if(Auth::user()->isAdmin())
                        <a href="{{ route('suppliers.create') }}" class="btn btn-success">
                            <i class="fas fa-plus me-2"></i>
                            Ajouter un fournisseur
                        </a>
                        
                        <a href="{{ route('purchases.create') }}" class="btn btn-warning">
                            <i class="fas fa-shopping-cart me-2"></i>
                            Nouvelle commande
                        </a>
                        
                        <a href="{{ route('purchases.index', ['status' => 'pending']) }}" class="btn btn-info">
                            <i class="fas fa-clock me-2"></i>
                            Commandes en attente
                        </a>
                    @endif
                </div>
            </div>
            <div class="card-footer">
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Période analysée : {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}
                </small>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Ici vous pouvez ajouter des graphiques si nécessaire
});
</script>
@endsection