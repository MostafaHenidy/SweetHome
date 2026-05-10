<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentInstallment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'installment_number',
        'amount',
        'percentage',
        'due_stage',
        'status',
        'payment_transaction_id',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'percentage' => 'integer',
        'paid_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function getFormattedAmountAttribute(): string
    {
        return '€' . number_format($this->amount, 2, ',', '.');
    }

    public function getDueStageLabelAttribute(): string
    {
        return match ($this->due_stage) {
            'deposit' => 'Deposit',
            'on_delivery' => 'On Delivery',
            'on_construction' => 'After Construction',
            default => ucfirst($this->due_stage),
        };
    }
}