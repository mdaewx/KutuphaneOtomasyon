<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Author;

class AuthorSeeder extends Seeder
{
    public function run(): void
    {
        Author::create([
            'name' => 'Orhan',
            'surname' => 'Pamuk',
            'biography' => 'Nobel ödüllü Türk yazar.',
            'birth_date' => '1952-06-07',
        ]);
    }
} 