<!-- resources/views/notifications/index.blade.php -->
@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">
        <i class="fas fa-bell me-2"></i>Notifications
        @if($unreadCount > 0)
            <span class="badge bg-danger">{{ $unreadCount }}</span>
        @endif
    </h1>
    
    <div class="btn-group">
        <!-- Bouton Marquer tout comme lu avec formulaire POST -->
        <form method="POST" action="{{ route('notifications.mark-all-read') }}" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-outline-primary"
                    onclick="return confirm('Marquer toutes les notifications comme lues ?')">
                <i class="fas fa-check-double me-1"></i>Tout marquer comme lu
            </button>
        </form>
        
        <!-- Bouton Supprimer les lues avec formulaire DELETE -->
        <form method="POST" action="{{ route('notifications.delete-read') }}" class="d-inline ms-2">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger"
                    onclick="return confirm('Supprimer toutes les notifications lues ?')">
                <i class="fas fa-trash me-1"></i>Supprimer les lues
            </button>
        </form>
        
        <a href="{{ route('notifications.settings') }}" class="btn btn-outline-secondary ms-2">
            <i class="fas fa-cog me-1"></i>Paramètres
        </a>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Type</label>
                <select name="type" class="form-select">
                    <option value="">Tous les types</option>
                    <option value="stock_alert" {{ request('type') == 'stock_alert' ? 'selected' : '' }}>Alerte Stock</option>
                    <option value="expiry_alert" {{ request('type') == 'expiry_alert' ? 'selected' : '' }}>Expiration</option>
                    <option value="sale_created" {{ request('type') == 'sale_created' ? 'selected' : '' }}>Vente</option>
                    <option value="prescription_ready" {{ request('type') == 'prescription_ready' ? 'selected' : '' }}>Ordonnance</option>
                    <option value="purchase_received" {{ request('type') == 'purchase_received' ? 'selected' : '' }}>Livraison</option>
                    <option value="system_alert" {{ request('type') == 'system_alert' ? 'selected' : '' }}>Système</option>
                </select>
            </div>
            
            <div class="col-md-3">
                <label class="form-label">Statut</label>
                <select name="status" class="form-select">
                    <option value="">Toutes</option>
                    <option value="unread" {{ request('status') == 'unread' ? 'selected' : '' }}>Non lues</option>
                    <option value="read" {{ request('status') == 'read' ? 'selected' : '' }}>Lues</option>
                </select>
            </div>
            
            <div class="col-md-3">
                <label class="form-label">Priorité</label>
                <select name="priority" class="form-select">
                    <option value="">Toutes</option>
                    <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>Élevée</option>
                    <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Moyenne</option>
                    <option value="normal" {{ request('priority') == 'normal' ? 'selected' : '' }}>Normale</option>
                    <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Faible</option>
                </select>
            </div>
            
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-filter me-1"></i>Filtrer
                </button>
                <a href="{{ route('notifications.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i>Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Notifications List -->
<div class="card">
    <div class="card-body p-0">
        @if($notifications->count() > 0)
            <div class="list-group list-group-flush">
                @foreach($notifications as $notification)
                    <div class="list-group-item {{ !$notification->isRead() ? 'bg-light' : '' }}">
                        <div class="d-flex w-100 justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="{{ $notification->type_icon }} me-2 text-{{ $notification->priority == 'high' ? 'danger' : ($notification->priority == 'medium' ? 'warning' : 'primary') }}"></i>
                                    <h6 class="mb-0 {{ !$notification->isRead() ? 'fw-bold' : '' }}">
                                        {{ $notification->title }}
                                    </h6>
                                    <span class="badge {{ $notification->priority_badge }} ms-2">
                                        {{ $notification->priority_label }}
                                    </span>
                                    @if(!$notification->isRead())
                                        <span class="badge bg-primary ms-1">Nouveau</span>
                                    @endif
                                </div>
                                
                                <p class="mb-1 text-muted">{{ $notification->message }}</p>
                                
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>{{ $notification->created_at->diffForHumans() }}
                                    <span class="badge bg-secondary ms-2">{{ $notification->type_label }}</span>
                                </small>
                            </div>
                            
                            <div class="dropdown ms-3">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                        type="button" data-bs-toggle="dropdown">
                                    Actions
                                </button>
                                <ul class="dropdown-menu">
                                    @if(!$notification->isRead())
                                        <li>
                                            <form method="POST" action="{{ route('notifications.mark-read', $notification->id) }}" class="m-0">
                                                @csrf
                                                <button type="submit" class="dropdown-item">
                                                    <i class="fas fa-check me-2"></i>Marquer comme lu
                                                </button>
                                            </form>
                                        </li>
                                    @endif
                                    
                                    @if($notification->action_url)
                                        <li>
                                            <a class="dropdown-item" href="{{ $notification->action_url }}">
                                                <i class="fas fa-external-link-alt me-2"></i>Voir détails
                                            </a>
                                        </li>
                                    @endif
                                    
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form method="POST" action="{{ route('notifications.destroy', $notification->id) }}" class="m-0">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger"
                                                    onclick="return confirm('Supprimer cette notification ?')">
                                                <i class="fas fa-trash me-2"></i>Supprimer
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Aucune notification</h5>
                <p class="text-muted">Vous n'avez aucune notification pour le moment.</p>
                
                @if(app()->environment('local'))
                    <form method="POST" action="{{ route('notifications.test') }}" class="mt-3">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-plus me-1"></i>Créer une notification de test
                        </button>
                    </form>
                @endif
            </div>
        @endif
    </div>
    
    @if($notifications->hasPages())
        <div class="card-footer">
            {{ $notifications->links() }}
        </div>
    @endif
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@endsection