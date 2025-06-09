@extends('layouts.app')

@section('content')
<div class="min-vh-100" style="background: linear-gradient(135deg, #e3f2fd 0%, #e8f5e8 100%); margin: -20px; padding: 20px;">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="d-flex align-items-center">
                <div class="me-3" style="width: 50px; height: 50px; background: linear-gradient(180deg, #336699 0%, #4a90e2 100%); border-radius: 15px; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 25px rgba(51, 102, 153, 0.3);">
                    <i class="fas fa-cash-register text-white fa-lg"></i>
                </div>
                <div>
                    <h2 class="mb-0 fw-bold" style="font-family: 'Poppins', sans-serif; color: #2c3e50;">Gestion des ventes</h2>
                    <small class="text-muted">Suivi et gestion des transactions</small>
                </div>
            </div>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('sales.create') }}" class="btn text-white fw-semibold" style="background: linear-gradient(180deg, #336699 0%, #4a90e2 100%); border: none; border-radius: 12px; padding: 12px 24px; box-shadow: 0 4px 15px rgba(51, 102, 153, 0.3); transition: all 0.3s ease;">
                <i class="fas fa-plus me-1"></i> Nouvelle vente
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0" role="alert" style="border-radius: 15px; background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0" role="alert" style="border-radius: 15px; background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-lg h-100" style="border-radius: 15px; background: linear-gradient(180deg, #336699 0%, #4a90e2 100%); color: white; transition: transform 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title opacity-75">Total des ventes</h6>
                            <h4 class="mb-0">{{ number_format($totalSales ?? 0, 2) }} €</h4>
                        </div>
                        <div style="width: 50px; height: 50px; background: rgba(255, 255, 255, 0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-euro-sign fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-lg h-100" style="border-radius: 15px; background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; transition: transform 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title opacity-75">Nombre de ventes</h6>
                            <h4 class="mb-0">{{ $salesCount ?? 0 }}</h4>
                        </div>
                        <div style="width: 50px; height: 50px; background: rgba(255, 255, 255, 0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-shopping-cart fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-lg h-100" style="border-radius: 15px; background: linear-gradient(135deg, #17a2b8 0%, #6f42c1 100%); color: white; transition: transform 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title opacity-75">Vente moyenne</h6>
                            <h4 class="mb-0">{{ number_format($averageSale ?? 0, 2) }} €</h4>
                        </div>
                        <div style="width: 50px; height: 50px; background: rgba(255, 255, 255, 0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-chart-line fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-lg h-100" style="border-radius: 15px; background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%); color: #212529; transition: transform 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title opacity-75">Aujourd'hui</h6>
                            <h4 class="mb-0">{{ isset($sales) ? $sales->where('sale_date', '>=', today())->count() : 0 }}</h4>
                        </div>
                        <div style="width: 50px; height: 50px; background: rgba(33, 37, 41, 0.15); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-calendar-day fa-2x" style="color: #212529;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card mb-4 border-0 shadow-lg" style="border-radius: 15px; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px);">
        <div class="card-header border-0" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 15px 15px 0 0;">
            <h5 class="card-title mb-0 fw-bold">
                <i class="fas fa-filter me-2" style="color: #336699;"></i>
                Filtres et recherche
            </h5>
        </div>
        <div class="card-body">
            <form action="{{ route('sales.index') }}" method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label fw-semibold">Recherche</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           placeholder="N° vente, client..." value="{{ request('search') }}"
                           style="border-radius: 10px; border: 2px solid #e9ecef;">
                </div>
                <div class="col-md-2">
                    <label for="payment_status" class="form-label fw-semibold">Statut paiement</label>
                    <select class="form-select" id="payment_status" name="payment_status" style="border-radius: 10px; border: 2px solid #e9ecef;">
                        <option value="">Tous</option>
                        <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Payé</option>
                        <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>En attente</option>
                        <option value="failed" {{ request('payment_status') == 'failed' ? 'selected' : '' }}>Échoué</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="date_from" class="form-label fw-semibold">Date début</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}"
                           style="border-radius: 10px; border: 2px solid #e9ecef;">
                </div>
                <div class="col-md-2">
                    <label for="date_to" class="form-label fw-semibold">Date fin</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}"
                           style="border-radius: 10px; border: 2px solid #e9ecef;">
                </div>
                <div class="col-md-2">
                    <label for="has_prescription" class="form-label fw-semibold">Ordonnance</label>
                    <select class="form-select" id="has_prescription" name="has_prescription" style="border-radius: 10px; border: 2px solid #e9ecef;">
                        <option value="">Toutes</option>
                        <option value="yes" {{ request('has_prescription') == 'yes' ? 'selected' : '' }}>Avec</option>
                        <option value="no" {{ request('has_prescription') == 'no' ? 'selected' : '' }}>Sans</option>
                    </select>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn w-100" style="background: linear-gradient(180deg, #336699 0%, #4a90e2 100%); color: white; border: none; border-radius: 10px; padding: 12px;">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des ventes -->
    <div class="card border-0 shadow-lg" style="border-radius: 15px; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px);">
        <div class="card-header border-0" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 15px 15px 0 0;">
            <h5 class="card-title mb-0 fw-bold">
                <i class="fas fa-list me-2" style="color: #336699;"></i>
                Liste des ventes ({{ isset($sales) ? $sales->total() : 0 }})
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead style="background-color: #f8f9fa;">
                        <tr>
                            <th class="border-0 fw-semibold" style="color: #336699;">N° Vente</th>
                            <th class="border-0 fw-semibold" style="color: #336699;">Client</th>
                            <th class="border-0 fw-semibold" style="color: #336699;">Vendeur</th>
                            <th class="border-0 fw-semibold" style="color: #336699;">Produits</th>
                            <th class="border-0 fw-semibold" style="color: #336699;">Montant</th>
                            <th class="border-0 fw-semibold" style="color: #336699;">Paiement</th>
                            <th class="border-0 fw-semibold" style="color: #336699;">Ordonnance</th>
                            <th class="border-0 fw-semibold" style="color: #336699;">Date</th>
                            <th class="border-0 fw-semibold" style="color: #336699;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sales ?? [] as $sale)
                            <tr>
                                <td class="border-0">
                                    <div class="d-flex align-items-center">
                                        <div class="me-2" style="width: 32px; height: 32px; background: linear-gradient(180deg, #336699 0%, #4a90e2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-receipt text-white small"></i>
                                        </div>
                                        <strong>{{ $sale->sale_number ?? 'N/A' }}</strong>
                                    </div>
                                </td>
                                <td class="border-0">
                                    @if($sale->client)
                                        <span class="fw-medium">{{ $sale->client->full_name }}</span>
                                    @else
                                        <span class="text-muted fst-italic">Client anonyme</span>
                                    @endif
                                </td>
                                <td class="border-0">
                                    @if($sale->user)
                                        <span class="fw-medium">{{ $sale->user->name }}</span>
                                    @else
                                        <span class="text-muted fst-italic">Utilisateur supprimé</span>
                                    @endif
                                </td>
                                <td class="border-0">
                                    <small>
                                        @if($sale->saleItems && $sale->saleItems->count() > 0)
                                            @foreach($sale->saleItems->take(2) as $item)
                                                @if($item->product)
                                                    {{ $item->product->name }}
                                                    @if($item->quantity > 1) ({{ $item->quantity }}) @endif
                                                @else
                                                    <span class="text-muted">Produit supprimé</span>
                                                @endif
                                                @if(!$loop->last), @endif
                                            @endforeach
                                            @if($sale->saleItems->count() > 2)
                                                <br><span class="text-muted">+{{ $sale->saleItems->count() - 2 }} autres</span>
                                            @endif
                                        @else
                                            <span class="text-muted">Aucun produit</span>
                                        @endif
                                    </small>
                                </td>
                                <td class="border-0">
                                    <strong class="text-success">{{ number_format($sale->total_amount ?? 0, 2) }} €</strong>
                                </td>
                                <td class="border-0">
                                    @php
                                        $statusClass = match($sale->payment_status ?? 'unknown') {
                                            'paid' => 'bg-success',
                                            'pending' => 'bg-warning text-dark',
                                            'failed' => 'bg-danger',
                                            default => 'bg-secondary'
                                        };
                                    @endphp
                                    <span class="badge {{ $statusClass }} rounded-pill">
                                        {{ ucfirst($sale->payment_status ?? 'Inconnu') }}
                                    </span>
                                    <br><small class="text-muted">{{ ucfirst($sale->payment_method ?? 'N/A') }}</small>
                                </td>
                                <td class="border-0">
                                    @if($sale->has_prescription)
                                        <span class="badge bg-success rounded-pill">Oui</span>
                                        @if($sale->prescription_number)
                                            <br><small class="text-muted">{{ $sale->prescription_number }}</small>
                                        @endif
                                    @else
                                        <span class="badge bg-secondary rounded-pill">Non</span>
                                    @endif
                                </td>
                                <td class="border-0">
                                    <span class="fw-medium">{{ $sale->sale_date ? $sale->sale_date->format('d/m/Y H:i') : 'N/A' }}</span>
                                    @if($sale->sale_date && $sale->sale_date < now()->subDays(7))
                                        <br><small class="text-muted">Ancienne</small>
                                    @endif
                                </td>
                                <td class="border-0">
                                    <div class="btn-group">
                                        <!-- Bouton Voir -->
                                        <a href="{{ route('sales.show', $sale->id) }}" class="btn btn-sm" style="background: linear-gradient(180deg, #336699 0%, #4a90e2 100%); color: white; border: none;" title="Voir les détails">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        <!-- Bouton Imprimer -->
                                        <a href="{{ route('sales.print', $sale->id) }}" class="btn btn-sm" style="background: linear-gradient(135deg, #6c757d 0%, #495057 100%); color: white; border: none;" target="_blank" title="Imprimer la facture">
                                            <i class="fas fa-print"></i>
                                        </a>
                                        
                                        <!-- Bouton Modifier - NOUVEAU -->
                                        @if($sale->payment_status !== 'paid' || Auth::user()->isAdmin())
                                            <a href="{{ route('sales.edit', $sale->id) }}" class="btn btn-sm" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; border: none;" title="Modifier la vente">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif
                                        
                                        <!-- Bouton Supprimer - seulement pour les admins et ventes récentes -->
                                        @if($sale->sale_date && $sale->sale_date >= now()->subDays(7) && Auth::user()->isAdmin())
                                            <button type="button" class="btn btn-sm" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; border: none;" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $sale->id }}" title="Supprimer la vente">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                    
                                    @if($sale->sale_date && $sale->sale_date >= now()->subDays(7) && Auth::user()->isAdmin())
                                        <!-- Modal de confirmation de suppression -->
                                        <div class="modal fade" id="deleteModal{{ $sale->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content" style="border-radius: 15px; border: none;">
                                                    <div class="modal-header" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; border-radius: 15px 15px 0 0;">
                                                        <h5 class="modal-title fw-bold">
                                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                                            Confirmer la suppression
                                                        </h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="alert alert-warning border-0" style="border-radius: 10px;">
                                                            <strong><i class="fas fa-exclamation-triangle me-1"></i>Attention!</strong>
                                                            Cette action est irréversible.
                                                        </div>
                                                        <p>Êtes-vous sûr de vouloir supprimer la vente <strong>{{ $sale->sale_number }}</strong>?</p>
                                                        <p><strong>Conséquences :</strong></p>
                                                        <ul>
                                                            <li>La vente sera définitivement supprimée</li>
                                                            <li>Le stock des produits sera restauré</li>
                                                            <li>Cette action ne peut pas être annulée</li>
                                                        </ul>
                                                        
                                                        @if($sale->saleItems && $sale->saleItems->count() > 0)
                                                            <div class="table-responsive">
                                                                <table class="table table-sm">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>Produit</th>
                                                                            <th>Quantité</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @foreach($sale->saleItems as $item)
                                                                            <tr>
                                                                                <td>{{ $item->product ? $item->product->name : 'Produit supprimé' }}</td>
                                                                                <td>{{ $item->quantity }}</td>
                                                                            </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 10px;">Annuler</button>
                                                        <form action="{{ route('sales.destroy', $sale->id) }}" method="POST" style="display: inline;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn text-white" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); border: none; border-radius: 10px;">
                                                                <i class="fas fa-trash me-1"></i>Supprimer définitivement
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 border-0">
                                    <div class="mb-3" style="width: 60px; height: 60px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                                        <i class="fas fa-inbox fa-2x text-muted"></i>
                                    </div>
                                    <h5 class="text-muted mb-2">Aucune vente trouvée</h5>
                                    <p class="text-muted">Essayez de modifier vos critères de recherche</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if(isset($sales) && $sales->hasPages())
            <div class="card-footer border-0" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 0 0 15px 15px;">
                {{ $sales->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>

@endsection

@section('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
    
    body {
        font-family: 'Rubik', sans-serif;
    }
    
    h1, h2, h3, h4, h5, h6 {
        font-family: 'Poppins', sans-serif;
    }
    
    .btn {
        transition: all 0.3s ease;
    }
    
    .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }
    
    .table-hover tbody tr:hover {
        background-color: rgba(51, 102, 153, 0.05);
        transform: scale(1.005);
        transition: all 0.2s ease;
    }
    
    .form-control:focus,
    .form-select:focus {
        border-color: #336699;
        box-shadow: 0 0 0 0.2rem rgba(51, 102, 153, 0.25);
    }
    
    .badge {
        font-size: 0.75rem;
    }
    
    .card {
        transition: transform 0.3s ease;
    }
    
    .modal-content {
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    }
    
    /* Harmonisation avec le sidebar */
    .text-primary {
        color: #336699 !important;
    }
    
    .bg-primary {
        background: linear-gradient(180deg, #336699 0%, #4a90e2 100%) !important;
    }
</style>
@endsection