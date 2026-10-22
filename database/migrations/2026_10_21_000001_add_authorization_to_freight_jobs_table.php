<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Trip-style authorization for freight jobs. New jobs start 'pending'; jobs
 * that already exist are backfilled to 'approved' so work in flight isn't
 * suddenly blocked from progressing or billing.
 */
class AddAuthorizationToFreightJobsTable extends Migration
{
    public function up()
    {
        Schema::table('freight_jobs', function (Blueprint $table) {
            $table->string('authorization')->default('pending')->index()->after('status');
            $table->unsignedBigInteger('authorized_by_id')->nullable()->after('authorization');
            $table->foreign('authorized_by_id')->references('id')->on('users')->onDelete('set null');
            $table->timestamp('authorization_date')->nullable()->after('authorized_by_id');
            $table->text('authorization_comments')->nullable()->after('authorization_date');
        });

        DB::table('freight_jobs')->update(['authorization' => 'approved']);
    }

    public function down()
    {
        Schema::table('freight_jobs', function (Blueprint $table) {
            $table->dropForeign(['authorized_by_id']);
            $table->dropColumn(['authorization', 'authorized_by_id', 'authorization_date', 'authorization_comments']);
        });
    }
}
