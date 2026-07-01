<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPayrollRunIdToJournalEntriesTable extends Migration
{
    public function up()
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->foreignId('payroll_run_id')->nullable()->after('payment_id')->constrained()->nullOnDelete();
            $table->index('payroll_run_id');
        });
    }

    public function down()
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('payroll_run_id');
        });
    }
}
