<?php

namespace Database\Seeders;

use App\Models\ServiceType;
use App\Models\InspectionType;
use App\Models\InspectionGroup;
use Illuminate\Database\Seeder;

class InspectionTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        

        $underbody = InspectionGroup::where('name','Under Bonet')->get()->first();
        $tyres = InspectionGroup::where('name','Tyres')->get()->first();
        $underbonet = InspectionGroup::where('name','Under Bonet')->get()->first();
        $interior = InspectionGroup::where('name','Interior')->get()->first();
        $exterior = InspectionGroup::where('name','Exterior')->get()->first();
        
        $small_service = ServiceType::where('name','Small Service')->get()->first();
        $medium_service = ServiceType::where('name','Meduim Service')->get()->first();
        $large_service = ServiceType::where('name','Large Service')->get()->first();
        


        $inspection_types = [
            //small service
            ['name' => "Change engine oil" ],
            ['name' => "Change engine oil filters" ],
            ['name' => "Check coolant" ],
            ['name' => "Change fuel filters" ],
            ['name' => "Topup gearbox and differential oils" ],
            ['name' => "Topup steering and brake fluids" ],
            ['name' => "Inspect Brakes" ],
            ['name' => "Check Electricals" ],
            ['name' => "Drain air tanks" ],
            ['name' => "Check tyres" ],
            ['name' => "Check wipers" ],

            ['name' => "Suspension (airbags, shocks, torque bushes, u-bolts, hanger brackets, axle seats, centre bolts, spring bushes)" ],
            ['name' => "Air System" ],
            ['name' => "Electricals" ],
            ['name' => "Check Tyres" ],
       
            //medium service
            ['name' => "Change air dryer" ],
            
            //large service
            ['name' => "Change gearbox and differential oils and filters" ],
            ['name' => "Change steering fluid and filter" ],
            ['name' => "Change retarder oil and filter" ],
            ['name' => "Top up brake fluid" ],
            ['name' => "Wheel Bearings" ],
            ['name' => "Inspect critical components (Ring Gear, engine sound, engine mountings, fan belts, prop shaft, steered axle, drum brakes, brake linings, brake chamber, slack adjuster, compressors, retarder, gearbox, clutch)" ],
            ['name' => "Check 5th wheel and replace worn parts as necessary" ],
            ['name' => "Suspension (airbags, shocks, torque bushes, u-bolts, hanger brackets, axle seats, centre bolts, spring bushes)" ],
            ['name' => "Check Braking System" ],
            ['name' => "Wheel Bearings" ],
            
            ['name' => "Brake Pads Condition" ],
            ['name' => "Disc Rotor Condition" ],
            ['name' => "Park Brake Operation" ],
            ['name' => "Steering Operation + Leaks" ],
            ['name' => "Suspension Condition Worn / Leaking  Shocks / Springs" ],
            ['name' => "Exhaust system - Leaks + Security of Mountings" ],
            ['name' => "Tyre Thread Depth" ],
            ['name' => "Tyre Pressure " ],
            ['name' => "Service Due" ],
            ['name' => "Cambelt Due" ],
            ['name' => "Oil Level + Condition" ],
            ['name' => "Coolant Level + Condition" ],
            ['name' => "Transmission Level + Condition" ],
            ['name' => "Drive Belt Condition" ],
            ['name' => "Battery Condition" ],
            ['name' => "ABS + Airbag Check Light" ],
            ['name' => "Rear View Mirror" ],
            ['name' => "Horn" ],
            ['name' => "Lighting" ],
            ['name' => "Washers/Wipers/Windscreen" ],
            ['name' => "Mirrors" ],

    

           
        ];
        InspectionType::insert($inspection_types);

    }
}
