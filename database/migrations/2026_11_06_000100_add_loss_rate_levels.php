<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Loss terms redesign (see LossRateService):
 *  - Allowable loss % is customer x cargo: customer_cargo_loss_allowances,
 *    then customers.allowable_loss_percentage (all cargos), then
 *    cargos.allowable_loss_percentage, then the company default.
 *  - Chargeable loss rate is per cargo and depends only on who carried it:
 *    own fleet (default transporter) vs subcontractor (all subbies alike):
 *    cargos.own_fleet_/subcontractor_chargeable_loss_rate, then the
 *    company own_fleet_/subcontractor_chargeable_loss_rate defaults.
 * The old single companies.chargeable_loss_rate seeds both new defaults.
 * Also adds the Trip Management > Master > Loss Rates menu entry.
 */
class AddLossRateLevels extends Migration
{
    private array $columns = [
        'companies' => [
            'own_fleet_chargeable_loss_rate' => 'rate',
            'subcontractor_chargeable_loss_rate' => 'rate',
        ],
        'cargos' => [
            'allowable_loss_percentage' => 'pct',
            'own_fleet_chargeable_loss_rate' => 'rate',
            'subcontractor_chargeable_loss_rate' => 'rate',
        ],
    ];

    public function up()
    {
        foreach ($this->columns as $table => $columns) {
            foreach ($columns as $column => $kind) {
                if (! Schema::hasColumn($table, $column)) {
                    Schema::table($table, function (Blueprint $t) use ($column, $kind) {
                        $kind === 'pct'
                            ? $t->decimal($column, 10, 6)->nullable()
                            : $t->decimal($column, 15, 4)->nullable();
                    });
                }
            }
        }

        if (! Schema::hasTable('customer_cargo_loss_allowances')) {
            Schema::create('customer_cargo_loss_allowances', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('customer_id');
                $table->unsignedBigInteger('cargo_id');
                $table->decimal('allowable_loss_percentage', 10, 6);
                $table->unsignedBigInteger('user_id')->nullable();
                $table->timestamps();

                $table->unique(['customer_id', 'cargo_id'], 'ccla_customer_cargo_unique');
                $table->index('cargo_id');
            });
        }

        if (Schema::hasColumn('companies', 'chargeable_loss_rate')) {
            DB::table('companies')->whereNotNull('chargeable_loss_rate')->whereNull('own_fleet_chargeable_loss_rate')
                ->update(['own_fleet_chargeable_loss_rate' => DB::raw('chargeable_loss_rate')]);
            DB::table('companies')->whereNotNull('chargeable_loss_rate')->whereNull('subcontractor_chargeable_loss_rate')
                ->update(['subcontractor_chargeable_loss_rate' => DB::raw('chargeable_loss_rate')]);
        }

        $moduleId = DB::table('modules')->where('slug', 'trip-master')->value('id');

        if ($moduleId) {
            DB::table('sub_modules')->updateOrInsert(
                ['module_id' => $moduleId, 'slug' => 'loss-rates'],
                [
                    'module_id' => $moduleId,
                    'slug' => 'loss-rates',
                    'name' => 'Loss Rates',
                    'icon' => 'fas fa-percentage',
                    'route_name' => 'loss_rates.index',
                    'sort_order' => 216,
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
        DB::table('sub_modules')->where('slug', 'loss-rates')->delete();
        Schema::dropIfExists('customer_cargo_loss_allowances');

        foreach ($this->columns as $table => $columns) {
            foreach (array_keys($columns) as $column) {
                if (Schema::hasColumn($table, $column)) {
                    Schema::table($table, function (Blueprint $t) use ($column) {
                        $t->dropColumn($column);
                    });
                }
            }
        }
    }
}
