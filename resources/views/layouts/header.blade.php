<!-- resources/views/layouts/header.blade.php -->
<header class="py-3 mb-4 border-bottom">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <div>
            <h3>{{ isset($title) ? $title : 'Tableau de bord' }}</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ Auth::user()->isAdmin() ? route('admin.dashboard') : route('pharmacist.dashboard') }}">Accueil</a></li>
                    @if(isset($breadcrumb))
                        @foreach($breadcrumb as $item)
                            <li class="breadcrumb-item {{ $loop->last ? 'active' : '' }}">
                                @if($loop->last)
                                    {{ $item['name'] }}
                                @else
                                    <a href="{{ $item['url'] }}">{{ $item['name'] }}</a>
                                @endif
                            </li>
                        @endforeach
                    @endif
                </ol>
            </nav>
        </div>
        
        <div class="d-flex align-items-center">
            <div class="theme-toggle me-3">
                <i id="theme-icon" class="fas fa-moon"></i>
            </div>
            
            <!-- Notification Dropdown -->
            @include('partials.notification-dropdown')
            
            <!-- User Menu -->
            <div class="dropdown ms-3">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-user"></i>
                    {{ Auth::user()->name }}
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><h6 class="dropdown-header">{{ Auth::user()->email }}</h6></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="{{ route('notifications.index') }}">
                        <i class="fas fa-bell me-2"></i>Notifications
                    </a></li>
                    <li><a class="dropdown-item" href="{{ route('notifications.settings') }}">
                        <i class="fas fa-cog me-2"></i>Paramètres notifications
                    </a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}" class="m-0">
                            @csrf
                            <button type="submit" class="dropdown-item">
                                <i class="fas fa-sign-out-alt me-2"></i>Déconnexion
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</header>