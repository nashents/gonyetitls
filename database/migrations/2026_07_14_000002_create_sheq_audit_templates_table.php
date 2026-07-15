<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSheqAuditTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sheq_audit_templates', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('name')->nullable();
            $table->string('standard')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sheq_audit_sections', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('sheq_audit_template_id')->unsigned()->nullable();
            $table->foreign('sheq_audit_template_id')->references('id')->on('sheq_audit_templates')->onDelete('cascade');
            $table->string('code')->nullable();
            $table->string('title')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sheq_audit_items', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('sheq_audit_section_id')->unsigned()->nullable();
            $table->foreign('sheq_audit_section_id')->references('id')->on('sheq_audit_sections')->onDelete('cascade');
            $table->string('code')->nullable();
            $table->text('requirement')->nullable();
            $table->text('guidance')->nullable();
            $table->integer('possible_mark')->default(0);
            $table->integer('sort_order')->default(0);
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
        Schema::dropIfExists('sheq_audit_items');
        Schema::dropIfExists('sheq_audit_sections');
        Schema::dropIfExists('sheq_audit_templates');
    }
}
