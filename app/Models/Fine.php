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
        'user_id',
        'book_id',
        'amount',
        'reason',
        'status',
        'paid_at',
        'payment_method',
        'collected_by',
        'payment_notes',
        'days_late',
        'paid_amount'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'days_late' => 'integer',
        'paid_amount' => 'decimal:2'
    ];

    protected $attributes = [
        'paid_amount' => 0.00,
        'status' => 'pending'
    ];

    // Fine types
    const TYPE_DAMAGE = 'damage';
    const TYPE_LOST = 'lost';
    const TYPE_LATE = 'late';

    // Fine statuses
    const STATUS_PENDING = 'pending';
    const STATUS_PAID = 'paid';
    const STATUS_CANCELLED = 'cancelled';

    // Payment methods
    const PAYMENT_METHOD_CASH = 'cash';
    const PAYMENT_METHOD_CREDIT_CARD = 'credit_card';
    const PAYMENT_METHOD_BANK_TRANSFER = 'bank_transfer';

    /**
     * Bu cezanın ait olduğu ödünç kaydı
     */
    public function borrowing()
    {
        return $this->belongsTo(Borrowing::class);
    }

    /**
     * Bu cezanın ait olduğu kullanıcı
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Bu cezanın ait olduğu kitap
     */
    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * Cezayı toplayan personel
     */
    public function collector()
    {
        return $this->belongsTo(User::class, 'collected_by');
    }

    /**
     * Ceza türü etiketi
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->reason) {
            self::TYPE_DAMAGE => 'Hasar Cezası',
            self::TYPE_LOST => 'Kayıp Cezası',
            self::TYPE_LATE => 'Gecikme Cezası',
            default => 'Diğer'
        };
    }

    /**
     * Get the status of the fine
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Get the status label
     */
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            self::STATUS_PAID => 'Ödendi',
            self::STATUS_CANCELLED => 'İptal Edildi',
            default => 'Ödenmedi'
        };
    }

    /**
     * Ödeme yöntemi etiketi
     */
    public function getPaymentMethodLabelAttribute()
    {
        if (!$this->payment_method) {
            return '-';
        }

        return match($this->payment_method) {
            self::PAYMENT_METHOD_CASH => 'Nakit',
            self::PAYMENT_METHOD_CREDIT_CARD => 'Kredi Kartı',
            self::PAYMENT_METHOD_BANK_TRANSFER => 'Banka Havalesi',
            default => $this->payment_method
        };
    }

    /**
     * Check if fine is pending
     */
    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if fine is paid
     */
    public function isPaid()
    {
        return $this->status === self::STATUS_PAID;
    }

    /**
     * Check if fine is cancelled
     */
    public function isCancelled()
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Cezayı öde
     */
    public function pay($amount, $paymentMethod, $collectedBy, $notes = null)
    {
        $this->paid_amount = $amount;
        $this->payment_method = $paymentMethod;
        $this->collected_by = $collectedBy;
        $this->payment_notes = $notes;
        $this->paid_at = now();
        $this->status = self::STATUS_PAID;
        return $this->save();
    }

    /**
     * Cezayı iptal et
     */
    public function cancel($notes = null)
    {
        $this->status = self::STATUS_CANCELLED;
        $this->payment_notes = $notes;
        return $this->save();
    }

    /**
     * Kalan ödeme tutarını hesapla
     */
    public function getRemainingAmount()
    {
        return $this->amount - $this->paid_amount;
    }
}
