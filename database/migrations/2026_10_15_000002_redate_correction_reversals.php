<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * The trip-bill account repair (2026_10_15_000001) reversed the old postings
 * dated the day it ran - re-date those (and any other correction reversal
 * since) to the original entry's date. See RedateCorrectionReversals; older
 * history can be done by hand with --since.
 */
class RedateCorrectionReversals extends Migration
{
    public function up()
    {
        Artisan::call('journals:redate-correction-reversals', ['--since' => '2026-10-14']);
        echo Artisan::output();
    }

    public function down()
    {
        // Data repair - nothing to roll back.
    }
}
