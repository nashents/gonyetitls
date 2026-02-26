<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUniqueValuesToEmployeeQualificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('employee_qualifications', function (Blueprint $table) {
             // 1. Add softDeletes (deleted_at) column
            // Make sure this was NOT already added before.
            $table->softDeletes();

            // 2. Drop foreign keys that may depend on the existing unique index
            // These assume you used the default Laravel naming conventions
            $table->dropForeign(['employee_id']);
            $table->dropForeign(['qualification_id']);

            // 3. Drop the old unique index on (employee_id, qualification_id)
            // Default name from Laravel: {table}_{col1}_{col2}_unique
            $table->dropUnique('employee_qualifications_employee_id_qualification_id_unique');

            // 4. Add new unique including deleted_at
            $table->unique(
                ['employee_id', 'qualification_id', 'deleted_at'],
                'employee_qualifications_emp_qual_deleted_unique'
            );

            // 5. Recreate the foreign keys
            $table->foreign('employee_id')
                ->references('id')
                ->on('employees')
                ->cascadeOnDelete();

            $table->foreign('qualification_id')
                ->references('id')
                ->on('qualifications')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employee_qualifications', function (Blueprint $table) {
              // Reverse everything carefully

            // 1. Drop FKs
            $table->dropForeign(['employee_id']);
            $table->dropForeign(['qualification_id']);

            // 2. Drop the new unique (with deleted_at)
            $table->dropUnique('employee_qualifications_emp_qual_deleted_unique');

            // 3. Drop the deleted_at column (soft deletes)
            $table->dropSoftDeletes();

            // 4. Restore the original unique index
            $table->unique(
                ['employee_id', 'qualification_id'],
                'employee_qualifications_employee_id_qualification_id_unique'
            );

            // 5. Recreate the FKs as they were
            $table->foreign('employee_id')
                ->references('id')
                ->on('employees')
                ->cascadeOnDelete();

            $table->foreign('qualification_id')
                ->references('id')
                ->on('qualifications')
                ->cascadeOnDelete();
        });
    }
}
