<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTyreDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tyre_documents', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('tyre_id')->nullable()->unsigned();
            $table->foreign('tyre_id')->references('id')->on('tyres')->onDelete('cascade');
            $table->string('title')->nullable();
            $table->bigInteger('vendor_id')->nullable()->unsigned();
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
            $table->string('filename')->nullable();
            $table->string('status')->nullable();
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
        Schema::dropIfExists('tyre_documents');
    }
}
