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
        'isbn',
        'shelf_id',
        'acquisition_source_id',
        'acquisition_date',
        'acquisition_price',
        'is_available',
        'quantity',
        'condition',  // fiziksel durum
        'status'      // ödünç durumu
    ];

    protected $casts = [
        'acquisition_date' => 'date',
        'acquisition_price' => 'decimal:2',
        'is_available' => 'boolean',
        'quantity' => 'integer'
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

    // Aktif ödünç verme kontrolü
    public function isCurrentlyBorrowed()
    {
        $borrowed = $this->borrowings()->whereNull('returned_at')->exists();
        
        // Ödünç verildiyse status'u da güncelleyelim
        if ($borrowed && $this->status !== self::STATUS_BORROWED) {
            $this->update(['status' => self::STATUS_BORROWED]);
        }
        
        return $borrowed;
    }

    // Kullanılabilirlik durumu kontrolü
    public function isAvailable()
    {
        // Eğer bir ödünç verme kaydı varsa ve iade edilmemişse, kitap mevcut değildir
        $activeBorrowing = $this->borrowings()->whereNull('returned_at')->exists();
        
        if ($activeBorrowing) {
            // Kitap ödünç verilmiş, mevcut değil
            if ($this->status !== self::STATUS_BORROWED) {
                $this->update(['status' => self::STATUS_BORROWED]);
            }
            return false;
        }
        
        // Kitap ödünç verilmemişse ve hasar/kayıp durumunda değilse, mevcuttur
        if ($this->status !== self::STATUS_LOST && $this->status !== self::STATUS_DAMAGED) {
            if ($this->status !== self::STATUS_AVAILABLE) {
                $this->update(['status' => self::STATUS_AVAILABLE, 'is_available' => true]);
            }
            return true;
        }
        
        // Kitap kayıp veya hasarlı ise kullanılamaz
        return false;
    }

    // Durumun okunabilir Türkçe adını döndüren metot
    public function getStatusLabelAttribute()
    {
        switch ($this->status) {
            case self::STATUS_AVAILABLE:
                return 'Stokta';
            case self::STATUS_BORROWED:
                return 'Ödünç Verildi';
            case self::STATUS_RESERVED:
                return 'Rezerve Edildi';
            case self::STATUS_LOST:
                return 'Kayıp';
            case self::STATUS_DAMAGED:
                return 'Hasarlı';
            default:
                return $this->status;
        }
    }

    // Fiziksel durumun okunabilir Türkçe adını döndüren metot
    public function getConditionLabelAttribute()
    {
        switch ($this->condition) {
            case self::CONDITION_NEW:
                return 'Yeni';
            case self::CONDITION_GOOD:
                return 'İyi';
            case self::CONDITION_FAIR:
                return 'Orta';
            case self::CONDITION_POOR:
                return 'Kötü';
            default:
                return $this->condition;
        }
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
