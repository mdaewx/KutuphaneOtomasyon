<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcquisitionSource extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'source_name',
        'source_type_id',
        'contact_person',
        'contact_email',
        'contact_phone',
        'address',
        'notes'
    ];

    /**
     * Bu edinme kaynağının ait olduğu kitap
     */
    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * Bu edinme kaynağının türü
     */
    public function sourceType(): BelongsTo
    {
        return $this->belongsTo(AcquisitionSourceType::class, 'source_type_id');
    }

    /**
     * Bu edinme kaynağına ait stoklar
     */
    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    /**
     * Bu edinme kaynağının yazarları
     */
    public function authors()
    {
        return $this->belongsToMany(Author::class, 'acquisition_source_author');
    }

    /**
     * Model kaydedilmeden önce
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($acquisitionSource) {
            // Eğer source_name varsa ve name boşsa, source_name'i name'e kopyala
            if (!empty($acquisitionSource->source_name) && empty($acquisitionSource->name)) {
                $acquisitionSource->name = $acquisitionSource->source_name;
            }
            // Eğer name varsa ve source_name boşsa, name'i source_name'e kopyala
            elseif (!empty($acquisitionSource->name) && empty($acquisitionSource->source_name)) {
                $acquisitionSource->source_name = $acquisitionSource->name;
            }
        });
    }
}
