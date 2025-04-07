<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRecipientReminderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recipient_reminder', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('recipient_id')->unsigned()->nullable();
            $table->foreign('recipient_id')->references('id')->on('recipients')->onDelete('cascade');
            $table->bigInteger('reminder_id')->unsigned()->nullable();
            $table->foreign('reminder_id')->references('id')->on('reminders')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('recipient_reminder');
    }
}
