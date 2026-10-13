<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->unsignedBigInteger('loading_point_id')->nullable()->after('port_of_loading_id');
            $table->foreign('loading_point_id')->references('id')->on('loading_points')->onDelete('set null');

            $table->unsignedBigInteger('offloading_point_id')->nullable()->after('port_of_discharge_id');
            $table->foreign('offloading_point_id')->references('id')->on('offloading_points')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropForeign(['loading_point_id']);
            $table->dropColumn('loading_point_id');

            $table->dropForeign(['offloading_point_id']);
            $table->dropColumn('offloading_point_id');
        });
    }
};
