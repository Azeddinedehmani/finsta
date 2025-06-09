@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <h2><i class="fas fa-sliders-h me-2"></i>Paramètres système</h2>
        <p class="text-muted">Configuration et paramètres de l'application</p>
    </div>
    <div class="col-md-4 text-end">
        <div class="btn-group">
            <a href="{{ route('admin.administration') }}" class="btn btn-secondary">
                <i class="fas fa-cogs me-1"></i> Administration
            </a>
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-users me-1"></i> Utilisateurs
            </a>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@php
function getGroupIcon($group) {
    return match($group) {
        'app' => 'fa-mobile-alt',
        'pharmacy' => 'fa-clinic-medical',
        'tax' => 'fa-calculator',
        'stock' => 'fa-boxes',
        'security' => 'fa-shield-alt',
        'backup' => 'fa-database',
        'prescription' => 'fa-file-prescription',
        default => 'fa-cog'
    };
}

function getGroupName($group) {
    return match($group) {
        'app' => 'Application',
        'pharmacy' => 'Pharmacie',
        'tax' => 'Fiscalité',
        'stock' => 'Stock et inventaire',
        'security' => 'Sécurité',
        'backup' => 'Sauvegarde',
        'prescription' => 'Ordonnances',
        default => ucfirst($group)
    };
}
@endphp

<form action="{{ route('admin.settings.update') }}" method="POST">
    @csrf
    
    @forelse($settings as $group => $groupSettings)
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">
                    <i class="fas {{ getGroupIcon($group) }} me-2"></i>
                    {{ getGroupName($group) }}
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($groupSettings as $setting)
                        <div class="col-md-6 mb-3">
                            <label for="setting_{{ $setting->key }}" class="form-label">
                                {{ $setting->description ?: $setting->key }}
                            </label>
                            
                            @if($setting->type === 'boolean')
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" 
                                           id="setting_{{ $setting->key }}" 
                                           name="settings[{{ $setting->key }}]" 
                                           value="1"
                                           {{ $setting->typed_value ? 'checked' : '' }}>
                                    <label class="form-check-label" for="setting_{{ $setting->key }}">
                                        {{ $setting->typed_value ? 'Activé' : 'Désactivé' }}
                                    </label>
                                </div>
                            @elseif($setting->type === 'integer')
                                <input type="number" class="form-control" 
                                       id="setting_{{ $setting->key }}" 
                                       name="settings[{{ $setting->key }}]" 
                                       value="{{ $setting->typed_value }}">
                            @elseif($setting->type === 'float')
                                <input type="number" step="0.01" class="form-control" 
                                       id="setting_{{ $setting->key }}" 
                                       name="settings[{{ $setting->key }}]" 
                                       value="{{ $setting->typed_value }}">
                            @elseif($setting->key === 'backup_frequency')
                                <select class="form-select" id="setting_{{ $setting->key }}" 
                                        name="settings[{{ $setting->key }}]">
                                    <option value="daily" {{ $setting->typed_value === 'daily' ? 'selected' : '' }}>Quotidienne</option>
                                    <option value="weekly" {{ $setting->typed_value === 'weekly' ? 'selected' : '' }}>Hebdomadaire</option>
                                    <option value="monthly" {{ $setting->typed_value === 'monthly' ? 'selected' : '' }}>Mensuelle</option>
                                </select>
                            @else
                                <input type="text" class="form-control" 
                                       id="setting_{{ $setting->key }}" 
                                       name="settings[{{ $setting->key }}]" 
                                       value="{{ $setting->typed_value }}">
                            @endif
                            
                            @if($setting->description)
                                <div class="form-text">{{ $setting->description }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @empty
        <div class="card">
            <div class="card-body text-center">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Aucun paramètre trouvé.</strong> 
                    Les paramètres par défaut doivent être initialisés.
                </div>
                <p class="text-muted">
                    Veuillez exécuter la commande suivante pour initialiser les paramètres système :
                </p>
                <code>php artisan db:seed --class=SystemSettingsSeeder</code>
            </div>
        </div>
    @endforelse

    @if(!$settings->isEmpty())
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">Sauvegarder les paramètres</h6>
                        <small class="text-muted">Les modifications seront appliquées immédiatement</small>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Enregistrer tous les paramètres
                    </button>
                </div>
            </div>
        </div>
    @endif
</form>

@section('scripts')
<script>
    // Real-time form validation feedback
    document.querySelectorAll('input[type="number"]').forEach(input => {
        input.addEventListener('input', function() {
            if (this.value < 0) {
                this.setCustomValidity('La valeur doit être positive');
            } else {
                this.setCustomValidity('');
            }
        });
    });

    // Confirmation for sensitive settings
    document.querySelectorAll('input[name*="password"], input[name*="security"]').forEach(input => {
        input.addEventListener('change', function() {
            if (this.type === 'checkbox' || this.value !== this.defaultValue) {
                // Show confirmation for security-related changes
                console.log('Security setting changed:', this.name);
            }
        });
    });
</script>
@endsection

@endsection