<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AcquisitionSource;
use App\Models\AcquisitionSourceType;

class AcquisitionSourceSeeder extends Seeder
{
    public function run()
    {
        // Varsayılan edinme kaynaklarını ekle
        $sources = [
            [
                'name' => 'Satın Alma - Merkez',
                'description' => 'Merkez kütüphane satın alma birimi'
            ],
            [
                'name' => 'Bağış - Genel',
                'description' => 'Genel bağış kayıtları'
            ],
            [
                'name' => 'Değişim Programı',
                'description' => 'Kütüphaneler arası değişim programı'
            ]
        ];

        foreach ($sources as $source) {
            AcquisitionSource::create($source);
        }
    }
} 