<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * New PAYE bracket table — replaces the original tax_brackets table
 * which stored all values as strings and had no effective dates.
 *
 * The old table is left intact for backward compatibility.
 * The PayrollEngine service reads from this table, not the old one.
 */
class CreateTaxBracketsV2Table extends Migration
{
    public function up()
    {
        if (Schema::hasTable('tax_brackets_v2')) {
            return;
        }


        Schema::create('tax_brackets_v2', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('statutory_deduction_type_id')->unsigned()
                ->comment('Must reference the PAYE statutory_deduction_type');
            $table->foreign('statutory_deduction_type_id')
                ->references('id')->on('statutory_deduction_types')->onDelete('cascade');

            $table->bigInteger('currency_id')->unsigned()->nullable();
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('set null');

            $table->date('effective_from');
            $table->date('effective_to')->nullable();

            // Bracket thresholds in the payroll currency
            $table->decimal('lower_band', 15, 4)->default(0);
            $table->decimal('upper_band', 15, 4)->nullable()->comment('NULL = no upper limit');

            $table->decimal('rate_percentage', 8, 4)->comment('Marginal rate for this band');
            $table->decimal('cumulative_tax_at_lower_band', 15, 4)->default(0)
                ->comment('Tax already paid on income up to lower_band (for fast calculation)');

            // AIDS Levy baked into the bracket (Zimbabwe-specific, but nullable)
            $table->decimal('aids_levy_percentage', 8, 4)->default(0);

            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->bigInteger('created_by')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['statutory_deduction_type_id', 'effective_from', 'lower_band'], 'tax_brackets_v2_type_date_band_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('tax_brackets_v2');
    }
}
