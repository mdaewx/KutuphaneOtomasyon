<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'category_id',
        'publisher_id',
        'isbn',
        'page_count',
        'language',
        'publication_year',
        'description',
        'cover_image',
        'shelf_id'
    ];

    protected $casts = [
        'publication_year' => 'integer',
        'page_count' => 'integer'
    ];

    // Ödünç verme işlemleri
    public function borrowings()
    {
        return $this->hasMany(Borrowing::class);
    }

    // Kitabın aktif ödünçleri
    public function activeBorrowings()
    {
        return $this->hasMany(Borrowing::class)->whereNull('returned_at');
    }

    // Kategori ilişkisi
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Yayıncı ilişkisi
    public function publisher()
    {
        return $this->belongsTo(Publisher::class, 'publisher_id');
    }

    // Helper method for getting publisher name safely
    public function getPublisherNameAttribute()
    {
        // Use the relationship, if it works correctly
        if ($this->publisher) {
            return $this->publisher->name;
        }
        
        // Direct database query as fallback
        if ($this->publisher_id) {
            $publisher = \DB::table('publishers')->where('id', $this->publisher_id)->first();
            if ($publisher) {
                return $publisher->name;
            }
        }
        
        // Default value if no publisher
        return 'Belirtilmemiş';
    }

    // Yazarlar çoklu ilişkisi
    public function authors()
    {
        return $this->belongsToMany(Author::class);
    }

    // Stok bilgileri
    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

    // Cezalar ilişkisi
    public function fines()
    {
        return $this->hasMany(Fine::class);
    }

    // Favori kitaplar ilişkisi
    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'favorite_books');
    }

    // Önerilen kitaplar ilişkisi
    public function suggestedTo()
    {
        return $this->belongsToMany(User::class, 'suggested_books');
    }

    /**
     * Kitabın kullanılabilir olup olmadığını kontrol eder
     */
    public function isAvailable()
    {
        return $this->getAvailableQuantityAttribute() > 0;
    }

    /**
     * Kullanılabilir stok miktarını hesaplar
     */
    public function getAvailableQuantityAttribute()
    {
        return $this->stocks()
            ->where('is_available', true)
            ->whereDoesntHave('borrowings', function($query) {
                $query->whereNull('returned_at');
            })
            ->count();
    }

    /**
     * Toplam stok miktarını hesaplar
     */
    public function getTotalQuantityAttribute()
    {
        return $this->stocks()->count();
    }

    // Raf ilişkisi
    public function shelf()
    {
        return $this->belongsTo(Shelf::class);
    }

    public function acquisitionSources()
    {
        return $this->hasMany(AcquisitionSource::class);
    }
}
