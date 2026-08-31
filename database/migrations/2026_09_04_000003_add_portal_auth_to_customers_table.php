<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds real login credentials to Customer for the new freight customer
 * portal (Phase 10). Deliberately does NOT touch the existing `pin`
 * column - that belongs to a separate, pre-existing, unrelated
 * User.category='customer' trip-portal login and is left completely
 * alone.
 */
class AddPortalAuthToCustomersTable extends Migration
{
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('password')->nullable()->after('pin');
            $table->rememberToken()->after('password');
        });
    }

    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['password', 'remember_token']);
        });
    }
}
