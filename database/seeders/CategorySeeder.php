<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            [
                'name' => 'Roman',
                'description' => 'Roman türündeki kitaplar',
                'slug' => 'roman'
            ],
            [
                'name' => 'Öykü',
                'description' => 'Öykü türündeki kitaplar',
                'slug' => 'oyku'
            ],
            [
                'name' => 'Şiir',
                'description' => 'Şiir türündeki kitaplar',
                'slug' => 'siir'
            ],
            [
                'name' => 'Tarih',
                'description' => 'Tarih konulu kitaplar',
                'slug' => 'tarih'
            ],
            [
                'name' => 'Felsefe',
                'description' => 'Felsefe konulu kitaplar',
                'slug' => 'felsefe'
            ],
            [
                'name' => 'Bilim',
                'description' => 'Bilim konulu kitaplar',
                'slug' => 'bilim'
            ],
            [
                'name' => 'Çocuk ve Gençlik',
                'description' => 'Çocuk ve gençlik kitapları',
                'slug' => 'cocuk-ve-genclik'
            ],
            [
                'name' => 'Kişisel Gelişim',
                'description' => 'Kişisel gelişim kitapları',
                'slug' => 'kisisel-gelisim'
            ]
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
} 