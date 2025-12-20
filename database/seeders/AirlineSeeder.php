<?php

namespace Database\Seeders;

use App\Models\Airline;
use Illuminate\Database\Seeder;

class AirlineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $airlines = [
            [
                'code' => 'MS',
                'name' => 'EgyptAir',
                'logo_url' => 'https://www.egyptair.com/Style%20Library/egyptair/images/logo-eg.png',
                'is_active' => true,
            ],
            [
                'code' => 'EK',
                'name' => 'Emirates',
                'logo_url' => 'https://www.emirates.com/etc/designs/aem-emirates/clientlibs/images/logos/emirates-logo.svg',
                'is_active' => true,
            ],
            [
                'code' => 'QR',
                'name' => 'Qatar Airways',
                'logo_url' => 'https://www.qatarairways.com/content/dam/images/renditions/horizontal-1/brand/qatar-airways-logo.png',
                'is_active' => true,
            ],
            [
                'code' => 'TK',
                'name' => 'Turkish Airlines',
                'logo_url' => 'https://www.turkishairlines.com/theme/img/TK_Logo.png',
                'is_active' => true,
            ],
            [
                'code' => 'SV',
                'name' => 'Saudia',
                'logo_url' => 'https://www.saudia.com/content/dam/saudia/logo/saudia-logo.svg',
                'is_active' => true,
            ],
            [
                'code' => 'RJ',
                'name' => 'Royal Jordanian',
                'logo_url' => 'https://www.rj.com/content/dam/royal-jordanian/logo/rj-logo.png',
                'is_active' => true,
            ],
            [
                'code' => 'GF',
                'name' => 'Gulf Air',
                'logo_url' => 'https://www.gulfair.com/content/dam/gulfair/logo/gf-logo.png',
                'is_active' => true,
            ],
            [
                'code' => 'BA',
                'name' => 'British Airways',
                'logo_url' => 'https://www.britishairways.com/assets/images/global/ba-logo.svg',
                'is_active' => true,
            ],
            [
                'code' => 'AF',
                'name' => 'Air France',
                'logo_url' => 'https://www.airfrance.com/FR/common/image/logo/logo_af.png',
                'is_active' => true,
            ],
            [
                'code' => 'LH',
                'name' => 'Lufthansa',
                'logo_url' => 'https://www.lufthansa.com/content/dam/lh/images/logo/lufthansa-logo.svg',
                'is_active' => true,
            ],
        ];

        foreach ($airlines as $airline) {
            Airline::updateOrCreate(
                ['code' => $airline['code']],
                $airline
            );
        }
    }
}