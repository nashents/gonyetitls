<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddSageFieldsToCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds Sage Intacct sync-state columns to customers and backfills any
     * record that already exists in Sage (identified by a non-empty custom_ref,
     * which the bulk-import from Sage populates with the Sage CUSTOMERID).
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('sage_intacct_id')->nullable()->after('custom_ref');
            // pending | synced | failed
            $table->string('sage_sync_status')->nullable()->after('sage_intacct_id');
            $table->timestamp('sage_last_synced_at')->nullable()->after('sage_sync_status');
            $table->text('sage_sync_error')->nullable()->after('sage_last_synced_at');
        });

        // Backfill: records imported from Sage already carry the Sage ID in
        // custom_ref, so treat them as already synced and never re-create them.
        DB::table('customers')
            ->whereNotNull('custom_ref')
            ->where('custom_ref', '!=', '')
            ->update([
                'sage_intacct_id'  => DB::raw('custom_ref'),
                'sage_sync_status' => 'synced',
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'sage_intacct_id',
                'sage_sync_status',
                'sage_last_synced_at',
                'sage_sync_error',
            ]);
        });
    }
}
