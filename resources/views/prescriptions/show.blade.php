@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <h2>Ordonnance {{ $prescription->prescription_number }}</h2>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('prescriptions.index') }}" class="btn btn-secondary me-2">
            <i class="fas fa-arrow-left me-1"></i> Retour
        </a>
        <a href="{{ route('prescriptions.print', $prescription->id) }}" class="btn btn-primary" target="_blank">
            <i class="fas fa-print me-1"></i> Imprimer
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
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

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Détails de l'ordonnance</h5>
                <span class="badge {{ $prescription->status_badge }}">
                    {{ $prescription->status_label }}
                </span>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Numéro:</strong>
                                <span>{{ $prescription->prescription_number }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Date de prescription:</strong>
                                <span>{{ $prescription->prescription_date->format('d/m/Y') }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Date d'expiration:</strong>
                                <span class="{{ $prescription->isExpired() ? 'text-danger' : ($prescription->isAboutToExpire() ? 'text-warning' : '') }}">
                                    {{ $prescription->expiry_date->format('d/m/Y') }}
                                    @if($prescription->isExpired())
                                        (Expirée)
                                    @elseif($prescription->isAboutToExpire())
                                        (Expire dans {{ $prescription->expiry_date->diffInDays(now()) }} jour(s))
                                    @endif
                                </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Créée par:</strong>
                                <span>{{ $prescription->createdBy->name }}</span>
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Client:</strong>
                                <span>
                                    <a href="{{ route('clients.show', $prescription->client->id) }}">
                                        {{ $prescription->client->full_name }}
                                    </a>
                                </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Médecin:</strong>
                                <span>{{ $prescription->doctor_name }}</span>
                            </li>
                            @if($prescription->doctor_speciality)
                                <li class="list-group-item d-flex justify-content-between">
                                    <strong>Spécialité:</strong>
                                    <span>{{ $prescription->doctor_speciality }}</span>
                                </li>
                            @endif
                            @if($prescription->doctor_phone)
                                <li class="list-group-item d-flex justify-content-between">
                                    <strong>Téléphone:</strong>
                                    <span>{{ $prescription->doctor_phone }}</span>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>

                @if($prescription->medical_notes)
                    <div class="alert alert-info">
                        <strong><i class="fas fa-notes-medical me-1"></i>Notes médicales:</strong>
                        <p class="mb-0 mt-2">{{ $prescription->medical_notes }}</p>
                    </div>
                @endif

                @if($prescription->pharmacist_notes)
                    <div class="alert alert-secondary">
                        <strong><i class="fas fa-user-md me-1"></i>Notes du pharmacien:</strong>
                        <p class="mb-0 mt-2">{{ $prescription->pharmacist_notes }}</p>
                    </div>
                @endif
            </div>
        </div>

        @if($prescription->client->allergies)
            <div class="alert alert-danger">
                <strong><i class="fas fa-exclamation-triangle me-1"></i>Allergies connues du client:</strong>
                <p class="mb-0 mt-2">{{ $prescription->client->allergies }}</p>
            </div>
        @endif

        <div class="card">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Médicaments prescrits</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Médicament</th>
                                <th class="text-center">Quantité prescrite</th>
                                <th class="text-center">Quantité délivrée</th>
                                <th>Posologie</th>
                                <th>Progression</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($prescription->prescriptionItems as $item)
                                <tr>
                                    <td>
                                        <strong>{{ $item->product_name }}</strong>
                                        @if($item->product && $item->product->dosage)
                                            <br><small class="text-muted">{{ $item->product->dosage }}</small>
                                        @endif
                                        @if($item->duration_days)
                                            <br><small class="text-info">Durée: {{ $item->duration_days }} jour(s)</small>
                                        @endif
                                        @if(!$item->hasValidProduct())
                                            <br><small class="text-danger"><i class="fas fa-exclamation-triangle me-1"></i>Produit non disponible</small>
                                        @endif
                                    </td>
                                    <td class="text-center">{{ $item->quantity_prescribed }}</td>
                                    <td class="text-center">
                                        <span class="{{ $item->isFullyDelivered() ? 'text-success' : ($item->isPartiallyDelivered() ? 'text-warning' : '') }}">
                                            {{ $item->quantity_delivered }}
                                        </span>
                                    </td>
                                    <td>
                                        <strong>{{ $item->dosage_instructions }}</strong>
                                        @if($item->instructions)
                                            <br><small class="text-muted">{{ $item->instructions }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar {{ $item->isFullyDelivered() ? 'bg-success' : 'bg-warning' }}" 
                                                 role="progressbar" 
                                                 style="width: {{ $item->delivery_percentage }}%"
                                                 aria-valuenow="{{ $item->delivery_percentage }}" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                                {{ $item->delivery_percentage }}%
                                            </div>
                                        </div>
                                        @if($item->remaining_quantity > 0)
                                            <small class="text-muted">Reste: {{ $item->remaining_quantity }}</small>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('prescriptions.print', $prescription->id) }}" class="btn btn-primary" target="_blank">
                        <i class="fas fa-print me-1"></i> Imprimer l'ordonnance
                    </a>
                    
                    {{-- Edit button - only for non-completed and non-expired prescriptions --}}
                    @if(!in_array($prescription->status, ['completed', 'expired']))
                        <a href="{{ route('prescriptions.edit', $prescription->id) }}" class="btn btn-warning">
                            <i class="fas fa-edit me-1"></i> Modifier
                        </a>
                    @endif
                    
                  {{-- Deliver button - only for non-completed and non-expired prescriptions --}}
                    @if($prescription->status !== 'completed' && !$prescription->isExpired())
                        <a href="{{ route('prescriptions.deliver', $prescription->id) }}" class="btn btn-success">
                            <i class="fas fa-pills me-1"></i> Délivrer médicaments
                        </a>
                    @endif
                    
                    <a href="{{ route('clients.show', $prescription->client->id) }}" class="btn btn-outline-primary">
                        <i class="fas fa-user me-1"></i> Voir le client
                    </a>
                    
                    {{-- DELETE BUTTON - IMPROVED PERMISSION CHECK --}}
                    @if(auth()->user()->canDeletePrescription($prescription))
                        <button type="button" class="btn btn-danger" onclick="confirmPrescriptionDelete({{ $prescription->id }}, '{{ $prescription->prescription_number }}')">
                            <i class="fas fa-trash me-1"></i> Supprimer l'ordonnance
                        </button>
                    @else
                        {{-- Show disabled delete button with explanation --}}
                        @php
                            $restrictionReason = auth()->user()->getDeletionRestrictionReason($prescription);
                        @endphp
                        @if($restrictionReason)
                            <button type="button" class="btn btn-outline-danger" disabled title="{{ $restrictionReason }}">
                                <i class="fas fa-lock me-1"></i> {{ $restrictionReason }}
                            </button>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- DELETE CONFIRMATION MODAL -->
<div class="modal fade" id="deletePrescriptionModal" tabindex="-1" aria-labelledby="deletePrescriptionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deletePrescriptionModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>Confirmer la suppression
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="deletePrescriptionContent">
                    <p class="mb-3">Êtes-vous sûr de vouloir supprimer l'ordonnance <strong id="prescriptionNumberToDelete"></strong> ?</p>
                    <div id="deletePrescriptionWarnings" class="alert alert-warning" style="display: none;">
                        <ul id="prescriptionWarningsList" class="mb-0"></ul>
                    </div>
                    <div id="prescriptionStockImpact" class="alert alert-info" style="display: none;">
                        <strong>Impact sur le stock :</strong>
                        <ul id="prescriptionStockList" class="mb-0 mt-2"></ul>
                    </div>
                    <div id="deletePrescriptionError" class="alert alert-danger" style="display: none;"></div>
                    <div id="deletePrescriptionLoading" class="text-center" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Chargement...</span>
                        </div>
                        <p class="mt-2">Vérification des dépendances...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" id="confirmPrescriptionDeleteBtn" class="btn btn-danger" onclick="executePrescriptionDelete()">
                    <i class="fas fa-trash me-1"></i>Supprimer définitivement
                </button>
            </div>
        </div>
    </div>
</div>

<!-- HIDDEN DELETE FORM -->
<form id="deletePrescriptionForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

@endsection

@section('scripts')
<script>
let currentPrescriptionIdToDelete = null;
let canDeletePrescription = false;
const baseUrl = '{{ url('/') }}';

function confirmPrescriptionDelete(prescriptionId, prescriptionNumber) {
    currentPrescriptionIdToDelete = prescriptionId;
    
    // Reset modal content
    document.getElementById('prescriptionNumberToDelete').textContent = prescriptionNumber;
    document.getElementById('deletePrescriptionWarnings').style.display = 'none';
    document.getElementById('prescriptionStockImpact').style.display = 'none';
    document.getElementById('deletePrescriptionError').style.display = 'none';
    document.getElementById('deletePrescriptionLoading').style.display = 'block';
    document.getElementById('confirmPrescriptionDeleteBtn').disabled = true;
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('deletePrescriptionModal'));
    modal.show();
    
    // Check dependencies
    checkPrescriptionDependencies(prescriptionId);
}

function checkPrescriptionDependencies(prescriptionId) {
    fetch(`${baseUrl}/prescriptions/${prescriptionId}/dependencies`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('deletePrescriptionLoading').style.display = 'none';
            
            if (data.error) {
                showPrescriptionDeleteError(data.message || 'Erreur lors de la vérification');
                return;
            }
            
            canDeletePrescription = data.can_delete;
            
            // Show warnings if any
            if (data.warnings && data.warnings.length > 0) {
                const warningsList = document.getElementById('prescriptionWarningsList');
                warningsList.innerHTML = '';
                data.warnings.forEach(warning => {
                    const li = document.createElement('li');
                    li.textContent = warning;
                    warningsList.appendChild(li);
                });
                document.getElementById('deletePrescriptionWarnings').style.display = 'block';
            }
            
            // Show stock impact if any
            if (data.stock_impact && data.stock_impact.length > 0) {
                const stockList = document.getElementById('prescriptionStockList');
                stockList.innerHTML = '';
                data.stock_impact.forEach(impact => {
                    const li = document.createElement('li');
                    li.innerHTML = `<strong>${impact.product_name}</strong>: +${impact.quantity_to_restore} (${impact.current_stock} → ${impact.new_stock})`;
                    stockList.appendChild(li);
                });
                document.getElementById('prescriptionStockImpact').style.display = 'block';
            }
            
            // Enable/disable delete button
            const deleteBtn = document.getElementById('confirmPrescriptionDeleteBtn');
            if (canDeletePrescription) {
                deleteBtn.disabled = false;
                deleteBtn.innerHTML = '<i class="fas fa-trash me-1"></i>Supprimer définitivement';
            } else {
                deleteBtn.disabled = true;
                deleteBtn.innerHTML = '<i class="fas fa-lock me-1"></i>Suppression non autorisée';
            }
        })
        .catch(error => {
            console.error('Error checking dependencies:', error);
            document.getElementById('deletePrescriptionLoading').style.display = 'none';
            showPrescriptionDeleteError('Erreur lors de la vérification des dépendances');
        });
}

function showPrescriptionDeleteError(message) {
    const errorDiv = document.getElementById('deletePrescriptionError');
    errorDiv.textContent = message;
    errorDiv.style.display = 'block';
    document.getElementById('confirmPrescriptionDeleteBtn').disabled = true;
}

function executePrescriptionDelete() {
    if (!canDeletePrescription || !currentPrescriptionIdToDelete) {
        return;
    }
    
    // Disable button and show loading
    const deleteBtn = document.getElementById('confirmPrescriptionDeleteBtn');
    deleteBtn.disabled = true;
    deleteBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Suppression...';
    
    // Set form action and submit
    const form = document.getElementById('deletePrescriptionForm');
    form.action = `${baseUrl}/prescriptions/${currentPrescriptionIdToDelete}`;
    form.submit();
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