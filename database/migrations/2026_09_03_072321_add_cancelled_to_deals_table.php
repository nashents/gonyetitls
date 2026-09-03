<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('deals', function (Blueprint $table) {
            $table->boolean('cancelled')->default(false)->after('completed');
            $table->text('status_comment')->nullable()->after('cancelled');
        });
    }

    public function down()
    {
        Schema::table('deals', function (Blueprint $table) {
            $table->dropColumn(['cancelled', 'status_comment']);
        });
    }
};
