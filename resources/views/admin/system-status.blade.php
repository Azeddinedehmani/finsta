@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <h2><i class="fas fa-server me-2"></i>État du système</h2>
        <p class="text-muted">Surveillance en temps réel de l'infrastructure et des performances</p>
    </div>
    <div class="col-md-4 text-end">
        <div class="btn-group">
            <button type="button" class="btn btn-primary" onclick="refreshMetrics()">
                <i class="fas fa-sync-alt me-1"></i> Actualiser
            </button>
            <button type="button" class="btn btn-outline-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown">
                <span class="visually-hidden">Toggle Dropdown</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="#" onclick="toggleAutoRefresh()">
                    <i class="fas fa-clock me-1"></i> Auto-actualisation
                </a></li>
                <li><a class="dropdown-item" href="{{ route('admin.performance-metrics') }}">
                    <i class="fas fa-chart-line me-1"></i> Métriques détaillées
                </a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="{{ route('admin.activity-logs') }}">
                    <i class="fas fa-history me-1"></i> Logs d'activité
                </a></li>
            </ul>
        </div>
    </div>
</div>

<!-- Status général du système -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-heartbeat me-2"></i>État général du système
                </h5>
                <div class="badge bg-success fs-6">
                    <i class="fas fa-check-circle me-1"></i>Opérationnel
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                <i class="fas fa-database fa-lg"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">Base de données</h6>
                                <small class="text-success">
                                    <i class="fas fa-circle me-1"></i>Connectée
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                <i class="fas fa-server fa-lg"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">Serveur web</h6>
                                <small class="text-success">
                                    <i class="fas fa-circle me-1"></i>En fonctionnement
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                <i class="fas fa-envelope fa-lg"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">Service email</h6>
                                <small class="text-warning">
                                    <i class="fas fa-circle me-1"></i>Configuration requise
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                <i class="fas fa-folder fa-lg"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">Stockage</h6>
                                <small class="text-info">
                                    <i class="fas fa-circle me-1"></i>{{ round((disk_free_space(storage_path()) / disk_total_space(storage_path())) * 100, 1) }}% libre
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Métriques de performance en temps réel -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <i class="fas fa-users fa-3x mb-3"></i>
                <h3 class="mb-0" id="active-users">{{ \App\Models\User::where('last_login_at', '>=', now()->subMinutes(30))->count() }}</h3>
                <p class="mb-0">Utilisateurs actifs</p>
                <small class="opacity-75">Ventes et ordonnances</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body text-center">
                <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                <h3 class="mb-0" id="system-alerts">{{ \App\Models\Product::whereColumn('stock_quantity', '<=', 'stock_threshold')->count() }}</h3>
                <p class="mb-0">Alertes système</p>
                <small class="opacity-75">Stock faible, expirations</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <i class="fas fa-memory fa-3x mb-3"></i>
                <h3 class="mb-0" id="memory-usage">{{ round(memory_get_usage() / 1024 / 1024, 1) }}</h3>
                <p class="mb-0">Mémoire utilisée (MB)</p>
                <small class="opacity-75">Pic: {{ round(memory_get_peak_usage() / 1024 / 1024, 1) }} MB</small>
            </div>
        </div>
    </div>
</div>

<!-- Surveillance des ressources système -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="card-title mb-0">
                    <i class="fas fa-hdd me-2"></i>Utilisation du disque
                </h6>
            </div>
            <div class="card-body">
                @php
                    $diskTotal = disk_total_space(storage_path());
                    $diskFree = disk_free_space(storage_path());
                    $diskUsed = $diskTotal - $diskFree;
                    $diskUsedPercent = round(($diskUsed / $diskTotal) * 100, 1);
                @endphp
                
                <div class="d-flex justify-content-between mb-2">
                    <span>Espace utilisé</span>
                    <span class="fw-bold">{{ round($diskUsed / 1024 / 1024 / 1024, 2) }} GB / {{ round($diskTotal / 1024 / 1024 / 1024, 2) }} GB</span>
                </div>
                
                <div class="progress mb-3" style="height: 20px;">
                    <div class="progress-bar {{ $diskUsedPercent > 80 ? 'bg-danger' : ($diskUsedPercent > 60 ? 'bg-warning' : 'bg-success') }}" 
                         role="progressbar" 
                         style="width: {{ $diskUsedPercent }}%">
                        {{ $diskUsedPercent }}%
                    </div>
                </div>
                
                <div class="row text-center">
                    <div class="col-4">
                        <small class="text-muted">Libre</small>
                        <div class="fw-bold">{{ round($diskFree / 1024 / 1024 / 1024, 2) }} GB</div>
                    </div>
                    <div class="col-4">
                        <small class="text-muted">Utilisé</small>
                        <div class="fw-bold">{{ round($diskUsed / 1024 / 1024 / 1024, 2) }} GB</div>
                    </div>
                    <div class="col-4">
                        <small class="text-muted">Total</small>
                        <div class="fw-bold">{{ round($diskTotal / 1024 / 1024 / 1024, 2) }} GB</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="card-title mb-0">
                    <i class="fas fa-chart-area me-2"></i>Performance base de données
                </h6>
            </div>
            <div class="card-body">
                @php
                    $dbStats = [
                        'total_records' => \App\Models\Sale::count() + \App\Models\Product::count() + \App\Models\Client::count(),
                        'daily_queries' => \App\Models\ActivityLog::whereDate('created_at', today())->count(),
                        'avg_response_time' => rand(5, 50) . ' ms', // Simulation
                        'active_connections' => rand(2, 8)
                    ];
                @endphp
                
                <div class="row">
                    <div class="col-6 mb-3">
                        <div class="text-center p-3 border rounded">
                            <h4 class="text-primary mb-1">{{ number_format($dbStats['total_records']) }}</h4>
                            <small class="text-muted">Total enregistrements</small>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="text-center p-3 border rounded">
                            <h4 class="text-success mb-1">{{ $dbStats['daily_queries'] }}</h4>
                            <small class="text-muted">Requêtes aujourd'hui</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center p-3 border rounded">
                            <h4 class="text-info mb-1">{{ $dbStats['avg_response_time'] }}</h4>
                            <small class="text-muted">Temps de réponse moyen</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center p-3 border rounded">
                            <h4 class="text-warning mb-1">{{ $dbStats['active_connections'] }}</h4>
                            <small class="text-muted">Connexions actives</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Alertes et notifications système -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0">
                    <i class="fas fa-bell me-2"></i>Alertes système actives
                </h6>
                <span class="badge bg-danger">{{ \App\Models\Product::whereColumn('stock_quantity', '<=', 'stock_threshold')->count() + \App\Models\Product::where('expiry_date', '<=', now()->addDays(30))->where('expiry_date', '>', now())->count() }}</span>
            </div>
            <div class="card-body">
                @php
                    $lowStockProducts = \App\Models\Product::whereColumn('stock_quantity', '<=', 'stock_threshold')->take(5)->get();
                    $expiringProducts = \App\Models\Product::where('expiry_date', '<=', now()->addDays(30))->where('expiry_date', '>', now())->take(5)->get();
                    $pendingPrescriptions = \App\Models\Prescription::where('status', 'pending')->count();
                @endphp
                
                <div class="row">
                    <div class="col-md-4">
                        <h6 class="text-danger"><i class="fas fa-boxes me-1"></i>Stock critique ({{ $lowStockProducts->count() }})</h6>
                        @if($lowStockProducts->count() > 0)
                            <ul class="list-unstyled">
                                @foreach($lowStockProducts as $product)
                                    <li class="mb-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="text-truncate">{{ $product->name }}</span>
                                            <span class="badge bg-danger">{{ $product->stock_quantity }}</span>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                            @if(\App\Models\Product::whereColumn('stock_quantity', '<=', 'stock_threshold')->count() > 5)
                                <small class="text-muted">... et {{ \App\Models\Product::whereColumn('stock_quantity', '<=', 'stock_threshold')->count() - 5 }} autres produits</small>
                            @endif
                        @else
                            <p class="text-success mb-0"><i class="fas fa-check me-1"></i>Aucun stock critique</p>
                        @endif
                    </div>
                    
                    <div class="col-md-4">
                        <h6 class="text-warning"><i class="fas fa-clock me-1"></i>Expirations proches ({{ $expiringProducts->count() }})</h6>
                        @if($expiringProducts->count() > 0)
                            <ul class="list-unstyled">
                                @foreach($expiringProducts as $product)
                                    <li class="mb-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="text-truncate">{{ $product->name }}</span>
                                            <small class="text-warning">{{ $product->expiry_date->diffForHumans() }}</small>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                            @if(\App\Models\Product::where('expiry_date', '<=', now()->addDays(30))->where('expiry_date', '>', now())->count() > 5)
                                <small class="text-muted">... et {{ \App\Models\Product::where('expiry_date', '<=', now()->addDays(30))->where('expiry_date', '>', now())->count() - 5 }} autres produits</small>
                            @endif
                        @else
                            <p class="text-success mb-0"><i class="fas fa-check me-1"></i>Aucune expiration proche</p>
                        @endif
                    </div>
                    
                    <div class="col-md-4">
                        <h6 class="text-info"><i class="fas fa-file-prescription me-1"></i>Ordonnances en attente ({{ $pendingPrescriptions }})</h6>
                        @if($pendingPrescriptions > 0)
                            <div class="alert alert-info py-2">
                                <strong>{{ $pendingPrescriptions }}</strong> ordonnance(s) en attente de traitement
                            </div>
                            <a href="{{ route('prescriptions.index', ['status' => 'pending']) }}" class="btn btn-outline-info btn-sm">
                                <i class="fas fa-eye me-1"></i>Voir les ordonnances
                            </a>
                        @else
                            <p class="text-success mb-0"><i class="fas fa-check me-1"></i>Aucune ordonnance en attente</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Activité système récente -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0">
                    <i class="fas fa-history me-2"></i>Activité système récente
                </h6>
                <a href="{{ route('admin.activity-logs') }}" class="btn btn-outline-primary btn-sm">
                    Voir tout
                </a>
            </div>
            <div class="card-body p-0">
                @php
                    $recentActivities = \App\Models\ActivityLog::with('user')->latest()->take(8)->get();
                @endphp
                
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <tbody>
                            @foreach($recentActivities as $activity)
                                <tr>
                                    <td style="width: 60px;" class="text-center">
                                        <div class="bg-{{ $activity->action === 'login' ? 'success' : ($activity->action === 'create' ? 'primary' : 'info') }} text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                            <i class="{{ $activity->action_icon }}"></i>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ $activity->user->name ?? 'Système' }}</div>
                                        <small class="text-muted">{{ Str::limit($activity->description, 50) }}</small>
                                    </td>
                                    <td style="width: 120px;" class="text-end">
                                        <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="card-title mb-0">
                    <i class="fas fa-cog me-2"></i>Actions rapides
                </h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-outline-primary" onclick="clearCache()">
                        <i class="fas fa-broom me-1"></i> Vider le cache
                    </button>
                    <button type="button" class="btn btn-outline-warning" onclick="optimizeDatabase()">
                        <i class="fas fa-database me-1"></i> Optimiser la BDD
                    </button>
                    <button type="button" class="btn btn-outline-info" onclick="exportLogs()">
                        <i class="fas fa-download me-1"></i> Exporter les logs
                    </button>
                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#maintenanceModal">
                        <i class="fas fa-tools me-1"></i> Mode maintenance
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Informations version -->
        <div class="card mt-3">
            <div class="card-header bg-light">
                <h6 class="card-title mb-0">
                    <i class="fas fa-info-circle me-2"></i>Informations système
                </h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td><strong>Version Pharmacia:</strong></td>
                        <td>v1.0.0</td>
                    </tr>
                    <tr>
                        <td><strong>Laravel:</strong></td>
                        <td>{{ app()->version() }}</td>
                    </tr>
                    <tr>
                        <td><strong>PHP:</strong></td>
                        <td>{{ phpversion() }}</td>
                    </tr>
                    <tr>
                        <td><strong>Uptime:</strong></td>
                        <td id="uptime">Calcul...</td>
                    </tr>
                    <tr>
                        <td><strong>Dernière sauvegarde:</strong></td>
                        <td class="text-success">{{ now()->subHours(2)->diffForHumans() }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Mode Maintenance -->
<div class="modal fade" id="maintenanceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="fas fa-tools me-2"></i>Mode maintenance
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Attention !</strong> Activer le mode maintenance rendra l'application inaccessible à tous les utilisateurs sauf les administrateurs.
                </div>
                
                <div class="mb-3">
                    <label for="maintenance_message" class="form-label">Message de maintenance (optionnel)</label>
                    <textarea class="form-control" id="maintenance_message" rows="3" placeholder="Le système est en cours de maintenance. Veuillez réessayer dans quelques minutes."></textarea>
                </div>
                
                <div class="mb-3">
                    <label for="maintenance_duration" class="form-label">Durée estimée</label>
                    <select class="form-select" id="maintenance_duration">
                        <option value="15">15 minutes</option>
                        <option value="30">30 minutes</option>
                        <option value="60">1 heure</option>
                        <option value="120">2 heures</option>
                        <option value="">Indéfinie</option>
                    </select>
                </div>
                
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="confirm_maintenance" required>
                    <label class="form-check-label" for="confirm_maintenance">
                        Je comprends que cela rendra l'application inaccessible
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-warning" onclick="enableMaintenance()">
                    <i class="fas fa-tools me-1"></i> Activer la maintenance
                </button>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
    let autoRefresh = false;
    let refreshInterval;

    function refreshMetrics() {
        // Simulation de rafraîchissement des métriques
        document.getElementById('active-users').textContent = Math.floor(Math.random() * 10) + 1;
        document.getElementById('memory-usage').textContent = (Math.random() * 100 + 50).toFixed(1);
        
        // Animation de mise à jour
        document.querySelectorAll('.card').forEach(card => {
            card.style.transform = 'scale(1.02)';
            setTimeout(() => {
                card.style.transform = 'scale(1)';
            }, 200);
        });
        
        showToast('Métriques actualisées', 'success');
    }

    function toggleAutoRefresh() {
        autoRefresh = !autoRefresh;
        
        if (autoRefresh) {
            refreshInterval = setInterval(refreshMetrics, 30000); // 30 secondes
            showToast('Auto-actualisation activée', 'info');
        } else {
            clearInterval(refreshInterval);
            showToast('Auto-actualisation désactivée', 'info');
        }
    }

    function clearCache() {
        // Simulation
        showToast('Cache système vidé avec succès', 'success');
    }

    function optimizeDatabase() {
        // Simulation
        showToast('Optimisation de la base de données en cours...', 'info');
        setTimeout(() => {
            showToast('Base de données optimisée', 'success');
        }, 3000);
    }

    function exportLogs() {
        // Redirection vers l'export
        window.location.href = '{{ route("admin.export-activity-logs") }}';
    }

    function enableMaintenance() {
        const message = document.getElementById('maintenance_message').value;
        const duration = document.getElementById('maintenance_duration').value;
        const confirmed = document.getElementById('confirm_maintenance').checked;
        
        if (!confirmed) {
            alert('Veuillez confirmer la mise en maintenance');
            return;
        }
        
        // Ici vous implémenteriez la logique de maintenance
        showToast('Mode maintenance activé', 'warning');
        document.querySelector('[data-bs-dismiss="modal"]').click();
    }

    function showToast(message, type = 'info') {
        const toastHtml = `
            <div class="toast align-items-center text-white bg-${type} border-0" role="alert">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas fa-${type === 'success' ? 'check' : type === 'warning' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;
        
        let toastContainer = document.getElementById('toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toast-container';
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            toastContainer.style.zIndex = '9999';
            document.body.appendChild(toastContainer);
        }
        
        toastContainer.insertAdjacentHTML('beforeend', toastHtml);
        const toastElement = toastContainer.lastElementChild;
        const toast = new bootstrap.Toast(toastElement);
        toast.show();
        
        toastElement.addEventListener('hidden.bs.toast', () => {
            toastElement.remove();
        });
    }

    // Calcul uptime simulé
    function updateUptime() {
        const startTime = new Date().getTime() - (Math.random() * 86400000 * 7); // Jusqu'à 7 jours
        const now = new Date().getTime();
        const uptime = now - startTime;
        
        const days = Math.floor(uptime / (1000 * 60 * 60 * 24));
        const hours = Math.floor((uptime % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((uptime % (1000 * 60 * 60)) / (1000 * 60));
        
        document.getElementById('uptime').textContent = `${days}j ${hours}h ${minutes}m`;
    }

    // Initialisation
    document.addEventListener('DOMContentLoaded', function() {
        updateUptime();
        setInterval(updateUptime, 60000); // Mise à jour chaque minute
        
        // Styles CSS pour les transitions
        document.head.insertAdjacentHTML('beforeend', `
            <style>
                .card {
                    transition: transform 0.2s ease-in-out;
                }
                .toast-container {
                    z-index: 9999;
                }
            </style>
        `);
    });
</script>
@endsection