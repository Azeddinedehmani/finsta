@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-cog me-2"></i>Paramètres de Notification
                </h5>
            </div>
            
            <form method="POST" action="{{ route('notifications.settings.update') }}">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-3">Méthodes de notification</h6>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" 
                                       name="email_notifications" id="email_notifications"
                                       {{ (auth()->user()->permissions['notifications']['email_notifications'] ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="email_notifications">
                                    <i class="fas fa-envelope me-2"></i>Notifications par email
                                </label>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" 
                                       name="browser_notifications" id="browser_notifications"
                                       {{ (auth()->user()->permissions['notifications']['browser_notifications'] ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="browser_notifications">
                                    <i class="fas fa-globe me-2"></i>Notifications navigateur
                                </label>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-3">Types de notifications</h6>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" 
                                       name="stock_alerts" id="stock_alerts"
                                       {{ (auth()->user()->permissions['notifications']['stock_alerts'] ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="stock_alerts">
                                    <i class="fas fa-box me-2"></i>Alertes de stock
                                </label>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" 
                                       name="expiry_alerts" id="expiry_alerts"
                                       {{ (auth()->user()->permissions['notifications']['expiry_alerts'] ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="expiry_alerts">
                                    <i class="fas fa-clock me-2"></i>Alertes d'expiration
                                </label>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" 
                                       name="sale_notifications" id="sale_notifications"
                                       {{ (auth()->user()->permissions['notifications']['sale_notifications'] ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="sale_notifications">
                                    <i class="fas fa-shopping-cart me-2"></i>Notifications de vente
                                </label>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" 
                                       name="prescription_notifications" id="prescription_notifications"
                                       {{ (auth()->user()->permissions['notifications']['prescription_notifications'] ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="prescription_notifications">
                                    <i class="fas fa-file-prescription me-2"></i>Notifications d'ordonnance
                                </label>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" 
                                       name="purchase_notifications" id="purchase_notifications"
                                       {{ (auth()->user()->permissions['notifications']['purchase_notifications'] ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="purchase_notifications">
                                    <i class="fas fa-truck me-2"></i>Notifications d'achat
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Enregistrer les paramètres
                    </button>
                    <a href="{{ route('notifications.index') }}" class="btn btn-secondary ms-2">
                        <i class="fas fa-arrow-left me-1"></i>Retour
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection