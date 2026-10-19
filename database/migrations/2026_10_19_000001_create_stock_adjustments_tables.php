<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stock count + adjustment: a count sheet snapshots each in-stock inventory
 * row's system qty, stores enter what's physically there, and on approval the
 * variance is applied to the row and posted DR/CR Stock Write-off against
 * Spares Inventory (StockAdjustmentService).
 */
class CreateStockAdjustmentsTables extends Migration
{
    public function up()
    {
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('adjustment_number')->nullable();
            $table->date('date');
            $table->foreignId('store_id')->nullable()->constrained('stores')->nullOnDelete();
            $table->text('description')->nullable();
            // draft -> pending (submitted) -> approved | rejected
            $table->string('status')->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('authorized_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('authorization_date')->nullable();
            $table->text('authorization_comments')->nullable();
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            $table->decimal('shortage_value', 15, 2)->default(0);
            $table->decimal('surplus_value', 15, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('stock_adjustment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_adjustment_id')->constrained('stock_adjustments')->cascadeOnDelete();
            $table->foreignId('inventory_id')->constrained('inventories')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('store_id')->nullable()->constrained('stores')->nullOnDelete();
            // Snapshot when the count started; counted_qty null = not counted (no change).
            $table->decimal('system_qty', 15, 4)->default(0);
            $table->decimal('counted_qty', 15, 4)->nullable();
            // Company-currency cost per unit, the same basis Dispatches credit Spares Inventory at.
            $table->decimal('unit_cost', 18, 6)->default(0);
            $table->timestamps();

            $table->unique(['stock_adjustment_id', 'inventory_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('stock_adjustment_items');
        Schema::dropIfExists('stock_adjustments');
    }
}
