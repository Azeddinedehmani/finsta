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
        {{-- Only show edit button if user is not a pharmacist --}}
        @can('edit-products')
            <a href="{{ route('inventory.edit', $product->id) }}" class="btn btn-primary">
                <i class="fas fa-edit me-1"></i> Modifier
            </a>
        @endcan
    </div>
</div>

<div class="card">
    <div class="card-header bg-light">
        <h5 class="card-title mb-0">{{ $product->name }}</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4 text-center mb-4">
                @if($product->image_path)
                    <img src="{{ asset('storage/'.$product->image_path) }}" alt="{{ $product->name }}" class="img-fluid rounded mb-3" style="max-height: 250px;">
                @else
                    <div class="bg-secondary text-white rounded d-flex align-items-center justify-content-center mb-3" style="height: 250px;">
                        <i class="fas fa-pills fa-5x"></i>
                    </div>
                @endif
                
                {{-- Only show stock adjustment button if user can manage stock --}}
                @can('manage-stock')
                    <div class="d-grid gap-2">
                        <button class="btn btn-warning" type="button">
                            <i class="fas fa-exchange-alt me-1"></i> Ajuster le stock
                        </button>
                    </div>
                @endcan
            </div>
            
            <div class="col-md-8">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-2">Informations générales</h6>
                        <ul class="list-group mb-4">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <strong>Catégorie</strong>
                                <span>{{ $product->category ? $product->category->name : 'N/A' }}</span>
                            </li>
                            @if($product->dosage)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <strong>Dosage</strong>
                                    <span>{{ $product->dosage }}</span>
                                </li>
                            @endif
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <strong>Code-barres</strong>
                                <span>{{ $product->barcode ?? 'N/A' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <strong>Emplacement</strong>
                                <span>{{ $product->location ?? 'N/A' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <strong>Ordonnance requise</strong>
                                <span>{{ $product->prescription_required ? 'Oui' : 'Non' }}</span>
                            </li>
                        </ul>
                    </div>
                    
                    <div class="col-md-6">
                        <h6 class="text-muted mb-2">Prix et stock</h6>
                        <ul class="list-group mb-4">
                            {{-- Only show pricing information if user can view prices --}}
                            @can('view-prices')
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <strong>Prix d'achat</strong>
                                    <span>{{ number_format($product->purchase_price, 2) }} €</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <strong>Prix de vente</strong>
                                    <span>{{ number_format($product->selling_price, 2) }} €</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <strong>Marge</strong>
                                    <span>{{ number_format($product->selling_price - $product->purchase_price, 2) }} € ({{ number_format((($product->selling_price - $product->purchase_price) / $product->purchase_price) * 100, 2) }}%)</span>
                                </li>
                            @else
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <strong>Prix de vente</strong>
                                    <span>{{ number_format($product->selling_price, 2) }} €</span>
                                </li>
                            @endcan
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <strong>Stock actuel</strong>
                                @if($product->isOutOfStock())
                                    <span class="badge bg-danger">Rupture</span>
                                @elseif($product->isLowStock())
                                    <span class="badge bg-warning text-dark">Faible ({{ $product->stock_quantity }})</span>
                                @else
                                    <span class="badge bg-success">{{ $product->stock_quantity }}</span>
                                @endif
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <strong>Seuil d'alerte</strong>
                                <span>{{ $product->stock_threshold }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-2">Fournisseur</h6>
                        <ul class="list-group mb-4">
                            <li class="list-group-item">
                                @if($product->supplier)
                                    <strong>{{ $product->supplier->name }}</strong><br>
                                    @if($product->supplier->contact_person)
                                        Contact: {{ $product->supplier->contact_person }}<br>
                                    @endif
                                    @if($product->supplier->phone_number)
                                        Tél: {{ $product->supplier->phone_number }}<br>
                                    @endif
                                    @if($product->supplier->email)
                                        Email: {{ $product->supplier->email }}
                                    @endif
                                @else
                                    <p class="text-muted mb-0">Aucun fournisseur associé</p>
                                @endif
                            </li>
                        </ul>
                    </div>
                    
                    <div class="col-md-6">
                        <h6 class="text-muted mb-2">Dates</h6>
                        <ul class="list-group mb-4">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <strong>Date d'expiration</strong>
                                @if($product->expiry_date)
                                    @if($product->isAboutToExpire())
                                        <span class="text-danger">{{ $product->expiry_date->format('d/m/Y') }}</span>
                                    @else
                                        <span>{{ $product->expiry_date->format('d/m/Y') }}</span>
                                    @endif
                                @else
                                    <span>N/A</span>
                                @endif
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <strong>Date de création</strong>
                                <span>{{ $product->created_at->format('d/m/Y') }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <strong>Dernière mise à jour</strong>
                                <span>{{ $product->updated_at->format('d/m/Y') }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
                
                @if($product->description)
                    <h6 class="text-muted mb-2">Description</h6>
                    <div class="card mb-4">
                        <div class="card-body">
                            {{ $product->description }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection