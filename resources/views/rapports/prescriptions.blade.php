@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <h2><i class="fas fa-prescription-bottle me-2"></i>Rapport des ordonnances</h2>
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
        <form action="{{ route('reports.prescriptions') }}" method="GET" class="row g-3">
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
    <div class="col-md-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Total ordonnances</h6>
                        <h4 class="mb-0">{{ $totalPrescriptions }}</h4>
                    </div>
                    <i class="fas fa-prescription-bottle fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Complètement délivrées</h6>
                        <h4 class="mb-0">{{ $completedPrescriptions }}</h4>
                    </div>
                    <i class="fas fa-check-circle fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Taux de délivrance</h6>
                        <h4 class="mb-0">{{ number_format($completionRate, 1) }}%</h4>
                    </div>
                    <i class="fas fa-chart-line fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- Répartition par statut -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Répartition des ordonnances par statut</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <canvas id="statusChart" height="200"></canvas>
                    </div>
                    <div class="col-md-6">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Statut</th>
                                        <th class="text-center">Nombre</th>
                                        <th class="text-end">%</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($prescriptionsByStatus as $status)
                                        <tr>
                                            <td>
                                                @switch($status->status)
                                                    @case('pending')
                                                        <span class="badge bg-warning text-dark">En attente</span>
                                                        @break
                                                    @case('partially_delivered')
                                                        <span class="badge bg-info">Partiellement délivrée</span>
                                                        @break
                                                    @case('completed')
                                                        <span class="badge bg-success">Complètement délivrée</span>
                                                        @break
                                                    @case('expired')
                                                        <span class="badge bg-danger">Expirée</span>
                                                        @break
                                                    @default
                                                        <span class="badge bg-secondary">{{ ucfirst($status->status) }}</span>
                                                @endswitch
                                            </td>
                                            <td class="text-center">{{ $status->count }}</td>
                                            <td class="text-end">
                                                {{ $totalPrescriptions > 0 ? number_format(($status->count / $totalPrescriptions) * 100, 1) : 0 }}%
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

        <!-- Top médicaments prescrits -->
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Top 15 des médicaments prescrits</h5>
            </div>
            <div class="card-body p-0">
                @if($topPrescribedMedications->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Rang</th>
                                    <th>Médicament</th>
                                    <th class="text-center">Qté prescrite</th>
                                    <th class="text-center">Qté délivrée</th>
                                    <th class="text-center">Nb ordonnances</th>
                                    <th class="text-center">Taux délivrance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topPrescribedMedications as $index => $medication)
                                    @php
                                        $deliveryRate = $medication->total_prescribed > 0 ? 
                                            ($medication->total_delivered / $medication->total_prescribed) * 100 : 0;
                                    @endphp
                                    <tr>
                                        <td>
                                            <span class="badge {{ $index < 3 ? 'bg-warning' : 'bg-secondary' }}">
                                                #{{ $index + 1 }}
                                            </span>
                                        </td>
                                        <td><strong>{{ $medication->name }}</strong></td>
                                        <td class="text-center">{{ $medication->total_prescribed }}</td>
                                        <td class="text-center">{{ $medication->total_delivered }}</td>
                                        <td class="text-center">{{ $medication->prescription_count }}</td>
                                        <td class="text-center">
                                            <span class="badge {{ $deliveryRate >= 80 ? 'bg-success' : ($deliveryRate >= 50 ? 'bg-warning text-dark' : 'bg-danger') }}">
                                                {{ number_format($deliveryRate, 1) }}%
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-prescription-bottle fa-3x mb-3"></i>
                        <p class="mb-0">Aucune prescription pour cette période</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Ordonnances expirées -->
        <div class="card mb-4">
            <div class="card-header bg-danger text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-calendar-times me-2"></i>
                    Ordonnances expirées ({{ $expiredPrescriptions->count() }})
                </h5>
            </div>
            <div class="card-body p-0">
                @if($expiredPrescriptions->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($expiredPrescriptions->take(10) as $prescription)
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">{{ $prescription->prescription_number }}</h6>
                                    <small class="text-danger">
                                        {{ $prescription->expiry_date->format('d/m/Y') }}
                                    </small>
                                </div>
                                <p class="mb-1">
                                    <strong>{{ $prescription->client->full_name }}</strong>
                                </p>
                                <small class="text-muted">
                                    Dr. {{ $prescription->doctor_name }}
                                </small>
                            </div>
                        @endforeach
                    </div>
                    @if($expiredPrescriptions->count() > 10)
                        <div class="card-footer text-center">
                            <small class="text-muted">
                                Et {{ $expiredPrescriptions->count() - 10 }} autres...
                            </small>
                        </div>
                    @endif
                @else
                    <div class="text-center text-success py-4">
                        <i class="fas fa-check-circle fa-2x mb-2"></i>
                        <p class="mb-0">Aucune ordonnance expirée</p>
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
                    <a href="{{ route('prescriptions.index') }}" class="btn btn-primary">
                        <i class="fas fa-prescription-bottle me-2"></i>
                        Voir toutes les ordonnances
                    </a>
                    
                    <a href="{{ route('prescriptions.create') }}" class="btn btn-success">
                        <i class="fas fa-plus me-2"></i>
                        Nouvelle ordonnance
                    </a>
                    
                    <a href="{{ route('prescriptions.index', ['status' => 'pending']) }}" class="btn btn-warning">
                        <i class="fas fa-clock me-2"></i>
                        Ordonnances en attente
                    </a>
                    
                    <a href="{{ route('prescriptions.index', ['expiry_filter' => 'expired']) }}" class="btn btn-danger">
                        <i class="fas fa-calendar-times me-2"></i>
                        Ordonnances expirées
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
    // Graphique en secteurs pour les statuts
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    const statusData = @json($prescriptionsByStatus);
    
    const statusLabels = statusData.map(item => {
        switch(item.status) {
            case 'pending': return 'En attente';
            case 'partially_delivered': return 'Partiellement délivrée';
            case 'completed': return 'Complètement délivrée';
            case 'expired': return 'Expirée';
            default: return item.status;
        }
    });
    
    const statusColors = ['#FFC107', '#17A2B8', '#28A745', '#DC3545', '#6C757D'];
    
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: statusLabels,
            datasets: [{
                data: statusData.map(item => item.count),
                backgroundColor: statusColors.slice(0, statusData.length),
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
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed / total) * 100).toFixed(1);
                            return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });
});
</script>
@endsection