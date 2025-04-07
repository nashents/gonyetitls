<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRetreadDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('retread_documents', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('retread_id')->unsigned()->nullable();
            $table->foreign('retread_id')->references('id')->on('retreads')->onDelete('cascade');
            $table->string('title')->nullable();
            $table->string('file')->nullable();
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
        Schema::dropIfExists('retread_documents');
    }
}
