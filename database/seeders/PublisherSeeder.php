<?php

namespace Database\Seeders;

use App\Models\Publisher;
use Illuminate\Database\Seeder;

class PublisherSeeder extends Seeder
{
    public function run()
    {
        $publishers = [
            [
                'name' => 'Yapı Kredi Yayınları',
                'address' => 'İstanbul',
                'phone' => '0212 123 45 67'
            ],
            [
                'name' => 'Can Yayınları',
                'address' => 'İstanbul',
                'phone' => '0212 234 56 78'
            ],
            [
                'name' => 'İş Bankası Kültür Yayınları',
                'address' => 'İstanbul',
                'phone' => '0212 345 67 89'
            ],
            [
                'name' => 'Doğan Kitap',
                'address' => 'İstanbul',
                'phone' => '0212 456 78 90'
            ]
        ];

        foreach ($publishers as $publisher) {
            Publisher::create($publisher);
        }
    }
} 