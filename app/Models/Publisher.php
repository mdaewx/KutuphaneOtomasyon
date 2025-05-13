<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Publisher extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'phone'
    ];

    /**
     * Bu yayınevine ait kitaplar
     */
    public function books()
    {
        return $this->hasMany(Book::class);
    }

    /**
     * Yayınevinin düzenlediği etkinlikler
     */
    public function events()
    {
        return $this->hasMany(Event::class);
    }
}
