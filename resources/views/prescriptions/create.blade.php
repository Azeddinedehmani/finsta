@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <h2>Nouvelle ordonnance</h2>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('prescriptions.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Retour aux ordonnances
        </a>
    </div>
</div>

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

<form action="{{ route('prescriptions.store') }}" method="POST" id="prescriptionForm">
    @csrf
    
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Informations générales</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="client_id" class="form-label">Client <span class="text-danger">*</span></label>
                            <select class="form-select @error('client_id') is-invalid @enderror" id="client_id" name="client_id" required>
                                <option value="">Sélectionner un client</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}
                                            data-allergies="{{ $client->allergies }}">
                                        {{ $client->full_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('client_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label for="doctor_name" class="form-label">Nom du médecin <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('doctor_name') is-invalid @enderror" 
                                   id="doctor_name" name="doctor_name" value="{{ old('doctor_name') }}" required>
                            @error('doctor_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="doctor_phone" class="form-label">Téléphone du médecin</label>
                            <input type="tel" class="form-control @error('doctor_phone') is-invalid @enderror" 
                                   id="doctor_phone" name="doctor_phone" value="{{ old('doctor_phone') }}">
                            @error('doctor_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label for="doctor_speciality" class="form-label">Spécialité</label>
                            <input type="text" class="form-control @error('doctor_speciality') is-invalid @enderror" 
                                   id="doctor_speciality" name="doctor_speciality" value="{{ old('doctor_speciality') }}">
                            @error('doctor_speciality')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="prescription_date" class="form-label">Date de prescription <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('prescription_date') is-invalid @enderror" 
                                   id="prescription_date" name="prescription_date" value="{{ old('prescription_date', date('Y-m-d')) }}" required>
                            @error('prescription_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label for="expiry_date" class="form-label">Date d'expiration <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('expiry_date') is-invalid @enderror" 
                                   id="expiry_date" name="expiry_date" value="{{ old('expiry_date', date('Y-m-d', strtotime('+3 months'))) }}" required>
                            @error('expiry_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="medical_notes" class="form-label">Notes médicales</label>
                        <textarea class="form-control @error('medical_notes') is-invalid @enderror" 
                                  id="medical_notes" name="medical_notes" rows="3" 
                                  placeholder="Notes du médecin, recommandations spéciales...">{{ old('medical_notes') }}</textarea>
                        @error('medical_notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Médicaments prescrits</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="product_search" class="form-label">Ajouter un médicament</label>
                        <select class="form-select" id="product_search">
                            <option value="">Sélectionner un produit</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" 
                                        data-name="{{ $product->name }}"
                                        data-dosage="{{ $product->dosage }}"
                                        data-prescription="{{ $product->prescription_required ? 'true' : 'false' }}">
                                    {{ $product->name }} {{ $product->dosage ? '- ' . $product->dosage : '' }}
                                    @if($product->prescription_required)
                                        (Ordonnance requise)
                                    @else
                                        (Vente libre)
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered" id="itemsTable">
                            <thead>
                                <tr>
                                    <th>Médicament</th>
                                    <th width="100">Quantité</th>
                                    <th width="200">Posologie</th>
                                    <th width="100">Durée (jours)</th>
                                    <th width="200">Instructions</th>
                                    <th width="80">Action</th>
                                </tr>
                            </thead>
                            <tbody id="itemsTableBody">
                                <tr id="noItemsRow">
                                    <td colspan="6" class="text-center text-muted">
                                        Aucun médicament ajouté
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    @error('items')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <!-- Alerte allergies client -->
            <div class="card mb-3" id="allergiesAlert" style="display: none;">
                <div class="card-header bg-danger text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>Allergies connues
                    </h5>
                </div>
                <div class="card-body">
                    <p id="allergiesText" class="mb-0"></p>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Résumé de l'ordonnance</h5>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <strong>Client:</strong> <span id="selectedClient">Non sélectionné</span>
                    </div>
                    <div class="mb-2">
                        <strong>Médecin:</strong> <span id="selectedDoctor">Non renseigné</span>
                    </div>
                    <div class="mb-2">
                        <strong>Date:</strong> <span id="selectedDate">{{ date('d/m/Y') }}</span>
                    </div>
                    <div class="mb-2">
                        <strong>Expiration:</strong> <span id="selectedExpiry">{{ date('d/m/Y', strtotime('+3 months')) }}</span>
                    </div>
                    <div>
                        <strong>Nombre de médicaments:</strong> <span id="itemsCount">0</span>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                            <i class="fas fa-save me-1"></i> Enregistrer l'ordonnance
                        </button>
                        <a href="{{ route('prescriptions.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i> Annuler
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
let itemCounter = 0;

document.addEventListener('DOMContentLoaded', function() {
    const clientSelect = document.getElementById('client_id');
    const productSearch = document.getElementById('product_search');
    const doctorName = document.getElementById('doctor_name');
    const prescriptionDate = document.getElementById('prescription_date');
    const expiryDate = document.getElementById('expiry_date');
    
    // Gérer la sélection du client
    clientSelect.addEventListener('change', function() {
        const option = this.options[this.selectedIndex];
        const allergies = option.dataset.allergies;
        
        document.getElementById('selectedClient').textContent = option.text || 'Non sélectionné';
        
        // Afficher/masquer l'alerte allergies
        const allergiesAlert = document.getElementById('allergiesAlert');
        if (allergies && allergies.trim() !== '') {
            document.getElementById('allergiesText').textContent = allergies;
            allergiesAlert.style.display = 'block';
        } else {
            allergiesAlert.style.display = 'none';
        }
        
        updateSubmitButton();
    });
    
    // Mettre à jour l'affichage du médecin
    doctorName.addEventListener('input', function() {
        document.getElementById('selectedDoctor').textContent = this.value || 'Non renseigné';
    });
    
    // Mettre à jour les dates
    prescriptionDate.addEventListener('change', function() {
        if (this.value) {
            const date = new Date(this.value);
            document.getElementById('selectedDate').textContent = date.toLocaleDateString('fr-FR');
        }
    });
    
    expiryDate.addEventListener('change', function() {
        if (this.value) {
            const date = new Date(this.value);
            document.getElementById('selectedExpiry').textContent = date.toLocaleDateString('fr-FR');
        }
    });
    
    // Ajouter un médicament
    productSearch.addEventListener('change', function() {
        if (this.value) {
            const option = this.options[this.selectedIndex];
            const productData = {
                id: this.value,
                name: option.dataset.name,
                dosage: option.dataset.dosage,
                prescription: option.dataset.prescription === 'true'
            };

            addPrescriptionItem(productData);
            this.value = '';
        }
    });
    
    // Restore old values if validation failed
    @if(old('items'))
        // Restore prescription items from old input
        const oldItems = @json(old('items'));
        const productOptions = document.getElementById('product_search').options;
        
        oldItems.forEach((item, index) => {
            // Find product data from options
            for (let i = 0; i < productOptions.length; i++) {
                const option = productOptions[i];
                if (option.value == item.product_id) {
                    const productData = {
                        id: option.value,
                        name: option.dataset.name,
                        dosage: option.dataset.dosage,
                        prescription: option.dataset.prescription === 'true'
                    };
                    
                    addPrescriptionItem(productData);
                    
                    // Set the old values
                    const lastRow = document.querySelector('#itemsTableBody tr:last-child');
                    if (lastRow) {
                        const quantityInput = lastRow.querySelector('input[name*="[quantity_prescribed]"]');
                        const dosageInput = lastRow.querySelector('input[name*="[dosage_instructions]"]');
                        const durationInput = lastRow.querySelector('input[name*="[duration_days]"]');
                        const instructionsInput = lastRow.querySelector('textarea[name*="[instructions]"]');
                        
                        if (quantityInput) quantityInput.value = item.quantity_prescribed || '';
                        if (dosageInput) dosageInput.value = item.dosage_instructions || '';
                        if (durationInput) durationInput.value = item.duration_days || '';
                        if (instructionsInput) instructionsInput.value = item.instructions || '';
                    }
                    break;
                }
            }
        });
    @endif
});

function addPrescriptionItem(product) {
    const tbody = document.getElementById('itemsTableBody');
    const noItemsRow = document.getElementById('noItemsRow');
    
    // Supprimer la ligne "Aucun médicament"
    if (noItemsRow) {
        noItemsRow.remove();
    }

    // Vérifier si le produit existe déjà
    const existingRow = document.querySelector(`tr[data-product-id="${product.id}"]`);
    if (existingRow) {
        alert('Ce médicament est déjà dans la liste');
        return;
    }

    const row = document.createElement('tr');
    row.setAttribute('data-product-id', product.id);
    row.innerHTML = `
        <td>
            <strong>${product.name}</strong>
            ${product.dosage ? '<br><small class="text-muted">' + product.dosage + '</small>' : ''}
            ${product.prescription ? '<br><small class="text-success"><i class="fas fa-prescription-bottle me-1"></i>Ordonnance requise</small>' : '<br><small class="text-warning">Vente libre</small>'}
            <input type="hidden" name="items[${itemCounter}][product_id]" value="${product.id}">
        </td>
        <td>
            <input type="number" class="form-control quantity-input" name="items[${itemCounter}][quantity_prescribed]" 
                   value="1" min="1" required>
        </td>
        <td>
            <input type="text" class="form-control" name="items[${itemCounter}][dosage_instructions]" 
                   placeholder="Ex: 1 cp matin et soir" required>
        </td>
        <td>
            <input type="number" class="form-control" name="items[${itemCounter}][duration_days]" 
                   min="1" placeholder="7">
        </td>
        <td>
            <textarea class="form-control" name="items[${itemCounter}][instructions]" rows="2" 
                      placeholder="Instructions spéciales..."></textarea>
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-danger" onclick="removePrescriptionItem(this)">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;

    tbody.appendChild(row);
    itemCounter++;
    updateItemsCount();
    updateSubmitButton();
}

function removePrescriptionItem(button) {
    const row = button.closest('tr');
    row.remove();

    // Si plus de médicaments, afficher la ligne "Aucun médicament"
    const tbody = document.getElementById('itemsTableBody');
    if (tbody.children.length === 0) {
        tbody.innerHTML = `
            <tr id="noItemsRow">
                <td colspan="6" class="text-center text-muted">
                    Aucun médicament ajouté
                </td>
            </tr>
        `;
    }

    updateItemsCount();
    updateSubmitButton();
}

function updateItemsCount() {
    const count = document.querySelectorAll('#itemsTableBody tr[data-product-id]').length;
    document.getElementById('itemsCount').textContent = count;
}

function updateSubmitButton() {
    const hasClient = document.getElementById('client_id').value !== '';
    const hasDoctor = document.getElementById('doctor_name').value !== '';
    const hasItems = document.querySelectorAll('#itemsTableBody tr[data-product-id]').length > 0;
    const submitBtn = document.getElementById('submitBtn');
    
    submitBtn.disabled = !(hasClient && hasDoctor && hasItems);
}

// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            if (alert.parentNode) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        }, 5000);
    });
});
</script>
@endsection