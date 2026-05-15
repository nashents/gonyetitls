<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGoodsReturnedsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('goods_returneds', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('purchase_id')->unsigned()->nullable();
            $table->foreign('purchase_id')->references('id')->on('purchases')->onDelete('cascade');
            $table->bigInteger('vendor_id')->unsigned()->nullable();
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
            $table->bigInteger('goods_received_id')->unsigned()->nullable();
            $table->foreign('goods_received_id')->references('id')->on('goods_receiveds')->onDelete('cascade');
            $table->bigInteger('employee_id')->unsigned()->nullable();
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->string('return_reference')->unique(); // GR-2026-0001
            $table->enum('return_type', ['replacement', 'refund', 'credit_note']);
            $table->enum('status', [
                'draft',
                'approved',
                'dispatched_to_supplier',
                'pending_replacement',
                'replacement_received',
                'refunded',
                'credited',
                'cancelled'
            ])->default('draft');
            $table->date('return_date');
            $table->date('expected_resolution_date')->nullable();
            $table->text('reason')->nullable();
            $table->decimal('total_return_value', 15, 2)->default(0);
            $table->string('currency', 3)->default('USD');
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
        Schema::dropIfExists('goods_returneds');
    }
}
