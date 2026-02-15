<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WasteReceptacle;

class WasteReceptacleSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'name' => 'Biodegradable Waste Bin',
                'description' => 'For organic/compostable waste such as food scraps and yard waste.',
                'status' => true,
            ],
            [
                'name' => 'Plastic Waste Bin',
                'description' => 'For plastic packaging, bottles, and other recyclable plastics.',
                'status' => true,
            ],
            [
                'name' => 'Stores Waste Bin',
                'description' => 'For general waste generated from stores/warehouse operations (non-hazardous).',
                'status' => true,
            ],
            [
                'name' => 'Paper Waste Bin',
                'description' => 'For paper and cardboard (clean/dry) for recycling.',
                'status' => true,
            ],
            [
                'name' => 'Hazardous Waste Bin',
                'description' => 'For hazardous waste (chemicals, contaminated materials). Handle per safety procedures.',
                'status' => true,
            ],
            [
                'name' => 'Metals Waste Bin',
                'description' => 'For scrap metal pieces, cans, and metal offcuts (non-contaminated).',
                'status' => true,
            ],
        ];

        foreach ($items as $data) {
            WasteReceptacle::updateOrCreate(
                ['name' => $data['name']],        // unique key
                [
                    'user_id' => null,            // system default
                    'description' => $data['description'],
                    'status' => $data['status'],
                ]
            );
        }
    }
}