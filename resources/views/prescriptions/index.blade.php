@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <h2>Gestion des ordonnances</h2>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('prescriptions.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Nouvelle ordonnance
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

<!-- Statistiques -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Total ordonnances</h6>
                        <h4 class="mb-0">{{ $totalPrescriptions }}</h4>
                    </div>
                    <i class="fas fa-file-prescription fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">En attente</h6>
                        <h4 class="mb-0">{{ $pendingCount }}</h4>
                    </div>
                    <i class="fas fa-clock fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Expire bientôt</h6>
                        <h4 class="mb-0">{{ $expiringCount }}</h4>
                    </div>
                    <i class="fas fa-exclamation-triangle fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Expirées</h6>
                        <h4 class="mb-0">{{ $expiredCount }}</h4>
                    </div>
                    <i class="fas fa-calendar-times fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header bg-light">
        <h5 class="card-title mb-0">Filtres et recherche</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('prescriptions.index') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="search" class="form-label">Recherche</label>
                <input type="text" class="form-control" id="search" name="search" placeholder="N° ordonnance, client, médecin..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label for="status" class="form-label">Statut</label>
                <select class="form-select" id="status" name="status">
                    <option value="">Tous</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>En attente</option>
                    <option value="partially_delivered" {{ request('status') == 'partially_delivered' ? 'selected' : '' }}>Partiellement</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Complètement</option>
                    <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expirée</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="date_from" class="form-label">Date début</label>
                <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <label for="date_to" class="form-label">Date fin</label>
                <input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-2">
                <label for="expiry_filter" class="form-label">Expiration</label>
                <select class="form-select" id="expiry_filter" name="expiry_filter">
                    <option value="">Toutes</option>
                    <option value="expiring_soon" {{ request('expiry_filter') == 'expiring_soon' ? 'selected' : '' }}>Expire dans 7j</option>
                    <option value="expired" {{ request('expiry_filter') == 'expired' ? 'selected' : '' }}>Expirées</option>
                </select>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header bg-light">
        <h5 class="card-title mb-0">Liste des ordonnances ({{ $prescriptions->total() }})</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>N° Ordonnance</th>
                        <th>Client</th>
                        <th>Médecin</th>
                        <th>Date prescription</th>
                        <th>Date expiration</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($prescriptions as $prescription)
                        <tr class="{{ $prescription->isExpired() ? 'table-danger' : ($prescription->isAboutToExpire() ? 'table-warning' : '') }}">
                            <td>
                                <strong>{{ $prescription->prescription_number }}</strong>
                                @if($prescription->isAboutToExpire() && !$prescription->isExpired())
                                    <br><small class="text-warning">
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        Expire dans {{ $prescription->expiry_date->diffInDays(now()) }} jour(s)
                                    </small>
                                @endif
                            </td>
                            <td>
                                {{ $prescription->client->full_name }}
                                @if($prescription->client->allergies)
                                    <br><small class="text-danger">
                                        <i class="fas fa-exclamation-triangle me-1"></i>Allergies
                                    </small>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $prescription->doctor_name }}</strong>
                                @if($prescription->doctor_speciality)
                                    <br><small class="text-muted">{{ $prescription->doctor_speciality }}</small>
                                @endif
                                @if($prescription->doctor_phone)
                                    <br><small><i class="fas fa-phone me-1"></i>{{ $prescription->doctor_phone }}</small>
                                @endif
                            </td>
                            <td>{{ $prescription->prescription_date->format('d/m/Y') }}</td>
                            <td>
                                {{ $prescription->expiry_date->format('d/m/Y') }}
                                @if($prescription->isExpired())
                                    <br><small class="text-danger">Expirée</small>
                                @elseif($prescription->isAboutToExpire())
                                    <br><small class="text-warning">Expire bientôt</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $prescription->status_badge }}">
                                    {{ $prescription->status_label }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('prescriptions.show', $prescription->id) }}" class="btn btn-sm btn-info text-white">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('prescriptions.print', $prescription->id) }}" class="btn btn-sm btn-secondary" target="_blank">
                                        <i class="fas fa-print"></i>
                                    </a>
                                    
                                    {{-- Edit button - only for non-completed and non-expired prescriptions --}}
                                    @if(!in_array($prescription->status, ['completed', 'expired']))
                                        <a href="{{ route('prescriptions.edit', $prescription->id) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endif
                                    
                                    {{-- Deliver button - only for non-completed and non-expired prescriptions --}}
                                    @if($prescription->status !== 'completed' && !$prescription->isExpired())
                                        <a href="{{ route('prescriptions.deliver', $prescription->id) }}" class="btn btn-sm btn-success">
                                            <i class="fas fa-pills"></i>
                                        </a>
                                    @endif

                                    {{-- DELETE BUTTON - IMPROVED PERMISSION CHECK --}}
                                    @if(auth()->user()->canDeletePrescription($prescription))
                                        <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete({{ $prescription->id }}, '{{ $prescription->prescription_number }}')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @else
                                        {{-- Show disabled delete button with tooltip explaining why it's disabled --}}
                                        @php
                                            $restrictionReason = auth()->user()->getDeletionRestrictionReason($prescription);
                                        @endphp
                                        @if($restrictionReason)
                                            <button type="button" class="btn btn-sm btn-outline-danger" disabled title="{{ $restrictionReason }}">
                                                <i class="fas fa-lock"></i>
                                            </button>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <p class="text-muted mb-0">Aucune ordonnance trouvée</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($prescriptions->hasPages())
        <div class="card-footer">
            {{ $prescriptions->appends(request()->query())->links() }}
        </div>
    @endif
</div>

<!-- DELETE CONFIRMATION MODAL -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>Confirmer la suppression
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="deleteContent">
                    <p class="mb-3">Êtes-vous sûr de vouloir supprimer l'ordonnance <strong id="prescriptionNumber"></strong> ?</p>
                    <div id="deleteWarnings" class="alert alert-warning" style="display: none;">
                        <ul id="warningsList" class="mb-0"></ul>
                    </div>
                    <div id="stockImpact" class="alert alert-info" style="display: none;">
                        <strong>Impact sur le stock :</strong>
                        <ul id="stockList" class="mb-0 mt-2"></ul>
                    </div>
                    <div id="deleteError" class="alert alert-danger" style="display: none;"></div>
                    <div id="deleteLoading" class="text-center" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Chargement...</span>
                        </div>
                        <p class="mt-2">Vérification des dépendances...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" id="confirmDeleteBtn" class="btn btn-danger" onclick="executeDelete()">
                    <i class="fas fa-trash me-1"></i>Supprimer définitivement
                </button>
            </div>
        </div>
    </div>
</div>

<!-- HIDDEN DELETE FORM -->
<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

@endsection

@section('scripts')
<script>
let currentPrescriptionId = null;
let canDelete = false;

function confirmDelete(prescriptionId, prescriptionNumber) {
    currentPrescriptionId = prescriptionId;
    
    // Reset modal content
    document.getElementById('prescriptionNumber').textContent = prescriptionNumber;
    document.getElementById('deleteWarnings').style.display = 'none';
    document.getElementById('stockImpact').style.display = 'none';
    document.getElementById('deleteError').style.display = 'none';
    document.getElementById('deleteLoading').style.display = 'block';
    document.getElementById('confirmDeleteBtn').disabled = true;
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
    
    // Check dependencies
    checkDependencies(prescriptionId);
}

function checkDependencies(prescriptionId) {
    fetch(`{{ route('prescriptions.index') }}/${prescriptionId}/dependencies`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('deleteLoading').style.display = 'none';
            
            if (data.error) {
                showError(data.message || 'Erreur lors de la vérification');
                return;
            }
            
            canDelete = data.can_delete;
            
            // Show warnings if any
            if (data.warnings && data.warnings.length > 0) {
                const warningsList = document.getElementById('warningsList');
                warningsList.innerHTML = '';
                data.warnings.forEach(warning => {
                    const li = document.createElement('li');
                    li.textContent = warning;
                    warningsList.appendChild(li);
                });
                document.getElementById('deleteWarnings').style.display = 'block';
            }
            
            // Show stock impact if any
            if (data.stock_impact && data.stock_impact.length > 0) {
                const stockList = document.getElementById('stockList');
                stockList.innerHTML = '';
                data.stock_impact.forEach(impact => {
                    const li = document.createElement('li');
                    li.innerHTML = `<strong>${impact.product_name}</strong>: +${impact.quantity_to_restore} (${impact.current_stock} → ${impact.new_stock})`;
                    stockList.appendChild(li);
                });
                document.getElementById('stockImpact').style.display = 'block';
            }
            
            // Enable/disable delete button
            const deleteBtn = document.getElementById('confirmDeleteBtn');
            if (canDelete) {
                deleteBtn.disabled = false;
                deleteBtn.innerHTML = '<i class="fas fa-trash me-1"></i>Supprimer définitivement';
            } else {
                deleteBtn.disabled = true;
                deleteBtn.innerHTML = '<i class="fas fa-lock me-1"></i>Suppression non autorisée';
            }
        })
        .catch(error => {
            console.error('Error checking dependencies:', error);
            document.getElementById('deleteLoading').style.display = 'none';
            showError('Erreur lors de la vérification des dépendances');
        });
}

function showError(message) {
    const errorDiv = document.getElementById('deleteError');
    errorDiv.textContent = message;
    errorDiv.style.display = 'block';
    document.getElementById('confirmDeleteBtn').disabled = true;
}

function executeDelete() {
    if (!canDelete || !currentPrescriptionId) {
        return;
    }
    
    // Disable button and show loading
    const deleteBtn = document.getElementById('confirmDeleteBtn');
    deleteBtn.disabled = true;
    deleteBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Suppression...';
    
    // Set form action and submit
    const form = document.getElementById('deleteForm');
    form.action = `{{ route('prescriptions.index') }}/${currentPrescriptionId}`;
    form.submit();
}

// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
});
</script>
@endsection