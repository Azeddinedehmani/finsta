@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <h2>{{ $supplier->name }}</h2>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('suppliers.index') }}" class="btn btn-secondary me-2">
            <i class="fas fa-arrow-left me-1"></i> Retour
        </a>
        <a href="{{ route('suppliers.edit', $supplier->id) }}" class="btn btn-primary">
            <i class="fas fa-edit me-1"></i> Modifier
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-truck me-2"></i>Informations du fournisseur
                </h5>
                <span class="badge {{ $supplier->active ? 'bg-success' : 'bg-secondary' }}">
                    {{ $supplier->active ? 'Actif' : 'Inactif' }}
                </span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Nom:</strong>
                                <span>{{ $supplier->name }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Personne de contact:</strong>
                                <span>{{ $supplier->contact_person ?? 'Non renseigné' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Téléphone:</strong>
                                <span>{{ $supplier->phone_number ?? 'Non renseigné' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Email:</strong>
                                <span>{{ $supplier->email ?? 'Non renseigné' }}</span>
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Statut:</strong>
                                <span>
                                    @if($supplier->active)
                                        <span class="badge bg-success">Actif</span>
                                    @else
                                        <span class="badge bg-secondary">Inactif</span>
                                    @endif
                                </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Créé le:</strong>
                                <span>{{ $supplier->created_at->format('d/m/Y H:i') }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Modifié le:</strong>
                                <span>{{ $supplier->updated_at->format('d/m/Y H:i') }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Nombre de produits:</strong>
                                <span class="badge bg-primary">{{ $totalProducts }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
                
                @if($supplier->address)
                    <div class="mt-3">
                        <strong>Adresse:</strong>
                        <p class="mt-2 mb-0">{{ $supplier->address }}</p>
                    </div>
                @endif

                @if($supplier->notes)
                    <div class="mt-3">
                        <strong>Notes:</strong>
                        <p class="mt-2 mb-0">{{ $supplier->notes }}</p>
                    </div>
                @endif
            </div>
        </div>

        @if($recentProducts->count() > 0)
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Produits récents ({{ $recentProducts->count() }})</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Produit</th>
                                    <th>Catégorie</th>
                                    <th class="text-end">Prix d'achat</th>
                                    <th class="text-center">Stock</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentProducts as $product)
                                    <tr>
                                        <td>
                                            <div>
                                                <strong>{{ $product->name }}</strong>
                                                @if($product->dosage)
                                                    <br><small class="text-muted">{{ $product->dosage }}</small>
                                                @endif
                                            </div>
                                        </td>
                                        <td>{{ $product->category ? $product->category->name : 'N/A' }}</td>
                                        <td class="text-end">{{ number_format($product->purchase_price, 2) }} €</td>
                                        <td class="text-center">
                                            @if($product->isOutOfStock())
                                                <span class="badge bg-danger">Rupture</span>
                                            @elseif($product->isLowStock())
                                                <span class="badge bg-warning text-dark">Faible ({{ $product->stock_quantity }})</span>
                                            @else
                                                <span class="badge bg-success">{{ $product->stock_quantity }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('inventory.show', $product->id) }}" class="btn btn-sm btn-info text-white">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($totalProducts > $recentProducts->count())
                    <div class="card-footer text-center">
                        <a href="{{ route('inventory.index', ['supplier' => $supplier->id]) }}" class="btn btn-sm btn-primary">
                            Voir tous les produits ({{ $totalProducts }})
                        </a>
                    </div>
                @endif
            </div>
        @endif
    </div>
    
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Statistiques</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between">
                        <strong>Total produits:</strong>
                        <span class="badge bg-primary">{{ $totalProducts }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <strong>Produits en stock faible:</strong>
                        <span class="badge {{ $lowStockProducts > 0 ? 'bg-warning text-dark' : 'bg-success' }}">
                            {{ $lowStockProducts }}
                        </span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <strong>Valeur totale du stock:</strong>
                        <span class="text-success">
                            {{ number_format($supplier->products()->sum(\DB::raw('purchase_price * stock_quantity')), 2) }} €
                        </span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Actions rapides</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('suppliers.edit', $supplier->id) }}" class="btn btn-primary">
                        <i class="fas fa-edit me-1"></i> Modifier les informations
                    </a>
                    <a href="{{ route('inventory.create', ['supplier_id' => $supplier->id]) }}" class="btn btn-success">
                        <i class="fas fa-plus me-1"></i> Ajouter un produit
                    </a>
                    @if($totalProducts > 0)
                        <a href="{{ route('inventory.index', ['supplier' => $supplier->id]) }}" class="btn btn-outline-primary">
                            <i class="fas fa-boxes me-1"></i> Voir tous les produits
                        </a>
                    @endif
                    <a href="{{ route('purchases.create', ['supplier_id' => $supplier->id]) }}" class="btn btn-outline-success">
                        <i class="fas fa-shopping-cart me-1"></i> Nouvelle commande
                    </a>
                    @if($supplier->products_count == 0)
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                            <i class="fas fa-trash me-1"></i> Supprimer le fournisseur
                        </button>
                    @endif
                </div>
            </div>
        </div>

        @if($supplier->contact_person || $supplier->phone_number || $supplier->email)
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Contact rapide</h5>
                </div>
                <div class="card-body">
                    @if($supplier->phone_number)
                        <div class="mb-2">
                            <a href="tel:{{ $supplier->phone_number }}" class="btn btn-outline-success btn-sm w-100">
                                <i class="fas fa-phone me-1"></i> Appeler
                            </a>
                        </div>
                    @endif
                    @if($supplier->email)
                        <div class="mb-2">
                            <a href="mailto:{{ $supplier->email }}" class="btn btn-outline-primary btn-sm w-100">
                                <i class="fas fa-envelope me-1"></i> Envoyer un email
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>

@if($supplier->products_count == 0)
    <!-- Modal de confirmation de suppression -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmer la suppression</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir supprimer le fournisseur <strong>{{ $supplier->name }}</strong>?</p>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Attention :</strong> Cette action est irréversible.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <form action="{{ route('suppliers.destroy', $supplier->id) }}" method="POST" style="display: inline;">
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
@endsection