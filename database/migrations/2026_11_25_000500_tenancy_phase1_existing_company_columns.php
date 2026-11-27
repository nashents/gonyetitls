<?php

use App\Support\TenancyBackfill;
use Illuminate\Database\Migrations\Migration;

/**
 * Multi-tenancy phase 1 (docs/multi-tenancy-plan.md): tables that already
 * have company_id get it repaired and filled so the BelongsToCompany filter
 * can be switched on for their models.
 *  1. Rows filed under the platform company (Gonyeti TLS) but created by a
 *     client company's employee move to that company (user decision).
 *  2. Rows with no company take their parent's, then their creator's, then
 *     the operating company's (single-client installs only).
 * Shared lookups (public holidays, PAYE tables...) keep company_id null = shared.
 */
return new class extends Migration
{
    /** table => [foreign key => parent table] used before the creator. */
    private const STRICT = [
        'agents' => [],
        'asset_position_logs' => [],
        'bank_accounts' => [],
        'bank_reconciliations' => ['bank_account_id' => 'bank_accounts'],
        'bank_statements' => ['bank_account_id' => 'bank_accounts'],
        'bookings' => [],
        'brokers' => [],
        'budgets' => [],
        'company_integrations' => [],
        'consignees' => [],
        'consignors' => [],
        'credit_notes' => ['invoice_id' => 'invoices'],
        'customers' => [],
        'customer_fuel_supplies' => [],
        'deals' => [],
        'debit_notes' => [],
        'debtor_journals' => [],
        'documents' => ['freight_job_id' => 'freight_jobs'],
        'drivers' => ['employee_id' => 'employees'],
        'employees' => [],
        'fiscalizations' => [],
        'fitnesses' => [],
        'freight_jobs' => [],
        'freight_job_reminders' => ['freight_job_id' => 'freight_jobs'],
        'import_logs' => [],
        'invoices' => [],
        'journal_entries' => [],
        'payments' => [],
        'payroll_audit_logs' => [],
        'payroll_company_configs' => [],
        'payroll_reversals' => [],
        'payroll_runs' => [],
        'quotations' => [],
        'receipts' => [],
        'recruitment_candidates' => [],
        'reminders' => [],
        'rentals' => [],
        'salary_advances' => [],
        'sales' => [],
        'shifts' => [],
        'supplier_journals' => [],
        'suspensions' => [],
        'transport_orders' => [],
        'transporters' => [],
        'trips' => [],
        'vendors' => [],
    ];

    public function up()
    {
        foreach (self::STRICT as $table => $parents) {
            TenancyBackfill::repairPlatformRows($table);
            TenancyBackfill::fill($table, $parents);
        }
    }

    public function down()
    {
        // Data repair - not reversible.
    }
};
