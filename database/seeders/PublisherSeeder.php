<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Publisher;

class PublisherSeeder extends Seeder
{
    public function run()
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
    }
} 