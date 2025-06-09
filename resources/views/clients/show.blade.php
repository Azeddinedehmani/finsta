@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <h2>{{ $client->full_name }}</h2>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('clients.index') }}" class="btn btn-secondary me-2">
            <i class="fas fa-arrow-left me-1"></i> Retour
        </a>
        <a href="{{ route('clients.edit', $client->id) }}" class="btn btn-primary">
            <i class="fas fa-edit me-1"></i> Modifier
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Informations personnelles</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Nom complet:</strong>
                                <span>{{ $client->full_name }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Email:</strong>
                                <span>{{ $client->email ?? 'N/A' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Téléphone:</strong>
                                <span>{{ $client->phone ?? 'N/A' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Date de naissance:</strong>
                                <span>{{ $client->date_of_birth ? $client->date_of_birth->format('d/m/Y') : 'N/A' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Âge:</strong>
                                <span>{{ $client->age ? $client->age . ' ans' : 'N/A' }}</span>
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Ville:</strong>
                                <span>{{ $client->city ?? 'N/A' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Code postal:</strong>
                                <span>{{ $client->postal_code ?? 'N/A' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Assurance:</strong>
                                <span>{{ $client->insurance_number ?? 'N/A' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Statut:</strong>
                                <span>
                                    @if($client->active)
                                        <span class="badge bg-success">Actif</span>
                                    @else
                                        <span class="badge bg-secondary">Inactif</span>
                                    @endif
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>
                
                @if($client->address)
                    <div class="mt-3">
                        <strong>Adresse:</strong>
                        <p class="mt-2 mb-0">{{ $client->address }}</p>
                    </div>
                @endif
            </div>
        </div>

        @if($client->emergency_contact_name || $client->emergency_contact_phone)
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Contact d'urgence</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <strong>Nom:</strong>
                            <span>{{ $client->emergency_contact_name ?? 'N/A' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <strong>Téléphone:</strong>
                            <span>{{ $client->emergency_contact_phone ?? 'N/A' }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        @endif

        @if($client->allergies || $client->medical_notes)
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Informations médicales</h5>
                </div>
                <div class="card-body">
                    @if($client->allergies)
                        <div class="mb-3">
                            <strong class="text-danger">Allergies:</strong>
                            <div class="mt-2 p-2 bg-warning bg-opacity-10 border border-warning rounded">
                                {{ $client->allergies }}
                            </div>
                        </div>
                    @endif
                    
                    @if($client->medical_notes)
                        <div>
                            <strong>Notes médicales:</strong>
                            <p class="mt-2 mb-0">{{ $client->medical_notes }}</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
    
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Statistiques</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between">
                        <strong>Total dépensé:</strong>
                        <span class="text-success">{{ number_format($client->total_spent, 2) }} €</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <strong>Nombre de ventes:</strong>
                        <span>{{ $client->sales->count() }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <strong>Dernière visite:</strong>
                        <span>{{ $client->sales->first() ? $client->sales->first()->sale_date->format('d/m/Y') : 'Jamais' }}</span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Actions rapides</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('sales.create', ['client_id' => $client->id]) }}" class="btn btn-primary">
                        <i class="fas fa-cash-register me-1"></i> Nouvelle vente
                    </a>
                    <a href="{{ route('clients.edit', $client->id) }}" class="btn btn-outline-primary">
                        <i class="fas fa-edit me-1"></i> Modifier les informations
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@if($recentSales->count() > 0)
    <div class="card mt-4">
        <div class="card-header bg-light">
            <h5 class="card-title mb-0">Ventes récentes</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>N° Vente</th>
                            <th>Produits</th>
                            <th>Montant</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentSales as $sale)
                            <tr>
                                <td>{{ $sale->sale_number }}</td>
                                <td>
                                    <small>
                                        @foreach($sale->saleItems->take(2) as $item)
                                            {{ $item->product->name }}
                                            @if($item->quantity > 1) ({{ $item->quantity }}) @endif
                                            @if(!$loop->last), @endif
                                        @endforeach
                                        @if($sale->saleItems->count() > 2)
                                            <br><span class="text-muted">+{{ $sale->saleItems->count() - 2 }} autres</span>
                                        @endif
                                    </small>
                                </td>
                                <td>{{ number_format($sale->total_amount, 2) }} €</td>
                                <td>{{ $sale->sale_date->format('d/m/Y H:i') }}</td>
                                <td>
                                    <a href="{{ route('sales.show', $sale->id) }}" class="btn btn-sm btn-info text-white">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif
@endsection