@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <h2><i class="fas fa-history me-2"></i>Activités de {{ $user->name }}</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Accueil</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Utilisateurs</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.users.show', $user->id) }}">{{ $user->name }}</a></li>
                <li class="breadcrumb-item active">Activités</li>
            </ol>
        </nav>
    </div>
    <div class="col-md-4 text-end">
        <div class="btn-group">
            <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-secondary">
                <i class="fas fa-user me-1"></i> Profil
            </a>
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Retour
            </a>
        </div>
    </div>
</div>

<!-- User info card -->
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex align-items-center">
            @if($user->profile_photo)
                <img src="{{ asset('storage/'.$user->profile_photo) }}" 
                     alt="{{ $user->name }}" 
                     class="rounded-circle me-3" 
                     style="width: 60px; height: 60px; object-fit: cover;">
            @else
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" 
                     style="width: 60px; height: 60px;">
                    <i class="fas fa-user fa-lg"></i>
                </div>
            @endif
            <div>
                <h5 class="mb-1">{{ $user->name }}</h5>
                <p class="text-muted mb-1">{{ $user->email }}</p>
                <span class="badge {{ $user->role === 'responsable' ? 'bg-info' : 'bg-success' }}">
                    {{ $user->role === 'responsable' ? 'Responsable' : 'Pharmacien' }}
                </span>
                @if($user->is_active)
                    <span class="badge bg-success">Actif</span>
                @else
                    <span class="badge bg-secondary">Inactif</span>
                @endif
            </div>
            <div class="ms-auto text-end">
                <div class="row text-center">
                    <div class="col">
                        <h4 class="text-primary mb-0">{{ $user->activityLogs()->count() }}</h4>
                        <small class="text-muted">Total activités</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filtres -->
<div class="card mb-4">
    <div class="card-header bg-light">
        <h5 class="card-title mb-0">Filtres</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.users.activity-logs', $user->id) }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="action" class="form-label">Action</label>
                <select class="form-select" id="action" name="action">
                    <option value="">Toutes les actions</option>
                    @php
                        $actions = $user->activityLogs()->distinct()->pluck('action')->filter()->sort()->values();
                    @endphp
                    @foreach($actions as $action)
                        <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                            {{ ucfirst($action) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="date_from" class="form-label">Du</label>
                <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-3">
                <label for="date_to" class="form-label">Au</label>
                <input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-1"></i> Filtrer
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Liste des activités -->
<div class="card">
    <div class="card-header bg-light">
        <h5 class="card-title mb-0">
            Historique des activités ({{ $activities->total() }})
            @if(request()->hasAny(['action', 'date_from', 'date_to']))
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
                        <th style="width: 100px;">Action</th>
                        <th>Description</th>
                        <th style="width: 100px;">Modèle</th>
                        <th style="width: 120px;">Adresse IP</th>
                        <th style="width: 80px;">Détails</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activities as $activity)
                        <tr>
                            <td>
                                <div style="font-size: 0.9em;">
                                    {{ $activity->created_at->format('d/m/Y') }}
                                    <br>{{ $activity->created_at->format('H:i:s') }}
                                </div>
                                <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                            </td>
                            <td>
                                <span class="badge {{ $activity->action_badge }}">
                                    <i class="{{ $activity->action_icon }} me-1"></i>
                                    {{ ucfirst($activity->action) }}
                                </span>
                            </td>
                            <td>
                                <div style="max-width: 300px;">
                                    {{ $activity->description }}
                                </div>
                            </td>
                            <td>
                                @if($activity->model_type)
                                    <span class="badge bg-light text-dark">
                                        {{ $activity->model_name }}
                                    </span>
                                    @if($activity->model_id)
                                        <br><small class="text-muted">ID: {{ $activity->model_id }}</small>
                                    @endif
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
                                @if($activity->old_values || $activity->new_values)
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
                        @if($activity->old_values || $activity->new_values)
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
                                                            <strong>Action:</strong>
                                                            <span class="badge {{ $activity->action_badge }}">{{ $activity->action }}</span>
                                                        </li>
                                                        <li class="list-group-item d-flex justify-content-between">
                                                            <strong>IP:</strong>
                                                            <span>{{ $activity->ip_address ?? 'N/A' }}</span>
                                                        </li>
                                                    </ul>
                                                </div>
                                                <div class="col-md-6">
                                                    <h6>Description</h6>
                                                    <p>{{ $activity->description }}</p>
                                                    
                                                    @if($activity->user_agent)
                                                        <h6>Navigateur</h6>
                                                        <small class="text-muted">{{ Str::limit($activity->user_agent, 100) }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                            
                                            @if($activity->old_values || $activity->new_values)
                                                <hr>
                                                <div class="row">
                                                    @if($activity->old_values)
                                                        <div class="col-md-6">
                                                            <h6 class="text-danger">Anciennes valeurs</h6>
                                                            <pre class="bg-light p-2 small">{{ json_encode($activity->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                        </div>
                                                    @endif
                                                    
                                                    @if($activity->new_values)
                                                        <div class="col-md-6">
                                                            <h6 class="text-success">Nouvelles valeurs</h6>
                                                            <pre class="bg-light p-2 small">{{ json_encode($activity->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
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
                            <td colspan="6" class="text-center py-4">
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
@endsection