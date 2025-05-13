<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add default publishers
        $publishers = [
            ['name' => 'Can Yayınları', 'address' => 'İstanbul', 'phone' => '555-123-4567'],
            ['name' => 'Yapı Kredi Yayınları', 'address' => 'İstanbul', 'phone' => '555-234-5678'],
            ['name' => 'İletişim Yayınları', 'address' => 'İstanbul', 'phone' => '555-345-6789'],
            ['name' => 'Doğan Kitap', 'address' => 'İstanbul', 'phone' => '555-456-7890'],
            ['name' => 'İş Bankası Kültür Yayınları', 'address' => 'İstanbul', 'phone' => '555-567-8901'],
            ['name' => 'Everest Yayınları', 'address' => 'İstanbul', 'phone' => '555-678-9012'],
        ];

        foreach ($publishers as $publisher) {
            DB::table('publishers')->updateOrInsert(
                ['name' => $publisher['name']],
                [
                    'address' => $publisher['address'], 
                    'phone' => $publisher['phone'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }

        // Update existing books with random publishers
        $publisherIds = DB::table('publishers')->pluck('id')->toArray();
        
        if (count($publisherIds) > 0) {
            $books = DB::table('books')->whereNull('publisher_id')->get();
            foreach ($books as $book) {
                DB::table('books')->where('id', $book->id)->update([
                    'publisher_id' => $publisherIds[array_rand($publisherIds)],
                    'updated_at' => now()
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We don't want to delete the publishers as they might be in use
        // Just leave them in place if the migration is reversed
    }
};
