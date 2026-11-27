<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Custom follow-up reminders on a freight job (e.g. "Permit ready before
 * discharge"). Emailed to the chosen staff and optionally the client's
 * contacts on the chosen days before the due date (FreightJobReminderService),
 * and listed in the freight reminders bell / daily digest until marked done.
 */
return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('freight_job_reminders')) {
            return;
        }

        Schema::create('freight_job_reminders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->unsignedBigInteger('freight_job_id')->index();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('title');
            $table->text('notes')->nullable();
            $table->text('client_message')->nullable();
            $table->dateTime('due_at');
            $table->json('remind_days')->nullable();      // e.g. [7,3,1,0] - days before the due date
            $table->json('sent_days')->nullable();        // which of those have been emailed
            $table->json('staff_user_ids')->nullable();
            $table->text('extra_emails')->nullable();     // comma separated
            $table->boolean('notify_client')->default(false);
            $table->boolean('daily_when_overdue')->default(false);
            $table->date('last_overdue_sent_on')->nullable();
            $table->string('status')->default('open');    // open / done
            $table->dateTime('completed_at')->nullable();
            $table->unsignedBigInteger('completed_by')->nullable();
            $table->dateTime('last_sent_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'due_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('freight_job_reminders');
    }
};
