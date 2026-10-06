<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDataBackupsTable extends Migration
{
    public function up()
    {
        Schema::create('data_backups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('queued'); // queued | running | completed | failed
            $table->string('path')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->unsignedInteger('exported_count')->default(0);
            $table->json('failed_exports')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('data_backups');
    }
}
