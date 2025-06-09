@extends('layouts.app')

@section('content')
<div class="min-vh-100" style="background: linear-gradient(135deg, #e3f2fd 0%, #e8f5e8 100%); margin: -20px; padding: 20px;">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="d-flex align-items-center">
                <div class="me-3" style="width: 50px; height: 50px; background: linear-gradient(180deg, #336699 0%, #4a90e2 100%); border-radius: 15px; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 25px rgba(51, 102, 153, 0.3);">
                    <i class="fas fa-truck text-white fa-lg"></i>
                </div>
                <div>
                    <h2 class="mb-0 fw-bold" style="font-family: 'Poppins', sans-serif; color: #2c3e50;">Réception - {{ $purchase->purchase_number }}</h2>
                    <small class="text-muted">Enregistrement de la réception des produits</small>
                </div>
            </div>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('purchases.show', $purchase->id) }}" class="btn text-white fw-semibold" style="background: linear-gradient(135deg, #6c757d 0%, #495057 100%); border: none; border-radius: 12px; padding: 12px 20px; box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3); transition: all 0.3s ease;">
                <i class="fas fa-arrow-left me-1"></i> Retour à la commande
            </a>
        </div>
    </div>

    <form action="{{ route('purchases.process-reception', $purchase->id) }}" method="POST">
        @csrf
        
        <div class="row">
            <div class="col-md-8">
                <!-- Informations fournisseur -->
                <div class="card mb-4 border-0 shadow-lg" style="border-radius: 15px; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px);">
                    <div class="card-header border-0" style="background: linear-gradient(135deg, #17a2b8 0%, #6f42c1 100%); color: white; border-radius: 15px 15px 0 0;">
                        <h5 class="card-title mb-0 fw-bold">
                            <i class="fas fa-truck me-2"></i>Informations fournisseur
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="d-flex align-items-start">
                                    <div class="me-3" style="width: 40px; height: 40px; background: linear-gradient(180deg, #336699 0%, #4a90e2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-building text-white"></i>
                                    </div>
                                    <div>
                                        <strong style="color: #336699; font-size: 1.1rem;">{{ $purchase->supplier->name }}</strong>
                                        @if($purchase->supplier->contact_person)
                                            <br><span style="color: #336699;">Contact:</span> <span class="fw-medium">{{ $purchase->supplier->contact_person }}</span>
                                        @endif
                                        @if($purchase->supplier->phone_number)
                                            <br><i class="fas fa-phone me-1" style="color: #336699;"></i><span class="fw-medium">{{ $purchase->supplier->phone_number }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-start">
                                    <div class="me-3" style="width: 40px; height: 40px; background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-file-invoice text-white"></i>
                                    </div>
                                    <div>
                                        <strong style="color: #336699;">Commande:</strong> <span class="fw-medium">{{ $purchase->purchase_number }}</span><br>
                                        <strong style="color: #336699;">Date commande:</strong> <span class="fw-medium">{{ $purchase->order_date->format('d/m/Y') }}</span><br>
                                        @if($purchase->expected_date)
                                            <strong style="color: #336699;">Date prévue:</strong> 
                                            <span class="fw-medium">{{ $purchase->expected_date->format('d/m/Y') }}</span>
                                            @if($purchase->expected_date->isPast())
                                                <span class="badge bg-danger ms-1">En retard</span>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Produits à recevoir -->
                <div class="card border-0 shadow-lg" style="border-radius: 15px; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px);">
                    <div class="card-header border-0" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 15px 15px 0 0;">
                        <h5 class="card-title mb-0 fw-bold">
                            <i class="fas fa-boxes me-2" style="color: #336699;"></i>
                            Produits à recevoir
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead style="background-color: #f8f9fa;">
                                    <tr>
                                        <th class="border-0 fw-semibold" style="color: #336699;">Produit</th>
                                        <th class="border-0 fw-semibold text-center" style="color: #336699;">Commandé</th>
                                        <th class="border-0 fw-semibold text-center" style="color: #336699;">Déjà reçu</th>
                                        <th class="border-0 fw-semibold text-center" style="color: #336699;">Reste à recevoir</th>
                                        <th class="border-0 fw-semibold text-center" style="color: #336699;">Quantité reçue</th>
                                        <th class="border-0 fw-semibold" style="color: #336699;">Stock actuel</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($purchase->purchaseItems as $item)
                                        <tr class="{{ $item->product->stock_quantity <= $item->product->stock_threshold ? 'table-warning' : '' }}">
                                            <td class="border-0">
                                                <div class="d-flex align-items-center">
                                                    <div class="me-2" style="width: 32px; height: 32px; background: linear-gradient(180deg, #336699 0%, #4a90e2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                                        <i class="fas fa-pills text-white small"></i>
                                                    </div>
                                                    <div>
                                                        <strong>{{ $item->product->name }}</strong>
                                                        @if($item->product->dosage)
                                                            <br><small class="text-muted">{{ $item->product->dosage }}</small>
                                                        @endif
                                                        @if($item->notes)
                                                            <br><small class="badge bg-info">{{ $item->notes }}</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center border-0">
                                                <span class="fw-medium">{{ $item->quantity_ordered }}</span>
                                            </td>
                                            <td class="text-center border-0">
                                                <span class="fw-medium text-success">{{ $item->quantity_received }}</span>
                                            </td>
                                            <td class="text-center border-0">
                                                <strong class="text-warning">{{ $item->remaining_quantity }}</strong>
                                            </td>
                                            <td class="text-center border-0">
                                                @if($item->remaining_quantity > 0)
                                                    <input type="hidden" name="items[{{ $loop->index }}][item_id]" value="{{ $item->id }}">
                                                    <input type="number" 
                                                           class="form-control text-center quantity-input fw-medium" 
                                                           name="items[{{ $loop->index }}][quantity_received]" 
                                                           value="{{ $item->remaining_quantity }}"
                                                           min="0" 
                                                           max="{{ $item->remaining_quantity }}"
                                                           style="width: 80px; border-radius: 8px; border: 2px solid #e9ecef; margin: 0 auto;">
                                                @else
                                                    <span class="badge bg-success rounded-pill">Complet</span>
                                                @endif
                                            </td>
                                            <td class="border-0">
                                                <div class="d-flex align-items-center">
                                                    <span class="fw-medium {{ $item->product->stock_quantity <= $item->product->stock_threshold ? 'text-danger' : 'text-success' }}">
                                                        {{ $item->product->stock_quantity }}
                                                    </span>
                                                    @if($item->product->stock_quantity <= $item->product->stock_threshold)
                                                        <span class="badge bg-danger ms-2">Stock faible!</span>
                                                    @endif
                                                </div>
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
                <!-- Notes de réception -->
                <div class="card mb-4 border-0 shadow-lg" style="border-radius: 15px; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px);">
                    <div class="card-header border-0" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 15px 15px 0 0;">
                        <h5 class="card-title mb-0 fw-bold">
                            <i class="fas fa-sticky-note me-2" style="color: #336699;"></i>
                            Notes de réception
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <textarea class="form-control" name="notes" rows="4" 
                                      placeholder="Notes sur la réception, problèmes rencontrés..."
                                      style="border-radius: 10px; border: 2px solid #e9ecef; resize: none;">{{ old('notes', $purchase->notes) }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Aide -->
                <div class="card mb-4 border-0 shadow-lg" style="border-radius: 15px; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px);">
                    <div class="card-header border-0" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 15px 15px 0 0;">
                        <h5 class="card-title mb-0 fw-bold">
                            <i class="fas fa-info-circle me-2" style="color: #336699;"></i>
                            Aide
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert border-0 mb-0" style="border-radius: 12px; background: linear-gradient(135deg, #17a2b8 0%, #6f42c1 100%); color: white;">
                            <strong><i class="fas fa-lightbulb me-1"></i>Conseils :</strong>
                            <ul class="mt-2 mb-0">
                                <li>Vérifiez la qualité des produits reçus</li>
                                <li>Contrôlez les dates d'expiration</li>
                                <li>Notez les éventuels problèmes</li>
                                <li>Le stock sera automatiquement mis à jour</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="card border-0 shadow-lg" style="border-radius: 15px; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px);">
                    <div class="card-header border-0" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 15px 15px 0 0;">
                        <h5 class="card-title mb-0 fw-bold">
                            <i class="fas fa-cogs me-2" style="color: #336699;"></i>
                            Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn text-white fw-semibold" id="receiveBtn" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border: none; border-radius: 10px; padding: 12px;">
                                <i class="fas fa-check me-1"></i> Enregistrer la réception
                            </button>
                            <button type="button" class="btn text-white fw-semibold" onclick="fillAllRemaining()" style="background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%); border: none; border-radius: 10px; padding: 12px; color: #212529 !important;">
                                <i class="fas fa-fill me-1"></i> Tout recevoir
                            </button>
                            <button type="button" class="btn text-white fw-semibold" onclick="clearAll()" style="background: linear-gradient(135deg, #6c757d 0%, #495057 100%); border: none; border-radius: 10px; padding: 12px;">
                                <i class="fas fa-eraser me-1"></i> Tout vider
                            </button>
                            <a href="{{ route('purchases.show', $purchase->id) }}" class="btn fw-semibold" style="background: transparent; border: 2px solid #dc3545; color: #dc3545; border-radius: 10px; padding: 12px;">
                                <i class="fas fa-times me-1"></i> Annuler
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
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
    
    .form-control:focus {
        border-color: #336699;
        box-shadow: 0 0 0 0.2rem rgba(51, 102, 153, 0.25);
    }
    
    .quantity-input {
        transition: all 0.3s ease;
    }
    
    .quantity-input:focus {
        transform: scale(1.05);
        border-color: #336699 !important;
        box-shadow: 0 0 0 0.2rem rgba(51, 102, 153, 0.25);
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
    
    .alert {
        border: none;
    }
    
    .badge {
        font-size: 0.75rem;
    }
</style>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const quantityInputs = document.querySelectorAll('.quantity-input');
    
    quantityInputs.forEach(input => {
        input.addEventListener('change', function() {
            const max = parseInt(this.getAttribute('max'));
            const value = parseInt(this.value);
            
            if (value > max) {
                this.value = max;
                // Style modern alert
                showAlert('Quantité ajustée au maximum disponible: ' + max, 'warning');
            }
            
            if (value < 0) {
                this.value = 0;
            }
        });
        
        // Add focus effect
        input.addEventListener('focus', function() {
            this.style.borderColor = '#336699';
            this.style.boxShadow = '0 0 0 0.2rem rgba(51, 102, 153, 0.25)';
        });
        
        input.addEventListener('blur', function() {
            this.style.borderColor = '#e9ecef';
            this.style.boxShadow = 'none';
        });
    });
    
    document.getElementById('receiveBtn').addEventListener('click', function(e) {
        const quantities = [];
        quantityInputs.forEach(input => {
            if (parseInt(input.value) > 0) {
                quantities.push(input.value);
            }
        });
        
        if (quantities.length === 0) {
            e.preventDefault();
            showAlert('Veuillez saisir au moins une quantité à recevoir.', 'error');
            return;
        }
        
        const confirm = window.confirm('Confirmer la réception de ces produits ? Le stock sera automatiquement mis à jour.');
        if (!confirm) {
            e.preventDefault();
        }
    });
});

function fillAllRemaining() {
    const quantityInputs = document.querySelectorAll('.quantity-input');
    quantityInputs.forEach(input => {
        const max = parseInt(input.getAttribute('max'));
        input.value = max;
        
        // Add animation effect
        input.style.background = 'linear-gradient(135deg, #28a745 0%, #20c997 100%)';
        input.style.color = 'white';
        
        setTimeout(() => {
            input.style.background = '';
            input.style.color = '';
        }, 500);
    });
    
    showAlert('Toutes les quantités restantes ont été remplies', 'success');
}

function clearAll() {
    const quantityInputs = document.querySelectorAll('.quantity-input');
    quantityInputs.forEach(input => {
        input.value = 0;
        
        // Add animation effect
        input.style.background = 'linear-gradient(135deg, #6c757d 0%, #495057 100%)';
        input.style.color = 'white';
        
        setTimeout(() => {
            input.style.background = '';
            input.style.color = '';
        }, 500);
    });
    
    showAlert('Toutes les quantités ont été vidées', 'info');
}

function showAlert(message, type) {
    // Create modern alert
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show border-0`;
    alertDiv.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        max-width: 400px;
    `;
    
    const bgColor = {
        'success': 'linear-gradient(135deg, #28a745 0%, #20c997 100%)',
        'warning': 'linear-gradient(135deg, #ffc107 0%, #fd7e14 100%)',
        'error': 'linear-gradient(135deg, #dc3545 0%, #c82333 100%)',
        'info': 'linear-gradient(135deg, #17a2b8 0%, #6f42c1 100%)'
    };
    
    alertDiv.style.background = bgColor[type] || bgColor['info'];
    alertDiv.style.color = type === 'warning' ? '#212529' : 'white';
    
    alertDiv.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'warning' ? 'exclamation-triangle' : type === 'error' ? 'times-circle' : 'info-circle'} me-2"></i>
        ${message}
        <button type="button" class="btn-close ${type === 'warning' ? '' : 'btn-close-white'}" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 3000);
}
</script>
@endsection