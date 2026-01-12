<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSortOrderToSubModulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sub_modules', function (Blueprint $table) {
            $table->string('slug');                 // e.g. manage-employees
            $table->string('icon')->nullable();
            $table->string('route_name')->nullable(); // e.g. employees.index
            $table->json('route_params')->nullable();
            $table->string('url')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);

            // Optional: "badge" key for counters you already have (leavesPendingCount etc)
            $table->string('badge_key')->nullable(); // e.g. leaves_pending_count

            $table->json('visibility')->nullable();

            $table->unique(['module_id', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sub_modules', function (Blueprint $table) {
            // 1) Drop unique index (Laravel auto-named)
            $table->dropUnique(['module_id', 'slug']);


            // 3) Drop columns
            $table->dropColumn([
                'slug',
                'icon',
                'route_name',
                'route_params',
                'url',
                'sort_order',
                'is_active',
                'badge_key',
                'visibility',
            ]);
        });
    }
}
