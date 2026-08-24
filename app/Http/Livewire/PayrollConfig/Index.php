<?php

namespace App\Http\Livewire\PayrollConfig;

use App\Models\Account;
use App\Models\Currency;
use App\Models\PayrollCompanyConfig;
use App\Models\PayrollFrequency;
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
    public $gl_wages_account;
    public $gl_nssa_account;
    public $gl_paye_account;
    public $gl_pension_account;
    public $gl_nec_account;
    public $gl_nssa_employer_expense_account;
    public $gl_nec_employer_expense_account;
    public $gl_pension_employer_expense_account;
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

    public function mount()
    {
        $this->company    = Auth::user()->employee->company;
        $this->currencies = Currency::orderBy('name')->get();
        $this->loadConfig();
        $this->loadFrequencies();
        $this->loadAccounts();
    }

    private function loadAccounts(): void
    {
        $accounts = Account::with('account_type_group')
            ->orderBy('code')
            ->get();

        $this->accountsByGroup = $accounts
            ->groupBy(fn ($account) => $account->account_type_group->name ?? 'Other')
            ->map(fn ($group) => $group->map(fn ($account) => [
                'code' => $account->code,
                'name' => $account->name,
            ])->values())
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
            $this->gl_wages_account              = $cfg->gl_wages_expense_account;
            $this->gl_nssa_account               = $cfg->gl_nssa_liability_account;
            $this->gl_paye_account               = $cfg->gl_paye_liability_account;
            $this->gl_pension_account            = $cfg->gl_pension_liability_account;
            $this->gl_nec_account                = $cfg->gl_nec_liability_account;
            $this->gl_nssa_employer_expense_account   = $cfg->gl_nssa_employer_expense_account;
            $this->gl_nec_employer_expense_account    = $cfg->gl_nec_employer_expense_account;
            $this->gl_pension_employer_expense_account= $cfg->gl_pension_employer_expense_account;
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
            'gl_wages_expense_account'                => $this->gl_wages_account,
            'gl_nssa_liability_account'               => $this->gl_nssa_account,
            'gl_paye_liability_account'               => $this->gl_paye_account,
            'gl_pension_liability_account'            => $this->gl_pension_account,
            'gl_nec_liability_account'                => $this->gl_nec_account,
            'gl_nssa_employer_expense_account'        => $this->gl_nssa_employer_expense_account,
            'gl_nec_employer_expense_account'         => $this->gl_nec_employer_expense_account,
            'gl_pension_employer_expense_account'     => $this->gl_pension_employer_expense_account,
            'gl_nssa_employee_liability_account'      => $this->gl_nssa_employee_account,
            'gl_aids_levy_liability_account'          => $this->gl_aids_levy_account,
            'gl_payroll_suspense_account'             => $this->gl_payroll_suspense_account,
            'gl_wages_payable_account'                => $this->gl_wages_payable_account,
            'active'                                  => true,
            'created_by'                              => Auth::id(),
            'updated_by'                              => Auth::id(),
        ];

        if ($this->config_id) {
            PayrollCompanyConfig::where('id', $this->config_id)->update($data);
        } else {
            $created = PayrollCompanyConfig::create($data);
            $this->config_id = $created->id;
        }

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
