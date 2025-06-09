@extends('layouts.app')

@section('styles')
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
    body {
        font-family: 'Rubik', sans-serif;
    }
    
    h1, h2, h3, h4, h5, h6 {
        font-family: 'Poppins', sans-serif;
    }
    
    /* Container principal qui respecte le sidebar */
    .main-container {
        background: linear-gradient(135deg, #e3f2fd 0%, #e8f5e8 100%);
        min-height: 100vh;
        padding: 20px;
        /* Suppression des marges négatives qui causent le problème */
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
    
    .btn-secondary-custom {
        background: linear-gradient(135deg, #6c757d 0%, #495057 100%) !important;
        border: none !important;
        border-radius: 12px !important;
        box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3) !important;
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
    
    .btn-warning-custom {
        background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%) !important;
        border: none !important;
        border-radius: 12px !important;
        box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3) !important;
        color: #212529 !important;
        font-weight: 600 !important;
    }
    
    .btn-danger-custom {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%) !important;
        border: none !important;
        border-radius: 12px !important;
        box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3) !important;
        color: white !important;
        font-weight: 600 !important;
    }
    
    .btn-outline-custom {
        background: rgba(51, 102, 153, 0.1) !important;
        border: 2px solid #336699 !important;
        border-radius: 12px !important;
        color: #336699 !important;
        font-weight: 600 !important;
    }
    
    .btn-outline-custom:hover {
        background: linear-gradient(180deg, #336699 0%, #4a90e2 100%) !important;
        color: white !important;
        border-color: #336699 !important;
    }
    
    .alert-custom-success {
        border: none !important;
        border-radius: 15px !important;
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%) !important;
        color: white !important;
        box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3) !important;
    }
    
    .alert-custom-danger {
        border: none !important;
        border-radius: 15px !important;
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%) !important;
        color: white !important;
        box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3) !important;
    }
    
    .alert-custom-info {
        border: none !important;
        border-radius: 15px !important;
        background: linear-gradient(135deg, #17a2b8 0%, #6f42c1 100%) !important;
        color: white !important;
        box-shadow: 0 4px 15px rgba(23, 162, 184, 0.3) !important;
    }
    
    .alert-custom-warning {
        border: none !important;
        border-radius: 15px !important;
        background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%) !important;
        color: #212529 !important;
        box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3) !important;
    }
    
    .card-header-details {
        background: linear-gradient(180deg, #336699 0%, #4a90e2 100%) !important;
        color: white !important;
        border-radius: 15px 15px 0 0 !important;
        border: none !important;
        padding: 16px !important;
    }
    
    .card-header-products {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%) !important;
        color: white !important;
        border-radius: 15px 15px 0 0 !important;
        border: none !important;
        padding: 16px !important;
    }
    
    .card-header-financial {
        background: linear-gradient(135deg, #17a2b8 0%, #6f42c1 100%) !important;
        color: white !important;
        border-radius: 15px 15px 0 0 !important;
        border: none !important;
        padding: 16px !important;
    }
    
    .card-header-actions {
        background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%) !important;
        color: #212529 !important;
        border-radius: 15px 15px 0 0 !important;
        border: none !important;
        padding: 16px !important;
    }
    
    .card-header-client {
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
    
    .list-group-item-primary {
        background: linear-gradient(180deg, #336699 0%, #4a90e2 100%) !important;
        color: white !important;
        border: none !important;
        border-radius: 8px !important;
        font-weight: 600 !important;
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
    
    .prescription-yes {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%) !important;
        color: white !important;
    }
    
    .prescription-no {
        background: linear-gradient(135deg, #6c757d 0%, #495057 100%) !important;
        color: white !important;
    }
    
    .modal-header-danger {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%) !important;
        color: white !important;
        border-radius: 15px 15px 0 0 !important;
        border: none !important;
    }
    
    /* Responsive pour maintenir la largeur du sidebar */
    @media (max-width: 768px) {
        .main-container {
            padding: 10px;
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
    
    /* Animation shake pour les erreurs */
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
        20%, 40%, 60%, 80% { transform: translateX(5px); }
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
                    <i class="fas fa-receipt text-white fa-lg"></i>
                </div>
                <div>
                    <h2 class="mb-0 fw-bold" style="color: #2c3e50;">Vente {{ $sale->sale_number }}</h2>
                    <small class="text-muted">Détails de la transaction</small>
                </div>
            </div>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('sales.index') }}" class="btn btn-secondary-custom me-2">
                <i class="fas fa-arrow-left me-1"></i> Retour aux ventes
            </a>
            <a href="{{ route('sales.print', $sale->id) }}" class="btn btn-primary-custom" target="_blank">
                <i class="fas fa-print me-1"></i> Imprimer
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-custom-success alert-dismissible fade show mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle me-3 fa-lg"></i>
                <div>{{ session('success') }}</div>
            </div>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-custom-danger alert-dismissible fade show mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-triangle me-3 fa-lg"></i>
                <div>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <!-- Détails de la vente -->
            <div class="card shadow-lg mb-4">
                <div class="card-header card-header-details d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-bold">
                        <i class="fas fa-info-circle me-2"></i>
                        Détails de la vente
                    </h5>
                    <span class="status-badge 
                        @if($sale->payment_status == 'paid') status-paid
                        @elseif($sale->payment_status == 'pending') status-pending  
                        @else status-failed @endif">
                        {{ ucfirst($sale->payment_status) }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="list-group-item-custom d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-hashtag me-2" style="color: #336699;"></i>
                                            <strong>Numéro de vente:</strong>
                                        </div>
                                        <span class="fw-bold">{{ $sale->sale_number }}</span>
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
                                        <span class="fw-medium">{{ $sale->user->name }}</span>
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
                                                <a href="{{ route('clients.show', $sale->client->id) }}" class="text-decoration-none fw-medium" style="color: #336699;">
                                                    {{ $sale->client->full_name }}
                                                </a>
                                            @else
                                                <em class="text-muted">Client anonyme</em>
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="list-group-item-custom d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-credit-card me-2" style="color: #336699;"></i>
                                            <strong>Mode de paiement:</strong>
                                        </div>
                                        <span class="fw-medium">{{ ucfirst($sale->payment_method) }}</span>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="list-group-item-custom d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-money-check-alt me-2" style="color: #336699;"></i>
                                            <strong>Statut paiement:</strong>
                                        </div>
                                        <span class="status-badge 
                                            @if($sale->payment_status == 'paid') status-paid
                                            @elseif($sale->payment_status == 'pending') status-pending  
                                            @else status-failed @endif">
                                            {{ ucfirst($sale->payment_status) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="list-group-item-custom d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-prescription-bottle me-2" style="color: #336699;"></i>
                                            <strong>Ordonnance:</strong>
                                        </div>
                                        <div>
                                            @if($sale->has_prescription)
                                                <span class="status-badge prescription-yes">Oui</span>
                                                @if($sale->prescription_number)
                                                    <br><small class="text-muted mt-1">{{ $sale->prescription_number }}</small>
                                                @endif
                                            @else
                                                <span class="status-badge prescription-no">Non</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($sale->notes)
                        <div class="alert alert-custom-info">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-sticky-note me-3 fa-lg"></i>
                                <div>
                                    <strong>Notes:</strong><br>
                                    {{ $sale->notes }}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Produits vendus -->
            <div class="card shadow-lg">
                <div class="card-header card-header-products">
                    <h5 class="card-title mb-0 fw-bold">
                        <i class="fas fa-pills me-2"></i>
                        Produits vendus
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="border-radius: 0 0 15px 15px; overflow: hidden;">
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
                                                    @if($item->product->prescription_required)
                                                        <br><small class="text-warning">
                                                            <i class="fas fa-prescription-bottle me-1"></i>
                                                            Ordonnance requise
                                                        </small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center" style="padding: 16px; border: none;">
                                            <span class="status-badge" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); color: #336699;">
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
                                <tr style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                                    <th colspan="3" class="text-end" style="padding: 16px; border: none; color: #336699;">Sous-total:</th>
                                    <th class="text-end" style="padding: 16px; border: none; color: #336699;">{{ number_format($sale->subtotal, 2) }} €</th>
                                </tr>
                                <tr style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                                    <th colspan="3" class="text-end" style="padding: 16px; border: none; color: #336699;">TVA (20%):</th>
                                    <th class="text-end" style="padding: 16px; border: none; color: #336699;">{{ number_format($sale->tax_amount, 2) }} €</th>
                                </tr>
                                @if($sale->discount_amount > 0)
                                    <tr style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                                        <th colspan="3" class="text-end" style="padding: 16px; border: none; color: #336699;">Remise:</th>
                                        <th class="text-end text-danger" style="padding: 16px; border: none;">-{{ number_format($sale->discount_amount, 2) }} €</th>
                                    </tr>
                                @endif
                                <tr class="table-footer">
                                    <th colspan="3" class="text-end" style="font-size: 1.1rem;">Total:</th>
                                    <th class="text-end" style="font-size: 1.3rem;">{{ number_format($sale->total_amount, 2) }} €</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <!-- Résumé financier -->
            <div class="card shadow-lg mb-4">
                <div class="card-header card-header-financial">
                    <h5 class="card-title mb-0 fw-bold">
                        <i class="fas fa-calculator me-2"></i>
                        Résumé financier
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="list-group-item-custom d-flex justify-content-between align-items-center">
                                <span class="fw-semibold">Sous-total:</span>
                                <span class="fw-bold">{{ number_format($sale->subtotal, 2) }} €</span>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="list-group-item-custom d-flex justify-content-between align-items-center">
                                <span class="fw-semibold">TVA:</span>
                                <span class="fw-bold">{{ number_format($sale->tax_amount, 2) }} €</span>
                            </div>
                        </div>
                        @if($sale->discount_amount > 0)
                            <div class="col-12">
                                <div class="list-group-item-custom d-flex justify-content-between align-items-center">
                                    <span class="fw-semibold">Remise:</span>
                                    <span class="text-danger fw-bold">-{{ number_format($sale->discount_amount, 2) }} €</span>
                                </div>
                            </div>
                        @endif
                        <div class="col-12">
                            <div class="list-group-item-primary d-flex justify-content-between align-items-center">
                                <strong style="font-size: 1.1rem;">Total:</strong>
                                <strong style="font-size: 1.3rem;">{{ number_format($sale->total_amount, 2) }} €</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="card shadow-lg mb-4">
                <div class="card-header card-header-actions">
                    <h5 class="card-title mb-0 fw-bold">
                        <i class="fas fa-cogs me-2"></i>
                        Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('sales.print', $sale->id) }}" class="btn btn-primary-custom" target="_blank">
                            <i class="fas fa-print me-1"></i> Imprimer le reçu
                        </a>
                        
                        @if($sale->payment_status !== 'paid')
                            <a href="{{ route('sales.edit', $sale->id) }}" class="btn btn-warning-custom">
                                <i class="fas fa-edit me-1"></i> Modifier le statut
                            </a>
                        @endif
                        
                        @if($sale->client)
                            <a href="{{ route('clients.show', $sale->client->id) }}" class="btn btn-outline-custom">
                                <i class="fas fa-user me-1"></i> Voir le client
                            </a>
                        @endif
                        
                        <a href="{{ route('sales.create') }}" class="btn btn-success-custom">
                            <i class="fas fa-plus me-1"></i> Nouvelle vente
                        </a>
                        
                        @if($sale->sale_date >= now()->subDays(7) && Auth::user()->isAdmin())
                            <button type="button" class="btn btn-danger-custom" data-bs-toggle="modal" data-bs-target="#deleteSaleModal">
                                <i class="fas fa-trash me-1"></i> Supprimer la vente
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Informations client -->
            @if($sale->client)
                <div class="card shadow-lg">
                    <div class="card-header card-header-client">
                        <h5 class="card-title mb-0 fw-bold">
                            <i class="fas fa-user-circle me-2"></i>
                            Informations client
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="list-group-item-custom text-center">
                                    <div class="mb-2" style="width: 40px; height: 40px; background: linear-gradient(180deg, #336699 0%, #4a90e2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                                        <i class="fas fa-user text-white"></i>
                                    </div>
                                    <strong class="fw-bold" style="font-size: 1.1rem;">{{ $sale->client->full_name }}</strong>
                                </div>
                            </div>
                            @if($sale->client->phone)
                                <div class="col-12">
                                    <div class="list-group-item-custom d-flex align-items-center">
                                        <i class="fas fa-phone me-3" style="color: #336699; width: 20px;"></i>
                                        <span>{{ $sale->client->phone }}</span>
                                    </div>
                                </div>
                            @endif
                            @if($sale->client->email)
                                <div class="col-12">
                                    <div class="list-group-item-custom d-flex align-items-center">
                                        <i class="fas fa-envelope me-3" style="color: #336699; width: 20px;"></i>
                                        <span>{{ $sale->client->email }}</span>
                                    </div>
                                </div>
                            @endif
                            <div class="col-12">
                                <div class="list-group-item-custom d-flex justify-content-between align-items-center">
                                    <span class="fw-semibold">Total dépensé:</span>
                                    <strong class="text-success" style="font-size: 1.1rem;">{{ number_format($sale->client->total_spent, 2) }} €</strong>
                                </div>
                            </div>
                        </div>
                        
                        @if($sale->client->allergies)
                            <div class="alert alert-custom-warning mt-3 mb-0">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-exclamation-triangle me-3 fa-lg"></i>
                                    <div>
                                        <strong>Allergies connues:</strong><br>
                                        {{ $sale->client->allergies }}
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if($sale->sale_date >= now()->subDays(7) && Auth::user()->isAdmin())
        <!-- Modal de confirmation de suppression -->
        <div class="modal fade" id="deleteSaleModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content" style="border-radius: 15px; border: none;">
                    <div class="modal-header modal-header-danger">
                        <h5 class="modal-title fw-bold">
                            <i class="fas fa-exclamation-triangle me-2"></i>Supprimer la vente
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-custom-danger">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-exclamation-triangle me-3 fa-lg"></i>
                                <div>
                                    <strong>ATTENTION!</strong> Cette action est définitive et irréversible.
                                </div>
                            </div>
                        </div>
                        
                        <p>Êtes-vous absolument sûr de vouloir supprimer la vente <strong>{{ $sale->sale_number }}</strong> ?</p>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header" style="background: linear-gradient(135deg, #17a2b8 0%, #6f42c1 100%); color: white;">
                                        <strong>Informations de la vente</strong>
                                    </div>
                                    <div class="card-body">
                                        <ul class="mb-0">
                                            <li><strong>Date :</strong> {{ $sale->sale_date->format('d/m/Y H:i') }}</li>
                                            <li><strong>Montant :</strong> {{ number_format($sale->total_amount, 2) }} €</li>
                                            <li><strong>Client :</strong> {{ $sale->client ? $sale->client->full_name : 'Anonyme' }}</li>
                                            <li><strong>Vendeur :</strong> {{ $sale->user->name }}</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white;">
                                        <strong>Conséquences</strong>
                                    </div>
                                    <div class="card-body">
                                        <ul class="mb-0 text-danger">
                                            <li>La vente sera définitivement supprimée</li>
                                            <li>Le stock des produits sera restauré</li>
                                            <li>Les données ne pourront pas être récupérées</li>
                                            <li>Cette action sera tracée dans les logs</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-header" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white;">
                                <strong>Produits qui seront remis en stock</strong>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0">
                                        <thead style="background: #f8f9fa;">
                                            <tr>
                                                <th style="color: #336699;">Produit</th>
                                                <th class="text-center" style="color: #336699;">Quantité à remettre</th>
                                                <th class="text-center" style="color: #336699;">Stock actuel</th>
                                                <th class="text-center" style="color: #336699;">Nouveau stock</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($sale->saleItems as $item)
                                                <tr>
                                                    <td>{{ $item->product->name }}</td>
                                                    <td class="text-center">
                                                        <span class="badge bg-warning text-dark">{{ $item->quantity }}</span>
                                                    </td>
                                                    <td class="text-center">{{ $item->product->stock_quantity }}</td>
                                                    <td class="text-center">
                                                        <strong class="text-success" style="font-size: 1.1rem;">
                                                            {{ $item->product->stock_quantity + $item->quantity }}
                                                        </strong>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-check mt-3" style="padding: 15px; background: rgba(220, 53, 69, 0.1); border-radius: 10px; border: 2px solid rgba(220, 53, 69, 0.2);">
                            <input class="form-check-input" type="checkbox" id="confirmDelete" required style="transform: scale(1.2);">
                            <label class="form-check-label text-danger fw-bold ms-2" for="confirmDelete">
                                Je comprends que cette action est irréversible et je souhaite procéder à la suppression
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary-custom" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Annuler
                        </button>
                        <form action="{{ route('sales.destroy', $sale->id) }}" method="POST" style="display: inline;" id="deleteSaleForm">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger-custom" id="confirmDeleteBtn" disabled>
                                <i class="fas fa-trash me-1"></i>Supprimer définitivement
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@if($sale->sale_date >= now()->subDays(7) && Auth::user()->isAdmin())
@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const confirmCheckbox = document.getElementById('confirmDelete');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    const deleteSaleForm = document.getElementById('deleteSaleForm');
    
    if (confirmCheckbox && confirmDeleteBtn) {
        confirmCheckbox.addEventListener('change', function() {
            confirmDeleteBtn.disabled = !this.checked;
            
            if (this.checked) {
                confirmDeleteBtn.style.background = 'linear-gradient(135deg, #dc3545 0%, #c82333 100%)';
                confirmDeleteBtn.style.transform = 'scale(1)';
            } else {
                confirmDeleteBtn.style.background = 'linear-gradient(135deg, #6c757d 0%, #495057 100%)';
                confirmDeleteBtn.style.transform = 'scale(0.98)';
            }
        });
        
        deleteSaleForm.addEventListener('submit', function(e) {
            if (!confirmCheckbox.checked) {
                e.preventDefault();
                
                // Animation d'erreur
                confirmCheckbox.style.animation = 'shake 0.5s ease-in-out';
                setTimeout(() => {
                    confirmCheckbox.style.animation = '';
                }, 500);
                
                alert('Vous devez cocher la case de confirmation pour procéder à la suppression.');
                return false;
            }
            
            const finalConfirm = confirm('Dernière confirmation : êtes-vous vraiment sûr de vouloir supprimer cette vente ? Cette action est irréversible.');
            if (!finalConfirm) {
                e.preventDefault();
                return false;
            }
            
            // Animation de loading
            confirmDeleteBtn.disabled = true;
            confirmDeleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Suppression en cours...';
            confirmDeleteBtn.style.background = 'linear-gradient(135deg, #6c757d 0%, #495057 100%)';
        });
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
    
    // Animation shake pour les erreurs
    const style = document.createElement('style');
    style.textContent = `
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
    `;
    document.head.appendChild(style);
});
</script>
@endsection
@endif