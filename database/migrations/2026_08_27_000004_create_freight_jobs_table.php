<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFreightJobsTable extends Migration
{
    public function up()
    {
        Schema::create('freight_jobs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('job_number')->unique();
            $table->string('customer_reference')->nullable()->index();

            $table->unsignedBigInteger('company_id')->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');

            $table->unsignedBigInteger('customer_id')->nullable();
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');

            $table->unsignedBigInteger('freight_service_type_id')->nullable();
            $table->foreign('freight_service_type_id')->references('id')->on('freight_service_types')->onDelete('set null');

            $table->unsignedBigInteger('salesperson_id')->nullable();
            $table->foreign('salesperson_id')->references('id')->on('users')->onDelete('set null');
            $table->unsignedBigInteger('operations_officer_id')->nullable();
            $table->foreign('operations_officer_id')->references('id')->on('users')->onDelete('set null');
            $table->unsignedBigInteger('clearing_officer_id')->nullable();
            $table->foreign('clearing_officer_id')->references('id')->on('users')->onDelete('set null');

            $table->string('import_export_type')->nullable();
            $table->string('shipment_type')->nullable();
            $table->string('primary_transport_mode')->nullable();

            $table->string('origin')->nullable();
            $table->string('destination')->nullable();
            $table->unsignedBigInteger('origin_country_id')->nullable();
            $table->foreign('origin_country_id')->references('id')->on('countries')->onDelete('set null');
            $table->unsignedBigInteger('destination_country_id')->nullable();
            $table->foreign('destination_country_id')->references('id')->on('countries')->onDelete('set null');

            $table->string('incoterm')->nullable();

            $table->unsignedBigInteger('currency_id')->nullable();
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('set null');

            $table->unsignedBigInteger('quotation_id')->nullable();
            $table->foreign('quotation_id')->references('id')->on('quotations')->onDelete('set null');

            $table->decimal('estimated_revenue', 15, 2)->nullable();
            $table->decimal('estimated_cost', 15, 2)->nullable();
            $table->decimal('estimated_margin', 15, 2)->nullable();
            $table->decimal('actual_revenue', 15, 2)->nullable();
            $table->decimal('actual_cost', 15, 2)->nullable();
            $table->decimal('actual_margin', 15, 2)->nullable();

            $table->string('status')->default('draft')->index();

            $table->timestamp('opened_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('freight_jobs');
    }
}
