<?php

namespace Database\Seeders;

use App\Models\WasteType;
use Illuminate\Database\Seeder;

class WasteTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
            $waste_types = [    
        [ 'name' => 'Effluent Water(Maintenance)', 'category' => 'Hazardous - Chemical', 'generation_area' => 'Washing Bay Workshop Inspection pit','general_composition' => 'Residual Hydrocarbons and Detergents', 'impact' => 'Surface and underground water contamination (Chemical)','control_methods' => 'Wash bay and Inspection pit effluent is Channeled through canals to oil separator.','status',True],
        [ 'name' => 'Used oil', 'category' => 'Hazardous - Chemical', 'generation_area' => 'Workshop','general_composition' => 'Grease, Engine oil', 'impact' => '1. Water and Soil pollution.','control_methods' => 'Collected and resold.','status',True],
        [ 'name' => 'Filters (Oil, Air, Transmission Fluid, fuel) - From vehicle maintenance', 'category' => 'Hazardous - Chemical', 'generation_area' => 'Workshop','general_composition' => 'Oil contaminated Items', 'impact' => '1. Water and soil pollution 2. Litter','control_methods' => 'Sold to waste processing company.','status',True],
        [ 'name' => 'Scrap metal', 'category' => 'Metal', 'generation_area' => 'Workshop (Used vehicle components and Parts)', 'general_composition' => 'Used spares', 'impact' => '1. Site Litter','control_methods' => '1. Stored in scrap yard. 2. Internal recycling 3. Sold to metal dealer','status',True],
        [ 'name' => 'Plastic', 'category' => 'Plastic', 'generation_area' => 'Canteen Offices Workshop Construction Works', 'general_composition' => 'Packaging for Stationery, Spares and Food Items', 'impact' => '1. Blocks storm drains, its harmful to animals. 2. Land Pollution 3. Environmental Nuisance', 'control_methods' => 'Collected and kept in separate into refuse receptacle. Collected by Local Town Council.','status',True],
        [ 'name' => 'Used Batteries', 'category' => 'Hazardous - Chemical', 'generation_area' => 'Workshop', 'general_composition' => 'Lead, Acid', 'impact' => '1. Environmental Pollution 2. Health effects of exposure to S02', 'control_methods' => '1. Collected by Recycling companies. 2. Sold for reuse to employees','status',True],
        [ 'name' => 'Empty drums', 'category' => 'Metal', 'generation_area' => 'Workshop', 'general_composition' => 'Steel', 'impact' => '1. Traps water and becomes breeding sites for vectors. 2. Housing of pests and rodents', 'control_methods' => 'Re-used scrap metal receptacle. Re-used as used oil storage.','status',True],
        [ 'name' => 'Scrap Tires', 'category' => 'Rubber', 'generation_area' => 'Workshop', 'general_composition' => 'Rubber', 'impact' => '1. Nonbiodegradable', 'control_methods' => '1. Stored at the scrap yard for reuse. 2. Sold to recycler','status',True],
        [ 'name' => 'Food', 'category' => 'Food and Biodegradable', 'generation_area' => 'Kitchen', 'general_composition' => 'Food remains (rice, sadza , beef , vegetables , etc.', 'impact' => '1. Decomposed food waste attracts rodents and pests and produces a rotten Odour', 'control_methods' => '1. Timely disposal 2. Fumigation','status',True],
        ];

          foreach ($waste_types as $data) {
        WasteType::updateOrCreate(
            [
                'name'     => $data['name'],
                'category' => $data['category'],
            ],
            [
                'generation_area' => $data['generation_area'],
                'general_composition'     => $data['general_composition'],
                'impact'          => $data['impact'],
                'control_methods' => $data['control_methods'],
            ]
        );
    }
    }
}
