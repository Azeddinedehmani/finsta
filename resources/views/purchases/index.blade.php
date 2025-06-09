@extends('layouts.app')

@section('content')
<div class="min-vh-100" style="background: linear-gradient(135deg, #e3f2fd 0%, #e8f5e8 100%); margin: -20px; padding: 20px;">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="d-flex align-items-center">
                <div class="me-3" style="width: 50px; height: 50px; background: linear-gradient(180deg, #336699 0%, #4a90e2 100%); border-radius: 15px; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 25px rgba(51, 102, 153, 0.3);">
                    <i class="fas fa-shopping-cart text-white fa-lg"></i>
                </div>
                <div>
                    <h2 class="mb-0 fw-bold" style="font-family: 'Poppins', sans-serif; color: #2c3e50;">Gestion des achats</h2>
                    <small class="text-muted">Suivi et gestion des commandes</small>
                </div>
            </div>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('purchases.create') }}" class="btn text-white fw-semibold" style="background: linear-gradient(180deg, #336699 0%, #4a90e2 100%); border: none; border-radius: 12px; padding: 12px 24px; box-shadow: 0 4px 15px rgba(51, 102, 153, 0.3); transition: all 0.3s ease;">
                <i class="fas fa-plus me-1"></i> Nouvelle commande
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
            <i class="fas fa-exclamation-triangle me-2"></i>
            @foreach($errors->all() as $error)
                {{ $error }}<br>
            @endforeach
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
                            <h6 class="card-title opacity-75">Total achats</h6>
                            <h4 class="mb-0">{{ number_format($totalPurchases, 2) }} €</h4>
                        </div>
                        <div style="width: 50px; height: 50px; background: rgba(255, 255, 255, 0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-shopping-cart fa-2x"></i>
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
                            <h6 class="card-title opacity-75">En attente</h6>
                            <h4 class="mb-0">{{ $pendingCount }}</h4>
                        </div>
                        <div style="width: 50px; height: 50px; background: rgba(33, 37, 41, 0.15); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-clock fa-2x" style="color: #212529;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-lg h-100" style="border-radius: 15px; background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; transition: transform 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title opacity-75">En retard</h6>
                            <h4 class="mb-0">{{ $overdueCount }}</h4>
                        </div>
                        <div style="width: 50px; height: 50px; background: rgba(255, 255, 255, 0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-exclamation-triangle fa-2x"></i>
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
                            <h6 class="card-title opacity-75">Reçues</h6>
                            <h4 class="mb-0">{{ $receivedCount }}</h4>
                        </div>
                        <div style="width: 50px; height: 50px; background: rgba(255, 255, 255, 0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-check-circle fa-2x"></i>
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
            <form action="{{ route('purchases.index') }}" method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label fw-semibold">Recherche</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           placeholder="N° commande, fournisseur..." value="{{ request('search') }}"
                           style="border-radius: 10px; border: 2px solid #e9ecef;">
                </div>
                <div class="col-md-2">
                    <label for="status" class="form-label fw-semibold">Statut</label>
                    <select class="form-select" id="status" name="status" style="border-radius: 10px; border: 2px solid #e9ecef;">
                        <option value="">Tous</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>En attente</option>
                        <option value="partially_received" {{ request('status') == 'partially_received' ? 'selected' : '' }}>Partiellement reçu</option>
                        <option value="received" {{ request('status') == 'received' ? 'selected' : '' }}>Reçu</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Annulé</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="supplier" class="form-label fw-semibold">Fournisseur</label>
                    <select class="form-select" id="supplier" name="supplier" style="border-radius: 10px; border: 2px solid #e9ecef;">
                        <option value="">Tous</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ request('supplier') == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
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
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn w-100" style="background: linear-gradient(180deg, #336699 0%, #4a90e2 100%); color: white; border: none; border-radius: 10px; padding: 12px;">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des commandes -->
    <div class="card border-0 shadow-lg" style="border-radius: 15px; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px);">
        <div class="card-header border-0" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 15px 15px 0 0;">
            <h5 class="card-title mb-0 fw-bold">
                <i class="fas fa-list me-2" style="color: #336699;"></i>
                Liste des commandes ({{ $purchases->total() }})
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead style="background-color: #f8f9fa;">
                        <tr>
                            <th class="border-0 fw-semibold" style="color: #336699;">N° Commande</th>
                            <th class="border-0 fw-semibold" style="color: #336699;">Fournisseur</th>
                            <th class="border-0 fw-semibold" style="color: #336699;">Date commande</th>
                            <th class="border-0 fw-semibold" style="color: #336699;">Date prévue</th>
                            <th class="border-0 fw-semibold" style="color: #336699;">Montant</th>
                            <th class="border-0 fw-semibold" style="color: #336699;">Statut</th>
                            <th class="border-0 fw-semibold" style="color: #336699;">Progression</th>
                            <th class="border-0 fw-semibold" style="color: #336699;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchases as $purchase)
                            <tr class="{{ $purchase->status === 'cancelled' ? 'table-secondary' : '' }}">
                                <td class="border-0">
                                    <div class="d-flex align-items-center">
                                        <div class="me-2" style="width: 32px; height: 32px; background: linear-gradient(180deg, #336699 0%, #4a90e2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-file-invoice text-white small"></i>
                                        </div>
                                        <div>
                                            <strong>{{ $purchase->purchase_number }}</strong>
                                            @if($purchase->expected_date && $purchase->expected_date->isPast() && $purchase->status === 'pending')
                                                <br><small class="text-danger">
                                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                                    En retard ({{ $purchase->expected_date->diffForHumans() }})
                                                </small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="border-0">
                                    <span class="fw-medium">{{ $purchase->supplier->name }}</span>
                                    @if($purchase->supplier->contact_person)
                                        <br><small class="text-muted">{{ $purchase->supplier->contact_person }}</small>
                                    @endif
                                </td>
                                <td class="border-0">
                                    <span class="fw-medium">{{ $purchase->order_date->format('d/m/Y') }}</span>
                                </td>
                                <td class="border-0">
                                    @if($purchase->expected_date)
                                        <span class="fw-medium">{{ $purchase->expected_date->format('d/m/Y') }}</span>
                                        @if($purchase->expected_date->isPast() && $purchase->status === 'pending')
                                            <br><small class="text-danger">En retard</small>
                                        @endif
                                    @else
                                        <span class="text-muted fst-italic">Non définie</span>
                                    @endif
                                </td>
                                <td class="border-0">
                                    <strong class="text-success">{{ number_format($purchase->total_amount, 2) }} €</strong>
                                    <br><small class="text-muted">HT: {{ number_format($purchase->subtotal, 2) }} €</small>
                                </td>
                                <td class="border-0">
                                    @php
                                        $statusClass = match($purchase->status) {
                                            'pending' => 'bg-warning text-dark',
                                            'partially_received' => 'bg-info',
                                            'received' => 'bg-success',
                                            'cancelled' => 'bg-secondary',
                                            default => 'bg-secondary'
                                        };
                                    @endphp
                                    <span class="badge {{ $statusClass }} rounded-pill">
                                        {{ $purchase->status_label }}
                                    </span>
                                </td>
                                <td class="border-0">
                                    @if($purchase->status !== 'cancelled')
                                        <div class="progress mb-1" style="height: 20px; border-radius: 10px;">
                                            <div class="progress-bar {{ $purchase->status === 'received' ? 'bg-success' : 'bg-info' }}" 
                                                 role="progressbar" 
                                                 style="width: {{ $purchase->progress_percentage }}%; border-radius: 10px;"
                                                 aria-valuenow="{{ $purchase->progress_percentage }}" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                                {{ $purchase->progress_percentage }}%
                                            </div>
                                        </div>
                                        <small class="text-muted">{{ $purchase->received_items }}/{{ $purchase->total_items }}</small>
                                    @else
                                        <span class="text-muted fst-italic">Annulé</span>
                                    @endif
                                </td>
                                <td class="border-0">
                                    <div class="btn-group">
                                        <!-- Bouton Voir -->
                                        <a href="{{ route('purchases.show', $purchase->id) }}" class="btn btn-sm" style="background: linear-gradient(180deg, #336699 0%, #4a90e2 100%); color: white; border: none;" title="Voir les détails">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        <!-- Bouton Imprimer -->
                                        <a href="{{ route('purchases.print', $purchase->id) }}" class="btn btn-sm" style="background: linear-gradient(135deg, #6c757d 0%, #495057 100%); color: white; border: none;" target="_blank" title="Imprimer la commande">
                                            <i class="fas fa-print"></i>
                                        </a>
                                        
                                        @if($purchase->status !== 'received' && $purchase->status !== 'cancelled')
                                            <!-- Bouton Modifier -->
                                            <a href="{{ route('purchases.edit', $purchase->id) }}" class="btn btn-sm" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; border: none;" title="Modifier la commande">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                            <!-- Bouton Réception -->
                                            <a href="{{ route('purchases.receive', $purchase->id) }}" class="btn btn-sm" style="background: linear-gradient(135deg, #17a2b8 0%, #6f42c1 100%); color: white; border: none;" title="Réceptionner">
                                                <i class="fas fa-truck"></i>
                                            </a>
                                        @endif

                                        @if($purchase->status === 'pending' || $purchase->status === 'cancelled')
                                            <!-- Bouton Supprimer -->
                                            <button type="button" class="btn btn-sm" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; border: none;" title="Supprimer la commande" onclick="confirmDelete({{ $purchase->id }}, '{{ $purchase->purchase_number }}')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 border-0">
                                    <div class="mb-3" style="width: 60px; height: 60px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                                        <i class="fas fa-inbox fa-2x text-muted"></i>
                                    </div>
                                    <h5 class="text-muted mb-2">Aucune commande trouvée</h5>
                                    <p class="text-muted">Essayez de modifier vos critères de recherche</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($purchases->hasPages())
            <div class="card-footer border-0" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 0 0 15px 15px;">
                {{ $purchases->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal de confirmation de suppression -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 15px; border: none; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);">
            <div class="modal-header border-0" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; border-radius: 15px 15px 0 0;">
                <h5 class="modal-title fw-bold" id="deleteConfirmModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Confirmer la suppression
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="mb-3" style="width: 60px; height: 60px; background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                    <i class="fas fa-trash fa-2x text-white"></i>
                </div>
                <h5 class="mb-3">Êtes-vous sûr de vouloir supprimer cette commande ?</h5>
                <p class="text-muted mb-0">
                    Commande: <strong id="purchaseNumber"></strong><br>
                    <small class="text-danger">Cette action est irréversible.</small>
                </p>
            </div>
            <div class="modal-footer border-0 justify-content-center">
                <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal" style="border-radius: 10px; padding: 10px 20px;">
                    <i class="fas fa-times me-1"></i> Annuler
                </button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn text-white" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); border: none; border-radius: 10px; padding: 10px 20px;">
                        <i class="fas fa-trash me-1"></i> Supprimer
                    </button>
                </form>
            </div>
        </div>
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
    
    /* Harmonisation avec le sidebar */
    .text-primary {
        color: #336699 !important;
    }
    
    .bg-primary {
        background: linear-gradient(180deg, #336699 0%, #4a90e2 100%) !important;
    }
    
    .progress {
        background-color: rgba(51, 102, 153, 0.1);
    }

    .modal-content {
        backdrop-filter: blur(10px);
    }

    .btn-group .btn {
        margin-right: 2px;
    }

    .btn-group .btn:last-child {
        margin-right: 0;
    }
</style>
@endsection

@section('scripts')
<script>
function confirmDelete(purchaseId, purchaseNumber) {
    document.getElementById('purchaseNumber').textContent = purchaseNumber;
    document.getElementById('deleteForm').action = '/purchases/' + purchaseId;
    
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    deleteModal.show();
}

// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
});
</script>
@endsection