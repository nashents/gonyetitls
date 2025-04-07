<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoryChecklistsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('category_checklists', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('checklist_category_id')->unsigned()->nullable();
            $table->foreign('checklist_category_id')->references('id')->on('checklist_categories')->onDelete('cascade');
            $table->bigInteger('checklist_sub_category_id')->unsigned()->nullable();
            $table->foreign('checklist_sub_category_id')->references('id')->on('checklist_sub_categories')->onDelete('cascade');
            $table->bigInteger('checklist_item_id')->unsigned()->nullable();
            $table->foreign('checklist_item_id')->references('id')->on('checklist_items')->onDelete('cascade');
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
        Schema::dropIfExists('category_checklists');
    }
}
