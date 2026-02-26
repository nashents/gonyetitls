<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AlterCharsInRecruitmentQualificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('recruitment_qualifications', function (Blueprint $table) {
             DB::statement("
                ALTER TABLE recruitment_qualifications
                MODIFY level VARCHAR(50) NULL,
                MODIFY status VARCHAR(50) NULL DEFAULT 'Pending'
            ");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('recruitment_qualifications', function (Blueprint $table) {
            DB::statement("
                ALTER TABLE recruitment_qualifications
                MODIFY level TINYINT UNSIGNED NULL,
                MODIFY status ENUM('pending','verified','rejected') NOT NULL DEFAULT 'pending'
            ");
        });
    }
}
