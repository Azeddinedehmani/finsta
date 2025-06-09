<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Prescription extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'prescription_number', 'client_id', 'doctor_name', 'doctor_phone', 'doctor_speciality',
        'prescription_date', 'expiry_date', 'status', 'medical_notes', 'pharmacist_notes',
        'created_by', 'delivered_by', 'delivered_at',
    ];

    protected $casts = [
        'prescription_date' => 'date',
        'expiry_date' => 'date',
        'delivered_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($prescription) {
            if (!$prescription->prescription_number) {
                $prescription->prescription_number = self::generateUniqueNumber();
            }
        });
    }

    /**
     * Generate a unique prescription number using timestamp + random for guaranteed uniqueness
     */
    public static function generateUniqueNumber()
    {
        $today = date('Ymd');
        $maxAttempts = 10;
        
        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            // Use timestamp + random number for uniqueness
            $timestamp = now()->format('His'); // Hour, minute, second
            $random = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
            $prescriptionNumber = "ORD-{$today}-{$timestamp}{$random}";
            
            // Check if this number already exists
            if (!self::where('prescription_number', $prescriptionNumber)->exists()) {
                return $prescriptionNumber;
            }
            
            // If collision, wait a bit and try again
            usleep(rand(1000, 5000)); // 1-5ms delay
        }
        
        // Absolute fallback - use microtime to guarantee uniqueness
        $microtime = str_replace('.', '', microtime(true));
        return "ORD-{$today}-" . substr($microtime, -9);
    }

    // Relations
    public function client() 
    { 
        return $this->belongsTo(Client::class); 
    }
    
    public function createdBy() 
    { 
        return $this->belongsTo(User::class, 'created_by'); 
    }
    
    public function deliveredBy() 
    { 
        return $this->belongsTo(User::class, 'delivered_by'); 
    }
    
    public function prescriptionItems() 
    { 
        return $this->hasMany(PrescriptionItem::class); 
    }

    // Status and date checks
    public function isExpired() 
    { 
        return $this->expiry_date->isPast(); 
    }
    
    public function isAboutToExpire($days = 7) 
    { 
        return $this->expiry_date->diffInDays(now()) <= $days && !$this->isExpired(); 
    }

    public function isFullyDelivered()
    {
        $totalItems = $this->prescriptionItems()->count();
        if ($totalItems === 0) return false;
        
        $fullyDeliveredItems = $this->prescriptionItems()
            ->whereRaw('quantity_delivered >= quantity_prescribed')
            ->count();
        
        return $fullyDeliveredItems === $totalItems;
    }

    public function isPartiallyDelivered()
    {
        $hasDeliveredItems = $this->prescriptionItems()
            ->where('quantity_delivered', '>', 0)
            ->count() > 0;
        
        return $hasDeliveredItems && !$this->isFullyDelivered();
    }

    public function getDeliveryProgressAttribute()
    {
        $totalPrescribed = $this->prescriptionItems()->sum('quantity_prescribed');
        $totalDelivered = $this->prescriptionItems()->sum('quantity_delivered');
        
        if ($totalPrescribed == 0) return 0;
        
        return round(($totalDelivered / $totalPrescribed) * 100, 1);
    }

    public function updateStatus()
    {
        $oldStatus = $this->status;
        
        if ($this->isExpired()) {
            $this->status = 'expired';
        } elseif ($this->isFullyDelivered()) {
            $this->status = 'completed';
            if (!$this->delivered_at) {
                $this->delivered_at = now();
                $this->delivered_by = auth()->id();
            }
        } elseif ($this->isPartiallyDelivered()) {
            $this->status = 'partially_delivered';
        } else {
            $this->status = 'pending';
        }
        
        // Only save if status actually changed
        if ($this->status !== $oldStatus) {
            $this->save();
        }
    }

    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'pending' => 'bg-warning text-dark',
            'partially_delivered' => 'bg-info text-white',
            'completed' => 'bg-success',
            'expired' => 'bg-danger',
            default => 'bg-secondary'
        };
    }

    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'pending' => 'En attente',
            'partially_delivered' => 'Partiellement délivrée',
            'completed' => 'Complètement délivrée',
            'expired' => 'Expirée',
            default => 'Inconnu'
        };
    }

    // Scopes
    public function scopePending($query) 
    { 
        return $query->where('status', 'pending'); 
    }
    
    public function scopeActive($query) 
    { 
        return $query->where('expiry_date', '>=', now()); 
    }
    
    public function scopeExpired($query) 
    { 
        return $query->where('expiry_date', '<', now()); 
    }

    /**
     * Get statistics for prescriptions
     */
    public static function getStatistics($startDate = null, $endDate = null)
    {
        $query = self::query();
        
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }
        
        return [
            'total' => $query->count(),
            'pending' => $query->where('status', 'pending')->count(),
            'partially_delivered' => $query->where('status', 'partially_delivered')->count(),
            'completed' => $query->where('status', 'completed')->count(),
            'expired' => $query->where('status', 'expired')->count(),
        ];
    }

    /**
     * Check if prescription can be modified
     */
    public function canBeModified()
    {
        return !in_array($this->status, ['completed', 'expired']);
    }

    /**
     * Check if prescription can be delivered
     */
    public function canBeDelivered()
    {
        return $this->status !== 'completed' && !$this->isExpired();
    }

    /**
     * Get remaining items to deliver
     */
    public function getRemainingItemsAttribute()
    {
        return $this->prescriptionItems()
            ->whereRaw('quantity_delivered < quantity_prescribed')
            ->get();
    }

    /**
     * Calculate total prescription value (if prices are available)
     */
    public function getTotalValueAttribute()
    {
        return $this->prescriptionItems()
            ->join('products', 'prescription_items.product_id', '=', 'products.id')
            ->sum(\DB::raw('prescription_items.quantity_prescribed * products.sale_price'));
    }

    /**
     * Calculate total delivered value
     */
    public function getDeliveredValueAttribute()
    {
        return $this->prescriptionItems()
            ->join('products', 'prescription_items.product_id', '=', 'products.id')
            ->sum(\DB::raw('prescription_items.quantity_delivered * products.sale_price'));
    }
}