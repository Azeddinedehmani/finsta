@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <h2>Gestion de l'inventaire</h2>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('inventory.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Nouveau produit
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<!-- Statistiques des produits -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Total produits</h6>
                        <h4 class="mb-0">{{ $products->total() }}</h4>
                    </div>
                    <i class="fas fa-boxes fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Stock faible</h6>
                        <h4 class="mb-0">{{ $products->where('stock_quantity', '<=', function($product) { return $product->stock_threshold; })->count() }}</h4>
                    </div>
                    <i class="fas fa-exclamation-triangle fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Rupture de stock</h6>
                        <h4 class="mb-0">{{ $products->where('stock_quantity', '<=', 0)->count() }}</h4>
                    </div>
                    <i class="fas fa-times-circle fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Catégories</h6>
                        <h4 class="mb-0">{{ $categories->count() }}</h4>
                    </div>
                    <i class="fas fa-tags fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header bg-light">
        <h5 class="card-title mb-0">Filtres et recherche</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('inventory.index') }}" method="GET" class="row g-3">
            <div class="col-md-4">
                <label for="search" class="form-label">Recherche</label>
                <input type="text" class="form-control" id="search" name="search" 
                       placeholder="Nom, code-barres, description..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label for="category" class="form-label">Catégorie</label>
                <select class="form-select" id="category" name="category">
                    <option value="">Toutes</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @if(auth()->user()->isAdmin())
                <div class="col-md-2">
                    <label for="supplier" class="form-label">Fournisseur</label>
                    <select class="form-select" id="supplier" name="supplier">
                        <option value="">Tous</option>
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
                <label for="stock_status" class="form-label">Statut stock</label>
                <select class="form-select" id="stock_status" name="stock_status">
                    <option value="">Tous</option>
                    <option value="low" {{ request('stock_status') == 'low' ? 'selected' : '' }}>Stock faible</option>
                    <option value="out" {{ request('stock_status') == 'out' ? 'selected' : '' }}>Rupture</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search"></i> Rechercher
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Liste des produits ({{ $products->total() }})</h5>
        <div class="btn-group btn-group-sm">
            <button type="button" class="btn btn-outline-secondary" id="viewGrid">
                <i class="fas fa-th"></i>
            </button>
            <button type="button" class="btn btn-outline-secondary active" id="viewList">
                <i class="fas fa-list"></i>
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive" id="listView">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Produit</th>
                        <th>Catégorie</th>
                        @if(auth()->user()->isAdmin())
                            <th>Fournisseur</th>
                        @endif
                        <th>Prix d'achat</th>
                        <th>Prix de vente</th>
                        <th>Stock</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr class="{{ $product->isOutOfStock() ? 'table-danger' : ($product->isLowStock() ? 'table-warning' : '') }}">
                            <td>
                                @if($product->image_path)
                                    <img src="{{ asset('storage/' . $product->image_path) }}" 
                                         alt="{{ $product->name }}" 
                                         class="img-thumbnail" 
                                         style="width: 50px; height: 50px; object-fit: cover;">
                                @else
                                    <div class="bg-light d-flex align-items-center justify-content-center" 
                                         style="width: 50px; height: 50px; border-radius: 4px;">
                                        <i class="fas fa-image text-muted"></i>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div>
                                    <strong>{{ $product->name }}</strong>
                                    @if($product->dosage)
                                        <br><small class="text-muted">{{ $product->dosage }}</small>
                                    @endif
                                    @if($product->barcode)
                                        <br><small class="text-info">{{ $product->barcode }}</small>
                                    @endif
                                    @if($product->prescription_required)
                                        <br><small class="badge bg-warning text-dark">Ordonnance requise</small>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $product->category->name ?? 'Sans catégorie' }}</span>
                            </td>
                            @if(auth()->user()->isAdmin())
                                <td>
                                    @if($product->supplier)
                                        <small>{{ $product->supplier->name }}</small>
                                    @else
                                        <small class="text-muted">Non défini</small>
                                    @endif
                                </td>
                            @endif
                            <td>{{ number_format($product->purchase_price, 2) }} MAD</td>
                            <td><strong>{{ number_format($product->selling_price, 2) }} MAD</strong></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="{{ $product->isOutOfStock() ? 'text-danger' : ($product->isLowStock() ? 'text-warning' : 'text-success') }}">
                                        {{ $product->stock_quantity }}
                                    </span>
                                    <small class="text-muted ms-1">/ {{ $product->stock_threshold }}</small>
                                    @if($product->isLowStock() || $product->isOutOfStock())
                                        <i class="fas fa-exclamation-triangle text-warning ms-1" title="Stock faible"></i>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($product->isOutOfStock())
                                    <span class="badge bg-danger">Rupture</span>
                                @elseif($product->isLowStock())
                                    <span class="badge bg-warning text-dark">Stock faible</span>
                                @else
                                    <span class="badge bg-success">En stock</span>
                                @endif
                                
                                @if($product->expiry_date)
                                    @if($product->isAboutToExpire(30))
                                        <br><small class="text-danger">Expire le {{ $product->expiry_date->format('d/m/Y') }}</small>
                                    @elseif($product->isAboutToExpire(90))
                                        <br><small class="text-warning">Expire le {{ $product->expiry_date->format('d/m/Y') }}</small>
                                    @endif
                                @endif
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('inventory.show', $product->id) }}" class="btn btn-sm btn-info text-white" title="Voir">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('inventory.edit', $product->id) }}" class="btn btn-sm btn-primary" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if(auth()->user()->canDeleteProduct($product))
                                        <button type="button" 
                                                class="btn btn-sm btn-danger delete-product-btn" 
                                                data-product-id="{{ $product->id }}"
                                                data-product-name="{{ $product->name }}"
                                                title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->isAdmin() ? '9' : '8' }}" class="text-center py-4">
                                <div class="d-flex flex-column align-items-center">
                                    <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                                    <p class="text-muted mb-0">Aucun produit trouvé</p>
                                    <a href="{{ route('inventory.create') }}" class="btn btn-primary mt-3">
                                        <i class="fas fa-plus me-1"></i> Ajouter le premier produit
                                    </a>
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
            {{ $products->appends(request()->query())->links() }}
        </div>
    @endif
</div>

{{-- DELETE CONFIRMATION MODAL --}}
<div class="modal fade" id="deleteProductModal" tabindex="-1" aria-labelledby="deleteProductModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteProductModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>Confirmer la suppression
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>Êtes-vous sûr de vouloir supprimer ce produit ?</strong></p>
                <div class="alert alert-warning">
                    <p class="mb-0"><strong>Produit :</strong> <span id="modalProductName"></span></p>
                </div>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Attention :</strong> Cette action est irréversible !
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <form id="deleteProductForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i>Supprimer le produit
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize modal
    const deleteModalEl = document.getElementById('deleteProductModal');
    const deleteModal = new bootstrap.Modal(deleteModalEl);
    
    // Get elements
    const deleteButtons = document.querySelectorAll('.delete-product-btn');
    const deleteForm = document.getElementById('deleteProductForm');
    const modalProductName = document.getElementById('modalProductName');

    // Add event listeners to delete buttons
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const productId = this.getAttribute('data-product-id');
            const productName = this.getAttribute('data-product-name');

            // Set modal data
            modalProductName.textContent = productName;
            deleteForm.action = `/inventory/${productId}`;

            // Show modal
            deleteModal.show();
        });
    });

    // View switcher
    const viewGrid = document.getElementById('viewGrid');
    const viewList = document.getElementById('viewList');
    
    if (viewGrid && viewList) {
        viewGrid.addEventListener('click', function() {
            viewGrid.classList.add('active');
            viewList.classList.remove('active');
            // Add grid view implementation here if needed
        });
        
        viewList.addEventListener('click', function() {
            viewList.classList.add('active');
            viewGrid.classList.remove('active');
            // Add list view implementation here if needed
        });
    }

    // Auto-hide alerts
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            if (alert.parentNode) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        }, 5000);
    });
});
</script>
@endpush