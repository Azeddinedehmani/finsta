@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <h2><i class="fas fa-boxes me-2"></i>Rapport d'inventaire</h2>
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

<!-- Statistiques générales -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Total produits</h6>
                        <h4 class="mb-0">{{ $totalProducts }}</h4>
                    </div>
                    <i class="fas fa-box fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Valeur du stock</h6>
                        <h4 class="mb-0">{{ number_format($totalStockValue, 0) }} €</h4>
                    </div>
                    <i class="fas fa-euro-sign fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Stock moyen</h6>
                        <h4 class="mb-0">{{ number_format($averageStockLevel, 1) }}</h4>
                    </div>
                    <i class="fas fa-layer-group fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- Produits avec stock faible -->
        <div class="card mb-4">
            <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Produits avec stock faible ({{ $lowStockProducts->count() }})
                </h5>
                @if($lowStockProducts->count() > 0)
                    <span class="badge bg-dark">Action requise</span>
                @endif
            </div>
            <div class="card-body p-0">
                @if($lowStockProducts->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Produit</th>
                                    <th>Catégorie</th>
                                    <th>Fournisseur</th>
                                    <th class="text-center">Stock actuel</th>
                                    <th class="text-center">Seuil</th>
                                    <th class="text-center">Criticité</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($lowStockProducts as $product)
                                    <tr class="{{ $product->stock_quantity == 0 ? 'table-danger' : 'table-warning' }}">
                                        <td>
                                            <div>
                                                <strong>{{ $product->name }}</strong>
                                                @if($product->dosage)
                                                    <br><small class="text-muted">{{ $product->dosage }}</small>
                                                @endif
                                            </div>
                                        </td>
                                        <td>{{ $product->category ? $product->category->name : 'N/A' }}</td>
                                        <td>{{ $product->supplier ? $product->supplier->name : 'N/A' }}</td>
                                        <td class="text-center">
                                            @if($product->stock_quantity == 0)
                                                <span class="badge bg-danger">0</span>
                                            @else
                                                <span class="badge bg-warning text-dark">{{ $product->stock_quantity }}</span>
                                            @endif
                                        </td>
                                        <td class="text-center">{{ $product->stock_threshold }}</td>
                                        <td class="text-center">
                                            @php
                                                $criticityLevel = $product->stock_quantity == 0 ? 'URGENT' : 
                                                                ($product->stock_quantity <= $product->stock_threshold * 0.5 ? 'ÉLEVÉ' : 'MOYEN');
                                                $criticityClass = $product->stock_quantity == 0 ? 'bg-danger' : 
                                                                ($product->stock_quantity <= $product->stock_threshold * 0.5 ? 'bg-warning text-dark' : 'bg-info');
                                            @endphp
                                            <span class="badge {{ $criticityClass }}">{{ $criticityLevel }}</span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="{{ route('inventory.show', $product->id) }}" class="btn btn-sm btn-info text-white">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if(Auth::user()->isAdmin())
                                                    <a href="{{ route('purchases.create', ['supplier_id' => $product->supplier_id]) }}" class="btn btn-sm btn-success">
                                                        <i class="fas fa-shopping-cart"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center text-success py-4">
                        <i class="fas fa-check-circle fa-3x mb-3"></i>
                        <h5>Excellent !</h5>
                        <p class="mb-0">Tous les produits ont un niveau de stock suffisant.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Produits qui expirent bientôt -->
        <div class="card">
            <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-calendar-times me-2"></i>
                    Produits expirant dans 30 jours ({{ $expiringProducts->count() }})
                </h5>
                @if($expiringProducts->count() > 0)
                    <span class="badge bg-warning text-dark">À surveiller</span>
                @endif
            </div>
            <div class="card-body p-0">
                @if($expiringProducts->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Produit</th>
                                    <th>Catégorie</th>
                                    <th>Stock</th>
                                    <th>Date d'expiration</th>
                                    <th>Jours restants</th>
                                    <th>Valeur</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($expiringProducts as $product)
                                    @php
                                        $daysLeft = $product->expiry_date->diffInDays(now());
                                        $urgencyClass = $daysLeft <= 7 ? 'table-danger' : ($daysLeft <= 15 ? 'table-warning' : '');
                                    @endphp
                                    <tr class="{{ $urgencyClass }}">
                                        <td>
                                            <div>
                                                <strong>{{ $product->name }}</strong>
                                                @if($product->dosage)
                                                    <br><small class="text-muted">{{ $product->dosage }}</small>
                                                @endif
                                            </div>
                                        </td>
                                        <td>{{ $product->category ? $product->category->name : 'N/A' }}</td>
                                        <td class="text-center">{{ $product->stock_quantity }}</td>
                                        <td>{{ $product->expiry_date->format('d/m/Y') }}</td>
                                        <td class="text-center">
                                            @if($daysLeft <= 7)
                                                <span class="badge bg-danger">{{ $daysLeft }} jour(s)</span>
                                            @elseif($daysLeft <= 15)
                                                <span class="badge bg-warning text-dark">{{ $daysLeft }} jours</span>
                                            @else
                                                <span class="badge bg-info">{{ $daysLeft }} jours</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            {{ number_format($product->stock_quantity * $product->purchase_price, 2) }} €
                                        </td>
                                        <td>
                                            <a href="{{ route('inventory.show', $product->id) }}" class="btn btn-sm btn-info text-white">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center text-success py-4">
                        <i class="fas fa-check-circle fa-3x mb-3"></i>
                        <h5>Parfait !</h5>
                        <p class="mb-0">Aucun produit n'expire dans les 30 prochains jours.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Valeur par catégorie -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-pie me-2"></i>Valeur par catégorie
                </h5>
            </div>
            <div class="card-body">
                <canvas id="categoriesChart" height="300"></canvas>
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
                    <a href="{{ route('inventory.index', ['stock_status' => 'low']) }}" class="btn btn-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Voir tous les stocks faibles
                    </a>
                    
                    <a href="{{ route('inventory.index', ['stock_status' => 'out']) }}" class="btn btn-danger">
                        <i class="fas fa-times-circle me-2"></i>
                        Voir les ruptures de stock
                    </a>
                    
                    <a href="{{ route('inventory.create') }}" class="btn btn-success">
                        <i class="fas fa-plus me-2"></i>
                        Ajouter un produit
                    </a>
                    
                    @if(Auth::user()->isAdmin())
                        <a href="{{ route('purchases.create') }}" class="btn btn-primary">
                            <i class="fas fa-shopping-cart me-2"></i>
                            Nouvelle commande
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Résumé par catégorie -->
@if($categoriesValue->count() > 0)
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">
                    <i class="fas fa-list me-2"></i>Résumé par catégorie
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Catégorie</th>
                                <th class="text-center">Quantité totale</th>
                                <th class="text-end">Valeur totale</th>
                                <th class="text-end">% du stock total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categoriesValue as $category)
                                <tr>
                                    <td><strong>{{ $category->category_name }}</strong></td>
                                    <td class="text-center">{{ number_format($category->total_quantity) }}</td>
                                    <td class="text-end">{{ number_format($category->total_value, 2) }} €</td>
                                    <td class="text-end">
                                        @php
                                            $percentage = $totalStockValue > 0 ? ($category->total_value / $totalStockValue) * 100 : 0;
                                        @endphp
                                        {{ number_format($percentage, 1) }}%
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th>Total</th>
                                <th class="text-center">{{ number_format($categoriesValue->sum('total_quantity')) }}</th>
                                <th class="text-end">{{ number_format($totalStockValue, 2) }} €</th>
                                <th class="text-end">100%</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Graphique en secteurs pour les catégories
    const categoriesCtx = document.getElementById('categoriesChart').getContext('2d');
    const categoriesData = @json($categoriesValue);
    
    const colors = [
        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
        '#FF9F40', '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0'
    ];
    
    new Chart(categoriesCtx, {
        type: 'doughnut',
        data: {
            labels: categoriesData.map(item => item.category_name),
            datasets: [{
                data: categoriesData.map(item => parseFloat(item.total_value)),
                backgroundColor: colors.slice(0, categoriesData.length),
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        usePointStyle: true
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return context.label + ': ' + value.toLocaleString('fr-FR', {
                                style: 'currency',
                                currency: 'EUR'
                            }) + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });
});
</script>
@endsection