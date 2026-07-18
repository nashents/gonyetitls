<?php

namespace Database\Seeders;

use App\Models\PayrollFrequency;
use Illuminate\Database\Seeder;

/**
 * Seeds the standard set of global pay frequencies. Monthly is the only one
 * active by default (matches PayrollStatutoryConfigSeeder's expectation);
 * the rest are pre-seeded inactive so an admin can just flip the one they
 * want on in Payroll Config instead of creating it from scratch.
 */
class PayrollFrequencySeeder extends Seeder
{
    public function run()
    {
        $frequencies = [
            ['code' => 'MONTHLY',      'name' => 'Monthly',      'periods_per_year' => 12, 'days_in_period' => null, 'active' => true],
            ['code' => 'SEMI_MONTHLY', 'name' => 'Semi-Monthly', 'periods_per_year' => 24, 'days_in_period' => null, 'active' => false],
            ['code' => 'FORTNIGHTLY',  'name' => 'Fortnightly',  'periods_per_year' => 26, 'days_in_period' => 14,   'active' => false],
            ['code' => 'WEEKLY',       'name' => 'Weekly',       'periods_per_year' => 52, 'days_in_period' => 7,    'active' => false],
        ];

        foreach ($frequencies as $freq) {
            PayrollFrequency::firstOrCreate(
                ['code' => $freq['code']],
                [
                    'company_id'       => null,
                    'name'             => $freq['name'],
                    'periods_per_year' => $freq['periods_per_year'],
                    'days_in_period'   => $freq['days_in_period'],
                    'active'           => $freq['active'],
                ]
            );
        }
    }
}
