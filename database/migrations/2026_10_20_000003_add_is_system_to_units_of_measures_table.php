<?php

use Database\Seeders\UnitsOfMeasureSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Lets users add their own Units of Measure (Trip Management > Master > UOMs)
 * while the seeded set stays locked: is_system rows can't be edited or deleted.
 */
class AddIsSystemToUnitsOfMeasuresTable extends Migration
{
    public function up()
    {
        if (! Schema::hasColumn('units_of_measures', 'is_system')) {
            Schema::table('units_of_measures', function (Blueprint $table) {
                $table->boolean('is_system')->default(false)->after('description');
            });
        }

        DB::table('units_of_measures')
            ->whereIn('abbreviation', UnitsOfMeasureSeeder::abbreviations())
            ->update(['is_system' => true]);

        $moduleId = DB::table('modules')->where('slug', 'trip-master')->value('id');

        if ($moduleId) {
            DB::table('sub_modules')->updateOrInsert(
                ['module_id' => $moduleId, 'slug' => 'units-of-measure'],
                [
                    'module_id' => $moduleId,
                    'slug' => 'units-of-measure',
                    'name' => 'UOMs',
                    'icon' => 'fas fa-balance-scale',
                    'route_name' => 'units_of_measure.index',
                    'sort_order' => 215,
                    'is_active' => true,
                    'badge_key' => null,
                    'visibility' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    public function down()
    {
        DB::table('sub_modules')->where('slug', 'units-of-measure')->delete();

        if (Schema::hasColumn('units_of_measures', 'is_system')) {
            Schema::table('units_of_measures', function (Blueprint $table) {
                $table->dropColumn('is_system');
            });
        }
    }
}
