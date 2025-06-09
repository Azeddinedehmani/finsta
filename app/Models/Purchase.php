<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'supplier_id',
        'user_id',
        'purchase_number',
        'status',
        'subtotal',
        'tax_amount',
        'total_amount',
        'order_date',
        'expected_date',
        'received_date',
        'notes',
        'received_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'order_date' => 'date',
        'expected_date' => 'date',
        'received_date' => 'date',
    ];

    /**
     * Boot method to generate purchase number.
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($purchase) {
            if (!$purchase->purchase_number) {
                $todayPurchasesCount = Purchase::whereDate('created_at', today())->count();
                $purchase->purchase_number = 'ACH-' . date('Ymd') . '-' . str_pad($todayPurchasesCount + 1, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    /**
     * Get the supplier that owns the purchase.
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the user that created the purchase.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user that received the purchase.
     */
    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    /**
     * Get the purchase items for the purchase.
     */
    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    /**
     * Calculate and update totals.
     */
    public function calculateTotals()
    {
        $this->subtotal = $this->purchaseItems()->sum('total_price');
        $this->tax_amount = $this->subtotal * 0.20; // 20% tax
        $this->total_amount = $this->subtotal + $this->tax_amount;
        $this->save();
    }

    /**
     * Update status based on received quantities.
     */
    public function updateStatus()
    {
        $totalOrdered = $this->purchaseItems()->sum('quantity_ordered');
        $totalReceived = $this->purchaseItems()->sum('quantity_received');
        
        if ($totalReceived === 0) {
            $this->status = 'pending';
        } elseif ($totalReceived < $totalOrdered) {
            $this->status = 'partially_received';
        } else {
            $this->status = 'received';
            if (!$this->received_date) {
                $this->received_date = now();
                $this->received_by = auth()->id();
            }
        }
        
        $this->save();
    }

    /**
     * Get status badge class.
     */
    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'pending' => 'bg-warning text-dark',
            'partially_received' => 'bg-info text-white',
            'received' => 'bg-success',
            'cancelled' => 'bg-danger',
            default => 'bg-secondary'
        };
    }

    /**
     * Get status label.
     */
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'pending' => 'En attente',
            'partially_received' => 'Partiellement reçu',
            'received' => 'Reçu',
            'cancelled' => 'Annulé',
            default => 'Inconnu'
        };
    }

    /**
     * Check if purchase is fully received.
     */
    public function isFullyReceived()
    {
        return $this->purchaseItems()->whereColumn('quantity_received', '<', 'quantity_ordered')->count() === 0;
    }

    /**
     * Check if purchase is partially received.
     */
    public function isPartiallyReceived()
    {
        return $this->purchaseItems()->where('quantity_received', '>', 0)->count() > 0 && !$this->isFullyReceived();
    }

    /**
     * Get total items count.
     */
    public function getTotalItemsAttribute()
    {
        return $this->purchaseItems()->sum('quantity_ordered');
    }

    /**
     * Get received items count.
     */
    public function getReceivedItemsAttribute()
    {
        return $this->purchaseItems()->sum('quantity_received');
    }

    /**
     * Get progress percentage.
     */
    public function getProgressPercentageAttribute()
    {
        if ($this->total_items === 0) return 0;
        return round(($this->received_items / $this->total_items) * 100, 1);
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeReceived($query)
    {
        return $query->where('status', 'received');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'pending')
                    ->where('expected_date', '<', now());
    }
}