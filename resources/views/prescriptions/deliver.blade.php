@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <h2>Délivrance - {{ $prescription->prescription_number }}</h2>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('prescriptions.show', $prescription->id) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Retour à l'ordonnance
        </a>
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

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<form action="{{ route('prescriptions.process-delivery', $prescription->id) }}" method="POST">
    @csrf
    
    <div class="row">
        <div class="col-md-8">
            <!-- Informations patient -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user me-2"></i>Informations patient
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>{{ $prescription->client->full_name }}</strong>
                            @if($prescription->client->date_of_birth)
                                <br>Âge: {{ $prescription->client->age }} ans
                            @endif
                            @if($prescription->client->phone)
                                <br><i class="fas fa-phone me-1"></i>{{ $prescription->client->phone }}
                            @endif
                        </div>
                        <div class="col-md-6">
                            <strong>Médecin:</strong> {{ $prescription->doctor_name }}
                            @if($prescription->doctor_speciality)
                                <br>{{ $prescription->doctor_speciality }}
                            @endif
                            <br><strong>Date:</strong> {{ $prescription->prescription_date->format('d/m/Y') }}
                            <br><strong>Expire le:</strong> 
                            <span class="{{ $prescription->isAboutToExpire() ? 'text-warning' : '' }}">
                                {{ $prescription->expiry_date->format('d/m/Y') }}
                                @if($prescription->isAboutToExpire())
                                    ({{ $prescription->expiry_date->diffInDays(now()) }} jour(s) restant(s))
                                @endif
                            </span>
                        </div>
                    </div>
                    
                    @if($prescription->client->allergies)
                        <div class="alert alert-danger mt-3 mb-0">
                            <strong><i class="fas fa-exclamation-triangle me-1"></i>ALLERGIES CONNUES:</strong>
                            {{ $prescription->client->allergies }}
                        </div>
                    @endif

                    @if($prescription->medical_notes)
                        <div class="alert alert-info mt-3 mb-0">
                            <strong><i class="fas fa-notes-medical me-1"></i>Notes médicales:</strong>
                            {{ $prescription->medical_notes }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Médicaments à délivrer -->
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Médicaments à délivrer</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Médicament</th>
                                    <th class="text-center">Prescrit</th>
                                    <th class="text-center">Déjà délivré</th>
                                    <th class="text-center">Reste à délivrer</th>
                                    <th class="text-center">Quantité à délivrer</th>
                                    <th>Stock disponible</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $hasDeliverableItems = false; @endphp
                                @foreach($prescription->prescriptionItems as $item)
                                    @php
                                        $maxDeliverable = min($item->remaining_quantity, $item->product ? $item->product->stock_quantity : 0);
                                        $isDeliverable = $item->hasValidProduct() && $item->remaining_quantity > 0 && $item->product->stock_quantity > 0;
                                        if ($isDeliverable) $hasDeliverableItems = true;
                                    @endphp
                                    <tr class="{{ !$item->hasValidProduct() ? 'table-danger' : ($item->product && $item->product->stock_quantity < $item->remaining_quantity ? 'table-warning' : '') }}">
                                        <td>
                                            <strong>{{ $item->product_name }}</strong>
                                            @if($item->product && $item->product->dosage)
                                                <br><small class="text-muted">{{ $item->product->dosage }}</small>
                                            @endif
                                            <br><strong class="text-info">{{ $item->dosage_instructions }}</strong>
                                            @if($item->instructions)
                                                <br><small class="text-muted">{{ $item->instructions }}</small>
                                            @endif
                                            @if($item->duration_days)
                                                <br><small class="text-success">Durée: {{ $item->duration_days }} jour(s)</small>
                                            @endif
                                            @if(!$item->hasValidProduct())
                                                <br><small class="text-danger"><i class="fas fa-exclamation-triangle me-1"></i>Produit non disponible</small>
                                            @endif
                                        </td>
                                        <td class="text-center">{{ $item->quantity_prescribed }}</td>
                                        <td class="text-center">{{ $item->quantity_delivered }}</td>
                                        <td class="text-center">
                                            <strong class="{{ $item->remaining_quantity > 0 ? 'text-primary' : 'text-success' }}">
                                                {{ $item->remaining_quantity }}
                                            </strong>
                                        </td>
                                        <td class="text-center">
                                            @if($item->remaining_quantity > 0 && $item->hasValidProduct())
                                                <input type="hidden" name="items[{{ $loop->index }}][item_id]" value="{{ $item->id }}">
                                                <input type="number" 
                                                       class="form-control text-center quantity-input" 
                                                       name="items[{{ $loop->index }}][quantity_to_deliver]" 
                                                       value="{{ $maxDeliverable }}"
                                                       min="0" 
                                                       max="{{ $maxDeliverable }}"
                                                       data-max="{{ $item->remaining_quantity }}"
                                                       data-stock="{{ $item->product ? $item->product->stock_quantity : 0 }}"
                                                       data-product-name="{{ $item->product_name }}"
                                                       style="width: 80px;"
                                                       {{ !$isDeliverable ? 'disabled' : '' }}>
                                            @elseif($item->remaining_quantity == 0)
                                                <span class="text-success"><i class="fas fa-check-circle me-1"></i>Complet</span>
                                            @else
                                                <span class="text-muted">Non disponible</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($item->hasValidProduct())
                                                <span class="{{ $item->product->stock_quantity < $item->remaining_quantity ? 'text-danger' : ($item->product->stock_quantity > 0 ? 'text-success' : 'text-danger') }}">
                                                    {{ $item->product->stock_quantity }}
                                                    @if($item->product->isLowStock())
                                                        <i class="fas fa-exclamation-triangle text-warning ms-1" title="Stock faible"></i>
                                                    @endif
                                                </span>
                                                @if($item->product->stock_quantity < $item->remaining_quantity)
                                                    <br><small class="text-danger">Stock insuffisant!</small>
                                                @elseif($item->product->stock_quantity == 0)
                                                    <br><small class="text-danger">En rupture de stock</small>
                                                @endif
                                            @else
                                                <span class="text-danger">Produit supprimé</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @if(!$hasDeliverableItems)
                    <div class="card-footer">
                        <div class="alert alert-warning mb-0">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            Aucun médicament ne peut être délivré actuellement (stock insuffisant ou produits non disponibles).
                        </div>
                    </div>
                @endif
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Notes du pharmacien</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <textarea class="form-control" name="pharmacist_notes" rows="4" 
                                  placeholder="Notes sur la délivrance, conseils au patient...">{{ old('pharmacist_notes', $prescription->pharmacist_notes) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Résumé de la délivrance</h5>
                </div>
                <div class="card-body">
                    <div id="deliverySummary">
                        <p class="text-muted">Sélectionnez les quantités à délivrer pour voir le résumé.</p>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($hasDeliverableItems)
                            <button type="submit" class="btn btn-success" id="deliverBtn">
                                <i class="fas fa-pills me-1"></i> Enregistrer la délivrance
                            </button>
                        @else
                            <button type="button" class="btn btn-success" disabled title="Aucun médicament délivrable">
                                <i class="fas fa-lock me-1"></i> Aucune délivrance possible
                            </button>
                        @endif
                        <a href="{{ route('prescriptions.show', $prescription->id) }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i> Annuler
                        </a>
                        <a href="{{ route('prescriptions.print', $prescription->id) }}" class="btn btn-outline-primary" target="_blank">
                            <i class="fas fa-print me-1"></i> Imprimer l'ordonnance
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
document.addEventListener('DOMContentLoaded', function() {
    const quantityInputs = document.querySelectorAll('.quantity-input');
    const deliverBtn = document.getElementById('deliverBtn');
    
    // Update delivery summary when quantities change
    function updateDeliverySummary() {
        const summary = document.getElementById('deliverySummary');
        let totalItems = 0;
        let deliveryItems = [];
        
        quantityInputs.forEach(input => {
            const quantity = parseInt(input.value) || 0;
            if (quantity > 0) {
                totalItems += quantity;
                deliveryItems.push({
                    name: input.dataset.productName,
                    quantity: quantity
                });
            }
        });
        
        if (totalItems > 0) {
            let html = `<h6 class="text-success">Délivrance prévue:</h6>`;
            html += `<ul class="list-unstyled">`;
            deliveryItems.forEach(item => {
                html += `<li><strong>${item.name}:</strong> ${item.quantity}</li>`;
            });
            html += `</ul>`;
            html += `<p class="text-muted mb-0">Total: ${totalItems} médicament(s)</p>`;
            summary.innerHTML = html;
        } else {
            summary.innerHTML = '<p class="text-muted">Aucun médicament sélectionné pour délivrance.</p>';
        }
    }
    
    // Validate quantities and update summary
    quantityInputs.forEach(input => {
        input.addEventListener('input', function() {
            const max = parseInt(this.getAttribute('max'));
            const value = parseInt(this.value) || 0;
            const stock = parseInt(this.dataset.stock);
            const maxPrescribed = parseInt(this.dataset.max);
            
            if (value > max) {
                this.value = max;
                if (stock < maxPrescribed) {
                    alert(`Quantité ajustée au stock disponible: ${stock}`);
                } else {
                    alert(`Quantité ajustée au maximum prescrit: ${maxPrescribed}`);
                }
            }
            
            updateDeliverySummary();
        });
        
        input.addEventListener('change', updateDeliverySummary);
    });
    
    // Initial summary update
    updateDeliverySummary();
    
    // Delivery confirmation
    if (deliverBtn) {
        deliverBtn.addEventListener('click', function(e) {
            const quantities = [];
            let hasQuantities = false;
            
            quantityInputs.forEach(input => {
                const quantity = parseInt(input.value) || 0;
                if (quantity > 0) {
                    hasQuantities = true;
                    quantities.push({
                        product: input.dataset.productName,
                        quantity: quantity
                    });
                }
            });
            
            if (!hasQuantities) {
                e.preventDefault();
                alert('Veuillez sélectionner au moins une quantité à délivrer.');
                return;
            }
            
            let confirmMessage = 'Confirmer la délivrance des médicaments suivants ?\n\n';
            quantities.forEach(item => {
                confirmMessage += `• ${item.product}: ${item.quantity}\n`;
            });
            
            const confirm = window.confirm(confirmMessage);
            if (!confirm) {
                e.preventDefault();
            }
        });
    }
    
    // Auto-calculate optimal delivery quantities
    const autoFillBtn = document.createElement('button');
    autoFillBtn.type = 'button';
    autoFillBtn.className = 'btn btn-outline-info btn-sm';
    autoFillBtn.innerHTML = '<i class="fas fa-magic me-1"></i>Remplir automatiquement';
    autoFillBtn.title = 'Remplit automatiquement avec les quantités optimales disponibles';
    
    autoFillBtn.addEventListener('click', function() {
        quantityInputs.forEach(input => {
            if (!input.disabled) {
                const max = parseInt(input.getAttribute('max'));
                input.value = max;
            }
        });
        updateDeliverySummary();
    });
    
    // Add auto-fill button to the card header
    const cardHeader = document.querySelector('.card-header h5');
    if (cardHeader && quantityInputs.length > 0) {
        const headerContainer = cardHeader.parentElement;
        headerContainer.style.display = 'flex';
        headerContainer.style.justifyContent = 'space-between';
        headerContainer.style.alignItems = 'center';
        headerContainer.appendChild(autoFillBtn);
    }
});
</script>
@endsection