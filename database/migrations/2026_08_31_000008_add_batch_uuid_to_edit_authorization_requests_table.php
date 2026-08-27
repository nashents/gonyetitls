<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lets a single "request edit" / "decide" action cover many records at once
 * (e.g. requesting edit authorization on several bills in one go). Rows
 * created together share a batch_uuid; single-record requests leave it null.
 */
class AddBatchUuidToEditAuthorizationRequestsTable extends Migration
{
    public function up()
    {
        Schema::table('edit_authorization_requests', function (Blueprint $table) {
            $table->uuid('batch_uuid')->nullable()->after('module');
            $table->index(['batch_uuid']);
        });
    }

    public function down()
    {
        Schema::table('edit_authorization_requests', function (Blueprint $table) {
            $table->dropIndex(['batch_uuid']);
            $table->dropColumn('batch_uuid');
        });
    }
}
