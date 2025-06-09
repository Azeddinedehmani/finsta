@extends('layouts.app')

@section('content')
<div class="min-vh-100 d-flex align-items-center" style="background: linear-gradient(135deg,rgb(214, 221, 253) 0%,rgb(195, 214, 218) 100%);">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                <div class="card border-0 shadow-xl" style="border-radius: 20px; backdrop-filter: blur(10px);">
                    <!-- Logo Section -->
                    <div class="text-center pt-5 pb-3">
                        <div class="logo-container mb-4">
                            <div class="logo-circle mx-auto mb-3" style="width: 120px; height: 120px; background: linear-gradient(45deg, #ffffff 0%, #f8f9fa 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 10px 30px rgba(79, 172, 254, 0.3); border: 3px solid #4facfe;">
                                <!-- Votre Logo -->
                                <img src="{{ asset('images/logo.png') }}" alt="PHARMACIA Logo" class="img-fluid" style="max-width: 90px; max-height: 90px;" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                
                                <!-- Icône de secours si le logo ne charge pas -->
                                <i class="fas fa-pills fa-4x" style="display: none; color: #4facfe;"></i>
                            </div>
                        </div>
                        <h2 class="fw-bold text-dark mb-1" style="font-size: 2.2rem; letter-spacing: 2px;">PHARMACIA</h2>
                        <div class="divider mx-auto mb-4" style="width: 60px; height: 3px; background: linear-gradient(45deg, #4facfe 0%, #00f2fe 100%); border-radius: 2px;"></div>
                    </div>

                    <div class="card-body px-5 pb-5">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show border-0" role="alert" style="border-radius: 15px; background: linear-gradient(45deg, #56ab2f 0%, #a8e6cf 100%);">
                                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <!-- Titre et description -->
                        <div class="text-center mb-4">
                            <div class="icon-container mb-3">
                                <i class="fas fa-key fa-3x" style="color: #4facfe;"></i>
                            </div>
                            <h4 class="fw-bold text-dark mb-3">Nouveau mot de passe</h4>
                            <p class="text-muted mb-3" style="font-size: 0.95rem;">
                                Saisissez le code reçu par email et votre nouveau mot de passe
                            </p>
                            
                            <!-- Email info badge -->
                            <div class="alert alert-info border-0 mb-4" style="border-radius: 15px; background: linear-gradient(45deg, #e3f2fd 0%, #bbdefb 100%);">
                                <i class="fas fa-info-circle me-2" style="color: #1976d2;"></i>
                                <strong style="color: #1976d2;">Code envoyé à : {{ $email }}</strong>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('password.reset') }}" class="needs-validation" novalidate>
                            @csrf
                            <input type="hidden" name="email" value="{{ $email }}">

                            <!-- Code de vérification -->
                            <div class="mb-4">
                                <label for="code" class="form-label fw-semibold text-dark mb-2">
                                    <i class="fas fa-shield-alt me-2" style="color: #4facfe;"></i>Code de vérification
                                </label>
                                <div class="position-relative">
                                    <input id="code" type="text" 
                                           class="form-control border-0 text-center @error('code') is-invalid @enderror" 
                                           name="code" 
                                           value="{{ old('code') }}" 
                                           required 
                                           maxlength="6"
                                           placeholder="000000"
                                           style="border-radius: 15px; background: linear-gradient(45deg, #f8f9fa 0%, #e9ecef 100%); padding: 20px; font-size: 1.4rem; letter-spacing: 4px; font-weight: bold;">
                                    
                                    @error('code')
                                        <div class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </div>
                                    @enderror
                                </div>
                                <div class="form-text text-center mt-2" style="color: #6c757d;">
                                    <i class="fas fa-clock me-1"></i>
                                    Le code est valide pendant 10 minutes
                                </div>
                            </div>

                            <!-- Nouveau mot de passe -->
                            <div class="mb-4">
                                <label for="password" class="form-label fw-semibold text-dark mb-2">
                                    <i class="fas fa-lock me-2" style="color: #4facfe;"></i>Nouveau mot de passe
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text border-0" style="background: linear-gradient(45deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 15px 0 0 15px;">
                                        <i class="fas fa-key text-muted"></i>
                                    </span>
                                    <input id="password" type="password" 
                                           class="form-control border-0 @error('password') is-invalid @enderror" 
                                           name="password" 
                                           required 
                                           autocomplete="new-password"
                                           placeholder="Nouveau mot de passe"
                                           style="border-radius: 0 15px 15px 0; background: linear-gradient(45deg, #f8f9fa 0%, #e9ecef 100%); padding: 15px;">
                                    
                                    @error('password')
                                        <div class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Confirmer mot de passe -->
                            <div class="mb-4">
                                <label for="password-confirm" class="form-label fw-semibold text-dark mb-2">
                                    <i class="fas fa-check-double me-2" style="color: #4facfe;"></i>Confirmer le mot de passe
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text border-0" style="background: linear-gradient(45deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 15px 0 0 15px;">
                                        <i class="fas fa-check text-muted"></i>
                                    </span>
                                    <input id="password-confirm" type="password" 
                                           class="form-control border-0" 
                                           name="password_confirmation" 
                                           required 
                                           autocomplete="new-password"
                                           placeholder="Confirmer le mot de passe"
                                           style="border-radius: 0 15px 15px 0; background: linear-gradient(45deg, #f8f9fa 0%, #e9ecef 100%); padding: 15px;">
                                </div>
                            </div>

                            <!-- Reset Button -->
                            <div class="d-grid mb-4">
                                <button type="submit" class="btn btn-lg text-white fw-bold" style="background: linear-gradient(45deg, #28a745 0%, #20c997 100%); border: none; border-radius: 15px; padding: 15px; box-shadow: 0 8px 25px rgba(40, 167, 69, 0.4); transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 12px 35px rgba(40, 167, 69, 0.5)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 8px 25px rgba(40, 167, 69, 0.4)'">
                                    <i class="fas fa-check me-2"></i>Réinitialiser le mot de passe
                                </button>
                            </div>
                        </form>

                        <!-- Actions -->
                        <div class="text-center">
                            <div class="d-flex justify-content-center gap-4 flex-wrap">
                                <a href="{{ route('password.forgot') }}" class="text-decoration-none d-inline-flex align-items-center" style="color: #6c757d; font-size: 0.9rem; font-weight: 500; transition: all 0.3s ease;" onmouseover="this.style.color='#4facfe'" onmouseout="this.style.color='#6c757d'">
                                    <i class="fas fa-redo me-2"></i>
                                    Renvoyer le code
                                </a>
                                <a href="{{ route('login') }}" class="text-decoration-none d-inline-flex align-items-center" style="color: #6c757d; font-size: 0.9rem; font-weight: 500; transition: all 0.3s ease;" onmouseover="this.style.color='#4facfe'" onmouseout="this.style.color='#6c757d'">
                                    <i class="fas fa-arrow-left me-2"></i>
                                    Retour à la connexion
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="text-center mt-4">
                    <p class="text-white-50" style="font-size: 0.9rem;">
                        © {{ date('Y') }} PHARMACIA. Tous droits réservés.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
    
    body {
        font-family: 'Poppins', sans-serif;
    }
    
    .card {
        background: rgba(255, 255, 255, 0.95);
    }
    
    .form-control:focus {
        box-shadow: 0 0 0 0.2rem rgba(79, 172, 254, 0.25);
        border-color: #4facfe;
    }
    
    .logo-circle {
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0% {
            box-shadow: 0 10px 30px rgba(79, 172, 254, 0.3);
        }
        50% {
            box-shadow: 0 15px 40px rgba(79, 172, 254, 0.5);
        }
        100% {
            box-shadow: 0 10px 30px rgba(79, 172, 254, 0.3);
        }
    }
    
    .btn:hover {
        transform: translateY(-2px);
    }
    
    .input-group-text:first-child {
        border-right: 1px solid #dee2e6;
    }
    
    .icon-container {
        animation: float 3s ease-in-out infinite;
    }
    
    @keyframes float {
        0%, 100% {
            transform: translateY(0px);
        }
        50% {
            transform: translateY(-10px);
        }
    }
    
    /* Animation du champ code */
    #code {
        transition: all 0.3s ease;
    }
    
    #code:focus {
        transform: scale(1.02);
        box-shadow: 0 0 0 0.2rem rgba(79, 172, 254, 0.25);
    }
</style>

<script>
// Auto-format du code à 6 chiffres avec animations
document.getElementById('code').addEventListener('input', function(e) {
    // Supprimer tous les caractères non numériques
    let value = e.target.value.replace(/\D/g, '');
    
    // Limiter à 6 chiffres
    if (value.length > 6) {
        value = value.substring(0, 6);
    }
    
    e.target.value = value;
    
    // Animation visuelle quand le code est complet
    if (value.length === 6) {
        e.target.style.background = 'linear-gradient(45deg, #d4edda 0%, #c3e6cb 100%)';
        e.target.style.borderColor = '#28a745';
        setTimeout(() => {
            e.target.style.background = 'linear-gradient(45deg, #f8f9fa 0%, #e9ecef 100%)';
            e.target.style.borderColor = '#4facfe';
        }, 1000);
    }
});

// Animation focus sur les champs
document.querySelectorAll('.form-control').forEach(input => {
    input.addEventListener('focus', function() {
        this.style.transform = 'scale(1.01)';
    });
    
    input.addEventListener('blur', function() {
        this.style.transform = 'scale(1)';
    });
});
</script>
@endsection