@extends('layouts.app')

@section('styles')
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
    body {
        font-family: 'Rubik', sans-serif;
        background: linear-gradient(135deg, #e3f2fd 0%, #e8f5e8 100%) !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    
    h1, h2, h3, h4, h5, h6 {
        font-family: 'Poppins', sans-serif;
    }
    
    .main-container {
        background: linear-gradient(135deg, #e3f2fd 0%, #e8f5e8 100%);
        min-height: 100vh;
        padding: 20px;
        margin: -20px;
    }
    
    .btn {
        transition: all 0.3s ease;
    }
    
    .btn:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    }
    
    .card {
        transition: transform 0.3s ease;
        border: none !important;
        border-radius: 15px !important;
        background: rgba(255, 255, 255, 0.95) !important;
        backdrop-filter: blur(10px) !important;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1) !important;
    }
    
    .card:hover {
        transform: translateY(-2px);
    }
    
    .form-control:focus,
    .form-select:focus {
        border-color: #336699 !important;
        box-shadow: 0 0 0 0.25rem rgba(51, 102, 153, 0.25) !important;
        transform: scale(1.02);
        transition: all 0.3s ease;
    }
    
    .table-hover tbody tr:hover {
        background-color: rgba(51, 102, 153, 0.05) !important;
        transform: scale(1.005);
        transition: all 0.2s ease;
    }
    
    .header-icon {
        width: 50px;
        height: 50px;
        background: linear-gradient(180deg, #336699 0%, #4a90e2 100%);
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 8px 25px rgba(51, 102, 153, 0.3);
    }
    
    .btn-primary-custom {
        background: linear-gradient(180deg, #336699 0%, #4a90e2 100%) !important;
        border: none !important;
        border-radius: 12px !important;
        padding: 12px 24px !important;
        box-shadow: 0 4px 15px rgba(51, 102, 153, 0.3) !important;
        color: white !important;
        font-weight: 600 !important;
    }
    
    .btn-success-custom {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%) !important;
        border: none !important;
        border-radius: 12px !important;
        box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3) !important;
        color: white !important;
        font-weight: 600 !important;
    }
    
    .btn-secondary-custom {
        background: linear-gradient(135deg, #6c757d 0%, #495057 100%) !important;
        border: none !important;
        border-radius: 12px !important;
        box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3) !important;
        color: white !important;
        font-weight: 600 !important;
    }
    
    .alert-custom {
        border: none !important;
        border-radius: 15px !important;
        background: linear-gradient(45deg, #17a2b8 0%, #6f42c1 100%) !important;
        color: white !important;
        box-shadow: 0 4px 15px rgba(23, 162, 184, 0.3) !important;
    }
    
    .card-header-products {
        background: linear-gradient(180deg, #336699 0%, #4a90e2 100%) !important;
        color: white !important;
        border-radius: 15px 15px 0 0 !important;
        border: none !important;
        padding: 16px !important;
    }
    
    .card-header-info {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%) !important;
        color: white !important;
        border-radius: 15px 15px 0 0 !important;
        border: none !important;
        padding: 16px !important;
    }
    
    .card-header-status {
        background: linear-gradient(135deg, #17a2b8 0%, #6f42c1 100%) !important;
        color: white !important;
        border-radius: 15px 15px 0 0 !important;
        border: none !important;
        padding: 16px !important;
    }
    
    .card-header-actions {
        background: linear-gradient(135deg, #e83e8c 0%, #fd7e14 100%) !important;
        color: white !important;
        border-radius: 15px 15px 0 0 !important;
        border: none !important;
        padding: 16px !important;
    }
    
    .table-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
        color: #336699 !important;
        font-weight: 600 !important;
        padding: 16px !important;
        border: none !important;
    }
    
    .table-footer {
        background: linear-gradient(180deg, #336699 0%, #4a90e2 100%) !important;
        color: white !important;
        font-weight: 700 !important;
        padding: 16px !important;
        border: none !important;
    }
    
    .list-group-item-custom {
        background: rgba(51, 102, 153, 0.05) !important;
        border: 1px solid rgba(51, 102, 153, 0.1) !important;
        border-radius: 8px !important;
        margin-bottom: 8px !important;
        transition: all 0.3s ease !important;
    }
    
    .list-group-item-custom:hover {
        background: rgba(51, 102, 153, 0.1) !important;
        transform: translateX(5px) !important;
    }
    
    .product-icon {
        width: 32px;
        height: 32px;
        background: linear-gradient(180deg, #336699 0%, #4a90e2 100%);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .status-badge {
        padding: 8px 16px !important;
        border-radius: 25px !important;
        font-weight: 600 !important;
        font-size: 0.85rem !important;
    }
    
    .status-paid {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%) !important;
        color: white !important;
    }
    
    .status-pending {
        background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%) !important;
        color: #212529 !important;
    }
    
    .status-failed {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%) !important;
        color: white !important;
    }
    
    @media (max-width: 768px) {
        .main-container {
            padding: 10px;
            margin: -10px;
        }
        
        .col-md-8, .col-md-4 {
            margin-bottom: 20px;
        }
        
        .table-responsive {
            font-size: 14px;
        }
        
        .btn {
            padding: 10px 16px;
            font-size: 14px;
        }
    }
</style>
@endsection

@section('content')
<div class="main-container">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="d-flex align-items-center">
                <div class="header-icon me-3">
                    <i class="fas fa-edit text-white fa-lg"></i>
                </div>
                <div>
                    <h2 class="mb-0 fw-bold" style="color: #2c3e50;">Modifier la vente {{ $sale->sale_number }}</h2>
                    <small class="text-muted">Modification des informations de la vente</small>
                </div>
            </div>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('sales.show', $sale->id) }}" class="btn btn-primary-custom">
                <i class="fas fa-arrow-left me-1"></i> Retour à la vente
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 mb-4" role="alert" style="border-radius: 15px; background: linear-gradient(45deg, #28a745 0%, #20c997 100%); color: white; box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);">
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle me-3 fa-lg"></i>
                <div>
                    <strong>Succès :</strong> {{ session('success') }}
                </div>
            </div>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 mb-4" role="alert" style="border-radius: 15px; background: linear-gradient(45deg, #dc3545 0%, #c82333 100%); color: white; box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-triangle me-3 fa-lg"></i>
                <div>
                    <strong>Erreurs détectées :</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-lg">
                <div class="card-header card-header-products">
                    <h5 class="card-title mb-0 fw-bold">
                        <i class="fas fa-pills me-2"></i>
                        Produits vendus
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive" style="border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th class="table-header">Produit</th>
                                    <th class="table-header text-center">Quantité</th>
                                    <th class="table-header text-end">Prix unitaire</th>
                                    <th class="table-header text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sale->saleItems as $item)
                                    <tr>
                                        <td style="padding: 16px; border: none;">
                                            <div class="d-flex align-items-center">
                                                <div class="product-icon me-3">
                                                    <i class="fas fa-pills text-white small"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-semibold">{{ $item->product->name }}</div>
                                                    @if($item->product->dosage)
                                                        <small class="text-muted">{{ $item->product->dosage }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center" style="padding: 16px; border: none;">
                                            <span class="badge" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); color: #336699; font-size: 1rem; padding: 8px 16px;">
                                                {{ $item->quantity }}
                                            </span>
                                        </td>
                                        <td class="text-end fw-semibold" style="padding: 16px; border: none; color: #336699;">
                                            {{ number_format($item->unit_price, 2) }} €
                                        </td>
                                        <td class="text-end" style="padding: 16px; border: none;">
                                            <span class="fw-bold text-success" style="font-size: 1.1rem;">
                                                {{ number_format($item->total_price, 2) }} €
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-end table-footer">Total:</th>
                                    <th class="text-end table-footer" style="font-size: 1.3rem;">
                                        {{ number_format($sale->total_amount, 2) }} €
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    <div class="alert alert-custom mt-4">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-info-circle me-3 fa-lg"></i>
                            <div>
                                <strong>Information importante :</strong><br>
                                Les produits vendus ne peuvent pas être modifiés après la création de la vente. 
                                Seuls le statut de paiement et les notes peuvent être mis à jour.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <form action="{{ route('sales.update', $sale->id) }}" method="POST" id="updateForm">
                @csrf
                @method('PUT')
                
                <!-- Informations de la vente -->
                <div class="card shadow-lg mb-4">
                    <div class="card-header card-header-info">
                        <h5 class="card-title mb-0 fw-bold">
                            <i class="fas fa-info-circle me-2"></i>
                            Informations de la vente
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="list-group-item-custom d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-hashtag me-2" style="color: #336699;"></i>
                                        <strong>Numéro:</strong>
                                    </div>
                                    <span class="badge status-badge" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); color: #336699;">
                                        {{ $sale->sale_number }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="list-group-item-custom d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-calendar me-2" style="color: #336699;"></i>
                                        <strong>Date:</strong>
                                    </div>
                                    <span>{{ $sale->sale_date->format('d/m/Y H:i') }}</span>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="list-group-item-custom d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-user-tie me-2" style="color: #336699;"></i>
                                        <strong>Vendeur:</strong>
                                    </div>
                                    <span>{{ $sale->user->name }}</span>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="list-group-item-custom d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-user me-2" style="color: #336699;"></i>
                                        <strong>Client:</strong>
                                    </div>
                                    <span>
                                        @if($sale->client)
                                            {{ $sale->client->full_name }}
                                        @else
                                            <em class="text-muted">Client anonyme</em>
                                        @endif
                                    </span>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="list-group-item-custom d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-credit-card me-2" style="color: #336699;"></i>
                                        <strong>Paiement:</strong>
                                    </div>
                                    <span class="badge status-badge" style="background: linear-gradient(135deg, #17a2b8 0%, #6f42c1 100%); color: white;">
                                        {{ ucfirst($sale->payment_method) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modifier le statut -->
                <div class="card shadow-lg mb-4">
                    <div class="card-header card-header-status">
                        <h5 class="card-title mb-0 fw-bold">
                            <i class="fas fa-edit me-2"></i>
                            Modifier le statut
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="payment_status" class="form-label fw-semibold">
                                <i class="fas fa-money-check-alt me-1" style="color: #336699;"></i>
                                Statut du paiement <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('payment_status') is-invalid @enderror" 
                                    id="payment_status" name="payment_status" required
                                    style="border-radius: 12px; border: 2px solid #e9ecef; padding: 12px 16px;">
                                <option value="paid" {{ old('payment_status', $sale->payment_status) == 'paid' ? 'selected' : '' }}>
                                    ✅ Payé
                                </option>
                                <option value="pending" {{ old('payment_status', $sale->payment_status) == 'pending' ? 'selected' : '' }}>
                                    ⏳ En attente
                                </option>
                                <option value="failed" {{ old('payment_status', $sale->payment_status) == 'failed' ? 'selected' : '' }}>
                                    ❌ Échoué
                                </option>
                            </select>
                            @error('payment_status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            
                            <!-- Affichage du statut actuel -->
                            <div class="mt-2">
                                <small class="text-muted">Statut actuel: </small>
                                <span class="badge status-badge 
                                    @if($sale->payment_status == 'paid') status-paid
                                    @elseif($sale->payment_status == 'pending') status-pending  
                                    @else status-failed @endif">
                                    {{ ucfirst($sale->payment_status) }}
                                </span>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label fw-semibold">
                                <i class="fas fa-sticky-note me-1" style="color: #336699;"></i>
                                Notes
                            </label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" name="notes" rows="4" 
                                      placeholder="Ajoutez des notes sur cette vente..."
                                      style="border-radius: 12px; border: 2px solid #e9ecef; padding: 12px 16px; resize: vertical;">{{ old('notes', $sale->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="card shadow-lg">
                    <div class="card-header card-header-actions">
                        <h5 class="card-title mb-0 fw-bold">
                            <i class="fas fa-cogs me-2"></i>
                            Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success-custom py-3" id="updateBtn">
                                <i class="fas fa-save me-2"></i> Mettre à jour la vente
                            </button>
                            <a href="{{ route('sales.show', $sale->id) }}" class="btn btn-secondary-custom py-2">
                                <i class="fas fa-times me-2"></i> Annuler les modifications
                            </a>
                            <hr class="my-3">
                            <a href="{{ route('sales.print', $sale->id) }}" class="btn btn-primary-custom py-2" target="_blank">
                                <i class="fas fa-print me-2"></i> Imprimer la facture
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const updateForm = document.getElementById('updateForm');
    const updateBtn = document.getElementById('updateBtn');
    const paymentStatusSelect = document.getElementById('payment_status');
    
    // Animation pour le changement de statut
    paymentStatusSelect.addEventListener('change', function() {
        const statusBadge = document.querySelector('.badge.status-badge:last-of-type');
        statusBadge.style.transform = 'scale(1.1)';
        statusBadge.style.transition = 'transform 0.2s ease';
        
        setTimeout(() => {
            statusBadge.style.transform = 'scale(1)';
        }, 200);
    });
    
    // Gestion de la soumission du formulaire
    updateForm.addEventListener('submit', function(e) {
        // Animation de loading
        updateBtn.disabled = true;
        updateBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Mise à jour en cours...';
        updateBtn.style.background = 'linear-gradient(135deg, #6c757d 0%, #495057 100%)';
        
        // Confirmer la modification si le statut change
        const originalStatus = '{{ $sale->payment_status }}';
        const newStatus = paymentStatusSelect.value;
        
        if (originalStatus !== newStatus) {
            const confirmMessage = `Êtes-vous sûr de vouloir changer le statut de "${originalStatus}" vers "${newStatus}" ?`;
            if (!confirm(confirmMessage)) {
                e.preventDefault();
                // Restaurer le bouton
                updateBtn.disabled = false;
                updateBtn.innerHTML = '<i class="fas fa-save me-2"></i> Mettre à jour la vente';
                updateBtn.style.background = 'linear-gradient(135deg, #28a745 0%, #20c997 100%)';
                return false;
            }
        }
    });
    
    // Validation en temps réel
    const requiredFields = ['payment_status'];
    requiredFields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        field.addEventListener('change', validateForm);
    });
    
    function validateForm() {
        const isValid = requiredFields.every(fieldId => {
            const field = document.getElementById(fieldId);
            return field.value.trim() !== '';
        });
        
        updateBtn.disabled = !isValid;
        if (!isValid) {
            updateBtn.style.background = 'linear-gradient(135deg, #6c757d 0%, #495057 100%)';
        } else {
            updateBtn.style.background = 'linear-gradient(135deg, #28a745 0%, #20c997 100%)';
        }
    }
    
    // Animation au survol des éléments de liste
    const listItems = document.querySelectorAll('.list-group-item-custom');
    listItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.style.background = 'rgba(51, 102, 153, 0.1)';
            this.style.transform = 'translateX(5px)';
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.background = 'rgba(51, 102, 153, 0.05)';
            this.style.transform = 'translateX(0)';
        });
    });
});
</script>
@endsection