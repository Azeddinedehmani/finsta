<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ActivityLog;

class LogActivity
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Skip logging for auth routes and certain conditions
        if ($this->shouldSkipLogging($request, $response)) {
            return $response;
        }

        // Only log for authenticated users and successful requests
        if (auth()->check() && $response->getStatusCode() < 400) {
            $this->logActivity($request, $response);
        }

        return $response;
    }

    /**
     * Determine if logging should be skipped
     */
    private function shouldSkipLogging(Request $request, Response $response): bool
    {
        $uri = $request->getRequestUri();
        $routeName = $request->route() ? $request->route()->getName() : null;

        // Skip auth-related routes
        $authRoutes = [
            '/login',
            '/logout', 
            '/forgot-password',
            '/reset-password',
            'login',
            'logout',
            'password.forgot',
            'password.send.code',
            'password.reset.form',
            'password.reset'
        ];

        // Skip if it's an auth route
        if (in_array($uri, $authRoutes) || in_array($routeName, $authRoutes)) {
            return true;
        }

        // Skip if URL contains auth patterns
        if (str_contains($uri, '/login') || 
            str_contains($uri, '/logout') || 
            str_contains($uri, '/forgot-password') || 
            str_contains($uri, '/reset-password')) {
            return true;
        }

        // Skip AJAX requests for notifications and API calls
        $skipRoutes = [
            'notifications.recent',
            'notifications.count',
            'sales.get-product',
        ];

        if (in_array($routeName, $skipRoutes) || 
            str_contains($uri, '/api/') || 
            ($request->ajax() && str_contains($uri, 'notification'))) {
            return true;
        }

        // Skip asset requests
        if (str_contains($uri, '/css/') || 
            str_contains($uri, '/js/') || 
            str_contains($uri, '/images/') ||
            str_contains($uri, '/favicon.ico')) {
            return true;
        }

        // Skip if user is not authenticated (except for specific public routes)
        if (!auth()->check()) {
            return true;
        }

        return false;
    }

    /**
     * Log the activity
     */
    private function logActivity(Request $request, Response $response)
    {
        try {
            $method = $request->method();
            $route = $request->route();
            $routeName = $route ? $route->getName() : null;
            $uri = $request->getRequestUri();

            // Determine action based on HTTP method and route
            $action = $this->determineAction($method, $routeName, $uri);
            $description = $this->generateDescription($action, $routeName, $request);

            // Extract model information if available
            $modelType = null;
            $modelId = null;
            
            if ($route) {
                $parameters = $route->parameters();
                
                // Try to identify the main model from route parameters
                $modelMappings = [
                    'product' => 'App\Models\Product',
                    'inventory' => 'App\Models\Product',
                    'client' => 'App\Models\Client',
                    'sale' => 'App\Models\Sale',
                    'prescription' => 'App\Models\Prescription',
                    'purchase' => 'App\Models\Purchase',
                    'supplier' => 'App\Models\Supplier',
                    'user' => 'App\Models\User',
                    'notification' => 'App\Models\Notification',
                ];
                
                foreach ($modelMappings as $param => $model) {
                    if (isset($parameters[$param])) {
                        $modelType = $model;
                        $modelId = $parameters[$param];
                        break;
                    }
                }
            }

            // Create activity log
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => $action,
                'description' => $description,
                'model_type' => $modelType,
                'model_id' => $modelId,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

        } catch (\Exception $e) {
            // Silently fail to prevent breaking the application
            \Log::error('Failed to log activity: ' . $e->getMessage(), [
                'uri' => $request->getRequestUri(),
                'method' => $request->method(),
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Determine the action based on method and route
     */
    private function determineAction(string $method, ?string $routeName, string $uri): string
    {
        // Handle export and print actions
        if (str_contains($uri, '/export') || str_contains($uri, '/download')) {
            return 'export';
        }
        
        if (str_contains($uri, '/print')) {
            return 'print';
        }
        
        // Handle specific route actions
        if ($routeName) {
            if (str_contains($routeName, '.destroy')) return 'delete';
            if (str_contains($routeName, '.store')) return 'create';
            if (str_contains($routeName, '.update')) return 'update';
            if (str_contains($routeName, '.edit')) return 'view_form';
            if (str_contains($routeName, '.create')) return 'view_form';
            if (str_contains($routeName, '.show') || str_contains($routeName, '.index')) return 'view';
            
            // Special actions
            if (str_contains($routeName, 'deliver')) return 'deliver';
            if (str_contains($routeName, 'receive')) return 'receive';
            if (str_contains($routeName, 'cancel')) return 'cancel';
            if (str_contains($routeName, 'toggle')) return 'toggle';
            if (str_contains($routeName, 'reset')) return 'reset';
        }

        // Fallback to HTTP method
        if ($method === 'GET') return 'view';
        if ($method === 'POST') return 'create';
        if (in_array($method, ['PUT', 'PATCH'])) return 'update';
        if ($method === 'DELETE') return 'delete';

        return 'action';
    }

    /**
     * Generate description for the activity
     */
    private function generateDescription(string $action, ?string $routeName, Request $request): string
    {
        if (!$routeName) {
            return "Action {$action} sur une ressource";
        }

        // Define comprehensive descriptions
        $descriptions = [
            // Sales
            'sales.index' => 'Consultation de la liste des ventes',
            'sales.show' => 'Consultation d\'une vente',
            'sales.create' => 'Affichage du formulaire de nouvelle vente',
            'sales.store' => 'Création d\'une nouvelle vente',
            'sales.edit' => 'Affichage du formulaire de modification de vente',
            'sales.update' => 'Modification d\'une vente',
            'sales.destroy' => 'Suppression d\'une vente',
            'sales.print' => 'Impression d\'une facture de vente',
            
            // Inventory/Products
            'inventory.index' => 'Consultation de l\'inventaire',
            'inventory.show' => 'Consultation d\'un produit',
            'inventory.create' => 'Affichage du formulaire de nouveau produit',
            'inventory.store' => 'Ajout d\'un nouveau produit',
            'inventory.edit' => 'Affichage du formulaire de modification de produit',
            'inventory.update' => 'Modification d\'un produit',
            'inventory.destroy' => 'Suppression d\'un produit',
            
            // Clients
            'clients.index' => 'Consultation de la liste des clients',
            'clients.show' => 'Consultation d\'un client',
            'clients.create' => 'Affichage du formulaire de nouveau client',
            'clients.store' => 'Création d\'un nouveau client',
            'clients.edit' => 'Affichage du formulaire de modification de client',
            'clients.update' => 'Modification d\'un client',
            'clients.destroy' => 'Suppression d\'un client',
            
            // Prescriptions
            'prescriptions.index' => 'Consultation de la liste des ordonnances',
            'prescriptions.show' => 'Consultation d\'une ordonnance',
            'prescriptions.create' => 'Affichage du formulaire de nouvelle ordonnance',
            'prescriptions.store' => 'Création d\'une nouvelle ordonnance',
            'prescriptions.edit' => 'Affichage du formulaire de modification d\'ordonnance',
            'prescriptions.update' => 'Modification d\'une ordonnance',
            'prescriptions.destroy' => 'Suppression d\'une ordonnance',
            'prescriptions.deliver' => 'Délivrance d\'une ordonnance',
            'prescriptions.process-delivery' => 'Traitement de la délivrance d\'ordonnance',
            'prescriptions.print' => 'Impression d\'une ordonnance',
            
            // Purchases
            'purchases.index' => 'Consultation de la liste des achats',
            'purchases.show' => 'Consultation d\'un achat',
            'purchases.create' => 'Affichage du formulaire de nouvelle commande',
            'purchases.store' => 'Création d\'une nouvelle commande d\'achat',
            'purchases.edit' => 'Affichage du formulaire de modification de commande',
            'purchases.update' => 'Modification d\'une commande d\'achat',
            'purchases.destroy' => 'Suppression d\'une commande d\'achat',
            'purchases.receive' => 'Réception d\'une commande d\'achat',
            'purchases.process-reception' => 'Traitement de la réception de commande',
            'purchases.cancel' => 'Annulation d\'une commande d\'achat',
            'purchases.print' => 'Impression d\'un bon de commande',
            
            // Suppliers
            'suppliers.index' => 'Consultation de la liste des fournisseurs',
            'suppliers.show' => 'Consultation d\'un fournisseur',
            'suppliers.create' => 'Affichage du formulaire de nouveau fournisseur',
            'suppliers.store' => 'Création d\'un nouveau fournisseur',
            'suppliers.edit' => 'Affichage du formulaire de modification de fournisseur',
            'suppliers.update' => 'Modification d\'un fournisseur',
            'suppliers.destroy' => 'Suppression d\'un fournisseur',
            
            // Users (Admin)
            'admin.users.index' => 'Consultation de la liste des utilisateurs',
            'admin.users.show' => 'Consultation d\'un utilisateur',
            'admin.users.create' => 'Affichage du formulaire de nouvel utilisateur',
            'admin.users.store' => 'Création d\'un nouvel utilisateur',
            'admin.users.edit' => 'Affichage du formulaire de modification d\'utilisateur',
            'admin.users.update' => 'Modification d\'un utilisateur',
            'admin.users.destroy' => 'Suppression d\'un utilisateur',
            'admin.users.toggle-status' => 'Changement de statut d\'un utilisateur',
            'admin.users.reset-password' => 'Réinitialisation du mot de passe d\'un utilisateur',
            'admin.users.activity-logs' => 'Consultation des activités d\'un utilisateur',
            'admin.users.export' => 'Export de la liste des utilisateurs',
            
            // Admin Dashboard & System
            'admin.dashboard' => 'Consultation du tableau de bord administrateur',
            'admin.administration' => 'Consultation du panneau d\'administration',
            'admin.settings' => 'Consultation des paramètres système',
            'admin.settings.update' => 'Modification des paramètres système',
            'admin.activity-logs' => 'Consultation des logs d\'activité',
            'admin.export-activity-logs' => 'Export des logs d\'activité',
            'admin.clear-old-logs' => 'Nettoyage des anciens logs',
            'admin.system-status' => 'Consultation du statut système',
            'admin.performance-metrics' => 'Consultation des métriques de performance',
            'admin.health-check' => 'Vérification de santé du système',
            'admin.toggle-maintenance' => 'Basculement du mode maintenance',
            'admin.clear-cache' => 'Vidage du cache',
            'admin.optimize-database' => 'Optimisation de la base de données',
            
            // Pharmacist Dashboard
            'pharmacist.dashboard' => 'Consultation du tableau de bord pharmacien',
            
            // Reports
            'reports.index' => 'Consultation du tableau de bord des rapports',
            'reports.sales' => 'Consultation du rapport des ventes',
            'reports.inventory' => 'Consultation du rapport d\'inventaire',
            'reports.clients' => 'Consultation du rapport des clients',
            'reports.prescriptions' => 'Consultation du rapport des ordonnances',
            'reports.financial' => 'Consultation du rapport financier',
            'reports.users' => 'Consultation du rapport des utilisateurs',
            'reports.suppliers' => 'Consultation du rapport des fournisseurs',
            
            // Notifications
            'notifications.index' => 'Consultation des notifications',
            'notifications.settings' => 'Consultation des paramètres de notification',
            'notifications.settings.update' => 'Modification des paramètres de notification',
            'notifications.mark-read' => 'Marquage d\'une notification comme lue',
            'notifications.mark-all-read' => 'Marquage de toutes les notifications comme lues',
            'notifications.destroy' => 'Suppression d\'une notification',
            'notifications.delete-read' => 'Suppression des notifications lues',
        ];

        // Return specific description or generate generic one
        if (isset($descriptions[$routeName])) {
            return $descriptions[$routeName];
        }

        // Generate generic description based on route pattern
        $parts = explode('.', $routeName);
        if (count($parts) >= 2) {
            $resource = str_replace(['admin.', 'reports.'], '', $parts[0]);
            $action = end($parts);
            
            $resourceNames = [
                'sales' => 'vente',
                'inventory' => 'produit',
                'clients' => 'client',
                'prescriptions' => 'ordonnance',
                'purchases' => 'achat',
                'suppliers' => 'fournisseur',
                'users' => 'utilisateur',
                'notifications' => 'notification',
                'dashboard' => 'tableau de bord',
                'home' => 'accueil',
            ];
            
            $actionNames = [
                'index' => 'consultation de la liste des',
                'show' => 'consultation d\'un',
                'create' => 'affichage du formulaire de nouveau',
                'store' => 'création d\'un nouveau',
                'edit' => 'affichage du formulaire de modification de',
                'update' => 'modification d\'un',
                'destroy' => 'suppression d\'un',
                'export' => 'export de',
                'print' => 'impression de',
            ];
            
            $resourceName = $resourceNames[$resource] ?? $resource;
            $actionName = $actionNames[$action] ?? $action;
            
            return ucfirst($actionName) . ' ' . $resourceName;
        }

        return "Action {$action} sur " . str_replace('.', ' ', $routeName);
    }
}