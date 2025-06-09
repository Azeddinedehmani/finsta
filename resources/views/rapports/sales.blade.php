@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <h2><i class="fas fa-shopping-cart me-2"></i>Rapport des ventes</h2>
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

<!-- Filtres -->
<div class="card mb-4">
    <div class="card-header bg-light">
        <h5 class="card-title mb-0">Filtres de période</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('reports.sales') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="date_from" class="form-label">Date de début</label>
                <input type="date" class="form-control" id="date_from" name="date_from" value="{{ $dateFrom }}">
            </div>
            <div class="col-md-3">
                <label for="date_to" class="form-label">Date de fin</label>
                <input type="date" class="form-control" id="date_to" name="date_to" value="{{ $dateTo }}">
            </div>
            <div class="col-md-3">
                <label for="group_by" class="form-label">Grouper par</label>
                <select class="form-select" id="group_by" name="group_by">
                    <option value="day" {{ $groupBy == 'day' ? 'selected' : '' }}>Jour</option>
                    <option value="week" {{ $groupBy == 'week' ? 'selected' : '' }}>Semaine</option>
                    <option value="month" {{ $groupBy == 'month' ? 'selected' : '' }}>Mois</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-1"></i> Filtrer
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Statistiques générales -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Chiffre d'affaires</h6>
                        <h4 class="mb-0">{{ number_format($totalSales, 2) }} €</h4>
                    </div>
                    <i class="fas fa-euro-sign fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Nombre de ventes</h6>
                        <h4 class="mb-0">{{ $totalTransactions }}</h4>
                    </div>
                    <i class="fas fa-shopping-cart fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Panier moyen</h6>
                        <h4 class="mb-0">{{ number_format($averageTransaction, 2) }} €</h4>
                    </div>
                    <i class="fas fa-chart-line fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- Graphique des ventes par période -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Évolution des ventes</h5>
            </div>
            <div class="card-body">
                <canvas id="salesChart" height="100"></canvas>
            </div>
        </div>

        <!-- Top produits vendus -->
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Top 10 des produits vendus</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Rang</th>
                                <th>Produit</th>
                                <th class="text-center">Quantité vendue</th>
                                <th class="text-end">Chiffre d'affaires</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topProducts as $index => $product)
                                <tr>
                                    <td>
                                        <span class="badge {{ $index < 3 ? 'bg-warning' : 'bg-secondary' }}">
                                            #{{ $index + 1 }}
                                        </span>
                                    </td>
                                    <td>
                                        <strong>{{ $product->name }}</strong>
                                    </td>
                                    <td class="text-center">{{ $product->total_quantity }}</td>
                                    <td class="text-end">
                                        <strong>{{ number_format($product->total_revenue, 2) }} €</strong>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        Aucune vente pour cette période
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Ventes par utilisateur -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Ventes par vendeur</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Vendeur</th>
                                <th class="text-end">CA</th>
                                <th class="text-center">Nb</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($salesByUser as $userSale)
                                <tr>
                                    <td>{{ $userSale->name }}</td>
                                    <td class="text-end">{{ number_format($userSale->total_sales, 0) }} €</td>
                                    <td class="text-center">{{ $userSale->total_transactions }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Répartition par mode de paiement -->
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Modes de paiement</h5>
            </div>
            <div class="card-body">
                <canvas id="paymentChart" height="200"></canvas>
            </div>
            <div class="card-footer p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <tbody>
                            @foreach($salesByPaymentMethod as $payment)
                                <tr>
                                    <td>
                                        @switch($payment->payment_method)
                                            @case('cash')
                                                <i class="fas fa-money-bill text-success me-2"></i>Espèces
                                                @break
                                            @case('card')
                                                <i class="fas fa-credit-card text-primary me-2"></i>Carte
                                                @break
                                            @case('insurance')
                                                <i class="fas fa-shield-alt text-info me-2"></i>Assurance
                                                @break
                                            @default
                                                <i class="fas fa-question text-muted me-2"></i>{{ ucfirst($payment->payment_method) }}
                                        @endswitch
                                    </td>
                                    <td class="text-end">{{ number_format($payment->total, 2) }} €</td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary">{{ $payment->count }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Graphique des ventes par période
    const salesCtx = document.getElementById('salesChart').getContext('2d');
    const salesData = @json($salesByPeriod);
    
    new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: salesData.map(item => {
                const date = new Date(item.period);
                return date.toLocaleDateString('fr-FR');
            }),
            datasets: [{
                label: 'Chiffre d\'affaires (€)',
                data: salesData.map(item => item.total),
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1,
                fill: true
            }, {
                label: 'Nombre de ventes',
                data: salesData.map(item => item.count),
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                tension: 0.1,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Évolution des ventes'
                }
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Chiffre d\'affaires (€)'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Nombre de ventes'
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            }
        }
    });

    // Graphique des modes de paiement (doughnut)
    const paymentCtx = document.getElementById('paymentChart').getContext('2d');
    const paymentData = @json($salesByPaymentMethod);
    
    const colors = ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF'];
    
    new Chart(paymentCtx, {
        type: 'doughnut',
        data: {
            labels: paymentData.map(item => {
                switch(item.payment_method) {
                    case 'cash': return 'Espèces';
                    case 'card': return 'Carte';
                    case 'insurance': return 'Assurance';
                    default: return item.payment_method.charAt(0).toUpperCase() + item.payment_method.slice(1);
                }
            }),
            datasets: [{
                data: paymentData.map(item => item.total),
                backgroundColor: colors.slice(0, paymentData.length),
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
});
</script>
@endsection