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
        'days_late',
        'fine_amount',
        'paid'
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
}
