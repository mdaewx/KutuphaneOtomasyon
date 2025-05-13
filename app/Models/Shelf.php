<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shelf extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'shelf_number',
        'description',
        'capacity',
        'location',
        'status'
    ];

    protected $casts = [
        'capacity' => 'integer',
        'status' => 'string'
    ];

    /**
     * Bu rafta bulunan kitaplar
     */
    public function books()
    {
        return $this->hasMany(Book::class);
    }

    /**
     * Bu rafta bulunan stok kayıtları
     */
    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

    /**
     * Rafın dolu olup olmadığını kontrol et
     */
    public function isFull()
    {
        return $this->stocks()->count() >= $this->capacity;
    }

    /**
     * Rafın aktif olup olmadığını kontrol et
     */
    public function isActive()
    {
        return $this->status === 'active';
    }

    /**
     * Rafın mevcut doluluk oranını hesapla
     */
    public function getOccupancyRate()
    {
        if ($this->capacity <= 0) return 0;
        return ($this->stocks()->count() / $this->capacity) * 100;
    }

    /**
     * Rafta kalan boş alan sayısını hesapla
     */
    public function getAvailableSpace()
    {
        return max(0, $this->capacity - $this->stocks()->count());
    }
}
