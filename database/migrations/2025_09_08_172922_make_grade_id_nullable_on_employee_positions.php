<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 0) Drop existing foreign keys (assumes default Laravel FK names).
        Schema::table('employee_positions', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropForeign(['job_title_id']);
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['rank_id']);
            $table->dropForeign(['grade_id']);
        });

        // 1) Normalize common sentinel values to NULL before we enforce FKs again.
        //    If you used other sentinel values, add them here.
        DB::table('employee_positions')
            ->where('department_id', 0)->update(['department_id' => null]);
        DB::table('employee_positions')
            ->where('job_title_id', 0)->update(['job_title_id' => null]);
        DB::table('employee_positions')
            ->where('branch_id', 0)->update(['branch_id' => null]);
        DB::table('employee_positions')
            ->where('rank_id', 0)->update(['rank_id' => null]);
        DB::table('employee_positions')
            ->where('grade_id', 0)->update(['grade_id' => null]);

        // 2) Make columns NULLable (no doctrine/dbal required; MySQL syntax).
        DB::statement("
            ALTER TABLE employee_positions
              MODIFY department_id BIGINT UNSIGNED NULL,
              MODIFY job_title_id BIGINT UNSIGNED NULL,
              MODIFY branch_id BIGINT UNSIGNED NULL,
              MODIFY rank_id BIGINT UNSIGNED NULL,
              MODIFY grade_id BIGINT UNSIGNED NULL
        ");

        // 3) Recreate FKs with RESTRICT (prevents deleting referenced rows in use).
        Schema::table('employee_positions', function (Blueprint $table) {
            $table->foreign('department_id')
                  ->references('id')->on('departments')->restrictOnDelete();

            $table->foreign('job_title_id')
                  ->references('id')->on('job_titles')->restrictOnDelete();

            $table->foreign('branch_id')
                  ->references('id')->on('branches')->restrictOnDelete();

            $table->foreign('rank_id')
                  ->references('id')->on('ranks')->restrictOnDelete();

            $table->foreign('grade_id')
                  ->references('id')->on('grades')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        // WARNING: Rolling back will fail if any of these columns contain NULLs.
        // Before running `down`, set NULLs to valid IDs (or delete those rows).

        Schema::table('employee_positions', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropForeign(['job_title_id']);
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['rank_id']);
            $table->dropForeign(['grade_id']);
        });

        DB::statement("
            ALTER TABLE employee_positions
              MODIFY department_id BIGINT UNSIGNED NOT NULL,
              MODIFY job_title_id BIGINT UNSIGNED NOT NULL,
              MODIFY branch_id BIGINT UNSIGNED NOT NULL,
              MODIFY rank_id BIGINT UNSIGNED NOT NULL,
              MODIFY grade_id BIGINT UNSIGNED NOT NULL
        ");

        Schema::table('employee_positions', function (Blueprint $table) {
            $table->foreign('department_id')
                  ->references('id')->on('departments')->restrictOnDelete();

            $table->foreign('job_title_id')
                  ->references('id')->on('job_titles')->restrictOnDelete();

            $table->foreign('branch_id')
                  ->references('id')->on('branches')->restrictOnDelete();

            $table->foreign('rank_id')
                  ->references('id')->on('ranks')->restrictOnDelete();

            $table->foreign('grade_id')
                  ->references('id')->on('grades')->restrictOnDelete();
        });
    }
};
