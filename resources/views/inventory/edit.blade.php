@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <h2>Modifier le produit</h2>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('inventory.show', $product->id) }}" class="btn btn-secondary me-2">
            <i class="fas fa-eye me-1"></i> Voir le produit
        </a>
        <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Retour à la liste
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
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card">
    <div class="card-header bg-light">
        <h5 class="card-title mb-0">
            <i class="fas fa-edit me-2"></i>Modifier le produit : {{ $product->name }}
        </h5>
    </div>
    <div class="card-body">
        <form action="{{ route('inventory.update', $product->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-md-8">
                    <!-- Informations générales -->
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-info-circle me-1"></i>Informations générales
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label">Nom du produit <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', $product->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="category_id" class="form-label">Catégorie <span class="text-danger">*</span></label>
                                    <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
                                        <option value="">Sélectionner une catégorie</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" 
                                                    {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="dosage" class="form-label">Dosage</label>
                                    <input type="text" class="form-control @error('dosage') is-invalid @enderror" 
                                           id="dosage" name="dosage" value="{{ old('dosage', $product->dosage) }}"
                                           placeholder="Ex: 500mg, 10ml, 1g">
                                    @error('dosage')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="barcode" class="form-label">Code-barres</label>
                                    <input type="text" class="form-control @error('barcode') is-invalid @enderror" 
                                           id="barcode" name="barcode" value="{{ old('barcode', $product->barcode) }}">
                                    @error('barcode')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" name="description" rows="3" 
                                          placeholder="Description détaillée du produit">{{ old('description', $product->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Prix et stock -->
                    <div class="card mb-4">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-euro-sign me-1"></i>Prix et stock
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="purchase_price" class="form-label">Prix d'achat (€) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" class="form-control @error('purchase_price') is-invalid @enderror" 
                                               id="purchase_price" name="purchase_price" value="{{ old('purchase_price', $product->purchase_price) }}" required>
                                        <span class="input-group-text">€</span>
                                    </div>
                                    @error('purchase_price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="selling_price" class="form-label">Prix de vente (€) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" class="form-control @error('selling_price') is-invalid @enderror" 
                                               id="selling_price" name="selling_price" value="{{ old('selling_price', $product->selling_price) }}" required>
                                        <span class="input-group-text">€</span>
                                    </div>
                                    @error('selling_price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="stock_quantity" class="form-label">Quantité en stock <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('stock_quantity') is-invalid @enderror" 
                                           id="stock_quantity" name="stock_quantity" value="{{ old('stock_quantity', $product->stock_quantity) }}" required>
                                    @error('stock_quantity')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="stock_threshold" class="form-label">Seuil d'alerte <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('stock_threshold') is-invalid @enderror" 
                                           id="stock_threshold" name="stock_threshold" value="{{ old('stock_threshold', $product->stock_threshold) }}" required>
                                    @error('stock_threshold')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <!-- Calcul de marge -->
                            <div class="alert alert-info">
                                <strong>Marge actuelle :</strong>
                                @php
                                    $margin = $product->selling_price - $product->purchase_price;
                                    $marginPercent = $product->purchase_price > 0 ? ($margin / $product->purchase_price) * 100 : 0;
                                @endphp
                                {{ number_format($margin, 2) }} € 
                                ({{ number_format($marginPercent, 2) }}%)
                            </div>
                        </div>
                    </div>

                    <!-- Autres informations -->
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-cogs me-1"></i>Autres informations
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="supplier_id" class="form-label">Fournisseur</label>
                                    <select class="form-select @error('supplier_id') is-invalid @enderror" id="supplier_id" name="supplier_id">
                                        <option value="">Sélectionner un fournisseur</option>
                                        @foreach($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}" 
                                                    {{ old('supplier_id', $product->supplier_id) == $supplier->id ? 'selected' : '' }}>
                                                {{ $supplier->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('supplier_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="location" class="form-label">Emplacement</label>
                                    <input type="text" class="form-control @error('location') is-invalid @enderror" 
                                           id="location" name="location" value="{{ old('location', $product->location) }}"
                                           placeholder="Ex: A1-01, Rayon B">
                                    @error('location')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="expiry_date" class="form-label">Date d'expiration</label>
                                    <input type="date" class="form-control @error('expiry_date') is-invalid @enderror" 
                                           id="expiry_date" name="expiry_date" 
                                           value="{{ old('expiry_date', $product->expiry_date ? $product->expiry_date->format('Y-m-d') : '') }}">
                                    @error('expiry_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="image" class="form-label">Image du produit</label>
                                    <input type="file" class="form-control @error('image') is-invalid @enderror" 
                                           id="image" name="image" accept="image/*">
                                    @error('image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @if($product->image_path)
                                        <small class="text-muted">Image actuelle disponible</small>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input @error('prescription_required') is-invalid @enderror" 
                                           type="checkbox" value="1" id="prescription_required" name="prescription_required" 
                                           {{ old('prescription_required', $product->prescription_required) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="prescription_required">
                                        <i class="fas fa-prescription-bottle me-1"></i>Ordonnance requise
                                    </label>
                                    @error('prescription_required')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Sidebar avec aperçu et actions -->
                <div class="col-md-4">
                    <!-- Image actuelle -->
                    @if($product->image_path)
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Image actuelle</h6>
                            </div>
                            <div class="card-body text-center">
                                <img src="{{ asset('storage/'.$product->image_path) }}" 
                                     alt="{{ $product->name }}" 
                                     class="img-fluid rounded" 
                                     style="max-height: 200px;">
                            </div>
                        </div>
                    @endif

                    <!-- Statut du stock -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Statut du stock</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Stock actuel:</span>
                                <span class="badge {{ $product->isOutOfStock() ? 'bg-danger' : ($product->isLowStock() ? 'bg-warning text-dark' : 'bg-success') }}">
                                    {{ $product->stock_quantity }}
                                </span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Seuil d'alerte:</span>
                                <span class="badge bg-info">{{ $product->stock_threshold }}</span>
                            </div>
                            @if($product->isLowStock())
                                <div class="alert alert-warning alert-sm">
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    Stock faible !
                                </div>
                            @endif
                            @if($product->isOutOfStock())
                                <div class="alert alert-danger alert-sm">
                                    <i class="fas fa-times-circle me-1"></i>
                                    Rupture de stock !
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Informations de création -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Informations</h6>
                        </div>
                        <div class="card-body">
                            <small class="text-muted">
                                <div class="mb-1">
                                    <strong>Créé le:</strong> {{ $product->created_at->format('d/m/Y H:i') }}
                                </div>
                                <div class="mb-1">
                                    <strong>Modifié le:</strong> {{ $product->updated_at->format('d/m/Y H:i') }}
                                </div>
                                @if($product->expiry_date)
                                    <div class="mb-1">
                                        <strong>Expire le:</strong> 
                                        <span class="{{ $product->isAboutToExpire() ? 'text-danger' : '' }}">
                                            {{ $product->expiry_date->format('d/m/Y') }}
                                        </span>
                                    </div>
                                @endif
                            </small>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Actions</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Mettre à jour
                                </button>
                                <a href="{{ route('inventory.show', $product->id) }}" class="btn btn-info text-white">
                                    <i class="fas fa-eye me-1"></i> Voir le produit
                                </a>
                                <a href="{{ route('inventory.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-list me-1"></i> Retour à la liste
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Calculer la marge en temps réel
    const purchasePriceInput = document.getElementById('purchase_price');
    const sellingPriceInput = document.getElementById('selling_price');
    
    function updateMargin() {
        const purchasePrice = parseFloat(purchasePriceInput.value) || 0;
        const sellingPrice = parseFloat(sellingPriceInput.value) || 0;
        const margin = sellingPrice - purchasePrice;
        const marginPercent = purchasePrice > 0 ? (margin / purchasePrice) * 100 : 0;
        
        const marginDisplay = document.querySelector('.alert-info strong').nextSibling;
        if (marginDisplay) {
            marginDisplay.textContent = ` ${margin.toFixed(2)} € (${marginPercent.toFixed(2)}%)`;
        }
    }
    
    purchasePriceInput.addEventListener('input', updateMargin);
    sellingPriceInput.addEventListener('input', updateMargin);
    
    // Validation du formulaire
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        const purchasePrice = parseFloat(purchasePriceInput.value) || 0;
        const sellingPrice = parseFloat(sellingPriceInput.value) || 0;
        
        if (sellingPrice <= purchasePrice) {
            if (!confirm('Le prix de vente est inférieur ou égal au prix d\'achat. Êtes-vous sûr de continuer ?')) {
                e.preventDefault();
            }
        }
    });
});
</script>
@endsection