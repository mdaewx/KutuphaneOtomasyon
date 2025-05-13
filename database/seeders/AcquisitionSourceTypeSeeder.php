<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AcquisitionSourceType;

class AcquisitionSourceTypeSeeder extends Seeder
{
    public function run()
    {
        $types = [
            [
                'type' => 'purchase',
                'name' => 'Satın Alma',
                'description' => 'Kitapların satın alma yoluyla edinilmesi'
            ],
            [
                'type' => 'donation',
                'name' => 'Bağış',
                'description' => 'Kitapların bağış yoluyla edinilmesi'
            ],
            [
                'type' => 'exchange',
                'name' => 'Değişim',
                'description' => 'Kitapların değişim yoluyla edinilmesi'
            ],
            [
                'type' => 'other',
                'name' => 'Diğer',
                'description' => 'Diğer edinme yöntemleri'
            ]
        ];

        foreach ($types as $type) {
            AcquisitionSourceType::updateOrCreate(
                ['type' => $type['type']],
                $type
            );
        }
    }
} 