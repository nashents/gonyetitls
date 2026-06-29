<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNecCategoriesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('nec_categories')) {
            return;
        }


        Schema::create('nec_categories', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('company_id')->unsigned()->nullable()
                ->comment('NULL = global / available to all companies in this country');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');

            $table->string('country', 10)->default('ZW');
            $table->string('name');
            $table->string('code', 50)->nullable()
                ->comment('RMT = Road Motor Transport, ENG = Engineering, etc.');
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);

            $table->bigInteger('created_by')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('nec_categories');
    }
}
