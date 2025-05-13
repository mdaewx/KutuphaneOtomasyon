<?php

namespace Database\Seeders;

use App\Models\Shelf;
use Illuminate\Database\Seeder;

class ShelfSeeder extends Seeder
{
    public function run()
    {
        $shelves = [
            [
                'name' => 'A-1',
                'description' => 'Roman ve Öykü Rafı',
                'capacity' => 100,
                'shelf_number' => 'A1',
                'code' => 'A1'
            ],
            [
                'name' => 'A-2',
                'description' => 'Şiir ve Edebiyat Rafı',
                'capacity' => 100,
                'shelf_number' => 'A2',
                'code' => 'A2'
            ],
            [
                'name' => 'B-1',
                'description' => 'Tarih ve Felsefe Rafı',
                'capacity' => 100,
                'shelf_number' => 'B1',
                'code' => 'B1'
            ],
            [
                'name' => 'B-2',
                'description' => 'Bilim ve Teknoloji Rafı',
                'capacity' => 100,
                'shelf_number' => 'B2',
                'code' => 'B2'
            ],
            [
                'name' => 'C-1',
                'description' => 'Çocuk ve Gençlik Rafı',
                'capacity' => 100,
                'shelf_number' => 'C1',
                'code' => 'C1'
            ],
            [
                'name' => 'C-2',
                'description' => 'Kişisel Gelişim Rafı',
                'capacity' => 100,
                'shelf_number' => 'C2',
                'code' => 'C2'
            ]
        ];

        foreach ($shelves as $shelf) {
            Shelf::create($shelf);
        }
    }
} 