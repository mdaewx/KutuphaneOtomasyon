<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fine extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'book_id',
        'borrowing_id',
        'days_late',
        'fine_amount',
        'paid',
        'paid_at',
        'payment_method',
        'payment_notes',
        'collected_by'
    ];

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
     * Bu cezanın ait olduğu ödünç kaydı
     */
    public function borrowing()
    {
        return $this->belongsTo(Borrowing::class);
    }

    /**
     * Bu cezayı tahsil eden personel
     */
    public function collector()
    {
        return $this->belongsTo(User::class, 'collected_by');
    }

    /**
     * Ceza ödenmiş mi kontrolü
     */
    public function isPaid()
    {
        return $this->paid;
    }

    /**
     * Ceza miktarını formatlı göster
     */
    public function getFormattedAmountAttribute()
    {
        return number_format($this->fine_amount, 2) . ' TL';
    }
}
