@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <h2><i class="fas fa-users me-2"></i>Rapport clients</h2>
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
        <form action="{{ route('reports.clients') }}" method="GET" class="row g-3">
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
                        <h6 class="card-title">Total clients</h6>
                        <h4 class="mb-0">{{ $totalClients }}</h4>
                    </div>
                    <i class="fas fa-users fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Clients actifs</h6>
                        <h4 class="mb-0">{{ $activeClients }}</h4>
                    </div>
                    <i class="fas fa-user-check fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Avec achats</h6>
                        <h4 class="mb-0">{{ $clientsWithPurchases }}</h4>
                    </div>
                    <i class="fas fa-shopping-cart fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Avec allergies</h6>
                        <h4 class="mb-0">{{ $clientsWithAllergies }}</h4>
                    </div>
                    <i class="fas fa-exclamation-triangle fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- Top clients -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Top 20 clients par montant dépensé</h5>
            </div>
            <div class="card-body p-0">
                @if($topClients->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Rang</th>
                                    <th>Client</th>
                                    <th>Email</th>
                                    <th class="text-center">Nb achats</th>
                                    <th class="text-end">Total dépensé</th>
                                    <th class="text-end">Panier moyen</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topClients as $index => $client)
                                    <tr>
                                        <td>
                                            <span class="badge {{ $index < 3 ? 'bg-warning' : 'bg-secondary' }}">
                                                #{{ $index + 1 }}
                                            </span>
                                        </td>
                                        <td>
                                            <div>
                                                <strong>{{ $client->full_name }}</strong>
                                                @if($client->phone)
                                                    <br><small class="text-muted">{{ $client->phone }}</small>
                                                @endif
                                            </div>
                                        </td>
                                        <td>{{ $client->email ?? 'N/A' }}</td>
                                        <td class="text-center">{{ $client->total_purchases }}</td>
                                        <td class="text-end">
                                            <strong>{{ number_format($client->total_spent, 2) }} €</strong>
                                        </td>
                                        <td class="text-end">
                                            {{ $client->total_purchases > 0 ? number_format($client->total_spent / $client->total_purchases, 2) : '0' }} €
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="3">Total</th>
                                    <th class="text-center">{{ $topClients->sum('total_purchases') }}</th>
                                    <th class="text-end">{{ number_format($topClients->sum('total_spent'), 2) }} €</th>
                                    <th class="text-end">
                                        {{ $topClients->sum('total_purchases') > 0 ? number_format($topClients->sum('total_spent') / $topClients->sum('total_purchases'), 2) : '0' }} €
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-users fa-3x mb-3"></i>
                        <p class="mb-0">Aucun achat client pour cette période</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Évolution nouveaux clients -->
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Évolution des nouveaux clients (12 derniers mois)</h5>
            </div>
            <div class="card-body">
                <canvas id="newClientsChart" height="100"></canvas>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Analyse des clients -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-pie me-2"></i>Analyse des clients
                </h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-12 mb-3">
                        <div class="border rounded p-3">
                            @php
                                $activePercentage = $totalClients > 0 ? ($activeClients / $totalClients) * 100 : 0;
                            @endphp
                            <h4 class="text-success">{{ number_format($activePercentage, 1) }}%</h4>
                            <small class="text-muted">Clients actifs</small>
                        </div>
                    </div>
                    <div class="col-12 mb-3">
                        <div class="border rounded p-3">
                            @php
                                $withPurchasesPercentage = $totalClients > 0 ? ($clientsWithPurchases / $totalClients) * 100 : 0;
                            @endphp
                            <h4 class="text-primary">{{ number_format($withPurchasesPercentage, 1) }}%</h4>
                            <small class="text-muted">Ont effectué des achats</small>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="border rounded p-3">
                            @php
                                $allergiesPercentage = $totalClients > 0 ? ($clientsWithAllergies / $totalClients) * 100 : 0;
                            @endphp
                            <h4 class="text-warning">{{ number_format($allergiesPercentage, 1) }}%</h4>
                            <small class="text-muted">Avec allergies déclarées</small>
                        </div>
                    </div>
                </div>
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
                    <a href="{{ route('clients.index') }}" class="btn btn-primary">
                        <i class="fas fa-users me-2"></i>
                        Voir tous les clients
                    </a>
                    
                    <a href="{{ route('clients.create') }}" class="btn btn-success">
                        <i class="fas fa-plus me-2"></i>
                        Ajouter un client
                    </a>
                    
                    <a href="{{ route('clients.index', ['status' => 'active']) }}" class="btn btn-info">
                        <i class="fas fa-user-check me-2"></i>
                        Clients actifs uniquement
                    </a>
                    
                    <a href="{{ route('sales.create') }}" class="btn btn-warning">
                        <i class="fas fa-shopping-cart me-2"></i>
                        Nouvelle vente
                    </a>
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
    // Graphique d'évolution des nouveaux clients
    const newClientsCtx = document.getElementById('newClientsChart').getContext('2d');
    const newClientsData = @json($newClientsByMonth);
    
    new Chart(newClientsCtx, {
        type: 'bar',
        data: {
            labels: newClientsData.map(item => {
                const [year, month] = item.month.split('-');
                return new Date(year, month - 1).toLocaleDateString('fr-FR', { 
                    year: 'numeric', 
                    month: 'short' 
                });
            }),
            datasets: [{
                label: 'Nouveaux clients',
                data: newClientsData.map(item => item.count),
                backgroundColor: 'rgba(54, 162, 235, 0.8)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Nouveaux clients par mois'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
});
</script>
@endsection