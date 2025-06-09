@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <h2><i class="fas fa-chart-bar me-2"></i>Tableau de bord - Rapports</h2>
    </div>
    <div class="col-md-4 text-end">
        <div class="btn-group">
            <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                <i class="fas fa-download me-1"></i> Exporter
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#" onclick="window.print()"><i class="fas fa-print me-2"></i>Imprimer</a></li>
                <li><a class="dropdown-item" href="#"><i class="fas fa-file-pdf me-2"></i>Export PDF</a></li>
                <li><a class="dropdown-item" href="#"><i class="fas fa-file-excel me-2"></i>Export Excel</a></li>
            </ul>
        </div>
    </div>
</div>

<!-- Navigation des rapports - VERSION UNIQUE -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body p-3">
                <div class="row text-center">
                    <div class="col-md-2">
                        <a href="{{ route('reports.sales') }}" class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                            <i class="fas fa-shopping-cart fa-2x mb-2"></i>
                            <span>Ventes</span>
                        </a>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('reports.inventory') }}" class="btn btn-outline-success w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                            <i class="fas fa-boxes fa-2x mb-2"></i>
                            <span>Inventaire</span>
                        </a>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('reports.clients') }}" class="btn btn-outline-info w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                            <i class="fas fa-users fa-2x mb-2"></i>
                            <span>Clients</span>
                        </a>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('reports.prescriptions') }}" class="btn btn-outline-warning w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                            <i class="fas fa-prescription-bottle fa-2x mb-2"></i>
                            <span>Ordonnances</span>
                        </a>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('reports.financial') }}" class="btn btn-outline-danger w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                            <i class="fas fa-euro-sign fa-2x mb-2"></i>
                            <span>Financier</span>
                        </a>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('reports.suppliers') }}" class="btn btn-outline-secondary w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                            <i class="fas fa-truck fa-2x mb-2"></i>
                            <span>Fournisseurs</span>
                        </a>
                    </div>
                </div>
                @if(Auth::user()->isAdmin())
                <div class="row text-center mt-3">
                    <div class="col-md-2">
                        <a href="{{ route('reports.users') }}" class="btn btn-outline-dark w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                            <i class="fas fa-users-cog fa-2x mb-2"></i>
                            <span>Utilisateurs</span>
                        </a>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Statistiques de vente -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Aujourd'hui</h6>
                        <h4 class="mb-0">{{ number_format($salesStats['today'], 2) }} €</h4>
                    </div>
                    <i class="fas fa-calendar-day fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Cette semaine</h6>
                        <h4 class="mb-0">{{ number_format($salesStats['week'], 2) }} €</h4>
                    </div>
                    <i class="fas fa-calendar-week fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Ce mois</h6>
                        <h4 class="mb-0">{{ number_format($salesStats['month'], 2) }} €</h4>
                    </div>
                    <i class="fas fa-calendar-alt fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Cette année</h6>
                        <h4 class="mb-0">{{ number_format($salesStats['year'], 2) }} €</h4>
                    </div>
                    <i class="fas fa-chart-line fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <!-- Statistiques inventaire -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">
                    <i class="fas fa-boxes me-2"></i>État de l'inventaire
                </h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <div class="border rounded p-3">
                            <h3 class="text-primary">{{ $inventoryStats['total_products'] }}</h3>
                            <small class="text-muted">Total produits</small>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="border rounded p-3">
                            <h3 class="text-warning">{{ $inventoryStats['low_stock'] }}</h3>
                            <small class="text-muted">Stock faible</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border rounded p-3">
                            <h3 class="text-danger">{{ $inventoryStats['out_of_stock'] }}</h3>
                            <small class="text-muted">Rupture</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border rounded p-3">
                            <h3 class="text-info">{{ $inventoryStats['expiring_soon'] }}</h3>
                            <small class="text-muted">Expire bientôt</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer text-center">
                <a href="{{ route('reports.inventory') }}" class="btn btn-sm btn-primary">
                    Voir le rapport détaillé
                </a>
            </div>
        </div>

        <!-- Statistiques clients -->
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">
                    <i class="fas fa-users me-2"></i>Clients
                </h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <div class="border rounded p-3">
                            <h3 class="text-success">{{ $clientStats['total_clients'] }}</h3>
                            <small class="text-muted">Total clients</small>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="border rounded p-3">
                            <h3 class="text-primary">{{ $clientStats['active_clients'] }}</h3>
                            <small class="text-muted">Actifs</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border rounded p-3">
                            <h3 class="text-info">{{ $clientStats['new_this_month'] }}</h3>
                            <small class="text-muted">Nouveaux ce mois</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border rounded p-3">
                            <h3 class="text-warning">{{ $clientStats['with_allergies'] }}</h3>
                            <small class="text-muted">Avec allergies</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer text-center">
                <a href="{{ route('reports.clients') }}" class="btn btn-sm btn-primary">
                    Voir le rapport détaillé
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        @if(Auth::user()->isAdmin() && isset($purchaseStats))
        <!-- Statistiques achats (Admin seulement) -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">
                    <i class="fas fa-shopping-cart me-2"></i>Achats (Admin)
                </h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-4 mb-3">
                        <div class="border rounded p-3">
                            <h3 class="text-warning">{{ $purchaseStats['pending_purchases'] }}</h3>
                            <small class="text-muted">En attente</small>
                        </div>
                    </div>
                    <div class="col-4 mb-3">
                        <div class="border rounded p-3">
                            <h3 class="text-success">{{ number_format($purchaseStats['total_this_month'], 0) }} €</h3>
                            <small class="text-muted">Ce mois</small>
                        </div>
                    </div>
                    <div class="col-4 mb-3">
                        <div class="border rounded p-3">
                            <h3 class="text-danger">{{ $purchaseStats['overdue_purchases'] }}</h3>
                            <small class="text-muted">En retard</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer text-center">
                <a href="{{ route('purchases.index') }}" class="btn btn-sm btn-primary">
                    Voir les commandes
                </a>
            </div>
        </div>
        @endif

        <!-- Alertes importantes -->
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>Alertes importantes
                </h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    @if($inventoryStats['out_of_stock'] > 0)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-times-circle text-danger me-2"></i>Produits en rupture de stock</span>
                        <span class="badge bg-danger rounded-pill">{{ $inventoryStats['out_of_stock'] }}</span>
                    </li>
                    @endif
                    
                    @if($inventoryStats['low_stock'] > 0)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-exclamation-triangle text-warning me-2"></i>Stock faible</span>
                        <span class="badge bg-warning text-dark rounded-pill">{{ $inventoryStats['low_stock'] }}</span>
                    </li>
                    @endif
                    
                    @if($inventoryStats['expiring_soon'] > 0)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-calendar-times text-info me-2"></i>Expire dans 30 jours</span>
                        <span class="badge bg-info rounded-pill">{{ $inventoryStats['expiring_soon'] }}</span>
                    </li>
                    @endif
                    
                    @if(Auth::user()->isAdmin() && isset($purchaseStats) && $purchaseStats['overdue_purchases'] > 0)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-truck text-danger me-2"></i>Commandes en retard</span>
                        <span class="badge bg-danger rounded-pill">{{ $purchaseStats['overdue_purchases'] }}</span>
                    </li>
                    @endif
                </ul>
                
                @if($inventoryStats['out_of_stock'] == 0 && $inventoryStats['low_stock'] == 0 && $inventoryStats['expiring_soon'] == 0)
                <div class="text-center text-success">
                    <i class="fas fa-check-circle fa-3x mb-2"></i>
                    <p class="mb-0">Aucune alerte importante pour le moment</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Scripts pour les graphiques si nécessaire
document.addEventListener('DOMContentLoaded', function() {
    // Ici vous pouvez ajouter des graphiques Chart.js
});
</script>
@endsection