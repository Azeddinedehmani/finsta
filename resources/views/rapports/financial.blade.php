@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <h2><i class="fas fa-euro-sign me-2"></i>Rapport financier</h2>
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
        <form action="{{ route('reports.financial') }}" method="GET" class="row g-3">
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

<!-- Résumé financier -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Revenus</h6>
                        <h4 class="mb-0">{{ number_format($revenue, 2) }} €</h4>
                    </div>
                    <i class="fas fa-arrow-up fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Dépenses</h6>
                        <h4 class="mb-0">{{ number_format($expenses, 2) }} €</h4>
                    </div>
                    <i class="fas fa-arrow-down fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card {{ $profit >= 0 ? 'bg-primary' : 'bg-warning' }} text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">{{ $profit >= 0 ? 'Bénéfice' : 'Perte' }}</h6>
                        <h4 class="mb-0">{{ number_format(abs($profit), 2) }} €</h4>
                    </div>
                    <i class="fas {{ $profit >= 0 ? 'fa-chart-line' : 'fa-chart-line-down' }} fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- Évolution mensuelle -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-line me-2"></i>Évolution sur 12 mois
                </h5>
            </div>
            <div class="card-body">
                <canvas id="financialChart" height="100"></canvas>
            </div>
        </div>

        <!-- Top marges par produit -->
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">
                    <i class="fas fa-percentage me-2"></i>Top 20 - Marges par produit
                </h5>
            </div>
            <div class="card-body p-0">
                @if($productMargins->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Produit</th>
                                    <th class="text-center">Qté vendue</th>
                                    <th class="text-end">CA</th>
                                    <th class="text-end">Coût</th>
                                    <th class="text-end">Marge</th>
                                    <th class="text-center">% Marge</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($productMargins as $product)
                                    @php
                                        $marginPercent = $product->total_cost > 0 ? (($product->total_margin / $product->total_cost) * 100) : 0;
                                    @endphp
                                    <tr>
                                        <td><strong>{{ $product->name }}</strong></td>
                                        <td class="text-center">{{ $product->total_sold }}</td>
                                        <td class="text-end">{{ number_format($product->total_revenue, 2) }} €</td>
                                        <td class="text-end">{{ number_format($product->total_cost, 2) }} €</td>
                                        <td class="text-end">
                                            <strong class="{{ $product->total_margin >= 0 ? 'text-success' : 'text-danger' }}">
                                                {{ number_format($product->total_margin, 2) }} €
                                            </strong>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge {{ $marginPercent >= 50 ? 'bg-success' : ($marginPercent >= 25 ? 'bg-warning text-dark' : 'bg-danger') }}">
                                                {{ number_format($marginPercent, 1) }}%
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th>Total</th>
                                    <th class="text-center">{{ $productMargins->sum('total_sold') }}</th>
                                    <th class="text-end">{{ number_format($productMargins->sum('total_revenue'), 2) }} €</th>
                                    <th class="text-end">{{ number_format($productMargins->sum('total_cost'), 2) }} €</th>
                                    <th class="text-end">
                                        <strong>{{ number_format($productMargins->sum('total_margin'), 2) }} €</strong>
                                    </th>
                                    <th class="text-center">
                                        @php
                                            $totalMarginPercent = $productMargins->sum('total_cost') > 0 ? 
                                                (($productMargins->sum('total_margin') / $productMargins->sum('total_cost')) * 100) : 0;
                                        @endphp
                                        <strong>{{ number_format($totalMarginPercent, 1) }}%</strong>
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-chart-bar fa-3x mb-3"></i>
                        <p class="mb-0">Aucune donnée de marge pour cette période</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Indicateurs de performance -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">
                    <i class="fas fa-tachometer-alt me-2"></i>Indicateurs clés
                </h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-12 mb-3">
                        <div class="border rounded p-3">
                            @php
                                $profitMargin = $revenue > 0 ? ($profit / $revenue) * 100 : 0;
                            @endphp
                            <h4 class="{{ $profitMargin >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ number_format($profitMargin, 1) }}%
                            </h4>
                            <small class="text-muted">Marge nette</small>
                        </div>
                    </div>
                    <div class="col-12 mb-3">
                        <div class="border rounded p-3">
                            @php
                                $roi = $expenses > 0 ? ($profit / $expenses) * 100 : 0;
                            @endphp
                            <h4 class="{{ $roi >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ number_format($roi, 1) }}%
                            </h4>
                            <small class="text-muted">ROI</small>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="border rounded p-3">
                            @php
                                $profitPerDay = $profit / max(1, \Carbon\Carbon::parse($dateFrom)->diffInDays(\Carbon\Carbon::parse($dateTo)) + 1);
                            @endphp
                            <h4 class="{{ $profitPerDay >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ number_format($profitPerDay, 0) }} €
                            </h4>
                            <small class="text-muted">{{ $profitPerDay >= 0 ? 'Bénéfice' : 'Perte' }} / jour</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Analyse des marges -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-pie me-2"></i>Répartition des marges
                </h5>
            </div>
            <div class="card-body">
                @if($productMargins->count() > 0)
                    <canvas id="marginChart" height="200"></canvas>
                    <div class="mt-3">
                        @php
                            $highMarginProducts = $productMargins->filter(function($product) {
                                return $product->total_cost > 0 && (($product->total_margin / $product->total_cost) * 100) >= 50;
                            })->count();
                            $lowMarginProducts = $productMargins->filter(function($product) {
                                return $product->total_cost > 0 && (($product->total_margin / $product->total_cost) * 100) < 25;
                            })->count();
                        @endphp
                        <small class="text-muted">
                            <div class="d-flex justify-content-between">
                                <span>Marge élevée (≥50%)</span>
                                <span class="badge bg-success">{{ $highMarginProducts }}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Marge faible (<25%)</span>
                                <span class="badge bg-danger">{{ $lowMarginProducts }}</span>
                            </div>
                        </small>
                    </div>
                @else
                    <div class="text-center text-muted">
                        <i class="fas fa-chart-pie fa-2x mb-2"></i>
                        <p class="mb-0">Pas de données</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Actions recommandées -->
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h5 class="card-title mb-0">
                    <i class="fas fa-lightbulb me-2"></i>Recommandations
                </h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    @if($profit < 0)
                        <li class="mb-2">
                            <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                            <strong>Attention :</strong> Période déficitaire. Analyser les coûts.
                        </li>
                    @endif
                    
                    @if($profitMargin < 10)
                        <li class="mb-2">
                            <i class="fas fa-chart-line text-warning me-2"></i>
                            Marge nette faible. Optimiser les prix de vente.
                        </li>
                    @endif
                    
                    @if($productMargins->where('total_margin', '<', 0)->count() > 0)
                        <li class="mb-2">
                            <i class="fas fa-minus-circle text-danger me-2"></i>
                            {{ $productMargins->where('total_margin', '<', 0)->count() }} produit(s) à marge négative.
                        </li>
                    @endif
                    
                    @if($profit >= 0 && $profitMargin >= 15)
                        <li class="mb-0">
                            <i class="fas fa-thumbs-up text-success me-2"></i>
                            Excellente performance financière !
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Graphique d'évolution mensuelle
    const financialCtx = document.getElementById('financialChart').getContext('2d');
    const revenueData = @json($revenueByMonth);
    const expenseData = @json($expensesByMonth);
    
    // Fusion des données par mois
    const months = [...new Set([...revenueData.map(r => r.month), ...expenseData.map(e => e.month)])].sort();
    
    const revenueByMonth = months.map(month => {
        const found = revenueData.find(r => r.month === month);
        return found ? parseFloat(found.revenue) : 0;
    });
    
    const expensesByMonth = months.map(month => {
        const found = expenseData.find(e => e.month === month);
        return found ? parseFloat(found.expenses) : 0;
    });
    
    const profitByMonth = months.map((month, index) => revenueByMonth[index] - expensesByMonth[index]);
    
    new Chart(financialCtx, {
        type: 'line',
        data: {
            labels: months.map(month => {
                const [year, monthNum] = month.split('-');
                return new Date(year, monthNum - 1).toLocaleDateString('fr-FR', { 
                    year: 'numeric', 
                    month: 'short' 
                });
            }),
            datasets: [{
                label: 'Revenus',
                data: revenueByMonth,
                borderColor: 'rgb(34, 197, 94)',
                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                tension: 0.1
            }, {
                label: 'Dépenses',
                data: expensesByMonth,
                borderColor: 'rgb(239, 68, 68)',
                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                tension: 0.1
            }, {
                label: 'Profit/Perte',
                data: profitByMonth,
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.1,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Évolution financière sur 12 mois'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString('fr-FR', {
                                style: 'currency',
                                currency: 'EUR',
                                minimumFractionDigits: 0
                            });
                        }
                    }
                }
            }
        }
    });

    // Graphique des marges (si des données existent)
    @if($productMargins->count() > 0)
    const marginCtx = document.getElementById('marginChart').getContext('2d');
    const marginData = @json($productMargins->take(5)); // Top 5 seulement pour la lisibilité
    
    new Chart(marginCtx, {
        type: 'doughnut',
        data: {
            labels: marginData.map(item => item.name),
            datasets: [{
                data: marginData.map(item => parseFloat(item.total_margin)),
                backgroundColor: [
                    '#10B981', '#3B82F6', '#F59E0B', '#EF4444', '#8B5CF6'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 10
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': ' + 
                                   context.parsed.toLocaleString('fr-FR', {
                                       style: 'currency',
                                       currency: 'EUR'
                                   });
                        }
                    }
                }
            }
        }
    });
    @endif
});
</script>
@endsection