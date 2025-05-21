<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Author extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'surname',
        'biography',
        'birth_date',
        'death_date'
    ];

    protected $dates = [
        'birth_date',
        'death_date'
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
