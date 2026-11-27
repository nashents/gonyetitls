<?php

use App\Models\FreightJob;
use App\Services\Freight\FreightJobStatusSync;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Each container stage now maps to a freight job status (editable on
 * Freight > Master > Container Stages), and recording a container stage
 * moves the job along (FreightJobStatusSync). Seeds the default links for
 * the system stages, then brings existing jobs in line with their containers
 * once (e.g. a job whose containers are Empty Returned but still showed
 * Transport Arranged).
 */
class LinkContainerStagesToJobStatus extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('container_stages', 'job_status')) {
            Schema::table('container_stages', function (Blueprint $table) {
                $table->string('job_status')->nullable()->after('description');
            });
        }

        foreach (FreightJobStatusSync::DEFAULT_MAP as $code => $status) {
            DB::table('container_stages')->where('code', $code)->whereNull('job_status')->update(['job_status' => $status]);
        }

        \App\Models\ContainerStage::flush();

        $sync = app(FreightJobStatusSync::class);
        FreightJob::whereHas('shipments.containers')->get()->each(fn ($job) => $sync->sync($job));
    }

    public function down()
    {
        if (Schema::hasColumn('container_stages', 'job_status')) {
            Schema::table('container_stages', function (Blueprint $table) {
                $table->dropColumn('job_status');
            });
        }
    }
}
