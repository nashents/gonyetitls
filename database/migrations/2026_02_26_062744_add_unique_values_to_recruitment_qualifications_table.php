<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUniqueValuesToRecruitmentQualificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('recruitment_qualifications', function (Blueprint $table) {
            // 1. Drop foreign keys that are using this index
            // These are the default Laravel names – adjust if you named them manually.
            $table->dropForeign(['candidate_id']);
            $table->dropForeign(['qualification_id']);

            // 2. Drop the old unique index on (candidate_id, qualification_id)
            $table->dropUnique('recruitment_qualifications_candidate_id_qualification_id_unique');

            // 3. Add new unique including deleted_at
            $table->unique(
                ['candidate_id', 'qualification_id', 'deleted_at'],
                'recruitment_qualifications_cand_qual_deleted_unique'
            );

            // 4. Recreate the foreign keys
            $table->foreign('candidate_id')
                ->references('id')
                ->on('recruitment_candidates')
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
        Schema::table('recruitment_qualifications', function (Blueprint $table) {
            // Reverse the process

            // 1. Drop FKs
            $table->dropForeign(['candidate_id']);
            $table->dropForeign(['qualification_id']);

            // 2. Drop the new unique
            $table->dropUnique('recruitment_qualifications_cand_qual_deleted_unique');

            // 3. Restore the original unique index
            $table->unique(
                ['candidate_id', 'qualification_id'],
                'recruitment_qualifications_candidate_id_qualification_id_unique'
            );

            // 4. Recreate the FKs as before
            $table->foreign('candidate_id')
                ->references('id')
                ->on('recruitment_candidates')
                ->cascadeOnDelete();

            $table->foreign('qualification_id')
                ->references('id')
                ->on('qualifications')
                ->cascadeOnDelete();
        });
    }
}
