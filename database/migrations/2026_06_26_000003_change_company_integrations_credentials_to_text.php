<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ChangeCompanyIntegrationsCredentialsToText extends Migration
{
    /**
     * The CompanyIntegration model casts `credentials` as `encrypted:array`,
     * which stores an encrypted (non-JSON) string. The original column was
     * typed JSON, so MySQL rejected the encrypted value (error 3140). Convert
     * it to TEXT so encrypted credentials can be stored. `config` stays JSON
     * (it is a plain `array` cast, not encrypted).
     *
     * Uses a raw statement to avoid requiring doctrine/dbal for ->change().
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE `company_integrations` MODIFY `credentials` TEXT NULL');
    }

    /**
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE `company_integrations` MODIFY `credentials` JSON NULL');
    }
}
