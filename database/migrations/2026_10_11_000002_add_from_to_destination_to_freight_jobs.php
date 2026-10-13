<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('freight_jobs', function (Blueprint $table) {
            $table->unsignedBigInteger('from')->nullable()->after('destination_country_id');
            $table->foreign('from')->references('id')->on('destinations')->onDelete('set null');

            $table->unsignedBigInteger('to')->nullable()->after('from');
            $table->foreign('to')->references('id')->on('destinations')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('freight_jobs', function (Blueprint $table) {
            $table->dropForeign(['from']);
            $table->dropColumn('from');

            $table->dropForeign(['to']);
            $table->dropColumn('to');
        });
    }
};
