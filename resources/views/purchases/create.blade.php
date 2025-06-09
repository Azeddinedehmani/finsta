@extends('layouts.app')

@section('styles')
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #336699 0%, #4a90e2 100%);
        --secondary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --success-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        --warning-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        --card-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        --hover-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        --border-radius: 15px;
        --glass-bg: rgba(255, 255, 255, 0.95);
        --glass-border: rgba(255, 255, 255, 0.2);
    }

    body {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        font-family: 'Poppins', sans-serif;
    }

    .page-header {
        background: var(--glass-bg);
        backdrop-filter: blur(20px);
        border: 1px solid var(--glass-border);
        border-radius: var(--border-radius);
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: var(--card-shadow);
        position: relative;
        overflow: hidden;
    }

    .page-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--primary-gradient);
    }

    .page-title {
        font-weight: 600;
        color: #2c3e50;
        margin: 0;
        font-size: 2rem;
    }

    .breadcrumb-nav {
        color: #6c757d;
        font-size: 0.9rem;
        margin-top: 0.5rem;
    }

    .glass-card {
        background: var(--glass-bg);
        backdrop-filter: blur(20px);
        border: 1px solid var(--glass-border);
        border-radius: var(--border-radius);
        box-shadow: var(--card-shadow);
        transition: all 0.3s ease;
        overflow: hidden;
        border: none;
    }

    .glass-card:hover {
        box-shadow: var(--hover-shadow);
        transform: translateY(-2px);
    }

    .card-header-gradient {
        background: var(--primary-gradient);
        color: white;
        padding: 1.5rem;
        border: none;
        position: relative;
    }

    .card-header-gradient::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    }

    .progress-indicator {
        background: var(--glass-bg);
        backdrop-filter: blur(20px);
        border: 1px solid var(--glass-border);
        border-radius: var(--border-radius);
        padding: 1.5rem;
        margin-bottom: 2rem;
        box-shadow: var(--card-shadow);
    }

    .step {
        display: inline-block;
        width: 35px;
        height: 35px;
        border-radius: 50%;
        background: #e9ecef;
        color: #6c757d;
        text-align: center;
        line-height: 35px;
        font-weight: 600;
        margin-right: 1rem;
        transition: all 0.3s ease;
        position: relative;
    }

    .step.active {
        background: var(--primary-gradient);
        color: white;
        box-shadow: 0 4px 15px rgba(74, 144, 226, 0.3);
        animation: pulse 2s infinite;
    }

    .step.completed {
        background: var(--success-gradient);
        color: white;
    }

    .step.completed::after {
        content: '\f00c';
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }

    .form-label {
        font-weight: 500;
        color: #2c3e50;
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
    }

    .form-control, .form-select {
        border: 2px solid #e9ecef;
        border-radius: 10px;
        padding: 0.75rem 1rem;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(10px);
    }

    .form-control:focus, .form-select:focus {
        border-color: #4a90e2;
        box-shadow: 0 0 0 0.2rem rgba(74, 144, 226, 0.25);
        background: white;
        transform: translateY(-1px);
    }

    .btn-primary {
        background: var(--primary-gradient);
        border: none;
        border-radius: 10px;
        padding: 0.75rem 1.5rem;
        font-weight: 500;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(74, 144, 226, 0.3);
    }

    .btn-primary:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(74, 144, 226, 0.4);
        background: var(--primary-gradient);
    }

    .btn-secondary {
        background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
        border: none;
        border-radius: 10px;
        padding: 0.75rem 1.5rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-danger {
        background: var(--warning-gradient);
        border: none;
        border-radius: 8px;
        padding: 0.5rem 1rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-success {
        background: var(--success-gradient);
        border: none;
        border-radius: 10px;
        padding: 0.75rem 1.5rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .alert {
        border: none;
        border-radius: var(--border-radius);
        padding: 1rem 1.5rem;
        backdrop-filter: blur(10px);
    }

    .alert-danger {
        background: linear-gradient(135deg, rgba(220, 53, 69, 0.1) 0%, rgba(220, 53, 69, 0.05) 100%);
        border-left: 4px solid #dc3545;
    }

    .table {
        background: var(--glass-bg);
        backdrop-filter: blur(10px);
        border-radius: var(--border-radius);
        overflow: hidden;
        box-shadow: var(--card-shadow);
        border: none;
    }

    .table thead th {
        background: var(--primary-gradient);
        color: white;
        border: none;
        font-weight: 500;
        padding: 1rem;
        font-size: 0.9rem;
    }

    .table tbody td {
        border: none;
        padding: 1rem;
        vertical-align: middle;
        border-bottom: 1px solid rgba(0,0,0,0.05);
    }

    .table tbody tr:hover {
        background: rgba(74, 144, 226, 0.05);
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    .table tfoot th {
        background: rgba(74, 144, 226, 0.1);
        border: none;
        padding: 1rem;
        font-weight: 600;
    }

    .supplier-info-card {
        background: linear-gradient(135deg, rgba(74, 144, 226, 0.1) 0%, rgba(74, 144, 226, 0.05) 100%);
        border: 1px solid rgba(74, 144, 226, 0.2);
        border-radius: var(--border-radius);
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        backdrop-filter: blur(10px);
        box-shadow: var(--card-shadow);
    }

    .summary-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 0;
        border-bottom: 1px solid rgba(0,0,0,0.05);
    }

    .summary-item:last-child {
        border-bottom: none;
    }

    .summary-label {
        font-weight: 500;
        color: #6c757d;
        font-size: 0.9rem;
    }

    .summary-value {
        font-weight: 600;
        color: #2c3e50;
    }

    .quantity-badge {
        background: var(--primary-gradient);
        color: white;
        border-radius: 20px;
        padding: 0.25rem 0.75rem;
        font-size: 0.8rem;
        font-weight: 500;
        display: inline-block;
    }

    .stock-indicator {
        display: inline-block;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        margin-right: 0.5rem;
    }

    .stock-indicator.high {
        background: #28a745;
        animation: pulse-green 2s infinite;
    }

    .stock-indicator.medium {
        background: #ffc107;
        animation: pulse-orange 2s infinite;
    }

    .stock-indicator.low {
        background: #dc3545;
        animation: pulse-red 2s infinite;
    }

    .product-search-container {
        position: relative;
        margin-bottom: 2rem;
    }

    .product-search-container::after {
        content: '\f002';
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        position: absolute;
        right: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
        pointer-events: none;
    }

    .animation-fadeIn {
        animation: fadeIn 0.5s ease;
    }

    .pulse-btn {
        animation: pulse 2s infinite;
    }

    .stats-card {
        background: linear-gradient(135deg, rgba(17, 153, 142, 0.1) 0%, rgba(56, 239, 125, 0.05) 100%);
        border: 1px solid rgba(17, 153, 142, 0.2);
        border-radius: var(--border-radius);
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        backdrop-filter: blur(10px);
        box-shadow: var(--card-shadow);
    }

    .priority-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 15px;
        font-size: 0.8rem;
        font-weight: 500;
    }

    .priority-normal {
        background: rgba(40, 167, 69, 0.1);
        color: #28a745;
        border: 1px solid rgba(40, 167, 69, 0.3);
    }

    .priority-high {
        background: rgba(255, 193, 7, 0.1);
        color: #ffc107;
        border: 1px solid rgba(255, 193, 7, 0.3);
    }

    .priority-urgent {
        background: rgba(220, 53, 69, 0.1);
        color: #dc3545;
        border: 1px solid rgba(220, 53, 69, 0.3);
    }

    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 2000;
    }

    .loading-spinner {
        background: white;
        padding: 2rem;
        border-radius: var(--border-radius);
        text-align: center;
        box-shadow: var(--card-shadow);
    }

    .card {
        border: none;
    }

    .card-header {
        background: var(--card-header-gradient, #f8f9fa);
        border-bottom: none;
    }

    .bg-light {
        background: var(--primary-gradient) !important;
        color: white !important;
    }

    .bg-light .card-title {
        color: white !important;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }

    @keyframes pulse-green {
        0%, 100% { box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7); }
        70% { box-shadow: 0 0 0 10px rgba(40, 167, 69, 0); }
    }

    @keyframes pulse-orange {
        0%, 100% { box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.7); }
        70% { box-shadow: 0 0 0 10px rgba(255, 193, 7, 0); }
    }

    @keyframes pulse-red {
        0%, 100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7); }
        70% { box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); }
    }

    /* Enhanced responsive design */
    @media (max-width: 992px) {
        .page-header {
            padding: 1.5rem;
        }
        
        .page-title {
            font-size: 1.5rem;
        }
        
        .progress-indicator {
            padding: 1rem;
        }
        
        .step {
            width: 30px;
            height: 30px;
            line-height: 30px;
            font-size: 0.8rem;
        }
    }

    @media (max-width: 768px) {
        .page-header .d-flex {
            flex-direction: column;
            gap: 1rem;
        }
        
        .progress-indicator .d-flex {
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        
        .step {
            margin-right: 0.5rem;
        }
        
        .glass-card {
            margin-bottom: 1.5rem;
        }
        
        .table-responsive {
            font-size: 0.8rem;
        }
        
        .supplier-info-card,
        .stats-card {
            margin-bottom: 1rem;
        }
    }

    /* Enhanced button states */
    .btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none !important;
    }

    .btn-primary:not(:disabled):active {
        transform: translateY(1px);
        box-shadow: 0 2px 8px rgba(74, 144, 226, 0.3);
    }

    /* Enhanced focus states */
    .btn:focus-visible,
    .form-control:focus-visible,
    .form-select:focus-visible {
        outline: 2px solid #4a90e2;
        outline-offset: 2px;
    }

    /* Custom toast styles */
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
</style>
@endsection

@section('content')
<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-spinner">
        <div class="spinner-border text-primary mb-3" role="status">
            <span class="visually-hidden">Chargement...</span>
        </div>
        <p class="mb-0">Création de la commande en cours...</p>
    </div>
</div>

<!-- Page Header -->
<div class="page-header animation-fadeIn">
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <h1 class="page-title">
                <i class="fas fa-plus-circle me-3" style="color: #4a90e2;"></i>
                Nouvelle commande d'achat
            </h1>
            <div class="breadcrumb-nav">
                <i class="fas fa-home me-1"></i>
                Dashboard / Achats / Nouvelle commande
            </div>
        </div>
        <a href="{{ route('purchases.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Retour aux achats
        </a>
    </div>
</div>

<!-- Progress Indicator -->
<div class="progress-indicator animation-fadeIn">
    <div class="d-flex align-items-center">
        <span class="step completed" id="step1">1</span>
        <span class="text-muted me-3">Informations générales</span>
        <span class="step active" id="step2">2</span>
        <span class="text-muted me-3">Sélection des produits</span>
        <span class="step" id="step3">3</span>
        <span class="text-muted">Validation</span>
    </div>
</div>

<!-- Error Messages -->
@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show animation-fadeIn" role="alert">
        <div class="d-flex align-items-center">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <div>
                <strong>Erreurs détectées:</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<form action="{{ route('purchases.store') }}" method="POST" id="purchaseForm">
    @csrf
    
    <div class="row">
        <div class="col-lg-8">
            <!-- General Information Card -->
            <div class="glass-card mb-4 animation-fadeIn">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Informations générales
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="supplier_id" class="form-label">
                                Fournisseur <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('supplier_id') is-invalid @enderror" id="supplier_id" name="supplier_id" required>
                                <option value="">Sélectionner un fournisseur</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" 
                                            {{ old('supplier_id', $selectedSupplierId ?? '') == $supplier->id ? 'selected' : '' }}
                                            data-contact="{{ $supplier->contact_person }}"
                                            data-phone="{{ $supplier->phone_number }}"
                                            data-email="{{ $supplier->email }}">
                                        {{ $supplier->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('supplier_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label for="order_date" class="form-label">
                                Date de commande <span class="text-danger">*</span>
                            </label>
                            <input type="date" class="form-control @error('order_date') is-invalid @enderror" 
                                   id="order_date" name="order_date" value="{{ old('order_date', date('Y-m-d')) }}" required>
                            @error('order_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="expected_date" class="form-label">Date de livraison prévue</label>
                            <input type="date" class="form-control @error('expected_date') is-invalid @enderror" 
                                   id="expected_date" name="expected_date" value="{{ old('expected_date') }}">
                            @error('expected_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="priority" class="form-label">Priorité</label>
                            <select class="form-select @error('priority') is-invalid @enderror" id="priority" name="priority">
                                <option value="normal" {{ old('priority', 'normal') == 'normal' ? 'selected' : '' }}>Normale</option>
                                <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>Élevée</option>
                                <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgente</option>
                            </select>
                            @error('priority')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" 
                                  id="notes" name="notes" rows="3" 
                                  placeholder="Notes sur la commande...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Products Selection Card -->
            <div class="glass-card animation-fadeIn">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-pills me-2"></i>
                        Produits à commander
                    </h5>
                </div>
                <div class="card-body p-4">
                    <!-- Product Search -->
                    <div class="product-search-container">
                        <label for="product_search" class="form-label">Ajouter un produit</label>
                        <select class="form-select" id="product_search">
                            <option value="">Rechercher et sélectionner un produit</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" 
                                        data-name="{{ $product->name }}"
                                        data-dosage="{{ $product->dosage }}"
                                        data-current-stock="{{ $product->stock_quantity }}"
                                        data-threshold="{{ $product->stock_threshold }}"
                                        data-purchase-price="{{ $product->purchase_price }}">
                                    {{ $product->name }} 
                                    {{ $product->dosage ? '- ' . $product->dosage : '' }}
                                    (Stock: {{ $product->stock_quantity }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Products Table -->
                    <div class="table-responsive">
                        <table class="table mb-0" id="productsTable">
                            <thead>
                                <tr>
                                    <th>Produit</th>
                                    <th width="120">Stock actuel</th>
                                    <th width="100">Quantité</th>
                                    <th width="120">Prix unitaire</th>
                                    <th width="120">Total</th>
                                    <th width="80">Action</th>
                                </tr>
                            </thead>
                            <tbody id="productsTableBody">
                                <tr id="noProductsRow">
                                    <td colspan="6" class="text-center text-muted py-5">
                                        <i class="fas fa-box-open fa-3x mb-3 d-block opacity-50"></i>
                                        Aucun produit ajouté
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="4" class="text-end">Sous-total:</th>
                                    <th id="subtotal">0.00 €</th>
                                    <th></th>
                                </tr>
                                <tr>
                                    <th colspan="4" class="text-end">TVA (20%):</th>
                                    <th id="tax">0.00 €</th>
                                    <th></th>
                                </tr>
                                <tr class="table-primary">
                                    <th colspan="4" class="text-end">Total TTC:</th>
                                    <th id="total">0.00 €</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    @error('products')
                        <div class="alert alert-danger mt-3">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- Supplier Info -->
            <div class="supplier-info-card d-none animation-fadeIn" id="supplierInfo">
                <h6 class="mb-3">
                    <i class="fas fa-truck me-2 text-primary"></i>
                    Informations fournisseur
                </h6>
                <div id="supplierDetails"></div>
            </div>

            <!-- Order Summary -->
            <div class="glass-card mb-4 animation-fadeIn">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-clipboard-list me-2"></i>
                        Résumé de la commande
                    </h6>
                </div>
                <div class="card-body p-3">
                    <div class="summary-item">
                        <span class="summary-label">Fournisseur</span>
                        <span class="summary-value" id="selectedSupplier">Non sélectionné</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Date de commande</span>
                        <span class="summary-value" id="selectedDate">{{ date('d/m/Y') }}</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Date prévue</span>
                        <span class="summary-value" id="selectedExpectedDate">Non définie</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Priorité</span>
                        <span class="summary-value">
                            <span class="priority-badge priority-normal" id="selectedPriority">Normale</span>
                        </span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Produits</span>
                        <span class="summary-value">
                            <span class="quantity-badge">
                                <i class="fas fa-pills me-1"></i>
                                <span id="itemsCount">0</span>
                            </span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="stats-card animation-fadeIn">
                <div class="text-center">
                    <div class="row">
                        <div class="col-6">
                            <div class="h4 mb-1 text-primary" id="totalValue">0 €</div>
                            <small class="text-muted">Valeur totale</small>
                        </div>
                        <div class="col-6">
                            <div class="h4 mb-1 text-success" id="estimatedSavings">0 €</div>
                            <small class="text-muted">Économies estimées</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="glass-card animation-fadeIn">
                <div class="card-body">
                    <div class="d-grid gap-3">
                        <button type="submit" class="btn btn-primary btn-lg" id="submitBtn" disabled>
                            <i class="fas fa-save me-2"></i> Créer la commande
                        </button>
                        <button type="button" class="btn btn-success" id="saveAsDraftBtn" disabled>
                            <i class="fas fa-file-alt me-2"></i> Sauvegarder comme brouillon
                        </button>
                        <a href="{{ route('purchases.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i> Annuler
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@section('scripts')
<script>
let productCounter = 0;

document.addEventListener('DOMContentLoaded', function() {
    initializeForm();
    setupEventListeners();
    setDefaultDate();
});

function initializeForm() {
    updateSubmitButton();
    updateProgressIndicator();
}

function setupEventListeners() {
    const supplierSelect = document.getElementById('supplier_id');
    const productSearch = document.getElementById('product_search');
    const orderDate = document.getElementById('order_date');
    const expectedDate = document.getElementById('expected_date');
    const priority = document.getElementById('priority');
    const form = document.getElementById('purchaseForm');

    // Gérer la sélection du fournisseur
    supplierSelect.addEventListener('change', handleSupplierChange);
    
    // Trigger sur la sélection pré-existante
    if (supplierSelect.value) {
        supplierSelect.dispatchEvent(new Event('change'));
    }

    // Ajouter un produit
    productSearch.addEventListener('change', handleProductAdd);
    
    // Mettre à jour les dates affichées
    orderDate.addEventListener('change', updateSelectedDate);
    expectedDate.addEventListener('change', updateSelectedExpectedDate);
    priority.addEventListener('change', updateSelectedPriority);

    // Submit form validation
    form.addEventListener('submit', handleFormSubmit);
    
    // Save as Draft
    document.getElementById('saveAsDraftBtn').addEventListener('click', handleSaveAsDraft);
}

function setDefaultDate() {
    updateSelectedDate.call(document.getElementById('order_date'));
}

function handleSupplierChange() {
    const option = this.options[this.selectedIndex];
    
    document.getElementById('selectedSupplier').textContent = option.text || 'Non sélectionné';
    
    // Afficher les informations du fournisseur
    const supplierInfo = document.getElementById('supplierInfo');
    const supplierDetails = document.getElementById('supplierDetails');
    
    if (this.value) {
        const contact = option.dataset.contact;
        const phone = option.dataset.phone;
        const email = option.dataset.email;
        
        let details = `<div class="mb-2"><strong>${option.text}</strong></div>`;
        if (contact) details += `<div class="mb-2"><i class="fas fa-user me-2 text-primary"></i>${contact}</div>`;
        if (phone) details += `<div class="mb-2"><i class="fas fa-phone me-2 text-primary"></i>${phone}</div>`;
        if (email) details += `<div class="mb-2"><i class="fas fa-envelope me-2 text-primary"></i>${email}</div>`;
        
        supplierDetails.innerHTML = details;
        supplierInfo.classList.remove('d-none');
        supplierInfo.classList.add('animation-fadeIn');
    } else {
        supplierInfo.classList.add('d-none');
    }
    
    updateSubmitButton();
    updateProgressIndicator();
}

function handleProductAdd() {
    if (this.value) {
        const option = this.options[this.selectedIndex];
        const productData = {
            id: this.value,
            name: option.dataset.name,
            dosage: option.dataset.dosage,
            currentStock: parseInt(option.dataset.currentStock),
            threshold: parseInt(option.dataset.threshold),
            purchasePrice: parseFloat(option.dataset.purchasePrice)
        };

        addProduct(productData);
        this.value = '';
    }
}

function addProduct(product) {
    const tbody = document.getElementById('productsTableBody');
    const noProductsRow = document.getElementById('noProductsRow');
    
    // Supprimer la ligne "Aucun produit"
    if (noProductsRow) {
        noProductsRow.remove();
    }

    // Vérifier si le produit existe déjà
    const existingRow = document.querySelector(`tr[data-product-id="${product.id}"]`);
    if (existingRow) {
        showToast('Ce produit est déjà dans la liste', 'warning');
        return;
    }

    // Calculer la quantité suggérée (seuil - stock actuel, minimum 1)
    const suggestedQty = Math.max(1, product.threshold - product.currentStock);
    const stockStatus = getStockStatus(product.currentStock, product.threshold);

    const row = document.createElement('tr');
    row.setAttribute('data-product-id', product.id);
    row.className = 'animation-fadeIn';
    row.innerHTML = `
        <td>
            <div class="d-flex align-items-center">
                <span class="stock-indicator ${stockStatus}"></span>
                <div>
                    <strong>${product.name}</strong>
                    ${product.dosage ? '<br><small class="text-muted">' + product.dosage + '</small>' : ''}
                </div>
            </div>
            <input type="hidden" name="products[${productCounter}][id]" value="${product.id}">
        </td>
        <td class="text-center">
            <span class="${product.currentStock <= product.threshold ? 'text-danger fw-bold' : 'text-success'}">
                ${product.currentStock}
            </span>
            <br><small class="text-muted">Seuil: ${product.threshold}</small>
        </td>
        <td>
            <input type="number" class="form-control quantity-input text-center" 
                   name="products[${productCounter}][quantity]" 
                   value="${suggestedQty}" min="1" 
                   onchange="updateRowTotal(this.closest('tr'))" required>
        </td>
        <td>
            <div class="input-group">
                <input type="number" step="0.01" class="form-control price-input text-end" 
                       name="products[${productCounter}][price]" 
                       value="${product.purchasePrice.toFixed(2)}" min="0" 
                       onchange="updateRowTotal(this.closest('tr'))" required>
                <span class="input-group-text">€</span>
            </div>
        </td>
        <td class="text-end">
            <span class="row-total fw-bold text-primary">${(suggestedQty * product.purchasePrice).toFixed(2)} €</span>
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-danger btn-sm" onclick="removeProduct(this)" title="Supprimer">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;

    tbody.appendChild(row);
    productCounter++;
    calculateTotals();
    updateItemsCount();
    updateSubmitButton();
    updateProgressIndicator();
    
    // Add smooth scroll to the new row
    setTimeout(() => {
        row.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }, 100);
}

function removeProduct(button) {
    const row = button.closest('tr');
    row.style.opacity = '0';
    row.style.transform = 'translateX(-100%)';
    
    setTimeout(() => {
        row.remove();

        // Si plus de produits, afficher la ligne "Aucun produit"
        const tbody = document.getElementById('productsTableBody');
        if (tbody.children.length === 0) {
            tbody.innerHTML = `
                <tr id="noProductsRow" class="animation-fadeIn">
                    <td colspan="6" class="text-center text-muted py-5">
                        <i class="fas fa-box-open fa-3x mb-3 d-block opacity-50"></i>
                        Aucun produit ajouté
                    </td>
                </tr>
            `;
        }

        calculateTotals();
        updateItemsCount();
        updateSubmitButton();
        updateProgressIndicator();
    }, 300);
}

function updateRowTotal(row) {
    const quantity = parseInt(row.querySelector('.quantity-input').value) || 0;
    const price = parseFloat(row.querySelector('.price-input').value) || 0;
    const total = quantity * price;
    
    row.querySelector('.row-total').textContent = total.toFixed(2) + ' €';
    calculateTotals();
}

function calculateTotals() {
    const rows = document.querySelectorAll('#productsTableBody tr[data-product-id]');
    let subtotal = 0;

    rows.forEach(row => {
        const totalText = row.querySelector('.row-total').textContent;
        const total = parseFloat(totalText.replace(' €', ''));
        subtotal += total;
    });

    const tax = subtotal * 0.20;
    const total = subtotal + tax;

    document.getElementById('subtotal').textContent = subtotal.toFixed(2) + ' €';
    document.getElementById('tax').textContent = tax.toFixed(2) + ' €';
    document.getElementById('total').textContent = total.toFixed(2) + ' €';
    document.getElementById('totalValue').textContent = total.toFixed(0) + ' €';
    
    // Calculate estimated savings (mock calculation)
    const savings = subtotal * 0.05; // 5% estimated savings
    document.getElementById('estimatedSavings').textContent = savings.toFixed(0) + ' €';
}

function updateItemsCount() {
    const count = document.querySelectorAll('#productsTableBody tr[data-product-id]').length;
    document.getElementById('itemsCount').textContent = count;
}

function updateSubmitButton() {
    const hasSupplier = document.getElementById('supplier_id').value !== '';
    const hasProducts = document.querySelectorAll('#productsTableBody tr[data-product-id]').length > 0;
    const submitBtn = document.getElementById('submitBtn');
    const draftBtn = document.getElementById('saveAsDraftBtn');
    
    submitBtn.disabled = !(hasSupplier && hasProducts);
    draftBtn.disabled = !hasSupplier;
    
    if (hasSupplier && hasProducts) {
        submitBtn.classList.add('pulse-btn');
    } else {
        submitBtn.classList.remove('pulse-btn');
    }
}

function updateProgressIndicator() {
    const hasSupplier = document.getElementById('supplier_id').value !== '';
    const hasProducts = document.querySelectorAll('#productsTableBody tr[data-product-id]').length > 0;
    
    const step1 = document.getElementById('step1');
    const step2 = document.getElementById('step2');
    const step3 = document.getElementById('step3');
    
    // Step 1: General info (completed when supplier is selected)
    if (hasSupplier) {
        step1.classList.add('completed');
        step1.classList.remove('active');
        step1.innerHTML = '';
    } else {
        step1.classList.remove('completed');
        step1.classList.add('active');
        step1.innerHTML = '1';
    }
    
    // Step 2: Products (active when supplier selected, completed when products added)
    if (hasSupplier) {
        if (hasProducts) {
            step2.classList.add('completed');
            step2.classList.remove('active');
            step2.innerHTML = '';
        } else {
            step2.classList.add('active');
            step2.classList.remove('completed');
            step2.innerHTML = '2';
        }
    } else {
        step2.classList.remove('active', 'completed');
        step2.innerHTML = '2';
    }
    
    // Step 3: Validation (active when ready to submit)
    if (hasSupplier && hasProducts) {
        step3.classList.add('active');
        step3.innerHTML = '3';
    } else {
        step3.classList.remove('active');
        step3.innerHTML = '3';
    }
}

function updateSelectedDate() {
    const date = new Date(this.value);
    document.getElementById('selectedDate').textContent = date.toLocaleDateString('fr-FR');
}

function updateSelectedExpectedDate() {
    const date = this.value ? new Date(this.value) : null;
    document.getElementById('selectedExpectedDate').textContent = date ? date.toLocaleDateString('fr-FR') : 'Non définie';
}

function updateSelectedPriority() {
    const priorityTexts = {
        'normal': 'Normale',
        'high': 'Élevée', 
        'urgent': 'Urgente'
    };
    
    const priorityClasses = {
        'normal': 'priority-normal',
        'high': 'priority-high',
        'urgent': 'priority-urgent'
    };
    
    const selectedPriorityElement = document.getElementById('selectedPriority');
    selectedPriorityElement.textContent = priorityTexts[this.value] || 'Normale';
    
    // Remove all priority classes
    selectedPriorityElement.className = 'priority-badge';
    // Add appropriate class
    selectedPriorityElement.classList.add(priorityClasses[this.value] || 'priority-normal');
}

function getStockStatus(currentStock, threshold) {
    if (currentStock <= threshold * 0.5) return 'low';
    if (currentStock <= threshold) return 'medium';
    return 'high';
}

function handleFormSubmit(e) {
    e.preventDefault();
    
    const hasProducts = document.querySelectorAll('#productsTableBody tr[data-product-id]').length > 0;
    
    if (!hasProducts) {
        showToast('Veuillez ajouter au moins un produit à la commande.', 'danger');
        return false;
    }
    
    showLoadingOverlay();
    
    // Show loading state on submit button
    const submitBtn = document.getElementById('submitBtn');
    const originalContent = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Création...';
    
    // Submit the form
    this.submit();
}

function handleSaveAsDraft() {
    const hasSupplier = document.getElementById('supplier_id').value !== '';
    
    if (!hasSupplier) {
        showToast('Veuillez sélectionner un fournisseur avant de sauvegarder.', 'warning');
        return;
    }
    
    // Here you would typically make an AJAX call to save as draft
    showToast('Brouillon sauvegardé avec succès!', 'success');
    
    // Simulate saving
    const draftBtn = document.getElementById('saveAsDraftBtn');
    const originalContent = draftBtn.innerHTML;
    draftBtn.disabled = true;
    draftBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Sauvegarde...';
    
    setTimeout(() => {
        draftBtn.disabled = false;
        draftBtn.innerHTML = originalContent;
    }, 1500);
}

function showToast(message, type = 'info') {
    const toastContainer = getOrCreateToastContainer();
    
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} alert-dismissible fade show`;
    toast.style.cssText = 'margin-bottom: 0.5rem; animation: slideInRight 0.3s ease;';
    toast.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="fas fa-${getToastIcon(type)} me-2"></i>
            ${message}
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    toastContainer.appendChild(toast);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (toast.parentNode) {
            toast.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.remove();
                }
            }, 300);
        }
    }, 5000);
}

function getOrCreateToastContainer() {
    let container = document.getElementById('toastContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toastContainer';
        container.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 2000; min-width: 300px; max-width: 400px;';
        document.body.appendChild(container);
    }
    return container;
}

function getToastIcon(type) {
    const icons = {
        'success': 'check-circle',
        'danger': 'exclamation-triangle',
        'warning': 'exclamation-circle',
        'info': 'info-circle'
    };
    return icons[type] || 'info-circle';
}

function showLoadingOverlay() {
    document.getElementById('loadingOverlay').style.display = 'flex';
}

function hideLoadingOverlay() {
    document.getElementById('loadingOverlay').style.display = 'none';
}

// Auto-save functionality (optional)
let autoSaveTimer;
function autoSave() {
    clearTimeout(autoSaveTimer);
    autoSaveTimer = setTimeout(() => {
        const hasSupplier = document.getElementById('supplier_id').value !== '';
        if (hasSupplier) {
            // Simulate auto-save
            console.log('Auto-save triggered');
            // You could make an AJAX call here to save the form data
        }
    }, 30000); // Auto-save every 30 seconds
}

// Trigger auto-save on form changes
document.getElementById('purchaseForm').addEventListener('input', autoSave);
document.getElementById('purchaseForm').addEventListener('change', autoSave);

// Enhanced keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + S to save as draft
    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
        e.preventDefault();
        const draftBtn = document.getElementById('saveAsDraftBtn');
        if (!draftBtn.disabled) {
            handleSaveAsDraft();
        }
    }
    
    // Ctrl/Cmd + Enter to submit form
    if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
        e.preventDefault();
        const submitBtn = document.getElementById('submitBtn');
        if (!submitBtn.disabled) {
            document.getElementById('purchaseForm').dispatchEvent(new Event('submit'));
        }
    }
    
    // Escape to clear product search
    if (e.key === 'Escape') {
        const productSearch = document.getElementById('product_search');
        if (productSearch === document.activeElement) {
            productSearch.value = '';
            productSearch.blur();
        }
    }
});

// Enhanced accessibility - announce changes to screen readers
function announceToScreenReader(message) {
    const announcement = document.createElement('div');
    announcement.setAttribute('aria-live', 'polite');
    announcement.setAttribute('aria-atomic', 'true');
    announcement.style.position = 'absolute';
    announcement.style.left = '-10000px';
    announcement.style.width = '1px';
    announcement.style.height = '1px';
    announcement.style.overflow = 'hidden';
    announcement.textContent = message;
    
    document.body.appendChild(announcement);
    
    setTimeout(() => {
        document.body.removeChild(announcement);
    }, 1000);
}

// Add screen reader announcements for key actions
const originalAddProduct = addProduct;
addProduct = function(product) {
    originalAddProduct(product);
    announceToScreenReader(`Produit ${product.name} ajouté à la commande`);
};

const originalRemoveProduct = removeProduct;
removeProduct = function(button) {
    const row = button.closest('tr');
    const productName = row.querySelector('strong').textContent;
    originalRemoveProduct(button);
    announceToScreenReader(`Produit ${productName} supprimé de la commande`);
};

// Enhanced error handling
window.addEventListener('error', function(e) {
    console.error('JavaScript Error:', e.error);
    showToast('Une erreur s\'est produite. Veuillez réessayer.', 'danger');
});

// Initialize tooltips for better UX
document.addEventListener('DOMContentLoaded', function() {
    // Add tooltips to action buttons
    const deleteButtons = document.querySelectorAll('.btn-danger[onclick*="removeProduct"]');
    deleteButtons.forEach(button => {
        if (!button.getAttribute('title')) {
            button.setAttribute('title', 'Supprimer ce produit de la commande');
        }
    });
    
    // Add keyboard navigation hints
    const form = document.getElementById('purchaseForm');
    form.addEventListener('keydown', function(e) {
        if (e.key === 'Tab') {
            // Ensure logical tab order
            const focusableElements = form.querySelectorAll(
                'input:not([disabled]), select:not([disabled]), textarea:not([disabled]), button:not([disabled]), [tabindex]:not([tabindex="-1"])'
            );
            
            // Add visual focus indicator for keyboard users
            focusableElements.forEach(el => {
                el.addEventListener('focus', function() {
                    this.style.outline = '2px solid #4a90e2';
                    this.style.outlineOffset = '2px';
                });
                
                el.addEventListener('blur', function() {
                    this.style.outline = '';
                    this.style.outlineOffset = '';
                });
            });
        }
    });
});

// Performance optimization - debounce calculations
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Debounced version of calculateTotals for better performance
const debouncedCalculateTotals = debounce(calculateTotals, 300);

// Replace direct calls to calculateTotals with debounced version in quantity/price inputs
document.addEventListener('input', function(e) {
    if (e.target.classList.contains('quantity-input') || e.target.classList.contains('price-input')) {
        updateRowTotal(e.target.closest('tr'));
        debouncedCalculateTotals();
    }
});
</script>
@endsection