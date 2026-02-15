<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnitsOfMeasureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // Try to set a sensible default user_id if you want ownership.
        // If you don't want that, just leave as null.
        $userId = null;

        $units = [
            // --- Count / Each ---
            ['name' => 'Each',                'abbreviation' => 'ea',   'description' => 'Single item/unit count.'],
            ['name' => 'Piece',               'abbreviation' => 'pc',   'description' => 'Individual piece/item.'],
            ['name' => 'Unit',                'abbreviation' => 'unit', 'description' => 'Generic unit count.'],
            ['name' => 'Pair',                'abbreviation' => 'pr',   'description' => 'Two items treated as one unit (e.g., gloves, shoes).'],
            ['name' => 'Dozen',               'abbreviation' => 'doz',  'description' => '12 units.'],

            // --- Length ---
            ['name' => 'Millimetre',          'abbreviation' => 'mm',   'description' => 'Length in millimetres.'],
            ['name' => 'Centimetre',          'abbreviation' => 'cm',   'description' => 'Length in centimetres.'],
            ['name' => 'Metre',               'abbreviation' => 'm',    'description' => 'Length in metres.'],
            ['name' => 'Kilometre',           'abbreviation' => 'km',   'description' => 'Length in kilometres.'],

            // --- Area ---
            ['name' => 'Square Metre',        'abbreviation' => 'm²',   'description' => 'Area in square metres.'],
            ['name' => 'Square Foot',         'abbreviation' => 'ft²',  'description' => 'Area in square feet.'],

            // --- Volume ---
            ['name' => 'Millilitre',          'abbreviation' => 'mL',   'description' => 'Volume in millilitres.'],
            ['name' => 'Litre',               'abbreviation' => 'L',    'description' => 'Volume in litres.'],
            ['name' => 'Cubic Metre',         'abbreviation' => 'm³',   'description' => 'Volume in cubic metres.'],

            // --- Mass / Weight ---
            ['name' => 'Milligram',           'abbreviation' => 'mg',   'description' => 'Mass in milligrams.'],
            ['name' => 'Gram',                'abbreviation' => 'g',    'description' => 'Mass in grams.'],
            ['name' => 'Kilogram',            'abbreviation' => 'kg',   'description' => 'Mass in kilograms.'],
            ['name' => 'Tonne',               'abbreviation' => 't',    'description' => 'Metric ton (1,000 kg).'],

            // --- Time ---
            ['name' => 'Second',              'abbreviation' => 's',    'description' => 'Time in seconds.'],
            ['name' => 'Minute',              'abbreviation' => 'min',  'description' => 'Time in minutes.'],
            ['name' => 'Hour',                'abbreviation' => 'hr',   'description' => 'Time in hours.'],
            ['name' => 'Day',                 'abbreviation' => 'day',  'description' => 'Time in days.'],

            // --- Packaging ---
            ['name' => 'Box',                 'abbreviation' => 'box',  'description' => 'Box packaging unit.'],
            ['name' => 'Carton',              'abbreviation' => 'ctn',  'description' => 'Carton packaging unit.'],
            ['name' => 'Pack',                'abbreviation' => 'pk',   'description' => 'Pack of items.'],
            ['name' => 'Bag',                 'abbreviation' => 'bag',  'description' => 'Bag packaging unit.'],
            ['name' => 'Bottle',              'abbreviation' => 'btl',  'description' => 'Bottle container unit.'],
            ['name' => 'Can',                 'abbreviation' => 'can',  'description' => 'Can container unit.'],
            ['name' => 'Drum',                'abbreviation' => 'drm',  'description' => 'Drum container unit (common for oils/chemicals).'],
            ['name' => 'Pallet',              'abbreviation' => 'plt',  'description' => 'Palletized quantity.'],

            // --- Logistics / Freight ---
            ['name' => 'Load',                'abbreviation' => 'load', 'description' => 'One delivered/transported load.'],
            ['name' => 'Trip',                'abbreviation' => 'trip', 'description' => 'One trip/service run.'],

            // --- Workshop / Automotive (useful in your world) ---
            ['name' => 'Set',                 'abbreviation' => 'set',  'description' => 'A group of components sold/used together.'],
        ];

        foreach ($units as $unit) {
            // Use abbreviation as a stable unique key (best practice).
            // If you later add a unique index on abbreviation, you’ll be happy you did.
            $existing = DB::table('units_of_measures')
                ->where('abbreviation', $unit['abbreviation'])
                ->first();

            if ($existing) {
                // If soft-deleted, revive it and update fields
                DB::table('units_of_measures')
                    ->where('id', $existing->id)
                    ->update([
                        'user_id' => $userId ?? $existing->user_id,
                        'name' => $unit['name'],
                        'description' => $unit['description'],
                        'abbreviation' => $unit['abbreviation'],
                        'updated_at' => now(),
                        'deleted_at' => null,
                    ]);
            } else {
                DB::table('units_of_measures')->insert([
                    'user_id' => $userId,
                    'name' => $unit['name'],
                    'abbreviation' => $unit['abbreviation'],
                    'description' => $unit['description'],
                    'created_at' => now(),
                    'updated_at' => now(),
                    'deleted_at' => null,
                ]);
            }
        }
    }
}
