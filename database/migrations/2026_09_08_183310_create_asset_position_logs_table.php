<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssetPositionLogsTable extends Migration
{
    /**
     * Periodic GPS snapshots per truck, written by fleet:log-asset-positions.
     * Backs the Asset Positions "in area since/for" and "24h/48h" columns,
     * which need a history of points rather than the single latest reading
     * the tracking providers otherwise expose.
     */
    public function up()
    {
        Schema::create('asset_position_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('horse_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('company_id')->nullable();
            $table->string('source');
            $table->decimal('latitude', 10, 6);
            $table->decimal('longitude', 10, 6);
            $table->decimal('speed', 8, 2)->nullable();
            $table->timestamp('recorded_at');
            $table->timestamps();

            $table->index(['horse_id', 'recorded_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('asset_position_logs');
    }
}
