@extends('layouts.app')

@section('content')
<div class="min-vh-100" style="background: linear-gradient(135deg, #e3f2fd 0%, #e8f5e8 100%); margin: -20px; padding: 20px;">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="d-flex align-items-center">
                <div class="me-3" style="width: 50px; height: 50px; background: linear-gradient(180deg, #336699 0%, #4a90e2 100%); border-radius: 15px; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 25px rgba(51, 102, 153, 0.3);">
                    <i class="fas fa-edit text-white fa-lg"></i>
                </div>
                <div>
                    <h2 class="mb-0 fw-bold" style="font-family: 'Poppins', sans-serif; color: #2c3e50;">Modifier la commande {{ $purchase->purchase_number }}</h2>
                    <small class="text-muted">Modification des informations de commande</small>
                </div>
            </div>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('purchases.show', $purchase->id) }}" class="btn text-white fw-semibold" style="background: linear-gradient(135deg, #6c757d 0%, #495057 100%); border: none; border-radius: 12px; padding: 12px 20px; box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3); transition: all 0.3s ease;">
                <i class="fas fa-arrow-left me-1"></i> Retour à la commande
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0" role="alert" style="border-radius: 15px; background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0" role="alert" style="border-radius: 15px; background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('purchases.update', $purchase->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="row">
            <div class="col-md-8">
                <!-- Informations générales -->
                <div class="card mb-4 border-0 shadow-lg" style="border-radius: 15px; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px);">
                    <div class="card-header border-0" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 15px 15px 0 0;">
                        <h5 class="card-title mb-0 fw-bold">
                            <i class="fas fa-info-circle me-2" style="color: #336699;"></i>
                            Informations générales
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" style="color: #336699;">Fournisseur</label>
                                <div class="input-group">
                                    <span class="input-group-text" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border: 2px solid #e9ecef; border-radius: 10px 0 0 10px;">
                                        <i class="fas fa-truck" style="color: #336699;"></i>
                                    </span>
                                    <input type="text" class="form-control" value="{{ $purchase->supplier->name }}" readonly style="border-radius: 0 10px 10px 0; border: 2px solid #e9ecef; background-color: #f8f9fa;">
                                </div>
                                <small class="text-muted"><i class="fas fa-lock me-1"></i>Le fournisseur ne peut pas être modifié</small>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" style="color: #336699;">Date de commande</label>
                                <div class="input-group">
                                    <span class="input-group-text" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border: 2px solid #e9ecef; border-radius: 10px 0 0 10px;">
                                        <i class="fas fa-calendar-alt" style="color: #336699;"></i>
                                    </span>
                                    <input type="date" class="form-control" value="{{ $purchase->order_date->format('Y-m-d') }}" readonly style="border-radius: 0 10px 10px 0; border: 2px solid #e9ecef; background-color: #f8f9fa;">
                                </div>
                                <small class="text-muted"><i class="fas fa-lock me-1"></i>La date de commande ne peut pas être modifiée</small>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="expected_date" class="form-label fw-semibold" style="color: #336699;">Date de livraison prévue</label>
                                <div class="input-group">
                                    <span class="input-group-text" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border: 2px solid #e9ecef; border-radius: 10px 0 0 10px;">
                                        <i class="fas fa-calendar-check" style="color: #336699;"></i>
                                    </span>
                                    <input type="date" class="form-control @error('expected_date') is-invalid @enderror" 
                                           id="expected_date" name="expected_date" 
                                           value="{{ old('expected_date', $purchase->expected_date ? $purchase->expected_date->format('Y-m-d') : '') }}"
                                           style="border-radius: 0 10px 10px 0; border: 2px solid #e9ecef;">
                                </div>
                                @error('expected_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="status" class="form-label fw-semibold" style="color: #336699;">Statut <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border: 2px solid #e9ecef; border-radius: 10px 0 0 10px;">
                                        <i class="fas fa-flag" style="color: #336699;"></i>
                                    </span>
                                    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required style="border-radius: 0 10px 10px 0; border: 2px solid #e9ecef;">
                                        <option value="pending" {{ old('status', $purchase->status) == 'pending' ? 'selected' : '' }}>
                                            En attente
                                        </option>
                                        <option value="cancelled" {{ old('status', $purchase->status) == 'cancelled' ? 'selected' : '' }}>
                                            Annulé
                                        </option>
                                    </select>
                                </div>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label fw-semibold" style="color: #336699;">Notes</label>
                            <div class="input-group">
                                <span class="input-group-text align-items-start" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border: 2px solid #e9ecef; border-radius: 10px 0 0 10px; padding-top: 12px;">
                                    <i class="fas fa-sticky-note" style="color: #336699;"></i>
                                </span>
                                <textarea class="form-control @error('notes') is-invalid @enderror" 
                                          id="notes" name="notes" rows="4" 
                                          placeholder="Notes sur la commande..."
                                          style="border-radius: 0 10px 10px 0; border: 2px solid #e9ecef; resize: none;">{{ old('notes', $purchase->notes) }}</textarea>
                            </div>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Produits commandés -->
                <div class="card border-0 shadow-lg" style="border-radius: 15px; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px);">
                    <div class="card-header border-0" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 15px 15px 0 0;">
                        <h5 class="card-title mb-0 fw-bold">
                            <i class="fas fa-boxes me-2" style="color: #336699;"></i>
                            Produits commandés
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead style="background-color: #f8f9fa;">
                                    <tr>
                                        <th class="border-0 fw-semibold" style="color: #336699;">Produit</th>
                                        <th class="border-0 fw-semibold text-center" style="color: #336699;">Qté commandée</th>
                                        <th class="border-0 fw-semibold text-center" style="color: #336699;">Qté reçue</th>
                                        <th class="border-0 fw-semibold text-end" style="color: #336699;">Prix unitaire</th>
                                        <th class="border-0 fw-semibold text-end" style="color: #336699;">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($purchase->purchaseItems as $item)
                                        <tr>
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
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center border-0">
                                                <span class="fw-medium">{{ $item->quantity_ordered }}</span>
                                            </td>
                                            <td class="text-center border-0">
                                                <span class="fw-medium {{ $item->isFullyReceived() ? 'text-success' : ($item->isPartiallyReceived() ? 'text-warning' : '') }}">
                                                    {{ $item->quantity_received }}
                                                </span>
                                            </td>
                                            <td class="text-end border-0">
                                                <span class="fw-medium">{{ number_format($item->unit_price, 2) }} €</span>
                                            </td>
                                            <td class="text-end border-0">
                                                <strong class="text-success">{{ number_format($item->total_price, 2) }} €</strong>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot style="background-color: #f8f9fa;">
                                    <tr>
                                        <th colspan="4" class="text-end border-0" style="color: #336699;">Sous-total:</th>
                                        <th class="text-end border-0" style="color: #336699;">{{ number_format($purchase->subtotal, 2) }} €</th>
                                    </tr>
                                    <tr>
                                        <th colspan="4" class="text-end border-0" style="color: #336699;">TVA (20%):</th>
                                        <th class="text-end border-0" style="color: #336699;">{{ number_format($purchase->tax_amount, 2) }} €</th>
                                    </tr>
                                    <tr style="background: linear-gradient(135deg, #336699 0%, #4a90e2 100%); color: white;">
                                        <th colspan="4" class="text-end border-0">Total:</th>
                                        <th class="text-end border-0">{{ number_format($purchase->total_amount, 2) }} €</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Note d'information -->
                <div class="alert border-0 mt-4" style="border-radius: 12px; background: linear-gradient(135deg, #17a2b8 0%, #6f42c1 100%); color: white;">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Note:</strong> Seules les informations générales peuvent être modifiées. 
                    Les produits commandés ne peuvent pas être modifiés après la création de la commande.
                </div>
            </div>
            
            <div class="col-md-4">
                <!-- Informations fournisseur -->
                <div class="card mb-4 border-0 shadow-lg" style="border-radius: 15px; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px);">
                    <div class="card-header border-0" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 15px 15px 0 0;">
                        <h5 class="card-title mb-0 fw-bold">
                            <i class="fas fa-building me-2" style="color: #336699;"></i>
                            Informations fournisseur
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="me-3" style="width: 40px; height: 40px; background: linear-gradient(180deg, #336699 0%, #4a90e2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-building text-white"></i>
                            </div>
                            <div>
                                <strong style="color: #336699;">{{ $purchase->supplier->name }}</strong>
                            </div>
                        </div>
                        
                        @if($purchase->supplier->contact_person)
                            <div class="mb-2">
                                <i class="fas fa-user me-2" style="color: #336699; width: 20px;"></i>
                                <strong style="color: #336699;">Contact:</strong> <span class="fw-medium">{{ $purchase->supplier->contact_person }}</span>
                            </div>
                        @endif
                        @if($purchase->supplier->phone_number)
                            <div class="mb-2">
                                <i class="fas fa-phone me-2" style="color: #336699; width: 20px;"></i>
                                <strong style="color: #336699;">Téléphone:</strong> <span class="fw-medium">{{ $purchase->supplier->phone_number }}</span>
                            </div>
                        @endif
                        @if($purchase->supplier->email)
                            <div class="mb-2">
                                <i class="fas fa-envelope me-2" style="color: #336699; width: 20px;"></i>
                                <strong style="color: #336699;">Email:</strong> <span class="fw-medium">{{ $purchase->supplier->email }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Résumé financier -->
                <div class="card mb-4 border-0 shadow-lg" style="border-radius: 15px; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px);">
                    <div class="card-header border-0" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 15px 15px 0 0;">
                        <h5 class="card-title mb-0 fw-bold">
                            <i class="fas fa-calculator me-2" style="color: #336699;"></i>
                            Résumé financier
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush" style="border-radius: 10px;">
                            <div class="list-group-item d-flex justify-content-between border-0 px-0" style="background: transparent;">
                                <span style="color: #336699;">Sous-total:</span>
                                <span class="fw-medium">{{ number_format($purchase->subtotal, 2) }} €</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between border-0 px-0" style="background: transparent;">
                                <span style="color: #336699;">TVA:</span>
                                <span class="fw-medium">{{ number_format($purchase->tax_amount, 2) }} €</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between border-0 px-3 py-3" style="background: linear-gradient(135deg, #336699 0%, #4a90e2 100%); color: white; border-radius: 10px;">
                                <strong>Total:</strong>
                                <strong>{{ number_format($purchase->total_amount, 2) }} €</strong>
                            </div>
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
                            <button type="submit" class="btn text-white fw-semibold" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border: none; border-radius: 10px; padding: 12px;">
                                <i class="fas fa-save me-1"></i> Mettre à jour
                            </button>
                            <a href="{{ route('purchases.show', $purchase->id) }}" class="btn fw-semibold" style="background: transparent; border: 2px solid #6c757d; color: #6c757d; border-radius: 10px; padding: 12px;">
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
    
    .form-control:focus,
    .form-select:focus {
        border-color: #336699;
        box-shadow: 0 0 0 0.2rem rgba(51, 102, 153, 0.25);
    }
    
    .input-group-text {
        transition: all 0.3s ease;
    }
    
    .form-control:focus + .input-group-text,
    .form-select:focus + .input-group-text {
        border-color: #336699;
    }
    
    .card {
        transition: transform 0.3s ease;
    }
    
    .list-group-item {
        transition: all 0.2s ease;
    }
    
    .list-group-item:hover {
        background-color: rgba(51, 102, 153, 0.02) !important;
    }
    
    /* Harmonisation avec le sidebar */
    .text-primary {
        color: #336699 !important;
    }
    
    .bg-primary {
        background: linear-gradient(180deg, #336699 0%, #4a90e2 100%) !important;
    }
    
    textarea {
        resize: none;
    }
    
    .invalid-feedback {
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }
</style>
@endsection