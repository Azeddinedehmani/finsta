@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <h2><i class="fas fa-users me-2"></i>Gestion des utilisateurs</h2>
    </div>
    <div class="col-md-4 text-end">
        <div class="btn-group">
            <a href="{{ route('admin.users.export', request()->query()) }}" class="btn btn-success">
                <i class="fas fa-download me-1"></i> Exporter CSV
            </a>
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Nouvel utilisateur
            </a>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        @if(session('temp_password'))
            <br><strong>Mot de passe temporaire: {{ session('temp_password') }}</strong>
        @endif
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Statistiques -->
<div class="row mb-4">
    <div class="col-md-2">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <i class="fas fa-users fa-2x mb-2"></i>
                <h4 class="mb-0">{{ $totalUsers }}</h4>
                <small>Total utilisateurs</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <i class="fas fa-user-check fa-2x mb-2"></i>
                <h4 class="mb-0">{{ $activeUsers }}</h4>
                <small>Actifs</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <i class="fas fa-user-shield fa-2x mb-2"></i>
                <h4 class="mb-0">{{ $adminUsers }}</h4>
                <small>Administrateurs</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-warning text-dark">
            <div class="card-body text-center">
                <i class="fas fa-user-md fa-2x mb-2"></i>
                <h4 class="mb-0">{{ $pharmacistUsers }}</h4>
                <small>Pharmaciens</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-secondary text-white">
            <div class="card-body text-center">
                <i class="fas fa-clock fa-2x mb-2"></i>
                <h4 class="mb-0">{{ $recentLogins }}</h4>
                <small>Connexions 7j</small>
            </div>
        </div>
    </div>
</div>

<!-- Filtres -->
<div class="card mb-4">
    <div class="card-header bg-light">
        <h5 class="card-title mb-0">Filtres et recherche</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.users.index') }}" method="GET" class="row g-3">
            <div class="col-md-4">
                <label for="search" class="form-label">Recherche</label>
                <input type="text" class="form-control" id="search" name="search" 
                       placeholder="Nom, email, téléphone..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <label for="role" class="form-label">Rôle</label>
                <select class="form-select" id="role" name="role">
                    <option value="">Tous les rôles</option>
                    <option value="responsable" {{ request('role') == 'responsable' ? 'selected' : '' }}>Responsable</option>
                    <option value="pharmacien" {{ request('role') == 'pharmacien' ? 'selected' : '' }}>Pharmacien</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="status" class="form-label">Statut</label>
                <select class="form-select" id="status" name="status">
                    <option value="">Tous les statuts</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Actif</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactif</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-1"></i> Filtrer
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Liste des utilisateurs -->
<div class="card">
    <div class="card-header bg-light">
        <h5 class="card-title mb-0">Liste des utilisateurs ({{ $users->total() }})</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Utilisateur</th>
                        <th>Contact</th>
                        <th>Rôle</th>
                        <th>Dernière connexion</th>
                        <th>Activités</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($user->profile_photo)
                                        <img src="{{ asset('storage/'.$user->profile_photo) }}" 
                                             alt="{{ $user->name }}" 
                                             class="rounded-circle me-3" 
                                             style="width: 40px; height: 40px; object-fit: cover;">
                                    @else
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" 
                                             style="width: 40px; height: 40px;">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <strong>{{ $user->name }}</strong>
                                        <br><small class="text-muted">{{ $user->email }}</small>
                                        @if($user->force_password_change)
                                            <br><small class="text-warning">
                                                <i class="fas fa-exclamation-triangle me-1"></i>
                                                Changement mot de passe requis
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($user->phone)
                                    <div><i class="fas fa-phone me-1"></i>{{ $user->phone }}</div>
                                @endif
                                @if($user->address)
                                    <div><i class="fas fa-map-marker-alt me-1"></i>{{ Str::limit($user->address, 30) }}</div>
                                @endif
                                @if(!$user->phone && !$user->address)
                                    <span class="text-muted">Non renseigné</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $user->role === 'responsable' ? 'bg-info' : 'bg-success' }}">
                                    {{ $user->role === 'responsable' ? 'Responsable' : 'Pharmacien' }}
                                </span>
                            </td>
                            <td>
                                @if($user->last_login_at)
                                    <div>{{ $user->last_login_at->format('d/m/Y H:i') }}</div>
                                    @if($user->last_login_ip)
                                        <small class="text-muted">IP: {{ $user->last_login_ip }}</small>
                                    @endif
                                @else
                                    <span class="text-muted">Jamais connecté</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-primary">{{ $user->activity_logs_count }}</span>
                                @if($user->activity_logs_count > 0)
                                    <br><small class="text-muted">activités</small>
                                @endif
                            </td>
                            <td>
                                @if($user->is_active)
                                    <span class="badge bg-success">Actif</span>
                                @else
                                    <span class="badge bg-secondary">Inactif</span>
                                @endif
                                
                                @if($user->id === auth()->id())
                                    <br><small class="text-primary">Vous</small>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-sm btn-info text-white">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    
                                    @if($user->id !== auth()->id())
                                        <!-- Toggle Status -->
                                        <form action="{{ route('admin.users.toggle-status', $user->id) }}" method="POST" style="display: inline;">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm {{ $user->is_active ? 'btn-warning' : 'btn-success' }}"
                                                    onclick="return confirm('Êtes-vous sûr de vouloir {{ $user->is_active ? 'désactiver' : 'activer' }} cet utilisateur ?')">
                                                <i class="fas {{ $user->is_active ? 'fa-pause' : 'fa-play' }}"></i>
                                            </button>
                                        </form>
                                        
                                        <!-- Reset Password -->
                                        <button type="button" class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#resetPasswordModal{{ $user->id }}">
                                            <i class="fas fa-key"></i>
                                        </button>
                                        
                                        <!-- Delete User -->
                                        @if($user->role !== 'responsable' || \App\Models\User::where('role', 'responsable')->where('id', '!=', $user->id)->count() > 0)
                                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $user->id }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    @endif
                                </div>
                                
                                @if($user->id !== auth()->id())
                                    <!-- Reset Password Modal -->
                                    <div class="modal fade" id="resetPasswordModal{{ $user->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Réinitialiser le mot de passe</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Êtes-vous sûr de vouloir réinitialiser le mot de passe de <strong>{{ $user->name }}</strong> ?</p>
                                                    <div class="alert alert-warning">
                                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                                        Un mot de passe temporaire sera généré et l'utilisateur devra le changer à sa prochaine connexion.
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                    <form action="{{ route('admin.users.reset-password', $user->id) }}" method="POST" style="display: inline;">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="btn btn-warning">
                                                            <i class="fas fa-key me-1"></i> Réinitialiser
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Delete Modal -->
                                    @if($user->role !== 'responsable' || \App\Models\User::where('role', 'responsable')->where('id', '!=', $user->id)->count() > 0)
                                        <div class="modal fade" id="deleteModal{{ $user->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Supprimer l'utilisateur</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>Êtes-vous sûr de vouloir supprimer <strong>{{ $user->name }}</strong> ?</p>
                                                        <div class="alert alert-danger">
                                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                                            <strong>Cette action est irréversible !</strong>
                                                            Toutes les données associées à cet utilisateur seront supprimées.
                                                        </div>
                                                        <p><strong>Statistiques :</strong></p>
                                                        <ul>
                                                            <li>{{ $user->activity_logs_count }} activités enregistrées</li>
                                                            <li>Membre depuis {{ $user->created_at->diffForHumans() }}</li>
                                                        </ul>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" style="display: inline;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger">
                                                                <i class="fas fa-trash me-1"></i> Supprimer définitivement
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <p class="text-muted mb-0">Aucun utilisateur trouvé</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($users->hasPages())
        <div class="card-footer">
            {{ $users->appends(request()->query())->links() }}
        </div>
    @endif
</div>
@endsection