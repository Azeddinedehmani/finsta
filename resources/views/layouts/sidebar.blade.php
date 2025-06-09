<?php
// resources/views/layouts/sidebar.blade.php - Updated version with pharmacist restrictions
?>
<div class="sidebar p-3" style="background: linear-gradient(180deg, #336699 0%, #4a90e2 100%); box-shadow: 4px 0 20px rgba(0, 0, 0, 0.1);">
    <!-- Logo and Brand Section -->
    <div class="text-center mb-4 pb-3 border-bottom border-light border-opacity-25">
        <div class="logo-container mb-3">
            <div class="logo-circle mx-auto mb-3" style="width: 80px; height: 80px; background: rgba(255, 255, 255, 0.15); border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2); border: 3px solid rgba(255, 255, 255, 0.3); backdrop-filter: blur(10px);">
                <img src="{{ asset('images/logo.png') }}" alt="PHARMACIA Logo" class="img-fluid" style="max-width: 50px; max-height: 50px;" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <i class="fas fa-pills fa-2x" style="display: none; color: white;"></i>
            </div>
        </div>
        <h3 class="my-0 text-white fw-bold" style="font-family: 'Poppins', sans-serif; letter-spacing: 2px; font-size: 1.5rem;">PHARMACIA</h3>
        <small class="text-white-50 d-block mt-1" style="font-family: 'Rubik', sans-serif; font-weight: 300;">Système de Gestion</small>
    </div>
    
    <!-- User Profile Section -->
    <div class="user-profile mb-4 pb-3 border-bottom border-light border-opacity-25">
        <div class="d-flex align-items-center p-3" style="background: rgba(255, 255, 255, 0.1); border-radius: 15px; backdrop-filter: blur(5px);">
            <div class="user-avatar me-3" style="width: 50px; height: 50px; background: linear-gradient(45deg, #4facfe 0%, #00f2fe 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 3px solid rgba(255, 255, 255, 0.3); box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);">
                <i class="fas fa-user fa-lg text-white"></i>
            </div>
            <div class="flex-grow-1">
                <div class="fw-bold text-white" style="font-size: 0.95rem;">{{ Auth::user()->name }}</div>
                <small class="text-white-50 d-flex align-items-center">
                    <span class="badge bg-light text-dark rounded-pill px-2 py-1" style="font-size: 0.7rem;">
                        {{ Auth::user()->isAdmin() ? 'Responsable' : 'Pharmacien' }}
                    </span>
                </small>
            </div>
        </div>
    </div>
    
    <!-- Navigation Menu -->
    <nav class="navigation-menu">
        <ul class="nav flex-column">
            <!-- Dashboard -->
            <li class="nav-item mb-1">
                <a class="nav-link {{ request()->is('*/dashboard') ? 'active' : '' }}" 
                   href="{{ Auth::user()->isAdmin() ? route('admin.dashboard') : route('pharmacist.dashboard') }}"
                   style="color: rgba(255, 255, 255, 0.8); padding: 12px 16px; margin: 2px 0; border-radius: 12px; transition: all 0.3s ease; text-decoration: none; display: flex; align-items: center; {{ request()->is('*/dashboard') ? 'background: rgba(255, 255, 255, 0.2); color: white; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);' : '' }}"
                   onmouseover="if (!this.classList.contains('active')) { this.style.background='rgba(255, 255, 255, 0.15)'; this.style.color='white'; this.style.transform='translateX(5px)'; }"
                   onmouseout="if (!this.classList.contains('active')) { this.style.background='transparent'; this.style.color='rgba(255, 255, 255, 0.8)'; this.style.transform='translateX(0)'; }">
                    <div class="nav-icon me-3" style="width: 35px; height: 35px; background: rgba(255, 255, 255, 0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-tachometer-alt"></i>
                    </div>
                    <span style="font-weight: 500;">Tableau de bord</span>
                </a>
            </li>
            
            <!-- Inventory -->
            <li class="nav-item mb-1">
                <a class="nav-link {{ request()->is('inventory*') ? 'active' : '' }}" 
                   href="{{ route('inventory.index') }}"
                   style="color: rgba(255, 255, 255, 0.8); padding: 12px 16px; margin: 2px 0; border-radius: 12px; transition: all 0.3s ease; text-decoration: none; display: flex; align-items: center; {{ request()->is('inventory*') ? 'background: rgba(255, 255, 255, 0.2); color: white; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);' : '' }}"
                   onmouseover="if (!this.classList.contains('active')) { this.style.background='rgba(255, 255, 255, 0.15)'; this.style.color='white'; this.style.transform='translateX(5px)'; }"
                   onmouseout="if (!this.classList.contains('active')) { this.style.background='transparent'; this.style.color='rgba(255, 255, 255, 0.8)'; this.style.transform='translateX(0)'; }">
                    <div class="nav-icon me-3" style="width: 35px; height: 35px; background: rgba(255, 255, 255, 0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-pills"></i>
                    </div>
                    <span style="font-weight: 500;">Inventaire</span>
                </a>
            </li>
            
            <!-- Sales -->
            <li class="nav-item mb-1">
                <a class="nav-link {{ request()->is('sales*') ? 'active' : '' }}" 
                   href="{{ route('sales.index') }}"
                   style="color: rgba(255, 255, 255, 0.8); padding: 12px 16px; margin: 2px 0; border-radius: 12px; transition: all 0.3s ease; text-decoration: none; display: flex; align-items: center; {{ request()->is('sales*') ? 'background: rgba(255, 255, 255, 0.2); color: white; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);' : '' }}"
                   onmouseover="if (!this.classList.contains('active')) { this.style.background='rgba(255, 255, 255, 0.15)'; this.style.color='white'; this.style.transform='translateX(5px)'; }"
                   onmouseout="if (!this.classList.contains('active')) { this.style.background='transparent'; this.style.color='rgba(255, 255, 255, 0.8)'; this.style.transform='translateX(0)'; }">
                    <div class="nav-icon me-3" style="width: 35px; height: 35px; background: rgba(255, 255, 255, 0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-cash-register"></i>
                    </div>
                    <span style="font-weight: 500;">Ventes</span>
                </a>
            </li>
            
            <!-- Clients -->
            <li class="nav-item mb-1">
                <a class="nav-link {{ request()->is('clients*') ? 'active' : '' }}" 
                   href="{{ route('clients.index') }}"
                   style="color: rgba(255, 255, 255, 0.8); padding: 12px 16px; margin: 2px 0; border-radius: 12px; transition: all 0.3s ease; text-decoration: none; display: flex; align-items: center; {{ request()->is('clients*') ? 'background: rgba(255, 255, 255, 0.2); color: white; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);' : '' }}"
                   onmouseover="if (!this.classList.contains('active')) { this.style.background='rgba(255, 255, 255, 0.15)'; this.style.color='white'; this.style.transform='translateX(5px)'; }"
                   onmouseout="if (!this.classList.contains('active')) { this.style.background='transparent'; this.style.color='rgba(255, 255, 255, 0.8)'; this.style.transform='translateX(0)'; }">
                    <div class="nav-icon me-3" style="width: 35px; height: 35px; background: rgba(255, 255, 255, 0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-users"></i>
                    </div>
                    <span style="font-weight: 500;">Clients</span>
                </a>
            </li>
            <!-- Prescriptions -->
            <li class="nav-item mb-1">
                <a class="nav-link {{ request()->is('prescriptions*') ? 'active' : '' }}" 
                   href="{{ route('prescriptions.index') }}"
                   style="color: rgba(255, 255, 255, 0.8); padding: 12px 16px; margin: 2px 0; border-radius: 12px; transition: all 0.3s ease; text-decoration: none; display: flex; align-items: center; position: relative; {{ request()->is('prescriptions*') ? 'background: rgba(255, 255, 255, 0.2); color: white; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);' : '' }}"
                   onmouseover="if (!this.classList.contains('active')) { this.style.background='rgba(255, 255, 255, 0.15)'; this.style.color='white'; this.style.transform='translateX(5px)'; }"
                   onmouseout="if (!this.classList.contains('active')) { this.style.background='transparent'; this.style.color='rgba(255, 255, 255, 0.8)'; this.style.transform='translateX(0)'; }">
                    <div class="nav-icon me-3" style="width: 35px; height: 35px; background: rgba(255, 255, 255, 0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-file-prescription"></i>
                    </div>
                    <span style="font-weight: 500;">Ordonnances</span>
                    @php
                        $pendingCount = \App\Models\Prescription::pending()->count();
                        $expiringCount = \App\Models\Prescription::active()->where('expiry_date', '<=', now()->addDays(7))->count();
                        $totalAlerts = $pendingCount + $expiringCount;
                    @endphp
                    @if($totalAlerts > 0)
                        <span class="badge rounded-pill ms-auto" style="background: linear-gradient(45deg, #ff6b6b 0%, #ee5a52 100%); color: white; font-size: 0.7rem; padding: 4px 8px; animation: pulse 2s infinite;">
                            {{ $totalAlerts }}
                        </span>
                    @endif
                </a>
            </li>
            
            <!-- Suppliers - ADMIN ONLY -->
            @if(Auth::user()->isAdmin())
            <li class="nav-item mb-1">
                <a class="nav-link {{ request()->is('suppliers*') ? 'active' : '' }}" 
                   href="{{ route('suppliers.index') }}"
                   style="color: rgba(255, 255, 255, 0.8); padding: 12px 16px; margin: 2px 0; border-radius: 12px; transition: all 0.3s ease; text-decoration: none; display: flex; align-items: center; position: relative; {{ request()->is('suppliers*') ? 'background: rgba(255, 255, 255, 0.2); color: white; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);' : '' }}"
                   onmouseover="if (!this.classList.contains('active')) { this.style.background='rgba(255, 255, 255, 0.15)'; this.style.color='white'; this.style.transform='translateX(5px)'; }"
                   onmouseout="if (!this.classList.contains('active')) { this.style.background='transparent'; this.style.color='rgba(255, 255, 255, 0.8)'; this.style.transform='translateX(0)'; }">
                    <div class="nav-icon me-3" style="width: 35px; height: 35px; background: rgba(255, 255, 255, 0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-truck"></i>
                    </div>
                    <span style="font-weight: 500;">Fournisseurs</span>
                    @php
                        $inactiveSuppliers = \App\Models\Supplier::where('active', false)->count();
                    @endphp
                    @if($inactiveSuppliers > 0)
                        <span class="badge rounded-pill ms-auto" style="background: linear-gradient(45deg, #ffc107 0%, #ffb300 100%); color: #212529; font-size: 0.7rem; padding: 4px 8px;">
                            {{ $inactiveSuppliers }}
                        </span>
                    @endif
                </a>
            </li>
            
            <!-- Purchases - ADMIN ONLY -->
            <li class="nav-item mb-1">
                <a class="nav-link {{ request()->is('purchases*') ? 'active' : '' }}" 
                   href="{{ route('purchases.index') }}"
                   style="color: rgba(255, 255, 255, 0.8); padding: 12px 16px; margin: 2px 0; border-radius: 12px; transition: all 0.3s ease; text-decoration: none; display: flex; align-items: center; position: relative; {{ request()->is('purchases*') ? 'background: rgba(255, 255, 255, 0.2); color: white; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);' : '' }}"
                   onmouseover="if (!this.classList.contains('active')) { this.style.background='rgba(255, 255, 255, 0.15)'; this.style.color='white'; this.style.transform='translateX(5px)'; }"
                   onmouseout="if (!this.classList.contains('active')) { this.style.background='transparent'; this.style.color='rgba(255, 255, 255, 0.8)'; this.style.transform='translateX(0)'; }">
                    <div class="nav-icon me-3" style="width: 35px; height: 35px; background: rgba(255, 255, 255, 0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <span style="font-weight: 500;">Achats</span>
                    @php
                        $pendingPurchases = \App\Models\Purchase::pending()->count();
                        $overduePurchases = \App\Models\Purchase::overdue()->count();
                        $totalPurchaseAlerts = $pendingPurchases + $overduePurchases;
                    @endphp
                    @if($totalPurchaseAlerts > 0)
                        <span class="badge rounded-pill ms-auto" style="background: linear-gradient(45deg, #17a2b8 0%, #138496 100%); color: white; font-size: 0.7rem; padding: 4px 8px;">
                            {{ $totalPurchaseAlerts }}
                        </span>
                    @endif
                </a>
            </li>
            @endif
            
            <!-- Reports - ADMIN ONLY -->
            @if(Auth::user()->isAdmin())
            <li class="nav-item mb-1">
                <a class="nav-link {{ request()->is('rapports*') ? 'active' : '' }}" 
                   href="{{ route('reports.index') }}"
                   style="color: rgba(255, 255, 255, 0.8); padding: 12px 16px; margin: 2px 0; border-radius: 12px; transition: all 0.3s ease; text-decoration: none; display: flex; align-items: center; {{ request()->is('rapports*') ? 'background: rgba(255, 255, 255, 0.2); color: white; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);' : '' }}"
                   onmouseover="if (!this.classList.contains('active')) { this.style.background='rgba(255, 255, 255, 0.15)'; this.style.color='white'; this.style.transform='translateX(5px)'; }"
                   onmouseout="if (!this.classList.contains('active')) { this.style.background='transparent'; this.style.color='rgba(255, 255, 255, 0.8)'; this.style.transform='translateX(0)'; }">
                    <div class="nav-icon me-3" style="width: 35px; height: 35px; background: rgba(255, 255, 255, 0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <span style="font-weight: 500;">Rapports</span>
                </a>
            </li>
            @endif
            
            @if(Auth::user()->isAdmin())
            <!-- Administration Section -->
            <li class="nav-item mt-4 mb-3">
                <div class="d-flex align-items-center px-3 mb-2">
                    <div style="flex: 1; height: 1px; background: rgba(255, 255, 255, 0.2);"></div>
                    <span class="px-3 text-white-50 text-uppercase small fw-bold" style="font-size: 0.7rem; letter-spacing: 1px;">Administration</span>
                    <div style="flex: 1; height: 1px; background: rgba(255, 255, 255, 0.2);"></div>
                </div>
            </li>
            
            <!-- User Management -->
            <li class="nav-item mb-1">
                <a class="nav-link {{ request()->is('admin/users*') ? 'active' : '' }}" 
                   href="{{ route('admin.users.index') }}"
                   style="color: rgba(255, 255, 255, 0.8); padding: 12px 16px; margin: 2px 0; border-radius: 12px; transition: all 0.3s ease; text-decoration: none; display: flex; align-items: center; position: relative; {{ request()->is('admin/users*') ? 'background: rgba(255, 255, 255, 0.2); color: white; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);' : '' }}"
                   onmouseover="if (!this.classList.contains('active')) { this.style.background='rgba(255, 255, 255, 0.15)'; this.style.color='white'; this.style.transform='translateX(5px)'; }"
                   onmouseout="if (!this.classList.contains('active')) { this.style.background='transparent'; this.style.color='rgba(255, 255, 255, 0.8)'; this.style.transform='translateX(0)'; }">
                    <div class="nav-icon me-3" style="width: 35px; height: 35px; background: rgba(255, 255, 255, 0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-users-cog"></i>
                    </div>
                    <span style="font-weight: 500;">Gestion utilisateurs</span>
                    @php
                        $inactiveUsers = \App\Models\User::where('is_active', false)->count();
                        $passwordChangeRequired = \App\Models\User::where('force_password_change', true)->count();
                        $userAlerts = $inactiveUsers + $passwordChangeRequired;
                    @endphp
                    @if($userAlerts > 0)
                        <span class="badge rounded-pill ms-auto" style="background: linear-gradient(45deg, #ffc107 0%, #ffb300 100%); color: #212529; font-size: 0.7rem; padding: 4px 8px;">
                            {{ $userAlerts }}
                        </span>
                    @endif
                </a>
            </li>
            
            <!-- Activity Logs -->
            <li class="nav-item mb-1">
                <a class="nav-link {{ request()->is('admin/activity-logs*') ? 'active' : '' }}" 
                   href="{{ route('admin.activity-logs') }}"
                   style="color: rgba(255, 255, 255, 0.8); padding: 12px 16px; margin: 2px 0; border-radius: 12px; transition: all 0.3s ease; text-decoration: none; display: flex; align-items: center; {{ request()->is('admin/activity-logs*') ? 'background: rgba(255, 255, 255, 0.2); color: white; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);' : '' }}"
                   onmouseover="if (!this.classList.contains('active')) { this.style.background='rgba(255, 255, 255, 0.15)'; this.style.color='white'; this.style.transform='translateX(5px)'; }"
                   onmouseout="if (!this.classList.contains('active')) { this.style.background='transparent'; this.style.color='rgba(255, 255, 255, 0.8)'; this.style.transform='translateX(0)'; }">
                    <div class="nav-icon me-3" style="width: 35px; height: 35px; background: rgba(255, 255, 255, 0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-history"></i>
                    </div>
                    <span style="font-weight: 500;">Logs d'activité</span>
                </a>
            </li>
            
            <!-- System Administration -->
            <li class="nav-item mb-1">
                <a class="nav-link {{ request()->is('admin/administration*') ? 'active' : '' }}" 
                   href="{{ route('admin.administration') }}"
                   style="color: rgba(255, 255, 255, 0.8); padding: 12px 16px; margin: 2px 0; border-radius: 12px; transition: all 0.3s ease; text-decoration: none; display: flex; align-items: center; {{ request()->is('admin/administration*') ? 'background: rgba(255, 255, 255, 0.2); color: white; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);' : '' }}"
                   onmouseover="if (!this.classList.contains('active')) { this.style.background='rgba(255, 255, 255, 0.15)'; this.style.color='white'; this.style.transform='translateX(5px)'; }"
                   onmouseout="if (!this.classList.contains('active')) { this.style.background='transparent'; this.style.color='rgba(255, 255, 255, 0.8)'; this.style.transform='translateX(0)'; }">
                    <div class="nav-icon me-3" style="width: 35px; height: 35px; background: rgba(255, 255, 255, 0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-cogs"></i>
                    </div>
                    <span style="font-weight: 500;">Administration système</span>
                </a>
            </li>
            @endif
            
            <!-- Notifications - FOR BOTH ROLES -->
            <li class="nav-item mb-1">
                <a class="nav-link {{ request()->is('notifications*') ? 'active' : '' }}" 
                   href="{{ route('notifications.index') }}"
                   style="color: rgba(255, 255, 255, 0.8); padding: 12px 16px; margin: 2px 0; border-radius: 12px; transition: all 0.3s ease; text-decoration: none; display: flex; align-items: center; position: relative; {{ request()->is('notifications*') ? 'background: rgba(255, 255, 255, 0.2); color: white; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);' : '' }}"
                   onmouseover="if (!this.classList.contains('active')) { this.style.background='rgba(255, 255, 255, 0.15)'; this.style.color='white'; this.style.transform='translateX(5px)'; }"
                   onmouseout="if (!this.classList.contains('active')) { this.style.background='transparent'; this.style.color='rgba(255, 255, 255, 0.8)'; this.style.transform='translateX(0)'; }">
                    <div class="nav-icon me-3" style="width: 35px; height: 35px; background: rgba(255, 255, 255, 0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-bell"></i>
                    </div>
                    <span style="font-weight: 500;">Notifications</span>
                    @php
                        $unreadNotifications = auth()->user()->notifications()->unread()->active()->count();
                    @endphp
                    @if($unreadNotifications > 0)
                        <span class="badge rounded-pill ms-auto" style="background: linear-gradient(45deg, #ff6b6b 0%, #ee5a52 100%); color: white; font-size: 0.7rem; padding: 4px 8px; animation: pulse 2s infinite;">
                            {{ $unreadNotifications }}
                        </span>
                    @endif
                </a>
            </li>
            
            <!-- System Settings - ADMIN ONLY -->
            @if(Auth::user()->isAdmin())
            <li class="nav-item mb-1">
                <a class="nav-link {{ request()->is('admin/settings*') ? 'active' : '' }}" 
                   href="{{ route('admin.settings') }}"
                   style="color: rgba(255, 255, 255, 0.8); padding: 12px 16px; margin: 2px 0; border-radius: 12px; transition: all 0.3s ease; text-decoration: none; display: flex; align-items: center; {{ request()->is('admin/settings*') ? 'background: rgba(255, 255, 255, 0.2); color: white; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);' : '' }}"
                   onmouseover="if (!this.classList.contains('active')) { this.style.background='rgba(255, 255, 255, 0.15)'; this.style.color='white'; this.style.transform='translateX(5px)'; }"
                   onmouseout="if (!this.classList.contains('active')) { this.style.background='transparent'; this.style.color='rgba(255, 255, 255, 0.8)'; this.style.transform='translateX(0)'; }">
                    <div class="nav-icon me-3" style="width: 35px; height: 35px; background: rgba(255, 255, 255, 0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-sliders-h"></i>
                    </div>
                    <span style="font-weight: 500;">Paramètres système</span>
                </a>
            </li>
            @endif
        </ul>
    </nav>
    
      <!-- Logout Button -->
    <div class="mt-auto pt-4">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn w-100" style="background: rgba(255, 255, 255, 0.1); color: white; border: 2px solid rgba(255, 255, 255, 0.3); border-radius: 12px; padding: 12px; font-weight: 500; transition: all 0.3s ease; backdrop-filter: blur(10px);" onmouseover="this.style.background='rgba(255, 255, 255, 0.2)'; this.style.borderColor='rgba(255, 255, 255, 0.5)'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(0, 0, 0, 0.2)';" onmouseout="this.style.background='rgba(255, 255, 255, 0.1)'; this.style.borderColor='rgba(255, 255, 255, 0.3)'; this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                <i class="fas fa-sign-out-alt me-2"></i> Déconnexion
            </button>
        </form>
    </div>
    
    <!-- Footer Info -->
    <div class="text-center mt-3 pt-3 border-top border-light border-opacity-25">
        <small class="text-white-50 d-block" style="font-size: 0.7rem;">
            © {{ date('Y') }} PHARMACIA
        </small>
        <small class="text-white-50" style="font-size: 0.65rem;">
            Version 1.0.0
        </small>
    </div>
</div>

<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

/* Logo animation */
.logo-circle {
    animation: logo-pulse 3s ease-in-out infinite;
}

@keyframes logo-pulse {
    0%, 100% {
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        transform: scale(1);
    }
    50% {
        box-shadow: 0 12px 35px rgba(0, 0, 0, 0.3);
        transform: scale(1.05);
    }
}

/* Badge animation */
@keyframes pulse {
    0% {
        transform: scale(1);
        opacity: 1;
    }
    50% {
        transform: scale(1.1);
        opacity: 0.8;
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}

/* Smooth navigation transitions */
.nav-link {
    position: relative;
    overflow: hidden;
}

.nav-link::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
    transition: left 0.5s;
}

.nav-link:hover::before {
    left: 100%;
}

/* Active state enhancement */
.nav-link.active {
    position: relative;
}

.nav-link.active::after {
    content: '';
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    width: 4px;
    height: 20px;
    background: linear-gradient(45deg, #4facfe 0%, #00f2fe 100%);
    border-radius: 2px;
    box-shadow: 0 0 10px rgba(79, 172, 254, 0.5);
}

/* User profile hover effect */
.user-profile:hover .user-avatar {
    transform: scale(1.1);
    transition: transform 0.3s ease;
}

/* Sidebar scroll enhancement */
.sidebar {
    overflow-y: auto;
    scrollbar-width: thin;
    scrollbar-color: rgba(255, 255, 255, 0.3) transparent;
}

.sidebar::-webkit-scrollbar {
    width: 6px;
}

.sidebar::-webkit-scrollbar-track {
    background: transparent;
}

.sidebar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.3);
    border-radius: 3px;
}

.sidebar::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.5);
}

/* Mobile responsiveness */
@media (max-width: 768px) {
    .sidebar {
        transform: translateX(-100%);
        transition: transform 0.3s ease;
        position: fixed;
        z-index: 1000;
        width: 280px;
        height: 100vh;
    }
    
    .sidebar.show {
        transform: translateX(0);
    }
}

/* Enhance logo container */
.logo-container {
    position: relative;
}

.logo-container::before {
    content: '';
    position: absolute;
    top: -10px;
    left: -10px;
    right: -10px;
    bottom: -10px;
    background: radial-gradient(circle at center, rgba(79, 172, 254, 0.1) 0%, transparent 70%);
    border-radius: 50%;
    animation: logo-glow 4s ease-in-out infinite;
}

@keyframes logo-glow {
    0%, 100% {
        opacity: 0.5;
        transform: scale(1);
    }
    50% {
        opacity: 1;
        transform: scale(1.1);
    }
}
</style>