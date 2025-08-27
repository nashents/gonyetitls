<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQualificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       Schema::create('qualifications', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();            // e.g., "ACCA", "CIS-1"
            $table->string('name');                      // e.g., "ACCA Professional"
            $table->string('category')->nullable();      // e.g., "Accounting", "Safety"
            $table->unsignedTinyInteger('level')->nullable(); // internal level scale (1-10)
            $table->boolean('is_expiring')->default(false);
            $table->unsignedSmallInteger('validity_months')->nullable(); // if expiring
            $table->text('description')->nullable();
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
        Schema::dropIfExists('qualifications');
    }
}
