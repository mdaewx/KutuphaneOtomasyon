<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcquisitionSourceType extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'name',

    ];



    // İlişkiler
    public function acquisitionSources()
    {
        return $this->hasMany(AcquisitionSource::class, 'source_type_id');
    }
}
