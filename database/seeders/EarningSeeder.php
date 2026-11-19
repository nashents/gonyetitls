<?php

namespace Database\Seeders;

use App\Models\Earning;
use Illuminate\Database\Seeder;

/**
 * Default payroll earnings - shown in the "Allowances" section of a salary.
 * (Accommodation / Transport used to be seeded as Allowances; Allowances are
 * now for driver trip expenses only.) Idempotent.
 */
class EarningSeeder extends Seeder
{
    public function run()
    {
        foreach (['Accommodation', 'Transport'] as $name) {
            Earning::firstOrCreate(
                ['name' => $name],
                ['calculate_by' => 'currency', 'status' => 1, 'taxable' => true]
            );
        }
    }
}
