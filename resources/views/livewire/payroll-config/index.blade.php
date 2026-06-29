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
                            </ul>

                            <div class="tab-content p-20">

                                {{-- GENERAL TAB --}}
                                @if($activeTab === 'general')
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Country Code <small class="text-muted">(ISO 2-letter)</small></label>
                                            <input wire:model.defer="country" type="text" class="form-control" maxlength="2" placeholder="ZW">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Default Payroll Currency</label>
                                            <select wire:model.defer="selectedCurrency" class="form-control">
                                                <option value="">Select currency</option>
                                                @foreach($currencies as $cur)
                                                <option value="{{ $cur->id }}">{{ $cur->code }} – {{ $cur->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label>Proration Method</label>
                                            <select wire:model.defer="proration_method" class="form-control">
                                                <option value="calendar_days">Calendar Days (actual / days in month)</option>
                                                <option value="working_days">Working Days (actual worked / work days)</option>
                                                <option value="fixed_divisor">Fixed Divisor (÷ 30 always)</option>
                                                <option value="none">None — always pay full amount</option>
                                            </select>
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
                                                    <input type="checkbox" wire:model.defer="{{ $field }}" id="toggle_{{ $field }}" style="display:none;">
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
                                <p class="text-muted mb-3"><i class="fa fa-info-circle"></i> Enter GL account codes from your chart of accounts. Leave blank if not applicable.</p>
                                <div class="row">
                                    @foreach([
                                        ['gl_wages_account',   'Wages Expense Account',          'fa-money'],
                                        ['gl_paye_account',    'PAYE Tax Liability Account',      'fa-balance-scale'],
                                        ['gl_nssa_account',    'NSSA / Social Security Liability','fa-shield'],
                                        ['gl_pension_account', 'Pension Fund Liability Account',  'fa-university'],
                                        ['gl_nec_account',     'NEC Levy Liability Account',      'fa-briefcase'],
                                    ] as [$field, $label, $icon])
                                    <div class="col-md-6 mb-3">
                                        <div class="form-group">
                                            <label><i class="fa {{ $icon }}"></i> {{ $label }}</label>
                                            <input wire:model.defer="{{ $field }}" type="text" class="form-control" placeholder="e.g. 5000/001">
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

                            </div>{{-- /tab-content --}}

                            {{-- Save button (not for frequencies tab) --}}
                            @if($activeTab !== 'frequencies')
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
    @if($showFreqModal)
    <div class="modal fade show" style="display:block; background:rgba(0,0,0,0.5);" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">{{ $freq_id ? 'Edit' : 'Add' }} Pay Frequency</h4>
                    <button class="close" wire:click="$set('showFreqModal', false)">&times;</button>
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
                    <button class="btn btn-default" wire:click="$set('showFreqModal', false)">Cancel</button>
                    <button class="btn btn-primary" wire:click="saveFrequency" wire:loading.attr="disabled">
                        <span wire:loading wire:target="saveFrequency"><i class="fa fa-spinner fa-spin"></i></span>
                        Save
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
