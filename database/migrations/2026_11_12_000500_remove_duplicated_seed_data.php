<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * A deploy ran the full DatabaseSeeder a second time on a live database. The
 * seeders are not idempotent, so every seeded lookup table (roles, ranks,
 * currencies, countries, horse makes/models, checklist items, tax brackets,
 * ...) got a second copy of its rows, and CompanySeeder / EmployeeSeeder added
 * a second "Basilmark" company with its company, transporter and admin logins
 * and a second seeded employee.
 *
 * This removes that second copy:
 *
 *  1. Lookup tables - rows identical in every column except id / timestamps
 *     are one group; the newer half of each group is the second seed run.
 *     A table is only touched when those newer halves form one block after
 *     the originals, the way a second run appends them - names the seed
 *     itself lists twice are left as they are. Everything that points at a removed row (declared foreign keys plus
 *     conventionally named `<singular>_id` columns) is repointed to the row
 *     that stays BEFORE the delete, so nothing is orphaned and no ON DELETE
 *     CASCADE can take live records with it. A soft-deleted copy never wins
 *     over a live one.
 *  2. Copies whose only difference is `status` (someone switched one of the
 *     two off afterwards) are merged too, but only inside the id range of the
 *     second seed run found in step 1.
 *  3. The duplicate seed company, logins and employee - removed only when
 *     nothing but their own seed rows references them; otherwise left alone
 *     and reported.
 *
 * Content-based and gated: unless at least five lookup tables are doubled
 * the way a second seed run leaves them, the migration changes nothing at
 * all, so instances that were never double seeded are untouched by a deploy.
 * Safe to re-run. Runs in one transaction. A summary is written
 * to the console and the log.
 */
return new class extends Migration
{
    /** How many lookup tables must be doubled before this counts as a second seed run. */
    private const MIN_DOUBLED_TABLES = 5;

    /** Seeded lookup tables, parents before children. */
    private array $tables = [
        'roles', 'ranks', 'departments', 'freight_service_types', 'currencies', 'branches',
        'vehicle_groups', 'vehicle_types', 'horse_types', 'trip_types', 'trailer_types',
        'vendor_types', 'job_titles', 'service_types', 'leave_types', 'job_types',
        'inspection_groups', 'inspection_types', 'categories', 'account_type_groups',
        'loan_types', 'allowances', 'horse_makes', 'horse_models', 'cargos', 'countries',
        'measurements', 'checklist_categories', 'checklist_sub_categories', 'checklist_items',
        'folders', 'loss_categories', 'loss_groups', 'training_items', 'training_departments',
        'transaction_types', 'tax_brackets', 'payment_methods', 'waste_types', 'stages',
        'criteria', 'checks', 'problem_categories',
    ];

    /** Reference columns that are neither foreign keys nor named `<singular>_id`. */
    private array $extraReferences = [
        'currencies' => ['companies.license_currency_id', 'payments.to_currency_id', 'payments.paid_currency_id'],
    ];

    /** Pivots that can end up holding the same pair twice once references are repointed. */
    private array $pivots = [
        'role_user' => ['user_id', 'role_id'],
        'employee_rank' => ['employee_id', 'rank_id'],
    ];

    private ?array $schemaColumns = null;
    private ?array $foreignKeys = null;
    private array $report = [];

    public function up(): void
    {
        // Gate: an instance that was never double seeded is left completely
        // untouched. A second run of DatabaseSeeder doubles dozens of lookup
        // tables at once, so anything less than several doubled tables is not
        // that event and nothing below runs - no table, login or company.
        $doubledTables = 0;
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && $this->planTable($table)[0]) {
                $doubledTables++;
            }
        }

        if ($doubledTables < self::MIN_DOUBLED_TABLES) {
            $message = 'Seed data was not duplicated on this instance - nothing changed.';
            Log::info("Duplicated seed data cleanup: {$message}");
            if (app()->runningInConsole()) {
                echo "
  {$message}

";
            }

            return;
        }

        DB::transaction(function () {
            foreach ($this->tables as $table) {
                if (Schema::hasTable($table)) {
                    $this->mergeDuplicateRows($table);
                }
            }

            foreach ($this->pivots as $table => $pair) {
                $this->dedupePivot($table, $pair);
            }

            $this->removeDuplicateSeedLogins();
            $this->removeDuplicateSeedCompanies();
        });

        if (!$this->report) {
            $this->report[] = 'No duplicated seed data found.';
        }

        Log::info("Duplicated seed data cleanup:\n".implode("\n", $this->report));

        if (app()->runningInConsole()) {
            echo "\n  ".implode("\n  ", $this->report)."\n\n";
        }
    }

    public function down(): void
    {
        // Data repair only.
    }

    // ------------------------------------------------------------------
    // Lookup tables
    // ------------------------------------------------------------------

    private function mergeDuplicateRows(string $table): void
    {
        [$merge, $statusFixes] = $this->planTable($table);

        if (!$merge) {
            return;
        }

        $this->applyPlan($table, $merge, $statusFixes);
    }

    /**
     * Work out, without changing anything, which rows of a table are the
     * second seed run's copies.
     *
     * @return array{0: array<int, int>, 1: array<int, mixed>} [removed id => surviving id, surviving id => status]
     */
    private function planTable(string $table): array
    {
        $columns = Schema::getColumnListing($table);
        if (!in_array('id', $columns, true)) {
            return [[], []];
        }

        $keyColumns = array_values(array_diff($columns, ['id', 'created_at', 'updated_at', 'deleted_at']));
        if (!$keyColumns) {
            return [[], []];
        }

        $rows = DB::table($table)->orderBy('id')->get()->map(fn ($row) => (array) $row)->all();

        $merge = [];        // removed id => surviving id
        $statusFixes = [];  // surviving id => status
        $secondRun = [];    // ids inserted by the second seed run

        // 1. Exact copies. Grouped in PHP so the comparison is strict - the
        //    database collation would also match "Tyres" with "tyres ".
        $firstRun = [];
        $duplicateGroups = [];
        foreach ($this->group($rows, $keyColumns) as $group) {
            if (count($group) < 2) {
                continue;
            }
            $extra = intdiv(count($group), 2);
            $ids = array_column($group, 'id');
            $firstRun = array_merge($firstRun, array_slice($ids, 0, count($ids) - $extra));
            $secondRun = array_merge($secondRun, array_slice($ids, -$extra));
            $duplicateGroups[] = [$group, $extra];
        }

        // A second seed run appends its copies as one block after every
        // original. Repeats that interleave with the originals, or are
        // scattered between unrelated rows, are names the seed itself lists
        // more than once (e.g. "Wheel Bearings") - not ours to remove.
        if (!$secondRun || min($secondRun) < max($firstRun)) {
            return [[], []];
        }

        $blockStart = min($secondRun);
        $blockEnd = max($secondRun);
        $rowsInBlock = count(array_filter($rows, fn ($row) => $row['id'] >= $blockStart && $row['id'] <= $blockEnd));

        if (count($secondRun) < 0.75 * $rowsInBlock) {
            return [[], []];
        }

        foreach ($duplicateGroups as [$group, $extra]) {
            $this->plan($group, $extra, $merge, $statusFixes);
        }

        // 2. Copies that differ only by status, within the second run's id range.
        if (in_array('status', $keyColumns, true)) {
            $from = $blockStart;
            $to = $blockEnd;
            $remaining = array_filter($rows, fn ($row) => !isset($merge[$row['id']]));

            foreach ($this->group($remaining, array_values(array_diff($keyColumns, ['status']))) as $group) {
                $inside = array_filter($group, fn ($row) => $row['id'] >= $from && $row['id'] <= $to);
                $before = array_filter($group, fn ($row) => $row['id'] < $from);

                if ($inside && $before && count($inside) + count($before) === count($group)) {
                    $this->plan($group, count($inside), $merge, $statusFixes);
                }
            }
        }

        return [$merge, $statusFixes];
    }

    private function applyPlan(string $table, array $merge, array $statusFixes): void
    {
        $references = $this->referenceColumns($table);
        $repointed = 0;

        foreach ($merge as $removedId => $survivorId) {
            foreach ($references as [$refTable, $refColumn]) {
                try {
                    $repointed += DB::table($refTable)->where($refColumn, $removedId)->update([$refColumn => $survivorId]);
                } catch (QueryException $e) {
                    throw new RuntimeException(
                        "Could not repoint {$refTable}.{$refColumn} from {$table} #{$removedId} to #{$survivorId}: ".$e->getMessage(),
                        0,
                        $e
                    );
                }
            }
        }

        foreach ($statusFixes as $survivorId => $status) {
            DB::table($table)->where('id', $survivorId)->update(['status' => $status]);
        }

        foreach (array_chunk(array_keys($merge), 500) as $ids) {
            DB::table($table)->whereIn('id', $ids)->delete();
        }

        $this->report[] = sprintf(
            '%s: removed %d duplicate row(s)%s',
            $table,
            count($merge),
            $repointed ? ", repointed {$repointed} reference(s) to the originals" : ''
        );
    }

    /** @return array<int, array<int, array>> rows grouped by identical values in $columns */
    private function group(array $rows, array $columns): array
    {
        $groups = [];
        foreach ($rows as $row) {
            $key = [];
            foreach ($columns as $column) {
                $value = $row[$column] ?? null;
                $key[] = $value === null ? null : (string) $value;
            }
            $groups[json_encode($key)][] = $row;
        }

        return array_values($groups);
    }

    /**
     * Decide which $extra rows of a group go and which row each one folds
     * into. Live rows are kept ahead of soft-deleted ones, then the oldest.
     */
    private function plan(array $group, int $extra, array &$merge, array &$statusFixes): void
    {
        usort($group, fn ($a, $b) => [isset($a['deleted_at']), $a['id']] <=> [isset($b['deleted_at']), $b['id']]);

        $keep = array_slice($group, 0, count($group) - $extra);
        $drop = array_slice($group, count($group) - $extra);

        foreach ($drop as $i => $row) {
            $survivor = $keep[$i % count($keep)];
            $merge[$row['id']] = $survivor['id'];

            // One live copy switched on, the other off: the survivor stays on.
            if (
                array_key_exists('status', $row)
                && !isset($row['deleted_at']) && !isset($survivor['deleted_at'])
                && is_numeric($row['status']) && is_numeric($survivor['status'])
                && $row['status'] > ($statusFixes[$survivor['id']] ?? $survivor['status'])
            ) {
                $statusFixes[$survivor['id']] = $row['status'];
            }
        }
    }

    private function dedupePivot(string $table, array $pair): void
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'id')) {
            return;
        }

        [$a, $b] = $pair;
        $live = Schema::hasColumn($table, 'deleted_at') ? 'deleted_at IS NULL' : '1 = 1';

        $extraIds = DB::table("{$table} as newer")
            ->whereRaw("newer.{$live}")
            ->whereExists(function ($query) use ($table, $a, $b, $live) {
                $query->selectRaw('1')->from("{$table} as older")
                    ->whereColumn("older.{$a}", "newer.{$a}")
                    ->whereColumn("older.{$b}", "newer.{$b}")
                    ->whereColumn('older.id', '<', 'newer.id')
                    ->whereRaw("older.{$live}");
            })
            ->pluck('newer.id')
            ->all();

        if ($extraIds) {
            DB::table($table)->whereIn('id', $extraIds)->delete();
            $this->report[] = sprintf('%s: removed %d repeated assignment(s)', $table, count($extraIds));
        }
    }

    // ------------------------------------------------------------------
    // Seeded company, logins and employee
    // ------------------------------------------------------------------

    /** EmployeeSeeder's admin + employee and CompanySeeder's transporter login. */
    private function removeDuplicateSeedLogins(): void
    {
        $identities = [
            'seeded admin login' => ['email' => 'pngorima@gonyetitls.com', 'username' => 'pngorima@gonyetitls.com', 'name' => 'Panashe', 'surname' => 'Ngorima'],
            'seeded transporter login' => ['email' => 'info@gonyetitls.com', 'category' => 'transporter', 'name' => 'Gonyeti TLS'],
        ];

        foreach ($identities as $label => $identity) {
            $userIds = DB::table('users')->where($identity)->orderBy('id')->pluck('id')->all();
            array_shift($userIds); // the first one is the original

            foreach ($userIds as $userId) {
                $employees = DB::table('employees')->where('user_id', $userId)->get();

                $blocked = $this->unexpectedReferences('users', [$userId], ['role_user.user_id', 'employees.user_id']);

                foreach ($employees as $employee) {
                    if ($employee->email !== 'pngorima@gonyetitls.com' || $employee->idnumber !== '631550534w44') {
                        $blocked["employees #{$employee->id} is not the seeded employee"] = 1;
                        continue;
                    }
                    $blocked += $this->unexpectedReferences('employees', [$employee->id], [
                        'department_employee.employee_id', 'employee_rank.employee_id', 'employee_leaves.employee_id',
                    ]);
                }

                if ($blocked) {
                    $this->report[] = "SKIPPED duplicate {$label} (user #{$userId}) - in use: ".implode(', ', array_keys($blocked));
                    continue;
                }

                foreach ($employees as $employee) {
                    foreach (['department_employee', 'employee_rank', 'employee_leaves'] as $child) {
                        DB::table($child)->where('employee_id', $employee->id)->delete();
                    }
                    DB::table('employees')->where('id', $employee->id)->delete();
                    $this->report[] = "employees: removed duplicate seeded employee #{$employee->id} ({$employee->employee_number})";
                }

                DB::table('role_user')->where('user_id', $userId)->delete();
                DB::table('users')->where('id', $userId)->delete();
                $this->report[] = "users: removed duplicate {$label} #{$userId}";
            }
        }
    }

    /** CompanySeeder's company + company login, seeded again next to the real company. */
    private function removeDuplicateSeedCompanies(): void
    {
        $firstCompanyId = DB::table('companies')->min('id');

        $companies = DB::table('companies')
            ->where('name', 'Basilmark Software Solutions')
            ->where('email', 'info@basilmark.com')
            ->where('id', '!=', $firstCompanyId)
            ->orderBy('id')
            ->get();

        // Per-company payroll defaults the seeders create for every company: table => its rate table.
        $configTables = [
            'nec_categories' => ['nec_rates', 'nec_category_id'],
            'pension_schemes' => ['pension_scheme_rates', 'pension_scheme_id'],
            'payroll_company_configs' => null,
        ];

        foreach ($companies as $company) {
            $allowed = [];
            foreach (array_keys($configTables) as $configTable) {
                $allowed[] = "{$configTable}.company_id";
            }

            $blocked = $this->unexpectedReferences('companies', [$company->id], $allowed);

            $configIds = [];
            foreach ($configTables as $configTable => $child) {
                if (!Schema::hasTable($configTable)) {
                    continue;
                }
                $ids = DB::table($configTable)->where('company_id', $company->id)->pluck('id')->all();
                $configIds[$configTable] = $ids;
                $blocked += $this->unexpectedReferences($configTable, $ids, $child ? ["{$child[0]}.{$child[1]}"] : []);
            }

            $user = DB::table('users')->where('id', $company->user_id)
                ->where('category', 'company')->where('email', 'info@basilmark.com')->first();
            if ($user) {
                $blocked += $this->unexpectedReferences('users', [$user->id], ['role_user.user_id', 'companies.user_id']);
                if (DB::table('companies')->where('user_id', $user->id)->where('id', '!=', $company->id)->exists()) {
                    $blocked['another company uses the same login'] = 1;
                }
            }

            $menuIds = Schema::hasTable('sub_modules')
                ? DB::table('sub_modules')->where('slug', "company-profile-{$company->id}")->pluck('id')->all()
                : [];
            $blocked += $this->unexpectedReferences('sub_modules', $menuIds, []);

            if ($blocked) {
                $this->report[] = "SKIPPED duplicate seed company #{$company->id} ({$company->name}) - in use: ".implode(', ', array_keys($blocked));
                continue;
            }

            foreach ($configTables as $configTable => $child) {
                $ids = $configIds[$configTable] ?? [];
                if (!$ids) {
                    continue;
                }
                if ($child && Schema::hasTable($child[0])) {
                    DB::table($child[0])->whereIn($child[1], $ids)->delete();
                }
                DB::table($configTable)->whereIn('id', $ids)->delete();
            }

            if ($menuIds) {
                DB::table('sub_modules')->whereIn('id', $menuIds)->delete();
            }

            DB::table('companies')->where('id', $company->id)->delete();
            $this->report[] = "companies: removed duplicate seed company #{$company->id} ({$company->name}) with its payroll defaults and menu entry";

            if ($user) {
                DB::table('role_user')->where('user_id', $user->id)->delete();
                DB::table('users')->where('id', $user->id)->delete();
                $this->report[] = "users: removed duplicate seeded company login #{$user->id}";
            }
        }
    }

    // ------------------------------------------------------------------
    // Reference discovery
    // ------------------------------------------------------------------

    /**
     * Rows that point at the given ids from anywhere other than $allowed
     * ("table.column"). Returns ["table.column (n)" => n].
     */
    private function unexpectedReferences(string $table, array $ids, array $allowed): array
    {
        if (!$ids) {
            return [];
        }

        $found = [];
        foreach ($this->referenceColumns($table) as [$refTable, $refColumn]) {
            if (in_array("{$refTable}.{$refColumn}", $allowed, true)) {
                continue;
            }
            $count = DB::table($refTable)->whereIn($refColumn, $ids)->count();
            if ($count) {
                $found["{$refTable}.{$refColumn} ({$count})"] = $count;
            }
        }

        return $found;
    }

    /**
     * Every column that holds an id of $table: declared foreign keys, columns
     * named `<singular>_id` that are not a foreign key to some other table,
     * and the hand-listed extras.
     *
     * @return array<int, array{0: string, 1: string}>
     */
    private function referenceColumns(string $table): array
    {
        $this->loadSchema();

        $references = [];
        foreach ($this->foreignKeys as $key => $referencedTable) {
            if ($referencedTable === $table) {
                $references[$key] = explode('.', $key, 2);
            }
        }

        $conventional = Str::singular($table).'_id';
        foreach ($this->schemaColumns as $key) {
            [$refTable, $refColumn] = explode('.', $key, 2);
            if ($refColumn === $conventional && !isset($this->foreignKeys[$key])) {
                $references[$key] = [$refTable, $refColumn];
            }
        }

        foreach ($this->extraReferences[$table] ?? [] as $key) {
            if (in_array($key, $this->schemaColumns, true)) {
                $references[$key] = explode('.', $key, 2);
            }
        }

        return array_values($references);
    }

    private function loadSchema(): void
    {
        if ($this->schemaColumns !== null) {
            return;
        }

        $database = DB::getDatabaseName();

        $this->schemaColumns = [];
        $columns = DB::select(
            "SELECT c.TABLE_NAME AS tbl, c.COLUMN_NAME AS col
               FROM information_schema.COLUMNS c
               JOIN information_schema.TABLES t ON t.TABLE_SCHEMA = c.TABLE_SCHEMA AND t.TABLE_NAME = c.TABLE_NAME
              WHERE c.TABLE_SCHEMA = ? AND t.TABLE_TYPE = 'BASE TABLE'",
            [$database]
        );
        foreach ($columns as $column) {
            $this->schemaColumns[] = "{$column->tbl}.{$column->col}";
        }

        $this->foreignKeys = [];
        $keys = DB::select(
            "SELECT TABLE_NAME AS tbl, COLUMN_NAME AS col, REFERENCED_TABLE_NAME AS ref
               FROM information_schema.KEY_COLUMN_USAGE
              WHERE TABLE_SCHEMA = ? AND REFERENCED_TABLE_NAME IS NOT NULL AND REFERENCED_COLUMN_NAME = 'id'",
            [$database]
        );
        foreach ($keys as $key) {
            $this->foreignKeys["{$key->tbl}.{$key->col}"] = $key->ref;
        }
    }
};
