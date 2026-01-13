<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rentals', function (Blueprint $table) {
            
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // creator/agent (optional
            $table->string('rental_number')->unique();
            // Assumes your existing Gonyeti customers table is "customers"
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->dateTime('pickup_at')->index();
            $table->dateTime('due_back_at')->index();
            $table->dateTime('returned_at')->nullable()->index();
            // operational (lean but useful)
            $table->unsignedInteger('pickup_odometer')->nullable();
            $table->unsignedInteger('return_odometer')->nullable();
            $table->unsignedTinyInteger('pickup_fuel_level')->nullable(); // 0 - 100
            $table->unsignedTinyInteger('return_fuel_level')->nullable(); // 0 - 100
            // money
            $table->decimal('rate_amount', 12, 2)->default(0);      // daily rate agreed at booking
            $table->decimal('deposit_amount', 12, 2)->default(0);   // refundable deposit
            $table->string('currency_code', 3)->default('USD')->index();
            // totals (computed from items; still handy to store)
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->decimal('paid_total', 12, 2)->default(0);
            $table->decimal('balance', 12, 2)->default(0);
            $table->enum('status', ['reserved', 'active', 'closed', 'cancelled'])
                  ->default('reserved')
                  ->index();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            // availability queries + dashboards
            $table->index(['vehicle_id', 'pickup_at', 'due_back_at']);
            $table->index(['company_id', 'status']);
            
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rentals');
    }
};