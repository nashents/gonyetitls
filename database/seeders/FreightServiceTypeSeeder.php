<?php

namespace Database\Seeders;

use App\Models\FreightServiceType;
use Illuminate\Database\Seeder;

class FreightServiceTypeSeeder extends Seeder
{
    public function run()
    {
        $types = [
            'Freight Forwarding',
            'Customs Clearing',
            'Freight Forwarding & Clearing',
            'Warehousing & Distribution',
        ];

        foreach ($types as $type) {
            FreightServiceType::updateOrCreate(
                ['name' => $type],
                ['name' => $type, 'is_locked' => true]
            );
        }
    }
}
