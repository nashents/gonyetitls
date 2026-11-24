<?php

use Database\Seeders\SheqLookupDescriptionSeeder;
use Illuminate\Database\Migrations\Migration;

/**
 * Fills the new description column on the seeded SHEQ lookups (loss cause
 * categories / groups / causes, training items / departments). Only blank
 * descriptions are filled, so anything typed in the app is kept.
 */
return new class extends Migration
{
    public function up()
    {
        (new SheqLookupDescriptionSeeder)->run();
    }

    public function down()
    {
        // Descriptions are dropped with the column by the previous migration.
    }
};
