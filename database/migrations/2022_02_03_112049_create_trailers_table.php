<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrailersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trailers', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('transporter_id')->nullable();
            $table->bigInteger('trailer_type_id')->nullable();
            $table->string('trailer_number')->nullable();
            $table->string('fleet_number')->nullable();
            $table->string('make')->nullable();
            $table->string('model')->nullable();
            $table->string('chasis_number')->nullable();
            $table->string('suspension_type')->nullable();
            $table->string('year')->nullable();
            $table->string('start_date')->nullable();
            $table->string('end_date')->nullable();
            $table->string('color')->nullable();
            $table->string('nvm')->nullable();
            $table->string('gvm')->nullable();
            $table->string('cargo_type')->nullable();
            $table->string('condition')->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('capacity')->nullable();
            $table->string('measurement')->nullable();
            $table->string('country_of_origin')->nullable();
            $table->string('registration_number')->nullable();
            $table->string('mileage')->nullable();
            $table->text('compartments')->nullable();
            $table->string('prev_service')->nullable();
            $table->string('prev_service_date')->nullable();
            $table->string('next_service')->nullable();
            $table->string('no_of_wheels')->nullable();
            $table->boolean('status')->default(1);
            $table->boolean('service')->default(0);
            $table->boolean('approval')->default(0);
            $table->boolean('archive')->default(0);
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
        Schema::dropIfExists('trailers');
    }
}
