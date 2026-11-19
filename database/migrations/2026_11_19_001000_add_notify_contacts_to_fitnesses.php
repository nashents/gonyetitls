<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Customer / vendor reminders can also email that customer's / vendor's
 * contacts (customer-facing copy, see ContactReminderMail). Per reminder.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('fitnesses', 'notify_contacts')) {
            Schema::table('fitnesses', function (Blueprint $table) {
                $table->boolean('notify_contacts')->default(false);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('fitnesses', 'notify_contacts')) {
            Schema::table('fitnesses', function (Blueprint $table) {
                $table->dropColumn('notify_contacts');
            });
        }
    }
};
