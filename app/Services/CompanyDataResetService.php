<?php

namespace App\Services;

use App\Services\Accounting\DefaultExpenseResolver;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Wipes all transactional/captured data (trips, invoicing, accounting, HR,
 * SHEQ, logistics, etc.) system-wide while leaving base modules (vehicles,
 * drivers, employees, customers, vendors, consignees, locations, config/
 * lookups) untouched. This system is single-tenant, so the wipe is not
 * scoped by company_id.
 *
 * Shared by the `company:reset-data` artisan command and the admin-only
 * Livewire UI under Company Profile > Settings.
 *
 * Optional "keep employees only" mode ($employeesOnly) returns the system
 * to its initial-deployment state plus the existing employees (with their
 * logins): every table is truncated except the employee/login tables, and
 * the reference data that deployment seeds (chart of accounts, expenses,
 * taxes, types, checklists, ...) is truncated and re-seeded from the same
 * seeders + data migrations a fresh install runs. Master data is then
 * pulled from Sage.
 */
class CompanyDataResetService
{
    /**
     * Tables to wipe, grouped by module. Only tables listed here are ever
     * touched. Anything not listed is left alone on purpose (fail-safe
     * default) — reportUnclassified() surfaces any such table that still
     * has rows so it can be reviewed and added deliberately.
     */
    protected array $wipeTables = [
        'Trips & fleet operations' => [
            'trips', 'trip_origins', 'trip_destinations', 'trip_transport_orders', 'trip_returns',
            'trip_positions', 'trip_locations', 'trip_expenses', 'trip_truck_stop', 'trip_statuses',
            'trailer_trip', 'border_trip', 'clearing_agent_trip', 'invoice_trips',
            'gate_passes', 'gate_pass_trailer', 'allowance_drivers', 'hours',
            'bookings', 'booking_employee', 'shifts', 'loading_point_shift', 'offloading_point_shift',
            'dispatches', 'dispatch_items', 'movements', 'requisitions', 'requisition_items',
            'assignments', 'breakdowns', 'breakdown_assignments', 'breakdown_assignment_trailer', 'breakdown_trailer',
            'tickets', 'ticket_requests', 'ticket_expenses', 'ticket_images', 'ticket_inventories', 'employee_ticket',
            'empty_runs', 'mileages', 'consumptions', 'consumption_logs', 'fuel_requests', 'top_ups', 'rehandlings',
            'fitness_notification', 'fitness_recipient', 'fitnesses', 'trailer_fitnesses',
            'reminders', 'reminder_items', 'reminder_copies', 'recipient_reminder',
            'allocations', 'employee_inspection', 'fuels', 'visitors', 'trip_documents', 'logs',
            'vehicle_assignments', 'trailer_assignments',
        ],
        'Incidents, inspections & workshop' => [
            'incidents', 'incident_damages', 'incident_dates', 'incident_injuries', 'incident_others',
            'basic_causes', 'immediate_causes',
            'inspections', 'inspection_schedules', 'inspection_results', 'checklist_results',
            'disposes', 'breakages', 'work_dones', 'works',
            'retreads', 'retread_items', 'retread_tyres', 'tyre_assignments', 'tyre_dispatches', 'tyre_purchases', 'tyre_transfers',
        ],
        'Inventory & procurement' => [
            'closing_stocks', 'opening_stocks', 'stocks', 'stock_adjustments', 'stock_adjustment_items',
            'transfers', 'transfer_items', 'inventory_transfers',
            'purchases', 'purchase_products', 'purchase_product_documents',
            'goods_receiveds', 'goods_returneds', 'goods_returned_items',
            'inventory_assignments', 'inventory_details', 'inventory_dispatches', 'inventory_documents',
            'inventory_products', 'inventory_purchases', 'inventory_requisition_details', 'inventory_requisitions',
            'inventories',
            'asset_assignments', 'asset_dispatches', 'asset_requisitions', 'asset_requisition_details',
            'waste_collections', 'waste_collection_items', 'waste_disposals', 'waste_disposal_items',
        ],
        'Sales, invoicing & accounting' => [
            'delivery_notes', 'orders', 'order_products', 'sale_items', 'sale_payments', 'sales',
            'quotation_items', 'quotation_products', 'quotations',
            'invoice_items', 'invoice_payments', 'invoice_products', 'invoices',
            'credit_note_items', 'credit_note_products', 'credit_notes',
            'debit_note_items', 'debit_notes',
            'receipts', 'payments', 'payment_items',
            'bank_account_invoice', 'bank_account_quotation',
            'bank_statements', 'bank_statement_lines', 'bank_reconciliations', 'bank_reconciliation_items',
            'account_transactions', 'account_activities', 'journal_entries', 'journal_entry_lines',
            'cash_flows', 'suspense_accounts', 'budgets', 'commissions', 'additional_costs', 'route_expenses', 'expenses',
            'deals', 'transport_orders', 'rentals', 'rental_items', 'suspensions',
            'bills', 'bill_payments', 'bill_expenses',
        ],
        'HR, payroll & recruitment' => [
            'attendances', 'attendance_entries', 'attendance_registers',
            'employee_leaves', 'leaves', 'leave_days',
            'loans', 'salaries', 'salary_items', 'salary_advances',
            'payroll_runs', 'payroll_salaries', 'payroll_salary_items', 'payrolls',
            'payroll_audit_logs', 'payroll_reversals', 'cpd_points',
            'recruitment_candidates', 'recruitment_checks', 'recruitment_decisions',
            'recruitment_qualifications', 'recruitment_scores',
            'applications', 'job_postings',
        ],
        'SHEQ (safety/health/environment) records' => [
            'sheq_actions', 'sheq_appointments', 'sheq_audit_responses', 'sheq_audits', 'sheq_changes',
            'sheq_context_issues', 'sheq_contractor_onboardings', 'sheq_drills', 'sheq_emergencies',
            'sheq_engagements', 'sheq_equipment_inspections', 'sheq_hygiene_surveys', 'sheq_management_reviews',
            'sheq_medical_surveillances', 'sheq_meetings', 'sheq_monitoring_readings', 'sheq_non_conformities',
            'sheq_objective_updates', 'sheq_objectives', 'sheq_obligation_evaluations', 'sheq_obligations',
            'sheq_ppe_issues', 'sheq_risk_assessments', 'sheq_risk_controls', 'sheq_risks', 'sheq_stakeholder_engagements',
        ],
        'Communications, claims & compliance' => [
            's_m_s', 'emails', 'attachments', 'claims', 'compliances', 'losses', 'recoveries',
            'fiscal_documents', 'fiscalizations', 'integration_logs',
        ],
        'System activity logs' => [
            'change_logs', 'audits',
        ],
    ];

    /**
     * Base modules and config/lookup data that must survive a reset, listed
     * here only so reportUnclassified() knows they are deliberately kept.
     */
    protected array $keepModules = [
        'Fleet identity' => ['vehicles', 'vehicle_makes', 'vehicle_models', 'vehicle_types', 'vehicle_groups', 'vehicle_images', 'vehicle_documents', 'transporter_vehicle', 'trailers', 'trailer_types', 'trailer_groups', 'trailer_images', 'trailer_documents', 'trailer_transporter', 'trailer_links', 'horses', 'horse_makes', 'horse_models', 'horse_types', 'horse_groups', 'horse_images', 'horse_documents', 'horse_transporter', 'tyres', 'tyre_details', 'tyre_documents', 'assets', 'asset_details', 'clusters', 'cluster_trailer'],
        'People & partners' => ['drivers', 'driver_transporter', 'employees', 'employee_positions', 'employee_rank', 'department_employee', 'employee_team', 'department_heads', 'employee_nec_assignments', 'employee_pension_enrollments', 'employee_qualifications', 'dependants', 'leave_type_employees', 'customers', 'vendors', 'vendor_types', 'consignees', 'consignors', 'transporters', 'brokers', 'broker_cargo', 'broker_driver', 'broker_horse', 'broker_trailer', 'agents', 'clearing_agents', 'contacts', 'recipients'],
        'Locations & network' => ['loading_points', 'offloading_points', 'destinations', 'distances', 'routes', 'corridors', 'corridor_transporter', 'borders', 'border_clearing_agent', 'border_route', 'cargo_transporter', 'cargos', 'truck_stops', 'stations', 'stages', 'racks', 'bins', 'stores', 'locations', 'location_pins', 'addresses', 'gates', 'groups', 'folders'],
        'Config & lookups' => ['currencies', 'countries', 'provinces', 'account_types', 'account_type_groups', 'accounts', 'tax_brackets', 'tax_brackets_v2', 'tax_tables', 'taxes', 'transaction_types', 'bank_accounts', 'products', 'brands', 'brand_product', 'product_attributes', 'attribute_product', 'attributes', 'attribute_values', 'categories', 'category_values', 'category_checklists', 'capacities', 'compartments', 'component_slots', 'criteria', 'criterions', 'measurements', 'unit_of_measures', 'units_of_measures', 'denominations', 'service_types', 'services', 'workshop_services', 'inspection_types', 'inspection_groups', 'inspection_services', 'checklist_categories', 'checklist_sub_categories', 'checklists', 'checklist_items', 'leave_types', 'loan_types', 'deductions', 'earnings', 'allowances', 'payment_methods', 'expense_categories', 'income_streams', 'problem_categories', 'loss_categories', 'loss_groups', 'discounts', 'rate_cards', 'rates', 'qualifications', 'qualification_equivalents', 'job_titles', 'job_types', 'ranks', 'grades', 'grade_job_title', 'departments', 'teams', 'branches', 'containers', 'cost_items', 'documents', 'trip_groups', 'trip_types'],
        'Payroll & SHEQ config' => ['payroll_frequencies', 'payroll_company_configs', 'pension_schemes', 'pension_scheme_rates', 'statutory_deduction_types', 'statutory_deduction_rates', 'nec_categories', 'nec_rates', 'public_holidays', 'sheq_audit_templates', 'sheq_audit_sections', 'sheq_audit_items', 'sheq_equipment', 'sheq_equipment_classes', 'sheq_stakeholders', 'sheq_chemicals', 'sheq_monitoring_parameters', 'waste_receptacles', 'waste_types'],
        'System & access' => ['users', 'admins', 'roles', 'permissions', 'role_user', 'modules', 'sub_modules', 'module_categories', 'module_groups', 'companies', 'settings', 'integration_providers', 'company_integrations', 'integration_mappings'],
    ];

    protected array $systemTables = ['migrations', 'jobs', 'failed_jobs', 'password_resets', 'personal_access_tokens', 'sessions', 'notifications', 'searches', 'login_audits', 'notices', 'checks', 'exchange_rates', 'trainings', 'training_departments', 'training_items', 'training_plans', 'training_requirements', 'metrics', 'data_backups', 'freight_reminder_digests'];

    /**
     * "Keep employees only" mode: the ONLY tables left untouched (besides
     * $systemTables). These are the employees, their logins, and the HR/
     * company setup their rows point at — truncating and re-seeding those
     * would re-number ids and break employee links. Everything else is
     * either re-seeded ($employeeOnlyReseed) or truncated. Inverted on
     * purpose: new tables are wiped by default in this mode.
     *
     * Drivers are kept because a driver is the employee's driver profile
     * (Employee->driver(), payroll driver/admin split); the Sage driver pull
     * de-dups on employee_id, so a re-pull links rather than duplicates.
     */
    protected array $employeeOnlyKeep = [
        'Employees' => ['employees', 'drivers', 'employee_positions', 'employee_rank', 'department_employee', 'employee_team', 'department_heads', 'employee_nec_assignments', 'employee_pension_enrollments', 'employee_qualifications', 'dependants', 'leave_type_employees', 'folders'],
        'HR setup employees point at' => ['departments', 'teams', 'branches', 'job_titles', 'job_title_qualifications', 'ranks', 'grades', 'grade_job_title', 'qualifications', 'qualification_equivalents', 'leave_types'],
        'Payroll statutory setup' => ['payroll_frequencies', 'payroll_company_configs', 'pension_schemes', 'pension_scheme_rates', 'statutory_deduction_types', 'statutory_deduction_rates', 'nec_categories', 'nec_rates', 'public_holidays', 'tax_brackets', 'tax_brackets_v2', 'tax_tables'],
        'Logins, roles & menus' => ['users', 'admins', 'roles', 'permissions', 'role_user', 'modules', 'sub_modules', 'module_categories', 'module_groups', 'edit_authorizers'],
        'Company & integration setup' => ['companies', 'company_company_type', 'company_types', 'settings', 'integration_providers', 'company_integrations', 'currencies', 'countries', 'provinces'],
    ];

    /** System tables that still hold captured data and are wiped in this mode. */
    protected array $employeeOnlyWipeSystem = ['exchange_rates', 'notices', 'searches', 'metrics'];

    /**
     * "Keep employees only" mode: reference data restored to the
     * initial-deployment state. Each seeder's tables are truncated, then the
     * seeders run in DatabaseSeeder order (seeder => tables it fills).
     */
    protected array $employeeOnlyReseed = [
        \Database\Seeders\VehicleGroupSeeder::class => ['vehicle_groups'],
        \Database\Seeders\VehicleTypeSeeder::class => ['vehicle_types'],
        \Database\Seeders\HorseTypeSeeder::class => ['horse_types'],
        \Database\Seeders\FreightServiceTypeSeeder::class => ['freight_service_types'],
        \Database\Seeders\TripTypeSeeder::class => ['trip_types'],
        \Database\Seeders\TrailerTypeSeeder::class => ['trailer_types'],
        \Database\Seeders\VendorTypeSeeder::class => ['vendor_types'],
        \Database\Seeders\ServiceTypeSeeder::class => ['service_types'],
        \Database\Seeders\JobTypeSeeder::class => ['job_types'],
        \Database\Seeders\InspectionGroupSeeder::class => ['inspection_groups'],
        \Database\Seeders\InspectionTypeSeeder::class => ['inspection_types'],
        \Database\Seeders\CategorySeeder::class => ['categories'],
        \Database\Seeders\AccountTypeGroupSeeder::class => ['account_type_groups'],
        \Database\Seeders\AccountTypeSeeder::class => ['account_types'],
        \Database\Seeders\AccountSeeder::class => ['accounts'],
        \Database\Seeders\ExpenseSeeder::class => ['expenses'],
        \Database\Seeders\LoanTypeSeeder::class => ['loan_types'],
        \Database\Seeders\DeductionSeeder::class => ['deductions'],
        \Database\Seeders\AllowanceSeeder::class => ['allowances'],
        \Database\Seeders\HorseMakeSeeder::class => ['horse_makes'],
        \Database\Seeders\HorseModelSeeder::class => ['horse_models'],
        \Database\Seeders\CargoSeeder::class => ['cargos'],
        \Database\Seeders\MeasurementSeeder::class => ['measurements'],
        \Database\Seeders\ChecklistCategorySeeder::class => ['checklist_categories'],
        \Database\Seeders\ChecklistSubCategorySeeder::class => ['checklist_sub_categories'],
        \Database\Seeders\ChecklistItemSeeder::class => ['checklist_items'],
        \Database\Seeders\LossCategorySeeder::class => ['loss_categories'],
        \Database\Seeders\LossGroupSeeder::class => ['loss_groups'],
        \Database\Seeders\TransactionTypeSeeder::class => ['transaction_types'],
        \Database\Seeders\PaymentMethodSeeder::class => ['payment_methods'],
        \Database\Seeders\WasteTypeSeeder::class => ['waste_types'],
        \Database\Seeders\UnitsOfMeasureSeeder::class => ['units_of_measures'],
        \Database\Seeders\LossCauseSeeder::class => ['losses'],
        \Database\Seeders\StageSeeder::class => ['stages'],
        \Database\Seeders\CriterionSeeder::class => ['criteria'],
        \Database\Seeders\ProblemCategorySeeder::class => ['problem_categories'],
        \Database\Seeders\TaxSeeder::class => ['taxes'],
        \Database\Seeders\PayrollControlAccountSeeder::class => [],
        \Database\Seeders\SheqAuditTemplateSeeder::class => ['sheq_audit_templates', 'sheq_audit_sections', 'sheq_audit_items'],
    ];

    /**
     * Data migrations that add rows to re-seeded tables on deployment. All
     * are idempotent (exists-check / firstOrCreate), so re-running is safe.
     */
    protected array $employeeOnlyDataMigrations = [
        '2026_09_30_000003_seed_unsplit_payroll_expense_accounts',
        '2026_10_10_000001_seed_standard_freight_service_types',
        '2026_10_13_000001_seed_opening_balance_equity_account',
        '2026_10_19_000002_seed_stock_write_off_account',
    ];

    /**
     * "Keep employees only" mode: shared tables where only the employee-owned
     * rows survive. table => closure scoping the rows to DELETE.
     */
    protected function employeeOnlyPartialWipes(): array
    {
        return [
            // Uploaded documents: keep HR documents, drop everything else.
            'documents' => fn ($q) => $q->whereNull('employee_id')->whereNull('department_id')
                ->whereNull('job_title_id')->whereNull('training_id'),
            // Sage links: keep the employee mapping, drop links to wiped rows
            // (horse_class, product_item, store_warehouse, ...) so the re-pull
            // creates fresh records instead of "linking" to a dead local_id.
            'integration_mappings' => fn ($q) => $q->where('entity_type', '<>', 'driver_employee'),
        ];
    }

    /**
     * "Keep employees only" mode: columns on kept rows that point into wiped
     * tables. TRUNCATE resets auto-increment, so these would otherwise
     * silently point at whichever new record re-uses the id after the Sage
     * re-pull. They are nulled instead.
     */
    protected array $employeeOnlyDanglingLinks = [
        'drivers' => ['transporter_id', 'customer_id'],
        'companies' => ['transporter_id', 'loading_point_id', 'offloading_point_id', 'customer_id', 'cargo_id'],
    ];

    /**
     * Kept master tables that carry a stored running balance which is not
     * recalculated automatically once the transactions behind it are wiped
     * (e.g. accounts.balance is the chart-of-accounts / bank-account
     * balance shown in the GL). These rows are never deleted — only the
     * listed columns are reset to 0.
     */
    protected array $balanceColumns = [
        'accounts' => ['balance'],
        'assets' => ['balance'],
        'containers' => ['balance', 'account_balance'],
        'customers' => ['balance'],
        'horses' => ['fuel_balance'],
        'tyres' => ['balance'],
        'vehicles' => ['fuel_balance'],
        'vendors' => ['balance'],
        'workshop_services' => ['balance'],
    ];

    /**
     * Build the dry-run report: per-group, per-table row counts plus the total,
     * and any table with data that isn't classified as wipe or keep.
     */
    public function preview(bool $employeesOnly = false): array
    {
        $wipeGroups = $this->wipeGroups($employeesOnly);
        $existing = collect($wipeGroups)->flatten()->values();
        $counts = $existing->mapWithKeys(fn ($t) => [$t => DB::table($t)->count()]);

        $groups = [];
        foreach ($wipeGroups as $group => $tables) {
            $rows = collect($tables)
                ->filter(fn ($t) => ($counts[$t] ?? 0) > 0)
                ->mapWithKeys(fn ($t) => [$t => $counts[$t]]);

            if ($rows->isEmpty()) {
                continue;
            }

            $groups[$group] = [
                'tables' => $rows->toArray(),
                'total' => $rows->sum(),
            ];
        }

        $partial = [];
        $links = [];
        if ($employeesOnly) {
            foreach ($this->employeeOnlyPartialWipes() as $table => $scope) {
                if (Schema::hasTable($table) && ($n = $scope(DB::table($table))->count()) > 0) {
                    $partial[$table] = $n;
                }
            }
            foreach ($this->existingDanglingLinks() as [$table, $column]) {
                if (($n = DB::table($table)->whereNotNull($column)->count()) > 0) {
                    $links["{$table}.{$column}"] = $n;
                }
            }
        }

        return [
            'groups' => $groups,
            'total' => $counts->sum() + array_sum($partial),
            // Employees-only mode wipes every unlisted table, so nothing is unclassified.
            'unclassified' => $employeesOnly ? [] : $this->unclassifiedTablesWithData($existing)->toArray(),
            'balances' => $this->previewBalances(),
            'partial' => $partial,
            'links' => $links,
        ];
    }

    /**
     * The tables each mode wipes, grouped. Standard mode = the explicit
     * $wipeTables list. Employees-only mode adds every other table in the
     * database that isn't in $employeeOnlyKeep / $systemTables / partially
     * wiped, under one "master data" group.
     */
    protected function wipeGroups(bool $employeesOnly): array
    {
        $groups = collect($this->wipeTables)
            ->map(fn ($tables) => array_values(array_filter($tables, fn ($t) => Schema::hasTable($t))))
            ->toArray();

        if (! $employeesOnly) {
            return $groups;
        }

        $all = $this->allTables();
        $reseed = $this->reseedTables()->intersect($all)->values();

        // Re-seeded tables get their own group, so drop them from the others.
        $groups = collect($groups)->map(fn ($tables) => array_values(array_diff($tables, $reseed->all())))->toArray();

        $alreadyWiped = collect($groups)->flatten()->merge($reseed);
        $keep = collect($this->employeeOnlyKeep)->flatten()
            ->merge(array_diff($this->systemTables, $this->employeeOnlyWipeSystem))
            ->merge(array_keys($this->employeeOnlyPartialWipes()));

        $groups['Master data (re-pull from Sage) & everything else'] = $all
            ->diff($alreadyWiped)
            ->diff($keep)
            ->sort()
            ->values()
            ->toArray();

        $groups['Reset to initial-deployment defaults (re-seeded after the wipe)'] = $reseed->toArray();

        return $groups;
    }

    protected function reseedTables(): Collection
    {
        return collect($this->employeeOnlyReseed)->flatten()->unique()->values();
    }

    /**
     * Re-run the deployment seeders + data migrations for the re-seeded
     * tables (already truncated), restoring a fresh-install state.
     */
    protected function reseed(array &$results): void
    {
        foreach (array_keys($this->employeeOnlyReseed) as $seeder) {
            try {
                Artisan::call('db:seed', ['--class' => $seeder, '--force' => true]);
                $results['[seed] ' . class_basename($seeder)] = 'ok';
            } catch (\Throwable $e) {
                $results['[seed] ' . class_basename($seeder)] = 'FAILED: ' . $e->getMessage();
            }
        }

        foreach ($this->employeeOnlyDataMigrations as $file) {
            try {
                $migration = require_once database_path("migrations/{$file}.php");
                if (! is_object($migration)) {
                    // Named-class migration: class is StudlyCase of the name minus the date prefix.
                    $class = Str::studly(implode('_', array_slice(explode('_', $file), 4)));
                    $migration = new $class;
                }
                ob_start();
                try {
                    $migration->up();
                } finally {
                    ob_end_clean();
                }
                $results["[migration] {$file}"] = 'ok';
            } catch (\Throwable $e) {
                $results["[migration] {$file}"] = 'FAILED: ' . $e->getMessage();
            }
        }
    }

    protected function existingDanglingLinks(): array
    {
        $pairs = [];
        foreach ($this->employeeOnlyDanglingLinks as $table => $columns) {
            foreach ($columns as $column) {
                if (Schema::hasTable($table) && Schema::hasColumn($table, $column)) {
                    $pairs[] = [$table, $column];
                }
            }
        }

        return $pairs;
    }

    protected function allTables(): Collection
    {
        return collect(DB::select('SHOW TABLES'))->map(fn ($row) => array_values((array) $row)[0]);
    }

    public function employeeOnlyKeepGroups(): array
    {
        return $this->employeeOnlyKeep;
    }

    /**
     * Which balance columns currently hold a non-zero value, and how many
     * rows would be affected — for the dry-run report.
     */
    public function previewBalances(): array
    {
        $balances = [];
        foreach ($this->balanceColumns as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }
            foreach ($columns as $column) {
                if (! Schema::hasColumn($table, $column)) {
                    continue;
                }
                $count = DB::table($table)->where($column, '<>', 0)->count();
                if ($count > 0) {
                    $balances["{$table}.{$column}"] = $count;
                }
            }
        }

        return $balances;
    }

    /**
     * Truncate every wipe-listed table that currently has rows. Returns a
     * ['table' => 'deleted N' | 'FAILED: ...'] map. Not wrapped in a DB
     * transaction — TRUNCATE auto-commits in MySQL — so failures are
     * reported per-table rather than rolled back as a whole.
     */
    public function execute(bool $employeesOnly = false): array
    {
        $existing = collect($this->wipeGroups($employeesOnly))->flatten()->values();
        $counts = $existing->mapWithKeys(fn ($t) => [$t => DB::table($t)->count()]);

        $results = [];
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        try {
            foreach ($existing as $table) {
                $before = $counts[$table] ?? 0;
                if ($before === 0) {
                    continue;
                }
                try {
                    DB::table($table)->truncate();
                    $results[$table] = "deleted {$before}";
                } catch (\Throwable $e) {
                    $results[$table] = 'FAILED: ' . $e->getMessage();
                }
            }

            if ($employeesOnly) {
                foreach ($this->employeeOnlyPartialWipes() as $table => $scope) {
                    if (! Schema::hasTable($table)) {
                        continue;
                    }
                    try {
                        $deleted = $scope(DB::table($table))->delete();
                        if ($deleted > 0) {
                            $results[$table] = "deleted {$deleted} (employee rows kept)";
                        }
                    } catch (\Throwable $e) {
                        $results[$table] = 'FAILED: ' . $e->getMessage();
                    }
                }

                foreach ($this->existingDanglingLinks() as [$table, $column]) {
                    try {
                        $affected = DB::table($table)->whereNotNull($column)->update([$column => null]);
                        if ($affected > 0) {
                            $results["{$table}.{$column}"] = "unlinked {$affected}";
                        }
                    } catch (\Throwable $e) {
                        $results["{$table}.{$column}"] = 'FAILED: ' . $e->getMessage();
                    }
                }
            }
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        if ($employeesOnly) {
            $this->reseed($results);
        }

        foreach ($this->balanceColumns as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }
            foreach ($columns as $column) {
                if (! Schema::hasColumn($table, $column)) {
                    continue;
                }
                try {
                    $affected = DB::table($table)->where($column, '<>', 0)->update([$column => 0]);
                    if ($affected > 0) {
                        $results["{$table}.{$column}"] = "zeroed {$affected}";
                    }
                } catch (\Throwable $e) {
                    $results["{$table}.{$column}"] = 'FAILED: ' . $e->getMessage();
                }
            }
        }

        // Dashboard KPIs (revenue, pending authorizations, fuel stock, etc.)
        // are cached for up to 10 minutes (see Dashboard\Index::loadAllKpis)
        // and would otherwise keep showing pre-reset figures until they expire.
        try {
            Cache::flush();
            $results['[cache]'] = 'flushed';
        } catch (\Throwable $e) {
            $results['[cache]'] = 'FAILED: ' . $e->getMessage();
        }

        // Both modes wipe `expenses`, which includes the core rows fuel/trip
        // billing looks up by name (Fuel - COGS, Transporter Payment) — put
        // them back so fuel approvals don't break straight after a reset.
        try {
            $results['[core expenses]'] = implode(' ', app(DefaultExpenseResolver::class)->resolve());
        } catch (\Throwable $e) {
            $results['[core expenses]'] = 'FAILED: ' . $e->getMessage();
        }

        Log::info('company:reset-data executed', [
            'mode' => $employeesOnly ? 'employees_only' : 'transactional',
            'total_rows_before' => $counts->sum(),
            'results' => $results,
        ]);

        return $results;
    }

    protected function unclassifiedTablesWithData(Collection $existingWipeTables): Collection
    {
        $keepList = collect($this->keepModules)->flatten()->values();
        $allTables = $this->allTables();

        $classified = $existingWipeTables->merge($keepList)->merge($this->systemTables);
        $unclassified = $allTables->diff($classified)->values();

        return $unclassified
            ->mapWithKeys(fn ($t) => [$t => Schema::hasTable($t) ? DB::table($t)->count() : 0])
            ->filter(fn ($count) => $count > 0);
    }
}
