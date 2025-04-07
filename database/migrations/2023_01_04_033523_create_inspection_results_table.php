<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInspectionResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inspection_results', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('inspection_id')->nullable()->unsigned();
            $table->foreign('inspection_id')->references('id')->on('inspections')->onDelete('cascade');
            $table->bigInteger('inspection_type_id')->nullable()->unsigned();
            $table->foreign('inspection_type_id')->references('id')->on('inspection_types')->onDelete('cascade');
            $table->string('status')->nullable();
            $table->string('hours')->nullable();
            $table->string('cost')->nullable();
            $table->text('comments')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inspection_results');
    }
}
