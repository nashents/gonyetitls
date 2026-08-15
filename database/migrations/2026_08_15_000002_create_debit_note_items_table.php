<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDebitNoteItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('debit_note_items', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('debit_note_id')->unsigned()->nullable();
            $table->foreign('debit_note_id')->references('id')->on('debit_notes')->onDelete('cascade');
            $table->bigInteger('bill_expense_id')->unsigned()->nullable();
            $table->string('item')->nullable();
            $table->text('description')->nullable();
            $table->string('qty')->nullable();
            $table->string('amount')->nullable();
            $table->string('subtotal')->nullable();
            $table->string('subtotal_inclusive')->nullable();
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
        Schema::dropIfExists('debit_note_items');
    }
}
