<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsCustomizedToMenuRegistryTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       // Module Groups
            Schema::table('module_groups', function (Blueprint $table) {
                $table->boolean('is_customized')->default(false)->after('is_active');
                $table->timestamp('customized_at')->nullable()->after('is_customized');
                // $table->index(['is_customized', 'is_active']);
            });

            // Modules
            Schema::table('modules', function (Blueprint $table) {
                $table->boolean('is_customized')->default(false)->after('is_active');
                $table->timestamp('customized_at')->nullable()->after('is_customized');
                // $table->index(['is_customized', 'is_active']);
            });

            // Submodules
            Schema::table('sub_modules', function (Blueprint $table) {
                $table->boolean('is_customized')->default(false)->after('is_active');
                $table->timestamp('customized_at')->nullable()->after('is_customized');
                // $table->index(['is_customized', 'is_active']);
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Submodules
        Schema::table('sub_modules', function (Blueprint $table) {
            // $table->dropIndex(['is_customized', 'is_active']);
            $table->dropColumn(['customized_at', 'is_customized']);
        });

        // Modules
        Schema::table('modules', function (Blueprint $table) {
            // $table->dropIndex(['is_customized', 'is_active']);
            $table->dropColumn(['customized_at', 'is_customized']);
        });

        // Module Groups
        Schema::table('module_groups', function (Blueprint $table) {
            // $table->dropIndex(['is_customized', 'is_active']);
            $table->dropColumn(['customized_at', 'is_customized']);
        });
    }
}
