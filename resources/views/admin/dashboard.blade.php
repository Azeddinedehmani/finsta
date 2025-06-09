@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="alert bg-accent text-white">
            <h4 class="alert-heading"><i class="fas fa-user-circle me-2"></i>Bienvenue, {{ Auth::user()->name }}!</h4>
            <p>Vous êtes connecté en tant que Responsable. Vous avez un accès complet à toutes les fonctionnalités du système.</p>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="card-title">Ventes du jour</h6>
                    <h3 class="mb-0">{{ number_format($stats['sales_today'], 2) }} €</h3>
                    <small>{{ $stats['sales_count_today'] }} vente(s)</small>
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
                    <h3 class="mb-0">{{ $stats['clients_today'] }}</h3>
                    <small>{{ $stats['total_clients'] }} au total</small>
                </div>
                <i class="fas fa-users fa-2x"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="card-title">Stock critique</h6>
                    <h3 class="mb-0">{{ $stats['products_low_stock'] }}</h3>
                    <small>Produit(s) à commander</small>
                </div>
                <i class="fas fa-exclamation-triangle fa-2x"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="card-title">Expiration proche</h6>
                    <h3 class="mb-0">{{ $stats['products_expiring'] }}</h3>
                    <small>Dans les 30 jours</small>
                </div>
                <i class="fas fa-clock fa-2x"></i>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Ventes des 7 derniers jours</h5>
                <small class="text-muted">{{ number_format($salesChart->sum('total'), 2) }} € total</small>
            </div>
            <div class="card-body">
                <canvas id="salesChart" height="120"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Alertes Stock</h5>
                <a href="{{ route('inventory.index', ['stock_status' => 'low']) }}" class="btn btn-sm btn-outline-primary">
                    Voir tout
                </a>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($lowStockProducts as $product)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">{{ $product->name }}</h6>
                                <small class="{{ $product->stock_quantity <= 0 ? 'text-danger' : 'text-warning' }}">
                                    Stock: {{ $product->stock_quantity }} 
                                    {{ $product->stock_quantity <= 1 ? 'unité' : 'unités' }}
                                </small>
                            </div>
                            <span class="badge {{ $product->stock_quantity <= 0 ? 'bg-danger' : 'bg-warning text-dark' }} rounded-pill">
                                {{ $product->stock_quantity <= 0 ? 'Rupture' : 'Critique' }}
                            </span>
                        </li>
                    @empty
                        <li class="list-group-item text-center text-success">
                            <i class="fas fa-check-circle me-2"></i>
                            Aucune alerte stock
                        </li>
                    @endforelse
                </ul>
            </div>
            @if($lowStockProducts->count() > 0)
                <div class="card-footer text-center">
                    <a href="{{ route('inventory.index', ['stock_status' => 'low']) }}" class="btn btn-sm btn-primary">
                        Voir toutes les alertes ({{ $stats['products_low_stock'] }})
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Ventes récentes</h5>
                <a href="{{ route('sales.index') }}" class="btn btn-sm btn-outline-primary">Voir tout</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Client</th>
                                <th>Produits</th>
                                <th>Montant</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentSales as $sale)
                                <tr>
                                    <td>
                                        {{ $sale->client ? $sale->client->full_name : 'Client anonyme' }}
                                    </td>
                                    <td>
                                        @if($sale->saleItems->count() > 0)
                                            {{ $sale->saleItems->first()->product->name ?? 'Produit supprimé' }}
                                            @if($sale->saleItems->count() > 1)
                                                <small class="text-muted">
                                                    +{{ $sale->saleItems->count() - 1 }} autre(s)
                                                </small>
                                            @endif
                                        @else
                                            <span class="text-muted">Aucun produit</span>
                                        @endif
                                    </td>
                                    <td>
                                        <strong>{{ number_format($sale->total_amount, 2) }} €</strong>
                                        @if($sale->has_prescription)
                                            <br><small class="text-info">
                                                <i class="fas fa-file-prescription"></i> Ordonnance
                                            </small>
                                        @endif
                                    </td>
                                    <td>
                                        <small>
                                            @if($sale->sale_date->isToday())
                                                {{ $sale->sale_date->format('H:i') }}
                                            @elseif($sale->sale_date->isYesterday())
                                                Hier
                                            @else
                                                {{ $sale->sale_date->format('d/m') }}
                                            @endif
                                        </small>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">
                                        Aucune vente récente
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($recentSales->count() > 0)
                <div class="card-footer text-center">
                    <a href="{{ route('sales.index') }}" class="btn btn-sm btn-primary">Voir toutes les ventes</a>
                </div>
            @endif
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Produits à commander</h5>
                <a href="{{ route('purchases.create') }}" class="btn btn-sm btn-success">
                    <i class="fas fa-plus"></i> Nouvelle commande
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Produit</th>
                                <th>Fournisseur</th>
                                <th>Stock actuel</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($productsToOrder as $product)
                                <tr>
                                    <td>
                                        <strong>{{ $product->name }}</strong>
                                        @if($product->dosage)
                                            <br><small class="text-muted">{{ $product->dosage }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $product->supplier ? $product->supplier->name : 'Aucun fournisseur' }}
                                    </td>
                                    <td>
                                        <span class="badge {{ $product->stock_quantity <= 0 ? 'bg-danger' : 'bg-warning text-dark' }}">
                                            {{ $product->stock_quantity }} unités
                                        </span>
                                        <br><small class="text-muted">Seuil: {{ $product->stock_threshold }}</small>
                                    </td>
                                    <td>
                                        @if($product->supplier)
                                            <a href="{{ route('purchases.create', ['supplier_id' => $product->supplier->id]) }}" 
                                               class="btn btn-sm btn-primary">
                                                Commander
                                            </a>
                                        @else
                                            <button class="btn btn-sm btn-secondary" disabled>
                                                Pas de fournisseur
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-success py-3">
                                        <i class="fas fa-check-circle me-2"></i>
                                        Tous les stocks sont corrects
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            @if($productsToOrder->count() > 0)
                <div class="card-footer text-center">
                    <a href="{{ route('inventory.index', ['stock_status' => 'low']) }}" class="btn btn-sm btn-primary">
                        Voir tous les produits à commander
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Additional statistics section -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Résumé de la journée</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-2">
                        <h4 class="text-primary">{{ $stats['prescriptions_pending'] }}</h4>
                        <small>Ordonnances en attente</small>
                    </div>
                    <div class="col-md-2">
                        <h4 class="text-warning">{{ $stats['purchases_pending'] }}</h4>
                        <small>Commandes en attente</small>
                    </div>
                    <div class="col-md-2">
                        <h4 class="text-info">{{ $stats['total_users'] }}</h4>
                        <small>Utilisateurs total</small>
                    </div>
                    <div class="col-md-2">
                        <h4 class="text-success">{{ $stats['active_users'] }}</h4>
                        <small>Utilisateurs actifs</small>
                    </div>
                    <div class="col-md-2">
                        <h4 class="text-secondary">{{ $userActivityChart->sum('count') }}</h4>
                        <small>Activités du mois</small>
                    </div>
                    <div class="col-md-2">
                        <h4 class="text-dark">{{ $stats['products_total'] ?? \App\Models\Product::count() }}</h4>
                        <small>Produits en inventaire</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Sales Chart
const ctx = document.getElementById('salesChart').getContext('2d');
const salesChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: @json($salesChart->pluck('date')->map(function($date) { return \Carbon\Carbon::parse($date)->format('d/m'); })),
        datasets: [{
            label: 'Ventes (€)',
            data: @json($salesChart->pluck('total')),
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.1)',
            tension: 0.1,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return value + ' €';
                    }
                }
            }
        },
        elements: {
            point: {
                radius: 4,
                hoverRadius: 6
            }
        }
    }
});

// Auto-refresh data every 5 minutes
setInterval(function() {
    // You can implement AJAX refresh here if needed
    console.log('Auto-refresh data...');
}, 300000);
</script>
@endsection