<?php

namespace Database\Seeders;

use App\Models\ProblemCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProblemCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            // Suspension & Steering
            ['name' => 'Suspension', 'description' => 'Suspension system failures including springs, shock absorbers and linkages'],
            ['name' => 'Steering', 'description' => 'Steering system faults including rack, column and power steering'],

            // Brakes
            ['name' => 'Brakes', 'description' => 'Brake system issues including pads, discs, drums and hydraulics'],
            ['name' => 'ABS System', 'description' => 'Anti-lock braking system faults'],

            // Drivetrain
            ['name' => 'Gearbox', 'description' => 'Transmission and gearbox faults'],
            ['name' => 'Clutch', 'description' => 'Clutch plate, pressure plate and release bearing failures'],
            ['name' => 'Differential', 'description' => 'Differential and axle faults'],
            ['name' => 'Driveshaft', 'description' => 'Driveshaft, CV joints and propshaft failures'],

            // Engine
            ['name' => 'Engine', 'description' => 'General engine faults, overheating and internal failures'],
            ['name' => 'Fuel System', 'description' => 'Fuel pump, injectors, filters and fuel line issues'],
            ['name' => 'Turbo', 'description' => 'Turbocharger and intercooler faults'],
            ['name' => 'Exhaust', 'description' => 'Exhaust system leaks, DPF and emissions faults'],

            // Electrical
            ['name' => 'Electrical', 'description' => 'General electrical faults, wiring and battery issues'],
            ['name' => 'Alternator', 'description' => 'Alternator and charging system faults'],
            ['name' => 'Starter Motor', 'description' => 'Starter motor and ignition system faults'],
            ['name' => 'Lights', 'description' => 'Headlights, indicators, brake lights and interior lighting'],

            // Cooling
            ['name' => 'Cooling System', 'description' => 'Radiator, coolant leaks, thermostat and water pump faults'],

            // Tyres & Wheels
            ['name' => 'Tyres', 'description' => 'Tyre wear, punctures and wheel alignment'],
            ['name' => 'Wheels & Rims', 'description' => 'Wheel bearing, hub and rim damage'],

            // Body & Cab
            ['name' => 'Body Damage', 'description' => 'Panel damage, cab repairs and structural bodywork'],
            ['name' => 'Windscreen', 'description' => 'Windscreen chips, cracks and wiper faults'],
            ['name' => 'Doors & Locks', 'description' => 'Door hinges, locks and seals'],

            // Service & Scheduled
            ['name' => 'Scheduled Service', 'description' => 'Routine oil, filter and service intervals'],
            ['name' => 'Pre-trip Inspection', 'description' => 'Defects identified during pre-trip vehicle checks'],

            // Other
            ['name' => 'Accident Damage', 'description' => 'Repairs resulting from road traffic accidents'],
            ['name' => 'Other', 'description' => 'Miscellaneous workshop jobs not covered by other categories'],
        ];

        foreach ($categories as $category) {
            ProblemCategory::firstOrCreate(
                ['name' => $category['name']],
                ['description' => $category['description']]
            );
        }
    }
}