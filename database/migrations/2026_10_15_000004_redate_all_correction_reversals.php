<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * 2026_10_15_000002/3 only re-dated correction reversals since 2026-10-14.
 * Older resyncs left the same distortion (e.g. a bill reposted three times
 * shows three debits in its own month while their reversals sit in the
 * months the resyncs ran) - re-date the whole history. Only reversals
 * followed by a repost of the same document are touched; deletions/voids
 * keep their dates. See RedateCorrectionReversals. Idempotent.
 */
class RedateAllCorrectionReversals extends Migration
{
    public function up()
    {
        Artisan::call('journals:redate-correction-reversals', ['--since' => '2000-01-01']);
        echo Artisan::output();
    }

    public function down()
    {
        // Data repair - nothing to roll back.
    }
}
