<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIntegrationLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('integration_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_integration_id')->constrained()->cascadeOnDelete();
            $table->string('direction')->nullable(); // inbound|outbound
            $table->string('action')->nullable();    // test|sync|push|pull|webhook
            $table->string('status')->nullable();    // ok|fail
            $table->text('message')->nullable();
            $table->json('meta')->nullable();        // safe metadata only
            $table->timestamps();
            $table->softDeletes();
            $table->index(['company_integration_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('integration_logs');
    }
}
