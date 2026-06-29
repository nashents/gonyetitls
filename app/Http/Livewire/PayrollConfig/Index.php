<?php

namespace App\Http\Livewire\PayrollConfig;

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

    // ── Frequency fields ─────────────────────────────────────────────────

    public $frequencies = [];
    public $showFreqModal = false;
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

    public function mount()
    {
        $this->company    = Auth::user()->employee->company;
        $this->currencies = Currency::orderBy('name')->get();
        $this->loadConfig();
        $this->loadFrequencies();
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

        $this->showFreqModal = true;
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

        $this->showFreqModal = false;
        $this->loadFrequencies();
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Frequency saved.']);
    }

    public function toggleFrequency($id)
    {
        $freq = PayrollFrequency::findOrFail($id);
        $freq->update(['active' => !$freq->active]);
        $this->loadFrequencies();
    }

    public function render()
    {
        return view('livewire.payroll-config.index');
    }
}
