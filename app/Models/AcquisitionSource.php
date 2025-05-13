<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcquisitionSource extends Model
{
    use HasFactory;

    protected $fillable = [

        'source_name',

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
    public function sourceType()
    {
        return $this->belongsTo(AcquisitionSourceType::class, 'source_type_id');
    }

    /**
     * Bu edinme kaynağına ait stoklar
     */
    public function stocks()
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
