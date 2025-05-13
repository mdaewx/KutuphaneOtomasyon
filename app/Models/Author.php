<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'surname'
    ];

    /**
     * Bu yazara ait kitaplar
     */
    public function books()
    {
        return $this->belongsToMany(Book::class);
    }

    /**
     * Yazarın tam adını döndürür
     */
    public function getFullNameAttribute()
    {
        return $this->name . ' ' . $this->surname;
    }
}
