<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClusterTrailerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       Schema::create('cluster_trailer', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cluster_id')->constrained('clusters')->cascadeOnDelete();
            $table->foreignId('trailer_id')->constrained('trailers')->cascadeOnDelete();
            // optional pivot metadata:
            $table->unsignedInteger('position')->nullable();   // order of trailers
            $table->timestamp('attached_at')->nullable();
            $table->unique(['cluster_id', 'trailer_id']);      // prevents duplicates
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cluster_trailer');
    }
}
