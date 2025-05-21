<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Publisher;

class UpdatePublisherEmailsSeeder extends Seeder
{
    public function run(): void
    {
        $publishers = [
            'Yapı Kredi Yayınları' => 'info@ykykultur.com.tr',
            'İş Bankası Kültür Yayınları' => 'kultur@isbank.com.tr',
            'Can Yayınları' => 'info@canyayinlari.com',
            'Doğan Kitap' => 'info@dogankitap.com.tr',
            'İletişim Yayınları' => 'iletisim@iletisim.com.tr',
            'Everest Yayınları' => 'info@everestyayinlari.com',
            'Sel Yayıncılık' => 'info@selyayincilik.com',
            'İletişim Yayınları' => 'bilgi@iletisim.com.tr',
            'Remzi Kitabevi' => 'info@remzi.com.tr',
            'Epsilon Yayınevi' => 'info@epsilonyayinevi.com'
        ];

        foreach ($publishers as $name => $email) {
            Publisher::where('name', $name)->update(['email' => $email]);
        }
    }
} 