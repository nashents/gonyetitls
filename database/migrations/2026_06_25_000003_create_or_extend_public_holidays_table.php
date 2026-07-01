<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrExtendPublicHolidaysTable extends Migration
{
    public function up()
    {
        // Table may already exist in the database — skip creation if so.
        if (Schema::hasTable('public_holidays')) {
            // Add any missing columns the existing table may not have yet
            Schema::table('public_holidays', function (Blueprint $table) {
                if (!Schema::hasColumn('public_holidays', 'recurring_annually')) {
                    $table->boolean('recurring_annually')->default(true)
                        ->comment('If true, only month and day matter; year is ignored in lookups')
                        ->after('date');
                }
                if (!Schema::hasColumn('public_holidays', 'active')) {
                    $table->boolean('active')->default(true)->after('recurring_annually');
                }
                if (!Schema::hasColumn('public_holidays', 'company_id')) {
                    $table->bigInteger('company_id')->unsigned()->nullable()
                        ->comment('NULL = applies to all companies in this country')
                        ->after('id');
                    $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
                }
            });
            return;
        }

        Schema::create('public_holidays', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('company_id')->unsigned()->nullable()
                ->comment('NULL = applies to all companies in this country');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');

            $table->string('country', 10)->default('ZW');
            $table->string('name');
            $table->date('date');
            $table->boolean('recurring_annually')->default(true)
                ->comment('If true, only month and day matter; year is ignored in lookups');
            $table->boolean('active')->default(true);

            $table->timestamps();

            $table->index(['country', 'date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('public_holidays');
    }
}
