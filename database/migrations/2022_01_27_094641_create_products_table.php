<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('department')->nullable();
            $table->bigInteger('category_id')->unsigned()->nullable();
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->bigInteger('category_value_id')->nullable();
            $table->bigInteger('brand_id')->unsigned()->nullable();
            $table->foreign('brand_id')->references('id')->on('brands')->onDelete('cascade');
            $table->string('product_number')->nullable();
            $table->string('name')->nullable();
            $table->bigInteger('account_id')->unsigned()->nullable();
            $table->bigInteger('expense_account_id')->unsigned()->nullable();
            $table->bigInteger('tax_id')->unsigned()->nullable();
            $table->string('price')->nullable();
            $table->string('sell_price')->nullable();
            $table->boolean('sell')->nullable()->default(0);
            $table->boolean('buy')->nullable()->default(0);
            $table->string('model')->nullable();
            $table->string('identification_number')->nullable();
            $table->string('slug')->nullable();
            $table->string('manufacturer')->nullable();
            $table->text('description')->nullable();
            $table->string('filename')->nullable()->default('noimage.png');
            $table->boolean('status')->nullable()->default(1);
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
        Schema::dropIfExists('products');
    }
}
