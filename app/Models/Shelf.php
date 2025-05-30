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
        'code',
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
     * Model boot metodu
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($shelf) {
            if (empty($shelf->shelf_number)) {
                // En son raf numarasını bul
                $lastShelf = static::orderBy('id', 'desc')->first();
                $nextId = $lastShelf ? $lastShelf->id + 1 : 1;
                $shelf->shelf_number = 'RAF-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);
            }
            
            // Code alanını shelf_number ile aynı yap
            if (empty($shelf->code)) {
                $shelf->code = $shelf->shelf_number;
            }
        });
    }

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
