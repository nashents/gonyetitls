<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Customs offices picked on a Customs Declaration (added once from the
 * declaration form, then selected). customs_declarations.customs_office keeps
 * storing the office NAME, so existing declarations and printouts are
 * untouched; the offices already typed on declarations are loaded here.
 */
class CreateCustomsOfficesTable extends Migration
{
    public function up()
    {
        Schema::create('customs_offices', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->unsignedBigInteger('country_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        $seen = [];
        $rows = DB::table('customs_declarations')
            ->whereNotNull('customs_office')
            ->where('customs_office', '<>', '')
            ->orderBy('id')
            ->get(['customs_office', 'country_id']);

        foreach ($rows as $row) {
            $name = trim($row->customs_office);
            $key = mb_strtolower($name);

            if ($name === '' || isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            DB::table('customs_offices')->insert([
                'name' => $name,
                'country_id' => $row->country_id,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down()
    {
        Schema::dropIfExists('customs_offices');
    }
}
