<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Opening tyres fitted without a dispatch (OpeningTyreFitmentService):
 * when/who, and the value the tyre had before it was zeroed.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('tyres', function (Blueprint $table) {
            if (!Schema::hasColumn('tyres', 'opening_fitted_at')) {
                $table->timestamp('opening_fitted_at')->nullable();
            }
            if (!Schema::hasColumn('tyres', 'opening_fitted_by_id')) {
                $table->unsignedBigInteger('opening_fitted_by_id')->nullable();
            }
            if (!Schema::hasColumn('tyres', 'opening_original_value')) {
                $table->decimal('opening_original_value', 15, 2)->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('tyres', function (Blueprint $table) {
            foreach (['opening_fitted_at', 'opening_fitted_by_id', 'opening_original_value'] as $column) {
                if (Schema::hasColumn('tyres', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
