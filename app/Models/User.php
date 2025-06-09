<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'date_of_birth',
        'address',
        'profile_photo',
        'is_active',
        'last_login_at',
        'last_login_ip',
        'permissions',
        'password_changed_at',
        'force_password_change',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'date_of_birth' => 'date',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
        'permissions' => 'array',
        'password_changed_at' => 'datetime',
        'force_password_change' => 'boolean',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Check if user is admin
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->role === 'responsable';
    }

    /**
     * Check if user is pharmacist
     *
     * @return bool
     */
    public function isPharmacist()
    {
        return $this->role === 'pharmacien';
    }

    /**
     * Get the notifications for the user.
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get unread notifications count
     */
    public function getUnreadNotificationsCountAttribute()
    {
        return $this->notifications()->unread()->active()->count();
    }

    /**
     * Get recent notifications
     */
    public function getRecentNotificationsAttribute()
    {
        return $this->notifications()->active()->latest()->take(5)->get();
    }

    /**
     * Get activity logs for this user
     */
    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * Check if user can delete prescriptions based on role and prescription status
     *
     * @param \App\Models\Prescription $prescription
     * @return bool
     */
    public function canDeletePrescription($prescription)
    {
        // Expired prescriptions cannot be deleted by anyone
        if ($prescription->isExpired()) {
            return false;
        }

        // Admin (responsable) can delete any non-expired prescription
        if ($this->isAdmin()) {
            return true;
        }

        // Pharmacist can only delete pending or partially delivered prescriptions
        if ($this->isPharmacist()) {
            return in_array($prescription->status, ['pending', 'partially_delivered']);
        }

        // Default: no permission for other roles
        return false;
    }

    /**
     * Get deletion restriction reason for UI feedback
     *
     * @param \App\Models\Prescription $prescription
     * @return string|null
     */
    public function getDeletionRestrictionReason($prescription)
    {
        if ($prescription->isExpired()) {
            return 'Ordonnance expirée - Suppression impossible';
        }

        if (!$this->isAdmin() && $prescription->status === 'completed') {
            return 'Seuls les responsables peuvent supprimer les ordonnances délivrées';
        }

        if ($this->isPharmacist() && !in_array($prescription->status, ['pending', 'partially_delivered'])) {
            return 'Les pharmaciens ne peuvent supprimer que les ordonnances en attente ou partiellement délivrées';
        }

        if (!$this->canDeletePrescription($prescription)) {
            return 'Suppression non autorisée pour ce statut';
        }

        return null; // No restriction
    }

    /**
     * Check if user can delete clients
     *
     * @param \App\Models\Client $client
     * @return bool
     */
    public function canDeleteClient($client)
    {
        // Only admins (responsable) can delete clients with associated data
        $salesCount = $client->sales()->count();
        $prescriptionsCount = $client->prescriptions()->count();
        
        if (($salesCount > 0 || $prescriptionsCount > 0) && !$this->isAdmin()) {
            return false;
        }

        return true;
    }

    /**
     * Check if user can delete products
     *
     * @param \App\Models\Product $product
     * @return bool
     */
    public function canDeleteProduct($product)
    {
        // Both admin and pharmacist can delete products
        // but you might want to add additional restrictions here
        return $this->isAdmin() || $this->isPharmacist();
    }

    /**
     * Check if user can delete sales
     *
     * @param \App\Models\Sale $sale
     * @return bool
     */
    public function canDeleteSale($sale)
    {
        // Sales older than 7 days cannot be deleted
        if ($sale->sale_date < now()->subDays(7)) {
            return false;
        }

        // Both admin and pharmacist can delete recent sales
        return $this->isAdmin() || $this->isPharmacist();
    }

    /**
     * Check if user can access admin features
     *
     * @return bool
     */
    public function canAccessAdminFeatures()
    {
        return $this->isAdmin();
    }

    /**
     * Check if user can manage suppliers
     *
     * @return bool
     */
    public function canManageSuppliers()
    {
        return $this->isAdmin();
    }

    /**
     * Check if user can view financial reports
     *
     * @return bool
     */
    public function canViewFinancialReports()
    {
        return $this->isAdmin();
    }

    /**
     * Get user's role display name
     *
     * @return string
     */
    public function getRoleDisplayNameAttribute()
    {
        return match($this->role) {
            'responsable' => 'Responsable',
            'pharmacien' => 'Pharmacien',
            default => ucfirst($this->role)
        };
    }

    /**
     * Check if user is active
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->is_active;
    }

    /**
     * Scope for active users
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for admin users
     */
    public function scopeAdmins($query)
    {
        return $query->where('role', 'responsable');
    }

    /**
     * Scope for pharmacist users
     */
    public function scopePharmacists($query)
    {
        return $query->where('role', 'pharmacien');
    }
}