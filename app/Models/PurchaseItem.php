<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'purchase_id',
        'product_id',
        'quantity_ordered',
        'quantity_received',
        'unit_price',
        'total_price',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'quantity_ordered' => 'integer',
        'quantity_received' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    /**
     * Boot method to calculate total price and update purchase status.
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($purchaseItem) {
            if (!$purchaseItem->total_price) {
                $purchaseItem->total_price = $purchaseItem->quantity_ordered * $purchaseItem->unit_price;
            }
        });

        static::updating(function ($purchaseItem) {
            if ($purchaseItem->isDirty(['quantity_ordered', 'unit_price'])) {
                $purchaseItem->total_price = $purchaseItem->quantity_ordered * $purchaseItem->unit_price;
            }
        });

        static::saved(function ($purchaseItem) {
            $purchaseItem->purchase->updateStatus();
        });
    }

    /**
     * Get the purchase that owns the purchase item.
     */
    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    /**
     * Get the product that owns the purchase item.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get remaining quantity to receive.
     */
    public function getRemainingQuantityAttribute()
    {
        return $this->quantity_ordered - $this->quantity_received;
    }

    /**
     * Check if item is fully received.
     */
    public function isFullyReceived()
    {
        return $this->quantity_received >= $this->quantity_ordered;
    }

    /**
     * Check if item is partially received.
     */
    public function isPartiallyReceived()
    {
        return $this->quantity_received > 0 && $this->quantity_received < $this->quantity_ordered;
    }

    /**
     * Get progress percentage for this item.
     */
    public function getProgressPercentageAttribute()
    {
        if ($this->quantity_ordered === 0) return 0;
        return round(($this->quantity_received / $this->quantity_ordered) * 100, 1);
    }
}