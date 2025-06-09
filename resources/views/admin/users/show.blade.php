@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <h2><i class="fas fa-user me-2"></i>Profil utilisateur</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Accueil</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Utilisateurs</a></li>
                <li class="breadcrumb-item active">{{ $user->name }}</li>
            </ol>
        </nav>
    </div>
    <div class="col-md-4 text-end">
        <div class="btn-group">
            <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-primary">
                <i class="fas fa-edit me-1"></i> Modifier
            </a>
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Retour
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <!-- Profil utilisateur -->
        <div class="card">
            <div class="card-body text-center">
                @if($user->profile_photo)
                    <img src="{{ asset('storage/'.$user->profile_photo) }}" 
                         alt="{{ $user->name }}" 
                         class="rounded-circle mb-3" 
                         style="width: 120px; height: 120px; object-fit: cover;">
                @else
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" 
                         style="width: 120px; height: 120px; font-size: 48px;">
                        <i class="fas fa-user"></i>
                    </div>
                @endif
                
                <h4>{{ $user->name }}</h4>
                <p class="text-muted">{{ $user->email }}</p>
                
                <span class="badge {{ $user->role === 'responsable' ? 'bg-info' : 'bg-success' }} mb-2">
                    {{ $user->role === 'responsable' ? 'Responsable' : 'Pharmacien' }}
                </span>
                
                @if($user->is_active)
                    <span class="badge bg-success">Actif</span>
                @else
                    <span class="badge bg-secondary">Inactif</span>
                @endif
                
                @if($user->id === auth()->id())
                    <br><span class="badge bg-warning text-dark mt-2">Votre compte</span>
                @endif
            </div>
        </div>

        <!-- Statistiques rapides -->
        <div class="card mt-4">
            <div class="card-header bg-light">
                <h6 class="card-title mb-0">Statistiques</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <h4 class="text-primary">{{ $stats['total_activities'] }}</h4>
                        <small>Total activités</small>
                    </div>
                    <div class="col-6">
                        <h4 class="text-success">{{ $stats['logins_count'] }}</h4>
                        <small>Connexions</small>
                    </div>
                </div>
                <hr>
                <div class="row text-center">
                    <div class="col-12">
                        <h4 class="text-info">{{ $stats['sales_count'] }}</h4>
                        <small>Actions ventes</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <!-- Informations personnelles -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h6 class="card-title mb-0">Informations personnelles</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td><strong>Nom complet:</strong></td>
                                <td>{{ $user->name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Email:</strong></td>
                                <td>{{ $user->email }}</td>
                            </tr>
                            <tr>
                                <td><strong>Téléphone:</strong></td>
                                <td>{{ $user->phone ?: 'Non renseigné' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Date de naissance:</strong></td>
                                <td>{{ $user->date_of_birth ? $user->date_of_birth->format('d/m/Y') : 'Non renseignée' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td><strong>Rôle:</strong></td>
                                <td>{{ $user->role === 'responsable' ? 'Responsable' : 'Pharmacien' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Statut:</strong></td>
                                <td>
                                    @if($user->is_active)
                                        <span class="badge bg-success">Actif</span>
                                    @else
                                        <span class="badge bg-secondary">Inactif</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Membre depuis:</strong></td>
                                <td>{{ $user->created_at->format('d/m/Y') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Dernière connexion:</strong></td>
                                <td>{{ $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : 'Jamais connecté' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                @if($user->address)
                    <hr>
                    <div class="row">
                        <div class="col-12">
                            <strong>Adresse:</strong><br>
                            {{ $user->address }}
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Activités récentes -->
        <div class="card">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0">Activités récentes</h6>
                <a href="{{ route('admin.users.activity-logs', $user->id) }}" class="btn btn-sm btn-outline-primary">
                    Voir tout
                </a>
            </div>
            <div class="card-body p-0">
                @if($user->activityLogs->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Action</th>
                                    <th>Description</th>
                                    <th>IP</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($user->activityLogs->take(10) as $activity)
                                    <tr>
                                        <td>
                                            <small>{{ $activity->created_at->format('d/m H:i') }}</small>
                                        </td>
                                        <td>
                                            <span class="badge {{ $activity->action_badge }}">
                                                {{ ucfirst($activity->action) }}
                                            </span>
                                        </td>
                                        <td>{{ Str::limit($activity->description, 40) }}</td>
                                        <td>
                                            @if($activity->ip_address)
                                                <code style="font-size: 0.8em;">{{ $activity->ip_address }}</code>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <p class="text-muted mb-0">Aucune activité enregistrée</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection