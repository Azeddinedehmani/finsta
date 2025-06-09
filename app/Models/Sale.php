<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'client_id',
        'user_id',
        'sale_number',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'payment_method',
        'payment_status',
        'has_prescription',
        'prescription_number',
        'notes',
        'sale_date',
        'client_name_at_deletion',
        'deleted_client_data',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'has_prescription' => 'boolean',
        'sale_date' => 'datetime',
        'deleted_client_data' => 'array',
    ];

    /**
     * Boot method to generate sale number.
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($sale) {
            if (!$sale->sale_number) {
                // Generate a unique sale number
                $todaySalesCount = Sale::whereDate('created_at', today())->count();
                $sale->sale_number = 'VTE-' . date('Ymd') . '-' . str_pad($todaySalesCount + 1, 4, '0', STR_PAD_LEFT);
            }
            
            if (!$sale->sale_date) {
                $sale->sale_date = now();
            }
        });
    }

    /**
     * Get the client that owns the sale.
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the user (pharmacist) that made the sale.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the sale items for the sale.
     */
    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * Get the products sold in this sale.
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'sale_items')
                    ->withPivot('quantity', 'unit_price', 'total_price');
    }

    /**
     * Calculate and update totals.
     */
    public function calculateTotals()
    {
        $this->subtotal = $this->saleItems()->sum('total_price');
        $this->tax_amount = $this->subtotal * 0.20; // 20% tax
        $this->total_amount = $this->subtotal + $this->tax_amount - ($this->discount_amount ?? 0);
        $this->save();
    }

    /**
     * Get payment status badge class.
     */
    public function getPaymentStatusBadgeAttribute()
    {
        return match($this->payment_status) {
            'paid' => 'bg-success',
            'pending' => 'bg-warning text-dark',
            'failed' => 'bg-danger',
            default => 'bg-secondary'
        };
    }

    /**
     * Get payment method label.
     */
    public function getPaymentMethodLabelAttribute()
    {
        return match($this->payment_method) {
            'cash' => 'Espèces',
            'card' => 'Carte bancaire',
            'insurance' => 'Assurance',
            'other' => 'Autre',
            default => ucfirst($this->payment_method)
        };
    }

    /**
     * Get payment status label.
     */
    public function getPaymentStatusLabelAttribute()
    {
        return match($this->payment_status) {
            'paid' => 'Payé',
            'pending' => 'En attente',
            'failed' => 'Échoué',
            default => ucfirst($this->payment_status)
        };
    }

    /**
     * Check if sale has prescription products.
     */
    public function hasPrescriptionProducts()
    {
        return $this->saleItems()
                   ->whereHas('product', function($query) {
                       $query->where('prescription_required', true);
                   })->exists();
    }

    /**
     * Get total items count.
     */
    public function getTotalItemsAttribute()
    {
        return $this->saleItems()->sum('quantity');
    }

    /**
     * Get client display name (current or deleted).
     */
    public function getClientDisplayNameAttribute()
    {
        if ($this->client) {
            return $this->client->full_name;
        }
        
        if ($this->client_name_at_deletion) {
            return $this->client_name_at_deletion . ' (supprimé)';
        }
        
        if ($this->deleted_client_data && isset($this->deleted_client_data['name'])) {
            return $this->deleted_client_data['name'] . ' (supprimé)';
        }
        
        return 'Client inconnu';
    }

    /**
     * Get client contact info (current or deleted).
     */
    public function getClientContactInfoAttribute()
    {
        if ($this->client) {
            return [
                'email' => $this->client->email,
                'phone' => $this->client->phone,
                'status' => 'active'
            ];
        }
        
        if ($this->deleted_client_data) {
            return [
                'email' => $this->deleted_client_data['email'] ?? null,
                'phone' => $this->deleted_client_data['phone'] ?? null,
                'status' => 'deleted',
                'deleted_at' => $this->deleted_client_data['deleted_at'] ?? null
            ];
        }
        
        return [
            'email' => null,
            'phone' => null,
            'status' => 'unknown'
        ];
    }

    /**
     * Check if this sale belongs to a deleted client.
     */
    public function isOrphanedSale()
    {
        return is_null($this->client_id) && (
            !is_null($this->client_name_at_deletion) || 
            !is_null($this->deleted_client_data)
        );
    }

    /**
     * Check if this sale has no client data at all.
     */
    public function hasNoClientData()
    {
        return is_null($this->client_id) && 
               is_null($this->client_name_at_deletion) && 
               is_null($this->deleted_client_data);
    }

    /**
     * Scope for today's sales.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('sale_date', today());
    }

    /**
     * Scope for sales with prescription.
     */
    public function scopeWithPrescription($query)
    {
        return $query->where('has_prescription', true);
    }

    /**
     * Scope for paid sales.
     */
    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    /**
     * Scope for orphaned sales (deleted clients).
     */
    public function scopeOrphaned($query)
    {
        return $query->whereNull('client_id')
                    ->where(function($q) {
                        $q->whereNotNull('client_name_at_deletion')
                          ->orWhereNotNull('deleted_client_data');
                    });
    }

    /**
     * Scope for sales with active clients.
     */
    public function scopeWithActiveClient($query)
    {
        return $query->whereNotNull('client_id')
                    ->whereHas('client');
    }

    /**
     * Scope for sales without any client data.
     */
    public function scopeWithoutClientData($query)
    {
        return $query->whereNull('client_id')
                    ->whereNull('client_name_at_deletion')
                    ->whereNull('deleted_client_data');
    }
}