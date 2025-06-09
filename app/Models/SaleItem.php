<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sale_id',
        'product_id',
        'quantity',
        'unit_price',
        'total_price',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    /**
     * Boot method to calculate total price.
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($saleItem) {
            // Calculate total price if not already set
            if (!$saleItem->total_price) {
                $saleItem->total_price = $saleItem->quantity * $saleItem->unit_price;
            }
        });

        static::updating(function ($saleItem) {
            // Recalculate total price if quantity or unit_price changed
            if ($saleItem->isDirty(['quantity', 'unit_price'])) {
                $saleItem->total_price = $saleItem->quantity * $saleItem->unit_price;
            }
        });

        // Note: We don't auto-update sale totals here to avoid infinite loops
        // The controller should handle updating sale totals after all items are processed
    }

    /**
     * Get the sale that owns the sale item.
     */
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Get the product that owns the sale item.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the line total (alias for total_price for compatibility).
     */
    public function getLineTotalAttribute()
    {
        return $this->total_price;
    }

    /**
     * Calculate savings if there was a discount.
     */
    public function getSavingsAttribute()
    {
        $regularPrice = $this->quantity * $this->product->selling_price;
        return $regularPrice - $this->total_price;
    }

    /**
     * Check if this item has a discount.
     */
    public function hasDiscount()
    {
        return $this->unit_price < $this->product->selling_price;
    }

    /**
     * Get discount percentage.
     */
    public function getDiscountPercentageAttribute()
    {
        if (!$this->hasDiscount()) {
            return 0;
        }
        
        $originalPrice = $this->product->selling_price;
        return round((($originalPrice - $this->unit_price) / $originalPrice) * 100, 2);
    }
}