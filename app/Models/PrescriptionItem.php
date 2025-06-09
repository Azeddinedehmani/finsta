<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrescriptionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'prescription_id',
        'product_id',
        'quantity_prescribed',
        'quantity_delivered',
        'dosage_instructions',
        'duration_days',
        'instructions',
        'is_substitutable',
        'substitute_product_id',
        'substitute_reason',
    ];

    protected $casts = [
        'is_substitutable' => 'boolean',
        'quantity_prescribed' => 'integer',
        'quantity_delivered' => 'integer',
        'duration_days' => 'integer',
    ];

    // Relationships
    public function prescription()
    {
        return $this->belongsTo(Prescription::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed(); // Include soft-deleted products
    }

    public function substituteProduct()
    {
        return $this->belongsTo(Product::class, 'substitute_product_id')->withTrashed();
    }

    // Status methods
    public function isFullyDelivered()
    {
        return $this->quantity_delivered >= $this->quantity_prescribed;
    }

    public function isPartiallyDelivered()
    {
        return $this->quantity_delivered > 0 && $this->quantity_delivered < $this->quantity_prescribed;
    }

    public function isPending()
    {
        return $this->quantity_delivered == 0;
    }

    public function getRemainingQuantityAttribute()
    {
        return max(0, $this->quantity_prescribed - $this->quantity_delivered);
    }

    public function getDeliveryPercentageAttribute()
    {
        if ($this->quantity_prescribed == 0) {
            return 0;
        }
        
        return round(($this->quantity_delivered / $this->quantity_prescribed) * 100, 1);
    }

    // Helper methods for handling missing products
    public function getProductNameAttribute()
    {
        if ($this->product) {
            return $this->product->name;
        }
        
        return "Produit non disponible (ID: {$this->product_id})";
    }

    public function getProductDosageAttribute()
    {
        if ($this->product && $this->product->dosage) {
            return $this->product->dosage;
        }
        
        return null;
    }

    public function hasValidProduct()
    {
        return $this->product !== null;
    }

    public function canBeDelivered()
    {
        return $this->hasValidProduct() && 
               !$this->isFullyDelivered() && 
               !$this->prescription->isExpired();
    }

    // Scopes
    public function scopeFullyDelivered($query)
    {
        return $query->whereColumn('quantity_delivered', '>=', 'quantity_prescribed');
    }

    public function scopePartiallyDelivered($query)
    {
        return $query->where('quantity_delivered', '>', 0)
                    ->whereColumn('quantity_delivered', '<', 'quantity_prescribed');
    }

    public function scopePending($query)
    {
        return $query->where('quantity_delivered', 0);
    }

    public function scopeWithValidProduct($query)
    {
        return $query->whereHas('product');
    }

    public function scopeWithMissingProduct($query)
    {
        return $query->whereDoesntHave('product');
    }
}