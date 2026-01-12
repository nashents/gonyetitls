<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSortOrderToModulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->foreignId('module_group_id')->constrained()->cascadeOnDelete();
            $table->string('slug');                 // e.g. employees
            $table->string('icon')->nullable();     // e.g. fas fa-users
            $table->string('route_name')->nullable(); // if module itself is clickable
            $table->json('route_params')->nullable();
            $table->string('url')->nullable();        // optional if not using routes
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            // Optional: store “visibility rules” (keep it simple & flexible)
            $table->json('visibility')->nullable(); // e.g. {"any_roles":["Admin"],"any_departments":["HR"]}
            // $table->unique(['module_group_id', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('modules', function (Blueprint $table) {
           // 1) Drop unique index
            // $table->dropUnique(['module_group_id', 'slug']);

            // 2) Drop foreign key + column
            $table->dropForeign(['module_group_id']);

            // 3) Drop the rest of the columns
            $table->dropColumn([
                'module_group_id',
                'slug',
                'icon',
                'route_name',
                'route_params',
                'url',
                'sort_order',
                'is_active',
                'visibility',
            ]);
        });
    }
}
