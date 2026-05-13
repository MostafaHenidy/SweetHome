<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number', 'type', 'user_id', 'customer_id', 'customer_name', 'customer_email',
        'customer_phone', 'customer_company_name', 'customer_vat_number', 'customer_tax_code',
        'shipping_address', 'billing_address',
        'subtotal', 'shipping_cost', 'tax', 'coupon_id', 'coupon_code',
        'discount_amount', 'total',
        'status', 'payment_method', 'payment_status', 'payment_transaction_id',
        'notes', 'quote_expires_at', 'quote_pdf_path',
        'quote_document_number', 'quote_document_year',
        'order_confirmation_pdf_path', 'invoice_pdf_path',
        'invoice_document_number', 'invoice_document_year',
        // New fields for enhanced order system
        'shipping_method', 'service_type', 'payment_plan_type', 'deposit_amount',
        // VIP system fields
        'is_vip_order', 'points_earned', 'points_redeemed', 'buy_buy_dept',
    ];

    protected $casts = [
        'shipping_address' => 'array',
        'billing_address' => 'array',
        'subtotal' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'quote_expires_at' => 'datetime',
        'quote_document_number' => 'integer',
        'quote_document_year' => 'integer',
        'invoice_document_number' => 'integer',
        'invoice_document_year' => 'integer',
        'is_vip_order' => 'boolean',
        'points_earned' => 'decimal:2',
        'points_redeemed' => 'decimal:2',
    ];

    public function formattedQuoteDocumentReference(): ?string
    {
        if ($this->quote_document_number === null || $this->quote_document_year === null) {
            return null;
        }

        return $this->quote_document_number.' / '.$this->quote_document_year;
    }

    public function formattedInvoiceDocumentReference(): ?string
    {
        if ($this->invoice_document_number === null || $this->invoice_document_year === null) {
            return null;
        }

        return $this->invoice_document_number.' / '.$this->invoice_document_year;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function pointsTransactions(): HasMany
    {
        return $this->hasMany(PointsTransaction::class);
    }

    public function couponUsage(): HasOne
    {
        return $this->hasOne(CouponUsage::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function paymentInstallments(): HasMany
    {
        return $this->hasMany(PaymentInstallment::class)->orderBy('installment_number');
    }

    public function getFormattedTotalAttribute(): string
    {
        return '€'.number_format($this->total, 2, ',', '.');
    }

    public function isQuoteRequest(): bool
    {
        return $this->type === 'quote_request';
    }

    public function isDirectPurchase(): bool
    {
        return $this->type === 'direct';
    }

    public function isQuoteExpired(): bool
    {
        return $this->quote_expires_at && $this->quote_expires_at->isPast();
    }

    public function canBeAccepted(): bool
    {
        return $this->status === 'quote_sent' && ! $this->isQuoteExpired();
    }

    // Enhanced order system helper methods
    public function isDelivery(): bool
    {
        return $this->shipping_method === 'delivery';
    }

    public function isPickup(): bool
    {
        return $this->shipping_method === 'pickup';
    }

    public function hasConstruction(): bool
    {
        return $this->service_type === 'shipping_and_construction';
    }

    public function getPendingInstallments()
    {
        return $this->paymentInstallments->where('status', 'pending');
    }

    public function getPaidInstallments()
    {
        return $this->paymentInstallments->where('status', 'paid');
    }

    public function getNextPendingInstallment()
    {
        return $this->paymentInstallments->where('status', 'pending')->first();
    }

    public function getPaymentPlanTypeLabel(): string
    {
        return match ($this->payment_plan_type) {
            '50-50' => '50-50 Payment Plan',
            '50-30-20' => '50-30-20 Payment Plan',
            default => 'Payment Plan',
        };
    }

    public function getShippingMethodLabel(): string
    {
        return match ($this->shipping_method) {
            'delivery' => 'Delivery',
            'pickup' => 'Pickup',
            default => ucfirst($this->shipping_method),
        };
    }

    public function getServiceTypeLabel(): string
    {
        return match ($this->service_type) {
            'shipping_only' => 'Shipping Only',
            'shipping_and_construction' => 'Shipping & Construction',
            default => ucfirst($this->service_type),
        };
    }

    /**
     * VIP Order Methods
     */
    public function isVipOrder(): bool
    {
        return (bool) $this->is_vip_order;
    }

    public function hasBuyBuyDept(): bool
    {
        return !empty($this->buy_buy_dept);
    }

    public function getBuyBuyDeptAttribute(): ?string
    {
        return $this->attributes['buy_buy_dept'] ?? null;
    }

    public function setBuyBuyDeptAttribute(?string $value): void
    {
        $this->attributes['buy_buy_dept'] = $value;
    }
}
