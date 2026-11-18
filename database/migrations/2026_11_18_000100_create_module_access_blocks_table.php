<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-user module locks (App\Support\ModuleAccess). A row blocks a user from a
 * whole module group (module_group_id set) or a single module (module_id set),
 * on top of whatever their department/role visibility allows.
 */
class CreateModuleAccessBlocksTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('module_access_blocks')) {
            return;
        }

        Schema::create('module_access_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('module_group_id')->nullable()->index();
            $table->unsignedBigInteger('module_id')->nullable()->index();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'module_group_id']);
            $table->index(['user_id', 'module_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('module_access_blocks');
    }
}
