<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTyreIdToChecklistResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('checklist_results', function (Blueprint $table) {
            $table->bigInteger('tyre_id')->nullable()->unsigned();
            $table->foreign('tyre_id')->references('id')->on('tyres')->onDelete('cascade');
            $table->bigInteger('tyre_assignment_id')->nullable()->unsigned();
            $table->foreign('tyre_assignment_id')->references('id')->on('tyre_assignments')->onDelete('cascade');
            $table->decimal('tread_depth_mm', 5, 2)->nullable();
            $table->decimal('pressure_psi', 5, 2)->nullable();
            $table->boolean('valve_ok')->default(true);
            $table->string('sidewall_damage')->default('None');
            $table->string('wear_pattern')->default('Even');
            $table->string('rim_condition')->default('OK');
            $table->boolean('wheel_nuts_torqued')->default(true);
            $table->boolean('axle_match')->default(true);
            $table->string('action_required')->default('None');
            $table->unsignedTinyInteger('rating')->default(5); // 1..5
            $table->text('notes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('checklist_results', function (Blueprint $table) {
            $table->dropForeign(['tyre_id']);
            $table->dropColumn('tyre_id');
            $table->dropForeign(['tyre_assignment_id']);
            $table->dropColumn('tyre_assignment_id');
            $table->dropColumn('tread_depth_mm');
            $table->dropColumn('pressure_psi');
            $table->dropColumn('valve_ok');
            $table->dropColumn('sidewall_damage');
            $table->dropColumn('wear_pattern');
            $table->dropColumn('rim_condition');
            $table->dropColumn('wheel_nuts_torqued');
            $table->dropColumn('axle_match');
            $table->dropColumn('action_required');
            $table->dropColumn('rating'); // 1..5
            $table->dropColumn('notes'); // 1..5
        });
    }
}
