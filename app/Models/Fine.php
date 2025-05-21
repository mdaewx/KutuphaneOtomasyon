<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Fine extends Model
{
    use HasFactory;

    protected $fillable = [
        'borrowing_id',
        'amount',
        'payment_status',
        'payment_method',
        'payment_reference',
        'paid_at',
        'approved_at',
        'approved_by',
        'admin_notes'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'approved_at' => 'datetime'
    ];

    /**
     * Bu cezanın ait olduğu ödünç kaydı
     */
    public function borrowing(): BelongsTo
    {
        return $this->belongsTo(Borrowing::class);
    }

    /**
     * Bu cezayı tahsil eden personel
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Ceza ödenmiş mi kontrolü
     */
    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    /**
     * Ceza bekliyor mu kontrolü
     */
    public function isPending(): bool
    {
        return $this->payment_status === 'pending';
    }

    /**
     * Ceza iptal mi kontrolü
     */
    public function isCancelled(): bool
    {
        return $this->payment_status === 'cancelled';
    }

    /**
     * Ceza miktarını formatlı göster
     */
    public function getFormattedAmountAttribute()
    {
        return number_format($this->amount, 2) . ' TL';
    }
}
