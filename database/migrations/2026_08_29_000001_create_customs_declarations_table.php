<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomsDeclarationsTable extends Migration
{
    public function up()
    {
        Schema::create('customs_declarations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shipment_id');
            $table->foreign('shipment_id')->references('id')->on('shipments')->onDelete('cascade');

            $table->unsignedBigInteger('clearing_agent_id')->nullable();
            $table->foreign('clearing_agent_id')->references('id')->on('clearing_agents')->onDelete('set null');

            $table->unsignedBigInteger('country_id')->nullable();
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('set null');

            $table->unsignedBigInteger('currency_id')->nullable();
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('set null');

            $table->string('declaration_number')->unique();
            $table->string('customs_office')->nullable();
            $table->string('entry_number')->nullable()->index();
            $table->string('declaration_reference')->nullable()->index();
            $table->string('declaration_type')->nullable()->index();
            $table->string('customs_procedure')->nullable();

            $table->unsignedBigInteger('declarant_id')->nullable();
            $table->foreign('declarant_id')->references('id')->on('users')->onDelete('set null');
            $table->unsignedBigInteger('clearing_officer_id')->nullable();
            $table->foreign('clearing_officer_id')->references('id')->on('users')->onDelete('set null');

            $table->date('declaration_date')->nullable();
            $table->date('submission_date')->nullable();
            $table->date('assessment_date')->nullable();
            $table->date('payment_date')->nullable();
            $table->date('inspection_date')->nullable();
            $table->date('release_date')->nullable();
            $table->date('clearance_date')->nullable();

            $table->string('status')->default('instructions_received')->index();

            $table->decimal('total_customs_value', 15, 2)->default(0);
            $table->decimal('total_duty', 15, 2)->default(0);
            $table->decimal('total_vat', 15, 2)->default(0);
            $table->decimal('total_excise', 15, 2)->default(0);
            $table->decimal('total_levies', 15, 2)->default(0);

            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('customs_declarations');
    }
}
