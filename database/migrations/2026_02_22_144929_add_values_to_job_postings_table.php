<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddValuesToJobPostingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('job_postings', function (Blueprint $table) {
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('rank_id')->unsigned()->nullable();
            $table->foreign('rank_id')->references('id')->on('ranks')->onDelete('cascade');
            $table->bigInteger('department_id')->unsigned()->nullable();
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->string('job_posting_number')->nullable();
            $table->bigInteger('job_title_id')->unsigned()->nullable();
            $table->foreign('job_title_id')->references('id')->on('job_titles')->onDelete('cascade');
            $table->string('due_date')->nullable();
            $table->string('start_date')->nullable();
            $table->string('contract_type')->nullable();
            $table->string('duration')->nullable();
            $table->text('description')->nullable();
            $table->text('duties')->nullable();
            $table->text('requirements')->nullable();
            $table->text('instructions')->nullable();
            $table->bigInteger('number_of_candidates')->default(1);
            $table->string('cover_image')->default('jobs.jpg');
            $table->string('status')->default(1);
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('job_postings', function (Blueprint $table) {
            
            // Drop foreign key constraints first
            $table->dropForeign(['user_id']);
            $table->dropForeign(['rank_id']);
            $table->dropForeign(['department_id']);
            $table->dropForeign(['job_title_id']);

            // Then drop the columns
            $table->dropColumn([
                'user_id',
                'rank_id',
                'department_id',
                'job_title_id',
                'job_posting_number',
                'due_date',
                'start_date',
                'contract_type',
                'duration',
                'description',
                'duties',
                'requirements',
                'instructions',
                'cover_image',
                'status',
                'number_of_candidates',
                'deleted_at', 
                // from $table->softDeletes()
            ]);
        });
    }
}
