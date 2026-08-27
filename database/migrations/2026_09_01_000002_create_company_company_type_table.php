<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompanyCompanyTypeTable extends Migration
{
    public function up()
    {
        Schema::create('company_company_type', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('company_id');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');

            $table->unsignedBigInteger('company_type_id');
            $table->foreign('company_type_id')->references('id')->on('company_types')->onDelete('cascade');

            $table->timestamps();

            $table->unique(['company_id', 'company_type_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('company_company_type');
    }
}
