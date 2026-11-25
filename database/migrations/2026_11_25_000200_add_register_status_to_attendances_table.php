<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Attendance registers can be saved as a draft (fully editable) and then
 * approved as final (locked; only unmarked employees can be filled in).
 * Registers saved before this existed are treated as final.
 */
class AddRegisterStatusToAttendancesTable extends Migration
{
    public function up()
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->string('register_status', 20)->default('final')->after('is_drivers');
            $table->timestamp('finalized_at')->nullable()->after('register_status');
            $table->unsignedBigInteger('finalized_by')->nullable()->after('finalized_at');
        });
    }

    public function down()
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['register_status', 'finalized_at', 'finalized_by']);
        });
    }
}
