<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title"><i class="fa fa-cog"></i> Payroll Configuration</div>
                        </div>
                        <div class="panel-body p-0">

                            {{-- Tab Nav --}}
                            <ul class="nav nav-tabs px-3 pt-2">
                                <li class="{{ $activeTab === 'general' ? 'active' : '' }}">
                                    <a href="#" wire:click.prevent="$set('activeTab','general')">
                                        <i class="fa fa-globe"></i> General
                                    </a>
                                </li>
                                <li class="{{ $activeTab === 'controls' ? 'active' : '' }}">
                                    <a href="#" wire:click.prevent="$set('activeTab','controls')">
                                        <i class="fa fa-toggle-on"></i> Controls
                                    </a>
                                </li>
                                <li class="{{ $activeTab === 'gl' ? 'active' : '' }}">
                                    <a href="#" wire:click.prevent="$set('activeTab','gl')">
                                        <i class="fa fa-book"></i> GL Accounts
                                    </a>
                                </li>
                                <li class="{{ $activeTab === 'frequencies' ? 'active' : '' }}">
                                    <a href="#" wire:click.prevent="$set('activeTab','frequencies')">
                                        <i class="fa fa-calendar"></i> Pay Frequencies
                                    </a>
                                </li>
                                <li class="{{ $activeTab === 'history' ? 'active' : '' }}">
                                    <a href="#" wire:click.prevent="$set('activeTab','history')">
                                        <i class="fa fa-history"></i> History
                                    </a>
                                </li>
                            </ul>

                            <div class="tab-content p-20">

                                {{-- GENERAL TAB --}}
                                @if($activeTab === 'general')
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Country Code <small class="text-muted">(ISO 2-letter)</small></label>
                                            <input wire:model.defer="country" type="text" class="form-control @error('country') is-invalid @enderror" maxlength="2" placeholder="ZW">
                                            @error('country') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Default Payroll Currency</label>
                                            <select wire:model.defer="selectedCurrency" class="form-control @error('selectedCurrency') is-invalid @enderror">
                                                <option value="">Select currency</option>
                                                @foreach($currencies as $currency)
                                                 <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>
                                                @endforeach
                                            </select>
                                            @error('selectedCurrency') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label>Proration Method</label>
                                            <select wire:model.defer="proration_method" class="form-control @error('proration_method') is-invalid @enderror">
                                                <option value="calendar_days">Calendar Days (actual / days in month)</option>
                                                <option value="working_days">Working Days (actual worked / work days)</option>
                                                <option value="fixed_30">Fixed 30 Days (÷ 30 always)</option>
                                                <option value="scheduled_days">Scheduled Days (per pay frequency schedule)</option>
                                            </select>
                                            @error('proration_method') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label>Tax Authority Name</label>
                                            <input wire:model.defer="tax_authority_name" type="text" class="form-control" placeholder="e.g. ZIMRA, SARS, ZRA">
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label>Social Security Authority</label>
                                            <input wire:model.defer="social_security_authority_name" type="text" class="form-control" placeholder="e.g. NSSA, NAPSA, PSPF">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <label>Work Week Days</label>
                                        <div class="d-flex gap-3 flex-wrap">
                                            @foreach([1=>'Mon',2=>'Tue',3=>'Wed',4=>'Thu',5=>'Fri',6=>'Sat',7=>'Sun'] as $num => $day)
                                            <div class="form-check">
                                                <label class="form-check-label">
                                                    <input type="checkbox" wire:model.defer="work_week_days" value="{{ $num }}" class="form-check-input"> {{ $day }}
                                                </label>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                @endif

                                {{-- CONTROLS TAB --}}
                                @if($activeTab === 'controls')
                                <div class="row">
                                    @foreach([
                                        ['exclude_public_holidays', 'Exclude public holidays from proration', 'info'],
                                        ['allow_negative_net_pay',  'Allow negative net pay',                'warning'],
                                        ['require_approval_before_lock', 'Require approval before locking payroll', 'success'],
                                        ['require_approval_for_reversal','Require approval for reversals',          'success'],
                                        ['auto_deduct_loans',            'Auto-deduct loans each pay run',          'default'],
                                        ['auto_deduct_salary_advances',  'Auto-deduct salary advances each pay run','default'],
                                    ] as [$field, $label, $color])
                                    <div class="col-md-6 mb-3">
                                        <div class="panel panel-{{ $color }}" style="margin-bottom:0;">
                                            <div class="panel-body py-2 px-3 d-flex align-items-center justify-content-between">
                                                <span>{{ $label }}</span>
                                                <div class="toggle-switch">
                                                    <input type="checkbox" {{ $this->$field ? 'checked' : '' }} disabled id="toggle_{{ $field }}" style="display:none;">
                                                    <label for="toggle_{{ $field }}" class="btn btn-sm {{ $this->$field ? 'btn-success' : 'btn-default' }}" wire:click="$toggle('{{ $field }}')">
                                                        {{ $this->$field ? 'ON' : 'OFF' }}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                @endif

                                {{-- GL ACCOUNTS TAB --}}
                                @if($activeTab === 'gl')
                                <p class="text-muted mb-3"><i class="fa fa-info-circle"></i> These accounts drive the actual payroll journal entry posted for each pay run. Accounts are grouped by their chart-of-accounts category so you can see whether you're picking an Expense or a Liability account. Leave a field blank to fall back to the default account of that name.</p>

                                <div class="panel panel-default mb-3">
                                    <div class="panel-body py-2 px-3 d-flex align-items-center justify-content-between">
                                        <div>
                                            <span>Split payroll expenses by employee type</span>
                                            <div class="text-muted small">On: driver payroll cost posts separately to Cost of Goods Sold accounts, admin/office cost stays Operating Expense. Off: every employee, drivers included, posts to one shared expense line.</div>
                                        </div>
                                        <div class="toggle-switch">
                                            <input type="checkbox" {{ $split_payroll_expenses_by_employee_type ? 'checked' : '' }} disabled id="toggle_split_payroll" style="display:none;">
                                            <label for="toggle_split_payroll" class="btn btn-sm {{ $split_payroll_expenses_by_employee_type ? 'btn-success' : 'btn-default' }}" wire:click="$toggle('split_payroll_expenses_by_employee_type')">
                                                {{ $split_payroll_expenses_by_employee_type ? 'ON' : 'OFF' }}
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <h6 class="text-muted">Debit lines (expenses){{ $split_payroll_expenses_by_employee_type ? ' — Admin / Ops' : '' }}</h6>
                                <div class="row mb-3">
                                    @foreach([
                                        ['gl_wages_account_admin',                    'Wages Expense Account'.($split_payroll_expenses_by_employee_type ? ' - Admin' : ''),                    'fa-money'],
                                        ['gl_nssa_employer_expense_account_admin',    'NSSA Employer Contribution Expense'.($split_payroll_expenses_by_employee_type ? ' - Admin' : ''),       'fa-shield'],
                                        ['gl_nec_employer_expense_account_admin',     'NEC Employer Contribution Expense'.($split_payroll_expenses_by_employee_type ? ' - Admin' : ''),        'fa-briefcase'],
                                        ['gl_pension_employer_expense_account_admin', 'Pension Employer Contribution Expense'.($split_payroll_expenses_by_employee_type ? ' - Admin' : ''),    'fa-university'],
                                    ] as [$field, $label, $icon])
                                    <div class="col-md-6 mb-3">
                                        <div class="form-group">
                                            <label><i class="fa {{ $icon }}"></i> {{ $label }}</label>
                                            <select wire:model.defer="{{ $field }}" class="form-control">
                                                <option value="">— none —</option>
                                                @foreach($accountsByGroup as $groupName => $typeGroups)
                                                <optgroup label="{{ $groupName }}">
                                                    @foreach($typeGroups as $typeName => $accounts)
                                                    <option value="" disabled>— {{ $typeName }} —</option>
                                                    @foreach($accounts as $account)
                                                    <option value="{{ $account['id'] }}">{{ $account['code'] ? $account['code'].' — '.$account['name'] : $account['name'] }}</option>
                                                    @endforeach
                                                    @endforeach
                                                </optgroup>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>

                                @if($split_payroll_expenses_by_employee_type)
                                <h6 class="text-muted">Debit lines (expenses) &mdash; Drivers / COGS</h6>
                                <p class="text-muted small mb-2">Posted for any payroll line whose employee has a linked Driver record &mdash; routes driver payroll cost to Cost of Goods Sold instead of Operating Expense.</p>
                                <div class="row mb-3">
                                    @foreach([
                                        ['gl_wages_account_drivers',                    'Wages Expense Account - Drivers',                    'fa-money'],
                                        ['gl_nssa_employer_expense_account_drivers',    'NSSA Employer Contribution Expense - Drivers',       'fa-shield'],
                                        ['gl_nec_employer_expense_account_drivers',     'NEC Employer Contribution Expense - Drivers',        'fa-briefcase'],
                                        ['gl_pension_employer_expense_account_drivers', 'Pension Employer Contribution Expense - Drivers',    'fa-university'],
                                    ] as [$field, $label, $icon])
                                    <div class="col-md-6 mb-3">
                                        <div class="form-group">
                                            <label><i class="fa {{ $icon }}"></i> {{ $label }}</label>
                                            <select wire:model.defer="{{ $field }}" class="form-control">
                                                <option value="">— none —</option>
                                                @foreach($accountsByGroup as $groupName => $typeGroups)
                                                <optgroup label="{{ $groupName }}">
                                                    @foreach($typeGroups as $typeName => $accounts)
                                                    <option value="" disabled>— {{ $typeName }} —</option>
                                                    @foreach($accounts as $account)
                                                    <option value="{{ $account['id'] }}">{{ $account['code'] ? $account['code'].' — '.$account['name'] : $account['name'] }}</option>
                                                    @endforeach
                                                    @endforeach
                                                </optgroup>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                @endif

                                <h6 class="text-muted">Credit lines (liabilities / payables)</h6>
                                <div class="row">
                                    @foreach([
                                        ['gl_paye_account',           'PAYE Tax Liability Account',            'fa-balance-scale'],
                                        ['gl_aids_levy_account',      'AIDS Levy Payable',                     'fa-balance-scale'],
                                        ['gl_nssa_account',           'NSSA Employer Contribution Payable',    'fa-shield'],
                                        ['gl_nssa_employee_account',  'NSSA Employee Contribution Payable',    'fa-shield'],
                                        ['gl_nec_account',            'NEC Levy Payable',                      'fa-briefcase'],
                                        ['gl_pension_account',        'Pension Payable',                       'fa-university'],
                                        ['gl_payroll_suspense_account','Payroll Suspense (loans/advances)',    'fa-question-circle'],
                                        ['gl_wages_payable_account',  'Salaries & Wages Payable (net pay)',    'fa-money'],
                                    ] as [$field, $label, $icon])
                                    <div class="col-md-6 mb-3">
                                        <div class="form-group">
                                            <label><i class="fa {{ $icon }}"></i> {{ $label }}</label>
                                            <select wire:model.defer="{{ $field }}" class="form-control">
                                                <option value="">— none —</option>
                                                @foreach($accountsByGroup as $groupName => $typeGroups)
                                                <optgroup label="{{ $groupName }}">
                                                    @foreach($typeGroups as $typeName => $accounts)
                                                    <option value="" disabled>— {{ $typeName }} —</option>
                                                    @foreach($accounts as $account)
                                                    <option value="{{ $account['id'] }}">{{ $account['code'] ? $account['code'].' — '.$account['name'] : $account['name'] }}</option>
                                                    @endforeach
                                                    @endforeach
                                                </optgroup>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                @endif

                                {{-- FREQUENCIES TAB --}}
                                @if($activeTab === 'frequencies')
                                <div class="mb-3">
                                    <button wire:click="openFreqModal()" class="btn btn-default btn-sm">
                                        <i class="fa fa-plus"></i> Add Frequency
                                    </button>
                                </div>
                                <table class="table table-striped table-sm table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Code</th>
                                            <th>Periods / Year</th>
                                            <th>Days / Period</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($frequencies as $freq)
                                        <tr>
                                            <td>{{ $freq['name'] }}</td>
                                            <td><code>{{ $freq['code'] }}</code></td>
                                            <td class="text-center">{{ $freq['periods_per_year'] }}</td>
                                            <td class="text-center">{{ $freq['days_in_period'] ?? '—' }}</td>
                                            <td>
                                                <span class="badge bg-{{ $freq['active'] ? 'success' : 'secondary' }} text-white">
                                                    {{ $freq['active'] ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td>
                                                <button wire:click="openFreqModal({{ $freq['id'] }})" class="btn btn-xs btn-default"><i class="fa fa-pencil"></i></button>
                                                <button wire:click="toggleFrequency({{ $freq['id'] }})" class="btn btn-xs btn-{{ $freq['active'] ? 'warning' : 'success' }}">
                                                    <i class="fa fa-{{ $freq['active'] ? 'pause' : 'play' }}"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="6" class="text-center text-muted">No frequencies defined yet.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                                @endif

                                {{-- HISTORY TAB --}}
                                @if($activeTab === 'history')
                                <p class="text-muted mb-3"><i class="fa fa-info-circle"></i> Every save to this company's payroll configuration is recorded here — who changed it, when, and the before/after value of each field.</p>
                                <div class="table-responsive">
                                    <table class="table table-striped table-sm table-bordered">
                                        <thead>
                                            <tr>
                                                <th>When</th>
                                                <th>Who</th>
                                                <th>Event</th>
                                                <th>Changes</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($auditHistory as $entry)
                                            <tr>
                                                <td class="text-nowrap">{{ $entry['created_at'] }}</td>
                                                <td>{{ $entry['user'] }}</td>
                                                <td><span class="badge bg-info text-white">{{ ucfirst($entry['event']) }}</span></td>
                                                <td>
                                                    @forelse($entry['changes'] as $field => $values)
                                                    <div>
                                                        <strong>{{ $field }}:</strong>
                                                        <span class="text-muted">{{ is_bool($values['old'] ?? null) ? ($values['old'] ? 'Yes' : 'No') : ($values['old'] ?? '—') }}</span>
                                                        <i class="fa fa-long-arrow-right mx-1"></i>
                                                        <span>{{ is_bool($values['new'] ?? null) ? ($values['new'] ? 'Yes' : 'No') : ($values['new'] ?? '—') }}</span>
                                                    </div>
                                                    @empty
                                                    <span class="text-muted">—</span>
                                                    @endforelse
                                                </td>
                                            </tr>
                                            @empty
                                            <tr><td colspan="4" class="text-center text-muted">No changes recorded yet.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                @endif

                            </div>{{-- /tab-content --}}

                            {{-- Save button (not for frequencies/history tabs) --}}
                            @if(!in_array($activeTab, ['frequencies', 'history']))
                            <div class="panel-footer px-20 py-3">
                                <button wire:click="saveConfig" wire:loading.attr="disabled" class="btn btn-primary">
                                    <span wire:loading wire:target="saveConfig"><i class="fa fa-spinner fa-spin"></i></span>
                                    <i class="fa fa-save"></i> Save Configuration
                                </button>
                            </div>
                            @endif

                        </div>{{-- /panel-body --}}
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Frequency Modal --}}
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="freqModal" tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">{{ $freq_id ? 'Edit' : 'Add' }} Pay Frequency</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-7">
                            <div class="form-group">
                                <label>Name <span class="text-danger">*</span></label>
                                <input wire:model.defer="freq_name" type="text" class="form-control @error('freq_name') is-invalid @enderror" placeholder="e.g. Monthly">
                                @error('freq_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Code <span class="text-danger">*</span></label>
                                <input wire:model.defer="freq_code" type="text" class="form-control @error('freq_code') is-invalid @enderror" placeholder="MONTHLY">
                                @error('freq_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Periods per Year <span class="text-danger">*</span></label>
                                <input wire:model.defer="freq_periods_per_year" type="number" class="form-control" min="1" max="365">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Days in Period</label>
                                <input wire:model.defer="freq_days_in_period" type="number" class="form-control" placeholder="Optional">
                            </div>
                        </div>
                    </div>
                    <div class="form-check">
                        <label class="form-check-label">
                            <input type="checkbox" wire:model.defer="freq_active" class="form-check-input"> Active
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary" wire:click="saveFrequency" wire:loading.attr="disabled">
                        <span wire:loading wire:target="saveFrequency"><i class="fa fa-spinner fa-spin"></i></span>
                        Save
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Mid-year change confirm modal --}}
    <div class="modal fade" id="confirmModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Confirm: Change Payroll Expense Split</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fa fa-warning"></i> This company already has posted payroll for {{ now()->year }}. Changing this setting now means payroll runs before today and after today will route wages to different GL accounts, which can distort Cost of Goods Sold / Operating Expense comparisons within the same year.
                    </div>
                    <p>Do you want to save this change anyway?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" wire:click="cancelSaveConfig" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-warning" wire:click="confirmSaveConfig" wire:loading.attr="disabled">
                        <span wire:loading wire:target="confirmSaveConfig"><i class="fa fa-spinner fa-spin"></i></span>
                        Yes, save anyway
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
