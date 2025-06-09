<?php
// app/Http/Middleware/PharmacistAccessControl.php - New middleware for strict access control

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PharmacistAccessControl
{
    /**
     * Handle an incoming request - Block pharmacist access to admin features
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        
        // If user is admin, allow everything
        if ($user->isAdmin()) {
            return $next($request);
        }

        // If user is pharmacist, check restricted routes
        if ($user->isPharmacist()) {
            $restrictedRoutes = [
                'suppliers',
                'purchases', 
                'rapports',
                'reports',
                'admin',
            ];

            $currentRoute = $request->route()->getName();
            $currentPath = $request->path();

            // Check if current route/path is restricted
            foreach ($restrictedRoutes as $restricted) {
                if (str_contains($currentRoute, $restricted) || str_contains($currentPath, $restricted)) {
                    return redirect()
                        ->route('pharmacist.dashboard')
                        ->with('error', 'Accès non autorisé. Cette fonctionnalité est réservée aux responsables.');
                }
            }
        }

        return $next($request);
    }
}

