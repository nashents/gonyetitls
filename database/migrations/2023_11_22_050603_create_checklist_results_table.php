<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChecklistResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('checklist_results', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('checklist_id')->nullable()->unsigned();
            $table->foreign('checklist_id')->references('id')->on('checklists')->onDelete('cascade');
            $table->bigInteger('checklist_item_id')->nullable()->unsigned();
            $table->foreign('checklist_item_id')->references('id')->on('checklist_items')->onDelete('cascade');
            $table->string('status')->nullable();
            $table->string('hours')->nullable();
            $table->string('cost')->nullable();
            $table->string('expiry')->nullable();
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
        Schema::dropIfExists('checklist_results');
    }
}
