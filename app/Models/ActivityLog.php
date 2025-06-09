<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'model_type',
        'model_id',
        'description',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that performed the action
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the model that was affected
     */
    public function getModelAttribute()
    {
        if ($this->model_type && $this->model_id && class_exists($this->model_type)) {
            try {
                return $this->model_type::find($this->model_id);
            } catch (\Exception $e) {
                return null;
            }
        }
        return null;
    }

    /**
     * Get action badge class
     */
    public function getActionBadgeAttribute()
    {
        return match($this->action) {
            'create' => 'bg-success',
            'update' => 'bg-warning text-dark',
            'delete' => 'bg-danger',
            'view' => 'bg-info',
            'view_form' => 'bg-info',
            'login' => 'bg-primary',
            'logout' => 'bg-secondary',
            'export' => 'bg-dark',
            'print' => 'bg-purple',
            'deliver' => 'bg-success',
            'receive' => 'bg-success',
            'cancel' => 'bg-warning text-dark',
            'toggle' => 'bg-warning text-dark',
            'reset' => 'bg-danger',
            default => 'bg-light text-dark'
        };
    }

    /**
     * Get action icon
     */
    public function getActionIconAttribute()
    {
        return match($this->action) {
            'create' => 'fas fa-plus',
            'update' => 'fas fa-edit',
            'delete' => 'fas fa-trash',
            'view' => 'fas fa-eye',
            'view_form' => 'fas fa-edit',
            'login' => 'fas fa-sign-in-alt',
            'logout' => 'fas fa-sign-out-alt',
            'export' => 'fas fa-download',
            'print' => 'fas fa-print',
            'deliver' => 'fas fa-truck',
            'receive' => 'fas fa-inbox',
            'cancel' => 'fas fa-times',
            'toggle' => 'fas fa-toggle-on',
            'reset' => 'fas fa-redo',
            default => 'fas fa-info'
        };
    }

    /**
     * Get action label in French
     */
    public function getActionLabelAttribute()
    {
        return match($this->action) {
            'create' => 'Création',
            'update' => 'Modification',
            'delete' => 'Suppression',
            'view' => 'Consultation',
            'view_form' => 'Formulaire',
            'login' => 'Connexion',
            'logout' => 'Déconnexion',
            'export' => 'Export',
            'print' => 'Impression',
            'deliver' => 'Livraison',
            'receive' => 'Réception',
            'cancel' => 'Annulation',
            'toggle' => 'Basculement',
            'reset' => 'Réinitialisation',
            default => ucfirst($this->action)
        };
    }

    /**
     * Get formatted model name
     */
    public function getModelNameAttribute()
    {
        if (!$this->model_type) return 'Système';
        
        return match($this->model_type) {
            'App\Models\User' => 'Utilisateur',
            'App\Models\Product' => 'Produit',
            'App\Models\Sale' => 'Vente',
            'App\Models\Client' => 'Client',
            'App\Models\Prescription' => 'Ordonnance',
            'App\Models\Purchase' => 'Achat',
            'App\Models\Supplier' => 'Fournisseur',
            'App\Models\Category' => 'Catégorie',
            'App\Models\Notification' => 'Notification',
            default => class_basename($this->model_type)
        };
    }

    /**
     * Scope for recent activities
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope for specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for specific action
     */
    public function scopeForAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope for specific model
     */
    public function scopeForModel($query, $modelType, $modelId = null)
    {
        $query->where('model_type', $modelType);
        
        if ($modelId) {
            $query->where('model_id', $modelId);
        }
        
        return $query;
    }

    /**
     * Scope for important actions only
     */
    public function scopeImportant($query)
    {
        return $query->whereIn('action', ['create', 'update', 'delete', 'login', 'logout']);
    }

    /**
     * Scope for business actions (excluding view/form actions)
     */
    public function scopeBusiness($query)
    {
        return $query->whereNotIn('action', ['view', 'view_form']);
    }

    /**
     * Log activity helper - Enhanced version
     */
    public static function logActivity($action, $description, $model = null, $oldValues = null, $newValues = null)
    {
        if (!auth()->check()) return;

        try {
            $log = new static();
            $log->user_id = auth()->id();
            $log->action = $action;
            $log->description = $description;
            $log->ip_address = request()->ip();
            $log->user_agent = request()->userAgent();

            if ($model) {
                $log->model_type = get_class($model);
                $log->model_id = $model->getKey() ?? null;
            }

            if ($oldValues) {
                $log->old_values = is_array($oldValues) ? $oldValues : $oldValues->toArray();
            }

            if ($newValues) {
                $log->new_values = is_array($newValues) ? $newValues : $newValues->toArray();
            }

            $log->save();

            return $log;
        } catch (\Exception $e) {
            // Log the error but don't break the application
            \Log::error('Failed to log activity: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Log business transaction (sale, purchase, prescription, etc.)
     */
    public static function logTransaction($type, $model, $action = 'create', $additionalData = [])
    {
        $descriptions = [
            'sale' => [
                'create' => "Vente créée: {$model->sale_number} - Montant: {$model->total_amount}€",
                'update' => "Vente modifiée: {$model->sale_number}",
                'delete' => "Vente supprimée: {$model->sale_number}",
            ],
            'purchase' => [
                'create' => "Commande créée: {$model->purchase_number} - Fournisseur: {$model->supplier->name}",
                'update' => "Commande modifiée: {$model->purchase_number}",
                'receive' => "Commande reçue: {$model->purchase_number}",
            ],
            'prescription' => [
                'create' => "Ordonnance créée: {$model->prescription_number} - Client: {$model->client->full_name}",
                'update' => "Ordonnance modifiée: {$model->prescription_number}",
                'deliver' => "Ordonnance délivrée: {$model->prescription_number}",
            ],
        ];

        $description = $descriptions[$type][$action] ?? "Action {$action} sur {$type}";
        
        return self::logActivity($action, $description, $model, null, $additionalData);
    }

    /**
     * Log stock changes
     */
    public static function logStockChange($product, $oldStock, $newStock, $reason = 'Modification manuelle')
    {
        $change = $newStock - $oldStock;
        $changeText = $change > 0 ? "+{$change}" : $change;
        
        return self::logActivity(
            'stock_update',
            "Stock modifié pour {$product->name}: {$oldStock} → {$newStock} ({$changeText}) - {$reason}",
            $product,
            ['stock_quantity' => $oldStock],
            ['stock_quantity' => $newStock, 'change' => $change, 'reason' => $reason]
        );
    }

    /**
     * Log user authentication events
     */
    public static function logAuth($action, $user = null, $additionalData = [])
    {
        $user = $user ?? auth()->user();
        
        $descriptions = [
            'login' => "Connexion réussie de {$user->name}",
            'logout' => "Déconnexion de {$user->name}",
            'failed_login' => "Tentative de connexion échouée pour {$user->email}",
        ];

        return self::logActivity(
            $action,
            $descriptions[$action] ?? "Action d'authentification: {$action}",
            $user,
            null,
            $additionalData
        );
    }

    /**
     * Clean old logs while keeping important ones
     */
    public static function cleanOldLogs($days = 90, $keepImportant = true)
    {
        $cutoffDate = now()->subDays($days);
        
        $query = self::where('created_at', '<', $cutoffDate);
        
        // Keep important actions longer (login, create, update, delete)
        if ($keepImportant) {
            $query->whereNotIn('action', ['login', 'logout', 'create', 'update', 'delete']);
        }
        
        return $query->delete();
    }

    /**
     * Get activity statistics
     */
    public static function getStatistics($days = 30)
    {
        $startDate = now()->subDays($days);
        
        return [
            'total' => self::where('created_at', '>=', $startDate)->count(),
            'by_action' => self::where('created_at', '>=', $startDate)
                ->select('action')
                ->selectRaw('COUNT(*) as count')
                ->groupBy('action')
                ->orderBy('count', 'desc')
                ->get(),
            'by_user' => self::where('created_at', '>=', $startDate)
                ->select('user_id')
                ->selectRaw('COUNT(*) as count')
                ->with('user:id,name')
                ->groupBy('user_id')
                ->orderBy('count', 'desc')
                ->get(),
            'by_model' => self::where('created_at', '>=', $startDate)
                ->whereNotNull('model_type')
                ->select('model_type')
                ->selectRaw('COUNT(*) as count')
                ->groupBy('model_type')
                ->orderBy('count', 'desc')
                    ->get(),
            'daily_trend' => self::where('created_at', '>=', $startDate)
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date')
                ->get(),
        ];
    }
}