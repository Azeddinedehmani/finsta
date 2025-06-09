@extends('layouts.app')

@section('content')
<div class="min-vh-100" style="background: linear-gradient(135deg, #e3f2fd 0%, #e8f5e8 100%); margin: -20px; padding: 20px;">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="d-flex align-items-center">
                <div class="me-3" style="width: 50px; height: 50px; background: linear-gradient(180deg, #336699 0%, #4a90e2 100%); border-radius: 15px; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 25px rgba(51, 102, 153, 0.3);">
                    <i class="fas fa-file-invoice text-white fa-lg"></i>
                </div>
                <div>
                    <h2 class="mb-0 fw-bold" style="font-family: 'Poppins', sans-serif; color: #2c3e50;">Commande {{ $purchase->purchase_number }}</h2>
                    <small class="text-muted">Détails de la commande d'achat</small>
                </div>
            </div>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('purchases.index') }}" class="btn text-white fw-semibold me-2" style="background: linear-gradient(135deg, #6c757d 0%, #495057 100%); border: none; border-radius: 12px; padding: 12px 20px; box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3); transition: all 0.3s ease;">
                <i class="fas fa-arrow-left me-1"></i> Retour aux achats
            </a>
            <a href="{{ route('purchases.print', $purchase->id) }}" class="btn text-white fw-semibold" style="background: linear-gradient(180deg, #336699 0%, #4a90e2 100%); border: none; border-radius: 12px; padding: 12px 20px; box-shadow: 0 4px 15px rgba(51, 102, 153, 0.3); transition: all 0.3s ease;" target="_blank">
                <i class="fas fa-print me-1"></i> Imprimer
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

    <div class="row">
        <div class="col-md-8">
            <!-- Détails de la commande -->
            <div class="card mb-4 border-0 shadow-lg" style="border-radius: 15px; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px);">
                <div class="card-header border-0 d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 15px 15px 0 0;">
                    <h5 class="card-title mb-0 fw-bold">
                        <i class="fas fa-info-circle me-2" style="color: #336699;"></i>
                        Détails de la commande
                    </h5>
                    @php
                        $statusClass = match($purchase->status) {
                            'pending' => 'bg-warning text-dark',
                            'partially_received' => 'bg-info',
                            'received' => 'bg-success',
                            'cancelled' => 'bg-secondary',
                            default => 'bg-secondary'
                        };
                    @endphp
                    <span class="badge {{ $statusClass }} rounded-pill px-3 py-2" style="font-size: 0.8rem;">
                        {{ $purchase->status_label }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="list-group list-group-flush" style="border-radius: 10px;">
                                <div class="list-group-item d-flex justify-content-between border-0 px-0" style="background: transparent;">
                                    <strong style="color: #336699;"><i class="fas fa-hashtag me-2"></i>Numéro de commande:</strong>
                                    <span class="fw-medium">{{ $purchase->purchase_number }}</span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between border-0 px-0" style="background: transparent;">
                                    <strong style="color: #336699;"><i class="fas fa-calendar me-2"></i>Date de commande:</strong>
                                    <span class="fw-medium">{{ $purchase->order_date->format('d/m/Y') }}</span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between border-0 px-0" style="background: transparent;">
                                    <strong style="color: #336699;"><i class="fas fa-clock me-2"></i>Date prévue:</strong>
                                    <span class="fw-medium">
                                        @if($purchase->expected_date)
                                            {{ $purchase->expected_date->format('d/m/Y') }}
                                            @if($purchase->expected_date->isPast() && $purchase->status === 'pending')
                                                <span class="badge bg-danger ms-2">En retard</span>
                                            @endif
                                        @else
                                            <span class="text-muted fst-italic">Non définie</span>
                                        @endif
                                    </span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between border-0 px-0" style="background: transparent;">
                                    <strong style="color: #336699;"><i class="fas fa-user me-2"></i>Créée par:</strong>
                                    <span class="fw-medium">{{ $purchase->user->name }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="list-group list-group-flush" style="border-radius: 10px;">
                                <div class="list-group-item d-flex justify-content-between border-0 px-0" style="background: transparent;">
                                    <strong style="color: #336699;"><i class="fas fa-truck me-2"></i>Fournisseur:</strong>
                                    <span class="fw-medium">
                                        <a href="{{ route('suppliers.show', $purchase->supplier->id) }}" style="color: #336699; text-decoration: none;">
                                            {{ $purchase->supplier->name }}
                                        </a>
                                    </span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between border-0 px-0" style="background: transparent;">
                                    <strong style="color: #336699;"><i class="fas fa-user-tie me-2"></i>Contact:</strong>
                                    <span class="fw-medium">{{ $purchase->supplier->contact_person ?? 'N/A' }}</span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between border-0 px-0" style="background: transparent;">
                                    <strong style="color: #336699;"><i class="fas fa-phone me-2"></i>Téléphone:</strong>
                                    <span class="fw-medium">{{ $purchase->supplier->phone_number ?? 'N/A' }}</span>
                                </div>
                                @if($purchase->received_at)
                                    <div class="list-group-item d-flex justify-content-between border-0 px-0" style="background: transparent;">
                                        <strong style="color: #336699;"><i class="fas fa-check-circle me-2"></i>Reçue le:</strong>
                                        <span class="fw-medium">{{ $purchase->received_date->format('d/m/Y') }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($purchase->notes)
                        <div class="alert border-0" style="border-radius: 12px; background: linear-gradient(135deg, #17a2b8 0%, #6f42c1 100%); color: white;">
                            <strong><i class="fas fa-sticky-note me-2"></i>Notes:</strong>
                            <p class="mb-0 mt-2">{{ $purchase->notes }}</p>
                        </div>
                    @endif
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
                                    <th class="border-0 fw-semibold" style="color: #336699;">Progression</th>
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
                                                    <br><small class="badge bg-info">Stock actuel: {{ $item->product->stock_quantity }}</small>
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
                                        <td class="border-0">
                                            <div class="progress mb-1" style="height: 20px; border-radius: 10px;">
                                                <div class="progress-bar {{ $item->isFullyReceived() ? 'bg-success' : 'bg-info' }}" 
                                                     role="progressbar" 
                                                     style="width: {{ $item->progress_percentage }}%; border-radius: 10px;"
                                                     aria-valuenow="{{ $item->progress_percentage }}" 
                                                     aria-valuemin="0" 
                                                     aria-valuemax="100">
                                                    {{ $item->progress_percentage }}%
                                                </div>
                                            </div>
                                            @if($item->remaining_quantity > 0)
                                                <small class="text-muted">Reste: {{ $item->remaining_quantity }}</small>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot style="background-color: #f8f9fa;">
                                <tr>
                                    <th colspan="4" class="text-end border-0" style="color: #336699;">Sous-total:</th>
                                    <th class="text-end border-0" style="color: #336699;">{{ number_format($purchase->subtotal, 2) }} €</th>
                                    <th class="border-0"></th>
                                </tr>
                                <tr>
                                    <th colspan="4" class="text-end border-0" style="color: #336699;">TVA (20%):</th>
                                    <th class="text-end border-0" style="color: #336699;">{{ number_format($purchase->tax_amount, 2) }} €</th>
                                    <th class="border-0"></th>
                                </tr>
                                <tr style="background: linear-gradient(135deg, #336699 0%, #4a90e2 100%); color: white;">
                                    <th colspan="4" class="text-end border-0">Total:</th>
                                    <th class="text-end border-0">{{ number_format($purchase->total_amount, 2) }} €</th>
                                    <th class="border-0"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <!-- Résumé financier -->
            <div class="card mb-4 border-0 shadow-lg" style="border-radius: 15px; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px);">
                <div class="card-header border-0" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 15px 15px 0 0;">
                    <h5 class="card-title mb-0 fw-bold">
                        <i class="fas fa-euro-sign me-2" style="color: #336699;"></i>
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

            <!-- Progression -->
            <div class="card mb-4 border-0 shadow-lg" style="border-radius: 15px; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px);">
                <div class="card-header border-0" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 15px 15px 0 0;">
                    <h5 class="card-title mb-0 fw-bold">
                        <i class="fas fa-chart-pie me-2" style="color: #336699;"></i>
                        Progression
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span style="color: #336699; font-weight: 500;">Réception:</span>
                            <span class="fw-medium">{{ $purchase->received_items }}/{{ $purchase->total_items }}</span>
                        </div>
                        <div class="progress mb-2" style="height: 20px; border-radius: 10px;">
                            <div class="progress-bar {{ $purchase->status === 'received' ? 'bg-success' : 'bg-info' }}" 
                                 role="progressbar" 
                                 style="width: {{ $purchase->progress_percentage }}%; border-radius: 10px;">
                                {{ $purchase->progress_percentage }}%
                            </div>
                        </div>
                        
                        @if($purchase->status !== 'received' && $purchase->status !== 'cancelled')
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Encore {{ $purchase->total_items - $purchase->received_items }} produit(s) à recevoir
                            </small>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="card mb-4 border-0 shadow-lg" style="border-radius: 15px; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px);">
                <div class="card-header border-0" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 15px 15px 0 0;">
                    <h5 class="card-title mb-0 fw-bold">
                        <i class="fas fa-cogs me-2" style="color: #336699;"></i>
                        Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('purchases.print', $purchase->id) }}" class="btn text-white fw-semibold" style="background: linear-gradient(180deg, #336699 0%, #4a90e2 100%); border: none; border-radius: 10px;" target="_blank">
                            <i class="fas fa-print me-1"></i> Imprimer la commande
                        </a>
                        
                        @if($purchase->status !== 'received' && $purchase->status !== 'cancelled')
                            <a href="{{ route('purchases.edit', $purchase->id) }}" class="btn text-white fw-semibold" style="background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%); border: none; border-radius: 10px; color: #212529 !important;">
                                <i class="fas fa-edit me-1"></i> Modifier
                            </a>
                            
                            <a href="{{ route('purchases.receive', $purchase->id) }}" class="btn text-white fw-semibold" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border: none; border-radius: 10px;">
                                <i class="fas fa-truck me-1"></i> Recevoir la livraison
                            </a>
                            
                            <form action="{{ route('purchases.cancel', $purchase->id) }}" method="POST" 
                                  onsubmit="return confirm('Êtes-vous sûr de vouloir annuler cette commande ?')">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn text-white fw-semibold w-100" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); border: none; border-radius: 10px;">
                                    <i class="fas fa-times me-1"></i> Annuler la commande
                                </button>
                            </form>
                        @endif
                        
                        <a href="{{ route('suppliers.show', $purchase->supplier->id) }}" class="btn fw-semibold" style="background: transparent; border: 2px solid #336699; color: #336699; border-radius: 10px;">
                            <i class="fas fa-truck me-1"></i> Voir le fournisseur
                        </a>
                        
                        <a href="{{ route('purchases.create', ['supplier_id' => $purchase->supplier->id]) }}" class="btn fw-semibold" style="background: transparent; border: 2px solid #28a745; color: #28a745; border-radius: 10px;">
                            <i class="fas fa-plus me-1"></i> Nouvelle commande
                        </a>
                    </div>
                </div>
            </div>

            <!-- Informations fournisseur -->
            <div class="card border-0 shadow-lg" style="border-radius: 15px; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px);">
                <div class="card-header border-0" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 15px 15px 0 0;">
                    <h5 class="card-title mb-0 fw-bold">
                        <i class="fas fa-building me-2" style="color: #336699;"></i>
                        Informations fournisseur
                    </h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush" style="border-radius: 10px;">
                        <div class="list-group-item border-0 px-0" style="background: transparent;">
                            <strong style="color: #336699;">{{ $purchase->supplier->name }}</strong>
                        </div>
                        @if($purchase->supplier->contact_person)
                            <div class="list-group-item border-0 px-0" style="background: transparent;">
                                <i class="fas fa-user me-2" style="color: #336699;"></i>{{ $purchase->supplier->contact_person }}
                            </div>
                        @endif
                        @if($purchase->supplier->phone_number)
                            <div class="list-group-item border-0 px-0" style="background: transparent;">
                                <i class="fas fa-phone me-2" style="color: #336699;"></i>{{ $purchase->supplier->phone_number }}
                            </div>
                        @endif
                        @if($purchase->supplier->email)
                            <div class="list-group-item border-0 px-0" style="background: transparent;">
                                <i class="fas fa-envelope me-2" style="color: #336699;"></i>{{ $purchase->supplier->email }}
                            </div>
                        @endif
                    </div>
                    
                    @if($purchase->supplier->phone_number || $purchase->supplier->email)
                        <div class="mt-3">
                            @if($purchase->supplier->phone_number)
                                <a href="tel:{{ $purchase->supplier->phone_number }}" class="btn btn-sm me-2 fw-semibold" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; border: none; border-radius: 8px;">
                                    <i class="fas fa-phone"></i> Appeler
                                </a>
                            @endif
                            @if($purchase->supplier->email)
                                <a href="mailto:{{ $purchase->supplier->email }}" class="btn btn-sm fw-semibold" style="background: linear-gradient(180deg, #336699 0%, #4a90e2 100%); color: white; border: none; border-radius: 8px;">
                                    <i class="fas fa-envelope"></i> Email
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
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
    
    .progress {
        background-color: rgba(51, 102, 153, 0.1);
    }
    
    a {
        transition: all 0.3s ease;
    }
    
    a:hover {
        text-decoration: none;
    }
</style>
@endsection