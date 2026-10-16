<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * TyreAssignmentService / TyreDispatchFitmentService write tyre_assignments.ticket_id
 * (fitting a tyre from a dispatch linked to a job card), but no migration ever
 * created the column - it only existed on databases patched by hand, so
 * approving a tyre dispatch failed elsewhere with "Unknown column 'ticket_id'".
 * ticket_inventory_id was in the same position. Guarded so patched databases
 * are left alone.
 */
class AddTicketIdToTyreAssignmentsTable extends Migration
{
    public function up()
    {
        Schema::table('tyre_assignments', function (Blueprint $table) {
            if (!Schema::hasColumn('tyre_assignments', 'ticket_id')) {
                $table->bigInteger('ticket_id')->unsigned()->nullable()->index();
            }
            if (!Schema::hasColumn('tyre_assignments', 'ticket_inventory_id')) {
                $table->bigInteger('ticket_inventory_id')->unsigned()->nullable();
            }
        });
    }

    public function down()
    {
        // Columns may pre-date this migration on hand-patched databases; leave them.
    }
}
