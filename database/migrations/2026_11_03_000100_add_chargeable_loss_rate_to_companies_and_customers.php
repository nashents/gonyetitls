<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Chargeable loss rate: amount charged per unit (ton / litre) of chargeable
 * loss. Company value is the Business Settings default; a customer's own
 * rate overrides it.
 */
return new class extends Migration
{
    public function up()
    {
        foreach (['companies', 'customers'] as $table) {
            if (! Schema::hasColumn($table, 'chargeable_loss_rate')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->decimal('chargeable_loss_rate', 15, 4)->nullable()->after('allowable_loss_percentage');
                });
            }
        }
    }

    public function down()
    {
        foreach (['companies', 'customers'] as $table) {
            if (Schema::hasColumn($table, 'chargeable_loss_rate')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropColumn('chargeable_loss_rate');
                });
            }
        }
    }
};
