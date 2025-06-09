@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <h2>
            @if(auth()->user()->isAdmin())
                Gestion des produits
            @else
                Consultation de l'inventaire
            @endif
        </h2>
        @if(auth()->user()->isPharmacist())
            <small class="text-muted">
                <i class="fas fa-info-circle me-1"></i>
                Vous consultez l'inventaire en mode lecture seule
            </small>
        @endif
    </div>
    <div class="col-md-4 text-end">
        @if(auth()->user()->isAdmin())
            <a href="{{ route('inventory.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Ajouter un produit
            </a>
        @else
            <div class="btn-group">
                <button type="button" class="btn btn-outline-info dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fas fa-filter me-1"></i> Filtres rapides
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{ route('inventory.index', ['stock_status' => 'low']) }}">
                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>Stock faible
                    </a></li>
                    <li><a class="dropdown-item" href="{{ route('inventory.index', ['stock_status' => 'out']) }}">
                        <i class="fas fa-times-circle text-danger me-2"></i>Rupture de stock
                    </a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="{{ route('inventory.index') }}">
                        <i class="fas fa-list me-2"></i>Tous les produits
                    </a></li>
                </ul>
            </div>
        @endif
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<!-- Quick Stats for Pharmacists -->
@if(auth()->user()->isPharmacist())
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title">Total Produits</h6>
                        <h4 class="mb-0">{{ \App\Models\Product::count() }}</h4>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-pills fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title">Stock Faible</h6>
                        <h4 class="mb-0">{{ \App\Models\Product::whereColumn('stock_quantity', '<=', 'stock_threshold')->count() }}</h4>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-exclamation-triangle fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title">Rupture</h6>
                        <h4 class="mb-0">{{ \App\Models\Product::where('stock_quantity', '<=', 0)->count() }}</h4>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-times-circle fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title">Expire Bientôt</h6>
                        <h4 class="mb-0">{{ \App\Models\Product::where('expiry_date', '<=', now()->addDays(30))->where('expiry_date', '>', now())->count() }}</h4>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-clock fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="card mb-4">
    <div class="card-header bg-light">
        <h5 class="card-title mb-0">Filtres et recherche</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('inventory.index') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="search" class="form-label">Recherche</label>
                <input type="text" class="form-control" id="search" name="search" placeholder="Nom, code barre..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label for="category" class="form-label">Catégorie</label>
                <select class="form-select" id="category" name="category">
                    <option value="">Toutes les catégories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @if(auth()->user()->isAdmin() && $suppliers->isNotEmpty())
            <div class="col-md-2">
                <label for="supplier" class="form-label">Fournisseur</label>
                <select class="form-select" id="supplier" name="supplier">
                    <option value="">Tous les fournisseurs</option>
                    <option value="none" {{ request('supplier') == 'none' ? 'selected' : '' }}>Sans fournisseur</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ request('supplier') == $supplier->id ? 'selected' : '' }}>
                            {{ $supplier->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="col-md-2">
                <label for="stock_status" class="form-label">État du stock</label>
                <select class="form-select" id="stock_status" name="stock_status">
                    <option value="">Tous les produits</option>
                    <option value="low" {{ request('stock_status') == 'low' ? 'selected' : '' }}>Stock faible</option>
                    <option value="out" {{ request('stock_status') == 'out' ? 'selected' : '' }}>Rupture de stock</option>
                </select>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-1"></i> Filtrer
                </button>
            </div>
            @if(request()->hasAny(['search', 'category', 'supplier', 'stock_status']))
            <div class="col-md-1 d-flex align-items-end">
                <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-times me-1"></i> Reset
                </a>
            </div>
            @endif
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header bg-light">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Liste des produits</h5>
            @if(auth()->user()->isPharmacist())
                <small class="text-muted">
                    <i class="fas fa-eye me-1"></i>{{ $products->total() }} produit(s) trouvé(s)
                </small>
            @endif
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Produit</th>
                        <th>Catégorie</th>
                        @if(auth()->user()->isAdmin())
                            <th>Prix d'achat</th>
                        @endif
                        <th>Prix de vente</th>
                        <th>Stock</th>
                        <th>Emplacement</th>
                        @if(auth()->user()->isAdmin() && $suppliers->isNotEmpty())
                            <th>Fournisseur</th>
                        @endif
                        <th>Expiration</th>
                        <th width="120">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr class="{{ $product->isOutOfStock() ? 'table-danger' : ($product->isLowStock() ? 'table-warning' : '') }}">
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($product->image_path)
                                        <img src="{{ asset('storage/'.$product->image_path) }}" 
                                             alt="{{ $product->name }}" 
                                             class="img-thumbnail me-2" 
                                             style="width: 45px; height: 45px; object-fit: cover;">
                                    @else
                                        <div class="bg-secondary text-white rounded me-2 d-flex align-items-center justify-content-center" 
                                             style="width: 45px; height: 45px; min-width: 45px;">
                                            <i class="fas fa-pills"></i>
                                        </div>
                                    @endif
                                    <div class="flex-grow-1">
                                        <div class="fw-bold">{{ $product->name }}</div>
                                        @if($product->dosage)
                                            <small class="text-muted">{{ $product->dosage }}</small>
                                        @endif
                                        @if($product->prescription_required)
                                            <br><span class="badge bg-info text-white" style="font-size: 0.7rem;">
                                                <i class="fas fa-prescription-bottle me-1"></i>Ordonnance
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">
                                    {{ $product->category ? $product->category->name : 'N/A' }}
                                </span>
                            </td>
                            @if(auth()->user()->isAdmin())
                            <td>
                                <span class="text-muted">{{ number_format($product->purchase_price, 2) }} €</span>
                            </td>
                            @endif
                            <td>
                                <span class="fw-bold text-success">{{ number_format($product->selling_price, 2) }} €</span>
                            </td>
                            <td>
                                @if($product->isOutOfStock())
                                    <span class="badge bg-danger">
                                        <i class="fas fa-times-circle me-1"></i>Rupture
                                    </span>
                                @elseif($product->isLowStock())
                                    <span class="badge bg-warning text-dark">
                                        <i class="fas fa-exclamation-triangle me-1"></i>{{ $product->stock_quantity }}
                                    </span>
                                @else
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle me-1"></i>{{ $product->stock_quantity }}
                                    </span>
                                @endif
                                <br><small class="text-muted">Seuil: {{ $product->stock_threshold }}</small>
                            </td>
                            <td>
                                <small class="text-muted">
                                    {{ $product->location ?? 'Non défini' }}
                                </small>
                            </td>
                            @if(auth()->user()->isAdmin() && $suppliers->isNotEmpty())
                            <td>
                                @if($product->supplier)
                                    <small class="text-muted">{{ $product->supplier->name }}</small>
                                @else
                                    <small class="text-muted">Aucun</small>
                                @endif
                            </td>
                            @endif
                            <td>
                                @if($product->expiry_date)
                                    @if($product->isAboutToExpire(30))
                                        <span class="text-danger fw-bold">
                                            <i class="fas fa-clock me-1"></i>{{ $product->expiry_date->format('d/m/Y') }}
                                        </span>
                                        <br><small class="text-danger">{{ $product->expiry_date->diffForHumans() }}</small>
                                    @else
                                        <span class="text-muted">{{ $product->expiry_date->format('d/m/Y') }}</span>
                                    @endif
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if(auth()->user()->isAdmin())
                                    <!-- Admin can view, edit, and delete -->
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('inventory.show', $product->id) }}" 
                                           class="btn btn-sm btn-info text-white" 
                                           title="Voir les détails">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('inventory.edit', $product->id) }}" 
                                           class="btn btn-sm btn-primary" 
                                           title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-sm btn-danger" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#deleteModal{{ $product->id }}"
                                                title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    
                                    <!-- Modal de confirmation de suppression -->
                                    <div class="modal fade" id="deleteModal{{ $product->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Confirmer la suppression</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="alert alert-warning">
                                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                                        <strong>Attention !</strong> Cette action est irréversible.
                                                    </div>
                                                    <p>Êtes-vous sûr de vouloir supprimer le produit <strong>{{ $product->name }}</strong> ?</p>
                                                    @if($product->stock_quantity > 0)
                                                        <div class="alert alert-info">
                                                            <i class="fas fa-info-circle me-2"></i>
                                                            Ce produit a encore <strong>{{ $product->stock_quantity }}</strong> unité(s) en stock.
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                    <form action="{{ route('inventory.destroy', $product->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger">
                                                            <i class="fas fa-trash me-1"></i>Supprimer définitivement
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <!-- Pharmacist can only view -->
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('inventory.show', $product->id) }}" 
                                           class="btn btn-sm btn-info text-white" 
                                           title="Voir les détails">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-secondary" 
                                                disabled
                                                title="Modification non autorisée">
                                            <i class="fas fa-lock"></i>
                                        </button>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->isAdmin() ? (count($suppliers) > 0 ? 9 : 8) : 7 }}" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-search fa-2x mb-3 d-block"></i>
                                    <h5>Aucun produit trouvé</h5>
                                    <p class="mb-0">
                                        @if(request()->hasAny(['search', 'category', 'supplier', 'stock_status']))
                                            Aucun produit ne correspond à vos critères de recherche.
                                            <br><a href="{{ route('inventory.index') }}" class="btn btn-sm btn-outline-primary mt-2">
                                                <i class="fas fa-times me-1"></i>Réinitialiser les filtres
                                            </a>
                                        @else
                                            Il n'y a aucun produit dans l'inventaire.
                                            @if(auth()->user()->isAdmin())
                                                <br><a href="{{ route('inventory.create') }}" class="btn btn-sm btn-primary mt-2">
                                                    <i class="fas fa-plus me-1"></i>Ajouter le premier produit
                                                </a>
                                            @endif
                                        @endif
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($products->hasPages())
        <div class="card-footer">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted">
                    Affichage de {{ $products->firstItem() }} à {{ $products->lastItem() }} sur {{ $products->total() }} produits
                </div>
                <div>
                    {{ $products->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    @endif
</div>

@if(auth()->user()->isPharmacist())
<!-- Help/Info section for pharmacists -->
<div class="card mt-4">
    <div class="card-header bg-info text-white">
        <h6 class="mb-0">
            <i class="fas fa-info-circle me-2"></i>Informations pour les pharmaciens
        </h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h6 class="text-muted">Légende des couleurs</h6>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <span class="badge bg-success me-2"><i class="fas fa-check-circle"></i></span>
                        Stock normal (au-dessus du seuil)
                    </li>
                    <li class="mb-2">
                        <span class="badge bg-warning text-dark me-2"><i class="fas fa-exclamation-triangle"></i></span>
                        Stock faible (en dessous du seuil)
                    </li>
                    <li class="mb-2">
                        <span class="badge bg-danger me-2"><i class="fas fa-times-circle"></i></span>
                        Rupture de stock (0 unité)
                    </li>
                </ul>
            </div>
            <div class="col-md-6">
                <h6 class="text-muted">Actions disponibles</h6>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <i class="fas fa-eye text-info me-2"></i>
                        Consulter les détails d'un produit
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-filter text-primary me-2"></i>
                        Filtrer et rechercher des produits
                    </li>
                    <li class="mb-2 text-muted">
                        <i class="fas fa-lock me-2"></i>
                        Modification et suppression réservées aux responsables
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endif

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit form on filter change for better UX
    const filterSelects = document.querySelectorAll('#category, #supplier, #stock_status');
    filterSelects.forEach(select => {
        select.addEventListener('change', function() {
            // Add a small delay to improve UX
            setTimeout(() => {
                this.closest('form').submit();
            }, 100);
        });
    });
    
    // Enhanced search with enter key
    const searchInput = document.getElementById('search');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                this.closest('form').submit();
            }
        });
    }
    
    // Highlight current filters
    const currentFilters = ['{{ request("search") }}', '{{ request("category") }}', '{{ request("supplier") }}', '{{ request("stock_status") }}'];
    const hasActiveFilters = currentFilters.some(filter => filter && filter.trim() !== '');
    
    if (hasActiveFilters) {
        const filterCard = document.querySelector('.card-header h5.card-title');
        if (filterCard && filterCard.textContent.includes('Filtres')) {
            filterCard.innerHTML = '<i class="fas fa-filter me-2 text-primary"></i>Filtres et recherche <small class="badge bg-primary ms-2">Actifs</small>';
        }
    }
});

// Tooltip initialization for disabled buttons
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
});
</script>
@endsection