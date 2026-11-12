<?php

use App\Services\Inventory\OverDispatchRepairService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Dispatch approval stopped a batch's balance at zero, so a line approved against an already
 * empty batch took nothing out of stock and the product showed more in store than there was.
 *
 * Adds the trail table for stock taken from another batch, then deducts every past over-issue
 * from the remaining batches of the same product and store (stock:repair-over-dispatches).
 */
return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('stock_shortfall_allocations')) {
            Schema::create('stock_shortfall_allocations', function (Blueprint $table) {
                $table->id();
                $table->string('item_type');                              // inventories | assets
                $table->unsignedBigInteger('item_id');                    // the batch that was short
                $table->unsignedBigInteger('donor_id')->nullable();       // the batch the stock came from; null = nothing left to take
                $table->unsignedBigInteger('dispatch_item_id')->nullable()->index();
                $table->unsignedBigInteger('product_id')->nullable();
                $table->decimal('qty', 15, 4);
                $table->string('reason');                                 // approval | repair
                $table->timestamps();
                $table->index(['item_type', 'item_id']);
            });
        }

        $rows = app(OverDispatchRepairService::class)->run(true);

        foreach ($rows as $r) {
            $from = collect($r['takes'])->map(fn ($qty, $id) => "{$qty} from #{$id}")->implode(', ') ?: 'nothing';
            Log::info("Over-dispatched {$r['type']} batch #{$r['batch_id']} ({$r['product']}): received {$r['received']}, "
                ."dispatched {$r['dispatched']}, over {$r['over']} - deducted {$from}"
                .($r['shortfall'] > 0 ? ", SHORTFALL {$r['shortfall']} (no stock left in store)" : ''));
        }
    }

    public function down()
    {
        // The stock corrections are a data fix and are not reversed.
        Schema::dropIfExists('stock_shortfall_allocations');
    }
};
