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
}
