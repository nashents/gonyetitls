<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIntegrationProvidersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('integration_providers', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // cartrack, sage_intacct, etc
            $table->string('name');
            $table->string('type'); // tracking, accounting, company, system, payments...
            $table->string('driver')->nullable(); // App\Integrations\Cartrack\CartrackDriver
            $table->json('required_credentials')->nullable(); // ["base_url","username","password"] etc
            $table->json('default_config')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('integration_providers');
    }
}
