<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCredentialSharesAndSharerSetting extends Migration
{
    /**
     * Credential sharing lock: companies.credential_sharer_ids lists the user
     * accounts (besides is_admin) allowed to see employee PINs and send login
     * details; credential_shares logs every send (who, to whom, which channel).
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('companies', 'credential_sharer_ids')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->json('credential_sharer_ids')->nullable();
            });
        }

        if (!Schema::hasTable('credential_shares')) {
            Schema::create('credential_shares', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->nullable()->index();
                $table->foreignId('user_id')->nullable()->index();          // account whose credentials were shared
                $table->foreignId('shared_by_id')->nullable()->index();     // who initiated the send
                $table->string('channel');                                  // email | whatsapp
                $table->string('recipient')->nullable();                    // email address / phone number
                $table->boolean('bulk')->default(false);
                $table->string('ip_address', 45)->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('credential_shares');

        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('credential_sharer_ids');
        });
    }
}
