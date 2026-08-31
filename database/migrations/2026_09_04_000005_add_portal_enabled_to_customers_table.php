<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Separate from the general "Active/Inactive" customers.status CRM badge
 * (already shown elsewhere and counted on dashboard widgets) - this
 * specifically gates freight portal login, so toggling it never changes
 * what that other badge means.
 */
class AddPortalEnabledToCustomersTable extends Migration
{
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->boolean('portal_enabled')->default(false)->after('remember_token');
        });
    }

    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('portal_enabled');
        });
    }
}
