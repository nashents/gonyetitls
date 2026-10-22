<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One row per recipient per day the freight reminders digest was emailed -
 * the unique key is what stops a recipient getting the digest twice in a day
 * when several requests race to send it (see FreightReminderService).
 */
class CreateFreightReminderDigestsTable extends Migration
{
    public function up()
    {
        Schema::create('freight_reminder_digests', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->date('sent_on');
            $table->unsignedInteger('reminder_count')->default(0);
            $table->timestamps();
            $table->unique(['email', 'sent_on']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('freight_reminder_digests');
    }
}
