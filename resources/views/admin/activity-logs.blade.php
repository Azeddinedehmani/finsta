@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <h2><i class="fas fa-history me-2"></i>Logs d'activité du système</h2>
        <p class="text-muted">Surveillance complète de toutes les activités des utilisateurs</p>
    </div>
    <div class="col-md-4 text-end">
        <div class="btn-group">
            <a href="{{ route('admin.export-activity-logs', request()->query()) }}" class="btn btn-success">
                <i class="fas fa-download me-1"></i> Exporter CSV
            </a>
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#clearLogsModal">
                <i class="fas fa-trash me-1"></i> Nettoyer
            </button>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Statistiques rapides -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <i class="fas fa-list fa-2x mb-2"></i>
                <h4 class="mb-0">{{ number_format($activities->total()) }}</h4>
                <small>Total activités</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <i class="fas fa-calendar-day fa-2x mb-2"></i>
                <h4 class="mb-0">{{ \App\Models\ActivityLog::whereDate('created_at', today())->count() }}</h4>
                <small>Aujourd'hui</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <i class="fas fa-eye fa-2x mb-2"></i>
                <h4 class="mb-0">{{ \App\Models\ActivityLog::whereIn('action', ['view', 'view_form'])->whereDate('created_at', today())->count() }}</h4>
                <small>Consultations aujourd'hui</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body text-center">
                <i class="fas fa-edit fa-2x mb-2"></i>
                <h4 class="mb-0">{{ \App\Models\ActivityLog::whereIn('action', ['create', 'update', 'delete'])->whereDate('created_at', today())->count() }}</h4>
                <small>Modifications aujourd'hui</small>
            </div>
        </div>
    </div>
</div>

<!-- Filtres avancés -->
<div class="card mb-4">
    <div class="card-header bg-light">
        <h5 class="card-title mb-0">Filtres avancés</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.activity-logs') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="search" class="form-label">Recherche</label>
                <input type="text" class="form-control" id="search" name="search" 
                       placeholder="Description, action..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label for="user_id" class="form-label">Utilisateur</label>
                <select class="form-select" id="user_id" name="user_id">
                    <option value="">Tous les utilisateurs</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="action" class="form-label">Action</label>
                <select class="form-select" id="action" name="action">
                    <option value="">Toutes les actions</option>
                    <option value="view" {{ request('action') == 'view' ? 'selected' : '' }}>Consultation</option>
                    <option value="view_form" {{ request('action') == 'view_form' ? 'selected' : '' }}>Formulaire</option>
                    <option value="create" {{ request('action') == 'create' ? 'selected' : '' }}>Création</option>
                    <option value="update" {{ request('action') == 'update' ? 'selected' : '' }}>Modification</option>
                    <option value="delete" {{ request('action') == 'delete' ? 'selected' : '' }}>Suppression</option>
                    <option value="export" {{ request('action') == 'export' ? 'selected' : '' }}>Export</option>
                    <option value="print" {{ request('action') == 'print' ? 'selected' : '' }}>Impression</option>
                    <option value="login" {{ request('action') == 'login' ? 'selected' : '' }}>Connexion</option>
                    <option value="logout" {{ request('action') == 'logout' ? 'selected' : '' }}>Déconnexion</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="model_type" class="form-label">Type de modèle</label>
                <select class="form-select" id="model_type" name="model_type">
                    <option value="">Tous les types</option>
                    @foreach($modelTypes as $modelType)
                        @php
                            $displayName = match($modelType) {
                                'App\Models\User' => 'Utilisateur',
                                'App\Models\Product' => 'Produit',
                                'App\Models\Sale' => 'Vente',
                                'App\Models\Client' => 'Client',
                                'App\Models\Prescription' => 'Ordonnance',
                                'App\Models\Purchase' => 'Achat',
                                'App\Models\Supplier' => 'Fournisseur',
                                'App\Models\Notification' => 'Notification',
                                default => class_basename($modelType)
                            };
                        @endphp
                        <option value="{{ $modelType }}" {{ request('model_type') == $modelType ? 'selected' : '' }}>
                            {{ $displayName }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1">
                <label for="date_from" class="form-label">Du</label>
                <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-1">
                <label for="date_to" class="form-label">Au</label>
                <input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </form>
        
        <!-- Filtres rapides -->
        <div class="mt-3">
            <div class="btn-group" role="group">
                <a href="{{ route('admin.activity-logs', ['action' => 'view']) }}" 
                   class="btn btn-sm btn-outline-info {{ request('action') == 'view' ? 'active' : '' }}">
                    <i class="fas fa-eye me-1"></i>Consultations
                </a>
                <a href="{{ route('admin.activity-logs', ['action' => 'create']) }}" 
                   class="btn btn-sm btn-outline-success {{ request('action') == 'create' ? 'active' : '' }}">
                    <i class="fas fa-plus me-1"></i>Créations
                </a>
                <a href="{{ route('admin.activity-logs', ['action' => 'update']) }}" 
                   class="btn btn-sm btn-outline-warning {{ request('action') == 'update' ? 'active' : '' }}">
                    <i class="fas fa-edit me-1"></i>Modifications
                </a>
                <a href="{{ route('admin.activity-logs', ['action' => 'delete']) }}" 
                   class="btn btn-sm btn-outline-danger {{ request('action') == 'delete' ? 'active' : '' }}">
                    <i class="fas fa-trash me-1"></i>Suppressions
                </a>
                <a href="{{ route('admin.activity-logs', ['date_from' => today()->format('Y-m-d')]) }}" 
                   class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-calendar-day me-1"></i>Aujourd'hui
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Table des activités -->
<div class="card">
    <div class="card-header bg-light">
        <h5 class="card-title mb-0">
            Historique des activités ({{ number_format($activities->total()) }})
            @if(request()->hasAny(['search', 'user_id', 'action', 'model_type', 'date_from', 'date_to']))
                <span class="badge bg-info">Filtré</span>
            @endif
        </h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width: 130px;">Date/Heure</th>
                        <th style="width: 150px;">Utilisateur</th>
                        <th style="width: 120px;">Action</th>
                        <th>Description</th>
                        <th style="width: 100px;">Modèle</th>
                        <th style="width: 120px;">Adresse IP</th>
                        <th style="width: 80px;">Détails</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activities as $activity)
                        <tr class="{{ in_array($activity->action, ['create', 'update', 'delete']) ? 'table-warning' : '' }}">
                            <td>
                                <div style="font-size: 0.9em;">
                                    {{ $activity->created_at->format('d/m/Y') }}
                                    <br>{{ $activity->created_at->format('H:i:s') }}
                                </div>
                                <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                            </td>
                            <td>
                                @if($activity->user)
                                    <div class="d-flex align-items-center">
                                        @if($activity->user->profile_photo)
                                            <img src="{{ asset('storage/'.$activity->user->profile_photo) }}" 
                                                 alt="{{ $activity->user->name }}" 
                                                 class="rounded-circle me-2" 
                                                 style="width: 32px; height: 32px; object-fit: cover;">
                                        @else
                                            <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center me-2" 
                                                 style="width: 32px; height: 32px; font-size: 12px;">
                                                {{ substr($activity->user->name, 0, 1) }}
                                            </div>
                                        @endif
                                        <div style="font-size: 0.9em;">
                                            <div>{{ Str::limit($activity->user->name, 15) }}</div>
                                            <small class="text-muted">
                                                {{ $activity->user->role === 'responsable' ? 'Admin' : 'Pharmacien' }}
                                            </small>
                                        </div>
                                    </div>
                                @else
                                    <div class="text-center">
                                        <i class="fas fa-cog text-muted"></i>
                                        <br><small class="text-muted">Système</small>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $activity->action_badge }}">
                                    <i class="{{ $activity->action_icon }} me-1"></i>
                                    {{ $activity->action_label ?? ucfirst($activity->action) }}
                                </span>
                                @if(in_array($activity->action, ['create', 'update', 'delete']))
                                    <br><small class="text-danger"><i class="fas fa-exclamation-triangle"></i> Important</small>
                                @endif
                            </td>
                            <td>
                                <div style="max-width: 350px;">
                                    {{ $activity->description }}
                                </div>
                                @if($activity->model_id && $activity->model_type)
                                    <br><small class="text-muted">
                                        ID: {{ $activity->model_id }}
                                        @if($activity->model)
                                            - {{ method_exists($activity->model, 'name') ? $activity->model->name : (method_exists($activity->model, 'title') ? $activity->model->title : '') }}
                                        @endif
                                    </small>
                                @endif
                            </td>
                            <td>
                                @if($activity->model_type)
                                    <span class="badge bg-light text-dark">
                                        {{ $activity->model_name }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($activity->ip_address)
                                    <code style="font-size: 0.8em;">{{ $activity->ip_address }}</code>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($activity->old_values || $activity->new_values || $activity->user_agent)
                                    <button type="button" class="btn btn-sm btn-outline-info" 
                                            data-bs-toggle="modal" data-bs-target="#activityModal{{ $activity->id }}">
                                        <i class="fas fa-info-circle"></i>
                                    </button>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>

                        <!-- Modal pour les détails de l'activité -->
                        @if($activity->old_values || $activity->new_values || $activity->user_agent)
                            <div class="modal fade" id="activityModal{{ $activity->id }}" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Détails de l'activité</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <h6>Informations générales</h6>
                                                    <ul class="list-group list-group-flush">
                                                        <li class="list-group-item d-flex justify-content-between">
                                                            <strong>Date:</strong>
                                                            <span>{{ $activity->created_at->format('d/m/Y H:i:s') }}</span>
                                                        </li>
                                                        <li class="list-group-item d-flex justify-content-between">
                                                            <strong>Utilisateur:</strong>
                                                            <span>{{ $activity->user->name ?? 'Système' }}</span>
                                                        </li>
                                                        <li class="list-group-item d-flex justify-content-between">
                                                            <strong>Action:</strong>
                                                            <span class="badge {{ $activity->action_badge }}">{{ $activity->action_label ?? $activity->action }}</span>
                                                        </li>
                                                        <li class="list-group-item d-flex justify-content-between">
                                                            <strong>IP:</strong>
                                                            <span>{{ $activity->ip_address ?? 'N/A' }}</span>
                                                        </li>
                                                        @if($activity->model_type)
                                                        <li class="list-group-item d-flex justify-content-between">
                                                            <strong>Modèle:</strong>
                                                            <span>{{ $activity->model_name }} (ID: {{ $activity->model_id }})</span>
                                                        </li>
                                                        @endif
                                                    </ul>
                                                </div>
                                                <div class="col-md-6">
                                                    <h6>Description complète</h6>
                                                    <p>{{ $activity->description }}</p>
                                                    
                                                    @if($activity->user_agent)
                                                        <h6>Navigateur/Agent</h6>
                                                        <small class="text-muted">{{ Str::limit($activity->user_agent, 150) }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                            
                                            @if($activity->old_values || $activity->new_values)
                                                <hr>
                                                <div class="row">
                                                    @if($activity->old_values)
                                                        <div class="col-md-6">
                                                            <h6 class="text-danger">Anciennes valeurs</h6>
                                                            <pre class="bg-light p-2 small" style="max-height: 300px; overflow-y: auto;">{{ json_encode($activity->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                        </div>
                                                    @endif
                                                    
                                                    @if($activity->new_values)
                                                        <div class="col-md-6">
                                                            <h6 class="text-success">Nouvelles valeurs</h6>
                                                            <pre class="bg-light p-2 small" style="max-height: 300px; overflow-y: auto;">{{ json_encode($activity->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <p class="text-muted mb-0">Aucune activité trouvée</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($activities->hasPages())
        <div class="card-footer">
            {{ $activities->appends(request()->query())->links() }}
        </div>
    @endif
</div>

<!-- Modal de nettoyage des logs -->
<div class="modal fade" id="clearLogsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nettoyer les logs d'activité</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.clear-old-logs') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Attention :</strong> Cette action supprimera définitivement les logs d'activité anciens.
                    </div>
                    
                    <div class="mb-3">
                        <label for="days" class="form-label">Supprimer les logs de plus de:</label>
                        <select class="form-select" id="days" name="days" required>
                            <option value="30">30 jours</option>
                            <option value="60">60 jours</option>
                            <option value="90" selected>90 jours</option>
                            <option value="180">180 jours</option>
                            <option value="365">1 an</option>
                        </select>
                        <div class="form-text">
                            Les logs récents seront conservés pour la surveillance de sécurité.
                        </div>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="confirmClear" required>
                        <label class="form-check-label" for="confirmClear">
                            Je comprends que cette action est irréversible
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i> Nettoyer les logs
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection