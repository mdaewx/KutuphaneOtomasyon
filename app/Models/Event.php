<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'date',
        'location',
        'category_id',
        'publisher_id'
    ];

    protected $casts = [
        'date' => 'date'
    ];

    /**
     * Bu etkinliğin bağlı olduğu kategori
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Bu etkinliği düzenleyen yayınevi
     */
    public function publisher()
    {
        return $this->belongsTo(Publisher::class);
    }
}
