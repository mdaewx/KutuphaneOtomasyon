<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'barcode',
        'status',
        'condition',
        'is_available',
        'shelf_id',
        'acquisition_source_id',
        'acquisition_date',
        'acquisition_price',
        'notes'
    ];

    protected $attributes = [
        'status' => 'available',
        'is_available' => true,
        'condition' => 'new'
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'acquisition_date' => 'date',
        'acquisition_price' => 'decimal:2'
    ];

    // İsimlendirme sabitleri ekleyelim
    const STATUS_AVAILABLE = 'available'; // Stokta, kullanılabilir
    const STATUS_BORROWED = 'borrowed';   // Ödünç verilmiş
    const STATUS_RESERVED = 'reserved';   // Rezerve edilmiş
    const STATUS_LOST = 'lost';           // Kayıp
    const STATUS_DAMAGED = 'damaged';     // Hasarlı

    const CONDITION_NEW = 'new';     // Yeni
    const CONDITION_GOOD = 'good';   // İyi
    const CONDITION_FAIR = 'fair';   // Orta
    const CONDITION_POOR = 'poor';   // Kötü

    /**
     * Bu stok kaydının ait olduğu kitap
     */
    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    // Raf ilişkisi
    public function shelf()
    {
        return $this->belongsTo(Shelf::class);
    }

    // Edinme kaynağı ilişkisi
    public function acquisitionSource()
    {
        return $this->belongsTo(AcquisitionSource::class);
    }

    // Ödünç verme ilişkisi
    public function borrowings()
    {
        return $this->hasMany(Borrowing::class);
    }

    public function activeBorrowing()
    {
        return $this->hasOne(Borrowing::class)->whereNull('returned_at');
    }

    // Kullanılabilirlik durumu kontrolü
    public function isAvailable()
    {
        return $this->status === 'available' && $this->is_available;
    }

    // Durumun okunabilir Türkçe adını döndüren metot
    public function getStatusTextAttribute()
    {
        return match($this->status) {
            'available' => 'Müsait',
            'borrowed' => 'Ödünç Verilmiş',
            'reserved' => 'Rezerve Edilmiş',
            'damaged' => 'Hasarlı',
            'lost' => 'Kayıp',
            default => 'Bilinmiyor'
        };
    }

    // Fiziksel durumun okunabilir Türkçe adını döndüren metot
    public function getConditionTextAttribute()
    {
        return match($this->condition) {
            'new' => 'Yeni',
            'good' => 'İyi',
            'fair' => 'Orta',
            'poor' => 'Kötü',
            default => 'Bilinmiyor'
        };
    }

    /**
     * Ensure condition is always saved using a valid value
     */
    public function setConditionAttribute($value)
    {
        // If Yeni is passed, transform to new
        if ($value === 'Yeni') {
            $this->attributes['condition'] = 'new';
        } 
        // Check if it's already one of our valid values
        else if (in_array($value, [self::CONDITION_NEW, self::CONDITION_GOOD, self::CONDITION_FAIR, self::CONDITION_POOR])) {
            $this->attributes['condition'] = $value;
        }
        // Default to 'new' for any other value
        else {
            $this->attributes['condition'] = 'new';
        }
    }
}
