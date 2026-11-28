<?php

namespace App\Console\Commands;

use App\Support\Tenant;
use App\Support\TenancyBackfill;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Is it safe to switch TENANCY_ENFORCED on for this instance?
 *
 *   php artisan tenancy:check          report only (read-only)
 *   php artisan tenancy:check --fix    repair company labels first (same as the
 *                                      2026_11_28 migration), then report
 *
 * Checks the three things that hid customers on trinitas (2026-11-27):
 *  - logins that resolve to no company (they would see nothing);
 *  - filtered rows with no / a platform (Gonyeti, Basilmark) / a vanished company;
 *  - links between filtered records in different companies (a trip whose
 *    customer is in another company shows no customer).
 */
class TenancyCheck extends Command
{
    protected $signature = 'tenancy:check {--fix : Repair company labels before checking}';

    protected $description = 'Check (and optionally repair) company labels before enabling multi-tenancy filtering';

    public function handle()
    {
        $this->line('Company filter (TENANCY_ENFORCED): ' . (Tenant::enforced() ? '<fg=yellow>ON</>' : 'off'));

        $companies = DB::table('companies')->orderBy('id')->get(['id', 'name', 'type']);
        $platform = Tenant::platformCompanyIds();
        $operating = Tenant::operatingCompanyId();
        $this->table(['id', 'Company', 'Type', 'Role'], $companies->map(fn ($c) => [
            $c->id, $c->name, $c->type,
            in_array((int) $c->id, $platform, true) ? 'platform (Gonyeti/Basilmark)' : ((int) $c->id === $operating ? 'CLIENT - default owner' : 'client'),
        ])->all());
        if (!$operating) {
            $this->warn('More than one client company (or none): rows whose creator has no client company cannot be assigned automatically.');
        }

        if ($this->option('fix')) {
            $this->info('Repairing company labels...');
            $rows = [];
            foreach (TenancyBackfill::alignAll() as $table => $r) {
                if ($r['creator'] + $r['parent'] + $r['default'] + $r['left'] > 0) {
                    $rows[] = [$table, $r['creator'], $r['parent'], $r['default'], $r['left']];
                }
            }
            $rows ? $this->table(['Table', 'From creator', 'From parent', 'To client co.', 'Still unassigned'], $rows) : $this->line('Nothing to repair.');
        }

        $problems = 0;

        // 1. Logins without a company.
        $noCompany = DB::table('users')->orderBy('id')->get(['id', 'name', 'email', 'category'])
            ->filter(fn ($u) => Tenant::companyOfUser((int) $u->id) === null);
        $this->newLine();
        $this->line('<options=bold>1. Logins that would see nothing (no company):</> ' . $noCompany->count());
        if ($noCompany->isNotEmpty()) {
            $problems += $noCompany->count();
            $this->table(['id', 'Name', 'Email', 'Category'], $noCompany->take(20)->map(fn ($u) => [$u->id, $u->name, $u->email, $u->category])->all());
        }

        // 2. Rows needing a company.
        $tables = TenancyBackfill::scopedTables();
        $needs = [];
        foreach ($tables as $table => $mode) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'company_id')) {
                continue;
            }
            if ($n = TenancyBackfill::needsCompany(DB::table($table), $table, $mode)->count()) {
                $needs[] = [$table, $mode, $n];
            }
        }
        $this->newLine();
        $this->line('<options=bold>2. Filtered rows with no / platform / missing company:</> ' . array_sum(array_column($needs, 2)));
        if ($needs) {
            $problems += array_sum(array_column($needs, 2));
            $this->table(['Table', 'Mode', 'Rows'], $needs);
        }

        // 3. Links across companies between filtered tables.
        $cross = [];
        foreach ($tables as $table => $mode) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'company_id')) {
                continue;
            }
            foreach (Schema::getColumnListing($table) as $column) {
                if (!Str::endsWith($column, '_id') || in_array($column, ['company_id', 'user_id', 'creator_id', 'created_by_id'], true)) {
                    continue;
                }
                $target = Str::plural(Str::beforeLast($column, '_id'));
                if (!isset($tables[$target]) || !Schema::hasColumn($target, 'company_id')) {
                    continue;
                }
                $query = DB::table($table . ' as a')->join($target . ' as b', 'b.id', '=', 'a.' . $column)
                    ->whereNotNull('a.company_id')->whereNotNull('b.company_id')
                    ->whereColumn('a.company_id', '!=', 'b.company_id');
                if (Schema::hasColumn($table, 'deleted_at')) {
                    $query->whereNull('a.deleted_at');
                }
                if ($n = $query->count()) {
                    $cross[] = ["{$table}.{$column}", $target, $n];
                }
            }
        }
        $this->newLine();
        $this->line('<options=bold>3. Records linked to a record in another company (link would show blank):</> ' . array_sum(array_column($cross, 2)));
        if ($cross) {
            $problems += array_sum(array_column($cross, 2));
            $this->table(['Link', 'Points at', 'Rows'], $cross);
        }

        $this->newLine();
        if ($problems === 0) {
            $this->info('SAFE: switching TENANCY_ENFORCED=true will not hide records on this instance.');
            return 0;
        }

        $this->error('NOT SAFE yet: fix the items above' . ($this->option('fix') ? ' (left after --fix need a person to decide).' : ' - try `php artisan tenancy:check --fix`.'));
        return 1;
    }
}
