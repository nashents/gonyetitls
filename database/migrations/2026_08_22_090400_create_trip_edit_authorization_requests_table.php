<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTripEditAuthorizationRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trip_edit_authorization_requests', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('trip_id')->unsigned();
            $table->foreign('trip_id')->references('id')->on('trips')->onDelete('cascade');
            $table->bigInteger('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->text('reason')->nullable();

            $table->string('status')->default('pending');
            $table->bigInteger('decided_by')->unsigned()->nullable();
            $table->foreign('decided_by')->references('id')->on('users')->onDelete('set null');
            $table->timestamp('decided_at')->nullable();
            $table->text('decision_comments')->nullable();

            $table->timestamp('consumed_at')->nullable();

            $table->timestamps();

            $table->index(['trip_id', 'status']);
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('trip_edit_authorization_requests');
    }
}
