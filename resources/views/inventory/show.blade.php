@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <h2>Détails du produit</h2>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('inventory.index') }}" class="btn btn-secondary me-2">
            <i class="fas fa-arrow-left me-1"></i> Retour
        </a>
        {{-- Only show edit button for admins --}}
        @if(auth()->user()->isAdmin())
            <a href="{{ route('inventory.edit', $product->id) }}" class="btn btn-primary">
                <i class="fas fa-edit me-1"></i> Modifier
            </a>
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

<div class="card">
    <div class="card-header bg-light">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">{{ $product->name }}</h5>
            @if($product->prescription_required)
                <span class="badge bg-info">
                    <i class="fas fa-prescription-bottle me-1"></i>Ordonnance requise
                </span>
            @endif
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4 text-center mb-4">
                @if($product->image_path)
                    <img src="{{ asset('storage/'.$product->image_path) }}" 
                         alt="{{ $product->name }}" 
                         class="img-fluid rounded mb-3" 
                         style="max-height: 300px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                @else
                    <div class="bg-secondary text-white rounded d-flex align-items-center justify-content-center mb-3" 
                         style="height: 300px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                        <i class="fas fa-pills fa-5x opacity-75"></i>
                    </div>
                @endif
                
                {{-- Stock status card --}}
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">État du stock</h6>
                    </div>
                    <div class="card-body">
                        <div class="text-center">
                            @if($product->isOutOfStock())
                                <span class="badge bg-danger fs-6 px-3 py-2">
                                    <i class="fas fa-times-circle me-1"></i>Rupture de stock
                                </span>
                                <div class="mt-2 text-danger">
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    Action requise !
                                </div>
                            @elseif($product->isLowStock())
                                <span class="badge bg-warning text-dark fs-6 px-3 py-2">
                                    <i class="fas fa-exclamation-triangle me-1"></i>Stock faible
                                </span>
                                <div class="mt-2 text-warning">
                                    <strong>{{ $product->stock_quantity }}</strong> unité(s) restante(s)
                                </div>
                            @else
                                <span class="badge bg-success fs-6 px-3 py-2">
                                    <i class="fas fa-check-circle me-1"></i>Stock normal
                                </span>
                                <div class="mt-2 text-success">
                                    <strong>{{ $product->stock_quantity }}</strong> unité(s) disponible(s)
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                
                {{-- Admin-only stock adjustment button --}}
                @if(auth()->user()->isAdmin())
                    <div class="d-grid gap-2">
                        <button class="btn btn-warning" type="button" data-bs-toggle="modal" data-bs-target="#stockAdjustmentModal">
                            <i class="fas fa-exchange-alt me-1"></i> Ajuster le stock
                        </button>
                        <button class="btn btn-danger" type="button" data-bs-toggle="modal" data-bs-target="#deleteProductModal">
                            <i class="fas fa-trash me-1"></i> Supprimer le produit
                        </button>
                    </div>
                @endif
            </div>
            
            <div class="col-md-8">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3">
                            <i class="fas fa-info-circle me-1"></i>Informations générales
                        </h6>
                        <ul class="list-group mb-4">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <strong><i class="fas fa-tags me-2 text-muted"></i>Catégorie</strong>
                                <span class="badge bg-light text-dark">{{ $product->category ? $product->category->name : 'N/A' }}</span>
                            </li>
                            @if($product->dosage)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <strong><i class="fas fa-pills me-2 text-muted"></i>Dosage</strong>
                                    <span>{{ $product->dosage }}</span>
                                </li>
                            @endif
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <strong><i class="fas fa-barcode me-2 text-muted"></i>Code-barres</strong>
                                <span class="font-monospace">{{ $product->barcode ?? 'N/A' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <strong><i class="fas fa-map-marker-alt me-2 text-muted"></i>Emplacement</strong>
                                <span>{{ $product->location ?? 'Non défini' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <strong><i class="fas fa-prescription-bottle me-2 text-muted"></i>Ordonnance</strong>
                                @if($product->prescription_required)
                                    <span class="badge bg-warning text-dark">Requise</span>
                                @else
                                    <span class="badge bg-success">Non requise</span>
                                @endif
                            </li>
                        </ul>
                    </div>
                    
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3">
                            <i class="fas fa-euro-sign me-1"></i>Prix et stock
                        </h6>
                        <ul class="list-group mb-4">
                            {{-- Show pricing information based on user role --}}
                            @if(auth()->user()->isAdmin())
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <strong><i class="fas fa-shopping-cart me-2 text-muted"></i>Prix d'achat</strong>
                                    <span class="text-danger fw-bold">{{ number_format($product->purchase_price, 2) }} €</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <strong><i class="fas fa-tag me-2 text-muted"></i>Prix de vente</strong>
                                    <span class="text-success fw-bold">{{ number_format($product->selling_price, 2) }} €</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <strong><i class="fas fa-chart-line me-2 text-muted"></i>Marge</strong>
                                    @php
                                        $margin = $product->selling_price - $product->purchase_price;
                                        $marginPercent = $product->purchase_price > 0 ? ($margin / $product->purchase_price) * 100 : 0;
                                    @endphp
                                    <span class="fw-bold {{ $margin > 0 ? 'text-success' : 'text-danger' }}">
                                        {{ number_format($margin, 2) }} € ({{ number_format($marginPercent, 2) }}%)
                                    </span>
                                </li>
                            @else
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <strong><i class="fas fa-tag me-2 text-muted"></i>Prix de vente</strong>
                                    <span class="text-success fw-bold">{{ number_format($product->selling_price, 2) }} €</span>
                                </li>
                                <li class="list-group-item">
                                    <div class="text-center text-muted">
                                        <i class="fas fa-lock me-1"></i>
                                        Prix d'achat et marge : accès réservé aux responsables
                                    </div>
                                </li>
                            @endif
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <strong><i class="fas fa-boxes me-2 text-muted"></i>Stock actuel</strong>
                                @if($product->isOutOfStock())
                                    <span class="badge bg-danger fs-6">{{ $product->stock_quantity }}</span>
                                @elseif($product->isLowStock())
                                    <span class="badge bg-warning text-dark fs-6">{{ $product->stock_quantity }}</span>
                                @else
                                    <span class="badge bg-success fs-6">{{ $product->stock_quantity }}</span>
                                @endif
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <strong><i class="fas fa-bell me-2 text-muted"></i>Seuil d'alerte</strong>
                                <span class="badge bg-info">{{ $product->stock_threshold }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3">
                            <i class="fas fa-truck me-1"></i>Fournisseur
                        </h6>
                        <div class="card mb-4">
                            <div class="card-body">
                                @if($product->supplier)
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-building text-primary me-2"></i>
                                        <strong>{{ $product->supplier->name }}</strong>
                                    </div>
                                    @if($product->supplier->contact_person)
                                        <div class="d-flex align-items-center mb-1">
                                            <i class="fas fa-user text-muted me-2"></i>
                                            <small>Contact: {{ $product->supplier->contact_person }}</small>
                                        </div>
                                    @endif
                                    @if($product->supplier->phone_number)
                                        <div class="d-flex align-items-center mb-1">
                                            <i class="fas fa-phone text-muted me-2"></i>
                                            <small>Tél: {{ $product->supplier->phone_number }}</small>
                                        </div>
                                    @endif
                                    @if($product->supplier->email)
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-envelope text-muted me-2"></i>
                                            <small>Email: {{ $product->supplier->email }}</small>
                                        </div>
                                    @endif
                                    @if(auth()->user()->isAdmin())
                                        <div class="mt-2">
                                            <a href="{{ route('suppliers.show', $product->supplier->id) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye me-1"></i>Voir le fournisseur
                                            </a>
                                        </div>
                                    @endif
                                @else
                                    <div class="text-center text-muted">
                                        <i class="fas fa-exclamation-circle mb-2 d-block"></i>
                                        <p class="mb-0">Aucun fournisseur associé</p>
                                        @if(auth()->user()->isAdmin())
                                            <a href="{{ route('inventory.edit', $product->id) }}" class="btn btn-sm btn-outline-primary mt-2">
                                                <i class="fas fa-plus me-1"></i>Associer un fournisseur
                                            </a>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3">
                            <i class="fas fa-calendar me-1"></i>Dates importantes
                        </h6>
                        <ul class="list-group mb-4">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <strong><i class="fas fa-clock me-2 text-muted"></i>Date d'expiration</strong>
                                @if($product->expiry_date)
                                    @if($product->isAboutToExpire(30))
                                        <span class="text-danger fw-bold">
                                            {{ $product->expiry_date->format('d/m/Y') }}
                                            <br><small>({{ $product->expiry_date->diffForHumans() }})</small>
                                        </span>
                                    @else
                                        <span>{{ $product->expiry_date->format('d/m/Y') }}</span>
                                    @endif
                                @else
                                    <span class="text-muted">Non définie</span>
                                @endif
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <strong><i class="fas fa-plus-circle me-2 text-muted"></i>Date de création</strong>
                                <span>{{ $product->created_at->format('d/m/Y à H:i') }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <strong><i class="fas fa-edit me-2 text-muted"></i>Dernière modification</strong>
                                <span>{{ $product->updated_at->format('d/m/Y à H:i') }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
                
                @if($product->description)
                    <h6 class="text-muted mb-3">
                        <i class="fas fa-file-alt me-1"></i>Description
                    </h6>
                    <div class="card mb-4">
                        <div class="card-body">
                            <p class="mb-0">{{ $product->description }}</p>
                        </div>
                    </div>
                @endif

                {{-- Quick actions --}}
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="fas fa-bolt me-1"></i>Actions rapides
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-2">
                            @if(auth()->user()->isAdmin())
                                <a href="{{ route('inventory.edit', $product->id) }}" class="btn btn-primary">
                                    <i class="fas fa-edit me-1"></i>Modifier
                                </a>
                                <a href="{{ route('inventory.create', ['supplier_id' => $product->supplier_id]) }}" class="btn btn-success">
                                    <i class="fas fa-plus me-1"></i>Produit similaire
                                </a>
                                @if($product->supplier)
                                    <a href="{{ route('purchases.create', ['supplier_id' => $product->supplier_id]) }}" class="btn btn-info text-white">
                                        <i class="fas fa-shopping-cart me-1"></i>Commander
                                    </a>
                                @endif
                            @endif
                            <a href="{{ route('sales.create', ['product_id' => $product->id]) }}" class="btn btn-outline-primary">
                                <i class="fas fa-cash-register me-1"></i>Vendre
                            </a>
                            <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-list me-1"></i>Retour à la liste
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Admin-only modals --}}
@if(auth()->user()->isAdmin())
    {{-- Stock Adjustment Modal --}}
    <div class="modal fade" id="stockAdjustmentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-exchange-alt me-2"></i>Ajustement de stock
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('inventory.update', $product->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Stock actuel : <strong>{{ $product->stock_quantity }}</strong> unité(s)
                        </div>
                        
                        <div class="mb-3">
                            <label for="new_stock" class="form-label">Nouveau stock</label>
                            <input type="number" class="form-control" id="new_stock" name="stock_quantity" 
                                   value="{{ $product->stock_quantity }}" min="0" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="adjustment_reason" class="form-label">Raison de l'ajustement</label>
                            <select class="form-select" id="adjustment_reason" name="adjustment_reason">
                                <option value="inventory">Inventaire</option>
                                <option value="damage">Produit endommagé</option>
                                <option value="expired">Produit expiré</option>
                                <option value="theft">Vol/Perte</option>
                                <option value="correction">Correction d'erreur</option>
                                <option value="other">Autre</option>
                            </select>
                        </div>

                        <!-- Hidden fields to maintain other product data -->
                        <input type="hidden" name="name" value="{{ $product->name }}">
                        <input type="hidden" name="category_id" value="{{ $product->category_id }}">
                        <input type="hidden" name="purchase_price" value="{{ $product->purchase_price }}">
                        <input type="hidden" name="selling_price" value="{{ $product->selling_price }}">
                        <input type="hidden" name="stock_threshold" value="{{ $product->stock_threshold }}">
                        @if($product->prescription_required)
                            <input type="hidden" name="prescription_required" value="1">
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-check me-1"></i>Ajuster le stock
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Delete Product Modal --}}
    <div class="modal fade" id="deleteProductModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>Supprimer le produit
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Attention !</strong> Cette action est irréversible.
                    </div>
                    <p>Êtes-vous sûr de vouloir supprimer le produit <strong>{{ $product->name }}</strong> ?</p>
                    @if($product->stock_quantity > 0)
                        <div class="alert alert-warning">
                            <i class="fas fa-boxes me-2"></i>
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
@endif

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Calculate stock difference in adjustment modal
    const stockInput = document.getElementById('new_stock');
    const currentStock = {{ $product->stock_quantity }};
    
    if (stockInput) {
        stockInput.addEventListener('input', function() {
            const newStock = parseInt(this.value) || 0;
            const difference = newStock - currentStock;
            
            // You could add a visual indicator of the change here
            // For example, showing +/- difference
        });
    }
});
</script>
@endsection