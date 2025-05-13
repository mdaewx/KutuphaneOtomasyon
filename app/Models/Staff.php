<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    use HasFactory;

    protected $table = 'staff';

    protected $fillable = [
        'name',
        'surname',
        'email',
        'phone',
        'position',
        'hire_date'
    ];

    protected $casts = [
        'hire_date' => 'date'
    ];

    /**
     * Personelin tam adını döndürür
     */
    public function getFullNameAttribute()
    {
        return $this->name . ' ' . $this->surname;
    }
}
