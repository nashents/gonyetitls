<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inspection_schedules', function (Blueprint $table) {

            $table->id();
            $table->foreignId('horse_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('trailer_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('asset_id')->nullable()->constrained()->cascadeOnDelete();
            $table->bigInteger('service_type_id')->unsigned()->nullable();
            $table->bigInteger('problem_category_id')->unsigned()->nullable();
            $table->string('inspection_schedule_number')->nullable();
            $table->string('type')->nullable();
            $table->string('component')->default('springs'); // springs, suspension, etc.
            $table->enum('trigger_type', ['mileage', 'date', 'both'])->default('both');
            $table->unsignedInteger('interval_km')->nullable();  // e.g. 10000
            $table->unsignedInteger('interval_days')->nullable(); // e.g. 90
            $table->date('last_inspection_date')->nullable();
            $table->unsignedBigInteger('last_inspection_km')->nullable();
            $table->date('next_due_date')->nullable();
            $table->unsignedBigInteger('next_due_km')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspection_schedules');
    }
};