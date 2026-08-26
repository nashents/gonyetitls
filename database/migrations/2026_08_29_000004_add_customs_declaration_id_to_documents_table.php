<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCustomsDeclarationIdToDocumentsTable extends Migration
{
    public function up()
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->unsignedBigInteger('customs_declaration_id')->nullable()->after('shipping_container_id');
            $table->foreign('customs_declaration_id')->references('id')->on('customs_declarations')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['customs_declaration_id']);
            $table->dropColumn('customs_declaration_id');
        });
    }
}
