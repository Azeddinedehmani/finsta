@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <h2>Modifier l'ordonnance {{ $prescription->prescription_number }}</h2>
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
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($prescription->prescriptionItems->where('product', null)->count() > 0)
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>Attention:</strong> Cette ordonnance contient des médicaments qui ne sont plus disponibles dans le système. 
        Veuillez contacter l'administrateur pour résoudre ce problème.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<form action="{{ route('prescriptions.update', $prescription->id) }}" method="POST">
    @csrf
    @method('PUT')
    
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Informations générales</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Client</label>
                            <input type="text" class="form-control" value="{{ $prescription->client->full_name }}" readonly>
                            <small class="text-muted">Le client ne peut pas être modifié</small>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="doctor_name" class="form-label">Nom du médecin <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('doctor_name') is-invalid @enderror" 
                                   id="doctor_name" name="doctor_name" value="{{ old('doctor_name', $prescription->doctor_name) }}" required>
                            @error('doctor_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="doctor_phone" class="form-label">Téléphone du médecin</label>
                            <input type="tel" class="form-control @error('doctor_phone') is-invalid @enderror" 
                                   id="doctor_phone" name="doctor_phone" value="{{ old('doctor_phone', $prescription->doctor_phone) }}">
                            @error('doctor_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label for="doctor_speciality" class="form-label">Spécialité</label>
                            <input type="text" class="form-control @error('doctor_speciality') is-invalid @enderror" 
                                   id="doctor_speciality" name="doctor_speciality" value="{{ old('doctor_speciality', $prescription->doctor_speciality) }}">
                            @error('doctor_speciality')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="prescription_date" class="form-label">Date de prescription <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('prescription_date') is-invalid @enderror" 
                                   id="prescription_date" name="prescription_date" 
                                   value="{{ old('prescription_date', $prescription->prescription_date->format('Y-m-d')) }}" required>
                            @error('prescription_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label for="expiry_date" class="form-label">Date d'expiration <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('expiry_date') is-invalid @enderror" 
                                   id="expiry_date" name="expiry_date" 
                                   value="{{ old('expiry_date', $prescription->expiry_date->format('Y-m-d')) }}" required>
                            @error('expiry_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="medical_notes" class="form-label">Notes médicales</label>
                        <textarea class="form-control @error('medical_notes') is-invalid @enderror" 
                                  id="medical_notes" name="medical_notes" rows="3" 
                                  placeholder="Notes du médecin, recommandations spéciales...">{{ old('medical_notes', $prescription->medical_notes) }}</textarea>
                        @error('medical_notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="pharmacist_notes" class="form-label">Notes du pharmacien</label>
                        <textarea class="form-control @error('pharmacist_notes') is-invalid @enderror" 
                                  id="pharmacist_notes" name="pharmacist_notes" rows="3" 
                                  placeholder="Notes du pharmacien, observations...">{{ old('pharmacist_notes', $prescription->pharmacist_notes) }}</textarea>
                        @error('pharmacist_notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

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
                                    <th>Instructions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($prescription->prescriptionItems as $item)
                                    <tr class="{{ $item->product ? '' : 'table-warning' }}">
                                        <td>
                                            @if($item->product)
                                                <strong>{{ $item->product->name }}</strong>
                                                @if($item->product->dosage)
                                                    <br><small class="text-muted">{{ $item->product->dosage }}</small>
                                                @endif
                                            @else
                                                <strong class="text-warning">
                                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                                    Produit non disponible (ID: {{ $item->product_id }})
                                                </strong>
                                                <br><small class="text-muted">Ce produit a été supprimé du système</small>
                                            @endif
                                        </td>
                                        <td class="text-center">{{ $item->quantity_prescribed }}</td>
                                        <td class="text-center">
                                            @if($item->product)
                                                <span class="{{ $item->isFullyDelivered() ? 'text-success' : ($item->isPartiallyDelivered() ? 'text-warning' : '') }}">
                                                    {{ $item->quantity_delivered }}
                                                </span>
                                            @else
                                                <span class="text-muted">{{ $item->quantity_delivered }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $item->dosage_instructions }}</td>
                                        <td>{{ $item->instructions }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="alert alert-info mt-3">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Note:</strong> Seules les informations générales et les notes peuvent être modifiées. 
                Les médicaments ne peuvent pas être modifiés après la création de l'ordonnance.
            </div>
        </div>
        
        <div class="col-md-4">
            @if($prescription->client->allergies)
                <div class="card mb-3">
                    <div class="card-header bg-danger text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>Allergies connues
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{{ $prescription->client->allergies }}</p>
                    </div>
                </div>
            @endif

            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Informations client</h5>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <strong>Nom:</strong> {{ $prescription->client->full_name }}
                    </div>
                    @if($prescription->client->phone)
                        <div class="mb-2">
                            <strong>Téléphone:</strong> {{ $prescription->client->phone }}
                        </div>
                    @endif
                    @if($prescription->client->date_of_birth)
                        <div class="mb-2">
                            <strong>Âge:</strong> {{ $prescription->client->age }} ans
                        </div>
                    @endif
                    @if($prescription->client->insurance_number)
                        <div class="mb-2">
                            <strong>Assurance:</strong> {{ $prescription->client->insurance_number }}
                        </div>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Mettre à jour
                        </button>
                        <a href="{{ route('prescriptions.show', $prescription->id) }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i> Annuler
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection