@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <h2><i class="fas fa-users me-2"></i>Rapport des utilisateurs</h2>
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
        <form action="{{ route('reports.users') }}" method="GET" class="row g-3">
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
    <div class="col-md-2">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <h4 class="mb-0">{{ $totalUsers }}</h4>
                <small>Total utilisateurs</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <h4 class="mb-0">{{ $activeUsers }}</h4>
                <small>Utilisateurs actifs</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <h4 class="mb-0">{{ $adminUsers }}</h4>
                <small>Administrateurs</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-warning text-dark">
            <div class="card-body text-center">
                <h4 class="mb-0">{{ $pharmacistUsers }}</h4>
                <small>Pharmaciens</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-danger text-white">
            <div class="card-body text-center">
                <h4 class="mb-0">{{ $usersNeedingPasswordChange }}</h4>
                <small>MDP à changer</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-secondary text-white">
            <div class="card-body text-center">
                <h4 class="mb-0">{{ number_format(($activeUsers / max($totalUsers, 1)) * 100, 1) }}%</h4>
                <small>Taux d'activité</small>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- Top utilisateurs par activité -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-bar me-2"></i>Top utilisateurs par activité sur la période
                </h5>
            </div>
            <div class="card-body p-0">
                @if($topUsersByActivity->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Utilisateur</th>
                                    <th>Rôle</th>
                                    <th class="text-center">Actions</th>
                                    <th class="text-center">Jours actifs</th>
                                    <th class="text-center">Dernière connexion</th>
                                    <th class="text-center">Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topUsersByActivity as $user)
                                    <tr>
                                        <td>
                                            <div>
                                                <strong>{{ $user->name }}</strong>
                                                <br><small class="text-muted">{{ $user->email }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge {{ $user->role === 'responsable' ? 'bg-danger' : 'bg-primary' }}">
                                                {{ $user->role === 'responsable' ? 'Admin' : 'Pharmacien' }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info">{{ $user->activity_count }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-success">{{ $user->active_days }}</span>
                                        </td>
                                        <td class="text-center">
                                            @if($user->last_login_at)
                                                <small>{{ \Carbon\Carbon::parse($user->last_login_at)->format('d/m/Y H:i') }}</small>
                                            @else
                                                <small class="text-muted">Jamais</small>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="badge {{ $user->is_active ? 'bg-success' : 'bg-danger' }}">
                                                {{ $user->is_active ? 'Actif' : 'Inactif' }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-users fa-3x mb-3"></i>
                        <p class="mb-0">Aucune activité utilisateur pour cette période</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Évolution mensuelle de l'activité -->
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-line me-2"></i>Évolution de l'activité (12 derniers mois)
                </h5>
            </div>
            <div class="card-body">
                <canvas id="activityChart" height="100"></canvas>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Actions les plus fréquentes -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">
                    <i class="fas fa-list me-2"></i>Actions les plus fréquentes
                </h5>
            </div>
            <div class="card-body p-0">
                @if($topActions->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Action</th>
                                    <th class="text-center">Nombre</th>
                                    <th class="text-end">%</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $totalActions = $topActions->sum('count'); @endphp
                                @foreach($topActions as $action)
                                    <tr>
                                        <td>
                                            @switch($action->action)
                                                @case('login')
                                                    <i class="fas fa-sign-in-alt text-success me-2"></i>Connexion
                                                    @break
                                                @case('logout')
                                                    <i class="fas fa-sign-out-alt text-secondary me-2"></i>Déconnexion
                                                    @break
                                                @case('create')
                                                    <i class="fas fa-plus text-primary me-2"></i>Création
                                                    @break
                                                @case('update')
                                                    <i class="fas fa-edit text-warning me-2"></i>Modification
                                                    @break
                                                @case('delete')
                                                    <i class="fas fa-trash text-danger me-2"></i>Suppression
                                                    @break
                                                @case('view')
                                                    <i class="fas fa-eye text-info me-2"></i>Consultation
                                                    @break
                                                @default
                                                    <i class="fas fa-cog text-muted me-2"></i>{{ ucfirst($action->action) }}
                                            @endswitch
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary">{{ $action->count }}</span>
                                        </td>
                                        <td class="text-end">
                                            {{ $totalActions > 0 ? number_format(($action->count / $totalActions) * 100, 1) : 0 }}%
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center text-muted py-3">
                        <p class="mb-0">Aucune action pour cette période</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Connexions récentes -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-sign-in-alt me-2"></i>Connexions récentes
                </h5>
            </div>
            <div class="card-body p-0">
                @if($recentLogins->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($recentLogins->take(10) as $login)
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">{{ $login->user->name ?? 'Utilisateur supprimé' }}</h6>
                                    <small>{{ $login->created_at->format('d/m/Y H:i') }}</small>
                                </div>
                                <p class="mb-1">
                                    <small class="text-muted">
                                        <i class="fas fa-globe me-1"></i>{{ $login->ip_address ?? 'IP inconnue' }}
                                    </small>
                                </p>
                                @if($login->user_agent)
                                    <small class="text-muted">
                                        {{ substr($login->user_agent, 0, 50) }}{{ strlen($login->user_agent) > 50 ? '...' : '' }}
                                    </small>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    @if($recentLogins->count() > 10)
                        <div class="card-footer text-center">
                            <small class="text-muted">
                                Et {{ $recentLogins->count() - 10 }} autres connexions...
                            </small>
                        </div>
                    @endif
                @else
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-sign-in-alt fa-2x mb-2"></i>
                        <p class="mb-0">Aucune connexion récente</p>
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
                    <a href="{{ route('admin.users.index') }}" class="btn btn-primary">
                        <i class="fas fa-users me-2"></i>
                        Gestion des utilisateurs
                    </a>
                    
                    <a href="{{ route('admin.users.create') }}" class="btn btn-success">
                        <i class="fas fa-user-plus me-2"></i>
                        Ajouter un utilisateur
                    </a>
                    
                    <a href="{{ route('admin.activity-logs') }}" class="btn btn-info">
                        <i class="fas fa-history me-2"></i>
                        Logs d'activité complets
                    </a>
                    
                    <a href="{{ route('admin.users.index', ['status' => 'inactive']) }}" class="btn btn-warning">
                        <i class="fas fa-user-times me-2"></i>
                        Utilisateurs inactifs
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

<!-- Performance des ventes -->
@if($salesPerformance->count() > 0)
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-line me-2"></i>Performance des ventes par utilisateur
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Utilisateur</th>
                                <th>Rôle</th>
                                <th class="text-center">Nb ventes</th>
                                <th class="text-end">CA total</th>
                                <th class="text-end">Panier moyen</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($salesPerformance as $user)
                                <tr>
                                    <td>
                                        <strong>{{ $user->name }}</strong>
                                        <br><small class="text-muted">{{ $user->email }}</small>
                                    </td>
                                    <td>
                                        <span class="badge {{ $user->role === 'responsable' ? 'bg-danger' : 'bg-primary' }}">
                                            {{ $user->role === 'responsable' ? 'Admin' : 'Pharmacien' }}
                                        </span>
                                    </td>
                                    <td class="text-center">{{ $user->sales_count }}</td>
                                    <td class="text-end">
                                        <strong>{{ number_format($user->total_sales_amount, 2) }} €</strong>
                                    </td>
                                    <td class="text-end">
                                        {{ number_format($user->average_sale_amount, 2) }} €
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="2">Total</th>
                                <th class="text-center">{{ $salesPerformance->sum('sales_count') }}</th>
                                <th class="text-end">{{ number_format($salesPerformance->sum('total_sales_amount'), 2) }} €</th>
                                <th class="text-end">
                                    {{ $salesPerformance->sum('sales_count') > 0 ? number_format($salesPerformance->sum('total_sales_amount') / $salesPerformance->sum('sales_count'), 2) : '0.00' }} €
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Analyse détaillée -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-pie me-2"></i>Analyse de la répartition
                </h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            @php
                                $adminPercentage = $totalUsers > 0 ? ($adminUsers / $totalUsers) * 100 : 0;
                            @endphp
                            <h4 class="text-danger">{{ number_format($adminPercentage, 1) }}%</h4>
                            <small class="text-muted">Administrateurs</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            @php
                                $pharmacistPercentage = $totalUsers > 0 ? ($pharmacistUsers / $totalUsers) * 100 : 0;
                            @endphp
                            <h4 class="text-primary">{{ number_format($pharmacistPercentage, 1) }}%</h4>
                            <small class="text-muted">Pharmaciens</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            @php
                                $activePercentage = $totalUsers > 0 ? ($activeUsers / $totalUsers) * 100 : 0;
                            @endphp
                            <h4 class="text-success">{{ number_format($activePercentage, 1) }}%</h4>
                            <small class="text-muted">Utilisateurs actifs</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            @php
                                $passwordChangePercentage = $totalUsers > 0 ? ($usersNeedingPasswordChange / $totalUsers) * 100 : 0;
                            @endphp
                            <h4 class="text-warning">{{ number_format($passwordChangePercentage, 1) }}%</h4>
                            <small class="text-muted">MDP à changer</small>
                        </div>
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
document.addEventListener('DOMContentLoaded', function() {
    // Graphique d'évolution de l'activité mensuelle
    const activityCtx = document.getElementById('activityChart').getContext('2d');
    const monthlyData = @json($userActivityByMonth);
    
    new Chart(activityCtx, {
        type: 'line',
        data: {
            labels: monthlyData.map(item => {
                const [year, month] = item.month.split('-');
                return new Date(year, month - 1).toLocaleDateString('fr-FR', { 
                    year: 'numeric', 
                    month: 'short' 
                });
            }),
            datasets: [{
                label: 'Total d\'activités',
                data: monthlyData.map(item => item.activity_count),
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.1,
                fill: true
            }, {
                label: 'Utilisateurs actifs',
                data: monthlyData.map(item => item.active_users),
                borderColor: 'rgb(34, 197, 94)',
                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                tension: 0.1,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Évolution de l\'activité utilisateur'
                }
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Nombre d\'activités'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Utilisateurs actifs'
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            }
        }
    });
});
</script>
@endsection