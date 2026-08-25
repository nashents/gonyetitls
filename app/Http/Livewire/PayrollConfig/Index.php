<?php

namespace App\Http\Livewire\PayrollConfig;

use App\Models\Account;
use App\Models\Currency;
use App\Models\PayrollCompanyConfig;
use App\Models\PayrollFrequency;
use App\Models\PayrollRun;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    // ── Company Config fields ─────────────────────────────────────────────

    public $config_id;
    public $country = 'ZW';
    public $selectedCurrency;
    public $tax_authority_name;
    public $social_security_authority_name;
    public $proration_method = 'calendar_days';
    public $work_week_days = [1,2,3,4,5];
    public $exclude_public_holidays = false;
    public $allow_negative_net_pay  = false;
    public $require_approval_before_lock = true;
    public $require_approval_for_reversal = true;
    public $auto_deduct_loans = true;
    public $auto_deduct_salary_advances = true;
    public $split_payroll_expenses_by_employee_type = false;
    public $gl_wages_account_admin;
    public $gl_wages_account_drivers;
    public $gl_nssa_account;
    public $gl_paye_account;
    public $gl_pension_account;
    public $gl_nec_account;
    public $gl_nssa_employer_expense_account_admin;
    public $gl_nssa_employer_expense_account_drivers;
    public $gl_nec_employer_expense_account_admin;
    public $gl_nec_employer_expense_account_drivers;
    public $gl_pension_employer_expense_account_admin;
    public $gl_pension_employer_expense_account_drivers;
    public $gl_nssa_employee_account;
    public $gl_aids_levy_account;
    public $gl_payroll_suspense_account;
    public $gl_wages_payable_account;

    // ── Frequency fields ─────────────────────────────────────────────────

    public $frequencies = [];
    public $freq_id;
    public $freq_name;
    public $freq_code;
    public $freq_periods_per_year = 12;
    public $freq_days_in_period;
    public $freq_active = true;

    // ── Support data ─────────────────────────────────────────────────────

    public $currencies;
    public $company;
    public $activeTab = 'general';
    public $accountsByGroup = [];
    public $auditHistory = [];

    // Tracks the last-saved value so saveConfig() can detect a real change
    // to the split toggle (vs. re-saving other fields with it left alone).
    public $originalSplitPayrollExpenses = false;

    // Holds a built $data array while a mid-year confirmation is pending.
    public $pendingConfigData = null;

    public function mount()
    {
        $this->authorize('view', PayrollCompanyConfig::class);

        $this->company    = Auth::user()->employee->company;
        $this->currencies = Currency::orderBy('name')->get();
        $this->loadConfig();
        $this->loadFrequencies();
        $this->loadAccounts();
        $this->loadAuditHistory();
    }

    /**
     * Who changed what and when for this company's payroll config, reusing
     * the owen-it/laravel-auditing data already being captured on every
     * save (PayrollCompanyConfig implements Auditable) — no separate
     * tracking needed, just a view onto the existing `audits` table.
     */
    private function loadAuditHistory(): void
    {
        if (!$this->config_id) {
            $this->auditHistory = [];
            return;
        }

        $config = PayrollCompanyConfig::find($this->config_id);

        $this->auditHistory = $config
            ? $config->audits()->with('user')->latest()->limit(50)->get()
                ->map(fn ($audit) => [
                    'user'       => trim(($audit->user?->name ?? 'Unknown') . ' ' . ($audit->user?->surname ?? '')),
                    'event'      => $audit->event,
                    'created_at' => optional($audit->created_at)->format('Y-m-d H:i'),
                    'changes'    => $audit->getModified(),
                ])
                ->toArray()
            : [];
    }

    private function loadAccounts(): void
    {
        $accounts = Account::with(['account_type', 'account_type_group'])
            ->orderBy('code')
            ->get();

        // Two-level: account_type_group (e.g. "Expenses") as the outer optgroup,
        // account_type (e.g. "Cost Of Goods Sold" vs "Operating Expense") as a
        // sub-heading within it, so COGS vs Ops is visible without losing the
        // top-level Assets/Liabilities/Expenses context.
        $this->accountsByGroup = $accounts
            ->groupBy(fn ($account) => $account->account_type_group->name ?? 'Other')
            ->sortKeys()
            ->map(fn ($group) => $group
                ->groupBy(fn ($account) => $account->account_type->name ?? 'Other')
                ->sortKeys()
                ->map(fn ($typeGroup) => $typeGroup->map(fn ($account) => [
                    'code' => $account->code,
                    'name' => $account->name,
                ])->values())
                ->toArray())
            ->toArray();
    }

    private function loadConfig(): void
    {
        $cfg = PayrollCompanyConfig::where('company_id', $this->company->id)->where('active', true)->latest()->first();

        if ($cfg) {
            $this->config_id                     = $cfg->id;
            $this->country                       = $cfg->country;
            $this->selectedCurrency              = $cfg->currency_id;
            $this->tax_authority_name            = $cfg->tax_authority_name;
            $this->social_security_authority_name= $cfg->social_security_authority_name;
            $this->proration_method              = $cfg->proration_method;
            $this->work_week_days                = $cfg->work_week_days ?? [1,2,3,4,5];
            $this->exclude_public_holidays       = $cfg->exclude_public_holidays_from_proration;
            $this->allow_negative_net_pay        = $cfg->allow_negative_net_pay;
            $this->require_approval_before_lock  = $cfg->require_approval_before_lock;
            $this->require_approval_for_reversal = $cfg->require_approval_for_reversal;
            $this->auto_deduct_loans             = $cfg->auto_deduct_loans;
            $this->auto_deduct_salary_advances   = $cfg->auto_deduct_salary_advances;
            $this->split_payroll_expenses_by_employee_type = $cfg->split_payroll_expenses_by_employee_type;
            $this->originalSplitPayrollExpenses  = $cfg->split_payroll_expenses_by_employee_type;
            $this->gl_wages_account_admin         = $cfg->gl_wages_expense_account_admin;
            $this->gl_wages_account_drivers       = $cfg->gl_wages_expense_account_drivers;
            $this->gl_nssa_account               = $cfg->gl_nssa_liability_account;
            $this->gl_paye_account               = $cfg->gl_paye_liability_account;
            $this->gl_pension_account            = $cfg->gl_pension_liability_account;
            $this->gl_nec_account                = $cfg->gl_nec_liability_account;
            $this->gl_nssa_employer_expense_account_admin   = $cfg->gl_nssa_employer_expense_account_admin;
            $this->gl_nssa_employer_expense_account_drivers = $cfg->gl_nssa_employer_expense_account_drivers;
            $this->gl_nec_employer_expense_account_admin    = $cfg->gl_nec_employer_expense_account_admin;
            $this->gl_nec_employer_expense_account_drivers  = $cfg->gl_nec_employer_expense_account_drivers;
            $this->gl_pension_employer_expense_account_admin   = $cfg->gl_pension_employer_expense_account_admin;
            $this->gl_pension_employer_expense_account_drivers = $cfg->gl_pension_employer_expense_account_drivers;
            $this->gl_nssa_employee_account      = $cfg->gl_nssa_employee_liability_account;
            $this->gl_aids_levy_account          = $cfg->gl_aids_levy_liability_account;
            $this->gl_payroll_suspense_account   = $cfg->gl_payroll_suspense_account;
            $this->gl_wages_payable_account      = $cfg->gl_wages_payable_account;
        } else {
            $this->selectedCurrency = $this->company->currency_id;
        }
    }

    private function loadFrequencies(): void
    {
        $this->frequencies = PayrollFrequency::where(function ($q) {
            $q->whereNull('company_id')->orWhere('company_id', $this->company->id);
        })->orderBy('name')->get()->toArray();
    }

    // ── Save config ──────────────────────────────────────────────────────

    public function saveConfig()
    {
        $this->authorize('update', PayrollCompanyConfig::class);

        $this->validate([
            'country'          => 'required|string|size:2',
            'selectedCurrency' => 'required|exists:currencies,id',
            'proration_method' => 'required',
        ]);

        $data = [
            'company_id'                              => $this->company->id,
            'country'                                 => strtoupper($this->country),
            'currency_id'                             => $this->selectedCurrency,
            'tax_authority_name'                      => $this->tax_authority_name,
            'social_security_authority_name'          => $this->social_security_authority_name,
            'proration_method'                        => $this->proration_method,
            'work_week_days'                          => $this->work_week_days,
            'exclude_public_holidays_from_proration'  => $this->exclude_public_holidays,
            'allow_negative_net_pay'                  => $this->allow_negative_net_pay,
            'require_approval_before_lock'            => $this->require_approval_before_lock,
            'require_approval_for_reversal'           => $this->require_approval_for_reversal,
            'auto_deduct_loans'                       => $this->auto_deduct_loans,
            'auto_deduct_salary_advances'             => $this->auto_deduct_salary_advances,
            'split_payroll_expenses_by_employee_type' => $this->split_payroll_expenses_by_employee_type,
            'gl_wages_expense_account_admin'           => $this->gl_wages_account_admin,
            'gl_wages_expense_account_drivers'         => $this->gl_wages_account_drivers,
            'gl_nssa_liability_account'               => $this->gl_nssa_account,
            'gl_paye_liability_account'               => $this->gl_paye_account,
            'gl_pension_liability_account'            => $this->gl_pension_account,
            'gl_nec_liability_account'                => $this->gl_nec_account,
            'gl_nssa_employer_expense_account_admin'   => $this->gl_nssa_employer_expense_account_admin,
            'gl_nssa_employer_expense_account_drivers' => $this->gl_nssa_employer_expense_account_drivers,
            'gl_nec_employer_expense_account_admin'    => $this->gl_nec_employer_expense_account_admin,
            'gl_nec_employer_expense_account_drivers'  => $this->gl_nec_employer_expense_account_drivers,
            'gl_pension_employer_expense_account_admin'   => $this->gl_pension_employer_expense_account_admin,
            'gl_pension_employer_expense_account_drivers' => $this->gl_pension_employer_expense_account_drivers,
            'gl_nssa_employee_liability_account'      => $this->gl_nssa_employee_account,
            'gl_aids_levy_liability_account'          => $this->gl_aids_levy_account,
            'gl_payroll_suspense_account'             => $this->gl_payroll_suspense_account,
            'gl_wages_payable_account'                => $this->gl_wages_payable_account,
            'active'                                  => true,
            'created_by'                              => Auth::id(),
            'updated_by'                              => Auth::id(),
        ];

        $splitChanged = $data['split_payroll_expenses_by_employee_type'] !== $this->originalSplitPayrollExpenses;

        if ($splitChanged && $this->hasPostedPayrollThisCalendarYear()) {
            $this->pendingConfigData = $data;
            $this->dispatchBrowserEvent('show-confirm-modal');
            return;
        }

        $this->persistConfig($data);
    }

    /**
     * Company already has GL-posted payroll runs dated this calendar year —
     * changing the split toggle now would make wages/employer-contribution
     * expenses route inconsistently (some months split COGS/Ops, some not)
     * within the same reporting year. Doesn't block the change, just gates
     * whether saveConfig() asks for confirmation first.
     */
    private function hasPostedPayrollThisCalendarYear(): bool
    {
        return PayrollRun::where('company_id', $this->company->id)
            ->whereYear('payroll_date', now()->year)
            ->whereIn('status', ['posted', 'archived'])
            ->exists();
    }

    /**
     * User confirmed they want the split-toggle change applied despite
     * existing payroll history this calendar year.
     */
    public function confirmSaveConfig()
    {
        $this->authorize('update', PayrollCompanyConfig::class);

        if ($this->pendingConfigData) {
            $this->persistConfig($this->pendingConfigData);
        }

        $this->pendingConfigData = null;
        $this->dispatchBrowserEvent('hide-confirm-modal');
    }

    /**
     * User backed out of the mid-year change — revert just the toggle (the
     * field that triggered the guard) so the form doesn't silently show a
     * value that was never saved; leave every other edited field as-is.
     */
    public function cancelSaveConfig()
    {
        $this->split_payroll_expenses_by_employee_type = $this->originalSplitPayrollExpenses;
        $this->pendingConfigData = null;
        $this->dispatchBrowserEvent('hide-confirm-modal');
    }

    private function persistConfig(array $data): void
    {
        if ($this->config_id) {
            PayrollCompanyConfig::where('id', $this->config_id)->update($data);
        } else {
            $created = PayrollCompanyConfig::create($data);
            $this->config_id = $created->id;
        }

        $this->originalSplitPayrollExpenses = $data['split_payroll_expenses_by_employee_type'];
        $this->loadAuditHistory();

        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Payroll configuration saved.']);
    }

    // ── Frequency CRUD ───────────────────────────────────────────────────

    public function openFreqModal($id = null)
    {
        $this->reset(['freq_id','freq_name','freq_code','freq_periods_per_year','freq_days_in_period']);
        $this->freq_active = true;
        $this->freq_periods_per_year = 12;

        if ($id) {
            $freq = PayrollFrequency::findOrFail($id);
            $this->freq_id              = $freq->id;
            $this->freq_name            = $freq->name;
            $this->freq_code            = $freq->code;
            $this->freq_periods_per_year= $freq->periods_per_year;
            $this->freq_days_in_period  = $freq->days_in_period;
            $this->freq_active          = $freq->active;
        }

        $this->dispatchBrowserEvent('show-freqModal');
    }

    public function saveFrequency()
    {
        $this->validate([
            'freq_name' => 'required|string|max:60',
            'freq_code' => 'required|string|max:20',
            'freq_periods_per_year' => 'required|integer|min:1|max:365',
        ]);

        $data = [
            'company_id'       => $this->company->id,
            'name'             => $this->freq_name,
            'code'             => strtoupper($this->freq_code),
            'periods_per_year' => $this->freq_periods_per_year,
            'days_in_period'   => $this->freq_days_in_period,
            'active'           => $this->freq_active,
        ];

        if ($this->freq_id) {
            PayrollFrequency::where('id', $this->freq_id)->update($data);
        } else {
            PayrollFrequency::create($data);
        }

        $this->loadFrequencies();
        $this->dispatchBrowserEvent('hide-freqModal');
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Frequency saved.']);
    }

    public function toggleFrequency($id)
    {
        $freq = PayrollFrequency::findOrFail($id);
        $activating = !$freq->active;

        if ($activating) {
            // Only one frequency can be active at a time for this company, so
            // activating one deactivates every other frequency visible to it
            // (global defaults + this company's own).
            PayrollFrequency::where('id', '!=', $id)
                ->where(function ($q) {
                    $q->whereNull('company_id')->orWhere('company_id', $this->company->id);
                })
                ->update(['active' => false]);
        }

        $freq->update(['active' => $activating]);
        $this->loadFrequencies();
    }

    public function render()
    {
        return view('livewire.payroll-config.index');
    }
}
