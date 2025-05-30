<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Publisher;

class PublisherSeeder extends Seeder
{
    public function run(): void
    {
        $publishers = [
            ['name' => 'İş Bankası Kültür Yayınları'],
            ['name' => 'Yapı Kredi Yayınları'],
            ['name' => 'Can Yayınları'],
            ['name' => 'Doğan Kitap'],
            ['name' => 'Remzi Kitabevi'],
            ['name' => 'İletişim Yayınları'],
            ['name' => 'Metis Yayınları'],
            ['name' => 'Sel Yayıncılık'],
            ['name' => 'Everest Yayınları'],
            ['name' => 'Alfa Yayınları']
        ];

        foreach ($publishers as $publisher) {
            Publisher::create($publisher);
        }

        Publisher::create([
            'name' => 'Masumiyet Müzesi',
            'address' => 'İstanbul',
            'phone' => '0212 123 45 67',
            'email' => 'info@masumiyetmuzesi.com',
            'description' => 'Masumiyet Müzesi Yayınları',
            'is_active' => true
        ]);
    }
} 