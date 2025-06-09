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
    
    .form-check-input:checked {
        background-color: #336699 !important;
        border-color: #336699 !important;
    }
    
    .header-card {
        background: linear-gradient(180deg, #336699 0%, #4a90e2 100%) !important;
        color: white !important;
        border-radius: 15px 15px 0 0 !important;
        border: none !important;
        padding: 16px !important;
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
    
    .btn-danger-custom {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%) !important;
        border: none !important;
        border-radius: 8px !important;
        color: white !important;
    }
    
    .alert-custom {
        border: none !important;
        border-radius: 15px !important;
        background: linear-gradient(45deg, #dc3545 0%, #c82333 100%) !important;
        color: white !important;
        box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3) !important;
    }
    
    .alert-warning-custom {
        background: linear-gradient(45deg, #ffc107 0%, #fd7e14 100%) !important;
        color: white !important;
        border-radius: 12px !important;
        border: none !important;
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
    
    .quantity-input {
        border-radius: 8px !important;
        border: 2px solid #e9ecef !important;
        text-align: center !important;
        font-weight: 600 !important;
    }
    
    .unit-price, .row-total {
        font-weight: 600 !important;
        color: #2c3e50 !important;
    }
    
    #submitBtn:disabled {
        opacity: 0.6 !important;
        cursor: not-allowed !important;
        transform: none !important;
        box-shadow: none !important;
        background: linear-gradient(135deg, #6c757d 0%, #495057 100%) !important;
    }
    
    .no-products-icon {
        width: 60px;
        height: 60px;
        background: linear-gradient(180deg, #336699 0%, #4a90e2 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        opacity: 0.7;
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
    
    .card-header-client {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%) !important;
        color: white !important;
        border-radius: 15px 15px 0 0 !important;
        border: none !important;
        padding: 16px !important;
    }
    
    .card-header-payment {
        background: linear-gradient(135deg, #17a2b8 0%, #6f42c1 100%) !important;
        color: white !important;
        border-radius: 15px 15px 0 0 !important;
        border: none !important;
        padding: 16px !important;
    }
    
    .card-header-prescription {
        background: linear-gradient(135deg, #e83e8c 0%, #fd7e14 100%) !important;
        color: white !important;
        border-radius: 15px 15px 0 0 !important;
        border: none !important;
        padding: 16px !important;
    }
    
    .card-header-notes {
        background: linear-gradient(135deg, #6c757d 0%, #495057 100%) !important;
        color: white !important;
        border-radius: 15px 15px 0 0 !important;
        border: none !important;
        padding: 16px !important;
    }
    
    .footer-card {
        background: rgba(255, 255, 255, 0.95) !important;
        backdrop-filter: blur(10px) !important;
        border-radius: 15px !important;
        padding: 20px !important;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1) !important;
        border: none !important;
    }
    
    .info-icon {
        width: 40px;
        height: 40px;
        background: linear-gradient(180deg, #336699 0%, #4a90e2 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
        20%, 40%, 60%, 80% { transform: translateX(5px); }
    }
    
    .stock-error-alert {
        animation: slideInRight 0.3s ease-out;
    }
    
    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(100%);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
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
                    <i class="fas fa-plus text-white fa-lg"></i>
                </div>
                <div>
                    <h2 class="mb-0 fw-bold" style="color: #2c3e50;">Nouvelle vente</h2>
                    <small class="text-muted">Création d'une nouvelle transaction</small>
                </div>
            </div>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('sales.index') }}" class="btn btn-primary-custom">
                <i class="fas fa-arrow-left me-1"></i> Retour aux ventes
            </a>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-custom alert-dismissible fade show mb-4" role="alert">
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

    <form action="{{ route('sales.store') }}" method="POST" id="saleForm">
        @csrf
        
        <div class="row">
            <div class="col-md-8">
                <div class="card shadow-lg mb-4">
                    <div class="card-header header-card">
                        <h5 class="card-title mb-0 fw-bold">
                            <i class="fas fa-boxes me-2"></i>
                            Sélection des produits
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <label for="product_search" class="form-label fw-semibold">
                                <i class="fas fa-search me-1" style="color: #336699;"></i>
                                Rechercher un produit
                            </label>
                            <select class="form-select" id="product_search" style="border-radius: 12px; border: 2px solid #e9ecef; padding: 12px 16px; font-size: 16px;">
                                <option value="">Sélectionner un produit...</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" 
                                            data-name="{{ $product->name }}"
                                            data-dosage="{{ $product->dosage ?? '' }}"
                                            data-price="{{ $product->selling_price }}"
                                            data-stock="{{ $product->stock_quantity }}"
                                            data-prescription="{{ $product->prescription_required ? 'true' : 'false' }}">
                                        {{ $product->name }} 
                                        @if($product->dosage) - {{ $product->dosage }} @endif
                                        - {{ number_format($product->selling_price, 2) }}€ 
                                        (Stock: {{ $product->stock_quantity }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="table-responsive" style="border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);">
                            <table class="table table-hover mb-0" id="productsTable">
                                <thead>
                                    <tr>
                                        <th class="table-header">Produit</th>
                                        <th class="table-header" width="120">Quantité</th>
                                        <th class="table-header" width="140">Prix unitaire</th>
                                        <th class="table-header" width="140">Total</th>
                                        <th class="table-header" width="100">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="productsTableBody">
                                    <tr id="noProductsRow">
                                        <td colspan="5" class="text-center py-5" style="border: none;">
                                            <div class="no-products-icon mb-3">
                                                <i class="fas fa-shopping-cart fa-2x text-white"></i>
                                            </div>
                                            <h6 class="text-muted mb-2">Aucun produit ajouté</h6>
                                            <p class="text-muted small">Utilisez la recherche ci-dessus pour ajouter des produits</p>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="3" class="text-end table-header">Sous-total:</th>
                                        <th class="table-header" id="subtotal">0.00 €</th>
                                        <th class="table-header"></th>
                                    </tr>
                                    <tr>
                                        <th colspan="3" class="text-end table-header">TVA (20%):</th>
                                        <th class="table-header" id="tax">0.00 €</th>
                                        <th class="table-header"></th>
                                    </tr>
                                    <tr>
                                        <th colspan="3" class="text-end table-header">Remise:</th>
                                        <th class="table-header">
                                            <input type="number" class="form-control form-control-sm" id="discount" name="discount_amount" 
                                                   min="0" step="0.01" value="{{ old('discount_amount', 0) }}" onchange="calculateTotals()"
                                                   style="border-radius: 8px; border: 2px solid #e9ecef;">
                                        </th>
                                        <th class="table-header"></th>
                                    </tr>
                                    <tr>
                                        <th colspan="3" class="text-end table-footer">Total:</th>
                                        <th class="table-footer" id="total" style="font-size: 1.2rem;">0.00 €</th>
                                        <th class="table-footer"></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <!-- Informations client -->
                <div class="card shadow-lg mb-4">
                    <div class="card-header card-header-client">
                        <h5 class="card-title mb-0 fw-bold">
                            <i class="fas fa-user me-2"></i>
                            Informations client
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="client_id" class="form-label fw-semibold">Client</label>
                            <select class="form-select @error('client_id') is-invalid @enderror" id="client_id" name="client_id" 
                                    style="border-radius: 12px; border: 2px solid #e9ecef; padding: 12px 16px;">
                                <option value="">Client anonyme</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}" 
                                            {{ old('client_id', $selectedClientId ?? '') == $client->id ? 'selected' : '' }}
                                            data-allergies="{{ $client->allergies ?? '' }}">
                                        {{ $client->full_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('client_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Alerte allergies client -->
                        <div class="alert alert-warning-custom" id="allergiesAlert" style="display: none;">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-exclamation-triangle me-2 fa-lg"></i>
                                <div>
                                    <strong>Allergies connues:</strong>
                                    <div id="allergiesText" class="mt-1"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Paiement -->
                <div class="card shadow-lg mb-4">
                    <div class="card-header card-header-payment">
                        <h5 class="card-title mb-0 fw-bold">
                            <i class="fas fa-credit-card me-2"></i>
                            Paiement
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="payment_method" class="form-label fw-semibold">
                                Mode de paiement <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('payment_method') is-invalid @enderror" id="payment_method" name="payment_method" required
                                    style="border-radius: 12px; border: 2px solid #e9ecef; padding: 12px 16px;">
                                <option value="cash" {{ old('payment_method', 'cash') == 'cash' ? 'selected' : '' }}>
                                    Espèces
                                </option>
                                <option value="card" {{ old('payment_method') == 'card' ? 'selected' : '' }}>
                                    Carte bancaire
                                </option>
                                <option value="insurance" {{ old('payment_method') == 'insurance' ? 'selected' : '' }}>
                                    Assurance
                                </option>
                                <option value="other" {{ old('payment_method') == 'other' ? 'selected' : '' }}>
                                    Autre
                                </option>
                            </select>
                            @error('payment_method')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Ordonnance -->
                <div class="card shadow-lg mb-4">
                    <div class="card-header card-header-prescription">
                        <h5 class="card-title mb-0 fw-bold">
                            <i class="fas fa-prescription-bottle me-2"></i>
                            Ordonnance
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="form-check" style="padding: 12px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 12px;">
                                <input class="form-check-input" type="checkbox" id="has_prescription" name="has_prescription" 
                                       value="1" {{ old('has_prescription') ? 'checked' : '' }}
                                       style="transform: scale(1.2);">
                                <label class="form-check-label fw-semibold ms-2" for="has_prescription">
                                    Vente avec ordonnance
                                </label>
                            </div>
                        </div>
                        
                        <div class="mb-3" id="prescription_number_field" style="display: {{ old('has_prescription') ? 'block' : 'none' }};">
                            <label for="prescription_number" class="form-label fw-semibold">Numéro d'ordonnance</label>
                            <input type="text" class="form-control @error('prescription_number') is-invalid @enderror" 
                                   id="prescription_number" name="prescription_number" value="{{ old('prescription_number') }}"
                                   style="border-radius: 12px; border: 2px solid #e9ecef; padding: 12px 16px;"
                                   placeholder="Ex: ORD-2024-001">
                            @error('prescription_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="card shadow-lg">
                    <div class="card-header card-header-notes">
                        <h5 class="card-title mb-0 fw-bold">
                            <i class="fas fa-sticky-note me-2"></i>
                            Notes
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" name="notes" rows="4" placeholder="Notes additionnelles, instructions spéciales..."
                                      style="border-radius: 12px; border: 2px solid #e9ecef; padding: 12px 16px; resize: vertical;">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bouton de soumission -->
        <div class="row mt-4">
            <div class="col-12 text-end">
                <div class="d-flex justify-content-between align-items-center footer-card">
                    <div class="d-flex align-items-center">
                        <div class="info-icon me-3">
                            <i class="fas fa-info text-white"></i>
                        </div>
                        <div>
                            <small class="text-muted">Vérifiez les informations avant de valider</small>
                            <div class="fw-semibold" style="color: #336699;">Total à encaisser: <span id="totalFooter" class="fs-5">0.00 €</span></div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success-custom px-5 py-3" id="submitBtn" disabled style="font-size: 16px;">
                        <i class="fas fa-save me-2"></i> Enregistrer la vente
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
let productCounter = 0;

document.addEventListener('DOMContentLoaded', function() {
    const productSearch = document.getElementById('product_search');
    const hasPrescription = document.getElementById('has_prescription');
    const prescriptionField = document.getElementById('prescription_number_field');
    const clientSelect = document.getElementById('client_id');

    // Gérer l'affichage du champ numéro d'ordonnance avec animation
    hasPrescription.addEventListener('change', function() {
        if (this.checked) {
            prescriptionField.style.display = 'block';
            prescriptionField.style.opacity = '0';
            setTimeout(() => {
                prescriptionField.style.opacity = '1';
                prescriptionField.style.transition = 'opacity 0.3s ease';
            }, 50);
        } else {
            prescriptionField.style.opacity = '0';
            setTimeout(() => {
                prescriptionField.style.display = 'none';
            }, 300);
        }
    });

    // Gérer l'affichage des allergies client avec animation
    clientSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const allergies = selectedOption.dataset.allergies;
        const allergiesAlert = document.getElementById('allergiesAlert');
        const allergiesText = document.getElementById('allergiesText');
        
        if (allergies && allergies.trim() !== '') {
            allergiesText.textContent = allergies;
            allergiesAlert.style.display = 'block';
            allergiesAlert.style.opacity = '0';
            setTimeout(() => {
                allergiesAlert.style.opacity = '1';
                allergiesAlert.style.transition = 'opacity 0.3s ease';
            }, 50);
        } else {
            allergiesAlert.style.opacity = '0';
            setTimeout(() => {
                allergiesAlert.style.display = 'none';
            }, 300);
        }
    });

    // Trigger allergies check on page load if client is pre-selected
    if (clientSelect.value) {
        clientSelect.dispatchEvent(new Event('change'));
    }

    // Ajouter un produit avec animation
    productSearch.addEventListener('change', function() {
        if (this.value) {
            const option = this.options[this.selectedIndex];
            const productData = {
                id: this.value,
                name: option.dataset.name,
                dosage: option.dataset.dosage,
                price: parseFloat(option.dataset.price),
                stock: parseInt(option.dataset.stock),
                prescription: option.dataset.prescription === 'true'
            };

            addProduct(productData);
            this.value = '';
        }
    });

    // Submit form validation avec feedback visuel
    document.getElementById('saleForm').addEventListener('submit', function(e) {
        const tbody = document.getElementById('productsTableBody');
        const hasProducts = tbody.querySelectorAll('tr[data-product-id]').length > 0;
        
        if (!hasProducts) {
            e.preventDefault();
            
            // Animation d'erreur
            const productCard = document.querySelector('.card');
            productCard.style.animation = 'shake 0.5s ease-in-out';
            
            // Afficher un message d'erreur stylé
            const alertHtml = `
                <div class="alert alert-custom alert-dismissible fade show" role="alert" style="margin-bottom: 20px;">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-triangle me-3 fa-lg"></i>
                        <div>
                            <strong>Action requise :</strong> Veuillez ajouter au moins un produit à la vente.
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
                </div>
            `;
            
            document.querySelector('.row.mb-4').insertAdjacentHTML('afterend', alertHtml);
            
            setTimeout(() => {
                productCard.style.animation = '';
            }, 500);
            
            return false;
        }
        
        // Show loading state avec animation
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Enregistrement en cours...';
        submitBtn.style.background = 'linear-gradient(135deg, #6c757d 0%, #495057 100%)';
    });
});

function addProduct(product) {
    const tbody = document.getElementById('productsTableBody');
    const noProductsRow = document.getElementById('noProductsRow');
    
    // Supprimer la ligne "Aucun produit" avec animation
    if (noProductsRow) {
        noProductsRow.style.opacity = '0';
        setTimeout(() => {
            noProductsRow.remove();
        }, 300);
    }

    // Vérifier si le produit existe déjà
    const existingRow = document.querySelector(`tr[data-product-id="${product.id}"]`);
    if (existingRow) {
        const quantityInput = existingRow.querySelector('.quantity-input');
        const currentQty = parseInt(quantityInput.value);
        const newQty = currentQty + 1;
        
        if (newQty <= product.stock) {
            quantityInput.value = newQty;
            updateRowTotal(existingRow);
            
            // Animation de mise à jour
            existingRow.style.background = 'rgba(51, 102, 153, 0.1)';
            setTimeout(() => {
                existingRow.style.background = '';
                existingRow.style.transition = 'background 0.3s ease';
            }, 500);
        } else {
            showStockError(`Stock insuffisant pour ${product.name}. Maximum disponible: ${product.stock}`);
        }
        return;
    }

    const row = document.createElement('tr');
    row.setAttribute('data-product-id', product.id);
    row.style.opacity = '0';
    row.innerHTML = `
        <td style="padding: 16px; border: none;">
            <div class="d-flex align-items-center">
                <div class="product-icon me-3">
                    <i class="fas fa-pills text-white small"></i>
                </div>
                <div>
                    <div class="fw-semibold">${product.name}</div>
                    ${product.dosage ? '<small class="text-muted">' + product.dosage + '</small>' : ''}
                    ${product.prescription ? '<br><span class="badge bg-warning text-dark small"><i class="fas fa-prescription-bottle me-1"></i>Ordonnance requise</span>' : ''}
                </div>
            </div>
            <input type="hidden" name="products[${productCounter}][id]" value="${product.id}">
        </td>
        <td style="padding: 16px; border: none;">
            <input type="number" class="form-control quantity-input" name="products[${productCounter}][quantity]" 
                   value="1" min="1" max="${product.stock}" onchange="updateRowTotal(this.closest('tr'))" required>
        </td>
        <td style="padding: 16px; border: none;">
            <span class="unit-price">${product.price.toFixed(2)} €</span>
        </td>
        <td style="padding: 16px; border: none;">
            <span class="row-total fw-bold text-success">${product.price.toFixed(2)} €</span>
        </td>
        <td style="padding: 16px; border: none;">
            <button type="button" class="btn btn-sm btn-danger-custom" onclick="removeProduct(this)">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;

    tbody.appendChild(row);
    
    // Animation d'apparition
    setTimeout(() => {
        row.style.opacity = '1';
        row.style.transition = 'opacity 0.3s ease';
    }, 50);
    
    productCounter++;
    calculateTotals();
    updateSubmitButton();
}

function removeProduct(button) {
    const row = button.closest('tr');
    
    // Animation de disparition
    row.style.opacity = '0';
    row.style.transform = 'translateX(-20px)';
    row.style.transition = 'all 0.3s ease';
    
    setTimeout(() => {
        row.remove();

        // Si plus de produits, afficher la ligne "Aucun produit"
        const tbody = document.getElementById('productsTableBody');
        if (tbody.children.length === 0) {
            const noProductsHTML = `
                <tr id="noProductsRow" style="opacity: 0;">
                    <td colspan="5" class="text-center py-5" style="border: none;">
                        <div class="no-products-icon mb-3">
                            <i class="fas fa-shopping-cart fa-2x text-white"></i>
                        </div>
                        <h6 class="text-muted mb-2">Aucun produit ajouté</h6>
                        <p class="text-muted small">Utilisez la recherche ci-dessus pour ajouter des produits</p>
                    </td>
                </tr>
            `;
            tbody.innerHTML = noProductsHTML;
            
            // Animation d'apparition
            const noProductsRow = document.getElementById('noProductsRow');
            setTimeout(() => {
                noProductsRow.style.opacity = '1';
                noProductsRow.style.transition = 'opacity 0.3s ease';
            }, 50);
        }

        calculateTotals();
        updateSubmitButton();
    }, 300);
}

function updateRowTotal(row) {
    const quantity = parseInt(row.querySelector('.quantity-input').value);
    const unitPriceText = row.querySelector('.unit-price').textContent;
    const unitPrice = parseFloat(unitPriceText.replace(' €', ''));
    const total = quantity * unitPrice;
    
    const rowTotalElement = row.querySelector('.row-total');
    rowTotalElement.textContent = total.toFixed(2) + ' €';
    
    // Animation de mise à jour
    rowTotalElement.style.background = 'rgba(51, 102, 153, 0.2)';
    rowTotalElement.style.borderRadius = '6px';
    rowTotalElement.style.padding = '4px 8px';
    rowTotalElement.style.transition = 'all 0.3s ease';
    
    setTimeout(() => {
        rowTotalElement.style.background = '';
        rowTotalElement.style.padding = '';
    }, 1000);
    
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

    const discount = parseFloat(document.getElementById('discount').value) || 0;
    const tax = subtotal * 0.20;
    const finalTotal = subtotal + tax - discount;

    // Mise à jour des totaux avec animation
    const subtotalElement = document.getElementById('subtotal');
    const taxElement = document.getElementById('tax');
    const totalElement = document.getElementById('total');
    const totalFooterElement = document.getElementById('totalFooter');

    subtotalElement.textContent = subtotal.toFixed(2) + ' €';
    taxElement.textContent = tax.toFixed(2) + ' €';
    totalElement.textContent = finalTotal.toFixed(2) + ' €';
    totalFooterElement.textContent = finalTotal.toFixed(2) + ' €';

    // Animation des totaux
    [subtotalElement, taxElement, totalElement, totalFooterElement].forEach(element => {
        element.style.transform = 'scale(1.05)';
        element.style.transition = 'transform 0.2s ease';
        setTimeout(() => {
            element.style.transform = 'scale(1)';
        }, 200);
    });
}

function updateSubmitButton() {
    const tbody = document.getElementById('productsTableBody');
    const hasProducts = tbody.querySelectorAll('tr[data-product-id]').length > 0;
    const submitBtn = document.getElementById('submitBtn');
    
    if (hasProducts) {
        submitBtn.disabled = false;
        submitBtn.className = 'btn btn-success-custom px-5 py-3';
        submitBtn.style.transform = 'scale(1)';
        submitBtn.style.cursor = 'pointer';
    } else {
        submitBtn.disabled = true;
        submitBtn.style.transform = 'scale(0.98)';
        submitBtn.style.cursor = 'not-allowed';
    }
}

function showStockError(message) {
    // Créer une alerte temporaire pour les erreurs de stock
    const alertHtml = `
        <div class="alert alert-warning-custom alert-dismissible fade show stock-error-alert" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px; box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3);">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-triangle me-3 fa-lg"></i>
                <div>
                    <strong>Stock insuffisant :</strong><br>
                    ${message}
                </div>
            </div>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', alertHtml);
    
    // Supprimer automatiquement après 5 secondes
    setTimeout(() => {
        const alert = document.querySelector('.stock-error-alert');
        if (alert) {
            alert.style.opacity = '0';
            alert.style.transform = 'translateX(100%)';
            setTimeout(() => alert.remove(), 300);
        }
    }, 5000);
}
</script>
@endsection