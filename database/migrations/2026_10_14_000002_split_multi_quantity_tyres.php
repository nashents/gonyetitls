<?php

use App\Models\Tyre;
use App\Services\Fleet\TyreSplitService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Log;

/**
 * One tyre record = one physical tyre. Records holding several tyres (qty or balance > 1, set
 * through the old Tyres > Edit) are split into qty-1 records with the cost shared equally.
 * Runs after 2026_10_14_000001 so fitted tyres already have balance 0.
 */
return new class extends Migration
{
    public function up()
    {
        $service = app(TyreSplitService::class);

        Tyre::where(fn ($q) => $q->where('qty', '>', 1)->orWhere('balance', '>', 1))
            ->orderBy('id')
            ->get()
            ->each(function (Tyre $tyre) use ($service) {
                $created = $service->split($tyre);
                Log::info("Tyre split: {$tyre->tyre_number} (#{$tyre->id}) into ".($created + 1).' records.');
            });
    }

    public function down()
    {
        // Not reversible: the split records may have been dispatched or fitted since.
    }
};
